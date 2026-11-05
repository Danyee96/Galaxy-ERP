<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedGoodsInventory extends Model
{
    use HasFactory;

    protected $fillable =[
        'id','finished_good_id','transit_id','stock_in','remarks','status',
    ];

    public function finishedGoods()
    {
        return $this->belongsTo(FinishedGoods::class, 'finished_good_id');
    }
}
