<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Serialization list | Galaxy Inverter</title><style>@page{size:A4 landscape;margin:12mm}body{font:10px Arial,sans-serif;color:#111}h1{font-size:18px}table{width:100%;border-collapse:collapse}th,td{padding:6px;border:1px solid #ccc;text-align:left;overflow-wrap:anywhere}thead{display:table-header-group}tr{break-inside:avoid}@media print{button{display:none}}</style></head><body>
<h1>Galaxy Inverter — Serialization Info List</h1>
<p>{{ now()->format('d M Y H:i') }} @if(!empty($filters['search'])) · Search: {{ $filters['search'] }} @endif
@foreach($columns as $key=>$label) @if(isset($filters[$key]) && $filters[$key] !== '') · {{ $label }}: {{ $filters[$key] }} @endif @endforeach
@if(!empty($filters['from'])) · From: {{ $filters['from'] }} @endif @if(!empty($filters['to'])) · To: {{ $filters['to'] }} @endif</p>
<button type="button" onclick="window.print()">Print</button>
<table><thead><tr><th>Sr.</th>@foreach($columns as $label)<th>{{ $label }}</th>@endforeach</tr></thead><tbody>@forelse($machines as $machine)<tr><td>{{ $loop->iteration }}</td>@foreach($columns as $key=>$label)<td>{{ $key==='date' && $machine->date ? date('d-M-Y',strtotime($machine->date)) : $machine->$key }}</td>@endforeach</tr>@empty<tr><td colspan="9">No matching records.</td></tr>@endforelse</tbody></table>
<script>window.addEventListener('load',()=>window.print());</script></body></html>
