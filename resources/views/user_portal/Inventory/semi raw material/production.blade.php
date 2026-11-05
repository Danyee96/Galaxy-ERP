@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Add Production Quantity')

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
          <h4 class="card-title">Add Production Quantity for {{$semi_raw_material->name}}</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveSemiRawMaterialProduction')}}" method="POST">
            @csrf
            <input type="hidden" name="machine_id" id="machine_id" value="{{$semi_raw_material->id}}">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Enter {{$semi_raw_material->name}} Quantity to be Produce</label>
                  <input type="number" min="0" max="10000" required name="machine_qty" id="machine_qty" class="form-control" placeholder="Enter {{$semi_raw_material->name}} Quantity to be Produce" autofocus>
                </div>
              </div>
            </div>
            <hr>

            <div id="recipe-container"></div>

            <hr>

            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary" name="submit" value="Save">Save</button>
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
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
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
      url: "{{ URL::route('get-semi-raw-material-production') }}",
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
</script>
@endsection
