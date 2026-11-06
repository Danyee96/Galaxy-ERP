@extends('layouts.contentLayoutMaster')
@section('title', 'Production plan '.$estimate->number)
@section('page-styles')
<link rel="stylesheet" href="{{ asset('brand/estimation.css') }}?v={{ is_file(public_path('brand/estimation.css')) ? filemtime(public_path('brand/estimation.css')) : '1' }}">
@endsection
@section('content')
<div class="erp-planner erp-plan-detail">
  <x-erp.page-header :title="$estimate->title" :description="'Plan #'.$estimate->number.' · '.$estimate->plan_date.' · '.ucfirst($plan['allocation_method'])" eyebrow="Saved production snapshot">
    <x-slot name="actions"><button type="button" class="btn btn-outline-primary" id="plan-print">Print</button><a class="btn btn-outline-primary" href="{{ route('productEstimates') }}">All estimates</a><a class="btn btn-outline-primary" href="{{ route('productionEstimation.pdf', $estimate->id) }}">Download PDF</a><button type="button" class="btn btn-primary" id="plan-export-excel">Export Excel</button><a class="btn btn-outline-primary" href="{{ route('productionEstimation.csv', $estimate->id) }}">CSV fallback</a></x-slot>
  </x-erp.page-header>
  <p class="planner-policy">Immutable calculation snapshot from {{ $plan['calculated_at'] }}. This plan has not reserved or deducted inventory.@if(isset($plan['warehouse_ids'])) Warehouse labels use current names; calculation quantities remain fixed.@endif @if(!$estimate->status) This plan is archived.@endif</p>
  <p id="plan-export-error" class="alert alert-warning" role="alert" hidden></p>
  <section class="erp-panel planner-saved-summary"><strong>{{ number_format($plan['summary']['planned_units']) }} planned units</strong><span>{{ number_format($plan['summary']['feasible_units']) }} feasible whole units</span><span>{{ $plan['summary']['shortage_components'] }} shortage materials</span><span class="badge {{ $plan['summary']['feasible'] ? 'badge-success' : 'badge-warning' }}">{{ $plan['summary']['planned_units'] == 0 ? 'No complete systems available' : ($plan['summary']['feasible'] ? 'Feasible plan' : 'Additional material needed') }}</span></section>
  <p class="planner-help">{{ $plan['allocation_rule'] }} Standalone maxima cannot be added together; feasible mix quantities share the same material pool.</p>
  <section class="erp-panel planner-results"><header class="erp-panel-header"><h2>Included systems</h2></header><div class="table-responsive" tabindex="0" role="region" aria-label="Saved system capacities">@include('user_portal.estimation.systems')</div></section>
  <section class="erp-panel planner-results"><header class="erp-panel-header"><div><h2>Material requirements & inventory snapshot</h2><p>{{ $plan['stock_policy'] }}</p></div></header><div class="table-responsive" tabindex="0" role="region" aria-label="Saved material requirements">@include('user_portal.estimation.materials')</div></section>
  <section class="erp-panel planner-results"><header class="erp-panel-header"><div><h2>Purchase requirements</h2><p>Additional quantities required for this selected plan; no purchasing action has been created.</p></div></header><div class="table-responsive"><table class="table"><thead><tr><th>Component / SKU</th><th>Additional quantity</th><th>Affected systems</th></tr></thead><tbody>@forelse(array_filter($plan['components'], fn($row) => $row['shortage'] !== '0') as $row)<tr><td>{{ $row['name'] }} · {{ $row['sku'] }}</td><td>{{ $row['shortage'] }}</td><td>{{ implode(', ', $row['affected_models'] ?? array_column($row['consumers'], 'name')) }}</td></tr>@empty<tr><td colspan="3" class="text-muted text-center">No additional materials required for this plan.</td></tr>@endforelse</tbody></table></div></section>
  @php($exportData = ['number' => $estimate->number, 'title' => $estimate->title, 'date' => $estimate->plan_date, 'plan' => $plan])
  <script type="application/json" id="plan-export-data">@json($exportData)</script>
</div>
@endsection
@section('page-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>
<script src="{{ asset('brand/estimation.js') }}?v={{ is_file(public_path('brand/estimation.js')) ? filemtime(public_path('brand/estimation.js')) : '1' }}" defer></script>
@endsection
