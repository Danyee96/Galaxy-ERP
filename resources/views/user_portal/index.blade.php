@extends('layouts.contentLayoutMaster')
@section('title', 'Dashboard')
@section('vendor-styles')
<link rel="stylesheet" href="{{ asset('vendors/css/charts/apexcharts.css') }}">
@endsection

@section('content')
<div class="erp-dashboard" id="dashboard-ecommerce" data-legacy-charts="{{ is_file(public_path('js/custom_charts/chart-one.js')) ? 'true' : 'false' }}">
  <x-erp.page-header title="Operations overview" description="Service workload, repair progress, and case distribution." eyebrow="Galaxy Inverter / Workspace">
    @if(Auth::user()->role_id == 1)
    <x-slot name="actions">
      <a class="btn btn-outline-primary" href="{{ route('reporting') }}"><i class="bx bx-file" aria-hidden="true"></i> View reports</a>
      <a class="btn btn-primary" href="{{ route('searchMachine') }}"><i class="bx bx-search" aria-hidden="true"></i> Find a machine</a>
    </x-slot>
    @endif
  </x-erp.page-header>
  @if(($overdueTransitCount ?? 0) > 0)
  <div class="alert alert-danger" role="status"><strong>{{ $overdueTransitCount }} overdue in-transit {{ $overdueTransitCount == 1 ? 'shipment' : 'shipments' }}.</strong> Awaiting full receipt. <a class="alert-link" href="{{ route('inTransitInventory', ['overdue' => 1]) }}">Review delayed inventory</a></div>
  @endif

  @if(Auth::user()->role_id == 1)
  <section class="erp-workload" aria-labelledby="erp-workload-title">
    <div class="erp-section-heading"><h2 id="erp-workload-title">Service workload</h2><span>All time</span></div>
    <dl class="erp-stat-grid">
      <x-erp.stat label="Total cases" :value="$total_complains" caption="All logged service cases" />
      <x-erp.stat label="Pending repairs" :value="$total_pending_complains" :href="route('pending-overall-complains')" tone="warning" caption="Cases awaiting resolution" />
      <x-erp.stat label="Resolved cases" :value="$total_completed_complains" :href="route('resolved-overall-complains')" tone="success" caption="Completed service cases" />
    </dl>
    <div class="erp-monthly">
      <div class="erp-monthly-label"><h3>{{ now()->format('F Y') }}</h3><span>Cases logged this month</span></div>
      <dl class="erp-monthly-stats">
        <x-erp.stat label="Total" :value="$total_monthly_complains" />
        <x-erp.stat label="Pending" :value="$total_monthly_pending_complains" :href="route('pending-monthly-complains')" tone="warning" />
        <x-erp.stat label="Resolved" :value="$total_monthly_completed_complains" :href="route('resolved-monthly-complains')" tone="success" />
      </dl>
    </div>
  </section>

  <x-erp.chart-panel id="delivery-comparison-chart" title="Service intake & deliveries" :description="now()->format('F Y').' · Daily case volumes'" type="line" :labels="array_values($monthly_days_arr)" :series="[['name' => 'Intake', 'data' => array_values($monthly_in_count_arr)], ['name' => 'Delivered', 'data' => array_values($monthly_out_count_arr)]]" empty="No service activity recorded for this period." />

  <div class="erp-section-heading erp-breakdown-heading"><h2>Service breakdown</h2><span>All logged cases</span></div>
  <div class="erp-chart-grid">
    <x-erp.chart-panel id="pie-chart-custom" title="Cases by company" description="Distribution across registered companies" type="pie" :labels="array_values($company_name)" :series="[['name' => 'Cases', 'data' => array_values($company_data)]]" />
    <x-erp.chart-panel id="donut-chart-custom" title="Cases by power rating" description="Distribution across power ratings" :labels="array_values($power_name)" :series="[['name' => 'Cases', 'data' => array_values($power_data)]]" />
    <x-erp.chart-panel id="pie-chart-custom-one" title="Reported faults" description="Cases grouped by error code" type="pie" :labels="array_values($error_name)" :series="[['name' => 'Cases', 'data' => array_values($error_data)]]" />
    <x-erp.chart-panel id="donut-chart-custom-one" title="Accessories received" description="Accessory records associated with service cases" :labels="array_values($accessory_name)" :series="[['name' => 'Records', 'data' => array_values($accessory_data)]]" />
  </div>
  @else
    <section class="erp-panel erp-welcome"><i class="bx bx-grid-alt" aria-hidden="true"></i><h2>Welcome, {{ Auth::user()->name }}</h2><p>Your workspace is ready. Choose a module from the navigation, or search for a page above.</p></section>
  @endif
</div>
@endsection

@section('vendor-scripts')
<script src="{{ asset('vendors/js/charts/apexcharts.min.js') }}"></script>
@endsection
@section('page-scripts')
{{-- Retain the existing chart contract when a deployed initializer is available. --}}
<script>
var company_data = Object.values(@json($company_data)), company_name = Object.values(@json($company_name));
var power_data = Object.values(@json($power_data)), power_name = Object.values(@json($power_name));
var error_data = Object.values(@json($error_data)), error_name = Object.values(@json($error_name));
var accessory_data = Object.values(@json($accessory_data)), accessory_name = Object.values(@json($accessory_name));
var monthly_days_arr = Object.values(@json($monthly_days_arr)), monthly_in_count_arr = Object.values(@json($monthly_in_count_arr)), monthly_out_count_arr = Object.values(@json($monthly_out_count_arr));
</script>
@if(is_file(public_path('js/custom_charts/chart-one.js')))
<script src="{{ asset('js/custom_charts/chart-one.js') }}"></script>
@else
<script src="{{ asset('brand/erp-dashboard.js') }}?v={{ is_file(public_path('brand/erp-dashboard.js')) ? filemtime(public_path('brand/erp-dashboard.js')) : '1' }}" defer></script>
@endif
@endsection
