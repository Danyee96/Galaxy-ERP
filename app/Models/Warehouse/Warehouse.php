<?php
namespace App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
class Warehouse extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['active' => 'boolean'];
    public function locations() { return $this->hasMany(Location::class); }
}
