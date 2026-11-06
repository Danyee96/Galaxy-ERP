# Galaxy ERP — UI/UX Audit

> Audit date: **2026-11-05**, Asia/Karachi. This is a read-only application audit of the current working tree, including pre-existing uncommitted branding/table changes. Only these audit documents were created. No improvements described here are implemented or authorized by this report.

[Project Overview](PROJECT_OVERVIEW.md) | [Module Map](MODULE_MAP.md) | [Database Relationships](DATABASE_RELATIONSHIPS.md) | [Workflow Map](WORKFLOW_MAP.md) | [Ui Ux Audit](UI_UX_AUDIT.md) | [Security Audit](SECURITY_AUDIT.md) | [Performance Audit](PERFORMANCE_AUDIT.md) | [Improvement Roadmap](IMPROVEMENT_ROADMAP.md)

## Current UI architecture

The main business shell uses `layouts/contentLayoutMaster` → vertical/horizontal layout → shared panels. Login uses fullLayoutMaster. The newer product/customer/estimate/invoice pages are complete standalone HTML documents under `user_portal/New`, with duplicated navigation and framework loads. Print slips and reports form a third presentation family. A dormant `user_portal/layout/master` is a fourth legacy layout family with incorrect include prefixes.

Current branding is centralized in `config/brand.php`, shared `resources/views/branding/*`, and `resources/sass/branding/galaxy.scss`. The supplied logo files and generated CSS are not content-inspected in this audit. Business master/customer/serial values were not renamed by the branding work.

## Positive existing work and previously mitigated issue

- Galaxy Inverter identity appears in the active shell, standalone headers, titles, footer, and print templates. Print logo embedding avoids remote image fetches for the supplied logo.
- The sidebar has keyboard-space activation and expanded-state attributes; search uses textContent rather than interpreting result labels as HTML.
- The recent table fix resets inherited `overflow-wrap:anywhere` for table content, keeps headings readable, and retains horizontal scrolling. The pending empty state and accessible scroll-region label are present. This is **not listed as an active unfixed defect**. It still needs regression coverage before broader CSS work.
- Earlier session browser checks covered desktop/mobile login and shell, representative business headers, and sample invoice/estimate/warranty print output. They were not repeated against generated bundles during this source-only audit.

## Screen and interaction coverage

| Area | Source reviewed | Main audit concern |
|---|---|---|
| Shell and navigation | layouts, panels, branding partials, custom JS/Sass, Helper | View-only permissions; production config selection; multiple layout systems |
| Login/users | login and User forms/controllers | Misleading remember option; field validation and account restrictions |
| Service intake/history/exit | Item views, reporting and Report views | Dense tables; packing filter zero bug; custody/scoped-state feedback |
| Production/recipes | Inventory raw/semi/finished/model views | Client preview is not server validation; old/new quantity labels differ |
| Testing | MachineTesting views and stopwatch JS | Measurement meanings/units; QA state; accessible field labels |
| Serial registry | Serialization views | Huge unpaginated list; duplicate/error feedback |
| Product/bundle/catalog | New/products, bundle editor, handwritten product JS/CSS | Mixed Bootstrap/jQuery; dynamic drawers; duplicate initialization |
| Customer list/forms | New/customers, customerScript | Independent customer master; label/focus checks needed |
| Estimate and invoice interactions | New create/list/calculation/invoice templates | Reservation meaning; stale displayed total; line/stock errors |
| Export and print | Item slips, New print templates, report downloads | Complete scope, external resources, consistent print metadata |
| Demo/legacy screens | Page/template controller/view graph | Potential retirement; do not confuse public sample routes with business features |

## Recommended UI direction, after integrity fixes

Keep the present branding while consolidating a shared business layout and component library one module at a time. Standardize field errors and success/warning states, visible report scope/counts, and clear action verbs. Use keyed line items rather than positional parallel arrays. Retain intentional semantic success/error colors; brand color changes should not erase status meaning. Introduce server-side pagination with a separate full-export action, not client-only search over arbitrarily capped records.

