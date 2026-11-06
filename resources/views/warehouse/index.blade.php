@extends('layouts.contentLayoutMaster')
@section('title','Warehouses & stock')
@section('vendor-scripts')
<script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>
@endsection
@section('content')
<div class="erp-warehouse">
<x-erp.page-header title="Warehouses & stock" description="View stock and move all or part of an item between warehouses." eyebrow="Inventory"><x-slot name="actions">@if((int)auth()->user()->role_id===1)<button class="btn btn-primary" type="button" data-open-warehouse>Add warehouse</button>@endif @if(in_array((int)auth()->user()->role_id,[1,4,5],true))<button class="btn btn-outline-primary" type="button" data-open-location>Add row / column</button>@endif <button class="btn btn-primary" type="button" data-open-transfer>Transfer stock</button></x-slot></x-erp.page-header>
@include('warehouse._feedback')
@include('warehouse._unassigned-warning')
<nav class="nav nav-tabs erp-warehouse-tabs mb-2" aria-label="Stock by warehouse">
<a class="nav-link {{ !request('warehouse_id') ? 'active' : '' }}" href="{{ route('warehouses.index',request()->except('warehouse_id','location_id','page','csv','print')) }}" @if(!request('warehouse_id')) aria-current="page" @endif>All stock</a>
@foreach($warehouses->where('code','!=','INTERNAL-TRANSIT') as $w)<a class="nav-link {{ (int)request('warehouse_id')===$w->id ? 'active' : '' }}" href="{{ route('warehouses.index',array_merge(request()->except('location_id','page','csv','print'),['warehouse_id'=>$w->id])) }}" @if((int)request('warehouse_id')===$w->id) aria-current="page" @endif>{{ $w->name }}{{ !$w->active ? ' (inactive)' : '' }}</a>@endforeach
</nav>
<section class="erp-panel erp-warehouse-search p-2 mb-2"><form method="get" class="d-flex flex-wrap align-items-end" style="gap:10px">
@if(request('warehouse_id'))<input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">@endif
@foreach(['source_type','source_id','location_id'] as $key) @if(request($key))<input type="hidden" name="{{ $key }}" value="{{ request($key) }}">@endif @endforeach
<label class="mb-0" style="flex:1;min-width:180px">Search stock<input class="form-control" name="search" value="{{ request('search') }}" placeholder="Item name, SKU or serial"></label>
<button class="btn btn-primary">Search</button><a class="btn btn-outline-primary" href="{{ route('warehouses.index',request()->only('warehouse_id')) }}">Reset</a>
<a class="btn btn-outline-primary" target="_blank" rel="noopener" href="{{ request()->fullUrlWithQuery(['print'=>1,'csv'=>null,'page'=>null]) }}">Print</a>
<a class="btn btn-outline-primary" href="{{ request()->fullUrlWithQuery(['csv'=>1,'print'=>null,'page'=>null]) }}">Export CSV</a>
</form></section>
<section class="erp-panel mb-2" data-list-tools="off"><div class="p-2 border-bottom"><strong>{{ number_format($balances->total()) }} stock records</strong><small class="d-block text-muted">Quantities are shown by warehouse location. Transfer all available stock at a location or enter a smaller quantity.</small></div><div class="table-responsive"><table class="table"><thead><tr><th>Item / SKU</th><th>Warehouse</th><th>Location</th><th>Available here</th><th>Total stock</th><th>Actions</th></tr></thead><tbody>
@forelse($balances as $b)<tr><td><strong>{{ $b->item->name }}</strong><small class="d-block text-muted">{{ optional($b->item->source)->sku }} · {{ $b->item->source_type }}</small></td><td>{{ $b->location->warehouse->name }}</td><td>@if($b->location->kind==='transfer') In transit @else @if($b->location->rack) Rack {{ $b->location->rack }} · @endif Row {{ $b->location->row }} · Column {{ $b->location->column }} @endif</td><td><strong>{{ \App\Services\Warehouse\Quantity::display($b->quantity) }}</strong>@if(!$b->location->active || !$b->location->warehouse->active)<small class="d-block text-warning">Inactive location</small>@endif</td><td>{{ \App\Services\Warehouse\Quantity::display($combined[$b->item_id] ?? 0) }}</td><td>@if($b->quantity>0 && $b->location->kind==='physical' && $b->location->active && $b->location->warehouse->active)<button type="button" class="btn btn-sm btn-primary" data-open-transfer="{{ $b->id }}">Transfer</button>@endif <a class="small" href="{{ route('warehouses.history',['item_id'=>$b->item_id]) }}">History</a></td></tr>@empty<tr><td colspan="6">No matching stock in this warehouse.</td></tr>@endforelse
</tbody></table></div><div class="p-1">{{ $balances->links('pagination::bootstrap-4') }}</div></section>
<div class="d-flex flex-wrap mb-2" style="gap:12px">@if(in_array((int)auth()->user()->role_id,[1,4,5],true))<a href="{{ route('productService',['add_item'=>1]) }}">Add new item</a>@endif<a href="#warehouse-movement" data-warehouse-action>Add / receive stock</a><a href="{{ route('warehouses.transfers') }}">Transfer history / pending deliveries</a><a href="{{ route('warehouses.history') }}">Stock history</a></div>
<details class="erp-panel p-2" id="warehouse-advanced"><summary>Advanced tools &amp; location settings</summary><div class="mt-2">@include('warehouse._advanced')</div></details>
<dialog id="quick-transfer-dialog" class="erp-stock-dialog"><form method="post" action="{{ route('warehouses.quickTransfer') }}">@csrf
<div class="d-flex justify-content-between align-items-center"><h2 class="h5">Transfer stock</h2><button type="button" class="btn btn-light" data-close-dialog aria-label="Close transfer">×</button></div>
<input type="hidden" name="request_key" value="{{ Str::uuid() }}">
<label class="d-block">Item and source<select class="form-control" name="balance_id" required aria-label="Item and source">@foreach($transferStocks as $stock)<option value="{{ $stock->id }}" data-warehouse="{{ $stock->location->warehouse_id }}" data-quantity="{{ \App\Services\Warehouse\Quantity::display($stock->quantity) }}">{{ $stock->item->name }} — {{ $stock->location->label }} — {{ \App\Services\Warehouse\Quantity::display($stock->quantity) }} available</option>@endforeach</select></label>
<label class="d-block">Move to warehouse<select class="form-control" name="destination_warehouse_id" required aria-label="Move to warehouse"><option value="">Choose warehouse</option>@foreach($warehouses->where('active',1)->where('code','!=','INTERNAL-TRANSIT') as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></label>
<label class="d-block">Destination row<select class="form-control" id="quick-destination-row" required aria-label="Destination row"></select></label>
<label class="d-block" id="quick-destination-label">Destination column / rack<select class="form-control" name="destination_location_id" required aria-label="Destination column / rack"></select></label>
<label class="d-block">Quantity<select name="mode" class="form-control" aria-label="Quantity to transfer"><option value="all">Entire available stock</option><option value="partial">Transfer a portion</option></select></label>
<label class="d-block" id="quick-quantity-label" hidden>Quantity to move<input class="form-control" name="quantity" type="number" min="0.0001" step="0.0001" disabled></label>
<p class="text-muted" id="quick-transfer-help" role="status"></p><p class="small">This moves stock immediately to the destination. For goods that need dispatch and later receipt, use Advanced tools.</p>
<button class="btn btn-primary" type="submit" id="quick-transfer-submit">Transfer stock</button>
</form></dialog>
@if((int)auth()->user()->role_id===1)
<dialog id="quick-warehouse-dialog" class="erp-stock-dialog"><form method="post" action="{{ route('warehouses.save') }}">@csrf
<div class="d-flex justify-content-between align-items-center"><h2 class="h5">Add warehouse</h2><button type="button" class="btn btn-light" data-close-dialog aria-label="Close warehouse">×</button></div>
<input type="hidden" name="code" value="WH-{{ Str::uuid() }}"><input type="hidden" name="active" value="1"><input type="hidden" name="create_default_location" value="1">
<label class="d-block">Warehouse name<input class="form-control" name="name" maxlength="255" required placeholder="e.g. Warehouse 2"></label>
<label class="d-block">Address (optional)<input class="form-control" name="address" maxlength="2000"></label>
<p class="text-muted">Row 1 / Column 1 is created automatically for every warehouse. Use Add row / column to add more locations later.</p><button class="btn btn-primary">Add warehouse</button>
</form></dialog>
@endif
@if(in_array((int)auth()->user()->role_id,[1,4,5],true))
<dialog id="quick-location-dialog" class="erp-stock-dialog"><form method="post" action="{{ route('warehouses.locations') }}">@csrf
<div class="d-flex justify-content-between align-items-center"><h2 class="h5">Add row / column</h2><button type="button" class="btn btn-light" data-close-dialog aria-label="Close location">×</button></div>
<label class="d-block">Warehouse<select class="form-control" name="warehouse_id" required aria-label="Location warehouse">@foreach($warehouses->where('active',1)->where('code','!=','INTERNAL-TRANSIT') as $w)<option value="{{ $w->id }}" @if((int)request('warehouse_id')===$w->id) selected @endif>{{ $w->name }}</option>@endforeach</select></label>
<label class="d-block">Row<input class="form-control" name="row" maxlength="60" placeholder="e.g. 2 or A" required></label>
<label class="d-block">Column<input class="form-control" name="column" maxlength="60" placeholder="e.g. 3 or B" required></label>
<label class="d-block">Rack (optional)<input class="form-control" name="rack" maxlength="60"></label>
<input type="hidden" name="active" value="1"><input type="hidden" name="reorder_level" value="0">
<p class="text-muted">To add another column to an existing row, enter that row and the new column. Stock is assigned only when you receive or transfer it.</p><button class="btn btn-primary" type="submit">Add row / column</button>
</form></dialog>
@endif
</div>
@endsection
@section('page-scripts')
<script type="application/json" id="warehouse-transit-data">@json($shipments)</script>
<script>
document.querySelectorAll('[data-warehouse-action]').forEach(link=>link.addEventListener('click',function(event){
  const target=document.getElementById(this.hash.slice(1)); if(!target)return;
  event.preventDefault(); let panel=target.closest('details'); while(panel){panel.open=true;panel=panel.parentElement.closest('details');}
  target.scrollIntoView({block:'start'}); target.querySelector('input:not([type=hidden]),select')?.focus({preventScroll:true});
}));
(function(){const receipt=document.querySelector('form[action$="/transit-receipt"]');if(receipt){const data=JSON.parse(document.getElementById('warehouse-transit-data').textContent),type=receipt.querySelector('[name=document_type]'),doc=receipt.querySelector('[name=document_id]'),qty=receipt.querySelector('[name=quantity]');function selectDocument(){const pending=data.find(d=>d.type===type.value&&String(d.id)===doc.value);qty.max=pending?.remaining||'';qty.value=pending?.remaining||'';}function populate(){doc.replaceChildren();data.filter(d=>d.type===type.value).forEach(d=>doc.add(new Option('#'+d.id+' · '+d.name+' · Remaining '+d.remaining,d.id)));if(!doc.options.length)doc.add(new Option('No shipments awaiting receipt',''));if(doc.dataset.receiptId&&Array.from(doc.options).some(o=>o.value===doc.dataset.receiptId)){doc.value=doc.dataset.receiptId;doc.dataset.receiptId='';}selectDocument();}type.addEventListener('change',populate);doc.addEventListener('change',selectDocument);populate();}document.querySelectorAll('[data-location-select]').forEach(function(select){const form=select.closest('form'),warehouse=form.querySelector('[name=warehouse_id]');function update(){Array.from(select.options).forEach(o=>o.hidden=o.dataset.warehouse!==warehouse.value);if(select.selectedOptions[0]?.hidden)select.value=Array.from(select.options).find(o=>!o.hidden)?.value||'';}warehouse.addEventListener('change',update);update();});document.querySelectorAll('.warehouse-item').forEach(item=>{const form=item.closest('form'),loc=form.querySelector('[name=location_id]'),out=form.querySelector('[data-stock-available]');let version=0;async function check(){const current=++version;if(!item.value||!loc.value)return;out.textContent='Checking stock…';try{const response=await fetch('/warehouses/availability?'+new URLSearchParams({item_id:item.value,location_id:loc.value}),{headers:{Accept:'application/json'}});const body=await response.json();if(current===version)out.textContent=response.ok?'Available at this location: '+body.quantity:'This location is not available.';}catch(e){if(current===version)out.textContent='Could not check availability. Stock is validated again when posting.';}}form.addEventListener('change',check);check();});let index=1;document.getElementById('warehouse-add-line').onclick=function(){if(index>=100)return;const line=document.querySelector('.warehouse-transfer-line').cloneNode(true);line.querySelectorAll('[name]').forEach(e=>{e.name=e.name.replace(/lines\[\d+\]/,'lines['+index+']');if(e.tagName==='INPUT')e.value='';});index++;document.getElementById('warehouse-transfer-lines').append(line);};document.getElementById('warehouse-transfer-lines').onclick=function(e){if(e.target.closest('[data-remove-line]')&&document.querySelectorAll('.warehouse-transfer-line').length>1)e.target.closest('.warehouse-transfer-line').remove();};})();

(function(){
const dialog=document.getElementById('quick-transfer-dialog'),form=dialog.querySelector('form'),source=form.elements.balance_id,destination=form.elements.destination_warehouse_id,location=form.elements.destination_location_id,mode=form.elements.mode,quantity=form.elements.quantity;
const locations=@json($quickLocations);
function enhanceSource(){const $=window.jQuery;if(!$?.fn?.select2 || $(source).data('select2'))return;$(source).select2({width:'100%',dropdownParent:$(dialog),minimumResultsForSearch:0});$(source).on('select2:select',()=>source.dispatchEvent(new Event('change',{bubbles:true})));}
window.addEventListener('erp:list-selects-ready',enhanceSource);enhanceSource();
const destinationRow=document.getElementById('quick-destination-row');
function updateColumns(){location.replaceChildren(new Option('Choose column',''));locations.filter(l=>String(l.warehouse_id)===destination.value&&l.row===destinationRow.value).forEach(l=>location.add(new Option('Column '+l.column+(l.rack?' · Rack '+l.rack:''),l.id)));if(location.options.length===2)location.selectedIndex=1;}
function updateLocations(){destinationRow.replaceChildren(new Option('Choose row',''));Array.from(new Set(locations.filter(l=>String(l.warehouse_id)===destination.value).map(l=>l.row))).forEach(row=>destinationRow.add(new Option('Row '+row,row)));if(destinationRow.options.length===2)destinationRow.selectedIndex=1;updateColumns();}
destinationRow.addEventListener('change',updateColumns);
function updateSource(){const selected=source.selectedOptions[0];Array.from(destination.options).forEach(o=>{o.disabled=o.value===selected?.dataset.warehouse;});if(destination.selectedOptions[0]?.disabled)destination.value='';quantity.max=selected?.dataset.quantity||'';document.getElementById('quick-transfer-help').textContent=selected?'Available at the selected source: '+selected.dataset.displayQuantity:'No stock available to transfer.';const hasDestination=Array.from(destination.options).some(o=>o.value&&!o.disabled);if(!hasDestination)document.getElementById('quick-transfer-help').textContent='Add another warehouse before transferring stock.';document.getElementById('quick-transfer-submit').disabled=!selected||!hasDestination;updateLocations();}
function updateMode(){const partial=mode.value==='partial';document.getElementById('quick-quantity-label').hidden=!partial;quantity.disabled=!partial;quantity.required=partial;}
source.addEventListener('change',updateSource);destination.addEventListener('change',updateLocations);mode.addEventListener('change',updateMode);
document.querySelectorAll('[data-open-transfer]').forEach(button=>button.addEventListener('click',()=>{if(button.dataset.openTransfer){source.value=button.dataset.openTransfer;if(window.jQuery?.fn?.select2)window.jQuery(source).trigger('change.select2');}mode.value='all';quantity.value='';updateSource();updateMode();dialog.showModal();enhanceSource();}));
document.querySelectorAll('[data-open-location]').forEach(button=>button.addEventListener('click',()=>document.getElementById('quick-location-dialog').showModal()));
document.querySelectorAll('[data-open-warehouse]').forEach(button=>button.addEventListener('click',()=>document.getElementById('quick-warehouse-dialog').showModal()));
document.querySelectorAll('[data-close-dialog]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog').close()));
})();
</script>
@endsection
