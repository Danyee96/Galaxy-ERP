<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    use HasFactory;

    protected $fillable = ['product_id','transit_id','stock_in','status','entry_by','received_by','expected_date','description',
    'production_date','remaining_stock','received_stock'];

    public function productDetails()
    {
        return $this->hasOne(Products::class, 'id', 'product_id');
    }

    public function entryBy()
    {
        return $this->hasOne(User::class, 'id', 'entry_by');
    }

    public function receivedBy()
    {
        return $this->hasOne(User::class, 'id', 'received_by');
    }
}
