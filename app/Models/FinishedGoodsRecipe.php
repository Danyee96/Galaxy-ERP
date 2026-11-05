<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedGoodsRecipe extends Model
{
    use HasFactory;

    protected $fillable =[
        'id','material_id','material_type','qty','status','finished_good_id',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'material_id');
    }

    public function semiRawMaterial()
    {
        return $this->belongsTo(SemiRawMaterial::class, 'material_id');
    }
}
