@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Machine List')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/buttons.bootstrap4.min.css')}}">
@endsection

@section('content')

<!-- Column selectors with Export Options and print table -->
<div class="users-list-table">
  <div class="card">
    <div class="card-content">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table zero-configuration" id="example">
            <thead>
              <tr style="color:black">
                <th>Sr.</th>
                <th>Serial Number</th>
                <th>Company<br>Name</th>
                <th style="min-width:70px">Power</th>
                <th style="min-width:80px">Model</th>
                <th style="min-width: 140px">Production<br>Date</th>
                <th style="min-width: 70px">Warranty<br>Years</th>
                <th style="min-width:100px">Warranty<br>Check</th>
              </tr>
            </thead>
            <tbody style="color: black">
              @foreach($machines as $key => $machine)
                <tr>
                  <td>{{ ++$key}}</td>
                  <td>{{$machine->serial_number}}</td>
                  <td>{{$machine->Company->name}}</td>
                  <td style="min-width:70px">{{$machine->Power->name}}</td>
                  <td style="min-width:80px">{{$machine->model}}</td>
                  <?php
                      $to = \Carbon\Carbon::parse($today_date);
                      $from = \Carbon\Carbon::parse($machine->production_date);
                      $warranty_year = $machine->warranty_years * 365;
                      $days = $to->diffInDays($from);
                  ?>
                  <td style="min-width: 140px">{{$from->format('d-M-Y')}}</td>
                  <td style="min-width: 70px">{{$machine->warranty_years}}</td>
                  @if($days < $warranty_year)
                  <td style="min-width:100px; color: green;">Active</td>
                  @else
                  <td style="min-width:100px; color: red;">Expired</td>
                  @endif
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
<script src="{{asset('js/scripts/datatables/datatable.js')}}"></script>
<script type="text/javascript">
  var table = $('#example').DataTable({
    "lengthMenu": [ 10, 25, 50, 75, 100, 500, 1000, 5000 ]
  });
</script>
@endsection
