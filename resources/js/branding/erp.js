(function () {
  'use strict';
  if (!document.body.classList.contains('erp-shell')) return;
  var root = document.documentElement;
  var toggle = document.querySelector('[data-erp-theme-toggle]');
  var searchToggle = document.querySelector('[data-erp-search-toggle]');
  var input = document.getElementById('brand-page-search');
  var results = document.getElementById('brand-search-results');
  var navigation = document.getElementById('main-menu-navigation');
  var sidebar = document.querySelector('.main-menu.brand-sidebar');
  var menuButton = document.querySelector('.brand-context .menu-toggle');
  var media = window.matchMedia('(prefers-color-scheme: dark)');

  function preference() {
    try { return localStorage.getItem('galaxy-erp-theme'); } catch (e) { return null; }
  }
  function setTheme(theme, persist) {
    root.dataset.erpTheme = theme;
    if (persist) { try { localStorage.setItem('galaxy-erp-theme', theme); } catch (e) { /* Session-only theme when storage is unavailable. */ } }
    if (toggle) {
      toggle.setAttribute('aria-pressed', String(theme === 'dark'));
      toggle.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
      toggle.title = toggle.getAttribute('aria-label');
      toggle.querySelector('i').className = theme === 'dark' ? 'bx bx-sun' : 'bx bx-moon';
    }
    document.dispatchEvent(new CustomEvent('galaxy:themechange', { detail: { theme: theme } }));
  }
  setTheme(root.dataset.erpTheme || 'light', false);
  if (toggle) toggle.addEventListener('click', function () { setTheme(root.dataset.erpTheme === 'dark' ? 'light' : 'dark', true); });
  function systemTheme(event) { if (!['dark', 'light'].includes(preference())) setTheme(event.matches ? 'dark' : 'light', false); }
  if (media.addEventListener) media.addEventListener('change', systemTheme);
  window.addEventListener('storage', function (event) {
    if (event.key === 'galaxy-erp-theme') setTheme(['dark', 'light'].includes(event.newValue) ? event.newValue : (media.matches ? 'dark' : 'light'), false);
  });
  function openSearch(open) {
    document.body.classList.toggle('erp-search-open', open);
    if (searchToggle) searchToggle.setAttribute('aria-expanded', String(open));
    if (open && input) input.focus();
    if (!open && results) { results.hidden = true; input.setAttribute('aria-expanded', 'false'); }
  }
  if (searchToggle) searchToggle.addEventListener('click', function () { openSearch(!document.body.classList.contains('erp-search-open')); });
  if (input) {
    var hint = input.parentElement.querySelector('kbd');
    if (hint && /Mac|iPhone|iPad/.test(navigator.platform)) hint.textContent = '⌘ K';
  }
  document.addEventListener('keydown', function (event) {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k' && input) { event.preventDefault(); openSearch(true); }
    if (event.key === 'Escape') {
      if (document.body.classList.contains('erp-search-open')) { openSearch(false); if (searchToggle) searchToggle.focus(); }
      if (document.body.classList.contains('menu-open') && menuButton) { menuButton.click(); menuButton.focus(); }
    }
    if (event.key === 'Tab' && sidebar && window.innerWidth < 1200 && document.body.classList.contains('menu-open')) {
      var controls = Array.from(sidebar.querySelectorAll('a, button, [tabindex="0"]')).filter(function (control) { return control.getClientRects().length && getComputedStyle(control).visibility !== 'hidden'; });
      var first = controls[0], last = controls[controls.length - 1];
      if (first && ((!sidebar.contains(event.target)) || (event.shiftKey && event.target === first) || (!event.shiftKey && event.target === last))) {
        event.preventDefault(); (event.shiftKey ? last : first).focus();
      }
    }
    if (results && results.contains(event.target) && ['ArrowDown', 'ArrowUp'].includes(event.key)) {
      var links = Array.from(results.querySelectorAll('a'));
      var position = links.indexOf(document.activeElement);
      if (!links.length) return;
      event.preventDefault();
      links[(position + (event.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length].focus();
    }
  });
  if (navigation) {
    navigation.querySelectorAll('li.active > a').forEach(function (link) { link.setAttribute('aria-current', 'page'); });
    navigation.querySelectorAll('li.has-sub > a').forEach(function (link) {
      link.addEventListener('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); link.click(); } });
    });
  }
  if (menuButton) {
    function syncMenu() {
      if (window.innerWidth >= 1200) {
        if (document.body.classList.contains('menu-collapsed') || document.body.classList.contains('menu-hide')) document.body.classList.remove('menu-collapsed', 'menu-hide');
        if (!document.body.classList.contains('menu-expanded')) document.body.classList.add('menu-expanded');
      }
      var expanded = window.innerWidth >= 1200 ? !document.body.classList.contains('menu-collapsed') : document.body.classList.contains('menu-open');
      menuButton.setAttribute('aria-expanded', String(expanded));
      var mobile = window.innerWidth < 1200;
      if (sidebar) {
        sidebar.inert = mobile && !expanded;
        sidebar.setAttribute('aria-hidden', String(mobile && !expanded));
        if (mobile && expanded && !sidebar.contains(document.activeElement)) {
          var first = sidebar.querySelector('.modern-nav-toggle');
          if (first) requestAnimationFrame(function () { if (document.body.classList.contains('menu-open')) first.focus({ preventScroll: true }); });
        }
      }
    }
    new MutationObserver(syncMenu).observe(document.body, { attributes: true, attributeFilter: ['class'] });
    window.addEventListener('resize', syncMenu);
    syncMenu();
  }
})();
