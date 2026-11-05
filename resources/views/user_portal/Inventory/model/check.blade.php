@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Check Model Production')

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
          <h4 class="card-title">Check Production for {{$ups_model->name}}</h4>
        </div>
        <div class="card-body">
          <input type="hidden" name="machine_id" id="machine_id" value="{{$ups_model->id}}">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label class="form-label" for="basic-default-name">Enter {{$ups_model->name}} Quantity to be Produce</label>
                <input type="number" min="0" max="10000" required name="machine_qty" id="machine_qty" class="form-control" placeholder="Enter {{$ups_model->name}} Quantity to be Produce" autofocus>
              </div>
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
      url: "{{ URL::route('get-model-production') }}",
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
