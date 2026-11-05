@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Add Machine Info')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection
{{-- page-styles --}}
@section('page-styles')
<link rel="stylesheet" type="text/css" href="{{asset('css/plugins/forms/validation/form-validation.css')}}">
@endsection

@section('content')
<!-- Simple Validation start -->
<section class="simple-validation">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Add Machine Info</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveMachineInfo')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Scan Serial Number</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="serial_number" readonly
                    placeholder="12123" required value="{{$serial_number->serial_number}}"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Customer Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name alpha_characters"
                    name="customer_name" maxlength="100"
                    placeholder="John Doe" required
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="select-company">Select Company</label>
                  <select class="form-control select2" id="select-company" name="company_id" required disabled>
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                    <option value="{{$company->id}}" {{ $company->id == $serial_number->company_id ? 'selected' : ''}}>{{$company->name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Entry Date</label>
                  <input
                    type="date"
                    class="form-control"
                    id="basic-default-name"
                    name="entry_date" readonly min="{{$today_date}}" max="{{$today_date}}" value="{{$today_date}}"
                    placeholder="dd/mm/yyyy"
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-phone-number">Phone Number</label>
                  <input
                    type="text"
                    class="form-control numeric_characters"
                    id="basic-default-phone-number"
                    name="phone" maxlength="11" minlength="11"
                    placeholder="03xx-xxxxxxx" 
                  />
                </div>
              </div>
            </div>
            <!-- <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="select-power">Select Power</label>
                  <select class="form-control select2" id="select-power" name="power_id" required disabled>
                    <option value="">Select Power</option>
                    @foreach($powers as $power)
                    <option value="{{$power->id}}" {{ $power->id == $serial_number->power_id ? 'selected' : ''}}>{{$power->name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="select-error">Select Error</label>
                  <select class="form-control select2" id="select-error" name="error_id" required>
                    <option value="">Select Error</option>
                    @foreach($errors as $error)
                    <option value="{{$error->id}}">{{$error->name}} - {{$error->description}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="select-error">Select User</label>
                  <select class="form-control select2" id="select-user" name="user" required>
                    <option value="">Select User</option>
                    @foreach($users as $user)
                    <option value="{{$user->id}}">{{$user->name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div> -->
            <!-- <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-description">Error Description</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-description"
                    name="error_description"
                    placeholder="Description" required
                  />
                </div>
              </div>
            </div> -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-maker">Production Date</label>
                  <input
                    type="date"
                    class="form-control"
                    id="basic-default-maker"
                    name="production_date" max="{{$today_date}}"
                    placeholder="YYYY-mm-dd" required value="{{$serial_number->production_date}}" disabled
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-model">Model</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-model"
                    name="model" maxlength="100"
                    placeholder="ABC" required value="{{$serial_number->model}}" disabled
                  />
                </div>
              </div>
            </div>
            <hr>
            <h5>Select Accessories</h5>
            <div class="row">
              @foreach($accessories as $accessory)
              <div class="col-md-3">
                <div class="form-group">
                  <input type="checkbox"  id="accessory" name="accessory[]" value="{{ $accessory->id }}" aria-invalid="false">
                  <label class="form-label" for="basic-default-accessory">{{ $accessory->name }}</label>
                </div>
              </div>
              @endforeach
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
