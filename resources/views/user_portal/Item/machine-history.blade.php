@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Machine History')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/buttons.bootstrap4.min.css')}}">
@endsection

@section('content')
@if(session()->has('message'))
  <div class="alert alert-success">
    {{ session()->get('message') }}
  </div>
@endif
<?php
  $to = \Carbon\Carbon::parse($today_date);
  $from = \Carbon\Carbon::parse($find_item->production_date);
  $calculate_warranty_days = $find_item->warranty_years * 365;
  $days = $to->diffInDays($from);
?>
<section id="column-selectors">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        @if($days > $calculate_warranty_days)
        <div class="card-header">
          <h4 class="card-title text-danger">Warranty Expired! As Machine Production Date is "{{$from->format("d-M-Y")}}"</h4>
        </div>
        @endif
        <div class="card-header">
          <h4 class="card-title">Machine History</h4>
          @if((count($open_machine_history)) < 1)
          <a href="{{route('addMachineInfo', $find_item->id)}}"><button class="btn btn-primary">Add New Record</button></a>
          @endif
        </div>
        <div class="card-body card-dashboard">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Seraial Number:</label> <span style="font-size: 16px; font-weight: bold; color: green;">{{$find_item->serial_number}}</span>
              </div>
            </div>
          </div>
          <hr>
          <h4 style="color: green;">Open Machine List</h4>
          <hr>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Id</th>
                  <th>Company Name</th>
                  <th>User Name</th>
                  <th>Phone Number</th>
                  <th style="min-width: 150px">Entry Date</th>
                  <th>Fault Detail</th>
                  <th>Status</th>
                  <th>Action</th>
                  <th>Edit</th>
                </tr>
              </thead>
              <tbody>
                @foreach($open_machine_history as $key => $open_machine)
                <tr>
                  <td>{{++$key}}</td>
                  <td>{{$open_machine->Company->name}}</td>
                  <td>{{$open_machine->name}}</td>
                  <td>{{$open_machine->phone}}</td>
                  <td style="min-width: 150px">{{$open_machine->entry_date}}</td>
                  <?php
                    if($open_machine->status == 0)
                    {
                      $status_name = 'In Factory';
                    }
                    elseif($open_machine->status == 1)
                    {
                      $status_name = 'Delivered';
                    }
                    else
                    {
                      $status_name = 'Pending';
                    }
                  ?>
                  <td>
                    <div class="modal fade text-left" id="backdrop{{$open_machine->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel4">Machine Detail</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <i class="bx bx-x"></i>
                            </button>
                          </div>
                          <div class="modal-body">
                            <b>Serial Number: {{$find_item->serial_number}}</b>
                            <br>
                            <b>Current Status : {{$status_name}}</b>
                            <hr>
                            Machine Model: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->model}}</span>
                            <br><hr>
                            <!-- Machine Power: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->Power ? $open_machine->Power->name : ''}}</span>
                            <br><hr>
                            Error Code: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->Error ? $open_machine->Error->name : ''}}</span>
                            <br><hr>
                            Error Description: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->error_description}}</span>
                            <br><hr>
                            Machine Maker Name: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->maker_name}}</span>
                            <br><hr> -->
                            Enter By: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->EntryBy ? $open_machine->EntryBy->name : 'N/A'}}</span>
                            <br><hr>
                            <span style="font-weight: bold; color: black; font-size: 16px">Accessories List: </span>
                            <br>
                            @foreach($open_machine->machineAccessories as $key => $machine_accessories)
                            <p>{{$machine_accessories->Accessory->name}}</p>
                            @endforeach
                            <br><hr>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-dismiss="modal">
                              <i class="bx bx-x d-block d-sm-none"></i>
                              <span class="d-none d-sm-block">Close</span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" data-target="#backdrop{{$open_machine->id}}">Fault Detail</button>
                  </td>
                  <td>{{$status_name}}</td>
                  <td>
                    @if($open_machine->packing_status == 0)
                    <a href="{{route('markAsPacked', $open_machine->id)}}"><button class="btn btn-danger">Mark as Packed</button></a>
                    @else
                    <a href="{{route('exitMachine', $open_machine->id)}}"><button class="btn btn-primary">Exit</button></a>
                    @endif
                  </td>
                  <td>
                    <a href="{{route('editMachine', $open_machine->id)}}"><button class="btn btn-success">Edit</button></a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <hr>
          <h4 style="color: green;">Close Machine List</h4>
          <hr>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Id</th>
                  <th>Company<br>Name</th>
                  <th style="min-width: 150px">Entry Date</th>
                  <th style="min-width: 150px">Exit Date</th>
                  <th style="min-width: 150px">Solution</th>
                  <th style="min-width: 150px">Cost</th>
                  <th style="min-width: 150px">View Details</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($close_machine_history as $key => $open_machine)
                <tr>
                  <td>{{++$key}}</td>
                  <td>{{$open_machine->Company->name}}</td>
                  <td style="min-width: 150px">{{$open_machine->entry_date}}</td>
                  <td style="min-width: 150px">{{$open_machine->exit_date}}</td>
                  <td style="min-width: 150px">{{$open_machine->resolution}}</td>
                  <td style="min-width: 150px">Rs. {{number_format($open_machine->cost)}}</td>
                  <?php
                    if($open_machine->status == 0)
                    {
                      $status_name = 'In Factory';
                    }
                    elseif($open_machine->status == 1)
                    {
                      $status_name = 'Delivered';
                    }
                    else
                    {
                      $status_name = 'Pending';
                    }
                  ?>
                  <td>
                    <div class="modal fade text-left" id="backdropsol{{$open_machine->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel4">Solution & Cost</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <i class="bx bx-x"></i>
                            </button>
                          </div>
                          <div class="modal-body">
                            <b>Serial Number: {{$find_item->serial_number}}</b>
                            <br>
                            <b>Current Status : {{$status_name}}</b>
                            <hr>
                            Solution: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->resolution}}</span>
                            <br><hr>
                            Cost: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> Rs. {{number_format($open_machine->cost)}}</span>
                            <br><hr>
                            Exit By: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->ExitBy ? $open_machine->ExitBy->name : 'N/A'}}</span>
                            <br><hr>
                            Exit Received By: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->receiver_name}}</span>
                            <br><hr>
                            Enter By: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->EntryBy ? $open_machine->EntryBy->name : 'N/A'}}</span>
                            <br><hr>
                            User Name: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->name}}</span>
                            <br><hr>
                            Phone: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->phone}}</span>
                            <br><hr>
                            Machine Model: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->model}}</span>
                            <br><hr>
                            Error Description: 
                            <br>
                            <span style="font-weight: bold; color: black; font-size: 16px"> {{$open_machine->error_description}}</span>
                            <br><hr>
                            <span style="font-weight: bold; color: black; font-size: 16px">Accessories List: </span>
                            <br>
                            @foreach($open_machine->exitMachineAccessories as $key => $machine_accessories)
                            <p>{{$machine_accessories->Accessory->name}}</p>
                            @endforeach
                            <br><hr>
                            <span style="font-weight: bold; color: black; font-size: 16px">Remaining Accessories List: </span>
                            <br>
                            @foreach($open_machine->exitMachineAccessoriesLeft as $key => $machine_accessories)
                            <p>{{$machine_accessories->Accessory->name}}</p>
                            @endforeach
                            <br><hr>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-dismiss="modal">
                              <i class="bx bx-x d-block d-sm-none"></i>
                              <span class="d-none d-sm-block">Close</span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" data-target="#backdropsol{{$open_machine->id}}">View Solution</button>
                  </td>
                  <td>{{$status_name}}</td>
                </tr>
                @endforeach
                <tr>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th style="font-weight: bold;font-size: 13px;">Total Cost</th>
                  <th style="font-weight: bold;font-size: 13px;">Rs. {{number_format($close_machine_history_cost)}}</th>
                  <th></th>
                  <th></th>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

{{-- vendor scripts --}}
@section('vendor-scripts')
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
@endsection
