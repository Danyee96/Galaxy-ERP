/* Searchable, collapsible existing filters. Native table controls remain unchanged. */
(() => {
  'use strict';
  const filterSelects = '.erp-list-tools select, .content-wrapper form[method="get" i] select, .content-wrapper form:not([method]) select, [data-list-filter] select';
  function enhanceSelects(root) {
    const $ = window.jQuery;
    if (!$?.fn?.select2) return;
    root.querySelectorAll(filterSelects).forEach(select => {
      if (select.closest('[data-search-dropdown="off"]')) return;
      const existing = $(select).data('select2');
      if (existing) {existing.options.set('minimumResultsForSearch',0); return;}
      $(select).select2({width:'100%',minimumResultsForSearch:0,dropdownCssClass:'erp-filter-options'});
    });
  }
  function dropdownsReady() {
    enhanceSelects(document);
    const $ = window.jQuery;
    if (!$?.fn?.select2) return;
    $(document).off('.erpListSearch')
      .on('select2:open.erpListSearch',filterSelects,()=>setTimeout(()=>document.querySelector('.select2-container--open .select2-search__field')?.focus(),0));
  }
  window.addEventListener('erp:list-selects-ready',dropdownsReady);
  function updateFilterBadge(panel) {
    const params = new URLSearchParams(window.location.search);
    const count = Array.from(panel.querySelectorAll('input,select,textarea')).filter(control => {
      if (control.closest('.select2-container') || control.type === 'hidden' || control.type === 'submit' || control.disabled) return false;
      if ((control.type === 'checkbox' || control.type === 'radio') && !control.checked) return false;
      if (!control.value || control.value === 'asc') return false;
      return control.name && params.has(control.name);
    }).length;
    const badge = panel.querySelector('[data-filter-active]');
    if (badge) badge.textContent = count ? count+' active' : 'All records';
  }
  function collapseFilters(root) {
    const candidates = [];
    root.querySelectorAll?.('.content-wrapper form[method="get" i],.content-wrapper form:not([method]),.galaxy-workspace form[method="get" i],.galaxy-workspace form:not([method])').forEach(form=>candidates.push(form));
    candidates.forEach(form => {
      if(form.closest('details,.modal,.sidebar,dialog,.filter-dropdown,[data-filter-collapse="off"]') || form.querySelector('input[type="hidden"][name="type"]')) return;
      if(!form.closest('.content-wrapper,.galaxy-workspace')?.querySelector('table')) return;
      const panel=document.createElement('details');panel.className='erp-filter-panel';
      const summary=document.createElement('summary');const title=document.createElement('strong');title.textContent='Filters & sorting';const badge=document.createElement('span');badge.dataset.filterActive='';summary.append(title,badge);panel.append(summary);
      form.before(panel);panel.append(form);
    });
    document.querySelectorAll('.erp-filter-panel').forEach(panel=>{
      if(!panel.dataset.filterBound){panel.dataset.filterBound='true';panel.addEventListener('input',()=>updateFilterBadge(panel));panel.addEventListener('change',()=>updateFilterBadge(panel));panel.addEventListener('click',()=>setTimeout(()=>updateFilterBadge(panel),0));}
      updateFilterBadge(panel);
    });
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',()=>collapseFilters(document),{once:true}); else collapseFilters(document);
  const listTables='.content-wrapper table.table,.main-content table.table,.galaxy-workspace table.table';
  let listsReady=document.readyState!=='loading';
  function styleList(table) {
    if(table.dataset.listStyled || !table.tHead || !table.tBodies.length || table.closest('.serialization-workspace,.modal,dialog,[data-list-style="off"]'))return;
    const editable=Array.from(table.querySelectorAll('tbody input:not([type=hidden]):not([type=checkbox]),tbody select,tbody textarea')).some(control=>!control.closest('.modal,dialog'));
    if(editable)return;
    const headers=Array.from(table.tHead.rows[table.tHead.rows.length-1].cells);
    if(!headers.length || headers.some(cell=>cell.colSpan>1))return;
    table.dataset.listStyled='true';table.classList.add('erp-data-list');
    table.closest('.card,.erp-panel')?.classList.add('erp-data-list-card');
    const wrapper=table.closest('.table-responsive');if(wrapper){wrapper.classList.add('erp-data-list-scroll');if(!wrapper.hasAttribute('tabindex'))wrapper.tabIndex=0;}
    const columns=headers.map(cell=>(cell.textContent||'').replace(/\s+/g,' ').trim().toLowerCase());
    function styleRows(rows){Array.from(rows).forEach(row=>{if(row.cells.length!==columns.length||row.classList.contains('child'))return;columns.forEach((name,index)=>{const cell=row.cells[index];if(/^(serial number|machine barcode|barcode|sku)$/.test(name))cell.classList.add('erp-data-serial');if(name==='model')cell.classList.add('erp-data-model');if(/^(sr\.?|#|id)$/.test(name))cell.classList.add('erp-data-index');if(/^(date|entry date|exit date)$/.test(name))cell.classList.add('erp-data-date');});});}
    styleRows(table.tBodies[0].rows);
    // Pagination/AJAX redraws may create fresh rows; leave all table behavior intact.
    new MutationObserver(records=>{const added=[];records.forEach(record=>record.addedNodes.forEach(node=>{if(node.nodeType===1&&node.tagName==='TR')added.push(node);}));styleRows(added);}).observe(table.tBodies[0],{childList:true});
  }
  function styleLists(root){if(!listsReady)return;if(root.matches?.(listTables))styleList(root);root.querySelectorAll?.(listTables).forEach(styleList);}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>{listsReady=true;styleLists(document);},{once:true});else styleLists(document);
  // Enhance existing controls and list presentation, never insert another toolbar.
  function start() {
    const roots = new Set();
    let scheduled = false;
    new MutationObserver(records => {
      records.forEach(record => record.addedNodes.forEach(node => {
        if (node.nodeType !== 1 || node.closest('.select2-container')) return;
        if (node.matches('select,form,table')) roots.add(node.parentElement);
        else if (node.querySelector('select,form,table')) roots.add(node);
      }));
      if (!roots.size || scheduled) return;
      scheduled = true;
      requestAnimationFrame(() => {
        scheduled = false;
        roots.forEach(root => {if(root?.isConnected) {enhanceSelects(root);collapseFilters(root);styleLists(root);}});
        roots.clear();
      });
    }).observe(document.documentElement,{childList:true,subtree:true});
  }
  start();
})();
