@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Add Inventory')

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
          <h4 class="card-title">Add {{$raw_material->name}} Inventory</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveInventory')}}" method="POST">
            @csrf
            <div class="form-group">
              <input type="hidden" name="raw_material_id" value="{{$raw_material->id}}">
              <label class="form-label" for="basic-default-qty">Enter {{$raw_material->name}} </label>
              <input
                type="number"
                class="form-control"
                id="basic-default-qty"
                name="material_qty" min="0" max="10000000"
                placeholder="5000" required
              />
            </div>
            <div class="form-group">
              <label for="select-transit">Select Transit</label>
              <select class="form-control select2" id="select-transit" name="select_transit" required>
                <option value="">Select Transit</option>
                @foreach($transits as $transit)
                <option value="{{$transit->id}}">{{$transit->name}}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="basic-default-name">Remarks</label>
              <input
                type="text"
                class="form-control"
                id="basic-default-name"
                name="remarks"
                placeholder="Enter Remarks" value=""
              />
            </div>

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
@endsection
