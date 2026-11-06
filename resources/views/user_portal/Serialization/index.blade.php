@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Serialization Info List')

@section('vendor-styles')
<link rel="stylesheet" href="{{ asset('vendors/css/forms/select/select2.min.css') }}">
@endsection

@section('content')
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
          <span class="erp-list-tools-status">{{ number_format($machines->total()) }} matching records · Combine fields to narrow results. Filters and printing include all pages.</span>
        </form>
        <div class="table-responsive">
          <table class="table" id="example">
            <thead>
              <tr style="color:black">
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
            <tbody style="color: black">
              @foreach($machines as $key => $machine)
                <tr>
                  <td>{{ $machines->firstItem() + $key }}</td>
                  <td>{{$machine->serial_number}}</td>
                  <td>{{date('d-M-Y', strtotime($machine->date))}}</td>
                  <td style="min-width:80px">{{$machine->model}}</td>
                  <td style="min-width:80px">{{$machine->dealer_name}}</td>
                  <td style="min-width:80px">{{$machine->dealer_city}}</td>
                  <td style="min-width:80px">{{$machine->customer_name}}</td>
                  <td style="min-width:80px">{{$machine->customer_city}}</td>
                  <td style="min-width:80px">{{$machine->delivery_boy}}</td>
                </tr>
              @endforeach              
            </tbody>
          </table>
        </div>
        {{ $machines->links() }}
      </div>
    </div>
  </div>
</div>
<!-- Column selectors with Export Options and print table -->

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
