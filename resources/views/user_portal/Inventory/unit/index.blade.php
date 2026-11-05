@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Unit List')

@section('content')
@if(session()->has('message'))
  <div class="alert alert-success">
    {{ session()->get('message') }}
  </div>
@endif
<!-- Hoverable rows start -->
<div class="row" id="table-hover-row">
  <div class="col-12">
    <div class="card">
      <!-- table hover -->
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Id</th>
              <th>Name</th>
              <th>Code</th>
            </tr>
          </thead>
          <tbody>
            @foreach($units as $key => $unit)
            <tr>
              <td>{{++$key}}</td>
              <td class="text-bold-500">{{$unit->name}}</td>
              <td class="text-bold-500">{{$unit->unit}}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- Hoverable rows end -->
@endsection
