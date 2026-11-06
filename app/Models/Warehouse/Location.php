<?php
namespace App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
class Location extends Model
{
    protected $table = 'warehouse_locations';
    protected $guarded = ['id'];
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function balances() { return $this->hasMany(Balance::class); }
    public function getLabelAttribute() { return $this->warehouse->name.' / '.($this->rack ? $this->rack.' / ' : '').$this->row.' / '.$this->column; }
}
