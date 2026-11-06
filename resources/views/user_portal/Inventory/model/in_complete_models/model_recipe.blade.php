@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Add In-Complete UPS Model')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection
{{-- page-styles --}}
@section('page-styles')
<link rel="stylesheet" type="text/css" href="{{asset('css/plugins/forms/validation/form-validation.css')}}">
@endsection

@section('content')
@if(config('warehouses.enabled'))<div data-warehouse-context-host data-recipe-type="ups"></div>@endif
<!-- Simple Validation start -->
<section class="simple-validation">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Add In-Complete UPS Model</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form">
            <div class="row">
              @foreach($model_recipes as $model_recipe)
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Enter Remaining Qty for {{$model_recipe->rawMaterial ? $model_recipe->rawMaterial->name : 'N/A'}}, left in this piece</label>
                  <input
                    type="number"
                    class="form-control qty_used"
                    id="qty_used" min="0" max="{{$model_recipe->qty}}"
                    name="qty_used[]"
                    placeholder="Enter Remaining Qty for {{$model_recipe->rawMaterial ? $model_recipe->rawMaterial->name : 'N/A'}}, left in this piece" required
                  />
                </div>
              </div>
              @endforeach
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
                    name="serial_number"
                    placeholder="Scan or Enter In-Complete UPS Model Serial Number" required autofocus
                  />
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-group">
                  <label class="form-label" for="select-company">Select Model</label>
                  <input
                    type="text"
                    class="form-control"
                    id="model_name"
                    name="model_name" value="{{$model->name}}"
                    placeholder="Model Name" readonly
                  />
                  <input
                    type="hidden"
                    class="form-control"
                    id="model_id"
                    name="model_id" value="{{$model->id}}"
                    placeholder="Model Id" readonly
                  />
                </div>
              </div>
              <div class="col-2">
                <label class="form-label" for="basic-default-name">Save</label>
                <br>
                <p class="btn btn-primary" name="submit" onclick="saveInComplete()" id="submit">Save</p>
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
          <h4 class="card-title">Scan In-Complete UPS Models List</h4>
        </div>
        <div class="card-header">
          <h5 class="card-title">Total Scanned Models: <span style="font-size: 15px" id="total">0</span></h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Serial Number</th>
                  <th>Model</th>
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
<SCRIPT language="javascript">

  function saveInComplete()
  {
    var checkedArray = [];
    $(".qty_used").each(function () {
      checkedArray.push($(this).val());
    });
    var model_id = $("#model_id").val();
    var serial_number_reference = $("#serial_number").val();

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
      url: '/add-in-complete-ups-model-new',
      type: 'POST',
      data: {_token: CSRF_TOKEN, serial_number_reference: serial_number_reference, model_id: model_id, checkedArray: checkedArray},
      dataType: 'json',

      success: function(response){
        var result = response.finished_model;
          
        if(response.status == 'Already'){
          alert(response.message);
        }

        if(response.status == 'NotEnoughInventory'){
          alert(response.message);
        }

        if(response.status == 'NoRecipe'){
          alert(response.message);
        }

        if(response.status == 'Success'){
          $("table tbody").prepend(
            "<tr>"
              + "<td>" + result.serial_number +"</td>"
              + "<td>" + result.model_detail.name +"</td>"
            + "</tr>"
          );
        }
                    
        $("#serial_number").val("");
      }
    });
  }
</SCRIPT>
@endsection
