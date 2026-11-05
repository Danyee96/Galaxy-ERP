<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedGoodsOut extends Model
{
    use HasFactory;

    protected $fillable =[
        'id','finished_good_id','company_id','customer_name','sale_quantity','sale_date','power_id','model',
    ];

    public function finishedGoods()
    {
        return $this->belongsTo(FinishedGoods::class, 'finished_good_id');
    }

    public function companyName()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function powerName()
    {
        return $this->belongsTo(Power::class, 'power_id');
    }
}
