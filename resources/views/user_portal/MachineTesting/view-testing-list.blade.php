@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','View Machine Testing List')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/buttons.bootstrap4.min.css')}}">
@endsection
@section('page-styles')
<style type="text/css">
  h6 {
    font-weight: bold;
    text-align: center;
    color: white;
    background-color: black;
    padding: 5px 0px;
  }
</style>
@endsection

@section('content')
@if(session()->has('message'))
  <div class="alert alert-success">
    {{ session()->get('message') }}
  </div>
@endif
<section id="column-selectors">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body card-dashboard">
          <div class="table-responsive">
            <table class="table table-striped" id="example">
              <thead>
                <tr>
                  <th>Id</th>
                  <th>Machine Barcode</th>
                  <th>Test By</th>
                  <th>View Report</th>
                </tr>
              </thead>
              <tbody>
                @foreach($machines as $key => $data)
                <tr>
                  <td>{{++$key}}</td>
                  <td>{{$data->barcode}}</td>
                  <td>{{$data->user ? $data->user->name : 'N/A'}}</td>
                  <td>
                    <div class="modal fade text-left" id="backdrop{{$data->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel4">Machine Report</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <i class="bx bx-x"></i>
                            </button>
                          </div>
                          <div class="modal-body">
                            <div class="row">
                              <div class="col-md-6">
                                <b>Serial Number: {{$data->barcode}}</b>
                              </div>
                              <div class="col-md-6">
                                <b>Total Time: {{$data->total_time}}</b>
                              </div>
                            </div>
                            <hr>
                            <h6><u>Supply and WIFI Test</u></h6>
                            <div class="row">
                              <div class="col-md-6">
                                SUPPLY AMP (BAT_VOLT): 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->supply_amp}}</span>
                              </div>
                              <div class="col-md-6">
                                NO-LOAD OUT VOLT (VAC): 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->no_load_voltage}}</span>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-md-6">
                                WIFI LIGHT TEST: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->wifi_light}}</span>
                              </div>
                            </div>
                            <hr>
                            <h6><u>Run without Battery Test</u></h6>
                            <div class="row">
                              <div class="col-md-6">
                                RUN SERIES PV ON 500W LD: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->pv_series}}</span>
                              </div>
                              <div class="col-md-6">
                                RUN SERIES INP ON 500W LD: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->ld_inp_series}}</span>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-md-6">
                                CHECK SHARING ON NO LD: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->no_ld_sharing}}</span>
                              </div>
                              <div class="col-md-6">
                                SHIFT TO DIRECT PV RUN 500W LD: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->direct_pv_run}}</span>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-md-6">
                                SHIFT TO WAPDA CHECK SHARING: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->wapda_check}}</span>
                              </div>
                              <div class="col-md-6">
                                OVL TEST WITHOUT BAT: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->ovl_test_bat}}</span>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-md-6">
                                CHECK ON-GRID (WR AMP): 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->check_on_grid}}</span>
                              </div>
                              <div class="col-md-6">
                                OFF-GRID SET DEFAULT 10A: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->off_grid}}</span>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-md-6">
                                BUTTON TEST: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->button_test}}</span>
                              </div>
                            </div>
                            <hr>
                            <h6><u>(Battery + INP + PV) Test</u></h6>
                            <div class="row">
                              <div class="col-md-6">
                                RUN ON 20% LD CK AMP-D: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->ck_amp_d}}</span>
                              </div>
                              <div class="col-md-6">
                                SHORT CIRCUIT TEST: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->short_circuit}}</span>
                              </div>
                            </div>
                            <hr>
                            <h6><u>BAT + INP</u></h6>
                            <div class="row">
                              <div class="col-md-6">
                                RUN ON SERIES INP: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->inp_series}}</span>
                              </div>
                              <div class="col-md-6">
                                CHECK THE CHG AMP DIFF(AMG): 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->agm_chg_amp}}</span>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-md-6">
                                CHECK THE CHG AMP DIFF(FLD): 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->fld_chg_amp}}</span>
                              </div>
                              <div class="col-md-6">
                                CHECK THE DUAL-OUT TEST: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->dual_out_test}}</span>
                              </div>
                            </div>
                            <hr>
                            <h6><u>BAT + INP + PV</u></h6>
                            <div class="row">
                              <div class="col-md-6">
                                OVL TEST POWER: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->ovl_test_power}}</span>
                              </div>
                              <div class="col-md-6">
                                ON INDUCTIVE LOAD TEST LCD: 
                                <br>
                                <span style="font-weight: bold; color: black; font-size: 16px"> {{$data->inductive_load_test}}</span>
                              </div>
                            </div>
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
                    <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" data-target="#backdrop{{$data->id}}">View Report</button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Column selectors with Export Options and print table -->

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
<script src="{{asset('js/scripts/datatables/datatable.js')}}"></script>
<script type="text/javascript">
  var table = $('#example').DataTable({
    "lengthMenu": [ 100, 500, 1000, 5000 ]
  });
</script>
@endsection
