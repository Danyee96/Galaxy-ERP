<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\App\Services\Warehouse\Inventory::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\View::composer(['user_portal.New.products','warehouse.index','warehouse.unassigned'],function($view) {
            $pending=collect();
            if(config('warehouses.enabled') && \Illuminate\Support\Facades\Schema::hasTable('warehouse_state')) {
                $ids=\Illuminate\Support\Facades\DB::table('warehouse_state')->where('key','like','unassigned:product:%')->pluck('key')->map(fn($key)=>(int)substr($key,19));
                $pending=\App\Models\Products::whereIn('id',$ids)->orderBy('name')->get(['id','name','sku','total_qty']);
            }
            $view->with('unassignedProducts',$pending);
            if($view->name()==='user_portal.New.products') {
                $stocks=collect();
                if(config('warehouses.enabled') && \Illuminate\Support\Facades\Schema::hasTable('warehouse_balances')) {
                    $ids=collect($view->getData()['products'] ?? [])->pluck('id');
                    $stocks=\Illuminate\Support\Facades\DB::table('warehouse_balances as b')
                        ->join('warehouse_items as i','i.id','=','b.item_id')->join('warehouse_locations as l','l.id','=','b.location_id')->join('warehouses as w','w.id','=','l.warehouse_id')
                        ->where('i.source_type','product')->whereIn('i.source_id',$ids)
                        ->orderBy('w.name')->orderBy('l.row')->orderBy('l.column')
                        ->get(['i.source_id','b.quantity','w.name as warehouse_name','l.rack','l.row','l.column','l.kind'])->groupBy('source_id');
                }
                $view->with('productWarehouseStock',$stocks);
            }

        });
        $map = [];
        foreach (config('warehouses.sources', []) as $type => $definition) {
            $map[$type] = $definition[0];
            $definition[0]::observe(\App\Services\Warehouse\StockObserver::class);
        }
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap($map);
        foreach ([\App\Models\Invoice::class, \App\Models\ProductInventory::class, \App\Models\RawMaterialInventory::class, \App\Models\SemiRawMaterialInventory::class, \App\Models\FinishedGoodsInventory::class, \App\Models\FinishedGoodsOut::class, \App\Models\FinishedModels::class] as $documentClass) {
            $documentClass::created(function ($document) {
                $request=request();
                if (!config('warehouses.enabled') || !$request->attributes->get('warehouse.transaction')) return;
                $reference=$document->getTable().':'.$document->id;
                $request->attributes->set('warehouse.document',$reference);
                // Some legacy receiving paths create their document after updating stock.
                \App\Models\Warehouse\Operation::where('notes','like','Legacy request '.$request->warehouse_request_key.';%')
                    ->where('reference',$request->route()->getName())->update(['reference'=>$reference]);
            });
        }
    }
}
