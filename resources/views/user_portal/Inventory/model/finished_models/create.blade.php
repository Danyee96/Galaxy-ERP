@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Add Finished UPS Model')

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
          <h4 class="card-title">Add Finished UPS Model</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-5">
              <div class="form-group">
                <label class="form-label" for="basic-default-name">Scan Serial Number</label>
                <input
                  type="text"
                  class="form-control"
                  id="serial_number"
                  name="serial_number"
                  placeholder="Scan or Enter Finished UPS Model Serial Number" required autofocus
                />
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label class="form-label" for="select-company">Select Model</label>
                <select class="form-control select2" name="model_id" id="model_id" required>
                  <option value="">Select Model</option>
                  @foreach($models as $model)
                  <option value="{{$model->id}}">{{$model->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-2">
              <label class="form-label" for="basic-default-name">Save</label>
              <br>
              <p class="btn btn-primary" name="submit" onclick="saveFinished()" id="submit">Save</p>
            </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Scan Finished UPS Models List</h4>
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

  function saveFinished()
  {
    var model_id = $("#model_id").val();
    var serial_number_reference = $("#serial_number").val();

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
      url: '/add-finished-ups-model',
      type: 'POST',
      data: {_token: CSRF_TOKEN, serial_number_reference: serial_number_reference, model_id: model_id},
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
