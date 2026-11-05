<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serialization extends Model
{
    use HasFactory;

    protected $fillable = ['date','serial_number','model','dealer_name','dealer_city','customer_name','customer_city','delivery_boy','created_by',];
}
