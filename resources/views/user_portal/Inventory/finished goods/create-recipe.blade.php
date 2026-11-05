@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Add Production Making')

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
          <h4 class="card-title">Add Production Making for {{$semi_raw_material->name}}</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveFinishedGoodsRecipe')}}" method="POST">
            @csrf
            <input type="hidden" name="menu_item_id" value="{{$semi_raw_material->id}}">

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <button type="button" onclick="newRecipe();" class="btn btn-primary"> <i class="bx bx-plus"></i> Add Raw Material </button>
                  <button type="button" onclick="newSemiRecipe();" class="btn btn-primary"> <i class="bx bx-plus"></i> Add Semi Raw Material </button>
                </div>
              </div>
            </div>

            @foreach($recipes as $recipe)
            <div class="row">
              <input type="hidden" name="recipe_id[]" value="{{$recipe->id}}">
              <div class="col-md-5">
                <div class="form-group">
                  @if($recipe->material_type == 'Raw')
                  <label class="form-label" for="basic-default-name">Raw Material</label>
                  <input type="text" disabled name="select_raw_item_old[]" class="form-control" placeholder="Material Name" value ="{{$recipe->rawMaterial ? $recipe->rawMaterial->name : ''}}">
                  @else
                  <label class="form-label" for="basic-default-name">Semi Raw Material</label>
                  <input type="text" disabled name="select_raw_item_old[]" class="form-control" placeholder="Material Name" value ="{{$recipe->semiRawMaterial ? $recipe->semiRawMaterial->name : ''}}">
                  @endif
                </div>
              </div>
              <div class="col-md-5">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Raw Material Quantity</label>
                  <input type="number" min="0" max="5000" required name="qty_old[]" class="form-control" placeholder="Enter Material Quantity Used" value ="{{$recipe->qty}}">
                </div>
              </div>
            </div>
            @endforeach

            <div id="recipe-container"></div>
            <div id="semi-recipe-container"></div>
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

  var recipeId = 0;
  function newRecipe()
  {
    recipeId++;
    var newRecipe = '<div style="margin-top: 10px" id="row-' + recipeId + '" class="row">' +
    '<div class="col-md-5">' +
    '<label class="form-label" for="basic-default-name">Select Raw Material</label>' +
    '<select class="form-control select2" id="select-country" name="select_raw_item[]" required>' +
    '<option value="">Select Raw Material</option>' +
    '@foreach($raw_materials as $raw_material)' + 
    '<option value="{{$raw_material->id}}">{{$raw_material->name}}</option>' +
    '@endforeach' + 
    '</select>'+
    '</div>' +
    '<div class="col-md-5">' +
    '<label class="form-label" for="basic-default-name">Raw Material Quantity</label>' +
    '<input type="number" min="0" max="5000" required name="qty[]" class="form-control" placeholder="Enter Material Quantity Used">' +
    '</div>' +
    '<div class="col-md-1">' +
    '<label class="form-label" for="basic-default-name">Remove</label>' +
    '<div class="input-group-btn">' +
    '<button class="btn btn-danger text-nowrap px-1" onclick="removeRecipe(' + recipeId + ');" type="button"> <i class="bx bx-x"></i></button>' +
    '</div>' +
    '</div>' +
    '</div>';

    $("#recipe-container").append(newRecipe);
  }

  function removeRecipe(rowId) {
    $("#row-" + rowId).remove();
  }
</script>
<script type="text/javascript">

  var semirecipeId = 0;
  function newSemiRecipe()
  {
    semirecipeId++;
    var newSemiRecipe = '<div style="margin-top: 10px" id="semirow-' + semirecipeId + '" class="row">' +
    '<div class="col-md-5">' +
    '<label class="form-label" for="basic-default-name">Select Raw Material</label>' +
    '<select class="form-control select2" id="select-country" name="select_semi_raw_item[]" required>' +
    '<option value="">Select Semi Raw Material</option>' +
    '@foreach($semi_raw_materials as $semi_raw_material)' + 
    '<option value="{{$semi_raw_material->id}}">{{$semi_raw_material->name}}</option>' +
    '@endforeach' + 
    '</select>'+
    '</div>' +
    '<div class="col-md-5">' +
    '<label class="form-label" for="basic-default-name">Semi Raw Material Quantity</label>' +
    '<input type="number" min="0" max="5000" required name="semi_qty[]" class="form-control" placeholder="Enter Semi Raw Material Quantity Used">' +
    '</div>' +
    '<div class="col-md-1">' +
    '<label class="form-label" for="basic-default-name">Remove</label>' +
    '<div class="input-group-btn">' +
    '<button class="btn btn-danger text-nowrap px-1" onclick="removeSemiRecipe(' + semirecipeId + ');" type="button"> <i class="bx bx-x"></i></button>' +
    '</div>' +
    '</div>' +
    '</div>';

    $("#semi-recipe-container").append(newSemiRecipe);
  }

  function removeSemiRecipe(rowId) {
    $("#semirow-" + rowId).remove();
  }
</script>
@endsection
