@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Exit Machine')

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
          <h4 class="card-title">Add Machine Resolution</h4>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('saveExitMachine', $item_history->id)}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Company Name:</label> <span style="font-size: 16px; font-weight: bold; color: green;">{{$item_history->Company->name}}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>User Name:</label> <span style="font-size: 16px; font-weight: bold; color: green;">{{$item_history->name}}</span>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Model:</label> <span style="font-size: 16px; font-weight: bold; color: green;">{{$item_history->model}}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>User Phone:</label> <span style="font-size: 16px; font-weight: bold; color: green;">{{$item_history->phone}}</span>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Entry Date:</label> <span style="font-size: 18px; font-weight: bold; color: green;">{{$item_history->entry_date}}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status:</label> <span style="font-size: 18px; font-weight: bold; color: green;">{{$item_history->status == 0 ? 'In Factory' : 'Delivered'}}</span>
                </div>
              </div>
            </div>
            <hr>
            <h4 class="card-title">Accessories came with Machine</h4>
            <div class="col-md-6">
              <div class="form-group">
                @foreach($item_history_accessories as $key => $machine_accessories)
                <div class="col-md-3">
                  <div class="form-group">
                    <input type="checkbox"  id="accessory" name="accessory[]" value="{{$machine_accessories->id}}" aria-invalid="false">
                    <label class="form-label" for="basic-default-accessory">{{$machine_accessories->Accessory->name}}</label>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-employee-code">Enter Machine Issue</label>
                  <input
                    type="text"
                    class="form-control"
                    id="advance_amount_value"
                    name="issue"
                    placeholder="Enter Machine Issue" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-employee-code">Enter Machine Solution</label>
                  <input
                    type="text"
                    class="form-control"
                    id="advance_amount_value"
                    name="solution"
                    placeholder="Add Solution" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-employee-code">Enter Receiver Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="advance_amount_value"
                    name="receiver_name"
                    placeholder="Add Receiver Name" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-employee-code">Enter Machine Solution Cost</label>
                  <input
                    type="number"
                    class="form-control"
                    id="cost"
                    name="cost" min="0"
                    placeholder="0" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary" name="submit" value="Submit">Exit</button>
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
