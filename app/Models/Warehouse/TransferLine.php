<?php
namespace App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
class TransferLine extends Model
{
    protected $table = 'warehouse_transfer_lines';
    protected $guarded = ['id'];
    public function item() { return $this->belongsTo(Item::class); }
    public function source() { return $this->belongsTo(Location::class, 'source_location_id'); }
    public function destination() { return $this->belongsTo(Location::class, 'destination_location_id'); }
}
