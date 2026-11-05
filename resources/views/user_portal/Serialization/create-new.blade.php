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
          <form id="jquery-val-form">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="basic-default-maker">Date</label>
                  <input
                    type="date"
                    class="form-control"
                    id="production_date"
                    name="production_date" max="{{$today_date}}" min="{{$today_date}}"
                    placeholder="YYYY-mm-dd" required value="{{$today_date}}"
                  />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="basic-default-maker">Dealer Name</label>
                  <input
                    type="text"
                    class="form-control alpha_characters"
                    id="dealer_name"
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
                    id="dealer_city"
                    name="dealer_city" maxlength="100"
                    placeholder="Dealer City"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="basic-default-model">Delivery Boy</label>
                  <input
                    type="text"
                    class="form-control alpha_characters"
                    id="delivery_boy"
                    name="delivery_boy" maxlength="100"
                    placeholder="Delivery Boy" required
                  />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="basic-default-maker">Customer Name</label>
                  <input
                    type="text"
                    class="form-control alpha_characters"
                    id="customer_name"
                    name="customer_name" maxlength="100"
                    placeholder="Customer Name"
                  />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="for-years">Customer City</label>
                  <input
                    type="text"
                    class="form-control alpha_characters"
                    id="customer_city"
                    name="customer_city" maxlength="100"
                    placeholder="Customer City"
                  />
                </div>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-md-5">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Scan Serial Number</label>
                  <input
                    type="text"
                    class="form-control"
                    id="serial_number"
                    name="serial_number" autofocus
                    placeholder="12123" required
                  />
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-group">
                  <label class="form-label" for="select-model">Select Model</label>
                  <select class="form-control select2" id="model_id" name="model" required>
                    <option value="">Select Model</option>
                    @foreach($models as $model)
                    <option value="{{$model->name}}">{{$model->name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-2">
                <label class="form-label" for="basic-default-name">Save</label>
                <br>
                <p type="button" class="btn btn-primary" onclick="saveSerialization()">Save</p>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Serialization List</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Serial Number</th>
                  <th>Model</th>
                  <th>Dealer Name</th>
                  <th>Dealer City</th>
                </tr>
              </thead>
              <tbody id="models-data">
                <!-- models data prepand here through ajax -->
              </tbody>
            </table>
          </div>
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
<SCRIPT language="javascript">

  function saveSerialization()
  {
    var model_id = $("#model_id").val();
    var serial_number = $("#serial_number").val();
    var production_date = $("#production_date").val();
    var dealer_name = $("#dealer_name").val();
    var dealer_city = $("#dealer_city").val();
    var delivery_boy = $("#delivery_boy").val();
    var customer_name = $("#customer_name").val();
    var customer_city = $("#customer_city").val();

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
      url: '/add-serialization',
      type: 'POST',
      data: {_token: CSRF_TOKEN, serial_number: serial_number, model_id: model_id, production_date: production_date, dealer_name: dealer_name, dealer_city: dealer_city, delivery_boy: delivery_boy, customer_name: customer_name, customer_city: customer_city},
      dataType: 'json',

      success: function(response){
        var result = response.finished_model;
          
        if(response.status == 'Already'){
          alert(response.message);
          $("#serial_number").val("");
        }

        if(response.status == 'Error'){
          alert(response.message);
        }

        if(response.status == 'Success'){
          $("table tbody").prepend(
            "<tr>"
              + "<td>" + result.serial_number +"</td>"
              + "<td>" + result.model +"</td>"
              + "<td>" + result.dealer_name +"</td>"
              + "<td>" + result.dealer_city +"</td>"
            + "</tr>"
          );
          $("#serial_number").val("");
        }
                    
      }
    });
  }
</SCRIPT>
@endsection
