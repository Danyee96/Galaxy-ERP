<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UPSModel extends Model
{
    use HasFactory;

    protected $fillable = ['name','qty','temp_qty','semi_qty','semi_temp_qty','status'];
}
