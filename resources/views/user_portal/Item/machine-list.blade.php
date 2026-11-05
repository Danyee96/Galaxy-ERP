@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','In Factory Machine List')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/buttons.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection

@section('content')

<!-- Column selectors with Export Options and print table -->
<button class="btn btn-primary waves-effect waves-light" id='print_qr'><i class="bx bx-printer"></i> Print Slip</button>
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
      <div class="col-md-4">
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
          <table class="table">
            <thead>
              <tr style="color:black">
                <th><input type="checkbox" id="selectall"/></th>
                <th>Sr.</th>
                <th>Serial Number</th>
                <th>Print</th>
                <th>Barcode Print</th>
                <th>Company<br>Name</th>
                <th style="min-width:70px">Power</th>
                <th style="min-width:60px">Error Code</th>
                <th style="min-width:150px">Entry<br>Date</th>
                <th style="min-width:80px">Entry<br>By</th>
                <th style="min-width:80px">Model</th>
                <th style="min-width:80px">Accessories</th>
                <th style="min-width: 140px">Error Discription</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody style="color: black">
              @php $counter = 0; @endphp
              @foreach($machine_histories as $key => $machine_history)
                <tr id="tr-{{$machine_history->id }}">
                  <td align="center"><input type="checkbox" class="case" name="case" value="{{$machine_history->id}}"/></td>
                  <td>{{ ++$key}}</td>
                  <td>{{$machine_history->Item->serial_number}}</td>
                  <td><a href="{{route('singlePrintSlip',$machine_history->id)}}" target="_blank"><button class="btn btn-primary"><i class="bx bx-printer"></i> Single Print</button></a></td>
                  <td><a href="{{route('singleBarcodePrintSlip',$machine_history->id)}}" target="_blank"><button class="btn btn-primary"><i class="bx bx-printer"></i> Barcode Print</button></a></td>
                  <td>{{$machine_history->Company->name}}</td>
                  <td style="min-width:70px">{{$machine_history->Power ? $machine_history->Power->name : ''}}</td>
                  <td style="min-width:60px">{{$machine_history->Error ? $machine_history->Error->name : ''}}</td>
                  <td style="min-width:150px">{{$machine_history->entry_date}}</td>
                  <td style="min-width:80px">{{$machine_history->EntryBy ? $machine_history->EntryBy->name : ''}}</td>
                  <td style="min-width:80px">{{$machine_history->model}}</td>
                  <td style="min-width:80px">
                    @foreach($machine_history->machineAccessories as $key => $machine_accessories)
                      {{$machine_accessories->Accessory->name}},
                    @endforeach
                  </td>
                  <td style="min-width: 140px">{{$machine_history->error_description}}</td>
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
<script type="text/javascript">
  function restaurantTime() {
    var time = $("#from").val();

    $("#to").attr({
      "min" : time,
    });
  }

  var checkedArray = [];
  $(function () {
    // add multiple select / deselect functionality

    $("#selectall").click(function () {
        var checkedArray = [];
        $('.case').prop('checked', this.checked);
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
    });

    // if all checkbox are selected, check the selectall checkbox
    // and viceversa
    $(".case").click(function () {
        if ($(".case").length == $(".case:checked").length) {
            $("#selectall").prop("checked", true);
        } else {
            $("#selectall").prop('checked', false);
        }

        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
    });

    $("#print_qr").click(function () {
        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });

        if (checkedArray.length == 0) {
            alert('please select some Machine for print Slip');
        } else {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/print-machine-slip',
                type: 'POST',
                data: {_token: CSRF_TOKEN, lists: checkedArray},
                dataType: 'html',
                success: function (data) {
                    var w = window.open('', 'Print Slips', 'width=100%,height=auto');
                    w.document.open();
                    w.document.write(data);
                    w.document.close();
                }
            });
        }
    });
});
</script>

@endsection
