@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Create User')

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
          <h4 class="card-title">Create User</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveUser')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="name"
                    placeholder="John Doe" required
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="select-country">Role</label>
                  <select class="form-control select2" id="select-country" name="role" required>
                    <option value="">Select Role</option>
                    @foreach($user_roles as $role)
                    <option value="{{$role->id}}">{{$role->role}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-email">Email</label>
                  <input
                    type="email"
                    id="basic-default-email" required
                    name="email"
                    class="form-control"
                    placeholder="john.doe@email.com"
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-password">Password</label>
                  <input
                    type="password"
                    id="basic-default-password" required
                    name="password"
                    class="form-control"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
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
