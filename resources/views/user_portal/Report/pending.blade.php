@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Pending Complains')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/buttons.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection

@section('content')

<!-- Column selectors with Export Options and print table -->
<div class="users-list-table">
  <div class="card">
    <div class="card-content">
      <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-2">
          <div class="col-md-6">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by Serial Number, Customer Name, Error Code...">
          </div>
          <div class="col-md-6">
            <select id="packingStatusFilter" class="form-control">
              <option value="">All Packing Status</option>
              <option value="0">Not Packed</option>
              <option value="1">Packed</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr style="color:black">
                <th>Sr.</th>
                <th>Serial Number</th>
                <th>Customer<br>Name</th>
                <th>Company<br>Name</th>
                <th style="min-width:70px">Power</th>
                <th style="min-width:80px">Model</th>
                <th style="min-width:60px">Error Code</th>
                <th style="min-width: 140px">Error Discription</th>
                <th style="min-width:150px">Entry<br>Date</th>
                <th style="min-width:150px">Packing Status</th>
                <th style="min-width:150px">Items Along with</th>
                <th style="min-width:150px">Total Days in Factory</th>
              </tr>
            </thead>
            <tbody style="color: black">
              @foreach($machine_histories as $key => $machine_history)
                <tr class="filterRow" data-serial="{{$machine_history->Item->serial_number}}" data-customer="{{$machine_history->name}}" data-company="{{$machine_history->Company->name}}" data-error-code="{{$machine_history->Error ? $machine_history->Error->name : ''}}" data-packing="{{$machine_history->packing_status}}">
                  <td>{{ ++$key}}</td>
                  <td>{{$machine_history->Item->serial_number}}</td>
                  <td>{{$machine_history->name}}</td>
                  <td>{{$machine_history->Company->name}}</td>
                  <td style="min-width:70px">{{$machine_history->Power ? $machine_history->Power->name : ''}}</td>
                  <td style="min-width:80px">{{$machine_history->model}}</td>
                  <td style="min-width:60px">{{$machine_history->Error ? $machine_history->Error->name : ''}}</td>
                  <td style="min-width: 140px">{{$machine_history->error_description}}</td>
                  <td style="min-width:150px">{{$machine_history->entry_date}}</td>
                  <td style="min-width:150px">
                    @if($machine_history->packing_status == 0)
                      <span class="badge badge-danger">Not Packed</span>
                    @else
                      <span class="badge badge-success">Packed</span>
                    @endif
                  </td>
                  <td style="min-width: 140px">
                    @foreach($machine_history->machineAccessories as $key => $accessory)
                      {{$key+1}}. {{$accessory->Accessory ? $accessory->Accessory->name : ''}}<br>
                    @endforeach
                  </td>
                  <?php
                      $to = \Carbon\Carbon::parse($today_date);
                      $from = \Carbon\Carbon::parse($machine_history->entry_date);
                      $days = $to->diffInDays($from);
                    ?>
                  <td style="min-width:150px">{{$days}}</td>
                </tr>
              @endforeach              
            </tbody>
          </table>
        </div>
        <!-- datatable ends -->
      </div>
    </div>
  </div>
</div>
<!-- Column selectors with Export Options and print table -->

@endsection
{{-- vendor scripts --}}
@section('vendor-scripts')
<script src="{{asset('vendors/js/forms/select/select2.full.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/buttons.html5.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/buttons.print.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/pdfmake.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/vfs_fonts.js')}}"></script>
@endsection
{{-- page scripts --}}
@section('page-scripts')
<script type="text/javascript">
  // Wait for jQuery to be loaded
  function waitForJQuery() {
    if (typeof jQuery === 'undefined') {
      setTimeout(waitForJQuery, 50);
      return;
    }

    var $ = jQuery;

    function restaurantTime() {
      var time = $("#from").val();

      $("#to").attr({
        "min" : time,
      });
    }

    function applyFilters() {
      var searchValue = $("#searchInput").val().toLowerCase().trim();
      var packingStatus = $("#packingStatusFilter").val();

      $(".filterRow").each(function () {
        var row = $(this);
        var serial = (row.data("serial") || "").toString().toLowerCase().trim();
        var customer = (row.data("customer") || "").toString().toLowerCase().trim();
        var company = (row.data("company") || "").toString().toLowerCase().trim();
        var errorCode = (row.data("error-code") || "").toString().toLowerCase().trim();
        var packing = (row.data("packing") || "").toString().trim();

        var searchMatch = searchValue === "" || 
                          serial.includes(searchValue) || 
                          customer.includes(searchValue) || 
                          company.includes(searchValue) || 
                          errorCode.includes(searchValue);

        var packingMatch = packingStatus === "" || packing === packingStatus;

        if (searchMatch && packingMatch) {
          row.show();
        } else {
          row.hide();
        }
      });
    }

    $(document).ready(function () {
      $.noConflict();

      // Initialize filters on page load
      applyFilters();

      // Filter event listeners
      $("#searchInput").on("keyup", function () {
        applyFilters();
      });

      $("#packingStatusFilter").on("change", function () {
        applyFilters();
      });

      setTimeout(function () {
          location.reload();
      }, 100000);

    });
  }

  // Start waiting for jQuery
  waitForJQuery();
</script>

@endsection
