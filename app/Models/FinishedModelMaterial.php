<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedModelMaterial extends Model
{
    use HasFactory;

    protected $fillable = ['finished_model_id','raw_material_id','qty','remain_qty','status'];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}
