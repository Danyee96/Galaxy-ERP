@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Edit semi raw material')

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
          <h4 class="card-title">Edit semi raw material</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('updateSemiRawMaterial')}}" method="POST">
            @csrf
            <input type="hidden" name="raw_material_id" value="{{$raw_material->id}}">
            <div class="form-group">
              <label class="form-label" for="basic-default-name">Name</label>
              <input
                type="text"
                class="form-control"
                id="basic-default-name"
                name="material_name"
                placeholder="Enter Semi Raw Material Name" maxlength="80" value="{{$raw_material->name}}" required
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
