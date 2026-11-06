<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\DB;
use App\Services\Warehouse\Inventory;
class WarehouseTransactions
{
    public const ACTIONS=['productService','saveTransitInventoryNew','markReceivedTransit','markReceivedTransitNew','saveInvoice','saveRawMaterial','saveInventory','saveSemiRawMaterial','saveFinishedGoods','saveSemiRawMaterialProduction','saveFinishedGoodsProduction','saveFinishedGoodsSale','saveTransitInventory','addFinishedUpsModel','addInCompleteUpsModel','markCompleteUpsModel','saveRemainigInventory','addInCompleteUpsModelNew','markCompleteUpsModelNew','saveRemainigInventoryNew'];
    public function handle($request, Closure $next)
    {
        if(!$request->route() || !in_array($request->route()->getActionMethod(),self::ACTIONS,true)) return $next($request);
        // productService is also the read-only product-list action.
        if($request->route()->getActionMethod()==='productService' && !$request->filled('type')) return $next($request);
        if(!config('warehouses.enabled')) { abort_if(app(Inventory::class)->installed(),503,'An active warehouse ledger exists. Re-enable warehouse management before posting stock.'); return $next($request); }
        abort_unless($request->user() && in_array((int)$request->user()->role_id,[1,3,4,5],true),403);
        abort_unless(\Illuminate\Support\Facades\Schema::hasTable('warehouse_state'),503,'Apply the warehouse migration and backfill before activation.');
        if($request->isMethod('GET')) { $token=$request->header('X-CSRF-TOKEN'); abort_unless(is_string($token) && hash_equals($request->session()->token(),$token),419,'Reload and submit the stock operation from the application.'); }
        $request->validate(['warehouse_request_key'=>'required|uuid','source_warehouse_ids'=>'nullable|array|max:100','source_warehouse_ids.*'=>'integer|exists:warehouses,id','source_location_id'=>'nullable|integer|exists:warehouse_locations,id','destination_warehouse_id'=>'nullable|integer|exists:warehouses,id','destination_location_id'=>'nullable|integer|exists:warehouse_locations,id']);
        if($request->route()->getActionMethod()==='saveTransitInventoryNew') $request->validate(['remarks'=>'nullable|string|max:4000','notes'=>'nullable|string|max:4000','new_quantity'=>'required|integer|min:1']);
        $request->validate(['new_quantity'=>'sometimes|numeric|gt:0','material_qty'=>'sometimes|numeric|gt:0','machine_qty'=>'sometimes|numeric|gt:0','sale_quantity'=>'sometimes|numeric|gt:0','initial_qty'=>'nullable|numeric|min:0','qty_old'=>'sometimes|array','qty_old.*'=>'numeric|min:0','raw_qty_old'=>'sometimes|array','raw_qty_old.*'=>'numeric|min:0','semi_qty_old'=>'sometimes|array','semi_qty_old.*'=>'numeric|min:0','quantity'=>'sometimes|array','quantity.*'=>'numeric|gt:0','qty_used'=>'sometimes|array','qty_used.*'=>'numeric|min:0']);
        try { return app(Inventory::class)->atomic(function() use($request,$next){
            $request->attributes->set('warehouse.transaction',true);
            $key=$request->warehouse_request_key;
            $hash=hash('sha256',json_encode([$request->route()->getName(),$request->route()->parameters(),$request->except(['_token','warehouse_request_key'])],JSON_THROW_ON_ERROR));
            $old=DB::table('warehouse_legacy_requests')->where('key',$key)->first();
            if($old) {
                if((int)$old->user_id!==(int)$request->user()->id || $old->input_hash!==$hash) app(Inventory::class)->fail('Request key reused with different stock inputs. Reload and retry.');
                return response($old->body,$old->status,json_decode($old->headers,true));
            }
            if($request->route()->getActionMethod()==='saveInvoice' && \App\Models\Invoice::where('invoice_number',$request->invoice_number)->exists()) app(Inventory::class)->fail('This invoice number has already issued stock.');
            if(in_array($request->route()->getActionMethod(),['saveRemainigInventory','saveRemainigInventoryNew'],true)) {
                $request->validate(['qty_used_ids'=>'required|array','qty_used_ids.*'=>'integer|distinct|exists:finished_model_materials,id','qty_used'=>'required|array']);
                if(count($request->qty_used_ids)!==count($request->qty_used)) app(Inventory::class)->fail('Material and quantity rows do not match.');
                foreach($request->qty_used_ids as $index=>$id){$line=\App\Models\FinishedModelMaterial::findOrFail($id);if((int)$line->finished_model_id!==(int)$request->model_id || \App\Services\Warehouse\Quantity::units($request->qty_used[$index])>\App\Services\Warehouse\Quantity::units($line->remain_qty))app(Inventory::class)->fail('Material allocation does not match the unfinished unit.');}
            }
            $response=$next($request);
            if($response->getStatusCode()>=400 || $request->attributes->get('warehouse.rollback')) throw new WarehouseResponseRollback($response);
            $headers=array_filter(['Content-Type'=>$response->headers->get('Content-Type'),'Location'=>$response->headers->get('Location')]);
            DB::table('warehouse_legacy_requests')->insert(['key'=>$key,'user_id'=>$request->user()->id,'input_hash'=>$hash,'status'=>$response->getStatusCode(),'headers'=>json_encode($headers),'body'=>$response->getContent()]);
            return $response;
        }); } catch (WarehouseResponseRollback $failure) { return $failure->response; }
        finally { $request->attributes->remove('warehouse.transaction'); $request->attributes->remove('warehouse.rollback'); $request->attributes->remove('warehouse.document'); }
    }
}

class WarehouseResponseRollback extends \RuntimeException
{
    public $response;
    public function __construct($response) { parent::__construct('Rollback warehouse response'); $this->response=$response; }
}
