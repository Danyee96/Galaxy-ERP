<?php
namespace App\Http\Controllers\Warehouse;
use App\Http\Controllers\Controller;
use App\Models\Warehouse\{Warehouse,Location,Item,Balance,Movement,Transfer};
use App\Services\Warehouse\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Schema};
use Illuminate\Validation\Rule;
class WarehouseController extends Controller
{
    public function __construct() { $this->middleware(function($r,$next){ abort_unless(auth()->check()&&in_array((int)auth()->user()->role_id,[1,3,4,5],true),403); abort_unless(config('warehouses.enabled') && Schema::hasTable('warehouse_state') && app(Inventory::class)->ready(),503,'Warehouse activation requires the additive migration and reconciled backfill.'); return $next($r); }); }
    public function index(Request $r)
    {
        $filters=$r->validate(['warehouse_id'=>'nullable|integer|exists:warehouses,id','location_id'=>'nullable|integer|exists:warehouse_locations,id','search'=>'nullable|string|max:255','rack'=>'nullable|string|max:60','row'=>'nullable|string|max:60','column'=>'nullable|string|max:60','low'=>'nullable|boolean','source_type'=>'nullable|in:product,raw,semi,finished,ups','source_id'=>'nullable|integer|min:1']);
        $query=Balance::with(['item.source','location.warehouse'])->whereHas('location',function($q)use($filters){
            foreach(['warehouse_id','rack','row','column'] as $key) if(!empty($filters[$key])) $q->where($key,$filters[$key]);
        });
        $query->where(fn($q)=>$q->whereHas('location',fn($l)=>$l->where('kind','physical'))->orWhere('quantity','>',0));
        if(!empty($filters['source_type'])) $query->whereHas('item',fn($q)=>$q->where('source_type',$filters['source_type']));
        if(!empty($filters['source_id'])) $query->whereHas('item',fn($q)=>$q->where('source_id',$filters['source_id']));
        if(!empty($filters['location_id'])) $query->where('location_id',$filters['location_id']);
        if(!empty($filters['search'])) {
            $term=$filters['search'];
            $query->whereHas('item',function($q)use($term){$q->whereHasMorph('source',array_map(fn($d)=>$d[0],config('warehouses.sources')),function($source,$class)use($term){$source->where($class===\App\Models\FinishedModels::class?'serial_number':'name','like','%'.$term.'%'); if($class===\App\Models\Products::class)$source->orWhere('sku','like','%'.$term.'%');});});
        }
        if($r->boolean('low')) $query->whereHas('location',fn($q)=>$q->where('kind','physical'))->where(function($q){
            $q->whereHas('location',fn($l)=>$l->whereColumn('warehouse_balances.quantity','<=','warehouse_locations.reorder_level'))
                ->orWhereHas('location',fn($l)=>$l->whereRaw('(SELECT COALESCE(SUM(b2.quantity),0) FROM warehouse_balances b2 JOIN warehouse_locations l2 ON l2.id=b2.location_id WHERE b2.item_id=warehouse_balances.item_id AND l2.warehouse_id=warehouse_locations.warehouse_id) <= COALESCE((SELECT r.quantity FROM warehouse_reorder_levels r WHERE r.item_id=warehouse_balances.item_id AND r.warehouse_id=warehouse_locations.warehouse_id),0)'));
        });
        if($r->boolean('csv')) return response()->streamDownload(function()use($query){
            $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
            $write=function($row)use($out){fputcsv($out,array_map(fn($v)=>preg_match('/^[\x00-\x20]*[=+@\-]/',(string)$v)?"'".$v:$v,$row));};
            $write(['Product','SKU','Source','Warehouse','Rack','Row','Column','Quantity','Location type']);
            $query->orderBy('id')->chunkById(200,function($rows)use($write){foreach($rows as $b)$write([$b->item->name,optional($b->item->source)->sku,$b->item->source_type,$b->location->warehouse->name,$b->location->rack,$b->location->row,$b->location->column,$b->quantity,$b->location->kind]);});fclose($out);
        },'warehouse-stock.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
        if($r->boolean('print')) return view('warehouse.print',['kind'=>'stock','rows'=>$query->reorder('id')->lazyById(200)->getIterator()]);
        $balances=$query->orderBy('item_id')->orderBy('location_id')->paginate(30)->withQueryString();
        $warehouses=Warehouse::withCount('locations')->orderBy('name')->get();
        $overview=DB::table('warehouse_balances as b')->join('warehouse_locations as l','l.id','=','b.location_id')->groupBy('l.warehouse_id')->selectRaw('l.warehouse_id, COUNT(DISTINCT b.item_id) as skus, SUM(b.quantity) as quantity')->get()->keyBy('warehouse_id');
        $warehouseSums=DB::table('warehouse_balances as b')->join('warehouse_locations as l','l.id','=','b.location_id')->where('l.kind','physical')->groupBy('b.item_id','l.warehouse_id')->selectRaw('b.item_id,l.warehouse_id,SUM(b.quantity) as quantity');
        $candidates=DB::table('warehouse_reorder_levels')->select('item_id','warehouse_id')->union(DB::table('warehouse_balances as b')->join('warehouse_locations as l','l.id','=','b.location_id')->where('l.kind','physical')->select('b.item_id','l.warehouse_id'));
        $lowCounts=DB::query()->fromSub($candidates,'c')->leftJoinSub($warehouseSums,'s',function($j){$j->on('s.item_id','=','c.item_id')->on('s.warehouse_id','=','c.warehouse_id');})->leftJoin('warehouse_reorder_levels as r',function($j){$j->on('r.item_id','=','c.item_id')->on('r.warehouse_id','=','c.warehouse_id');})->whereRaw('COALESCE(s.quantity,0) <= COALESCE(r.quantity,0)')->groupBy('c.warehouse_id')->selectRaw('c.warehouse_id,COUNT(*) as low_count')->pluck('low_count','c.warehouse_id');
        $activity=['in'=>[],'out'=>[]];
        foreach(['in'=>'destination_location_id','out'=>'source_location_id'] as $side=>$column) $activity[$side]=DB::table('warehouse_movements as m')->join('warehouse_locations as l','l.id','=','m.'.$column)->groupBy('l.warehouse_id')->selectRaw('l.warehouse_id, COUNT(*) as movements')->pluck('movements','l.warehouse_id')->all();
        $locations=Location::with('warehouse')->orderBy('warehouse_id')->orderBy('rack')->get();
        $items=Item::with('source')->orderBy('id')->get();
        $shipments=[];
        if(Schema::hasTable('product_inventories'))foreach(DB::table('product_inventories as d')->join('products as p','p.id','=','d.product_id')->where('d.status',0)->whereIn('d.transit_id',[2,3])->get(['d.id','d.stock_in','d.received_stock','p.name']) as $doc){$remaining=\App\Services\Warehouse\Quantity::units($doc->stock_in)-\App\Services\Warehouse\Quantity::units($doc->received_stock);if($remaining>0)$shipments[]=['type'=>'product','id'=>$doc->id,'name'=>$doc->name,'remaining'=>\App\Services\Warehouse\Quantity::text($remaining)];}
        if(Schema::hasTable('raw_material_inventories')) {
            $receipts=DB::table('warehouse_transit_receipts')->where('document_type','raw')->groupBy('document_id')->selectRaw('document_id,SUM(quantity) as received');
            $docs=DB::table('raw_material_inventories as d')->join('raw_materials as r','r.id','=','d.raw_material_id')->leftJoinSub($receipts,'received',fn($j)=>$j->on('received.document_id','=','d.id'))->where('d.status',0)->whereIn('d.transit_id',[2,3])->get(['d.id','d.stock_in','r.name','received.received']);
            foreach($docs as $doc){$remaining=\App\Services\Warehouse\Quantity::units($doc->stock_in)-\App\Services\Warehouse\Quantity::units($doc->received);if($remaining>0)$shipments[]=['type'=>'raw','id'=>$doc->id,'name'=>$doc->name,'remaining'=>\App\Services\Warehouse\Quantity::text($remaining)];}
        }

        $combined=Balance::whereIn('item_id',$balances->pluck('item_id'))->groupBy('item_id')->selectRaw('item_id,SUM(quantity) as total')->pluck('total','item_id');
        $breakdown=DB::table('warehouse_balances as b')->join('warehouse_locations as l','l.id','=','b.location_id')->join('warehouses as w','w.id','=','l.warehouse_id')->whereIn('b.item_id',$balances->pluck('item_id'))->groupBy('b.item_id','w.id','w.name')->selectRaw('b.item_id,w.id,w.name,SUM(b.quantity) as quantity')->get()->groupBy('item_id');
        $levels=DB::table('warehouse_reorder_levels')->whereIn('item_id',$balances->pluck('item_id'))->get()->keyBy(fn($r)=>$r->item_id.':'.$r->warehouse_id);
        $quickLocations=$locations->where('active',1)->where('kind','physical')->map(fn($l)=>['id'=>$l->id,'warehouse_id'=>$l->warehouse_id,'label'=>$l->label,'row'=>$l->row,'column'=>$l->column,'rack'=>$l->rack])->values();
        $transferStocks=Balance::with(['item.source','location.warehouse'])->where('quantity','>',0)->whereHas('location',fn($q)=>$q->where('active',1)->where('kind','physical')->whereHas('warehouse',fn($w)=>$w->where('active',1)))->orderBy('item_id')->get();
        return view('warehouse.index',compact('transferStocks','quickLocations','balances','warehouses','locations','items','overview','combined','activity','breakdown','lowCounts','levels','shipments'));
    }
    public function saveWarehouse(Request $r,Inventory $inventory)
    {
        abort_unless((int)$r->user()->role_id===1,403);
        $data=$r->validate(['id'=>'nullable|integer|exists:warehouses,id','code'=>['required','string','max:40',Rule::unique('warehouses','code')->ignore($r->id)],'name'=>'required|string|max:255','address'=>'nullable|string|max:2000','description'=>'nullable|string|max:2000','active'=>'required|boolean','create_default_location'=>'nullable|boolean','initial_row'=>'nullable|string|max:60','initial_column'=>'nullable|string|max:60']);
        $inventory->atomic(function()use($data,$inventory){
            $w=isset($data['id'])?Warehouse::lockForUpdate()->findOrFail($data['id']):new Warehouse;
            if($w->exists && in_array($w->code,['LEGACY','INTERNAL-TRANSIT'],true) && ($data['code']!==$w->code || !$data['active'])) $inventory->fail('Default/system warehouses cannot change their code or be deactivated. Names may be edited.');
            $new=!$w->exists;
            $w->fill(collect($data)->except(['id','create_default_location','initial_row','initial_column'])->all())->save();
            if($w->code!=='INTERNAL-TRANSIT') Location::firstOrCreate(['warehouse_id'=>$w->id,'rack'=>'','row'=>'1','column'=>'1'],['active'=>1]);
            if($new && !empty($data['create_default_location']) && (($data['initial_row'] ?? '1')!=='1' || ($data['initial_column'] ?? '1')!=='1')) Location::firstOrCreate(['warehouse_id'=>$w->id,'rack'=>'','row'=>$data['initial_row'] ?? '1','column'=>$data['initial_column'] ?? '1']);
        }); return back()->with('message','Warehouse saved.');
    }
    public function deleteWarehouse(Request $r,Inventory $inventory)
    {
        abort_unless((int)$r->user()->role_id===1,403);
        $r->validate(['id'=>'required|integer|exists:warehouses,id']);
        $inventory->atomic(function()use($r,$inventory){
            $w=Warehouse::lockForUpdate()->findOrFail($r->id);
            if(in_array($w->code,['LEGACY','INTERNAL-TRANSIT'],true))$inventory->fail('System/default warehouses cannot be deleted.');
            $ids=$w->locations()->pluck('id');
            if(DB::table('warehouse_reorder_levels')->where('warehouse_id',$w->id)->exists() || Balance::whereIn('location_id',$ids)->exists() || Movement::whereIn('source_location_id',$ids)->orWhereIn('destination_location_id',$ids)->exists() || \App\Models\Warehouse\TransferLine::whereIn('source_location_id',$ids)->orWhereIn('destination_location_id',$ids)->exists())$inventory->fail('Warehouse stock or history exists. Deactivate it instead.');
            $w->locations()->delete();$w->delete();
        });return back()->with('message','Empty warehouse deleted.');
    }
    public function saveLocation(Request $r,Inventory $inventory)
    {
        abort_unless(in_array((int)$r->user()->role_id,[1,4,5],true),403);
        $data=$r->validate(['id'=>'nullable|integer|exists:warehouse_locations,id','warehouse_id'=>'required|integer|exists:warehouses,id','rack'=>'nullable|string|max:60','row'=>'required|string|max:60','column'=>'required|string|max:60','active'=>'required|boolean','reorder_level'=>'required|numeric|min:0|max:9999999999']);
        $inventory->atomic(function()use($data,$inventory){
            $warehouse=Warehouse::findOrFail($data['warehouse_id']);
            if(!$warehouse->active || $warehouse->code==='INTERNAL-TRANSIT')$inventory->fail('Select an active physical warehouse.');
            $data['rack']=$data['rack']??'';
            $loc=isset($data['id'])?Location::lockForUpdate()->findOrFail($data['id']):new Location;
            if($loc->exists && ((int)$loc->warehouse_id!==(int)$data['warehouse_id'] || $loc->kind!=='physical')) $inventory->fail('An existing location cannot be moved to another warehouse or change system status.');
            if($loc->exists && $loc->rack==='' && $loc->row==='1' && $loc->column==='1' && (!$data['active'] || $data['rack']!=='' || $data['row']!=='1' || $data['column']!=='1')) $inventory->fail('Row 1 / Column 1 is required for every warehouse. Add another location instead of changing or deactivating it.');
            if($loc->exists && (int)DB::table('warehouse_state')->where('key','default_location')->value('value')===$loc->id && !$data['active'])$inventory->fail('The default legacy receiving location must remain active.');
            if(Location::where('warehouse_id',$data['warehouse_id'])->where('rack',$data['rack'])->where('row',$data['row'])->where('column',$data['column'])->when($loc->exists,fn($q)=>$q->where('id','!=',$loc->id))->exists())$inventory->fail('This rack/row/column already exists.');
            $loc->fill(collect($data)->except('id')->all())->save();
        });return back()->with('message','Location saved.');
    }
    public function reorder(Request $r,Inventory $inventory)
    {
        abort_unless(in_array((int)$r->user()->role_id,[1,4,5],true),403);
        $data=$r->validate(['item_id'=>'required|integer|exists:warehouse_items,id','warehouse_id'=>'required|integer|exists:warehouses,id','quantity'=>'required|regex:/^\d{1,10}(\.\d{1,4})?$/']);
        $inventory->atomic(fn()=>DB::table('warehouse_reorder_levels')->updateOrInsert(['item_id'=>$data['item_id'],'warehouse_id'=>$data['warehouse_id']],['quantity'=>$data['quantity']]));
        return back()->with('message','Warehouse low-stock threshold saved.');
    }
    public function unassigned()
    {
        return view('warehouse.unassigned',['warehouses'=>Warehouse::where('active',1)->where('code','!=','INTERNAL-TRANSIT')->get(),'locations'=>Location::with('warehouse')->where('active',1)->where('kind','physical')->whereHas('warehouse',fn($q)=>$q->where('active',1))->get()]);
    }
    public function assignUnassigned(Request $r,Inventory $inventory)
    {
        abort_unless(in_array((int)auth()->user()->role_id,[1,4,5],true),403);
        $input=$r->validate(['request_key'=>'required|uuid','product_id'=>'required|integer|exists:products,id','warehouse_id'=>'required|integer|exists:warehouses,id','location_id'=>'required|integer|exists:warehouse_locations,id','quantity'=>'required|regex:/^\d{1,10}(\.\d{1,4})?$/','reference'=>'required|string|max:255','notes'=>'nullable|string|max:4000']);
        $inventory->assignUnassignedProduct($input);
        return back()->with('message','Stock corrected and assigned to the selected warehouse.');
    }
    public function post(Request $r,Inventory $inventory)
    {
        $input=$r->validate(['request_key'=>'required|uuid','source'=>'nullable|in:purchase,production,sale,return,other,adjustment','type'=>'required|in:inbound,outbound,return,adjustment-in,adjustment-out','item_id'=>'required|integer|exists:warehouse_items,id','warehouse_id'=>'required|integer|exists:warehouses,id','location_id'=>'required|integer|exists:warehouse_locations,id','quantity'=>'required|regex:/^\d{1,10}(\.\d{1,4})?$/','reference'=>'required|string|max:255','date'=>'required|date_format:Y-m-d','notes'=>'nullable|string|max:4000']);
        $type=$input['type'];unset($input['type']);$inventory->post($type,$input); return back()->with('message','Stock movement posted.');
    }
    public function quickTransfer(Request $r,Inventory $inventory)
    {
        $input=$r->validate(['request_key'=>'required|uuid','balance_id'=>'required|integer|exists:warehouse_balances,id','destination_warehouse_id'=>'required|integer|exists:warehouses,id','destination_location_id'=>'required|integer|exists:warehouse_locations,id','mode'=>'required|in:all,partial','quantity'=>'nullable|required_if:mode,partial|regex:/^\d{1,10}(\.\d{1,4})?$/','reference'=>'nullable|string|max:255']);
        $op=$inventory->quickTransfer($input);
        return back()->with('message','Stock transferred successfully. Transfer #'.$op->result['transfer_id']);
    }
    public function transfer(Request $r,Inventory $inventory)
    {
        $data=$r->validate(['request_key'=>'required|uuid','reference'=>'required|string|max:255','date'=>'required|date_format:Y-m-d','notes'=>'nullable|string|max:4000','lines'=>'required|array|min:1|max:100','lines.*.item_id'=>'required|integer|exists:warehouse_items,id','lines.*.source_location_id'=>'required|integer|exists:warehouse_locations,id','lines.*.destination_location_id'=>'required|integer|exists:warehouse_locations,id','lines.*.quantity'=>'required|regex:/^\d{1,10}(\.\d{1,4})?$/']);
        $inventory->transfer($data);return redirect()->route('warehouses.transfers')->with('message','Pending transfer created. Dispatch when ready.');
    }
    public function transfers() { return view('warehouse.transfers',['transfers'=>Transfer::with(['operation','lines.item.source','lines.source.warehouse','lines.destination.warehouse'])->latest()->paginate(20)]); }
    public function processTransfer(Request $r,Inventory $inventory)
    {
        $input=$r->validate(['request_key'=>'required|uuid','transfer_id'=>'required|integer|exists:warehouse_transfers,id','action'=>'required|in:dispatch,receive,cancel','lines'=>'required_if:action,receive|array|min:1','lines.*.id'=>'required|integer|distinct','lines.*.quantity'=>'required|regex:/^\d{1,10}(\.\d{1,4})?$/']);
        $action=$input['action'];unset($input['action']);if($action==='dispatch')$inventory->dispatch($input);elseif($action==='cancel')$inventory->cancelTransfer($input);else $inventory->receiveTransfer($input);
        return back()->with('message','Transfer updated.');
    }
    public function receipt(Request $r,Inventory $inventory)
    {
        $input=$r->validate(['request_key'=>'required|uuid','document_type'=>'required|in:product,raw','document_id'=>'required|integer|min:1','warehouse_id'=>'required|integer|exists:warehouses,id','location_id'=>'required|integer|exists:warehouse_locations,id','quantity'=>'required|regex:/^\d{1,10}(\.\d{1,4})?$/','reference'=>'required|string|max:255','date'=>'required|date_format:Y-m-d','notes'=>'nullable|string|max:4000']);
        $inventory->receiveTransit($input);return back()->with('message','Transit stock received.');
    }
    public function history(Request $r)
    {
        $r->validate(['item_id'=>'nullable|integer','warehouse_id'=>'nullable|integer','type'=>'nullable|string|max:60','from'=>'nullable|date_format:Y-m-d','to'=>'nullable|date_format:Y-m-d'.($r->filled('from')?'|after_or_equal:from':'')]);
        $q=Movement::with(['item.source','source.warehouse','destination.warehouse','operation'])->when($r->item_id,fn($q)=>$q->where('item_id',$r->item_id))->when($r->warehouse_id,fn($q)=>$q->where(function($q)use($r){$q->whereHas('source',fn($l)=>$l->where('warehouse_id',$r->warehouse_id))->orWhereHas('destination',fn($l)=>$l->where('warehouse_id',$r->warehouse_id));}))->latest('id');
        if($r->filled('type'))$q->whereHas('operation',function($op)use($r){if($r->type==='legacy')$op->where('type','like','legacy:%');else $op->where('type',$r->type);});
        if($r->filled('from'))$q->where('created_at','>=',$r->from.' 00:00:00');
        if($r->filled('to'))$q->where('created_at','<',\Carbon\Carbon::parse($r->to)->addDay()->toDateString().' 00:00:00');
        if($r->boolean('csv')) return response()->streamDownload(function()use($q){$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");$write=function($row)use($out){fputcsv($out,array_map(fn($v)=>preg_match('/^[\x00-\x20]*[=+@\-]/',(string)$v)?"'".$v:$v,$row));};$write(['Date','Item','Type','Source / purpose','Quantity','Source','Destination','Source before','Source after','Destination before','Destination after','Reference','User','Notes']);$q->reorder('id')->chunkById(200,function($rows)use($write){foreach($rows as $m)$write([$m->created_at,$m->item->name,$m->operation->type,$m->operation->source,$m->quantity,optional($m->source)->label,optional($m->destination)->label,$m->source_before,$m->source_after,$m->destination_before,$m->destination_after,$m->operation->reference,$m->operation->user_id,$m->operation->notes]);},'id');fclose($out);},'warehouse-movements.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
        if($r->boolean('print')) return view('warehouse.print',['kind'=>'history','rows'=>$q->reorder('id')->lazyById(200)->getIterator()]);
        return view('warehouse.history',['movements'=>$q->paginate(40)->withQueryString(),'warehouses'=>Warehouse::orderBy('name')->get()]);
    }
    public function productionAvailability(Request $r)
    {
        $r->validate(['recipe_type'=>'required|in:ups,remaining,semi,finished','recipe_id'=>'required|integer|min:1','warehouse_ids'=>'nullable|array|max:100','warehouse_ids.*'=>'integer|exists:warehouses,id','location_id'=>'nullable|integer|exists:warehouse_locations,id']);
        $ids=['raw'=>[],'semi'=>[]];
        if($r->recipe_type==='ups')$ids['raw']=\App\Models\ModelRecipe::where('model_id',$r->recipe_id)->pluck('raw_material_id')->all();
        elseif($r->recipe_type==='remaining')$ids['raw']=\App\Models\FinishedModelMaterial::where('finished_model_id',$r->recipe_id)->pluck('raw_material_id')->all();
        elseif($r->recipe_type==='semi')$ids['raw']=\App\Models\SemiRawMaterialRecipe::where('semi_raw_material_id',$r->recipe_id)->pluck('raw_material_id')->all();
        else foreach(\App\Models\FinishedGoodsRecipe::where('finished_good_id',$r->recipe_id)->get() as $line)$ids[$line->material_type==='Raw'?'raw':'semi'][]=$line->material_id;
        $items=Item::with('source')->where(function($q)use($ids){foreach($ids as $type=>$values)$q->orWhere(fn($q)=>$q->where('source_type',$type)->whereIn('source_id',$values));})->get();
        $balances=Balance::with('location.warehouse')->whereIn('item_id',$items->pluck('id'))->whereHas('location',function($q)use($r){$q->where('active',1)->where('kind','physical')->whereHas('warehouse',fn($w)=>$w->where('active',1));if($r->filled('warehouse_ids'))$q->whereIn('warehouse_id',$r->warehouse_ids);})->when($r->location_id,fn($q)=>$q->where('location_id',$r->location_id))->get();
        return response()->json(['materials'=>$items->map(function($item)use($balances){$total=0;$warehouses=[];foreach($balances->where('item_id',$item->id)as$b){$qty=\App\Services\Warehouse\Quantity::units($b->quantity);$total+=$qty;$wid=$b->location->warehouse_id;if(!isset($warehouses[$wid]))$warehouses[$wid]=['name'=>$b->location->warehouse->name,'units'=>0];$warehouses[$wid]['units']+=$qty;}return ['name'=>$item->name,'quantity'=>\App\Services\Warehouse\Quantity::text($total),'warehouses'=>array_map(fn($w)=>['name'=>$w['name'],'quantity'=>\App\Services\Warehouse\Quantity::text($w['units'])],array_values($warehouses))];})]);
    }
    public function availability(Request $r)
    {
        $data=$r->validate(['item_id'=>'required|integer|exists:warehouse_items,id','location_id'=>'required|integer|exists:warehouse_locations,id']);
        $location=app(Inventory::class)->location((int)$data['location_id']);
        return response()->json(['quantity'=>(string)(Balance::where('item_id',$data['item_id'])->where('location_id',$location->id)->value('quantity')??0),'location'=>$location->label]);
    }
    public function context()
    {
        return response()->json(['warehouses'=>Warehouse::where('active',1)->where('code','!=','INTERNAL-TRANSIT')->get(['id','name']), 'locations'=>Location::with('warehouse:id,name')->where('active',1)->where('kind','physical')->whereHas('warehouse',fn($q)=>$q->where('active',1))->get()->map(fn($l)=>['id'=>$l->id,'warehouse_id'=>$l->warehouse_id,'label'=>$l->label]), 'default_location'=>(int)DB::table('warehouse_state')->where('key','default_location')->value('value'), 'actions'=>collect(\Illuminate\Support\Facades\Route::getRoutes())->filter(fn($route)=>in_array($route->getActionMethod(),\App\Http\Middleware\WarehouseTransactions::ACTIONS,true))->map(fn($route)=>'/'.preg_replace('/\{[^}]+\}/','__ID__',$route->uri()))->values()]);
    }
}
