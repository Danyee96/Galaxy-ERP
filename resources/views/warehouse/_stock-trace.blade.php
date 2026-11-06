@if($warehouseMovements->isNotEmpty())
<h2 style="font-size:14px;margin-top:24px">Warehouse movement trace</h2>
<table class="table" style="width:100%;font-size:10px"><thead style="display:table-header-group"><tr><th>Date</th><th>Item</th><th>Movement</th><th>Quantity</th><th>Source warehouse / rack / row / column</th><th>Destination warehouse / rack / row / column</th></tr></thead><tbody>@foreach($warehouseMovements as $movement)<tr style="page-break-inside:avoid"><td>{{ $movement->created_at }}</td><td>{{ $movement->item->name }}</td><td>{{ $movement->operation->type }}</td><td>{{ $movement->quantity }}</td><td>{{ optional($movement->source)->label ?? 'External' }}</td><td>{{ optional($movement->destination)->label ?? 'External' }}</td></tr>@endforeach</tbody></table>
@endif
