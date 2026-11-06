@extends('layouts.contentLayoutMaster')
@section('title', 'Production planning')
@section('vendor-styles')
<link rel="stylesheet" href="{{ asset('vendors/css/forms/select/select2.min.css') }}">
@endsection
@section('page-styles')
<link rel="stylesheet" href="{{ asset('brand/estimation.css') }}?v={{ filemtime(public_path('brand/estimation.css')) }}">
@endsection
@section('content')
<div class="erp-planner" id="production-planner" data-preview-url="{{ route('productionEstimation.preview') }}" data-save-url="{{ route('productionEstimation.store') }}">
  <x-erp.page-header title="Production estimation" description="Combine system recipes, compare capacity, and plan material requirements." eyebrow="Manufacturing / Planning">
    <x-slot name="actions"><button type="button" class="btn btn-outline-primary" id="plan-print" disabled>Print</button><button type="button" class="btn btn-primary" id="plan-export-excel" disabled>Export Excel</button><a class="btn btn-outline-primary" href="{{ route('productEstimates') }}">Saved estimates</a></x-slot>
  </x-erp.page-header>
  <p class="planner-policy">Planning only. Saving a production plan does not reserve stock, deduct inventory, start production, or create purchase orders.</p>
  <form id="planner-form">
    @if(config('warehouses.enabled'))
    <div class="erp-panel p-2 mb-2"><label for="plan-warehouses">Warehouse scope</label><select class="form-control" id="plan-warehouses" multiple size="3">@foreach(\App\Models\Warehouse\Warehouse::where('active',1)->where('code','!=','INTERNAL-TRANSIT')->orderBy('name')->get() as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>@endforeach</select><small>Select none for all active warehouses, one for a single warehouse, or several to combine stock. No inventory is reserved.</small></div>
    @endif
    <div class="erp-panel planner-heading">
      <div><label for="plan-title">Estimation title</label><input class="form-control" id="plan-title" name="title" required maxlength="255" placeholder="e.g. November production mix"></div>
      <div><label for="plan-number">Estimate number</label><input class="form-control" id="plan-number" value="Assigned on save" readonly><small>Numbers are assigned safely by the server.</small></div>
      <div><label for="plan-date">Planning date</label><input class="form-control" type="date" id="plan-date" name="plan_date" value="{{ now()->format('Y-m-d') }}" required></div>
      <div><label for="plan-mode">Allocation method</label><select id="plan-mode" name="mode" class="form-control"><option value="manual">Manual production</option><option value="priority">Priority allocation</option><option value="balanced">Balanced ratio</option></select></div>
    </div>
    <div class="planner-workspace">
      <section class="erp-panel planner-models" aria-labelledby="planner-models-title">
        <header class="erp-panel-header"><div><h2 id="planner-models-title">Systems to manufacture</h2><p>Search bundle models. Shared components use one stock pool.</p></div><button type="button" class="btn btn-outline-primary" id="planner-add">Add model</button></header>
        <p class="planner-rule" id="planner-rule">Enter requested whole units. Row order determines the feasible allocation when the complete request cannot be met.</p>
        <div id="planner-rows"></div>
        <div class="planner-model-footer"><small>Up to 100 distinct models per plan. Move rows to change priority. Ratios are positive integer weights.</small><button type="button" class="btn btn-outline-primary" id="planner-recalculate">Recalculate</button></div>
      </section>
      <aside class="erp-panel planner-summary" aria-labelledby="planner-summary-title">
        <header class="erp-panel-header"><h2 id="planner-summary-title">Production summary</h2></header>
        <div class="planner-summary-body">
          <p id="planner-status" role="status" aria-live="polite">Select a system to calculate a plan.</p>
          <dl class="planner-totals"><div><dt>Requested units</dt><dd id="planner-requested">—</dd></div><div><dt>Planned units</dt><dd id="planner-planned">—</dd></div><div><dt>Feasible whole units</dt><dd id="planner-feasible">—</dd></div><div><dt>Materials to purchase</dt><dd id="planner-shortages">—</dd></div></dl>
          <p class="planner-help" id="planner-bottlenecks"></p>
          <button type="button" class="btn btn-outline-primary" id="planner-use-proposal" hidden>Adjust proposal in manual mode</button>
          <button type="submit" class="btn btn-primary" id="planner-save" disabled>Save production plan</button>
          <small>Inventory and recipes are checked again when saving. A changed plan must be reviewed before retrying.</small>
        </div>
      </aside>
    </div>
  </form>
  <div id="planner-error" class="alert alert-danger" role="alert" tabindex="-1" hidden></div>
  <section class="erp-panel planner-results" aria-labelledby="planner-capacity-title">
    <header class="erp-panel-header"><div><h2 id="planner-capacity-title">System capacity</h2><p>Maximum alone uses the entire usable stock for one model. Those maxima cannot be added together.</p></div></header>
    <div class="table-responsive" tabindex="0" role="region" aria-label="System capacity results"><table class="table"><thead><tr><th>System / SKU</th><th>Requested</th><th>Planned</th><th>Feasible in mix</th><th>Maximum alone</th><th>Standalone bottleneck</th><th>Shortage materials</th></tr></thead><tbody id="planner-capacity"><tr><td colspan="7" class="planner-empty">Your selected systems will appear here.</td></tr></tbody></table></div>
  </section>
  <section class="erp-panel planner-results" aria-labelledby="planner-materials-title">
    <header class="erp-panel-header"><div><h2 id="planner-materials-title">Combined material requirements</h2><p id="planner-stock-policy">Usable stock excludes active legacy committed demand and future transit. Projected stock is shown separately, never subtracted twice.</p></div></header>
    <div class="table-responsive" tabindex="0" role="region" aria-label="Combined component requirements"><table class="table"><thead><tr><th>Component / SKU</th><th>Physical stock</th><th>Legacy committed</th><th>Usable</th><th>Total required</th><th>Allocated to feasible mix</th><th>Remaining</th><th>Shortage</th><th>Consuming systems</th></tr></thead><tbody id="planner-materials"><tr><td colspan="9" class="planner-empty">Select models to view their combined requirements.</td></tr></tbody></table></div>
  </section>
  <section class="erp-panel planner-results" id="planner-purchase-section" aria-labelledby="planner-purchase-title" hidden>
    <header class="erp-panel-header"><div><h2 id="planner-purchase-title">Purchase requirements</h2><p>Additional material needed for the complete selected plan. No purchase orders are created.</p></div></header>
    <div class="table-responsive" tabindex="0" role="region" aria-label="Purchase requirements"><table class="table"><thead><tr><th>Component / SKU</th><th>Additional quantity required</th><th>Affected models</th></tr></thead><tbody id="planner-purchases"></tbody></table></div>
  </section>
  <p class="planner-help" id="planner-allocation-rule"></p>
  <template id="planner-row-template"><div class="planner-model-row"><span class="planner-position" aria-hidden="true"></span><div class="planner-model-field"><label>System model</label><select class="form-control planner-model"><option value="">Search and select a system</option>@foreach($all_products as $product)<option value="{{ $product->id }}">{{ $product->name }}{{ $product->sku ? ' · '.$product->sku : '' }}</option>@endforeach</select></div><div><label>Requested units</label><input type="number" class="form-control planner-quantity" value="1" min="0" max="1000000" step="1"></div><div class="planner-weight-field"><label>Ratio weight</label><input type="number" class="form-control planner-weight" value="1" min="1" max="1000" step="1"></div><div class="planner-row-actions"><button class="erp-icon-button" type="button" data-row-action="up" aria-label="Move model up"><i class="bx bx-up-arrow-alt" aria-hidden="true"></i></button><button class="erp-icon-button" type="button" data-row-action="down" aria-label="Move model down"><i class="bx bx-down-arrow-alt" aria-hidden="true"></i></button><button class="erp-icon-button" type="button" data-row-action="remove" aria-label="Remove model"><i class="bx bx-x" aria-hidden="true"></i></button></div></div></template>
</div>
@endsection
@section('vendor-scripts')
<script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>
@endsection
@section('page-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>
<script src="{{ asset('brand/estimation.js') }}?v={{ filemtime(public_path('brand/estimation.js')) }}" defer></script>
@endsection
