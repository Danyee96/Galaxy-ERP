@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Serialization Info List')

@section('vendor-styles')
<link rel="stylesheet" href="{{ asset('vendors/css/forms/select/select2.min.css') }}">
@endsection

@section('content')
<div class="serialization-workspace">
<header class="serialization-heading">
  <div class="serialization-heading-copy"><span class="serialization-heading-icon" aria-hidden="true"><i class="bx bx-barcode"></i></span><div><p class="serialization-eyebrow">OPERATIONS / SERIALIZATION</p><h1>Serialization register</h1><p>Find machines, trace deliveries and review customer records.</p></div></div>
  <a class="btn btn-primary" href="{{ route('create-serialization-data') }}"><i class="bx bx-plus" aria-hidden="true"></i> Add serialization</a>
</header>
@if(session()->has('message'))
  <div class="alert alert-success">
    {{ session()->get('message') }}
  </div>
@endif
<!-- Column selectors with Export Options and print table -->
<div class="users-list-table" data-list-tools="off">
  <div class="card">
    <div class="card-content">
      <div class="card-body">
        @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
        <details class="erp-filter-panel serialization-filter-panel" @if($errors->any()) open @endif>
        <summary class="serialization-filter-heading"><div><i class="bx bx-filter-alt" aria-hidden="true"></i><strong>Filter records</strong><span data-filter-active>All records</span></div><span class="serialization-count">{{ number_format($machines->total()) }} matches</span></summary>
        <form method="get" action="{{ route('serialization-list') }}" class="erp-list-tools erp-field-filters">
          <label>Search records<input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search all records…"></label>
          <label>Serial Number<input type="search" name="serial_number" value="{{ $filters['serial_number'] ?? '' }}" placeholder="Any serial number"></label>
          <label>From Date<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
          <label>To Date<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
          @foreach($values as $key=>$options)
          <label>{{ $columns[$key] }}<select name="{{ $key }}" aria-label="{{ $columns[$key] }}"><option value="">Any</option>@foreach($options as $value)<option value="{{ $value }}" @if(($filters[$key] ?? '')===$value) selected @endif>{{ $value }}</option>@endforeach</select></label>
          @endforeach
          <label>Sort by<select name="sort" aria-label="Sort by"><option value="">Original order</option>@foreach($columns as $key=>$label)<option value="{{ $key }}" @if(($filters['sort'] ?? '')===$key) selected @endif>{{ $label }}</option>@endforeach</select></label>
          <label>Order<select name="direction" aria-label="Order"><option value="asc">Ascending</option><option value="desc" @if(($filters['direction'] ?? '')==='desc') selected @endif>Descending</option></select></label>
          <div class="erp-field-filter-actions">
            <button class="btn btn-sm btn-primary" type="submit">Apply filters</button>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('serialization-list') }}">Reset</a>
            <button class="btn btn-sm btn-outline-primary" type="submit" name="print" value="1" formtarget="_blank">Print filtered data</button>
          </div>
          <span class="erp-list-tools-status"><i class="bx bx-info-circle" aria-hidden="true"></i> Search and print include all matching records, across every page.</span>
        </form>
        </details>
        <div class="serialization-results-heading"><div><h2>Registered machines</h2><span>Showing {{ $machines->firstItem() ?? 0 }}–{{ $machines->lastItem() ?? 0 }} of {{ number_format($machines->total()) }} records</span></div><span class="serialization-live">Page {{ $machines->currentPage() }} of {{ $machines->lastPage() }}</span></div>
        <div class="table-responsive serialization-table-scroll" tabindex="0" role="region" aria-label="Serialization records">
          <table class="table" id="example">
            <thead>
              <tr>
                <th>Sr.</th>
                <th style="min-width: 80px">Serial Number</th>
                <th style="min-width: 100px">Date</th>
                <th style="min-width: 80px">Model</th>
                <th style="min-width: 80px">Dealer Name</th>
                <th style="min-width: 80px">Dealer City</th>
                <th style="min-width: 80px">Customer Name</th>
                <th style="min-width: 80px">Customer City</th>
                <th style="min-width: 80px">Delivery Boy</th>
              </tr>
            </thead>
            <tbody>
              @forelse($machines as $key => $machine)
                <tr>
                  <td>{{ $machines->firstItem() + $key }}</td>
                  <td><span class="serialization-serial">{{$machine->serial_number}}</span></td>
                  <td>{{date('d-M-Y', strtotime($machine->date))}}</td>
                  <td style="min-width:80px"><span class="serialization-model">{{$machine->model}}</span></td>
                  <td style="min-width:80px">{{ $machine->dealer_name ?: '—' }}</td>
                  <td style="min-width:80px">{{ $machine->dealer_city ?: '—' }}</td>
                  <td style="min-width:80px">{{ $machine->customer_name ?: '—' }}</td>
                  <td style="min-width:80px">{{ $machine->customer_city ?: '—' }}</td>
                  <td style="min-width:80px">{{ $machine->delivery_boy ?: '—' }}</td>
                </tr>
              @empty<tr><td colspan="9"><div class="serialization-empty"><i class="bx bx-search-alt" aria-hidden="true"></i><strong>No matching records</strong><span>Try another model, city or date range.</span></div></td></tr>@endforelse
            </tbody>
          </table>
        </div>
<div class="serialization-pagination">{{ $machines->links('pagination::bootstrap-4') }}</div>
      </div>
    </div>
  </div>
</div>
<!-- Column selectors with Export Options and print table -->
</div>
@endsection

@section('vendor-scripts')
<script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>
@endsection
@section('page-scripts')
<script>
  $('.erp-field-filters select').select2({width:'100%', minimumResultsForSearch:0, dropdownCssClass:'erp-filter-options'});
  $('.erp-field-filters select').on('select2:open',function(){
    setTimeout(function(){document.querySelector('.select2-container--open .select2-search__field')?.focus();},0);
  });
</script>
@endsection
