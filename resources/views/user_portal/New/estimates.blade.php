@extends('layouts.contentLayoutMaster')
@section('title', 'Estimates & production plans')
@section('content')
<x-erp.page-header title="Estimates & production plans" description="Snapshot-based production plans and existing inventory reservations." eyebrow="Manufacturing / Planning">
  <x-slot name="actions"><a class="btn btn-outline-primary" href="{{ route('productEstimatesCalculation') }}">Legacy reservation report</a><a class="btn btn-primary" href="{{ route('createProductEstimates') }}">New production plan</a></x-slot>
</x-erp.page-header>
@if(session('message'))<div class="alert alert-info" role="status">{{ session('message') }}</div>@endif
<div class="card"><div class="card-body">
  <form method="get" class="row mb-2"><div class="col-sm-6"><label for="estimate-search">Find an estimate</label><input id="estimate-search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Number or title"></div><div class="col-sm-4"><label for="estimate-kind">Type</label><select id="estimate-kind" name="kind" class="form-control"><option value="">All estimates</option><option value="planning" @if(request('kind') === 'planning') selected @endif>Unreserved production plans</option><option value="legacy" @if(request('kind') === 'legacy') selected @endif>Legacy reservations</option></select></div><div class="col-sm-2 align-self-end"><button class="btn btn-primary mt-1" type="submit">Filter</button></div></form>
  <div class="table-responsive" tabindex="0" role="region" aria-label="Saved estimates"><table class="table"><thead><tr><th>Number / title</th><th>Systems & quantities</th><th>Type / method</th><th>Material requirements</th><th>Shortages</th><th>Actions</th></tr></thead><tbody>
  @forelse($datas as $data)<tr>
    <td><strong>{{ $data->number }}</strong><br>{{ $data->title }}<br><small class="text-muted">{{ $data->plan_date ?? optional($data->created_at)->format('Y-m-d') }}</small></td>
    <td>@if($data->planning_version)@foreach($data->systems as $system)<div>{{ $system->name }} × {{ number_format($system->planned_units) }}</div>@endforeach @else{{ optional($data->bundleDetails)->name ?? 'Legacy model unavailable' }} × {{ $data->quantity }}@endif</td>
    <td>@if($data->planning_version)<span class="badge badge-light-primary">Planning only</span><br>{{ ucfirst($data->allocation_method) }}@else<span class="badge badge-light-warning">Legacy reservation</span>@endif</td>
    <td>@if($data->planning_version){{ count($data->calculation_snapshot['components'] ?? []) }} combined components<br><small class="text-muted">Stock snapshot; no reservation</small>@else @foreach($data->estimateDetails as $line)<div>{{ $line->quantity_in_stock }} × {{ optional($line->productDetails)->name ?? 'Missing component' }}</div>@endforeach @endif</td>
    <td>@if($data->planning_version){{ $data->calculation_snapshot['summary']['shortage_components'] ?? 0 }} materials<br>{{ $data->calculation_snapshot['summary']['feasible_units'] ?? 0 }} feasible units @else @foreach($data->estimateDetails as $line)@if($line->quantity_needed < 0)<div>{{ -$line->quantity_needed }} × {{ optional($line->productDetails)->name ?? 'Missing component' }}</div>@endif @endforeach @endif</td>
    <td>@if($data->planning_version)<a class="btn btn-sm btn-outline-primary" href="{{ route('productionEstimation.show', $data->id) }}">Details</a><a class="btn btn-sm btn-outline-primary" href="{{ route('productionEstimation.pdf', $data->id) }}">PDF</a><form method="post" action="{{ route('productionEstimation.archive', $data->id) }}" class="mt-1">@csrf<button class="btn btn-sm btn-light" type="submit">Archive plan</button></form>@else<a class="btn btn-sm btn-outline-primary" href="{{ route('SingleEstimateDownload', $data->id) }}" target="_blank" rel="noopener">Historic print</a><a class="btn btn-sm btn-light" href="{{ route('deleteEstimate', $data->id) }}">Cancel reservation</a>@endif</td>
  </tr>@empty<tr><td colspan="6" class="text-center text-muted py-3">No estimates match these filters.</td></tr>@endforelse
  </tbody></table></div>
  {{ $datas->links('pagination::bootstrap-4') }}
</div></div>
@endsection
