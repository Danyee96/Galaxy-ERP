@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','View Finished Goods Sale')

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
<section id="column-selectors">
  <div class="row">
    <div class="col-12">
      <div class="card">
          <div class="table-responsive">
        <div class="card-body card-dashboard">
            <table class="table table-striped ">
              <thead>
                <tr>
                  <th>Id</th>
                  <th>Item Name</th>
                  <th>Company Name</th>
                  <th>Customer Name</th>
                  <th>Sale Quantity</th>
                  <th>Power</th>
                  <th>Model</th>
                  <th>Production Date</th>
                </tr>
              </thead>
              <tbody>
                @foreach($finished_good_sales as $key => $finished_good_sale)
                <tr>
                  <td>{{++$key}}</td>
                  <td>{{$finished_good_sale->finishedGoods->name}}</td>
                  <td>{{$finished_good_sale->companyName->name}}</td>
                  <td>{{$finished_good_sale->customer_name}}</td>
                  <td>{{number_format($finished_good_sale->sale_quantity)}}</td>
                  <td>{{$finished_good_sale->powerName->name}}</td>
                  <td>{{$finished_good_sale->model}}</td>
                  <td>{{$finished_good_sale->sale_date}}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
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
@endsection
