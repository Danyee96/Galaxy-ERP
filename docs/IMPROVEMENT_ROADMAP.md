# Galaxy ERP — Improvement Roadmap

> Audit date: **2026-11-05**, Asia/Karachi. This is a read-only application audit of the current working tree, including pre-existing uncommitted branding/table changes. Only these audit documents were created. No improvements described here are implemented or authorized by this report.

[Project Overview](PROJECT_OVERVIEW.md) | [Module Map](MODULE_MAP.md) | [Database Relationships](DATABASE_RELATIONSHIPS.md) | [Workflow Map](WORKFLOW_MAP.md) | [Ui Ux Audit](UI_UX_AUDIT.md) | [Security Audit](SECURITY_AUDIT.md) | [Performance Audit](PERFORMANCE_AUDIT.md) | [Improvement Roadmap](IMPROVEMENT_ROADMAP.md)

## Implementation boundary

**Audit only.** The application remains in its pre-audit state. Do not apply this roadmap until the user selects the next scope. No application code, business records, migration history, API contracts, or calculations were changed; no package was installed or upgraded. Recommendations are deliberately conditional where a business rule is not established.

The priorities below are sequenced by access/stock integrity and regression safety, not by visual ease. Work can be split into small reviewable changes; do not combine an authorization redesign, stock schema conversion, Laravel upgrade, and UI rewrite in one release.

## Phase 0 — Preserve evidence and establish a safe baseline

- Retain the current working-tree changes; do not reset, commit, or push them as part of the audit.
- Provision a disposable MySQL test environment and fail-fast isolation guard before enabling mutation/RefreshDatabase tests (TEST-03).
- Correct the guest/authenticated smoke-test expectations in a later test change (TEST-02), then build synthetic factories and characterization tests (TEST-01).
- Obtain a capability matrix, stock/reservation definitions, identifier scopes, and date/rounding policy. Capture current contracts, including old/new remaining-material semantics.
- Record deployment topology, runtime versions, and a redacted secret/backup access checklist. Back up/recovery planning is an operational prerequisite, not authorization to export business data into the public repository.

**Exit evidence:** green isolated smoke tests; an explicit test DB guard; approved representative business examples; no non-test DB access in mutation CI.

## Phase 1 — Contain access and supply-chain exposure

- Restrict the public migration/cache-clear entrypoints at the deployment boundary; do not verify by invoking them (SEC-01).
- Enforce server-side authorization for account administration first, then receipts/production/invoices/service/reporting (SEC-02).
- Coordinate migration of GET mutations and product creation to protected verbs/forms without silently breaking clients (SEC-03, SEC-04).
- Address active-account login, throttling, scoped object IDs, HTML-fragment escaping, and exception redaction (SEC-05, SEC-07–SEC-09, SERV-04).
- Triage reachable Composer/npm/CDN advisories, API guard exposure, PDF remote resources, seeded credentials, and actual Docker usage (SEC-10–SEC-15).

**Exit evidence:** representative users receive allowed results/403 consistently; no business mutation through reads; validation fails before persistence; no executable stored names; advisory reachability/remediation decisions documented. This phase does not authorize deleting routes or upgrading packages without a selected implementation scope.

## Phase 2 — Make ERP postings conserve quantities

- Implement validated, atomic posting operations with ordered locks/idempotency and observable error handling (STK-01).
- Make full/partial receipts and estimate cancellation bounded and repeat-safe (STK-02–STK-05).
- Derive invoice and production demand server-side, aggregate repeated components, enforce approved stock policy, and calculate authoritative totals (STK-06, PROD-01, PROD-04, UI-04).
- Scope manufacturing completion over the entire unit, preserving old/new input contracts (PROD-02, PROD-03).
- Agree projected/on-hand/reserved/fulfilled semantics before changing temp quantities or adding a canonical movement ledger (STK-07, STK-08).
- Introduce serial/document-number constraints only after approved duplicate profiling/remediation (DB-03). Do not silently overwrite or merge duplicates.

