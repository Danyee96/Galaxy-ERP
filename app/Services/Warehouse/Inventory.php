<?php
namespace App\Services\Warehouse;

use App\Models\Warehouse\{Warehouse, Location, Item, Balance, Movement, Operation, Transfer, TransferLine};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Inventory
{
    private int $depth=0;
    private ?bool $installed=null;
    public function inTransaction(): bool { return $this->depth>0 && DB::transactionLevel()>0; }
    public function fail(string $message): void { throw ValidationException::withMessages(['inventory' => $message]); }
    public function ready(): bool { return $this->installed=(bool) DB::table('warehouse_state')->where('key','ready')->value('value'); }
    public function installed(): bool { if($this->installed!==null)return $this->installed; return $this->installed=\Illuminate\Support\Facades\Schema::hasTable('warehouse_state') && $this->ready(); }
    public function atomic(callable $action)
    {
        return DB::transaction(function () use ($action) {
            DB::table('warehouse_state')->where('key','mutex')->update(['value'=>DB::raw('value')]);
            if (!DB::table('warehouse_state')->where('key','mutex')->lockForUpdate()->first()) $this->fail('Warehouse backfill has not initialized the transaction lock.');
            if (!$this->ready()) $this->fail('Run and reconcile the warehouse backfill before enabling inventory operations.');
            $this->depth++;
            try { return $action(); } finally { $this->depth--; }
        }, 5);
    }
    public function operation(string $type, array $input, callable $action): Operation
    {
        return $this->atomic(function () use ($type,$input,$action) {
            $hash = hash('sha256', json_encode([$type,$input],JSON_THROW_ON_ERROR));
            $op = Operation::where('request_key',$input['request_key'])->first();
            if ($op) {
                if ($op->input_hash !== $hash || (int)$op->user_id !== (int)auth()->id()) $this->fail('This request key belongs to a different transaction.');
                return $op;
            }
            $op = Operation::create(['request_key'=>$input['request_key'], 'input_hash'=>$hash,'type'=>$type,'source'=>$input['source']??$type,'reference'=>$input['reference']??null,'date'=>$input['date']??today()->toDateString(),'user_id'=>auth()->id(),'notes'=>$input['notes']??null]);
            $op->update(['result'=>$action($op)]);
            return $op;
        });
    }
    public function itemFor($source): Item
    {
        $type = array_search(get_class($source), array_map(fn($definition)=>$definition[0],config('warehouses.sources')),true);
        if ($type === false) $this->fail('Unsupported stock source.');
        if($type==='product' && DB::table('warehouse_state')->where('key','unassigned:product:'.$source->id)->exists()) $this->fail('This product has unassigned legacy stock. Correct its quantity and assign a warehouse from the inventory warning first.');
        return Item::firstOrCreate(['source_type'=>$type,'source_id'=>$source->id]);
    }
    public function location(int $id, bool $physical = true): Location
    {
        $loc=Location::with('warehouse')->lockForUpdate()->findOrFail($id);
        if (!$loc->active || !$loc->warehouse->active || ($physical && $loc->kind !== 'physical')) $this->fail('Select an active physical warehouse location.');
        return $loc;
    }
    public function balance(int $item, int $location): Balance
    {
        Balance::firstOrCreate(['item_id'=>$item,'location_id'=>$location],['quantity'=>'0']);
        return Balance::where('item_id',$item)->where('location_id',$location)->lockForUpdate()->firstOrFail();
    }
    public function move(Operation $op, Item $item, ?int $from, ?int $to, int $units): void
    {
        $precision=config('warehouses.sources.'.$item->source_type)[3]??4;
        if($units % (10 ** (4-$precision)) !== 0) $this->fail('This stock source supports '.$precision.' decimal places.');
        if ($units<=0 || ($from === $to)) $this->fail('A movement needs a positive quantity and different locations.');
        if ($item->source_type === 'ups' && $units !== 10000) $this->fail('Serialized UPS units must move one complete unit.');
        $source=$from ? $this->balance($item->id,$from) : null;
        $dest=$to ? $this->balance($item->id,$to) : null;
        $before=$source ? Quantity::units($source->quantity) : null; $dbefore=$dest ? Quantity::units($dest->quantity) : null;
        if ($source && $before < $units) $this->fail('Insufficient stock at the selected source location.');
        if ($dest && $item->source_type === 'ups' && $dbefore+$units>10000) $this->fail('This serialized unit already exists at the destination.');
        if ($source) $source->update(['quantity'=>Quantity::text($before-$units)]);
        if ($dest) $dest->update(['quantity'=>Quantity::text($dbefore+$units)]);
        Movement::create(['operation_id'=>$op->id,'item_id'=>$item->id,'source_location_id'=>$from,'destination_location_id'=>$to,'quantity'=>Quantity::text($units),'source_before'=>$before===null?null:Quantity::text($before),'source_after'=>$before===null?null:Quantity::text($before-$units),'destination_before'=>$dbefore===null?null:Quantity::text($dbefore),'destination_after'=>$dbefore===null?null:Quantity::text($dbefore+$units)]);
    }
    public function sync(Item $item, int $delta, bool $updateProjected=true): void
    {
        [$class,$field,$projected]=config('warehouses.sources.'.$item->source_type);
        if (!$field) return; // Serialized units have no legacy quantity aggregate.
        $model=$class::whereKey($item->source_id)->lockForUpdate()->firstOrFail();
        $sum=$item->balances()->sum('quantity');
        if(in_array($item->source_type,['raw','semi','finished'],true) && Quantity::units($sum)>9999999999900) $this->fail('Quantity exceeds the existing material stock column capacity.');
        if (Quantity::units($model->$field)!==Quantity::units($sum)-$delta) $this->fail('Warehouse and legacy totals differ. Reconcile before posting.');
        $changes=[$field=>Quantity::text(Quantity::units($sum))];
        if ($model->usesTimestamps()) $changes['updated_at']=now();
        if ($projected && $updateProjected) $changes[$projected]=Quantity::text(Quantity::units($model->$projected)+$delta);
        DB::table($model->getTable())->where('id',$model->id)->update($changes);
        $this->verifyLegacy($model);
    }
    public function post(string $type, array $input): Operation
    {
        return $this->operation($type,$input,function ($op) use ($type,$input) {
            $item=Item::lockForUpdate()->findOrFail($input['item_id']);
            $loc=$this->location((int)$input['location_id']);
            if ((int)$loc->warehouse_id !== (int)$input['warehouse_id']) $this->fail('Location does not belong to the selected warehouse.');
            $qty=Quantity::units($input['quantity']);
            if (!in_array($type,['inbound','outbound','return','adjustment-in','adjustment-out'],true)) $this->fail('Invalid stock movement.');
            $in=in_array($type,['inbound','return','adjustment-in'],true);
            if ($item->source_type === 'ups' && $in && Quantity::units($item->balances()->sum('quantity'))+$qty>10000) $this->fail('A serial number can only represent one unit.');
            $this->move($op,$item,$in?null:$loc->id,$in?$loc->id:null,$qty);
            $this->sync($item,$in?$qty:-$qty);
            return ['item_id'=>$item->id];
        });
    }
    public function quickTransfer(array $input): Operation
    {
        return $this->operation('quick-transfer',$input,function($op) use($input) {
            $source=Balance::with('location')->lockForUpdate()->findOrFail($input['balance_id']);
            $destination=$this->location((int)$input['destination_location_id']);
            if((int)$destination->warehouse_id!==(int)$input['destination_warehouse_id']) $this->fail('Destination location does not belong to the selected warehouse.');
            if((int)$source->location->warehouse_id===(int)$destination->warehouse_id) $this->fail('Choose a different destination warehouse.');
            $available=Quantity::units($source->quantity);
            $units=$input['mode']==='all'?$available:Quantity::units($input['quantity']??'0');
            if($units<=0 || $units>$available) $this->fail('Enter a positive quantity no greater than available stock.');
            $quantity=Quantity::text($units);
            $created=$this->transfer(['request_key'=>(string)Str::uuid(),'reference'=>$input['reference']??'Quick stock transfer','lines'=>[['item_id'=>$source->item_id,'source_location_id'=>$source->location_id,'destination_location_id'=>$destination->id,'quantity'=>$quantity]]]);
            $id=$created->result['transfer_id'];
            $this->dispatch(['request_key'=>(string)Str::uuid(),'transfer_id'=>$id]);
            $line=TransferLine::where('transfer_id',$id)->firstOrFail();
            $this->receiveTransfer(['request_key'=>(string)Str::uuid(),'transfer_id'=>$id,'lines'=>[['id'=>$line->id,'quantity'=>$quantity]]]);
            return ['transfer_id'=>$id,'quantity'=>$quantity];
        });
    }

    public function transfer(array $input): Operation
    {
        return $this->operation('transfer',$input,function ($op) use ($input) {
            $transfer=Transfer::create(['operation_id'=>$op->id,'status'=>'pending']);
            $required=[];
            foreach ($input['lines'] as $line) {
                $this->location((int)$line['source_location_id']); $this->location((int)$line['destination_location_id']);
                if ($line['source_location_id']==$line['destination_location_id']) $this->fail('Choose a different destination.');
                if (Quantity::units($line['quantity'])<=0) $this->fail('Transfer quantities must be positive.');
                $stockItem=Item::findOrFail($line['item_id']);$units=Quantity::units($line['quantity']);
                if($stockItem->source_type==='ups' && $units!==10000)$this->fail('Serialized units must transfer whole.');
                $key=$line['item_id'].':'.$line['source_location_id'];$required[$key]=($required[$key]??0)+$units;
                if(Quantity::units($this->balance($stockItem->id,(int)$line['source_location_id'])->quantity)<$required[$key])$this->fail('Insufficient stock for the requested transfer lines.');
                $transfer->lines()->create($line);
            }
            return ['transfer_id'=>$transfer->id];
        });
    }
    public function dispatch(array $input): Operation
    {
        return $this->operation('transfer-dispatch',$input,function ($op) use ($input) {
            $transfer=Transfer::lockForUpdate()->findOrFail($input['transfer_id']);
            if(!$op->reference)$op->update(['reference'=>'transfer:'.$transfer->id]);
            if ($transfer->status!=='pending') $this->fail('This transfer has already been dispatched.');
            $holding=(int)DB::table('warehouse_state')->where('key','transfer_location')->value('value');
            foreach ($transfer->lines()->with('item')->orderBy('item_id')->get() as $line) {
                $this->location($line->source_location_id); $this->location($line->destination_location_id);
                $this->move($op,$line->item,$line->source_location_id,$holding,Quantity::units($line->quantity));
            }
            $transfer->update(['status'=>'in_transit']);
            return ['transfer_id'=>$transfer->id];
        });
    }
    public function receiveTransfer(array $input): Operation
    {
        return $this->operation('transfer-receipt',$input,function ($op) use ($input) {
            $transfer=Transfer::lockForUpdate()->findOrFail($input['transfer_id']);
            if(!$op->reference)$op->update(['reference'=>'transfer:'.$transfer->id]);
            if (!in_array($transfer->status,['in_transit','partially_received'],true)) $this->fail('Transfer is not awaiting receipt.');
            $holding=(int)DB::table('warehouse_state')->where('key','transfer_location')->value('value');
            $receivedAny=false;
            foreach ($input['lines'] as $receipt) {
                $line=$transfer->lines()->with('item')->lockForUpdate()->findOrFail($receipt['id']);
                $qty=Quantity::units($receipt['quantity']);
                if ($qty===0) continue;
                $receivedAny=true;
                if ($qty<0 || $qty>Quantity::units($line->quantity)-Quantity::units($line->received)) $this->fail('Receipt exceeds the quantity still in transit.');
                $this->location($line->destination_location_id);
                $this->move($op,$line->item,$holding,$line->destination_location_id,$qty);
                $line->update(['received'=>Quantity::text(Quantity::units($line->received)+$qty)]);
            }
            if(!$receivedAny) $this->fail('Enter at least one positive receipt quantity.');
            $complete=!$transfer->lines()->whereColumn('received','<','quantity')->exists();
            $transfer->update(['status'=>$complete?'completed':'partially_received']);
            return ['transfer_id'=>$transfer->id];
        });
    }
    public function cancelTransfer(array $input): Operation
    {
        return $this->operation('transfer-cancel',$input,function($op)use($input){
            $transfer=Transfer::lockForUpdate()->findOrFail($input['transfer_id']);
            if(!$op->reference)$op->update(['reference'=>'transfer:'.$transfer->id]);
            if(!in_array($transfer->status,['pending','in_transit','partially_received'],true))$this->fail('This transfer cannot be cancelled.');
            if($transfer->status!=='pending')foreach($transfer->lines()->with('item')->orderBy('item_id')->get() as $line){
                $left=Quantity::units($line->quantity)-Quantity::units($line->received);
                if($left>0){$this->location($line->source_location_id);$holding=(int)DB::table('warehouse_state')->where('key','transfer_location')->value('value');$this->move($op,$line->item,$holding,$line->source_location_id,$left);}
            }
            $transfer->update(['status'=>'cancelled']);return ['transfer_id'=>$transfer->id,'note'=>'Unreceived quantities returned to source; already received quantities remain at destination.'];
        });
    }

    public function receiveTransit(array $input): Operation
    {
        return $this->operation('transit-receipt',$input,function($op) use($input) {
            $type=$input['document_type'];
            if(!in_array($type,['product','raw'],true)) $this->fail('Invalid transit source.');
            $class=$type==='product'?\App\Models\ProductInventory::class:\App\Models\RawMaterialInventory::class;
            $doc=$class::whereKey($input['document_id'])->lockForUpdate()->firstOrFail();
            if(!$op->reference)$op->update(['reference'=>$doc->getTable().':'.$doc->id]);
            if((int)$doc->status!==0 || !in_array((int)$doc->transit_id,[2,3],true)) $this->fail('This shipment is not awaiting receipt.');
            $already=$type==='product'?Quantity::units($doc->received_stock):Quantity::units(DB::table('warehouse_transit_receipts')->where('document_type',$type)->where('document_id',$doc->id)->sum('quantity'));
            $remaining=Quantity::units($doc->stock_in)-$already;
            $qty=Quantity::units($input['quantity']);
            if($qty<=0 || $qty>$remaining) $this->fail('Receipt exceeds the remaining shipment quantity.');
            if($type==='product' && $qty%10000!==0) $this->fail('The existing product transit schema requires whole-unit receipts. Use direct inbound for decimal purchases.');
            $source=$type==='product'?\App\Models\Products::findOrFail($doc->product_id):\App\Models\RawMaterial::findOrFail($doc->raw_material_id);
            $item=$this->itemFor($source); $loc=$this->location((int)$input['location_id']);
            if(isset($input['warehouse_id']) && (int)$input['warehouse_id']!==$loc->warehouse_id) $this->fail('Receipt location is outside the selected warehouse.');
            $this->move($op,$item,null,$loc->id,$qty); $this->sync($item,$qty,false);
            if($type==='product') {
                $column=(int)$doc->transit_id===2?'air_transit':'sea_transit';
                DB::table($source->getTable())->where('id',$source->id)->update([$column=>Quantity::text(Quantity::units($source->$column)-$qty)]);
                $doc->update(['received_stock'=>($already+$qty)/10000,'remaining_stock'=>($remaining-$qty)/10000,'status'=>$qty===$remaining?1:0,'received_by'=>auth()->id()]);
            } else {
                DB::table($source->getTable())->where('id',$source->id)->update(['transit_stock'=>Quantity::text(Quantity::units($source->transit_stock)-$qty)]);
                if($qty===$remaining) $doc->update(['status'=>1,'received_by'=>auth()->id()]);
            }
            DB::table('warehouse_transit_receipts')->insert(['operation_id'=>$op->id,'document_type'=>$type,'document_id'=>$doc->id,'quantity'=>Quantity::text($qty),'created_at'=>now(),'updated_at'=>now()]);
            return ['document_type'=>$type,'document_id'=>$doc->id];
        });
    }

    public function assignUnassignedProduct(array $input): Operation
    {
        return $this->operation('legacy-reconciliation',$input,function($op) use($input) {
            $key='unassigned:product:'.$input['product_id'];
            $state=DB::table('warehouse_state')->where('key',$key)->lockForUpdate()->first();
            if(!$state) $this->fail('This product is not awaiting warehouse assignment.');
            $product=\App\Models\Products::whereKey($input['product_id'])->lockForUpdate()->firstOrFail();
            $before=Quantity::units($product->total_qty);
            if($before!==Quantity::units($state->value) || $before>=0) $this->fail('The excluded quantity changed. Reconcile it before assignment.');
            $quantity=Quantity::units($input['quantity']);
            if($quantity<0) $this->fail('Enter the actual non-negative physical stock.');
            $location=$this->location((int)$input['location_id']);
            if((int)$location->warehouse_id!==(int)$input['warehouse_id']) $this->fail('Location does not belong to the selected warehouse.');
            if(Item::where('source_type','product')->where('source_id',$product->id)->exists()) $this->fail('This product already has warehouse stock.');
            $item=Item::create(['source_type'=>'product','source_id'=>$product->id]);
            $this->balance($item->id,$location->id);
            if($quantity>0) $this->move($op,$item,null,$location->id,$quantity);
            DB::table($product->getTable())->where('id',$product->id)->update([
                'total_qty'=>Quantity::text($quantity),
                'temp_qty'=>Quantity::text(Quantity::units($product->temp_qty)+$quantity-$before),
                'updated_at'=>now(),
            ]);
            $this->verifyLegacy($product);
            DB::table('warehouse_state')->where('key',$key)->delete();
            return ['product_id'=>$product->id,'previous_quantity'=>Quantity::text($before),'corrected_quantity'=>Quantity::text($quantity),'warehouse_id'=>$location->warehouse_id,'location_id'=>$location->id];
        });
    }

    public function verifyLegacy($source): void
    {
        $type=array_search(get_class($source),array_map(fn($d)=>$d[0],config('warehouses.sources')),true);
        if($type===false)return;$field=config('warehouses.sources.'.$type)[1];if(!$field)return;
        if($type==='product' && $source->type!=='Inventory' && Quantity::units($source->$field)===0 && !Item::where('source_type',$type)->where('source_id',$source->id)->exists())return;
        $item=Item::where('source_type',$type)->where('source_id',$source->id)->firstOrFail();
        $stored=DB::table($source->getTable())->where('id',$source->id)->value($field);
        if(Quantity::units($stored)!==Quantity::units($item->balances()->sum('quantity'))) $this->fail('The legacy stock column cannot represent this quantity exactly; the transaction was rolled back.');
    }

    /** Legacy Eloquent writes are mirrored in the same enclosing transaction. */
    public function legacy($source, bool $created=false): void
    {
        $type=array_search(get_class($source),array_map(fn($d)=>$d[0],config('warehouses.sources')),true);
        if ($type===false) return;
        if ($source instanceof \App\Models\Products && $source->type !== 'Inventory' && Quantity::units($source->total_qty)===0 && ($created || Quantity::units($source->getRawOriginal('total_qty'))===0)) return;
        $field=config('warehouses.sources.'.$type)[1];
        if (!$created && (!$field || !$source->isDirty($field))) return;
        if (!$this->inTransaction()) $this->fail('Stock updates must use the warehouse transaction service.');
        $old=$created?0:Quantity::units($source->getRawOriginal($field));
        $new=$field?Quantity::units($source->$field):10000;
        $delta=$new-$old;
        $item=$this->itemFor($source);
        if (!$created) {
            $stored=DB::table($source->getTable())->where('id',$source->id)->lockForUpdate()->value($field);
            if (Quantity::units($stored)!==$old) $this->fail('Stock changed concurrently. Refresh and retry.');
        }
        if (Quantity::units($item->balances()->sum('quantity'))!==$old) $this->fail('Warehouse and legacy totals differ; reconcile before posting.');
        if (!$delta) { if($created){$location=(int)(request()->input('destination_location_id') ?: DB::table('warehouse_state')->where('key','default_location')->value('value'));$this->location($location);$this->balance($item->id,$location);} return; }
        $r=request();
        $op=Operation::create(['request_key'=>(string)Str::uuid(),'input_hash'=>hash('sha256',(string)Str::uuid()),'type'=>'legacy:'.($r->route()?$r->route()->getActionMethod():'stock-update'),'source'=>'legacy','reference'=>$r->input('reference') ?? ($r->filled('invoice_number') ? 'invoice:'.$r->invoice_number : ($r->filled('serial_number_reference') ? 'UPS:'.$r->serial_number_reference : ($r->attributes->get('warehouse.document') ?? $r->route()?->getName()))),'date'=>today()->toDateString(),'user_id'=>auth()->id(),'notes'=>'Legacy workflow synchronized using '.config('warehouses.legacy_allocation')]);
        if ($delta>0) {
            $id=(int)($r->input('destination_location_id') ?: DB::table('warehouse_state')->where('key','default_location')->value('value'));
            $loc=$this->location($id);
            if ($r->filled('destination_warehouse_id') && (int)$r->destination_warehouse_id!==$loc->warehouse_id) $this->fail('Destination location belongs to another warehouse.');
            $this->move($op,$item,null,$loc->id,$delta);
        } else {
            $query=$item->balances()->where('quantity','>',0)->whereHas('location',function($q){$q->where('active',1)->where('kind','physical')->whereHas('warehouse',fn($w)=>$w->where('active',1));});
            if ($r->filled('source_warehouse_ids')) $query->whereHas('location',fn($q)=>$q->whereIn('warehouse_id',(array)$r->source_warehouse_ids));
            if ($r->filled('source_location_id')) $query->where('location_id',$r->source_location_id);
            $left=-$delta;
            foreach ($query->orderBy('location_id')->lockForUpdate()->get() as $balance) {
                $take=min($left,Quantity::units($balance->quantity));
                if ($take>0) $this->move($op,$item,$balance->location_id,null,$take);
                $left-=$take; if (!$left) break;
            }
            if ($left) $this->fail('Insufficient stock in the selected source warehouses/locations.');
        }
    }
}
