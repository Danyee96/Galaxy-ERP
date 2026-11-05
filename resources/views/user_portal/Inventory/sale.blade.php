@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Add Finished Goods Sale')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection
{{-- page-styles --}}
@section('page-styles')
<link rel="stylesheet" type="text/css" href="{{asset('css/plugins/forms/validation/form-validation.css')}}">
@endsection

@section('content')
@if(session()->has('danger'))
  <div class="alert alert-danger">
    {{ session()->get('danger') }}
  </div>
@endif
<!-- Simple Validation start -->
<section class="simple-validation">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Please Add Finished Goods Sale</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveFinishedGoodsSale')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="select-finished-goods">Select Finished Goods</label>
                  <select class="form-control select2" id="select-finished-goods" name="finished_goods_id" required>
                    <option value="">Select Finished Goods</option>
                    @foreach($finished_goods as $finished_good)
                    <option value="{{$finished_good->id}}">{{$finished_good->name}}</option>
                    @endforeach
                  </select>
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
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-phone-number">Sale Quantity</label>
                  <input
                    type="number"
                    class="form-control"
                    id="basic-default-phone-number" required
                    name="sale_quantity" min="0"
                    placeholder="10" 
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Sale Date</label>
                  <input
                    type="date"
                    class="form-control"
                    id="basic-default-name" required
                    name="sale_date" max="{{$today_date}}" value="{{$today_date}}"
                    placeholder="dd/mm/yyyy"
                  />
                </div>
              </div>
            </div>
            <div class="row">
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
              <div class="col-md-6">
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
            <hr>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary" name="submit" value="Submit">Sale</button>
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
