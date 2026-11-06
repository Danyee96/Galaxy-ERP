/* Shared tools for read-only ERP lists. Existing server filters remain authoritative. */
(() => {
  'use strict';
  const collator = new Intl.Collator(undefined, {numeric: true, sensitivity: 'base'});
  const text = cell => (cell?.textContent || '').replace(/\s+/g, ' ').trim();
  function dataTable(table) {
    const $ = window.jQuery;
    return $?.fn?.dataTable?.isDataTable(table) ? $(table).DataTable() : null;
  }
  function initialize(table) {
    if (table.dataset.listTools || !table.tHead || !table.tBodies.length || table.closest('[data-list-tools="off"],.erp-list-print')) return;
    // Editable line-item tables retain their existing form and calculation behavior.
    if (table.querySelector('tbody input:not([type=hidden]):not([type=checkbox]),tbody select,tbody textarea')) return;
    const headers = Array.from(table.tHead.rows[table.tHead.rows.length - 1].cells);
    if (!headers.length || headers.some(h => h.colSpan > 1)) return;
    table.dataset.listTools = 'true';
    const columns = headers.map((h, index) => ({index, label: text(h)})).filter(c => c.label && !/^(sr\.?|s\.?\s*no\.?|#|actions?|select|image|photo)$/i.test(c.label));
    if (!columns.length) return;
    const bar = document.createElement('div');
    bar.className = 'erp-list-tools';
    bar.dataset.listClient = 'true';
    const field = (label, element) => { const wrap = document.createElement('label'); wrap.textContent = label; element.setAttribute('aria-label', label); wrap.append(element); bar.append(wrap); return element; };
    const search = field('Search records', document.createElement('input'));
    search.type = 'search'; search.placeholder = 'Search this list…';
    const column = field('Filter column', document.createElement('select'));
    column.add(new Option('All columns', ''));
    columns.forEach(c => column.add(new Option(c.label, String(c.index))));
    const value = field('Field value', document.createElement('select'));
    value.add(new Option('Select a filter column first', '')); value.disabled = true;
    const sort = field('Sort by', document.createElement('select')); sort.add(new Option('Original order', ''));
    columns.forEach(c => sort.add(new Option(c.label, String(c.index))));
    const direction = field('Order', document.createElement('select')); direction.add(new Option('Ascending', 'asc')); direction.add(new Option('Descending', 'desc'));
    const button = (label, callback) => {const el = document.createElement('button'); el.type = 'button'; el.className = 'btn btn-sm btn-outline-primary'; el.textContent = label; el.addEventListener('click', callback); bar.append(el); return el;};
    const status = document.createElement('span'); status.className = 'erp-list-tools-status'; status.setAttribute('role', 'status');
    const host = table.closest('.table-responsive') || table;
    host.before(bar);
    const original = Array.from(table.tBodies[0].rows);
    const originalRows = new Set(original);
    const isRecord = row => row.cells.length === headers.length && !row.querySelector('td[colspan]');
    const normalize = value => value.replace(/\s+/g, ' ').trim().toLocaleLowerCase();
    function populateValues() {
      const previous = value.value;
      value.replaceChildren(new Option(column.value === '' ? 'Select a filter column first' : 'All values', ''));
      value.disabled = column.value === '';
      if (value.disabled) return;
      const dt = dataTable(table);
      const rows = dt ? dt.rows({search: 'none', page: 'all'}).nodes().toArray() : Array.from(table.tBodies[0].rows);
      const distinct = new Map();
      rows.filter(isRecord).forEach(row => {
        const label = text(row.cells[Number(column.value)]), key = normalize(label);
        if (!distinct.has(key)) distinct.set(key, label);
      });
      Array.from(distinct.entries()).sort((a,b) => collator.compare(a[1],b[1])).forEach(([key,label]) => value.add(new Option(label || '(Blank)', JSON.stringify(key))));
      if (Array.from(value.options).some(option => option.value === previous)) value.value = previous;
    }
    const matches = row => {
      const needle = search.value.toLocaleLowerCase().trim(); const filter = value.value;
      return (!needle || text(row).toLocaleLowerCase().includes(needle)) && (!filter || normalize(text(row.cells[Number(column.value)])) === JSON.parse(filter));
    };
    let registered = false;
    function apply() {
      const dt = dataTable(table);
      if (dt) {
        if (!registered) {
          window.jQuery.fn.dataTable.ext.search.push((settings, data, dataIndex) => {
            if (settings.nTable !== table) return true;
            const needle = search.value.toLocaleLowerCase().trim(), filter = value.value;
            const all = data.join(' ').toLocaleLowerCase();
            return (!needle || all.includes(needle)) && (!filter || normalize(text(settings.aoData[dataIndex]?.nTr?.cells[Number(column.value)])) === JSON.parse(filter));
          });
          registered = true;
        }
        dt.order(sort.value === '' ? [] : [[Number(sort.value), direction.value]]).draw();
        status.textContent = `${dt.rows({search: 'applied'}).count()} matching records`;
      } else {
        const rows = Array.from(table.tBodies[0].rows).filter(isRecord);
        rows.forEach(row => { if (!originalRows.has(row)) {originalRows.add(row); original.push(row);} });
        rows.forEach(row => {row.hidden = !matches(row);});
        if (sort.value !== '') {
          const index = Number(sort.value), sign = direction.value === 'desc' ? -1 : 1;
          const key = row => {
            const cell = row.cells[index]; const raw = cell?.dataset.order || text(cell);
            if (/^\d{1,2}[-/]([a-z]{3}|\d{1,2})[-/]\d{4}$/i.test(raw) || /^\d{4}-\d{2}-\d{2}$/.test(raw)) {
              const parsed = Date.parse(raw); if (!Number.isNaN(parsed)) return parsed;
            }
            const numeric = raw.replace(/,/g, '');
            return /^-?\d+(\.\d+)?$/.test(numeric) ? Number(numeric) : raw;
          };
          rows.sort((a,b) => {const x=key(a), y=key(b); return sign * (typeof x === 'number' && typeof y === 'number' ? x-y : collator.compare(String(x),String(y)));}).forEach(row => table.tBodies[0].append(row));
        } else original.filter(row => row.isConnected).forEach(row => table.tBodies[0].append(row));
        status.textContent = `${rows.filter(row => !row.hidden).length} of ${rows.length} loaded records`;
      }
    }
    button('Reset list', () => {search.value = ''; value.value = ''; column.value = ''; sort.value = ''; direction.value = 'asc'; populateValues(); apply();});
    button('Print filtered data', () => {
      const dt = dataTable(table);
      const rows = dt ? dt.rows({search:'applied', order:'applied'}).nodes().toArray() : Array.from(table.tBodies[0].rows).filter(row => !row.hidden && row.style.display !== 'none');
      const frame = document.createElement('iframe'); frame.className = 'erp-list-print-frame'; frame.title = 'Filtered list print preview'; document.body.append(frame);
      const doc = frame.contentDocument;
      const style = doc.createElement('style'); style.textContent = '@page{size:A4 landscape;margin:12mm}body{font:10px Arial,sans-serif;color:#111}h1{font-size:18px}p{color:#555}table{width:100%;border-collapse:collapse;table-layout:auto}th,td{padding:6px;border:1px solid #ccc;text-align:left;overflow-wrap:anywhere}thead{display:table-header-group}tr{break-inside:avoid}a{color:inherit;text-decoration:none}button,input,select,textarea,.dropdown,.btn{display:none!important}'; doc.head.append(style);
      const title = doc.createElement('h1'); title.textContent = `Galaxy Inverter — ${document.querySelector('.content-header-title')?.textContent.trim() || document.title}`; doc.body.append(title);
      const info = doc.createElement('p'); info.textContent = `${rows.length} filtered loaded records · ${new Date().toLocaleString()}${search.value ? ' · Search: '+search.value : ''}${value.value ? ' · '+(column.selectedOptions[0]?.textContent || 'Filter')+': '+value.selectedOptions[0].textContent : ''}`; doc.body.append(info);
      const copy = doc.createElement('table'); copy.append(table.tHead.cloneNode(true)); const body = doc.createElement('tbody'); rows.forEach(row => {const clone=row.cloneNode(true); clone.hidden=false; clone.removeAttribute('style'); body.append(clone);}); copy.append(body);
      // Copy text only: no event handlers, interactive actions, or untrusted markup in the print document.
      copy.querySelectorAll('script,style,button,input,select,textarea,.dropdown,.btn').forEach(el=>el.remove());
      const actionColumns = headers.map((h,i)=> /^(actions?|select)$/i.test(text(h)) ? i : -1).filter(i=>i>=0).reverse();
      copy.querySelectorAll('tr').forEach(row=>{actionColumns.forEach(i=>row.cells[i]?.remove()); Array.from(row.cells).forEach(cell=>{cell.textContent=text(cell); cell.removeAttribute('style'); cell.removeAttribute('width');});});
      doc.body.append(copy);
      frame.contentWindow.addEventListener('afterprint',()=>frame.remove(),{once:true});
      setTimeout(()=>{frame.contentWindow.focus();frame.contentWindow.print();},100);
    });
    bar.append(status);
    let timer;
    search.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(apply,180);});
    column.addEventListener('change',()=>{value.value=''; populateValues(); apply();});
    value.addEventListener('focus',populateValues);
    [value,sort,direction].forEach(el=>el.addEventListener('change',apply));
    status.textContent = 'Filters and printing apply to loaded records; use page filters for the full dataset.';
    enhanceSelects(bar);
  }
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
      .on('select2:open.erpListSearch',filterSelects,()=>setTimeout(()=>document.querySelector('.select2-container--open .select2-search__field')?.focus(),0))
      .on('select2:select.erpListSearch select2:unselect.erpListSearch','.erp-list-tools[data-list-client] select',function(){this.dispatchEvent(new Event('change',{bubbles:true}));});
  }
  window.addEventListener('erp:list-selects-ready',dropdownsReady);
  function scan() {document.querySelectorAll('.content-wrapper table,.main-content table').forEach(initialize);}
  function start() {
    scan();
    const pending = new Set();
    const selectRoots = new Set();
    let scheduled = false;
    new MutationObserver(records => {
      records.forEach(record => record.addedNodes.forEach(node => {
        if (node.nodeType !== 1 || node.closest('.select2-container,.erp-list-print-frame')) return;
        if (node.matches('select')) selectRoots.add(node.parentElement);
        else if (node.querySelector('select')) selectRoots.add(node);
        if (node.closest('.erp-list-tools')) return;
        const parentTable = node.closest('table');
        if (parentTable && !parentTable.dataset.listTools) pending.add(parentTable);
        node.querySelectorAll('table:not([data-list-tools])').forEach(table => pending.add(table));
      }));
      if ((!pending.size && !selectRoots.size) || scheduled) return;
      scheduled = true;
      requestAnimationFrame(() => {
        scheduled = false;
        pending.forEach(table => {if (table.isConnected && table.closest('.content-wrapper,.main-content')) initialize(table);});
        pending.clear();
        selectRoots.forEach(root => {if(root?.isConnected) enhanceSelects(root);});
        selectRoots.clear();
      });
    }).observe(document.documentElement,{childList:true,subtree:true});
  }
  // Initialize as table markup arrives, even when unrelated external assets delay DOMContentLoaded.
  start();
})();