A future responsive/accessibility check should cover 390px phone, tablet, and desktop widths; both sidebar states; empty and populated tables; last-column keyboard/scroll access; every product drawer and modal; barcode entry; print preview; focus return; disabled/failed/slow requests; and high zoom. Any issue discovered in that future exercise must be added with evidence rather than assumed here.

## Findings

## Confirmed defects

<a id="ui-01"></a>

### UI-01 — Dashboard chart source is missing

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [resources/views/user_portal/index.blade.php:242](../resources/views/user_portal/index.blade.php#L242) — `js/custom_charts/chart-one.js`; [webpack.mix.js:27](../webpack.mix.js#L27) — `mixAssetsDir`.
- **Problem and evidence:** The dashboard references js/custom_charts/chart-one.js, but there is no corresponding first-party source at resources/js/custom_charts/chart-one.js and the Mix script globs do not build that directory.
- **Potential impact:** The repository cannot reproduce the dashboard chart initializer from source. Earlier session browser checks showed empty chart containers; generated assets were not inspected during this audit.
- **Recommended fix:** Recover the intended initializer or implement it from the approved dashboard data contract, include it in the asset build, and verify no-data and populated states.
- **Dependencies:** REP-01 business-date calculation and agreed chart labels/colors.
- **Regression risks:** Do not invent replacement metrics or change calculations while restoring the visual initializer.
- **Expected improvement:** Reproducible, functioning dashboard charts.

<a id="ui-02"></a>

### UI-02 — Standalone business pages mix framework versions and duplicate scripts

- **Classification / severity:** Confirmed defect / **Medium**.
- **Exact file and method/section:** [resources/views/user_portal/New/products.blade.php:22](../resources/views/user_portal/New/products.blade.php#L22) — `New/script.js`; [resources/views/user_portal/New/estimates-calculation.blade.php:66](../resources/views/user_portal/New/estimates-calculation.blade.php#L66) — `bootstrap/4.3.1`; [resources/views/user_portal/New/invoice-new.blade.php:9](../resources/views/user_portal/New/invoice-new.blade.php#L9) — `bootstrap@5.3.0`; [resources/views/panels/scripts.blade.php:6](../resources/views/panels/scripts.blade.php#L6) — `vendors.min.js`.
- **Problem and evidence:** The main shell is Bootstrap 4; standalone screens load Bootstrap 5 and some also load Bootstrap 4/slim jQuery before reloading other bundles. products includes New/script.js twice. Print views use Bootstrap 3 CDNs.
- **Potential impact:** Load order and differing modal/dropdown APIs can produce inconsistent behavior and duplicate event registration; style fixes must cover several competing stacks.
- **Recommended fix:** Inventory each screen dependency, choose one supported business-screen stack, and migrate shared layouts/components incrementally.
- **Dependencies:** Modal data attributes, Select2, exports, theme scripts, and package upgrade plan.
- **Regression risks:** Switching framework versions in bulk breaks modals and menus. First remove only verified redundant loads with browser checks.
- **Expected improvement:** Consistent UI behavior and fewer duplicate downloads/listeners.

<a id="ui-03"></a>

### UI-03 — Pending-report filter drops the numeric zero status

- **Classification / severity:** Confirmed defect / **Medium**.
- **Exact file and method/section:** [resources/views/user_portal/Report/pending.blade.php:128](../resources/views/user_portal/Report/pending.blade.php#L128) — `applyFilters`.
- **Problem and evidence:** Packing is read as (row.data("packing") || "").toString(); jQuery parses data-packing="0" as numeric zero, which the fallback replaces with an empty string.
- **Potential impact:** Selecting Not Packed (value 0) can hide rows that should match.
- **Recommended fix:** Use an explicit null/undefined fallback or read the attribute as a string. Add a browser assertion for both packing statuses combined with text search.
- **Dependencies:** Packing-status representation and current client-side filtering.
- **Regression risks:** Do not replace zero in other fields where zero is meaningful; test empty attributes separately.
- **Expected improvement:** Not Packed correctly matches zero-valued rows.

<a id="ui-04"></a>

### UI-04 — Removing an invoice row leaves the displayed total stale

- **Classification / severity:** Confirmed defect / **Medium**.
- **Exact file and method/section:** [resources/views/user_portal/New/invoice-new.blade.php:327](../resources/views/user_portal/New/invoice-new.blade.php#L327) — `removeAddon`; [resources/views/user_portal/New/invoice-new.blade.php:322](../resources/views/user_portal/New/invoice-new.blade.php#L322) — `updateTotalCount`; [app/Http/Controllers/user_portal/QuickBookController.php:890](../app/Http/Controllers/user_portal/QuickBookController.php#L890) — `saveInvoice`.
- **Problem and evidence:** removeAddon removes the row without calling updateTotalCount. The backend accepts total_count from the form rather than deriving it from surviving lines.
- **Potential impact:** The displayed and stored quantity total can disagree with invoice details after row removal.
- **Recommended fix:** Recalculate on removal and recalculate authoritative totals server-side at save.
- **Dependencies:** STK-06, bundle quantity semantics, and line-item form controls.
- **Regression risks:** Define whether total_count means parent item quantity or expanded component quantity before changing totals.
- **Expected improvement:** Visible and stored totals reconcile to the accepted lines.

<a id="ui-05"></a>

### UI-05 — Remember-me checkbox is not wired to authentication

- **Classification / severity:** Confirmed defect / **Low**.
- **Exact file and method/section:** [resources/views/user_portal/login.blade.php:63](../resources/views/user_portal/login.blade.php#L63) — `exampleCheck1`; [app/Http/Controllers/user_portal/LoginController.php:19](../app/Http/Controllers/user_portal/LoginController.php#L19) — `userLogin`.
- **Problem and evidence:** The Keep me logged in checkbox has no submitted name and the controller never passes a remember option.
- **Potential impact:** Users are offered a session-persistence option that has no effect.
- **Recommended fix:** Either remove/relabel the option or wire an agreed remember-me policy through request validation and the guard.
- **Dependencies:** Shared workstation policy and SEC-06 logout behavior.
- **Regression risks:** Persistent login may be inappropriate on factory terminals; do not enable it merely to match the label.
- **Expected improvement:** The form accurately represents the session behavior.

<a id="ui-08"></a>

### UI-08 — Shared scripts assume controls exist on every host page

- **Classification / severity:** Confirmed defect / **Medium**.
- **Exact file and method/section:** [public/New/script.js:50](../public/New/script.js#L50) — `attachEvents`; [public/New/script.js:974](../public/New/script.js#L974) — `window.onload`; [resources/views/user_portal/New/products.blade.php:22](../resources/views/user_portal/New/products.blade.php#L22) — `New/script.js`; [public/New/customerScript.js:1](../public/New/customerScript.js#L1) — `addCustomerManually`; [resources/views/user_portal/New/estimates-calculation.blade.php:114](../resources/views/user_portal/New/estimates-calculation.blade.php#L114) — `New/customerScript.js`.
- **Problem and evidence:** attachEvents assigns onclick on elements without checking their presence; window.onload unconditionally initializes product sidebar controls. The same script can be loaded by pages with different control sets and is duplicated on products. customerScript.js assigns onclick immediately to addCustomerManually, but estimates and estimates-calculation include the script without that control.
- **Potential impact:** The estimates screens have a source-confirmed null-control initialization error. Other missing controls can also abort later initialization. Earlier browser checks observed a shared-script null-control error; generated assets were not reinspected during this audit.
- **Recommended fix:** Make initializers page-specific and conditional; load the shared script once and use addEventListener rather than replacing global onload.
- **Dependencies:** Drawer generation, all existing hosts, and UI-02.
- **Regression risks:** Dynamic HTML recreates elements, so listener guards must still bind newly inserted controls.
- **Expected improvement:** Pages initialize only the controls they actually own.

## Potential risks and design questions

<a id="ui-06"></a>

### UI-06 — Modal, form, and navigation accessibility needs targeted validation

- **Classification / severity:** Potential risk / **Medium**.
- **Exact file and method/section:** [resources/views/user_portal/New/products.blade.php:124](../resources/views/user_portal/New/products.blade.php#L124) — `id="openSidebar"`; [resources/views/user_portal/Inventory/model/in_complete_models/complete.blade.php:43](../resources/views/user_portal/Inventory/model/in_complete_models/complete.blade.php#L43) — `id="qty_used"`; [resources/views/panels/navbar.blade.php:20](../resources/views/panels/navbar.blade.php#L20) — `dropdown-user-link`; [resources/assets/js/biztally.js:2](../resources/assets/js/biztally.js#L2) — `DOMContentLoaded`.
- **Problem and evidence:** Repeated material inputs reuse IDs; several standalone inputs/icon controls lack explicit accessible labels. The custom sidebar/page search improves keyboard support, but modal focus return and screen-reader announcements are not covered by tests.
- **Potential impact:** Keyboard and assistive-technology users may encounter ambiguous fields or lose context. This is not a completed WCAG conformance assessment.
- **Recommended fix:** Use unique IDs/labels, named controls, tested focus trapping/return, and accessible feedback. Run keyboard and screen-reader checks on actual workflows.
- **Dependencies:** Chosen frontend stack, UI-02, and user accessibility requirements.
- **Regression risks:** DOM ID changes require updating JS selectors, labels, and automated tests together.
- **Expected improvement:** Reliable keyboard operation and clearly identified controls.

<a id="ui-07"></a>

### UI-07 — Legacy drawers use fixed desktop widths

- **Classification / severity:** Potential risk / **Medium**.
- **Exact file and method/section:** [public/New/script.js:805](../public/New/script.js#L805) — `openProductSidebar`; [public/New/script.js:939](../public/New/script.js#L939) — `sidebar.style.width`; [resources/views/user_portal/New/products.blade.php:127](../resources/views/user_portal/New/products.blade.php#L127) — `sidebar`; [public/New/customerScript.js:2](../public/New/customerScript.js#L2) — `691px`.
- **Problem and evidence:** Several drawer paths assign width="491px" in JavaScript; no viewport-bound calculation is applied there. The customer drawer similarly assigns 691px.
- **Potential impact:** On narrow screens the drawer can exceed viewport width or hide controls. Prior branding checks covered headers, not every create/edit drawer state.
- **Recommended fix:** Use a shared responsive drawer width such as min(viewport minus inset, maximum width), with accessible scrolling and close behavior.
- **Dependencies:** UI-02, focus management, and all drawer creation functions.
- **Regression risks:** Changing width can expose existing form layout assumptions; verify fields and footer buttons at phone widths.
- **Expected improvement:** Create/edit actions remain usable on small screens.

<a id="ui-09"></a>

### UI-09 — Exports and screen assets depend on external CDN availability

- **Classification / severity:** Potential risk / **Medium**.
- **Exact file and method/section:** [resources/views/user_portal/New/products.blade.php:709](../resources/views/user_portal/New/products.blade.php#L709) — `xlsx.full.min.js`; [resources/views/user_portal/New/categories.blade.php:135](../resources/views/user_portal/New/categories.blade.php#L135) — `fnExcelReport`; [resources/views/user_portal/Item/print-slip.blade.php:7](../resources/views/user_portal/Item/print-slip.blade.php#L7) — `maxcdn.bootstrapcdn.com`.
- **Problem and evidence:** Business and print pages load multiple remote CSS/JS/fonts, often without integrity attributes. Client exports rely on these globals and rendered HTML.
- **Potential impact:** Offline factory use, CSP changes, CDN failure, or blocked networks can disable UI/export/print behavior. npm audit does not cover these separately loaded copies.
- **Recommended fix:** Pin and inventory runtime assets, self-host approved critical assets where appropriate, and provide clear export failures. Include CDN copies in dependency triage.
- **Dependencies:** Hosting/CSP requirements, frontend consolidation, and export strategy.
- **Regression risks:** Replacing CDN paths can alter plugin versions; verify modals, PDF styles, spreadsheet values, and licensing.
- **Expected improvement:** Predictable offline/network-degraded behavior and a complete runtime asset inventory.
