/* Search only links rendered for the signed-in user's role. */
document.addEventListener('DOMContentLoaded', function () {
    const navigation = document.getElementById('main-menu-navigation');
    if (navigation) {
        navigation.querySelectorAll('li').forEach(function (item, index) {
            const submenu = item.querySelector(':scope > ul');
            const trigger = item.querySelector(':scope > a');
            if (!submenu || !trigger) return;
            submenu.id = 'brand-submenu-' + index;
            trigger.setAttribute('role', 'button');
            trigger.setAttribute('aria-controls', submenu.id);
            if (submenu.querySelector('li.active')) item.classList.add('open');
            function sync() { trigger.setAttribute('aria-expanded', String(item.classList.contains('open'))); }
            sync();
            new MutationObserver(sync).observe(item, { attributes: true, attributeFilter: ['class'] });
            trigger.addEventListener('keydown', function (event) {
                if (event.key === ' ') { event.preventDefault(); trigger.click(); }
            });
        });
    }
    const input = document.getElementById('brand-page-search');
    const results = document.getElementById('brand-search-results');
    if (!input || !results) return;
    const seen = new Set();
    const pages = Array.from(document.querySelectorAll('#main-menu-navigation a')).filter(function (link) {
        if (link.parentElement.querySelector('ul') || !link.href || link.hash || seen.has(link.href)) return false;
        seen.add(link.href);
        return true;
    }).map(function (link) { return { label: link.textContent.trim(), href: link.href }; });
    function close() { results.hidden = true; input.setAttribute('aria-expanded', 'false'); }
    function search() {
        results.replaceChildren();
        const matches = pages.filter(function (page) { return page.label.toLowerCase().includes(input.value.toLowerCase().trim()); }).slice(0, 10);
        matches.forEach(function (page) {
            const link = document.createElement('a');
            link.href = page.href;
            link.textContent = page.label;
            results.appendChild(link);
        });
        if (!matches.length) {
            const empty = document.createElement('p');
            empty.textContent = 'No matching pages.';
            results.appendChild(empty);
        }
        results.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }
    input.addEventListener('input', search);
    input.addEventListener('focus', search);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowDown') { event.preventDefault(); const first = results.querySelector('a'); if (first) first.focus(); }
        if (event.key === 'Enter') { const first = results.querySelector('a'); if (first) first.click(); }
    });
    document.addEventListener('keydown', function (event) {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k' && input.offsetParent) { event.preventDefault(); input.focus(); }
        if (event.key === 'Escape') { if (results.contains(document.activeElement)) input.focus(); close(); }
    });
    document.addEventListener('click', function (event) { if (!input.parentElement.contains(event.target)) close(); });
    input.parentElement.addEventListener('focusout', function (event) { if (!input.parentElement.contains(event.relatedTarget)) close(); });
});
