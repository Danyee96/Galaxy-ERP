# Galaxy ERP — Performance Audit

> Audit date: **2026-11-05**, Asia/Karachi. This is a read-only application audit of the current working tree, including pre-existing uncommitted branding/table changes. Only these audit documents were created. No improvements described here are implemented or authorized by this report.

[Project Overview](PROJECT_OVERVIEW.md) | [Module Map](MODULE_MAP.md) | [Database Relationships](DATABASE_RELATIONSHIPS.md) | [Workflow Map](WORKFLOW_MAP.md) | [Ui Ux Audit](UI_UX_AUDIT.md) | [Security Audit](SECURITY_AUDIT.md) | [Performance Audit](PERFORMANCE_AUDIT.md) | [Improvement Roadmap](IMPROVEMENT_ROADMAP.md)

## Method and limits

This audit reviewed query construction, relationship use in Blade, schema/index metadata, and non-executing query plans. It did **not** run a load test, benchmark PDF generation on the full database, fetch large datasets, query business balances, or inspect generated/minified assets. Existing two tests were run as-is with result caching disabled.

Prior session observations are supporting context only: a shared CLI process rendering several pages exhausted its 128 MB memory limit on machine-list/serialization rendering. Cumulative retained state makes that an unreliable isolated endpoint benchmark. The source-confirmed problems are unbounded reads, incomplete eager loads, and missing indexes; fresh per-endpoint memory/latency figures are not claimed here.

## Read-only EXPLAIN results

`EXPLAIN FORMAT=TRADITIONAL` used non-matching synthetic bindings. Output below includes only access type and chosen/candidate keys, not row values or estimated business record counts.

| Lookup | Source operation | Plan | Key / candidate key |
|---|---|---|---|
| service_history_lookup | `IndexController::fetchMachineDetails → item_histories.item_id` | `ALL` | `none` / `none` |
| accessory_history_lookup | `ItemHistory::machineAccessories → item_history_accessories.item_history_id` | `ALL` | `none` / `none` |
| product_ledger_lookup | `Products::productInventory → product_inventories.product_id/status` | `ALL` | `none` / `none` |
| invoice_details_lookup | `Invoice::invoiceDetails → invoice_details.invoice_id` | `ALL` | `none` / `none` |
| serialization_lookup | `SerializationController::saveSerialization → serializations.serial_number` | `ALL` | `none` / `none` |
| bundle_recipe_lookup | `QuickBookController::saveInvoice → bundles.bundle_id` | `ALL` | `none` / `none` |

These plans validate the absent-index observation for this local MySQL schema. Production plans can differ and must be checked before migrations. `EXPLAIN ANALYZE` was not used.

## Query and rendering hotspots

| Source | Growth driver | Existing mitigation / next measurement |
|---|---|---|
| SerializationController::serializationList | All serialized units in one response | No pagination; measure bounded list vs export separately |
| IndexController::reporting | Up to 5,000 histories plus related data/markup | Has eager loading; cap remains silent and large |
| IndexController::machineList | Up to 5,000 histories plus lazy user/accessory reads | Partial eager loading only |
| IndexController::overallPending/overallComplete | All matching histories with lazy relations | Add paginated read model and matching related data |
| QuickBookController::receivedTransitInventory | Entire received history plus product/users | No pagination or relation eager loading |
| QuickBookController::productService | Entire active catalog plus global counts and filters | Some SQL counts; still all-row rendering |
| QuickBookController::productEstimatesCalculation and download counterpart | Product-by-product details; repeated parent find calls | Share a filtered eager-loaded query/read model |
| QuickBookController::getInvoice | Two full pagination setups, first discarded | Final query already paginated/eager loaded; remove only redundant work |
| Report/custom-machine-report-download Blade loop | Master by serial → all completed histories → error lookups | Move data preparation out of view and scope it |
| MenuServiceProvider::boot | Three static JSON reads on every request | Lower-priority overhead; measure before optimization |

## Index plan candidates, not executed changes

