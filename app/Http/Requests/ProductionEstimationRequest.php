<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionEstimationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array((int) $this->user()->role_id, [1, 4, 5], true);
    }

    public function rules(): array
    {
        $saving = $this->routeIs('productionEstimation.store');
        return [
            'title' => ($saving ? 'required' : 'nullable').'|string|max:255',
            'plan_date' => 'required|date_format:Y-m-d',
            'mode' => 'required|in:manual,priority,balanced',
            'warehouse_ids' => config('warehouses.enabled') ? 'nullable|array|max:100' : 'prohibited',
            'warehouse_ids.*' => config('warehouses.enabled') ? 'integer|distinct|exists:warehouses,id' : 'prohibited',
            'models' => 'required|array|min:1|max:100',
            'models.*.product_id' => 'required|integer|min:1|distinct',
            'models.*.quantity' => 'required|integer|min:0|max:1000000',
            'models.*.weight' => 'required|integer|min:1|max:1000',
            'preview_fingerprint' => ($saving ? 'required' : 'nullable').'|string|size:64',
            'request_key' => ($saving ? 'required' : 'nullable').'|uuid',
        ];
    }

    public function planningInput(): array
    {
        $data = $this->validated();
        if (!empty($data['warehouse_ids']) && !config('warehouses.enabled')) abort(422, 'Warehouse planning is not activated.');
        if (isset($data['warehouse_ids'])) { $data['warehouse_ids'] = array_map('intval', $data['warehouse_ids']); sort($data['warehouse_ids']); }
        $data['models'] = array_map(fn ($row) => ['product_id' => (int) $row['product_id'], 'quantity' => (int) $row['quantity'], 'weight' => (int) $row['weight']], array_values($data['models']));
        return $data;
    }
}
