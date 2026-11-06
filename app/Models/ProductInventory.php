<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductInventory extends Model
{
    use HasFactory;

    protected $fillable = ['product_id','transit_id','stock_in','status','entry_by','received_by','expected_date','description',
    'production_date','remaining_stock','received_stock'];

    public function scopeOverdue($query)
    {
        return $query->whereIn('transit_id', [1, 2, 3])->where('status', 0)
            ->where('expected_date', '>', '0000-00-00')
            ->where('expected_date', '<', Carbon::today(config('app.timezone'))->toDateString());
    }

    public function getOverdueDaysAttribute(): int
    {
        if ((int) $this->status !== 0 || $this->status === null || !in_array((int) $this->transit_id, [1, 2, 3], true) || !$this->expected_date) return 0;
        try {
            $date = Carbon::createFromFormat('!Y-m-d', $this->expected_date, config('app.timezone'));
            if (!$date || $date->format('Y-m-d') !== $this->expected_date || $this->expected_date === '0000-00-00') return 0;
            $today = Carbon::today(config('app.timezone'));
            return $date->lt($today) ? (int) $date->diffInDays($today) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function productDetails()
    {
        return $this->hasOne(Products::class, 'id', 'product_id');
    }

    public function entryBy()
    {
        return $this->hasOne(User::class, 'id', 'entry_by');
    }

    public function receivedBy()
    {
        return $this->hasOne(User::class, 'id', 'received_by');
    }
}
