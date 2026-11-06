@if(isset($unassignedProducts) && $unassignedProducts->isNotEmpty())
<div class="erp-stock-warning mb-2" role="alert">
  <i class="bx bx-error-circle" aria-hidden="true"></i>
  <div class="erp-stock-warning-copy"><strong>{{ $unassignedProducts->count() }} products need stock correction.</strong><p>Excluded from warehouse stock. Correct quantities and assign a location when ready.</p></div>
  <a href="{{ route('warehouses.unassigned') }}">Review unassigned products <span aria-hidden="true">→</span></a>
</div>
@endif
