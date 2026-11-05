@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Create Error')

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
          <h4 class="card-title">Create Error</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveError')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Code</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="name"
                    placeholder="00" required
                  />
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-description">Description</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-description"
                    name="description"
                    placeholder="Description" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary" name="submit" value="Submit">Save</button>
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