| Candidate | Why | Preconditions / risk |
|---|---|---|
| item_histories(item_id, status, created_at) or measured prefix | Per-machine open/closed history | Verify exact sort/selectivity; existing date/status indexes do not include item_id |
| item_history_accessories(item_history_id, status) | Accessory custody and returned/unreturned subsets | FK/type review and write overhead |
| product_inventories(product_id, status, created_at) | Product ledger | Decide ordering/receipt event history; profile composite selectivity |
| invoice_details(invoice_id, product_id) | Invoice page eager loads/search | Review invoice_id-only vs composite storage cost |
| bundles(bundle_id, product_id) | Bundle expansion | Unique component constraint only if duplicate components are forbidden |
| serializations(serial_number) | Duplicate/search path | Unique only after identifier-scope/data review |
| finished_models(serial_number), machine_testings(barcode/machine_barcode) | Manufacturing/test identity checks | Nullable unique and retest policy must be defined |
| estimate_values(estimate_id), estimate_values(product_id) | Reservation/report loading | Consider data lifecycle and relation query plans |
| model_recipes(model_id), finished_model_materials(finished_model_id) | Recipe/unit completion | Aggregate component uniqueness and current model scoping |
| product_inventories(status, transit_id, created_at) | Transit lists | Measure against write rate and other proposed indexes |

Do not add every candidate. Existing dashboard migration already adds status/date/company/power/error indexes to item_histories and accessory_id to item_history_accessories; those should be evaluated, not duplicated.

## Proposed performance acceptance gates

These are targets for an approved later benchmark, not improvements already measured:

- Interactive list queries and HTML size should scale with selected page size, not total retained records.
- Relation queries should remain bounded as page size changes; choose a documented query-count ceiling per screen.
- Complete exports must preserve scope, totals, and authorization while keeping request/worker memory bounded.
- Establish p50/p95 response time, peak PHP memory, query count/time, transfer size, and browser interaction time on a synthetic realistic dataset before choosing numeric SLOs.
- Re-run selected query plans and compare write throughput after each index change.
- Concurrency tests must prove no lost stock updates, over-receipts, repeated reversals, or duplicate identities; throughput alone is not ERP correctness.

No queue infrastructure, indexes, caches, pagination, or calculations were changed during this audit.

## Findings

## Confirmed defects

<a id="perf-01"></a>

