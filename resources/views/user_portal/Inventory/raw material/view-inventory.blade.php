@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','View Inventory')

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
                  <th>Name</th>
                  <th>Transit Method</th>
                  <th>Purchase Quantity</th>
                  <th>Remarks</th>
                  <th>Purchased By</th>
                  <th>Received By</th>
                  <th>Purchase Date</th>
                </tr>
              </thead>
              <tbody>
                @foreach($raw_materials as $key => $raw_material)
                <tr>
                  <td>{{++$key}}</td>
                  <td>{{$raw_material->rawMaterial->name}}</td>
                  <td>{{$raw_material->transitMethod->name}}</td>
                  <td>{{number_format($raw_material->stock_in)}}</td>
                  <td>{{$raw_material->remarks}}</td>
                  <td>{{$raw_material->entryBy->name}}</td>
                  <td>{{$raw_material->receivedBy ? $raw_material->receivedBy->name : ''}}</td>
                  <td>{{$raw_material->created_at->format("d-M-Y")}}</td>
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
