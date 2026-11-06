<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductEstimate extends Model
{
    use HasFactory;

    protected $fillable = ['title','number','bundle_id','quantity','status','planning_version','allocation_method','plan_date','calculation_snapshot','inventory_fingerprint','created_by','request_key'];

    protected $casts = ['calculation_snapshot' => 'array', 'planning_version' => 'integer'];

    /** Resolve renamed warehouse labels without rewriting the calculation snapshot. */
    public function planningSnapshot(): array
    {
        $plan=$this->calculation_snapshot??[];
        if(!config('warehouses.enabled'))return $plan;
        $ids=[];foreach($plan['components']??[]as$component)foreach($component['warehouses']??[]as$warehouse)$ids[]=$warehouse['warehouse_id'];
        if(!$ids)return $plan;
        $names=\App\Models\Warehouse\Warehouse::whereIn('id',array_unique($ids))->pluck('name','id');
        foreach($plan['components']as&$component)foreach($component['warehouses']??[]as$index=>$warehouse)$component['warehouses'][$index]['name']=$names[$warehouse['warehouse_id']]??$warehouse['name'];
        unset($component);return $plan;
    }

    public function systems()
    {
        return $this->hasMany(ProductEstimateSystem::class)->orderBy('position');
    }

    public function components()
    {
        return $this->hasMany(ProductEstimateComponent::class);
    }

    public function bundleDetails()
    {
        return $this->hasOne(Products::class, 'id', 'bundle_id');
    }

    public function estimateDetails()
    {
        return $this->hasMany(EstimateValue::class, 'estimate_id');
    }
}
