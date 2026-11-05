@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','In-Complete UPS Models List')

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
@if(session()->has('warning'))
  <div class="alert alert-warning">
    {{ session()->get('warning') }}
  </div>
@endif
@if(session()->has('danger'))
  <div class="alert alert-danger">
    {{ session()->get('danger') }}
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
                <th style="min-width: 80px">Model</th>
                <th style="min-width: 80px">Qty Used</th>
                <th style="min-width: 80px">Scanned By</th>
                <th style="min-width: 80px">Add</th>
                <th style="min-width: 80px">Complete</th>
              </tr>
            </thead>
            <tbody style="color: black">
              @foreach($models as $key => $model)
                <tr>
                  <td>{{ ++$key}}</td>
                  <td>{{$model->serial_number}}</td>
                  <td style="min-width:80px">{{$model->modelDetail ? $model->modelDetail->name : 'N/A'}}</td>
                  <td style="min-width:80px">
                    <div class="modal fade text-left" id="backdrop{{$model->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel4">Remaining Machine Qty Detail</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <i class="bx bx-x"></i>
                            </button>
                          </div>
                          <div class="modal-body">
                            @foreach($model->modelMaterial as $key => $modelMaterial)
                              <p><i class="bx bx-stop mr-1"></i> {{$modelMaterial->rawMaterial ? $modelMaterial->rawMaterial->name : 'N/A'}} X {{$modelMaterial->remain_qty}} remains</p>
                            @endforeach
                            <hr>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-dismiss="modal">
                              <i class="bx bx-x d-block d-sm-none"></i>
                              <span class="d-none d-sm-block">Close</span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" data-target="#backdrop{{$model->id}}">Qty Detail</button>
                  </td>
                  <td style="min-width:80px">{{$model->user ? $model->user->name : 'N/A'}}</td>
                  <td style="min-width:80px">
                    <a href="{{route('fetchInCompleteModelRecipeNew', $model->id)}}"><button class="btn btn-warning">Add</button></a>
                  </td>
                  <td style="min-width:80px">
                    <a href="{{route('markCompleteUpsModelNew', $model->id)}}"><button class="btn btn-success">Complete</button></a>
                  </td>
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
