<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedModels extends Model
{
    use HasFactory;

    protected $fillable = ['serial_number','model_id','created_by','status','is_complete'];

    public function modelDetail()
    {
        return $this->belongsTo(UPSModel::class, 'model_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modelMaterial()
    {
        return $this->hasMany(FinishedModelMaterial::class, 'finished_model_id', 'id');
    }
}
