<?php
namespace App\Services\Warehouse;
use App\Models\Warehouse\Item;
class StockObserver
{
    public function creating($model) {
        if($model instanceof \App\Models\Products && $model->type!=='Inventory' && Quantity::units($model->total_qty)===0)return;
        if(!config('warehouses.enabled')) { if(app(Inventory::class)->installed())app(Inventory::class)->fail('An active warehouse ledger exists. Re-enable warehouse management before changing stock.'); return; }
        if(!app(Inventory::class)->inTransaction())app(Inventory::class)->fail('Create physical stock through the warehouse transaction service.');
    }
    public function updating($model) {
        $definition=collect(config('warehouses.sources'))->first(fn($d)=>$d[0]===get_class($model));
        if(!$definition || !$definition[1] || !$model->isDirty($definition[1]))return;
        if(config('warehouses.enabled'))app(Inventory::class)->legacy($model);
        elseif(app(Inventory::class)->installed())app(Inventory::class)->fail('An active warehouse ledger exists. Re-enable warehouse management before changing stock.');
    }
    public function created($model) { if(config('warehouses.enabled')) app(Inventory::class)->legacy($model,true); }
    public function saved($model) {
        if(!config('warehouses.enabled')) return;
        $definition=collect(config('warehouses.sources'))->first(fn($d)=>$d[0]===get_class($model));
        if($definition && $definition[1] && ($model->wasRecentlyCreated || $model->wasChanged($definition[1]))) app(Inventory::class)->verifyLegacy($model);
    }
    public function deleting($model) {
        if(!config('warehouses.enabled')) { if(app(Inventory::class)->installed())app(Inventory::class)->fail('An active warehouse ledger exists. Re-enable warehouse management before deleting stock.'); return; }
        $item=app(Inventory::class)->itemFor($model);
        if($item->movements()->exists() || $item->balances()->where('quantity','!=',0)->exists()) app(Inventory::class)->fail('Stock or movement history exists. Deactivate this item instead of deleting it.');
    }
}
