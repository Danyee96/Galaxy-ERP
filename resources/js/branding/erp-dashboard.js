(function () {
  'use strict';
  var charts = [];
  // Distinct categorical colors; keep one color per slice instead of cycling eight muted shades.
  var palette = ['#2563eb', '#06b6d4', '#8b5cf6', '#f59e0b', '#10b981', '#ec4899', '#f97316', '#6366f1', '#14b8a6', '#e11d48', '#0ea5e9', '#84cc16', '#a855f7', '#d97706', '#059669', '#db2777', '#4f46e5', '#0891b2', '#65a30d', '#c026d3', '#ea580c', '#7c3aed', '#0284c7', '#be123c'];
  function chartColors(count) {
    return Array.from({ length: count }, function (_, index) {
      return palette[index] || 'hsl(' + Math.round(index * 137.508 % 360) + ', 72%, 48%)';
    });
  }
  function appearance() {
    var styles = getComputedStyle(document.body);
    return { theme: { mode: document.documentElement.dataset.erpTheme === 'dark' ? 'dark' : 'light' }, chart: { foreColor: styles.getPropertyValue('--erp-muted').trim() }, grid: { borderColor: styles.getPropertyValue('--erp-border').trim() } };
  }
  document.querySelectorAll('.erp-dashboard .erp-chart[data-chart]').forEach(function (element) {
    var state = element.parentElement.querySelector('[data-chart-state]');
    function message(text, error) { state.hidden = false; state.textContent = text; state.classList.toggle('is-error', !!error); }
    // Leave any deployed, working chart initializer in control of its own chart.
    if (element.querySelector('.apexcharts-canvas')) { state.hidden = true; return; }
    var data;
    try { data = JSON.parse(element.dataset.chart); } catch (e) { message('Chart data is unavailable.', true); return; }
    var series = data.series.map(function (item) { return { name: item.name, data: item.data.map(Number) }; });
    if (!series.some(function (item) { return item.data.some(function (value) { return value !== 0; }); })) { element.parentElement.classList.add('is-empty'); message(data.empty); return; }
    if (typeof window.ApexCharts !== 'function') { message('Chart unavailable. Use View data or reload this page.', true); return; }
    var theme = appearance();
    var isLine = data.type === 'line';
    var options = {
      chart: { type: data.type, height: 274, background: 'transparent', fontFamily: getComputedStyle(document.body).fontFamily, foreColor: theme.chart.foreColor, toolbar: { show: false }, animations: { enabled: false } },
      theme: theme.theme,
      series: isLine ? series : (series[0] ? series[0].data : []),
      labels: data.labels,
      colors: chartColors(isLine ? series.length : data.labels.length),
      grid: { borderColor: theme.grid.borderColor, strokeDashArray: 3 },
      dataLabels: { enabled: false },
      legend: { position: 'bottom', fontSize: '11px', height: 70, itemMargin: { horizontal: 8, vertical: 3 } },
      stroke: { width: isLine ? 2 : 1, colors: isLine ? undefined : [getComputedStyle(document.body).getPropertyValue('--erp-surface').trim()], curve: 'straight' },
      fill: { opacity: 1 },
      plotOptions: { pie: { donut: { size: '72%' } } },
      tooltip: { theme: theme.theme.mode },
      noData: { text: data.empty },
      responsive: [{ breakpoint: 480, options: { chart: { height: 260 }, legend: { height: 90 } } }]
    };
    if (isLine) {
      options.xaxis = { categories: data.labels, labels: { rotate: 0, hideOverlappingLabels: true, style: { fontSize: '10px' } }, axisBorder: { show: false }, axisTicks: { show: false } };
      options.yaxis = { min: 0, forceNiceScale: true, labels: { formatter: function (value) { return Math.round(value).toLocaleString(); } } };
      options.legend.height = 32;
    }
    try {
      var chart = new window.ApexCharts(element, options);
      Promise.resolve(chart.render()).then(function () { state.hidden = true; charts.push(chart); }).catch(function () { message('Chart unavailable. Exact values are available under View data.', true); });
    } catch (e) { message('Chart unavailable. Exact values are available under View data.', true); }
  });
  document.addEventListener('galaxy:themechange', function () {
    var settings = appearance();
    charts.forEach(function (chart) { chart.updateOptions({ theme: settings.theme, chart: settings.chart, grid: settings.grid, stroke: { colors: chart.w.config.chart.type === 'line' ? undefined : [getComputedStyle(document.body).getPropertyValue('--erp-surface').trim()] }, tooltip: { theme: settings.theme.mode } }, false, false); });
  });
})();
