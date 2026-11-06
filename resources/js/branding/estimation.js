(function () {
  'use strict';
  const format = value => { const parts = String(value ?? '0').split('.'); parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); return parts.join('.'); };
  // Both output formats consume the exact server calculation rendered on screen.
  function reportSections(saved) {
    if (saved.sections) return saved.sections;
    const p = saved.plan, n = value => Number(value);
    const sections = [
      { name: 'Summary', rows: [['Galaxy Inverter — Production estimation'], ['Title', saved.title || 'Untitled estimation'], ['Estimate', String(saved.number || 'Unsaved')], ['Date', saved.date], ['Method', p.allocation_method], ['Requested units', n(p.summary.requested_units)], ['Planned units', n(p.summary.planned_units)], ['Feasible whole units', n(p.summary.feasible_units)], ['Shortage materials', n(p.summary.shortage_components)], ['Allocation rule', p.allocation_rule], ['Stock policy', p.stock_policy]] },
      { name: 'Systems', rows: [['System', 'SKU', 'Requested', 'Planned', 'Feasible in mix', 'Maximum alone', 'Weight', 'Bottleneck', 'Shortage materials'], ...p.systems.map(s => [s.name, s.sku, n(s.requested_units), n(s.planned_units), n(s.feasible_units), n(s.standalone_maximum), n(s.weight), s.bottlenecks.join('; '), (s.shortage_requirements || []).map(r => r.name + ': ' + r.quantity).join('; ')])] },
      { name: 'Materials', rows: [['Component', 'SKU', 'Physical', 'Legacy committed', 'Projected', 'In transit', 'Usable', 'Required', 'Allocated', 'Remaining', 'Shortage', 'Consuming systems'], ...p.components.map(c => [c.name, c.sku, ...['physical_stock', 'legacy_committed', 'projected_stock', 'in_transit', 'available', 'required', 'allocated', 'remaining', 'shortage'].map(k => n(c[k])), c.consumers.map(s => s.name + ' × ' + s.units + ' (' + s.per_unit + ' each)').join('; ')])] },
      { name: 'Purchase requirements', rows: [['Component', 'SKU', 'Additional quantity', 'Affected systems'], ...p.components.filter(c => Number(c.shortage) > 0).map(c => [c.name, c.sku, n(c.shortage), (c.affected_models || c.consumers.map(s => s.name)).join('; ')])] }
    ];
    if (p.components.some(c => c.warehouses)) sections.push({name:'Warehouse allocation',rows:[['Component','Warehouse','Physical','Available','Allocated'],...p.components.flatMap(c=>(c.warehouses || []).map(w=>[c.name,w.name,Number(w.physical),Number(w.available),Number(w.allocated)]))]});
    return sections;
  }
  const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[c]));
  function exportExcel(saved) {
    if (!window.XLSX) throw Error('Excel library unavailable. Please reload or use Print.');
    const workbook = XLSX.utils.book_new();
    reportSections(saved).forEach(section => {
      const sheet = XLSX.utils.aoa_to_sheet(section.rows);
      sheet['!cols'] = section.rows[0].map((_, i) => ({ wch: section.name === 'Summary' ? (i ? 100 : 24) : (i === 0 || i === section.rows[0].length - 1 ? 38 : (i === 1 ? 20 : 17)) }));
      if (section.name === 'Summary') sheet['!cols'] = [{wch:24}, {wch:100}];
      Object.keys(sheet).filter(k => k[0] !== '!').forEach(k => { if (sheet[k].t === 'n') sheet[k].z = '#,##0.####'; });
      XLSX.utils.book_append_sheet(workbook, sheet, section.name);
    });
    // SheetJS CE writes data/widths; reuse its ZIP support to apply native OOXML styles.
    const zip = XLSX.CFB.read(new Uint8Array(XLSX.write(workbook, {bookType:'xlsx', type:'array'})), {type:'array'});
    const styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.####"/></numFmts><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF164B76"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border/><border><left style="thin"><color rgb="FFD4DCE5"/></left><right style="thin"><color rgb="FFD4DCE5"/></right><top style="thin"><color rgb="FFD4DCE5"/></top><bottom style="thin"><color rgb="FFD4DCE5"/></bottom></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="164" fontId="0" fillId="0" borderId="1" applyNumberFormat="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    const encoder = new TextEncoder(), decoder = new TextDecoder();
    zip.FullPaths.forEach((path, i) => {
      const entry = zip.FileIndex[i];
      if (path.endsWith('/xl/styles.xml')) entry.content = encoder.encode(styles);
      if (/\/xl\/worksheets\/sheet\d+\.xml$/.test(path)) {
        const xml = decoder.decode(entry.content).replace(/<c\b([^>]*)>/g, (tag, attrs) => {
          const header = /\br="[A-Z]+1"/.test(attrs), numeric = !/\bt="/.test(attrs) || /\bt="n"/.test(attrs);
          return '<c' + attrs.replace(/\s+s="[^"]*"/g, '') + ' s="' + (header ? 1 : (numeric ? 2 : 0)) + '">';
        });
        entry.content = encoder.encode(xml);
      }
      entry.size = entry.content ? entry.content.length : entry.size;
    });
    const blob = new Blob([XLSX.CFB.write(zip, {fileType:'zip', type:'array'})], {type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
    const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = 'Production_Plan_' + (saved.number || 'Unsaved') + '.xlsx'; link.click(); setTimeout(() => URL.revokeObjectURL(link.href), 1000);
  }
  function printReport(saved) {
    document.getElementById('estimation-print-frame')?.remove();
    const frame = document.createElement('iframe'); frame.id = 'estimation-print-frame'; frame.title = 'Estimation print preview'; frame.style.cssText = 'position:fixed;width:0;height:0;border:0;';
    frame.setAttribute('sandbox', 'allow-same-origin allow-modals');
    frame.srcdoc = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Galaxy Inverter estimation</title><style>@page{size:A4 landscape;margin:12mm}body{font:10px Arial,sans-serif;color:#172b40}h1{font-size:19px}h2{font-size:13px;margin-top:18px}table{width:100%;border-collapse:collapse;table-layout:fixed;margin-bottom:14px}th,td{border:1px solid #ccd5df;padding:5px;text-align:left;vertical-align:top;overflow-wrap:anywhere}th{background:#edf2f7}thead{display:table-header-group}tr{break-inside:avoid}h2{break-after:avoid}p{font-size:9px}</style></head><body><h1>Galaxy Inverter — Production estimation</h1>' + reportSections(saved).map(section => '<h2>' + escapeHtml(section.name) + '</h2><table><thead><tr>' + section.rows[0].map(v => '<th>' + escapeHtml(v) + '</th>').join('') + '</tr></thead><tbody>' + section.rows.slice(1).map(row => '<tr>' + row.map(v => '<td>' + escapeHtml(v) + '</td>').join('') + '</tr>').join('') + '</tbody></table>').join('') + '<p>Planning only. No inventory is reserved or deducted.</p></body></html>';
    frame.onload = () => { frame.contentWindow.focus(); frame.contentWindow.print(); };
    document.body.appendChild(frame);
  }
  function bindExports(getData, error) {
    [['plan-export-excel', exportExcel], ['plan-print', printReport]].forEach(([id, action]) => {
      document.getElementById(id)?.addEventListener('click', () => {
        try { const data = getData(); if (!data) throw Error('Wait for a valid calculation before exporting.'); action(data); }
        catch (e) { error.hidden = false; error.textContent = e.message || 'Could not export this estimation.'; }
      });
    });
  }
  const exportData = document.getElementById('plan-export-data');
  if (exportData) bindExports(() => JSON.parse(exportData.textContent), document.getElementById('plan-export-error'));
  const root = document.getElementById('production-planner');
  if (!root) return;
  const form = document.getElementById('planner-form'), rows = document.getElementById('planner-rows');
  const mode = document.getElementById('plan-mode'), save = document.getElementById('planner-save');
  const status = document.getElementById('planner-status'), error = document.getElementById('planner-error');
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  let revision = 0, serial = 0, timer, controller, plan = null, saving = false;
  function uuid() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => { const r = Math.floor(Math.random() * 16); return (c === 'x' ? r : (r & 3 | 8)).toString(16); });
  }
  let requestKey = uuid();
  const exportButtons = ['plan-export-excel', 'plan-print'].map(id => document.getElementById(id));
  const enableExports = enabled => exportButtons.forEach(button => { button.disabled = !enabled; });
  bindExports(() => {
    if (!plan || saving) return null;
    const invalid = Array.from(form.querySelectorAll('input,select')).find(field => field.id !== 'plan-title' && !field.disabled && !field.checkValidity());
    if (invalid) { invalid.reportValidity(); return null; }
    return {title: input().title, date: input().plan_date, number: null, plan};
  }, error);
  function input() {
    return { title: document.getElementById('plan-title').value.trim(), plan_date: document.getElementById('plan-date').value, mode: mode.value, warehouse_ids: Array.from(document.getElementById('plan-warehouses')?.selectedOptions || []).map(o => Number(o.value)),
      models: Array.from(rows.children).filter(row => row.querySelector('.planner-model').value).map(row => ({ product_id: Number(row.querySelector('.planner-model').value), quantity: Number(row.querySelector('.planner-quantity').value), weight: Number(row.querySelector('.planner-weight').value) })) };
  }
  function feedback(message, failed) { status.textContent = message; status.classList.toggle('is-error', !!failed); }
  function showError(message) { error.textContent = message; error.hidden = false; }
  function syncRows() {
    const selected = Array.from(rows.querySelectorAll('.planner-model')).map(select => select.value).filter(Boolean);
    Array.from(rows.children).forEach((row, i) => {
      row.querySelector('.planner-position').textContent = i + 1;
      row.querySelector('[data-row-action="up"]').disabled = i === 0;
      row.querySelector('[data-row-action="down"]').disabled = i === rows.children.length - 1;
      row.querySelector('.planner-quantity').disabled = mode.value !== 'manual';
      row.querySelector('.planner-weight').disabled = mode.value !== 'balanced';
      const select = row.querySelector('.planner-model');
      Array.from(select.options).forEach(option => { option.disabled = option.value !== '' && option.value !== select.value && selected.includes(option.value); });
      if (window.jQuery && jQuery.fn.select2) jQuery(select).trigger('change.select2');
    });
    root.classList.toggle('planner-balanced', mode.value === 'balanced');
    document.getElementById('planner-add').disabled = rows.children.length >= 100;
    document.getElementById('planner-rule').textContent = {
      manual: 'Enter requested whole units. Row order determines feasible allocation when the complete request cannot be met.',
      priority: 'Higher rows consume stock first. The server proposes the maximum whole units per model; requested quantities do not cap this mode.',
      balanced: 'Enter positive integer ratio weights. The server allocates complete ratio batches and one partial weighted round; unused stock may remain.'
    }[mode.value];
  }
  function addRow(values) {
    const row = document.getElementById('planner-row-template').content.firstElementChild.cloneNode(true);
    serial++;
    row.querySelectorAll('label').forEach((label, i) => { const field = label.nextElementSibling; field.id = 'plan-field-' + serial + '-' + i; label.htmlFor = field.id; });
    rows.appendChild(row);
    const select = row.querySelector('.planner-model');
    if (values) { select.value = values.product_id; row.querySelector('.planner-quantity').value = values.quantity; row.querySelector('.planner-weight').value = values.weight || 1; }
    if (window.jQuery && jQuery.fn.select2) {
      jQuery(select).select2({ width: '100%', placeholder: 'Search system models' }).on('change.planner', schedule);
    }
    syncRows();
  }
  function clearResults() {
    ['requested', 'planned', 'feasible', 'shortages'].forEach(id => { document.getElementById('planner-' + id).textContent = '—'; });
    [['planner-capacity', 7], ['planner-materials', 9]].forEach(([id, colspan]) => { const cell = document.createElement('td'); cell.colSpan = colspan; cell.className = 'planner-empty'; cell.textContent = 'Select models to calculate requirements.'; const tr = document.createElement('tr'); tr.appendChild(cell); document.getElementById(id).replaceChildren(tr); });
    document.getElementById('planner-purchase-section').hidden = true;
    document.getElementById('planner-bottlenecks').textContent = '';
    document.getElementById('planner-allocation-rule').textContent = '';
    document.getElementById('planner-use-proposal').hidden = true;
  }
  function schedule() {
    if (saving) return;
    enableExports(false); revision++; plan = null; requestKey = uuid(); save.disabled = true; error.hidden = true;
    clearTimeout(timer); if (controller) controller.abort();
    syncRows();
    if (!input().models.length) { root.setAttribute('aria-busy', 'false'); clearResults(); feedback('Select a system to calculate a plan.'); return; }
    feedback('Recalculating combined requirements…');
    timer = setTimeout(() => calculate(revision), 350);
  }
  async function responseBody(response) {
    if ([401, 419].includes(response.status)) throw Error('Your session expired. Reload the page and sign in again.');
    if (response.status === 403) throw Error('Your role does not have access to production planning.');
    const body = await response.json();
    if (!response.ok && response.status !== 409) throw Error(body.errors ? Object.values(body.errors).flat().join(' ') : (body.message || 'The calculation failed. Please retry.'));
    return body;
  }
  async function calculate(epoch) {
    controller = new AbortController();
    root.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch(root.dataset.previewUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, signal: controller.signal, body: JSON.stringify(input()) });
      const body = await responseBody(response);
      if (epoch !== revision) return;
      render(body.plan); plan = body.plan; save.disabled = false; enableExports(true);
    } catch (e) {
      if (e.name !== 'AbortError' && epoch === revision) { clearResults(); feedback('Could not calculate the plan.', true); showError(e.message || 'Connection failed. Use Recalculate to retry.'); }
    } finally { if (epoch === revision) root.setAttribute('aria-busy', 'false'); }
  }
  function cell(row, text, className) { const td = document.createElement('td'); td.textContent = text; if (className) td.className = className; row.appendChild(td); return td; }
  function identity(row, name, sku) { const td = cell(row, ''); const strong = document.createElement('strong'); strong.textContent = name; const small = document.createElement('small'); small.textContent = sku || 'No SKU'; td.append(strong, small); return td; }
  function render(current) {
    document.getElementById('planner-requested').textContent = format(current.summary.requested_units);
    document.getElementById('planner-planned').textContent = format(current.summary.planned_units);
    document.getElementById('planner-feasible').textContent = format(current.summary.feasible_units);
    document.getElementById('planner-shortages').textContent = format(current.summary.shortage_components);
    feedback(current.summary.planned_units === 0 ? 'No complete systems can be allocated with the usable stock.' : (current.summary.feasible ? 'The combined plan is feasible.' : 'The complete requested plan needs additional materials.'));
    document.getElementById('planner-bottlenecks').textContent = current.summary.bottlenecks.length ? 'Limiting components: ' + current.summary.bottlenecks.join(', ') : 'No selected model is blocked by the remaining material pool.';
    document.getElementById('planner-stock-policy').textContent = current.stock_policy;
    document.getElementById('planner-allocation-rule').textContent = current.allocation_rule;
    document.getElementById('planner-use-proposal').hidden = current.allocation_method === 'manual';
    const capacity = document.getElementById('planner-capacity'); capacity.replaceChildren();
    current.systems.forEach(system => { const row = document.createElement('tr'); identity(row, system.name, system.sku); ['requested_units', 'planned_units', 'feasible_units', 'standalone_maximum'].forEach(key => cell(row, format(system[key]), 'planner-numeric')); cell(row, system.bottlenecks.join(', ')); cell(row, (system.shortage_requirements || []).map(r => r.name + ': ' + r.quantity).join('; ') || 'None'); capacity.appendChild(row); });
    const materials = document.getElementById('planner-materials'), purchases = document.getElementById('planner-purchases'); materials.replaceChildren(); purchases.replaceChildren();
    current.components.forEach(component => {
      const row = document.createElement('tr'); identity(row, component.name, component.sku);
      const physical = cell(row, format(component.physical_stock), 'planner-numeric'); const projected = document.createElement('small'); projected.textContent = 'Projected ' + format(component.projected_stock) + ' · Incoming ' + format(component.in_transit); physical.appendChild(projected);
      (component.warehouses || []).forEach(w => { const small = document.createElement('small'); small.textContent = w.name + ': ' + format(w.physical) + ' physical · ' + format(w.available) + ' usable · ' + format(w.allocated) + ' allocated'; physical.appendChild(small); });
      ['legacy_committed', 'available', 'required', 'allocated', 'remaining', 'shortage'].forEach(key => cell(row, format(component[key]), 'planner-numeric' + (key === 'shortage' && component[key] !== '0' ? ' plan-shortage' : '')));
      cell(row, component.consumers.map(s => s.name + ' × ' + format(s.units) + ' (' + s.per_unit + ' each)').join('; ')); materials.appendChild(row);
      if (component.shortage !== '0') { const shortage = document.createElement('tr'); identity(shortage, component.name, component.sku); cell(shortage, format(component.shortage), 'plan-shortage'); cell(shortage, (component.affected_models || component.consumers.map(s => s.name)).join(', ')); purchases.appendChild(shortage); }
    });
    document.getElementById('planner-purchase-section').hidden = current.summary.shortage_components === 0;
  }
  document.getElementById('planner-add').addEventListener('click', () => { if (rows.children.length < 100) { addRow(); schedule(); const field = rows.lastElementChild.querySelector('.planner-model'); field.focus(); } });
  document.getElementById('planner-recalculate').addEventListener('click', schedule);
  rows.addEventListener('click', event => {
    const action = event.target.closest('[data-row-action]'); if (!action) return;
    const row = action.closest('.planner-model-row');
    if (action.dataset.rowAction === 'remove') { if (window.jQuery && jQuery.fn.select2) jQuery(row.querySelector('.planner-model')).select2('destroy'); row.remove(); if (!rows.children.length) addRow(); }
    if (action.dataset.rowAction === 'up' && row.previousElementSibling) rows.insertBefore(row, row.previousElementSibling);
    if (action.dataset.rowAction === 'down' && row.nextElementSibling) rows.insertBefore(row.nextElementSibling, row);
    schedule();
  });
  rows.addEventListener('input', schedule);
  if (!(window.jQuery && jQuery.fn.select2)) rows.addEventListener('change', schedule);
  mode.addEventListener('change', schedule);
  document.getElementById('plan-warehouses')?.addEventListener('change', schedule);
  document.getElementById('plan-date').addEventListener('change', schedule);
  document.getElementById('plan-title').addEventListener('input', () => { requestKey = uuid(); });
  document.getElementById('planner-use-proposal').addEventListener('click', () => {
    if (!plan) return;
    if (plan.systems.some(s => s.planned_units > 1000000)) { showError('The proposal exceeds the manual entry limit of 1,000,000 units per model. Adjust requested quantities explicitly.'); return; }
    const proposal = plan.systems.slice();
    rows.querySelectorAll('.planner-model').forEach(select => { if (window.jQuery && jQuery.fn.select2) jQuery(select).select2('destroy'); }); rows.replaceChildren();
    mode.value = 'manual'; proposal.forEach(s => addRow({ product_id: s.product_id, quantity: s.planned_units, weight: s.weight })); schedule();
  });
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (!plan || saving || !form.reportValidity()) return;
    saving = true; enableExports(false); error.hidden = true;
    const payload = Object.assign(input(), { preview_fingerprint: plan.fingerprint, request_key: requestKey });
    const controls = Array.from(form.querySelectorAll('input, select, button')).map(control => [control, control.disabled]); controls.forEach(([control]) => { control.disabled = true; });
    save.textContent = 'Saving snapshot…';
    try {
      const response = await fetch(root.dataset.saveUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload) });
      const body = await responseBody(response);
      if (response.status === 409) { plan = body.plan; render(plan); showError(body.message); error.focus(); }
      else { window.location.assign(body.url); return; }
    } catch (e) { showError(e.message || 'Save failed. Retry with the same plan to avoid duplicates.'); error.focus(); }
    saving = false; controls.forEach(([control, disabled]) => { control.disabled = disabled; }); save.disabled = !plan; save.textContent = 'Save production plan'; enableExports(!!plan); syncRows();
  });
  addRow();
})();