### PERF-01 — Growing business lists are fetched and rendered without pagination

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/SerializationController.php:14](../app/Http/Controllers/user_portal/SerializationController.php#L14) — `serializationList`; [app/Http/Controllers/user_portal/QuickBookController.php:249](../app/Http/Controllers/user_portal/QuickBookController.php#L249) — `receivedTransitInventory`; [app/Http/Controllers/user_portal/QuickBookController.php:30](../app/Http/Controllers/user_portal/QuickBookController.php#L30) — `productService`; [app/Http/Controllers/user_portal/IndexController.php:549](../app/Http/Controllers/user_portal/IndexController.php#L549) — `overallComplete`; [app/Http/Controllers/user_portal/IndexController.php:855](../app/Http/Controllers/user_portal/IndexController.php#L855) — `customMachineList`.
- **Problem and evidence:** These methods use all()/get() over growing datasets. Some service reports merely cap at 5,000 rather than paginate. Invoices already use pagination and are a useful partial precedent.
- **Potential impact:** PHP memory, HTML transfer, browser DOM size, and time-to-interact grow with total history. Prior session checks exhausted a 128 MB CLI process on machine/serialization renders; those were cumulative-process observations, not isolated endpoint benchmarks.
- **Recommended fix:** Paginate/filter interactive lists on the server, select needed columns, eager-load page relations, and offer a separate complete export path.
- **Dependencies:** REP-04 export completeness, searchable API contracts, and filter UX.
- **Regression risks:** Client-only search/export currently sees every rendered row; replacing it with pagination must preserve an explicit full-export option.
- **Expected improvement:** Page cost scales with page size rather than total history.

<a id="perf-02"></a>

### PERF-02 — Relationship loading creates N+1 query paths

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/IndexController.php:518](../app/Http/Controllers/user_portal/IndexController.php#L518) — `overallPending`; [app/Http/Controllers/user_portal/IndexController.php:471](../app/Http/Controllers/user_portal/IndexController.php#L471) — `machineList`; [app/Http/Controllers/user_portal/QuickBookController.php:249](../app/Http/Controllers/user_portal/QuickBookController.php#L249) — `receivedTransitInventory`; [app/Http/Controllers/user_portal/QuickBookController.php:742](../app/Http/Controllers/user_portal/QuickBookController.php#L742) — `productEstimatesCalculation`; [resources/views/user_portal/Report/custom-machine-report-download.blade.php:182](../resources/views/user_portal/Report/custom-machine-report-download.blade.php#L182) — `App\Models\ItemList::where`.
- **Problem and evidence:** Pending/completed/received-transit lists access relations without with(). machineList eager-loads four relations but its view also accesses users/accessories. Estimate calculations lazy-load per product and find the estimate again in filter and map; custom PDF queries inside Blade.
- **Potential impact:** Query counts grow with displayed records and recipe/detail rows. Missing relationship-key indexes make each repeated query more expensive.
- **Recommended fix:** Build eager-loaded bounded read queries, add missing inverse relations for estimate detail parents, precompute view data, and assert query-count budgets.
- **Dependencies:** PERF-01, PERF-03, REP-06, and exact columns/relations used by views.
- **Regression risks:** Dropping required key columns from select lists breaks eager loading; test null references and historical snapshots.
- **Expected improvement:** Stable query counts per page and fewer repeated table scans.

<a id="perf-03"></a>

### PERF-03 — Important relationship and lookup predicates have no indexes

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [database/migrations/2026_09_14_000001_add_dashboard_indexes.php:9](../database/migrations/2026_09_14_000001_add_dashboard_indexes.php#L9) — `up`; [database/migrations/2024_12_24_151026_create_product_inventories_table.php:14](../database/migrations/2024_12_24_151026_create_product_inventories_table.php#L14) — `up`; [database/migrations/2025_01_07_155031_create_invoice_details_table.php:14](../database/migrations/2025_01_07_155031_create_invoice_details_table.php#L14) — `up`; [app/Http/Controllers/user_portal/IndexController.php:258](../app/Http/Controllers/user_portal/IndexController.php#L258) — `fetchMachineDetails`.
- **Problem and evidence:** Metadata confirms missing indexes on item_histories.item_id, item_history_accessories.item_history_id, product_inventories.product_id/status, invoice_details.invoice_id, serializations.serial_number, and bundles.bundle_id. Read-only EXPLAIN for representative lookups reports ALL with no candidate index on all six.
- **Potential impact:** Common detail, ledger, serial search, and eager-load queries scan full tables. Existing dashboard status/date/company/power/error indexes are useful but do not cover these keys.
- **Recommended fix:** Validate production query plans, then add narrowly scoped forward indexes, considering composites and the agreed unique identifiers.
- **Dependencies:** DB-03, real query frequencies, table size, write overhead, and online migration capabilities.
- **Regression risks:** Indexes consume storage and increase write cost; avoid blindly adding every candidate or assuming plans stay unchanged.
- **Expected improvement:** Indexed lookups instead of full scans for common relationship queries.

<a id="perf-04"></a>

### PERF-04 — Invoice listing executes a discarded query set

- **Classification / severity:** Confirmed defect / **Medium**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/QuickBookController.php:849](../app/Http/Controllers/user_portal/QuickBookController.php#L849) — `getInvoice`.
- **Problem and evidence:** The method first paginates 10 with eager loads and computes a number, then replaces both with a second paginated-25 query and repeated latest-number lookup.
- **Potential impact:** Every visit performs unnecessary count/fetch/relation and numbering queries.
- **Recommended fix:** Retain one filtered eager-loaded pagination query and one safe numbering strategy; preserve current final 25-row behavior.
- **Dependencies:** DB-03 numbering fix and search/pagination UI.
- **Regression risks:** Do not accidentally change final page size, search persistence, sorting, or numbering while removing discarded work.
- **Expected improvement:** Fewer database round trips with identical intended list output.

## Potential risks and design questions

<a id="perf-05"></a>

### PERF-05 — Stock filters coerce strings and report searches cannot use ordinary indexes well

- **Classification / severity:** Potential risk / **Medium**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/QuickBookController.php:30](../app/Http/Controllers/user_portal/QuickBookController.php#L30) — `productService`; [app/Http/Controllers/user_portal/QuickBookController.php:1074](../app/Http/Controllers/user_portal/QuickBookController.php#L1074) — `lowStockProducts`; [app/Http/Controllers/user_portal/QuickBookController.php:849](../app/Http/Controllers/user_portal/QuickBookController.php#L849) — `getInvoice`; [app/Http/Controllers/user_portal/IndexController.php:416](../app/Http/Controllers/user_portal/IndexController.php#L416) — `reporting`.
- **Problem and evidence:** Low-stock counts use CAST(... AS UNSIGNED), while other branches compare string columns directly. Invoice search uses leading-wildcard LIKE; report dates are wrapped with whereDate.
- **Potential impact:** Counts and lists may disagree for negative/text values, and these expressions can limit useful index access. Exact optimizer behavior depends on schema and data.
- **Recommended fix:** After DB-02, use typed consistent predicates; define search needs and use validated range predicates/search indexes only where measured.
- **Dependencies:** DB-02 conversion, negative-stock policy, and REP-03 date semantics.
- **Regression risks:** Unsigned casts have existing behavior for negatives; replacing them changes business results and requires approval.
- **Expected improvement:** Consistent stock filters and measured, index-friendly query shapes.

<a id="perf-06"></a>

### PERF-06 — Client-side export scales with the full rendered DOM

- **Classification / severity:** Potential risk / **Medium**.
- **Exact file and method/section:** [resources/views/user_portal/New/products.blade.php:287](../resources/views/user_portal/New/products.blade.php#L287) — `fnExcelReport`; [resources/views/user_portal/New/categories.blade.php:301](../resources/views/user_portal/New/categories.blade.php#L301) — `pdfContainer`; [app/Http/Controllers/user_portal/IndexController.php:454](../app/Http/Controllers/user_portal/IndexController.php#L454) — `downloadMachineData`.
- **Problem and evidence:** Exports clone/clean DOM tables for spreadsheets or rasterize them for PDF; server PDF exports are synchronous. Work is tied to loaded rows and document size.
- **Potential impact:** Large exports can freeze the browser or consume a web worker while omitted/paginated records are silently absent.
- **Recommended fix:** Specify export scope and budgets; use streamed CSV or bounded queued exports with authorization and safe downloads for complete datasets.
- **Dependencies:** PERF-01, REP-04, asynchronous worker deployment, and output-format requirements.
- **Regression risks:** Spreadsheet typing, formatting, filenames, and privacy access must remain compatible; a queue is not yet configured operationally.
- **Expected improvement:** Complete exports without unbounded request/DOM work.

<a id="perf-07"></a>

### PERF-07 — Every request loads all theme menu JSON and broad asset builds

- **Classification / severity:** Potential risk / **Low**.
- **Exact file and method/section:** [app/Providers/MenuServiceProvider.php:24](../app/Providers/MenuServiceProvider.php#L24) — `boot`; [webpack.mix.js:27](../webpack.mix.js#L27) — `mixAssetsDir`; [resources/views/panels/styles.blade.php:5](../resources/views/panels/styles.blade.php#L5) — `vendors.min.css`.
- **Problem and evidence:** The provider reads and decodes three menu JSON files for every request, including JSON APIs, while the active business sidebar is hand-authored. Mix compiles/copies a broad theme asset tree.
- **Potential impact:** Unneeded request I/O and build/runtime payload may accumulate, but this is lower priority than query and stock issues; timings were not measured.
- **Recommended fix:** After usage mapping, scope menu data to relevant views/cache static configuration and trim entrypoints only where unused assets are proven.
- **Dependencies:** ARCH-03 dynamic layouts, localization, and UI-02 dependency inventory.
- **Regression risks:** Horizontal/demo screens may still depend on these menus; do not remove them from all requests blindly.
- **Expected improvement:** Less incidental work and more focused asset entrypoints.
