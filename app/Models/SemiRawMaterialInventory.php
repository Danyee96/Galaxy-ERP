<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemiRawMaterialInventory extends Model
{
    use HasFactory;

    protected $fillable =[
        'id','semi_raw_material_id','transit_id','stock_in','remarks','status',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(SemiRawMaterial::class, 'semi_raw_material_id');
    }
}
