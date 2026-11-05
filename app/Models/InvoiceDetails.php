<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetails extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id','product_id','product_qty'];

    public function productDetails()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
