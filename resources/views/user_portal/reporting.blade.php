@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Reporting')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/buttons.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection

@section('content')

<!-- Column selectors with Export Options and print table -->
<div class="users-list-filter px-1 mt-1">
  <form method="get">
    <div class="row border rounded py-2 mb-2">
      <div class="col-md-3">
        <div class="form-group">
          <label>From Date</label>
          <fieldset class="form-group">
          <input type="date" name="from" id="from" class="form-control" max="{{$today_date}}" required="required" value="<?php if(isset($_GET['from'])) echo $_GET['from']; ?>" onChange="restaurantTime()">
          </fieldset>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>To Date</label>
          <fieldset class="form-group">
          <input type="date" name="to" id="to" class="form-control" max="{{$today_date}}" required="required" value="<?php if(isset($_GET['to'])) echo $_GET['to']; ?>">
          </fieldset>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Company</label>
          <fieldset class="form-group">
            <select class="form-control select2" id="select-company" name="company" required>
              <option value="any">Any</option>
              @foreach($companies as $company)
              <option @isset($_GET['company']) @if($company->id==$_GET['company']) selected @endif @endisset value="{{$company->id}}">{{$company->name}}</option>
              @endforeach
            </select>
          </fieldset>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Power</label>
          <fieldset class="form-group">
            <select class="form-control select2" id="select-power" name="power" required>
              <option value="any">Any</option>
              @foreach($powers as $power)
              <option @isset($_GET['power']) @if($power->id==$_GET['power']) selected @endif @endisset value="{{$power->id}}">{{$power->name}}</option>
              @endforeach
            </select>
          </fieldset>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Error</label>
          <fieldset class="form-group">
            <select class="form-control select2" id="select-error" name="error" required>
              <option value="any">Any</option>
              @foreach($errors as $error)
              <option @isset($_GET['error']) @if($error->id==$_GET['error']) selected @endif @endisset value="{{$error->id}}">{{$error->name}}</option>
              @endforeach
            </select>
          </fieldset>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
        <br>
          <fieldset class="form-group">
            <button type="submit" class="btn btn-primary">Filter</button>
          </fieldset>
        </div>
      </div>
    </div>
  </form>
</div>

<div class="users-list-table">
  <div class="card">
    <div class="card-content">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table zero-configuration" id="example">
            <thead>
              <tr style="color:black">
                <th>Sr.</th>
                <th>Serial Number</th>
                <th style="min-width:80px">Customer<br>Name</th>
                <th>Company<br>Name</th>
                <th style="min-width:80px">Model</th>
                <th style="min-width:70px">Power</th>
                <th style="min-width:60px">Error Code</th>
                <th style="min-width:150px">Entry<br>Date</th>
                <th style="min-width:150px">Exit<br>Date</th>
                <th style="min-width:150px">Total Days in Factory</th>
                <th style="min-width:140px">Cost</th>
                <th style="min-width:80px">Entry<br>By</th>
                <th style="min-width:80px">Exit<br>By</th>
                <th style="min-width:80px">Receiver Name</th>
                <th style="min-width:80px">Accessories</th>
                <th style="min-width: 140px">Error Discription</th>
                <th style="min-width: 140px">Solution</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody style="color: black">
              @foreach($machine_histories as $key => $machine_history)
                <tr>
                  <td>{{ ++$key}}</td>
                  <td>{{$machine_history->Item->serial_number}}</td>
                  <td style="min-width:80px">{{$machine_history->name}}</td>
                  <td>{{$machine_history->Company->name}}</td>
                  <td style="min-width:80px">{{$machine_history->model}}</td>
                  <td style="min-width:70px">{{$machine_history->Power ? $machine_history->Power->name : ''}}</td>
                  <td style="min-width:60px">{{$machine_history->Error ? $machine_history->Error->name : ''}}</td>
                  <td style="min-width:150px">{{$machine_history->entry_date}}</td>
                  <td style="min-width:150px">{{$machine_history->exit_date}}</td>
                  @if(!empty($machine_history->exit_date))
                    <?php
                      $to = \Carbon\Carbon::parse($machine_history->exit_date);
                      $from = \Carbon\Carbon::parse($machine_history->entry_date);
                      $days = $to->diffInDays($from);
                    ?>
                  <td style="min-width:150px">{{$days}}</td>
                  @else
                  <?php
                      $to = \Carbon\Carbon::parse($today_date);
                      $from = \Carbon\Carbon::parse($machine_history->entry_date);
                      $days = $to->diffInDays($from);
                    ?>
                  <td style="min-width:150px">{{$days}}</td>
                  @endif
                  <td style="min-width:140px">Rs. {{number_format($machine_history->cost)}}</td>
                  <td style="min-width:80px">{{$machine_history->EntryBy ? $machine_history->EntryBy->name : ''}}</td>
                  <td style="min-width:80px">{{$machine_history->ExitBy ? $machine_history->ExitBy->name : ''}}</td>
                  <td style="min-width:80px">{{$machine_history->receiver_name}}</td>
                  <td style="min-width:80px">
                    @foreach($machine_history->machineAccessories as $key => $machine_accessories)
                      {{$machine_accessories->Accessory->name}},
                    @endforeach
                  </td>
                  <td style="min-width: 140px">{{$machine_history->error_description}}</td>
                  <td style="min-width: 140px">{{$machine_history->resolution}}</td>
                  <td>{{$machine_history->status == 0 ? 'In Factory' : 'Delivered'}}</td>
                </tr>
              @endforeach              
            </tbody>
          </table>
        </div>
        <!-- datatable ends -->
      </div>
    </div>
  </div>
</div>
<!-- Column selectors with Export Options and print table -->

@endsection
{{-- vendor scripts --}}
@section('vendor-scripts')
<script src="{{asset('vendors/js/forms/select/select2.full.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/buttons.html5.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/buttons.print.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/pdfmake.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/vfs_fonts.js')}}"></script>
@endsection
{{-- page scripts --}}
@section('page-scripts')
<script src="{{asset('js/scripts/datatables/datatable.js')}}"></script>
<script type="text/javascript">
  var table = $('#example').DataTable({
    "lengthMenu": [ 100, 500, 1000, 5000 ]
  });
</script>
<script type="text/javascript">
  function restaurantTime() {
    var time = $("#from").val();

    $("#to").attr({
      "min" : time,
    });
  }
</script>

@endsection
