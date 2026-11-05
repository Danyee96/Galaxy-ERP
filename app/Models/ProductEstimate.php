<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductEstimate extends Model
{
    use HasFactory;

    protected $fillable = ['title','number','bundle_id','quantity','status'];

    public function bundleDetails()
    {
        return $this->hasOne(Products::class, 'id', 'bundle_id');
    }

    public function estimateDetails()
    {
        return $this->hasMany(EstimateValue::class, 'estimate_id');
    }
}
