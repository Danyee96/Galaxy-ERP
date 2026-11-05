<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemiRawMaterialRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'raw_material_id',
        'semi_raw_material_id',
        'qty',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}
