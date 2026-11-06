<?php

namespace App\Services\Estimation;

use App\Models\Bundle;
use App\Models\EstimateValue;
use App\Models\ProductEstimate;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProductionEstimationPlanner
{
    private MultiSystemEstimationService $calculator;

    public function __construct(MultiSystemEstimationService $calculator)
    {
        $this->calculator = $calculator;
    }

    public function preview(array $input): array
    {
        return DB::transaction(function () use ($input) { return $this->calculate($input, false); });
    }

    public function save(array $input, int $actor): ProductEstimate
    {
        return DB::transaction(function () use ($input, $actor) {
            // First operation takes the common write mutex, including on SQLite test databases.
            $number = $this->nextNumber();
            $existing = ProductEstimate::where('request_key', $input['request_key'])->first();
            if ($existing) {
                if ((int) $existing->created_by !== $actor || ($existing->calculation_snapshot['input_hash'] ?? '') !== $this->inputHash($input)) {
                    throw new InvalidArgumentException('This save key was already used for a different plan. Refresh the page before saving.');
                }
                return $existing;
            }
            $snapshot = $this->calculate($input, true);
            if (!hash_equals($snapshot['fingerprint'], $input['preview_fingerprint'])) throw new PreviewChangedException($snapshot);
            $snapshot['input_hash'] = $this->inputHash($input);
            $estimate = ProductEstimate::create(['title' => $input['title'], 'number' => (string) $number, 'bundle_id' => null, 'quantity' => null, 'status' => 1,
                'planning_version' => 1, 'allocation_method' => $input['mode'], 'plan_date' => $input['plan_date'], 'created_by' => $actor,
                'calculation_snapshot' => $snapshot, 'inventory_fingerprint' => $snapshot['fingerprint'], 'request_key' => $input['request_key']]);
            foreach ($snapshot['systems'] as $position => $system) {
                $estimate->systems()->create(array_intersect_key($system, array_flip(['product_id', 'name', 'sku', 'requested_units', 'planned_units', 'feasible_units', 'standalone_maximum', 'weight', 'recipe_snapshot'])) + ['position' => $position]);
            }
            foreach ($snapshot['components'] as $component) $estimate->components()->create(array_intersect_key($component, array_flip(['product_id', 'name', 'sku', 'physical_stock', 'legacy_committed', 'projected_stock', 'in_transit', 'available', 'required', 'allocated', 'remaining', 'shortage', 'consumers'])));
            return $estimate;
        }, 5);
    }

    /** Keep the legacy reservation contract, with common numbering and atomic writes. */
    public function saveLegacy(array $input): ProductEstimate
    {
        return DB::transaction(function () use ($input) {
            $number = $this->nextNumber();
            $model = Products::where('type', 'Bundle')->where('id', $input['id'])->lockForUpdate()->first();
            if (!$model) throw new InvalidArgumentException('Select an existing bundle.');
            $recipe = Bundle::where('bundle_id', $model->id)->orderBy('id')->lockForUpdate()->get();
            if ($recipe->isEmpty()) throw new InvalidArgumentException('Bundle recipe does not exist. Add a recipe first.');
            $products = Products::whereIn('id', $recipe->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($recipe as $line) if (!$products->has($line->product_id) || !is_numeric($line->quantity) || $line->quantity <= 0) throw new InvalidArgumentException('The bundle recipe contains missing inventory or invalid quantities.');
            $estimate = ProductEstimate::create(['title' => $input['title'], 'number' => (string) $number, 'bundle_id' => $model->id, 'quantity' => $input['qty'], 'status' => 1]);
            foreach ($recipe as $line) {
                $product = $products[$line->product_id];
                $demand = $line->quantity * $input['qty'];
                if ($demand > 2147483647) throw new InvalidArgumentException('Legacy estimates cannot store this component demand; use a production plan.');
                $projected = $product->temp_qty ?? 0;
                if (!is_numeric($projected)) throw new InvalidArgumentException('Legacy projected inventory contains an invalid quantity.');
                $shortage = min(0, $projected - $demand);
                $product->update(['temp_qty' => max(0, $projected - $demand)]);
                EstimateValue::create(['estimate_id' => $estimate->id, 'bundle_id' => $model->id, 'product_id' => $line->product_id, 'quantity_in_stock' => $demand, 'quantity_needed' => $shortage]);
            }
            return $estimate;
        }, 5);
    }

    private function calculate(array $input, bool $lock): array
    {
        $ids = array_column($input['models'], 'product_id');
        $modelQuery = Products::whereIn('id', $ids)->where('type', 'Bundle')->where('status', 1)->orderBy('id');
        if ($lock) $modelQuery->lockForUpdate();
        $models = $modelQuery->get(['id', 'name', 'sku']);
        $recipeQuery = Bundle::whereIn('bundle_id', $ids)->where(function ($q) { $q->where('status', 1)->orWhereNull('status'); })->orderBy('id');
        if ($lock) $recipeQuery->lockForUpdate();
        $recipes = $recipeQuery->get(['id', 'bundle_id', 'product_id', 'quantity']);
        $stockQuery = Products::whereIn('id', $recipes->pluck('product_id'))->where('type', 'Inventory')->where('status', 1)->orderBy('id');
        if ($lock) $stockQuery->lockForUpdate();
        $stock = $stockQuery->get(['id', 'name', 'sku', 'total_qty', 'temp_qty', 'air_transit', 'sea_transit'])->keyBy('id')->toArray();
        $committed = DB::table('estimate_values')->join('product_estimates', 'product_estimates.id', '=', 'estimate_values.estimate_id')
            ->where('product_estimates.status', 1)->whereNull('product_estimates.planning_version')->whereIn('estimate_values.product_id', array_keys($stock))
            ->groupBy('estimate_values.product_id')->selectRaw('estimate_values.product_id, SUM(estimate_values.quantity_in_stock) AS committed')->pluck('committed', 'product_id');
        foreach ($stock as $id => &$product) {
            $product['legacy_committed'] = (string) ($committed[$id] ?? 0);
            $product['name'] = $product['name'] ?? 'Component '.$id;
            $product['sku'] = $product['sku'] ?? '';
        }
        unset($product);
        $warehouseStock = [];
        if (config('warehouses.enabled')) {
            $warehouseQuery = DB::table('warehouse_balances as b')->join('warehouse_items as i','i.id','=','b.item_id')->join('warehouse_locations as l','l.id','=','b.location_id')->join('warehouses as w','w.id','=','l.warehouse_id')
                ->where('i.source_type','product')->whereIn('i.source_id',array_keys($stock))->where('l.kind','physical')->where('l.active',1)->where('w.active',1);
            if (!empty($input['warehouse_ids'])) $warehouseQuery->whereIn('w.id',$input['warehouse_ids']);
            if ($lock) $warehouseQuery->lockForUpdate();
            $warehouseRows=$warehouseQuery->orderBy('w.id')->orderBy('b.id')->get(['i.source_id','w.id as warehouse_id','w.name','b.quantity']);
            foreach($stock as $id=>&$product) {
                $total=0;$byWarehouse=[];
                foreach($warehouseRows->where('source_id',$id) as $row){$units=\App\Services\Warehouse\Quantity::units($row->quantity);$total+=$units;$byWarehouse[$row->warehouse_id]=($byWarehouse[$row->warehouse_id]??0)+$units;}
                $product['total_qty']=\App\Services\Warehouse\Quantity::text($total);
                $legacy=\App\Services\Warehouse\Quantity::units($product['legacy_committed']);
                foreach($byWarehouse as $wid=>$units){$held=min($legacy,$units);$legacy-=$held;$warehouseStock[$id][]=['warehouse_id'=>$wid,'name'=>$warehouseRows->firstWhere('warehouse_id',$wid)->name,'physical'=>\App\Services\Warehouse\Quantity::text($units),'available'=>\App\Services\Warehouse\Quantity::text($units-$held)];}
            }
            unset($product);
        }
        $catalog = [];
        foreach ($models as $model) $catalog[$model->id] = ['name' => $model->name ?? '', 'sku' => $model->sku ?? '', 'recipe' => []];
        foreach ($recipes as $recipe) if (isset($catalog[$recipe->bundle_id])) $catalog[$recipe->bundle_id]['recipe'][] = ['product_id' => $recipe->product_id, 'quantity' => $recipe->quantity];
        $plan = $this->calculator->calculate($input['models'], $catalog, $stock, $input['mode']);
        if (config('warehouses.enabled')) {
            $plan['warehouse_ids']=$input['warehouse_ids']??[];
            foreach($plan['components'] as &$component){
                $allocation=\App\Services\Warehouse\Quantity::units($component['allocated']);
                $component['warehouses']=$warehouseStock[$component['product_id']]??[];
                foreach($component['warehouses'] as &$warehouse){$take=min($allocation,\App\Services\Warehouse\Quantity::units($warehouse['available']));$warehouse['allocated']=\App\Services\Warehouse\Quantity::text($take);$allocation-=$take;}
                unset($warehouse);
            }
            unset($component);
            $plan['stock_policy'].=' Warehouse scope excludes internal transfer holding stock. Legacy commitments are conservatively deducted once from the selected pool, in warehouse ID order.';
        }
        $plan['fingerprint'] = hash('sha256', json_encode([$input['models'], $input['mode'], $catalog, $stock, $input['warehouse_ids'] ?? [], $warehouseStock], JSON_THROW_ON_ERROR));
        $plan['calculated_at'] = now()->toIso8601String();
        return $plan;
    }

    private function nextNumber(): int
    {
        DB::table('estimate_number_counters')->insertOrIgnore(['name' => 'product_estimates', 'next_number' => 1001]);
        $counter = DB::table('estimate_number_counters')->where('name', 'product_estimates')->lockForUpdate()->first();
        $cast = DB::connection()->getDriverName() === 'sqlite' ? 'INTEGER' : 'UNSIGNED';
        $maximum = DB::table('product_estimates')->selectRaw('MAX(CAST(number AS '.$cast.')) AS maximum')->value('maximum');
        $number = max(1001, (int) $counter->next_number, (int) $maximum + 1);
        if ($number >= 9007199254740990) throw new InvalidArgumentException('Estimate number capacity reached.');
        DB::table('estimate_number_counters')->where('name', 'product_estimates')->update(['next_number' => $number + 1]);
        return $number;
    }

    private function inputHash(array $input): string
    {
        $parts = [$input['title'], $input['plan_date'], $input['mode'], $input['models']];
        if (!empty($input['warehouse_ids'])) $parts[] = $input['warehouse_ids'];
        return hash('sha256', json_encode($parts, JSON_THROW_ON_ERROR));
    }
}
