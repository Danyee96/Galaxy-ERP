(function () {
  'use strict';
  const uuid = () => crypto.randomUUID ? crypto.randomUUID() : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => { const n = Math.floor(Math.random() * 16); return (c === 'x' ? n : (n & 3 | 8)).toString(16); });
  fetch('/warehouses/context', {headers: {'Accept':'application/json'}}).then(r => { if(!r.ok) throw Error('Warehouse setup is not ready.'); return r.json(); }).then(data => {
    const patterns = data.actions.map(path => new RegExp('^' + path.replace(/[.*+?^${}()|[\]\\]/g, '\\$&').replace('__ID__', '[^/]+') + '/?$'));
    function showStockError(message) {
      document.querySelectorAll('[data-warehouse-error]').forEach(e=>e.remove());
      const note=document.createElement('div');note.className='alert alert-danger';note.dataset.warehouseError='1';note.setAttribute('role','alert');note.tabIndex=-1;note.textContent=message;
      (document.querySelector('.warehouse-context') || document.querySelector('.content-body,.container') || document.body).prepend(note);note.focus();
    }
    const flashed=document.getElementById('warehouse-validation-errors');
    if(flashed && !document.querySelector('.alert[role=alert]')) { const messages=JSON.parse(flashed.textContent);if(messages.length)showStockError(messages.join(' ')); }
    const matches = url => patterns.some(re => re.test(new URL(url, location.origin).pathname));
    const hidden = (form, name, value) => { const input = document.createElement('input'); input.type='hidden'; input.name=name; input.value=value; form.append(input); };
    function fields() {
      const box=document.createElement('fieldset');box.className='border rounded p-2 my-2 warehouse-context';
      const legend=document.createElement('legend');legend.className='h6';legend.textContent='Warehouse allocation';box.append(legend);
      const note=document.createElement('small');note.className='d-block text-muted';note.textContent='Unselected source uses location-ID order across active physical warehouses. Receiving uses the selected exact location. Rack / row / column are included in location labels.';box.append(note);
      [['Source warehouse(s)','source_warehouse_ids[]',true],['Exact source location (optional)','source_location_id',false],['Destination location','destination_location_id',false]].forEach(([label,name,multiple])=>{
        const id='wh-'+uuid(),text=document.createElement('label'),select=document.createElement('select');text.textContent=label;text.htmlFor=id;select.id=id;select.name=name;select.className='form-control mb-1';select.multiple=multiple;if(multiple)select.size=3;
        if(!multiple){const opt=new Option(name==='source_location_id'?'Automatic allocation':'Default legacy receiving', '');select.add(opt);}
        const options=multiple?data.warehouses:data.locations;
        options.forEach(row=>select.add(new Option(row.label||row.name,String(row.id))));box.append(text,select);
      });return box;
    }
    function prepare(form){
      if(form.dataset.warehouseReady || !matches(form.action) || (form.method.toLowerCase()==='get' && !form.querySelector('[name=initial_qty]')))return;
      form.dataset.warehouseReady='1';
      if(form.method.toLowerCase()==='get'){form.method='post';if(!form.querySelector('[name=_token]'))hidden(form,'_token',document.querySelector('meta[name=csrf-token]')?.content||document.querySelector('input[name=_token]')?.value||'');}
      if(!form.querySelector('[name=warehouse_request_key]'))hidden(form,'warehouse_request_key',uuid());
      if(!form.querySelector('.warehouse-context'))form.prepend(fields());
    }
    document.querySelectorAll('form').forEach(prepare);
    document.querySelectorAll('[data-warehouse-context-host]').forEach(host=>host.append(fields()));
    document.querySelectorAll('.warehouse-context').forEach(box=>{
      const form=box.closest('form'),host=box.closest('[data-warehouse-context-host]');
      const path=form?new URL(form.action,location.origin).pathname:'';
      const recipeType=host?.dataset.recipeType || (path.includes('save-semi-raw-material-production')?'semi':path.includes('save-finished-goods-production')?'finished':path.toLowerCase().includes('saveremainig')?'remaining':null);
      if(!recipeType)return;
      const output=document.createElement('div');output.className='small mt-2';output.setAttribute('role','status');box.append(output);let version=0;
      async function availability(){
        const recipeId=(form||document).querySelector('[name=machine_id], [name=model_id]')?.value;
        if(!recipeId){output.textContent='Select a model to see warehouse-specific component stock.';return;}
        const params=new URLSearchParams({recipe_type:recipeType,recipe_id:recipeId});box.querySelectorAll('[name="source_warehouse_ids[]"] option:checked').forEach(o=>params.append('warehouse_ids[]',o.value));
        const location=box.querySelector('[name=source_location_id]').value;if(location)params.set('location_id',location);const current=++version;
        try{const response=await fetch('/warehouses/production-availability?'+params,{headers:{Accept:'application/json'}});const body=await response.json();if(current!==version)return;output.replaceChildren();if(!response.ok){output.textContent='Component availability unavailable. Allocation is validated when saving.';return;}const title=document.createElement('strong');title.textContent='Physical component stock in selected sources';output.append(title);body.materials.forEach(material=>{const row=document.createElement('p');row.className='mb-0';row.textContent=material.name+': '+material.quantity+' ('+material.warehouses.map(w=>w.name+': '+w.quantity).join('; ')+')';output.append(row);});}catch(e){if(current===version)output.textContent='Could not load source stock.';}
      }
      box.addEventListener('change',availability);document.querySelectorAll('[name=model_id],[name=machine_id]').forEach(select=>{if(window.jQuery)jQuery(select).on('change.warehouseAvailability',availability);else select.addEventListener('change',availability);});availability();
    });
    new MutationObserver(records=>records.forEach(record=>record.addedNodes.forEach(node=>{if(node.nodeType!==1)return;if(node.matches('form'))prepare(node);node.querySelectorAll('form').forEach(prepare);const parent=node.closest('form');if(parent)prepare(parent);}))).observe(document.body,{childList:true,subtree:true});
    // AJAX production/scanner workflows retain one idempotency key for retries of identical input.
    const keys=new Map();
    if(window.jQuery)jQuery.ajaxPrefilter(function(options){
      if(!matches(options.url)||String(options.type||'GET').toUpperCase()==='GET')return;
      if(typeof options.data!=='string')return;
      const original=options.url+'|'+options.data;
      if(!keys.has(original))keys.set(original,uuid());
      const params=new URLSearchParams(options.data);
      params.set('warehouse_request_key',keys.get(original));
      const context=document.querySelector('.warehouse-context');
      if(context)context.querySelectorAll('select').forEach(select=>{Array.from(select.selectedOptions).forEach(o=>{if(o.value)params.append(select.name,o.value);});});
      options.data=params.toString();
    });
    if(window.jQuery)jQuery(document).ajaxError(function(event,xhr,settings){
      if(!matches(settings.url))return;
      const message=xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : (xhr.status===403?'Your role cannot post this stock operation.':'Stock operation failed. No inventory changes were committed; reload and retry.');
      showStockError(message);
    });
    // Historical GET stock actions keep the old route but require an idempotent, explicit confirmation.
    document.addEventListener('click',async event=>{
      const link=event.target.closest('a[href]');if(!link||!matches(link.href))return;
      const path=new URL(link.href).pathname;
      if(!/mark-received-transit|save-transit-inventory|mark-complete/.test(path))return;
      event.preventDefault();
      if(/save-transit-inventory\//.test(path)){const id=path.split('/').filter(Boolean).pop();location.assign('/warehouses?'+new URLSearchParams({receipt_type:'raw',receipt_id:id}));return;}
      if(!confirm('Receive/complete stock using the default warehouse allocation? Use Warehouses for an exact receiving location.'))return;
      const url=new URL(link.href);url.searchParams.set('warehouse_request_key',link.dataset.warehouseKey||(link.dataset.warehouseKey=uuid()));
      try{const token=document.querySelector('meta[name=csrf-token]')?.content||document.querySelector('input[name=_token]')?.value||'';const response=await fetch(url,{headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}});if(!response.ok){const body=await response.json();throw Error(body.errors?Object.values(body.errors).flat().join(' '):'Stock operation failed. Reload and retry.');}location.assign(response.redirected?response.url:location.href);}catch(error){showStockError(error.message);}
    });
  }).catch(error=>{const target=document.querySelector('main,.content-body,.container');if(target){const note=document.createElement('p');note.className='alert alert-warning';note.textContent=error.message+' Stock changes remain blocked until configuration is reconciled.';target.prepend(note);}});
})();
