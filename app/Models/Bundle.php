<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $fillable = ['bundle_id','product_id','quantity','status'];

    public function productDetails()
    {
        return $this->hasOne(Products::class, 'id', 'product_id');
    }
}
