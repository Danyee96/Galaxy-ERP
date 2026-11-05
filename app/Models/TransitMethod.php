<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransitMethod extends Model
{
    use HasFactory;

    protected $fillable =[
        'id','name','status',
    ];
}