**Exit evidence:** isolated rollback/concurrency tests, repeated-posting conservation tests, and approved before/after quantity examples. A rehearsal reconciliation reports discrepancies without correcting production records automatically.

## Phase 3 — Correct service and report behavior

- Fix the repeat-warranty field mismatch and approve all boundary behavior (SERV-01, SERV-03).
- Replace hardcoded company/staff assumptions with agreed rules (SERV-02, DB-07).
- Fix company-only report SQL, matching detail/footer filters, event-day grouping, and scoped error details (REP-01–REP-03, REP-06).
- Clearly separate complete exports from paginated views and resolve the in-factory population label (REP-04, REP-05).
- Adopt service and QA state rules only after owner/engineering approval (SERV-05, PROD-06).

**Exit evidence:** all filter combinations run; report totals equal their displayed populations; warranty examples match the approved policy; historical stored outcomes remain untouched unless a separate data change is authorized.

## Phase 4 — Improve schema and query scaling

- Profile invalid numeric values/orphans without exposing confidential rows; agree conversions; introduce typed columns/casts and FK constraints through new forward migrations (DB-01, DB-02).
- Add only measured relationship/lookup indexes and verify plans/write cost (PERF-03).
- Introduce server-side paging/search, eager loading, scoped read models, and a complete export path (PERF-01, PERF-02, PERF-05, PERF-06).
- Remove discarded invoice-list work and lower-priority menu I/O after behavior is covered (PERF-04, PERF-07).

**Exit evidence:** explain plans use appropriate keys, per-page memory/query counts are bounded, exports remain complete, and data conversion rehearsals reconcile totals. Never edit applied migration files as a shortcut.

## Phase 5 — Consolidate architecture, UI, and supported dependencies

- Extract tested posting/query services and consolidate actual duplicate code (ARCH-01, ARCH-02).
- Resolve production helper/build path handling, restore the chart source, and standardize the main/standalone frontend stacks incrementally (ARCH-05, ARCH-06, UI-01, UI-02).
- Fix packing filtering, remember-option truthfulness, drawers, shared initializers, accessibility, and external-asset failure modes (UI-03–UI-09).
- Confirm unused-route/template/dependency candidates with traffic/consumer evidence; deprecate intentionally rather than deleting based on grep (ARCH-03, ARCH-04).
- Plan the framework/runtime dependency migration based on the advisory exposure and supported target version; do not postpone an actionable critical mitigation solely for a full UI rewrite.
- Define customer accounting, units, unit traceability, and any expanded finance scope before building them (DB-06, PROD-05, SALE-02, SALE-03).

**Exit evidence:** source-reproducible builds, stable routes/contracts, no duplicate initialization, representative browser/print/accessibility checks, and a supported dependency plan with passing regression gates.

## Finding-to-phase checklist

Each ID links to the full issue contract, including location, severity, impact, recommendation, dependencies, regression risk, and expected improvement. Phase assignment is a suggested owner-planning aid, not an implementation authorization or promised estimate.

