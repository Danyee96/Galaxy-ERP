<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = ['name','sku','category','type','total_qty','initial_qty','date','re_order','inventory_asset','description','sale_price','income_account','purchase_info','cost','expense_account','image','temp_qty','sea_transit','air_transit','status'];

    public function bundleDetails() {
        return $this->hasMany(Bundle::class, 'bundle_id');
    }

    public function estimateValues() {
        return $this->hasMany(EstimateValue::class, 'product_id');
    }

    public function productInventory() {
        return $this->hasMany(ProductInventory::class, 'product_id')->where('status', 1);
    }
}
