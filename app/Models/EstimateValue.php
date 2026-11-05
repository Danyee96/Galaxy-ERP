<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateValue extends Model
{
    use HasFactory;

    protected $fillable = ['estimate_id','bundle_id','product_id','quantity_in_stock','quantity_needed'];

    public function productDetails()
    {
        return $this->hasOne(Products::class, 'id', 'product_id');
    }
}