| Finding | Suggested phase | Primary regression gate |
|---|---|---|
| [SEC-01](SECURITY_AUDIT.md#sec-01) — Unauthenticated operational endpoints | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-02](SECURITY_AUDIT.md#sec-02) — Login is treated as authorization | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-03](SECURITY_AUDIT.md#sec-03) — State changes use GET | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-04](SECURITY_AUDIT.md#sec-04) — Product listing can create products through query parameters | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-05](SECURITY_AUDIT.md#sec-05) — Custom login ignores disabled accounts and has no limiter | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-06](SECURITY_AUDIT.md#sec-06) — Logout does not explicitly invalidate session state | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-07](SECURITY_AUDIT.md#sec-07) — Most business writes lack server-side validation | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-08](SECURITY_AUDIT.md#sec-08) — Recipe preview HTML interpolates stored names without escaping | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-09](SECURITY_AUDIT.md#sec-09) — Lookup APIs return raw exception messages | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-10](SECURITY_AUDIT.md#sec-10) — Repository seeder contains fixed credentials and personal fields | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-11](SECURITY_AUDIT.md#sec-11) — Locked dependencies match published security advisories | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-12](SECURITY_AUDIT.md#sec-12) — Local configuration is not a production-hardening baseline | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-13](SECURITY_AUDIT.md#sec-13) — API guard and token schema do not agree | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-14](SECURITY_AUDIT.md#sec-14) — PDF remote loading has a broad trust boundary | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [SEC-15](SECURITY_AUDIT.md#sec-15) — Legacy Docker builds fetch executable installers over HTTP | 1 — access/exposure | Access/validation/escaping/token tests and deployment/advisory review |
| [ARCH-01](MODULE_MAP.md#arch-01) — Business rules are concentrated in oversized controllers | 5 — consolidation/scope | Caller inventory, contract parity, and clean disposable build |
| [ARCH-02](MODULE_MAP.md#arch-02) — Duplicate code and parallel workflow implementations | 5 — consolidation/scope | Caller inventory, contract parity, and clean disposable build |
| [ARCH-03](MODULE_MAP.md#arch-03) — Demo routes and apparently orphaned templates remain | 5 — consolidation/scope | Caller inventory, contract parity, and clean disposable build |
| [ARCH-04](MODULE_MAP.md#arch-04) — Potentially unnecessary dependencies require usage confirmation | 5 — consolidation/scope | Caller inventory, contract parity, and clean disposable build |
| [ARCH-05](MODULE_MAP.md#arch-05) — Production build retains a theme-demo asset base path | 5 — consolidation/scope | Caller inventory, contract parity, and clean disposable build |
| [DB-01](DATABASE_RELATIONSHIPS.md#db-01) — No relational foreign keys protect business data | 4 — schema/query scaling | Disposable MySQL migration/reconciliation rehearsal |
| [DB-02](DATABASE_RELATIONSHIPS.md#db-02) — Quantities and prices use incompatible storage types | 4 — schema/query scaling | Disposable MySQL migration/reconciliation rehearsal |
| [DB-03](DATABASE_RELATIONSHIPS.md#db-03) — Business identifiers have no unique database constraints | 2 — posting integrity | Disposable MySQL migration/reconciliation rehearsal |
| [DB-04](DATABASE_RELATIONSHIPS.md#db-04) — Role inverse relation points to the wrong foreign key | 4 — schema/query scaling | Disposable MySQL migration/reconciliation rehearsal |
| [DB-05](DATABASE_RELATIONSHIPS.md#db-05) — Historical migration rollback is incomplete | 4 — schema/query scaling | Disposable MySQL migration/reconciliation rehearsal |
| [DB-06](DATABASE_RELATIONSHIPS.md#db-06) — Units and classification masters are not relationally integrated | 5 — consolidation/scope | Disposable MySQL migration/reconciliation rehearsal |
| [DB-07](DATABASE_RELATIONSHIPS.md#db-07) — Documented fresh setup does not provision a usable business account | 3 — service/report correctness | Disposable MySQL migration/reconciliation rehearsal |
| [STK-01](WORKFLOW_MAP.md#stk-01) — Multi-record stock writes lack transactions and locks | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [STK-02](WORKFLOW_MAP.md#stk-02) — Full transit receipt can be applied repeatedly | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [STK-03](WORKFLOW_MAP.md#stk-03) — Partial receipt accepts invalid or excessive quantities | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [STK-04](WORKFLOW_MAP.md#stk-04) — Estimate cancellation releases reservations repeatedly | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [STK-05](WORKFLOW_MAP.md#stk-05) — Estimate cancellation can release more than was reserved | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [STK-06](WORKFLOW_MAP.md#stk-06) — Invoice issuing does not enforce stock or line validity | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [STK-07](WORKFLOW_MAP.md#stk-07) — Projected stock and reservation semantics are not unified | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [STK-08](WORKFLOW_MAP.md#stk-08) — Material consumption does not create a complete movement ledger | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [PROD-01](WORKFLOW_MAP.md#prod-01) — Production save trusts the client material list and consumption | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [PROD-02](WORKFLOW_MAP.md#prod-02) — Partial manufacturing completion checks only submitted material IDs | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [PROD-03](WORKFLOW_MAP.md#prod-03) — Legacy partial completion checks the wrong required amount | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [PROD-04](WORKFLOW_MAP.md#prod-04) — Duplicate recipe components can defeat per-line availability checks | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [PROD-05](WORKFLOW_MAP.md#prod-05) — Manufacturing counters and serial lifecycle are disconnected | 5 — consolidation/scope | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [PROD-06](WORKFLOW_MAP.md#prod-06) — Testing records do not enforce a quality-release decision | 3 — service/report correctness | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [SERV-01](WORKFLOW_MAP.md#serv-01) — Repeat-service warranty uses a nonexistent field | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [SERV-02](WORKFLOW_MAP.md#serv-02) — Service intake depends on hardcoded company and staff IDs | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [SERV-03](WORKFLOW_MAP.md#serv-03) — Warranty and reporting date semantics need a single policy | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [SERV-04](WORKFLOW_MAP.md#serv-04) — Accessory return updates are not scoped to the service history | 1 — access/exposure | Frozen-date fixtures and matching report populations |
| [SERV-05](WORKFLOW_MAP.md#serv-05) — Service transitions and edits lack explicit state guards | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [SALE-01](WORKFLOW_MAP.md#sale-01) — Finished-goods sale rejects selling the exact available quantity | 2 — posting integrity | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [SALE-02](WORKFLOW_MAP.md#sale-02) — Current invoices are quantity-issue documents, not a full finance ledger | 5 — consolidation/scope | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [SALE-03](WORKFLOW_MAP.md#sale-03) — Bundle replacement is destructive and not transactional | 5 — consolidation/scope | Approved quantity/state examples; rollback, repeat, and concurrency tests |
| [REP-01](WORKFLOW_MAP.md#rep-01) — Dashboard event charts group by record creation day | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [REP-02](WORKFLOW_MAP.md#rep-02) — Company-only custom report uses a nonexistent column | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [REP-03](WORKFLOW_MAP.md#rep-03) — Custom report footer totals ignore selected filters | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [REP-04](WORKFLOW_MAP.md#rep-04) — Reports silently cap their supposedly complete population | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [REP-05](WORKFLOW_MAP.md#rep-05) — In-factory machine list includes delivered histories | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [REP-06](WORKFLOW_MAP.md#rep-06) — Custom PDF error details are outside report scope | 3 — service/report correctness | Frozen-date fixtures and matching report populations |
| [UI-01](UI_UX_AUDIT.md#ui-01) — Dashboard chart source is missing | 5 — consolidation/scope | Keyboard/mobile/browser/network/print checks on affected controls |
| [UI-02](UI_UX_AUDIT.md#ui-02) — Standalone business pages mix framework versions and duplicate scripts | 5 — consolidation/scope | Keyboard/mobile/browser/network/print checks on affected controls |
| [UI-03](UI_UX_AUDIT.md#ui-03) — Pending-report filter drops the numeric zero status | 5 — consolidation/scope | Keyboard/mobile/browser/network/print checks on affected controls |
| [UI-04](UI_UX_AUDIT.md#ui-04) — Removing an invoice row leaves the displayed total stale | 2 — posting integrity | Keyboard/mobile/browser/network/print checks on affected controls |
| [UI-05](UI_UX_AUDIT.md#ui-05) — Remember-me checkbox is not wired to authentication | 5 — consolidation/scope | Keyboard/mobile/browser/network/print checks on affected controls |
| [UI-06](UI_UX_AUDIT.md#ui-06) — Modal, form, and navigation accessibility needs targeted validation | 5 — consolidation/scope | Keyboard/mobile/browser/network/print checks on affected controls |
| [UI-07](UI_UX_AUDIT.md#ui-07) — Legacy drawers use fixed desktop widths | 5 — consolidation/scope | Keyboard/mobile/browser/network/print checks on affected controls |
| [UI-08](UI_UX_AUDIT.md#ui-08) — Shared scripts assume controls exist on every host page | 5 — consolidation/scope | Keyboard/mobile/browser/network/print checks on affected controls |
| [UI-09](UI_UX_AUDIT.md#ui-09) — Exports and screen assets depend on external CDN availability | 5 — consolidation/scope | Keyboard/mobile/browser/network/print checks on affected controls |
| [PERF-01](PERFORMANCE_AUDIT.md#perf-01) — Growing business lists are fetched and rendered without pagination | 4 — schema/query scaling | Bounded query/memory measurements and unchanged output scope |
| [PERF-02](PERFORMANCE_AUDIT.md#perf-02) — Relationship loading creates N+1 query paths | 4 — schema/query scaling | Bounded query/memory measurements and unchanged output scope |
| [PERF-03](PERFORMANCE_AUDIT.md#perf-03) — Important relationship and lookup predicates have no indexes | 4 — schema/query scaling | Bounded query/memory measurements and unchanged output scope |
| [PERF-04](PERFORMANCE_AUDIT.md#perf-04) — Invoice listing executes a discarded query set | 4 — schema/query scaling | Bounded query/memory measurements and unchanged output scope |
| [PERF-05](PERFORMANCE_AUDIT.md#perf-05) — Stock filters coerce strings and report searches cannot use ordinary indexes well | 4 — schema/query scaling | Bounded query/memory measurements and unchanged output scope |
| [PERF-06](PERFORMANCE_AUDIT.md#perf-06) — Client-side export scales with the full rendered DOM | 4 — schema/query scaling | Bounded query/memory measurements and unchanged output scope |
| [PERF-07](PERFORMANCE_AUDIT.md#perf-07) — Every request loads all theme menu JSON and broad asset builds | 4 — schema/query scaling | Bounded query/memory measurements and unchanged output scope |
| [TEST-01](IMPROVEMENT_ROADMAP.md#test-01) — ERP business behavior has no automated regression coverage | 0 — safe test baseline | Non-test database guard and isolated green suite |
| [TEST-02](IMPROVEMENT_ROADMAP.md#test-02) — The existing feature test expects the wrong guest response | 0 — safe test baseline | Non-test database guard and isolated green suite |
| [TEST-03](IMPROVEMENT_ROADMAP.md#test-03) — Test database isolation is not configured | 0 — safe test baseline | Non-test database guard and isolated green suite |
| [ARCH-06](MODULE_MAP.md#arch-06) — Production layout selection ignores the normal custom configuration | 5 — consolidation/scope | Caller inventory, contract parity, and clean disposable build |

## Existing test results

Fresh command: `php vendor/bin/phpunit --do-not-cache-result --colors=never`.

- `Tests\Unit\ExampleTest::testBasicTest`: pass, assertion is only `assertTrue(true)`.
- `Tests\Feature\ExampleTest::testBasicTest`: fail, expected homepage HTTP 200 but received 302 from the correct guest auth redirect.
- Two tests / two assertions. No coverage collector was run; do not infer a numeric coverage percentage.
- The test source was inspected before execution and does not mutate business records. No new tests were written, no RefreshDatabase behavior was added, and no schema reset was performed.

## Missing test matrix

Proposed cases below describe future tests, not tests created by this audit. Use synthetic data and a disposable MySQL database; tests must never depend on current customer accounts, seeded credentials, serials, or business balances.

| Area / exact source target | Required cases | Issue coverage |
|---|---|---|
| LoginController::userLogin | Active/disabled user, generic errors, valid/missing inputs, limiter, session persistence choice | SEC-05, UI-05 |
| UserController::saveUser/updateUser/updateUserPassword | Role allow/deny, target ID scope, duplicate email, password requirements, missing record | SEC-02, SEC-07 |
| routes/web.php operational routes | Anonymous/authenticated access denial without invoking commands; allowed HTTP methods | SEC-01, SEC-03 |
| QuickBookController::productService | GET is read-only; validated create/filter branches and contract preservation | SEC-04, SEC-07 |
| RawMaterialController::saveInventory/saveTransitInventory | Opening/immediate/pending, valid mode, repeated receipt, negative/zero quantities, failure rollback | STK-01–STK-03 |
| QuickBookController receipt methods | Partial, exact final, excessive, negative, duplicate, legacy full after partial, concurrent receipts | STK-01–STK-03 |
| QuickBookController::saveProductEstimates/deleteEstimate | Sufficient/insufficient allocations, recipe absent, save-cancel conservation, double cancel, transit/reservation semantics | STK-04, STK-05, STK-07 |
| QuickBookController::saveInvoice | Direct and bundle lines, overlapping components, array keys, active type, duplicate submit, overdraw policy, total_count derivation | STK-06, UI-04 |
| RawMaterialController production saves | Authoritative recipe, omitted/fake components, decimals, duplicate material, stale preview, variance policy, rollback | PROD-01, PROD-04 |
| ModelController incomplete/completion methods | Old/new contract meanings, bounds, cross-model IDs, submitted subset, whole-unit completion, repeat/concurrent completion | PROD-02–PROD-05 |
| RawMaterialController::saveFinishedGoodsSale | Below/equal/above stock, positive bounds, rollback | SALE-01 |
| MachineTestingController | Required/ranged results after QA policy, duplicate board/unit, assignment/retest, tester list scope | PROD-06, DB-03 |
| SerializationController | Duplicate/concurrent serial insert, invalid model/date, per-scope uniqueness | DB-03, PROD-05 |
| IndexController warranty/intake | Correct persisted years field, nulls, exact boundary, leap dates, future date, repeat intake, company/staff rules | SERV-01–SERV-03 |
| IndexController::saveExitMachine/markAsPacked/saveEditMachine | Cross-history accessory rejection, allowed transitions, replay, historical snapshots | SERV-04, SERV-05 |
| IndexController::index | Distinct created/entry/exit days, month boundaries, empty datasets, cohort meaning | REP-01, SERV-03 |
| IndexController::customMachineDataPDF | All eight company/power/error combinations, invalid date/filter IDs, footer/detail equality, scoped error list | REP-02, REP-03, REP-06 |
| IndexController export/list queries | Records beyond current caps, page/search scope, in-factory label semantics, complete export | REP-04, REP-05 |
| QuickBookController::getInvoice | One query setup, 25-row pagination, query-string persistence, search and numbering concurrency | PERF-04, DB-03 |
| Models and new schema migrations | Inverse relations, FK null/deletion behavior, duplicates, numeric conversion, reversible new migrations | DB-01–DB-06 |
| Query regression suite | Bounded eager-load counts, indexed relationship predicates, no queries inside report view | PERF-01–PERF-03 |
| AJAX recipe preview rendering | Product/material names with quotes/markup stay text; nested JSON shape preserved | SEC-08 |
| Browser service filters | Packing 0/1, combined text search, no-result state, readable headers and last-column access | UI-03, prior table fix |
| Browser product/invoice UI | Remove-line totals, once-only initialization, each modal/drawer at 390px, focus/labels, failed lookup | UI-02, UI-04, UI-06–UI-08 |
| Print/export | Invoice/estimate/warranty logo and names, local resources, correct totals, large dataset scope, spreadsheet typing | UI-09, PERF-06, REP-04 |
| API /api/user | Chosen guard valid/malformed/revoked/expired tokens; no nonexistent token-column queries | SEC-11, SEC-13 |
| Build/deployment | Production asset roots, no demo-URL selection, locked dependency checks, no secrets/artifact drift | ARCH-05, ARCH-06, SEC-11, SEC-15 |

## Contract and data safeguards for later changes

- Preserve route names/paths/verbs and JSON keys until a coordinated migration is explicitly selected; fixing unsafe GETs still requires changing clients deliberately.
- Never recalculate stored warranty outcomes, reservations, stock balances, invoice quantities, or report accounting definitions as an incidental refactor.
- Do not execute historical migration rollback/seeders on the inherited database. Add forward corrective migrations only after profiling/rehearsal.
- Keep existing user work intact. Audit documentation does not authorize commits, pushes, dependency installs, or cleanup.
- Every implementation change should declare the problem, approved behavior, affected callers, tests, data implications, and rollback plan.

## Findings

## Confirmed defects

<a id="test-01"></a>

### TEST-01 — ERP business behavior has no automated regression coverage

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [tests/Unit/ExampleTest.php:14](../tests/Unit/ExampleTest.php#L14) — `testBasicTest`; [tests/Feature/ExampleTest.php:15](../tests/Feature/ExampleTest.php#L15) — `testBasicTest`; [database/factories/UserFactory.php:15](../database/factories/UserFactory.php#L15) — `definition`.
- **Problem and evidence:** The suite contains only an assertTrue unit test and an unauthenticated homepage response test; there are no stock, production, service, permission, reporting, API, or browser test cases and no domain factories beyond User.
- **Potential impact:** Changes to critical quantities and permissions have no regression safety net. This is source-observed coverage absence, not a measured line-coverage percentage.
- **Recommended fix:** Build synthetic domain factories and characterization tests for the matrix below before refactoring; add CI once isolation is established.
- **Dependencies:** TEST-03, approved business rules, and deterministic dates.
- **Regression risks:** Tests that merely mirror buggy calculations can preserve defects; separate current-behavior characterization from approved-correctness cases.
- **Expected improvement:** A meaningful gate for business and security changes.

<a id="test-02"></a>

### TEST-02 — The existing feature test expects the wrong guest response

- **Classification / severity:** Confirmed defect / **Medium**.
- **Exact file and method/section:** [tests/Feature/ExampleTest.php:15](../tests/Feature/ExampleTest.php#L15) — `testBasicTest`; [routes/web.php:71](../routes/web.php#L71) — `Route::get('/'`.
- **Problem and evidence:** A fresh read-only suite run returned 302 for the authenticated homepage, while the test asserts 200. The unit example passed.
- **Potential impact:** The existing baseline is red and does not distinguish correct guest redirection from a regression.
- **Recommended fix:** Assert redirect-to-login for guests and 200 for a synthetic authenticated user with required fixtures.
- **Dependencies:** TEST-03 isolation and minimal dashboard reference data.
- **Regression risks:** Do not remove auth or weaken the route just to satisfy the old test.
- **Expected improvement:** A trustworthy green baseline that tests the actual access contract.

## Potential risks and design questions

<a id="test-03"></a>

### TEST-03 — Test database isolation is not configured

- **Classification / severity:** Potential risk / **High**.
- **Exact file and method/section:** [phpunit.xml:24](../phpunit.xml#L24) — `DB_CONNECTION`; [tests/CreatesApplication.php:14](../tests/CreatesApplication.php#L14) — `createApplication`.
- **Problem and evidence:** phpunit.xml comments out dedicated DB connection/database settings; bootstrapping otherwise uses the local environment. No dedicated test environment or CI workflow was found.
- **Potential impact:** Adding RefreshDatabase or mutation tests now could run migrations or writes against the inherited database. Existing two tests do not perform business writes.
- **Recommended fix:** Provision an isolated disposable MySQL test database, force safe test connection settings, add a fail-fast non-test-database guard, and isolate all service integrations.
- **Dependencies:** TEST-01, MySQL-specific aggregates/casts, and CI secrets.
- **Regression risks:** SQLite-only tests would miss MySQL coercion and locking behavior; never run destructive tests against the current database.
- **Expected improvement:** Safe repeatable tests including transactions and concurrency.
