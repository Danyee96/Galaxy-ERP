@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Add Remaining Inventory')

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
          <h4 class="card-title">Add Remaining Inventory</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" method="POST" action="{{route('saveRemainigInventoryNew')}}" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <input
                type="hidden"
                class="form-control"
                name="model_id" value="{{$model_id}}" required
              />
              @foreach($materials as $material)
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Enter Remaining Qty for {{$material->rawMaterial ? $material->rawMaterial->name : 'N/A'}}, left in this piece <br><span style="font-size: 9px; color: red;">*(Remaining Qty is {{$material->remain_qty}})</span></label>
                  <input
                    type="number"
                    class="form-control qty_used"
                    id="qty_used" min="0" max="{{$material->remain_qty}}"
                    name="qty_used[]"
                    placeholder="Enter Remaining Qty for {{$material->rawMaterial ? $material->rawMaterial->name : 'N/A'}}, left in this piece" required
                  />
                  <input
                    type="hidden"
                    class="form-control qty_used_ids"
                    id="qty_used_ids"
                    name="qty_used_ids[]" value="{{$material->id}}" required
                  />
                </div>
              </div>
              @endforeach
            </div>
            <hr>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary" name="submit" value="Save">Update</button>
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
@endsection
