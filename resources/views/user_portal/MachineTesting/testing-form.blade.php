@extends('layouts.contentLayoutMaster')
{{-- title --}}
@section('title','Testing Form')

@section('vendor-styles')
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection
{{-- page-styles --}}
@section('page-styles')
<link rel="stylesheet" type="text/css" href="{{asset('css/plugins/forms/validation/form-validation.css')}}">
<style type="text/css">
  h6 {
    font-weight: bold;
    text-align: center;
    color: white;
    background-color: black;
    padding: 7px 0px;
  }
</style>
@endsection

@section('content')
@if(session()->has('danger'))
  <div class="alert alert-danger">
    {{ session()->get('danger') }}
  </div>
@endif
<!-- Simple Validation start -->
<section class="simple-validation">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Please Scan Serial Number</h4>
          <div><span style="color: green; font-weight: bold;">Time: </span><span id="stopwatchone">00:00:00</span></div>
        </div>
        <div class="card-body">
          <form id="jquery-val-form" action="{{route('save-testing-form')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <input name="total_time" type="hidden" id="stopwatch" value="00:00:00">
            <h6><u>Supply and WIFI Test</u></h6>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">SUPPLY AMP (BAT_VOLT)</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="supply_amp" maxlength="15"
                    placeholder="3A / 15V" required
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-maker">NO-LOAD OUT VOLT (VAC)</label>
                  <input
                    type="text"
                    class="form-control numeric_characters"
                    id="no_load_voltage"
                    name="no_load_voltage" maxlength="5"
                    placeholder="230" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">WIFI LIGHT TEST</label>
                  <select class="form-control select2" id="select-wifi" name="wifi_light" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
            </div>
            <h6><u>Run without Battery Test</u></h6>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">RUN SERIES PV ON 500W LD</label>
                  <select class="form-control select2" id="select-pv-series" name="pv_series" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">RUN SERIES INP ON 500W LD</label>
                  <select class="form-control select2" id="select-ld-series" name="ld_inp_series" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">CHECK SHARING ON NO LD</label>
                  <select class="form-control select2" id="select-no-sharing" name="no_ld_sharing" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">SHIFT TO DIRECT PV RUN 500W LD</label>
                  <select class="form-control select2" id="select-pv-run" name="direct_pv_run" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">SHIFT TO WAPDA CHECK SHARING</label>
                  <select class="form-control select2" id="select-wapda" name="wapda_check" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">OVL TEST WITHOUT BAT</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="ovl_test_bat" maxlength="15"
                    placeholder="5000W" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">CHECK ON-GRID (WR AMP)</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="check_on_grid" maxlength="15"
                    placeholder="10 - 16" required
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">OFF-GRID SET DEFAULT 10A</label>
                  <select class="form-control select2" id="select-off-grid" name="off_grid" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">BUTTON TEST</label>
                  <select class="form-control select2" id="select-button" name="button_test" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
            </div>
            <h6><u>(Battery + INP + PV) Test</u></h6>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">RUN ON 20% LD CK AMP-D</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="ck_amp_d" maxlength="5"
                    placeholder="3A" required
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">SHORT CIRCUIT TEST</label>
                  <select class="form-control select2" id="select-short-circuit" name="short_circuit" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
            </div>
            <h6><u>BAT + INP</u></h6>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">RUN ON SERIES INP</label>
                  <select class="form-control select2" id="select-inp-series" name="inp_series" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">CHECK THE CHG AMP DIFF(AMG)</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="agm_chg_amp" maxlength="15"
                    placeholder="3A-24V" required
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">CHECK THE CHG AMP DIFF(FLD)</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="fld_chg_amp" maxlength="10"
                    placeholder="27V" required
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">CHECK THE DUAL-OUT TEST</label>
                  <select class="form-control select2" id="select-dual-test" name="dual_out_test" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
            </div>
            <h6><u>BAT + INP + PV</u></h6>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">OVL TEST POWER</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="ovl_test_power" maxlength="15"
                    placeholder="3800" required
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">ON INDUCTIVE LOAD TEST LCD</label>
                  <select class="form-control select2" id="select-inductive" name="inductive_load_test" required>
                    <option value="">SELECT VALUE</option>
                    <option value="okay">OKAY</option>
                    <option value="not okay">NOT OKAY</option>                    
                  </select>
                </div>
              </div>
            </div>
            <h6><u>ASSIGN SERIAL NUMBER</u></h6>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label" for="basic-default-name">Scan Serial Number</label>
                  <input
                    type="text"
                    class="form-control"
                    id="basic-default-name"
                    name="serial_number" value=""
                    placeholder="12123" required
                  />
                </div>
              </div>
            </div>
            <hr>
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
<script type="text/javascript">
  $('.numeric_characters').bind('input', function() {
    var c = this.selectionStart,
        r = /[^0-9 +]/gi,
        v = $(this).val();
    if(r.test(v)) {
      $(this).val(v.replace(r, ''));
      c--;
    }
    this.setSelectionRange(c, c);
  });
</script>
<script type="text/javascript">
  $('.alpha_characters').bind('input', function() {
    var c = this.selectionStart,
        r = /[^a-z . -]/gi,
        v = $(this).val();
    if(r.test(v)) {
      $(this).val(v.replace(r, ''));
      c--;
    }
    this.setSelectionRange(c, c);
  });
</script>
<script>
  // Function to format time
  function formatTime(seconds) {
    var hours   = Math.floor(seconds / 3600);
    var minutes = Math.floor((seconds - (hours * 3600)) / 60);
    var secs    = seconds - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (secs    < 10) {secs    = "0"+secs;}
            
    return hours+':'+minutes+':'+secs;
  }

  // Function to update stopwatch
  function updateStopwatch() {
    var startTime = new Date().getTime() - window.startTime;
    document.getElementById('stopwatchone').innerHTML = formatTime(Math.floor(startTime / 1000));
  }

  // Start the stopwatch
  window.startTime = new Date().getTime();
  setInterval(updateStopwatch, 1000); // Update every second
</script>

<script>
  // Function to format time
  function formatTime(seconds) {
    var hours   = Math.floor(seconds / 3600);
    var minutes = Math.floor((seconds - (hours * 3600)) / 60);
    var secs    = seconds - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (secs    < 10) {secs    = "0"+secs;}
            
    return hours+':'+minutes+':'+secs;
  }

  // Function to update stopwatch
  function updateStopwatch() {
    var startTime = new Date().getTime() - window.startTime;
    document.getElementById('stopwatch').value = formatTime(Math.floor(startTime / 1000));
  }

  // Start the stopwatch
  window.startTime = new Date().getTime();
  setInterval(updateStopwatch, 1000); // Update every second
</script>
@endsection
