@if(session('message'))<div class="alert alert-success" role="status">{{ session('message') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
<style>@media print{nav,.main-menu,.footer,.erp-page-actions,form,summary{display:none!important}.app-content{margin:0!important;padding:0!important}.table-responsive{overflow:visible!important}thead{display:table-header-group}tr{break-inside:avoid}@page{size:A4 landscape;margin:12mm}}</style>
