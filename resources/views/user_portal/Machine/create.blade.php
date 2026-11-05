@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Create Machine Info')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection
{{-- page-styles --}}
@section('page-styles')
<link rel="stylesheet" type="text/css" href="{{asset('css/plugins/forms/validation/form-validation.css')}}">
@endsection

@section('content')
@if(session()->has('message'))
  <div class="alert alert-success">
    {{ session()->get('message') }}
  </div>
@endif
<!-- Simple Validation start -->
<section class="simple-validation">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Please Create New Machine Info</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveNewCreateMachineInfo')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Scan Serial Number</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="serial_number" autofocus
                    placeholder="12123" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="select-company">Select Company</label>
                  <select class="form-control select2" id="select-company" name="company_id" required>
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                    <option value="{{$company->id}}">{{$company->name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="select-power">Select Power</label>
                  <select class="form-control select2" id="select-power" name="power_id" required>
                    <option value="">Select Power</option>
                    @foreach($powers as $power)
                    <option value="{{$power->id}}">{{$power->name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="basic-default-maker">Production Date</label>
                  <input
                    type="date"
                    class="form-control"
                    id="basic-default-maker"
                    name="production_date" max="{{$today_date}}"
                    placeholder="YYYY-mm-dd" required
                  />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="for-years">Select Warranty Years</label>
                  <select class="form-control select2" id="select-year" name="warranty_year" required>
                    <option value="">Select Warranty Year</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="basic-default-model">Model</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-model"
                    name="model" maxlength="100"
                    placeholder="ABC" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary" name="submit" value="Submit">Save</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Input Validation end -->
@endsection

{{-- vendor scripts --}}
@section('vendor-scripts')
<script src="{{asset('vendors/js/forms/select/select2.full.min.js')}}"></script>
<script src="{{asset('vendors/js/forms/validation/jquery.validate.min.js')}}"></script>
@endsection

{{-- page scripts --}}
@section('page-scripts')
<script src="{{asset  ('js/scripts/forms/validation/form-validation.js')}}"></script>
<script type="text/javascript">
  $('.numeric_characters').bind('input', function() {
    var c = this.selectionStart,
        r = /[^0-9 +]/gi,
        v = $(this).val();
    if(r.test(v)) {
      $(this).val(v.replace(r, ''));
      c--;
    }
    this.setSelectionRange(c, c);
  });
</script>
<script type="text/javascript">
  $('.alpha_characters').bind('input', function() {
    var c = this.selectionStart,
        r = /[^a-z . -]/gi,
        v = $(this).val();
    if(r.test(v)) {
      $(this).val(v.replace(r, ''));
      c--;
    }
    this.setSelectionRange(c, c);
  });
</script>
@endsection
