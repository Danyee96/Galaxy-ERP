<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemHistoryAccessory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_history_id','accessory_id','status',
    ];

    public function Accessory(){
        return $this->hasOne(Accessories::class,'id','accessory_id');
    }
}
