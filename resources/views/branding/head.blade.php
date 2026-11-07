<meta name="theme-color" content="#164b6e">
<link rel="icon" type="image/svg+xml" href="{{ asset('brand/favicon.svg') }}">
<link rel="apple-touch-icon" href="{{ asset('brand/galaxy-logo.png') }}">
<link rel="stylesheet" href="{{ asset('brand/galaxy.css') }}?v={{ is_file(public_path('brand/galaxy.css')) ? filemtime(public_path('brand/galaxy.css')) : '1' }}">

@if(config('warehouses.enabled') && Auth::check() && in_array((int)Auth::user()->role_id,[1,3,4,5],true))
<script type="application/json" id="warehouse-validation-errors">@json($errors instanceof \Illuminate\Support\ViewErrorBag ? $errors->all() : [])</script>
<script src="{{ asset('brand/warehouse-context.js') }}?v={{ is_file(public_path('brand/warehouse-context.js')) ? filemtime(public_path('brand/warehouse-context.js')) : '1' }}" defer></script>
@endif

<link rel="stylesheet" href="{{ asset('brand/list-tools.css') }}?v={{ is_file(public_path('brand/list-tools.css')) ? filemtime(public_path('brand/list-tools.css')) : '1' }}">
<script src="{{ asset('brand/list-tools.js') }}?v={{ is_file(public_path('brand/list-tools.js')) ? filemtime(public_path('brand/list-tools.js')) : '1' }}"></script>

<link rel="stylesheet" href="{{ asset('vendors/css/forms/select/select2.min.css') }}">
