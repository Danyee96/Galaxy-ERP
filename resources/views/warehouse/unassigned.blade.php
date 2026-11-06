@extends('layouts.contentLayoutMaster')
@section('title','Unassigned inventory')
@section('content')
<x-erp.page-header title="Unassigned inventory" description="Correct excluded legacy stock and choose its warehouse location." eyebrow="Inventory"><x-slot name="actions"><a class="btn btn-outline-primary" href="{{ route('warehouses.index') }}">Warehouse stock</a></x-slot></x-erp.page-header>
@include('warehouse._feedback')
@include('warehouse._unassigned-warning')
<p>Existing negative quantities remain unchanged until you submit a correction. Enter the actual physical quantity (zero is allowed). Each correction is recorded in the audit trail.</p>
@forelse($unassignedProducts as $product)
<details class="erp-panel p-2 mb-2"><summary><strong>{{ $product->name }}</strong> · {{ $product->sku }} · Legacy stock: <span class="text-danger">{{ \App\Services\Warehouse\Quantity::display($product->total_qty) }}</span></summary>
@if(in_array((int)auth()->user()->role_id,[1,4,5],true))
<form method="post" action="{{ route('warehouses.assignUnassigned') }}" class="mt-2">@csrf
<input type="hidden" name="request_key" value="{{ Str::uuid() }}"><input type="hidden" name="product_id" value="{{ $product->id }}">
<label>Actual physical stock<input class="form-control" type="number" name="quantity" min="0" step="0.0001" required></label>
@include('warehouse._location-fields')
<label>Correction reference / reason<input class="form-control" name="reference" maxlength="255" required></label>
<button type="submit" class="btn btn-primary mt-2">Correct stock and assign warehouse</button>
</form>
@endif
</details>
@empty<div class="alert alert-success">All excluded products have been corrected and assigned.</div>@endforelse
@endsection
