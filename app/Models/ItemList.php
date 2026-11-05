<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemList extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number','power_id','company_id','maker_name','model','production_date','warranty_years',
    ];

    public function Company(){
        return $this->hasOne(Company::class,'id','company_id');
    }

    public function Power(){
        return $this->hasOne(Power::class,'id','power_id');
    }

    public function machineHistory() {
        return $this->hasMany(ItemHistory::class, 'item_id')->where('status',1);
    }
}
