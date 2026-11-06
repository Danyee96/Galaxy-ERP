<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;
use App\Models\Warehouse\{Warehouse,Location,Item,Operation};
use App\Services\Warehouse\{Inventory,Quantity};
class WarehouseBackfill extends Command
{
    protected $signature='warehouses:backfill {--apply : Explicitly apply the deterministic mapping; default is read-only} {--skip-negative-products : Leave negative products unassigned for later correction} {--warehouse-name=Legacy warehouse} {--row=Unassigned} {--column=Unassigned}';
    protected $description='Validate legacy stock, map it once to LEGACY/Unassigned, and verify every aggregate';
    public function handle(Inventory $inventory)
    {
        $sources=[]; $excluded=[]; $failures=[]; $failureCount=0;
        foreach(config('warehouses.sources') as $type=>[$class,$field]) {
            $model=new $class;
            if (!Schema::hasTable($model->getTable())) continue;
            foreach($class::orderBy('id')->cursor() as $source) {
                try { $amount=$field?Quantity::units($source->$field):10000; } catch (\Illuminate\Validation\ValidationException $error) { $failureCount++; if(count($failures)<20)$failures[]='Invalid physical quantity: '.$type.' #'.$source->id; continue; }
                if($type==='product' && $source->type!=='Inventory' && $amount===0) continue;
                if($amount<0 && $type==='product' && $this->option('skip-negative-products')) { $excluded[]=[$source->id,$amount]; continue; }
                if($amount<0) { $failureCount++; if(count($failures)<20)$failures[]='Negative legacy stock: '.$type.' #'.$source->id; continue; }
                $sources[]=[$type,$source->id,$amount];
            }
        }
        if($failureCount) { foreach($failures as $message)$this->error($message); $this->error($failureCount.' stock exceptions found (first 20 shown). No stock was mapped.'); return 1; }
        if(count($excluded)) $this->warn(count($excluded).' negative products will remain unassigned; their quantities will not change.');
        $this->info(count($sources).' legacy source records validated. Existing transit/projected quantities are NOT physical stock.');
        if(!$this->option('apply')) { $this->info('Dry run only. Valid stock will use the selected warehouse name, row and column; no stock is changed.'); return 0; }
        DB::transaction(function() use($inventory,$sources,$excluded){
            DB::table('warehouse_state')->insertOrIgnore(['key'=>'mutex','value'=>'1']);
            DB::table('warehouse_state')->where('key','mutex')->lockForUpdate()->first();
            $ready=$inventory->ready();
            if(!$ready) {
                $default=Warehouse::firstOrCreate(['code'=>'LEGACY'],['name'=>$this->option('warehouse-name'),'description'=>'Deterministic mapping for previously unassigned physical stock.']);
                $location=Location::firstOrCreate(['warehouse_id'=>$default->id,'rack'=>'','row'=>$this->option('row'),'column'=>$this->option('column')]);
                $system=Warehouse::firstOrCreate(['code'=>'INTERNAL-TRANSIT'],['name'=>'Internal transfers','description'=>'System holding warehouse; internal transfers remain in combined stock.']);
                $holding=Location::firstOrCreate(['warehouse_id'=>$system->id,'rack'=>'','row'=>'In transit','column'=>'Holding'],['kind'=>'transfer']);
                foreach(['default_location'=>$location->id,'transfer_location'=>$holding->id] as $key=>$value) DB::table('warehouse_state')->updateOrInsert(['key'=>$key],['value'=>(string)$value]);
            }
            foreach($excluded as [$id,$amount]) {
                $product=\App\Models\Products::whereKey($id)->lockForUpdate()->firstOrFail();
                if(Quantity::units($product->total_qty)!==$amount) throw new \RuntimeException('Excluded stock changed during preflight. Retry.');
                if(Item::where('source_type','product')->where('source_id',$id)->exists()) throw new \RuntimeException('Cannot exclude a product already tracked in warehouses. Reconcile its existing ledger.');
                $key='unassigned:product:'.$id;
                if(!DB::table('warehouse_state')->where('key',$key)->exists()) {
                    DB::table('warehouse_state')->insert(['key'=>$key,'value'=>Quantity::text($amount)]);
                    Operation::create(['request_key'=>(string)Str::uuid(),'input_hash'=>hash('sha256',$key.':'.$amount),'type'=>'legacy-unassigned','source'=>'opening','reference'=>'legacy:product:'.$id,'date'=>today()->toDateString(),'notes'=>'Negative legacy stock excluded from warehouse opening; no stock changed.','result'=>['product_id'=>$id,'legacy_quantity'=>Quantity::text($amount)]]);
                }
            }
            foreach($sources as [$type,$id,$amount]) {
                [$class,$field]=config('warehouses.sources.'.$type);
                $source=$class::whereKey($id)->lockForUpdate()->firstOrFail();
                $live=$field?Quantity::units($source->$field):10000;
                if($live!==$amount) throw new \RuntimeException('Legacy stock changed during preflight. Retry in a maintenance window.');
                $item=Item::firstOrCreate(['source_type'=>$type,'source_id'=>$id]);
                if(!$ready) $inventory->balance($item->id,$location->id);
                if(!$ready && !$item->movements()->exists() && $amount>0) {
                    if(Quantity::units($item->balances()->sum('quantity'))!==0) throw new \RuntimeException('Untracked existing warehouse stock; refusing to duplicate.');
                    $op=Operation::create(['request_key'=>(string)Str::uuid(),'input_hash'=>hash('sha256',$type.':'.$id.':'.$amount),'type'=>'opening','source'=>'opening','reference'=>'legacy:'.$type.':'.$id,'date'=>today()->toDateString(),'notes'=>'Mapped without changing the legacy physical aggregate.']);
                    $inventory->move($op,$item,null,$location->id,$amount);
                }
                if($ready && !$field) { $amount=Quantity::units($item->balances()->sum('quantity')); if(!in_array($amount,[0,10000],true))throw new \RuntimeException('Serialized unit balance is invalid.'); }
                if(Quantity::units($item->balances()->sum('quantity'))!==$amount) throw new \RuntimeException('Reconciliation mismatch: '.$type.' #'.$id.'. Entire backfill rolled back.');
            }
            DB::table('warehouse_state')->updateOrInsert(['key'=>'ready'],['value'=>'1']);
        });
        $inventory->ready();
        $this->info('Per-item physical totals reconciled. Legacy aggregates were not modified. Re-running is safe.');
        return 0;
    }
}
