<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialInventory extends Model
{
    use HasFactory;

    protected $fillable =[
        'id','raw_material_id','transit_id','stock_in','remarks','status','entry_by','received_by',
    ];

    public function rawMaterial(){
        return $this->hasOne(RawMaterial::class,'id','raw_material_id');
    }

    public function transitMethod(){
        return $this->hasOne(TransitMethod::class,'id','transit_id');
    }

    public function entryBy(){
        return $this->hasOne(User::class,'id','entry_by');
    }

    public function receivedBy(){
        return $this->hasOne(User::class,'id','received_by');
    }
}
