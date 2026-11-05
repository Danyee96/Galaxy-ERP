@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Custom Machine Report')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/buttons.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection

@section('content')

<!-- Column selectors with Export Options and print table -->
<div class="users-list-filter px-1 mt-1">
  <form method="post" action="{{route('custom-machine-report-pdf')}}">
    @csrf
    <div class="row border rounded py-2 mb-2">
      <div class="col-md-3">
        <div class="form-group">
          <label>From Date</label>
          <fieldset class="form-group">
          <input type="date" name="from" id="from" class="form-control" max="{{$today_date}}" required="required" value="<?php if(isset($_GET['from'])) echo $_GET['from']; ?>" onChange="restaurantTime()">
          </fieldset>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>To Date</label>
          <fieldset class="form-group">
          <input type="date" name="to" id="to" class="form-control" max="{{$today_date}}" required="required" value="<?php if(isset($_GET['to'])) echo $_GET['to']; ?>">
          </fieldset>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Company</label>
          <fieldset class="form-group">
            <select class="form-control select2" id="select-company" name="company" required>
              <option value="any">Any</option>
              @foreach($companies as $company)
              <option @isset($_GET['company']) @if($company->id==$_GET['company']) selected @endif @endisset value="{{$company->id}}">{{$company->name}}</option>
              @endforeach
            </select>
          </fieldset>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Power</label>
          <fieldset class="form-group">
            <select class="form-control select2" id="select-power" name="power" required>
              <option value="any">Any</option>
              @foreach($powers as $power)
              <option @isset($_GET['power']) @if($power->id==$_GET['power']) selected @endif @endisset value="{{$power->id}}">{{$power->name}}</option>
              @endforeach
            </select>
          </fieldset>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Error</label>
          <fieldset class="form-group">
            <select class="form-control select2" id="select-error" name="error" required>
              <option value="any">Any</option>
              @foreach($errors as $error)
              <option @isset($_GET['error']) @if($error->id==$_GET['error']) selected @endif @endisset value="{{$error->id}}">{{$error->name}}</option>
              @endforeach
            </select>
          </fieldset>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
        <br>
          <fieldset class="form-group">
            <button type="submit" class="btn btn-primary">Download</button>
          </fieldset>
        </div>
      </div>
    </div>
  </form>
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
