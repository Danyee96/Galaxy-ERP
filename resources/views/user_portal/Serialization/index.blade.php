@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Serialization Info List')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/buttons.bootstrap4.min.css')}}">
@endsection

@section('content')
@if(session()->has('message'))
  <div class="alert alert-success">
    {{ session()->get('message') }}
  </div>
@endif
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
                  <td>{{ ++$key}}</td>
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
