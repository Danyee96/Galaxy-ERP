@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Resolved Complains')

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
                <th style="min-width:150px">Exit<br>Date</th>
                <th style="min-width:150px">Total Days in Factory</th>
              </tr>
            </thead>
            <tbody style="color: black">
              @foreach($machine_histories as $key => $machine_history)
                <tr>
                  <td>{{ ++$key}}</td>
                  <td>{{$machine_history->Item->serial_number}}</td>
                  <td>{{$machine_history->name}}</td>
                  <td>{{$machine_history->Company->name}}</td>
                  <td style="min-width:70px">{{$machine_history->Power ? $machine_history->Power->name : ''}}</td>
                  <td style="min-width:80px">{{$machine_history->model}}</td>
                  <td style="min-width:60px">{{$machine_history->Error ? $machine_history->Error->name : ''}}</td>
                  <td style="min-width: 140px">{{$machine_history->error_description}}</td>
                  <td style="min-width:150px">{{$machine_history->entry_date}}</td>
                  <td style="min-width:150px">{{$machine_history->exit_date}}</td>
                  <?php
                      $to = \Carbon\Carbon::parse($machine_history->exit_date);
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
  function restaurantTime() {
    var time = $("#from").val();

    $("#to").attr({
      "min" : time,
    });
  }
</script>

@endsection
