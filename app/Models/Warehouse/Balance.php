<?php
namespace App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
class Balance extends Model
{
    protected $table = 'warehouse_balances';
    protected $guarded = ['id'];
    public function item() { return $this->belongsTo(Item::class); }
    public function location() { return $this->belongsTo(Location::class); }
}
