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
  // Enhance existing filter forms only; never insert an additional table toolbar.
  function start() {
    const roots = new Set();
    let scheduled = false;
    new MutationObserver(records => {
      records.forEach(record => record.addedNodes.forEach(node => {
        if (node.nodeType !== 1 || node.closest('.select2-container')) return;
        if (node.matches('select,form')) roots.add(node.parentElement);
        else if (node.querySelector('select,form')) roots.add(node);
      }));
      if (!roots.size || scheduled) return;
      scheduled = true;
      requestAnimationFrame(() => {
        scheduled = false;
        roots.forEach(root => {if(root?.isConnected) {enhanceSelects(root);collapseFilters(root);}});
        roots.clear();
      });
    }).observe(document.documentElement,{childList:true,subtree:true});
  }
  start();
})();
