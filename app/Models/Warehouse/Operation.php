<?php
namespace App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
class Operation extends Model
{
    protected $table = 'warehouse_operations';
    protected $guarded = ['id'];
    protected $casts = ['result' => 'array'];
    public function movements() { return $this->hasMany(Movement::class); }
}
