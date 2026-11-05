@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Add Serialization')

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
@if(session()->has('error'))
  <div class="alert alert-danger">
    {{ session()->get('error') }}
  </div>
@endif
<!-- Simple Validation start -->
<section class="simple-validation">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Please Add Serialization Info</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveSerialization')}}" method="POST" enctype="multipart/form-data">
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
                  <label class="form-label" for="select-model">Select Model</label>
                  <select class="form-control select2" id="select-model" name="model" required>
                    <option value="">Select Model</option>
                    <option value="Pluto 5200">Pluto 5200</option>
                    <option value="Pluto 7200">Pluto 7200</option>
                    <option value="Venus 5000">Venus 5000</option>
                    <option value="Venus 6500">Venus 6500</option>
                    <option value="Mercury II">Mercury II</option>
                    <option value="Mercury 2200">Mercury 2200</option>
                    <option value="Mercury 3200">Mercury 3200</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-maker">Date</label>
                  <input
                    type="date"
                    class="form-control"
                    id="basic-default-maker"
                    name="production_date" max="{{$today_date}}" min="{{$today_date}}"
                    placeholder="YYYY-mm-dd" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="basic-default-maker">Dealer Name</label>
                  <input
                    type="text"
                    class="form-control alpha_characters"
                    id="basic-default-model"
                    name="dealer_name" maxlength="100"
                    placeholder="Dealer Name" required
                  />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="for-years">Dealer City</label>
                  <input
                    type="text"
                    class="form-control alpha_characters"
                    id="basic-default-model-t"
                    name="dealer_city" maxlength="100"
                    placeholder="Dealer City"
                  />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="basic-default-model">Delivery Boy</label>
                  <input
                    type="text"
                    class="form-control alpha_characters"
                    id="basic-default-model-d"
                    name="delivery_boy" maxlength="100"
                    placeholder="Delivery Boy" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-maker">Customer Name</label>
                  <input
                    type="text"
                    class="form-control alpha_characters"
                    id="basic-default-model"
                    name="customer_name" maxlength="100"
                    placeholder="Customer Name"
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="for-years">Customer City</label>
                  <input
                    type="text"
                    class="form-control alpha_characters"
                    id="basic-default-model-t"
                    name="customer_city" maxlength="100"
                    placeholder="Customer City"
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
