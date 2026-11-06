<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEstimateComponent extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['consumers' => 'array'];

    public function estimate()
    {
        return $this->belongsTo(ProductEstimate::class, 'product_estimate_id');
    }
}
