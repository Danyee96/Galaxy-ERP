<?php
namespace App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
class Item extends Model
{
    protected $table = 'warehouse_items';
    protected $guarded = ['id'];
    public function source() { return $this->morphTo(__FUNCTION__, 'source_type', 'source_id'); }
    public function balances() { return $this->hasMany(Balance::class); }
    public function movements() { return $this->hasMany(Movement::class); }
    public function getNameAttribute() { return optional($this->source)->name ?? optional($this->source)->serial_number ?? 'Removed item '.$this->source_type.' #'.$this->source_id; }
}
