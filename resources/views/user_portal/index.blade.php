@extends('layouts.contentLayoutMaster')
{{-- page Title --}}
@section('title','Welcome to Dashboard')
{{-- vendor css --}}
@section('vendor-styles')
<link rel="stylesheet" type="text/css" href="{{asset('vendors/css/charts/apexcharts.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/swiper.min.css')}}">
@endsection
@section('page-styles')
<link rel="stylesheet" type="text/css" href="{{asset('css/pages/dashboard-ecommerce.css')}}">
@endsection

@section('content')
<?php
function format_number_in_k_notation($number)
{
  if ($number < 1000) {
  return sprintf('%d', $number);
  }

  if ($number < 1000000) {
  return sprintf('%d%s', floor($number / 1000), 'K+');
  }

  if ($number >= 1000000 && $number < 1000000000) {
  return sprintf('%d%s', floor($number / 1000000), 'M+');
  }

  if ($number >= 1000000000 && $number < 1000000000000) {
  return sprintf('%d%s', floor($number / 1000000000), 'B+');
  }

  return sprintf('%d%s', floor($number / 1000000000000), 'T+');
}
?>
<div class="brand-hero"><h1>Dashboard</h1><p>Welcome back, {{ Auth::user()->name }}. Here’s your operations overview.</p></div>
<!-- Dashboard Ecommerce Starts -->
@if(Auth::user()->role_id == 1)
<section id="dashboard-ecommerce">
  <div class="row">
    <div class="col-xl-12 col-12 dashboard-users">
      <div class="row  ">
        <div class="col-12">
          <div class="row">
            <div class="col-sm-4 col-12 dashboard-users-success">
              <div class="card text-center">
                <div class="card-body py-1">
                  <div class="badge-circle badge-circle-lg badge-circle-light-primary mx-auto mb-50">
                    <i class="bx bx-error font-medium-5"></i>
                  </div>
                  <div class="text-muted line-ellipsis">Overall Complains</div>
                  <h3 class="mb-0">{{$total_complains}}</h3>
                </div>
              </div>
            </div>
            <div class="col-sm-4 col-12 dashboard-users-danger">
              <a href="{{route('pending-overall-complains')}}">
              <div class="card text-center">
                <div class="card-body py-1">
                  <div class="badge-circle badge-circle-lg badge-circle-light-danger mx-auto mb-50">
                    <i class="bx bxs-error font-medium-5"></i>
                  </div>
                  <div class="text-muted line-ellipsis">Overall Pending Complains</div>
                  <h3 class="mb-0">{{format_number_in_k_notation($total_pending_complains)}}</h3>
                </div>
              </div>
              </a>
            </div>
            <div class="col-sm-4 col-12 dashboard-users-danger">
              <a href="{{route('resolved-overall-complains')}}">
              <div class="card text-center">
                <div class="card-body py-1">
                  <div class="badge-circle badge-circle-lg badge-circle-light-success mx-auto mb-50">
                    <i class="bx bx-check-circle font-medium-5"></i>
                  </div>
                  <div class="text-muted line-ellipsis">Overall Resolved Complains</div>
                  <h3 class="mb-0">{{format_number_in_k_notation($total_completed_complains)}}</h3>
                </div>
              </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-12 col-12 dashboard-users">
      <div class="row  ">
        <div class="col-12">
          <div class="row">
            <div class="col-sm-4 col-12 dashboard-users-success">
              <div class="card text-center">
                <div class="card-body py-1">
                  <div class="badge-circle badge-circle-lg badge-circle-light-primary mx-auto mb-50">
                    <i class="bx bx-error font-medium-5"></i>
                  </div>
                  <div class="text-muted line-ellipsis"><?php echo date('F'); ?> Complains</div>
                  <h3 class="mb-0">{{$total_monthly_complains}}</h3>
                </div>
              </div>
            </div>
            <div class="col-sm-4 col-12 dashboard-users-danger">
              <a href="{{route('pending-monthly-complains')}}">
              <div class="card text-center">
                <div class="card-body py-1">
                  <div class="badge-circle badge-circle-lg badge-circle-light-danger mx-auto mb-50">
                    <i class="bx bxs-error font-medium-5"></i>
                  </div>
                  <div class="text-muted line-ellipsis"><?php echo date('F'); ?> Pending Complains</div>
                  <h3 class="mb-0">{{format_number_in_k_notation($total_monthly_pending_complains)}}</h3>
                </div>
              </div>
              </a>
            </div>
            <div class="col-sm-4 col-12 dashboard-users-danger">
              <a href="{{route('resolved-monthly-complains')}}">
              <div class="card text-center">
                <div class="card-body py-1">
                  <div class="badge-circle badge-circle-lg badge-circle-light-success mx-auto mb-50">
                    <i class="bx bx-check-circle font-medium-5"></i>
                  </div>
                  <div class="text-muted line-ellipsis"><?php echo date('F'); ?> Resolved Complains</div>
                  <h3 class="mb-0">{{format_number_in_k_notation($total_monthly_completed_complains)}}</h3>
                </div>
              </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Company Ratio</h4>
        </div>
        <div class="card-body">
          <script type="text/javascript">
            var company_data = @json($company_data);
            company_data = Object.values(company_data);
            //
            var company_name = @json($company_name);
            company_name = Object.values(company_name);
          </script>
          <div id="pie-chart-custom" class="d-flex justify-content-center"></div>
        </div>
      </div>
    </div>
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Power Ratio</h4>
        </div>
        <div class="card-body">
          <script type="text/javascript">
            var power_data = @json($power_data);
            power_data = Object.values(power_data);
            //
            var power_name = @json($power_name);
            power_name = Object.values(power_name);
          </script>
          <div id="donut-chart-custom" class="d-flex justify-content-center"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12 col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Complain Resolution Comparison</h4>
        </div>
        <div class="card-body">
          <script type="text/javascript">
            var monthly_days_arr = @json($monthly_days_arr);
            monthly_days_arr = Object.values(monthly_days_arr);
            //
            var monthly_in_count_arr = @json($monthly_in_count_arr);
            monthly_in_count_arr = Object.values(monthly_in_count_arr);
            //
            var monthly_out_count_arr = @json($monthly_out_count_arr);
            monthly_out_count_arr = Object.values(monthly_out_count_arr);
          </script>
          <div id="delivery-comparison-chart"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Error Ratio</h4>
        </div>
        <div class="card-body">
          <script type="text/javascript">
            var error_data = @json($error_data);
            error_data = Object.values(error_data);
            //
            var error_name = @json($error_name);
            error_name = Object.values(error_name);
          </script>
          <div id="pie-chart-custom-one" class="d-flex justify-content-center"></div>
        </div>
      </div>
    </div>
    <div class="col-lg-6 col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Accessories Ratio</h4>
        </div>
        <div class="card-body">
          <script type="text/javascript">
            var accessory_data = @json($accessory_data);
            accessory_data = Object.values(accessory_data);
            //
            var accessory_name = @json($accessory_name);
            accessory_name = Object.values(accessory_name);
          </script>
          <div id="donut-chart-custom-one" class="d-flex justify-content-center"></div>
        </div>
      </div>
    </div>
  </div>
</section>
@else
<section id="dashboard-ecommerce">
  <br>
  <h1 style="text-align: center;">Welcome to AutoPower Warranty System</h1>
</section>
@endif
<!-- Dashboard Ecommerce ends -->
@endsection

@section('vendor-scripts')
<script src="{{asset('vendors/js/charts/apexcharts.min.js')}}"></script>
<script src="{{asset('vendors/js/extensions/swiper.min.js')}}"></script>
@endsection

@section('page-scripts')
<script src="{{asset('js/custom_charts/chart-one.js')}}"></script>
@endsection
