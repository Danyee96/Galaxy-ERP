@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Check Product Estimate')

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
          <h4 class="card-title">Check Product Estimate</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="basic-default-name">Enter Title</label>
                <input type="text" required name="title" id="title" class="form-control" placeholder="Enter Title">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="basic-default-name">Estimate Number</label>
                <input type="text" required disabled name="number" id="estimate_number" class="form-control" placeholder="Estimate Number" value="{{$estimate_number}}">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="select-company">Select Bundle</label>
                <select class="form-control select2" name="machine_id" id="machine_id" required>
                  <option value="">Select Bundle</option>
                  @foreach($all_products as $bundle)
                  <option value="{{$bundle->id}}">{{$bundle->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label class="form-label" for="basic-default-name">Enter Quantity to be Produce</label>
                <input type="number" min="0" max="10000" required name="machine_qty" id="machine_qty" class="form-control" placeholder="Enter Quantity to be Produce">
              </div>
            </div>
            <div class="col-md-1">
              <br>
              <p class="btn btn-primary" name="submit" onclick="saveMachineProduction()" id="submit">Save</p>
            </div>
          </div>
          <hr>

          <div id="recipe-container"></div>

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
  $("#machine_qty").on("keyup", function(e){
    machineProduction();
  });

  function machineProduction()
  {
    var qty = $("#machine_qty").val();
    var id = $("#machine_id").val();
    // alert(id);
    $.ajax({
      type: "post",
      url: "{{ URL::route('getProductEstimates') }}",
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        id: id,
        qty : qty
      },
      success: function(response) {
        console.log(response.response);
        if(response.response.status == 0)
        {
          alert(response.response.message);
        }
        else
        {
          $('#recipe-container').html(response.response.data);
        }
      },
      'error': function (response) {
        alert("Something went wrong!, Please contact support!");
      }
    });
  }

  function saveMachineProduction()
  {
    var qty = $("#machine_qty").val();
    var id = $("#machine_id").val();
    var title = $("#title").val();
    var estimate_number = $("#estimate_number").val();
    // alert(id);
    $.ajax({
      type: "post",
      url: "{{ URL::route('saveProductEstimates') }}",
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        id: id,
        qty : qty,
        title : title,
        estimate_number : estimate_number
      },
      success: function(response) {
        console.log(response.response);
        if(response.response.status == 0)
        {
          alert(response.response.message);
        }
        else
        {
          window.location.href = 'product-estimates';
        }
      },
      'error': function (response) {
        alert("Something went wrong!, Please contact support!");
      }
    });
  }
</script>
@endsection
