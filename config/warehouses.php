<?php
return [
    'enabled' => env('WAREHOUSES_ENABLED', false),
    // Explicit backwards-compatible rule: oldest location first, active warehouses only.
    'legacy_allocation' => 'location_id_ascending',
    'sources' => [
        'product' => [App\Models\Products::class, 'total_qty', 'temp_qty', 4],
        'raw' => [App\Models\RawMaterial::class, 'current_stock', 'temp_stock', 2],
        'semi' => [App\Models\SemiRawMaterial::class, 'current_stock', 'temp_stock', 2],
        'finished' => [App\Models\FinishedGoods::class, 'current_stock', 'temp_stock', 2],
        'ups' => [App\Models\FinishedModels::class, null, null, 0],
    ],
];
