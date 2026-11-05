@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Edit User')

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
          <h4 class="card-title">Edit User</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('updateUser',$user->id)}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="name" value="{{$user->name}}"
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
                    <option value="{{$role->id}}" {{ $role->id ==  $user->role_id ? 'selected' : '' }}>{{$role->role}}</option>
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
                    id="basic-default-email" required readonly
                    name="email"
                    class="form-control" value="{{$user->email}}"
                    placeholder="john.doe@email.com"
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="select-country">Status</label>
                  <select class="form-control select2" id="select-country" name="status" required>
                    <option value="">Select Status</option>
                    <option value="1" {{ $user->status == 1 ? 'selected' : ''}} >Active</option>
                    <option value="0" {{ $user->status == 0 ? 'selected' : ''}} >Banned</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary" name="submit" value="Submit">Update</button>
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
