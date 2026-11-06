<!DOCTYPE html>
<!--
Template Name: Frest HTML Admin Template
Author: :Pixinvent
Website: http://www.pixinvent.com/
Contact: hello@pixinvent.com
Follow: www.twitter.com/pixinvents
Like: www.facebook.com/pixinvents
Purchase: https://1.envato.market/pixinvent_portfolio
Renew Support: https://1.envato.market/pixinvent_portfolio
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.

-->
{{-- pageConfigs variable pass to Helper's updatePageConfig function to update page configuration  --}}
@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
// confiData variable layoutClasses array in Helper.php file.
  $configData = Helper::applClasses();
@endphp

<html class="loading" lang="@if(session()->has('locale')){{session()->get('locale')}}@else{{$configData['defaultLanguage']}}@endif"
 data-textdirection="{{$configData['direction'] == 'rtl' ? 'rtl' : 'ltr' }}" data-asset-path="{{ asset('/')}}">
  <!-- BEGIN: Head-->

    <head>
    <meta  charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ config('brand.name') }}</title>

    {{-- Include core + vendor Styles --}}
    @include('panels.styles')
    <script>
    try { var erpTheme = localStorage.getItem('galaxy-erp-theme'); document.documentElement.dataset.erpTheme = erpTheme === 'dark' || erpTheme === 'light' ? erpTheme : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); } catch (e) { document.documentElement.dataset.erpTheme = 'light'; }
    </script>
    <link rel="stylesheet" href="{{ asset('brand/erp.css') }}?v={{ is_file(public_path('brand/erp.css')) ? filemtime(public_path('brand/erp.css')) : '1' }}">
    <script src="{{ asset('brand/erp.js') }}?v={{ is_file(public_path('brand/erp.js')) ? filemtime(public_path('brand/erp.js')) : '1' }}" defer></script>
    </head>
    <!-- END: Head-->

     @if(!empty($configData['mainLayoutType']) && isset($configData['mainLayoutType']))
     @include(($configData['mainLayoutType'] === 'horizontal-menu') ? 'layouts.horizontalLayoutMaster':'layouts.verticalLayoutMaster')
     @else
     {{-- if mainLaoutType is empty or not set then its print below line --}}
     <h1>{{'mainLayoutType Option is empty in config custom.php file.'}}</h1>
     @endif

</html>


