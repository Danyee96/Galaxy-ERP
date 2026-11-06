<?php
namespace App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
class Movement extends Model
{
    protected $table = 'warehouse_movements';
    protected $guarded = ['id'];
    public function item() { return $this->belongsTo(Item::class); }
    public function source() { return $this->belongsTo(Location::class, 'source_location_id'); }
    public function destination() { return $this->belongsTo(Location::class, 'destination_location_id'); }
    public function operation() { return $this->belongsTo(Operation::class); }
}
