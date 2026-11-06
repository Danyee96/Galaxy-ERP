<?php
namespace App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
class Transfer extends Model
{
    protected $table = 'warehouse_transfers';
    protected $guarded = ['id'];
    public function operation() { return $this->belongsTo(Operation::class); }
    public function lines() { return $this->hasMany(TransferLine::class); }
}
