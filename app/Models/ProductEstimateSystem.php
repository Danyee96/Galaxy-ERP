<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEstimateSystem extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['recipe_snapshot' => 'array'];

    public function estimate()
    {
        return $this->belongsTo(ProductEstimate::class, 'product_estimate_id');
    }
}
