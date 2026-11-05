<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id','company_id','name','phone','entry_date','error_id','error_description','maker_name','model','power_id','status','created_by','exit_by','exit_date','resolution','cost','warranty_status','receiver_name','entry_by','packing_status'
    ];

    public function Item(){
        return $this->hasOne(ItemList::class,'id','item_id');
    }

    public function Power(){
        return $this->hasOne(Power::class,'id','power_id');
    }

    public function Company(){
        return $this->hasOne(Company::class,'id','company_id');
    }

    public function Error(){
        return $this->hasOne(ErrorInfo::class,'id','error_id');
    }

    public function ExitBy(){
        return $this->hasOne(User::class,'id','exit_by');
    }

    public function EntryBy(){
        return $this->hasOne(User::class,'id','entry_by');
    }

    public function machineAccessories() {
        return $this->hasMany(ItemHistoryAccessory::class, 'item_history_id');
    }

    public function exitMachineAccessories() {
        return $this->hasMany(ItemHistoryAccessory::class, 'item_history_id')->where('status',1);
    }

    public function exitMachineAccessoriesLeft() {
        return $this->hasMany(ItemHistoryAccessory::class, 'item_history_id')->where('status',0);
    }
}
