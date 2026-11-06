# Galaxy ERP — Project Overview

> Audit date: **2026-11-05**, Asia/Karachi. This is a read-only application audit of the current working tree, including pre-existing uncommitted branding/table changes. Only these audit documents were created. No improvements described here are implemented or authorized by this report.

[Project Overview](PROJECT_OVERVIEW.md) | [Module Map](MODULE_MAP.md) | [Database Relationships](DATABASE_RELATIONSHIPS.md) | [Workflow Map](WORKFLOW_MAP.md) | [Ui Ux Audit](UI_UX_AUDIT.md) | [Security Audit](SECURITY_AUDIT.md) | [Performance Audit](PERFORMANCE_AUDIT.md) | [Improvement Roadmap](IMPROVEMENT_ROADMAP.md)

## Executive assessment

Galaxy ERP is a server-rendered Laravel monolith for inverter service, stock handling, manufacturing, testing, serial dispatch records, estimates, and quantity-based invoice issues. The core is understandable, but **access control and inventory invariants are not strong enough for unrestricted production use**. First stabilize the permission boundary and posting/reversal rules; refactor and upgrade only after a meaningful isolated test baseline exists.

The current product has two distinct stock domains. Raw/semi/finished manufacturing uses `current_stock`, `temp_stock`, and material recipes. The newer product system uses `total_qty`, `temp_qty`, separate transit counters, bundles, and estimate reservations. They are not an integrated general ledger. Existing “invoice” records are quantity-issue documents; complete receivables, tax, payment, purchasing, BOM versioning, and accounting behavior must not be assumed.

## Repository and runtime shape

| Area | Observed architecture |
|---|---|
| Backend | Laravel v8.83.29, classic HTTP/console kernels, controller/Eloquent/Blade monolith |
| Business controllers | 9 controllers / 172 public methods; the largest is QuickBookController |
| Models / migrations | 38 models / 61 application migration files; 47 local tables including framework/OAuth/migration tables |
| Routes | 278 registered routes: 171 `web,auth`, 105 `web`, one `web,guest`, one `api,auth:api`; no duplicate registered route names found |
| Views | 229 Blade files: 98 business/legacy `user_portal` views and 107 template/demo `pages` views, plus shared layouts/auth/branding/errors |
| Authorization | Session login; integer role IDs in menus; no application policy/capability layer |
| Database | MySQL; no foreign keys; product quantities/prices often strings, manufacturing quantities often DOUBLE |
| Frontend | Laravel Mix/Webpack/Sass; Bootstrap 4 main shell, Bootstrap 5 standalone screens, Bootstrap 3 print dependencies; jQuery, Select2, ApexCharts, browser exports |
| Background work | Queue settings exist, locally synchronous; no application scheduled jobs, domain jobs, or stock reconciliation command found |
| Integrations | PDF via Dompdf; Passport traits/tables but incompatible API guard configuration; no direct business Guzzle/HTTP integration found |
| Tests | Two example tests, no domain regression suite or CI workflow found |

## Dependencies

Direct PHP versions from `composer.lock`:

| Dependency | Locked version | Observed purpose / decision |
|---|---|---|
| `barryvdh/laravel-dompdf` | `v2.2.0` | Used for machine-report PDF output. |
| `fideloper/proxy` | `4.4.2` | HTTP trusted-proxy middleware. |
| `fruitcake/laravel-cors` | `v2.2.0` | CORS middleware; scanner marks package abandoned. |
| `guzzlehttp/guzzle` | `7.10.0` | Direct dependency, no direct business use found; transitive needs remain possible. |
| `laravel/framework` | `v8.83.29` | Application framework; security modernization requires a controlled plan. |
| `laravel/passport` | `v10.4.2` | User token trait / OAuth schema; guard mismatch needs resolution. |
| `laravel/tinker` | `v2.10.1` | Developer console; retain/remove by operational requirements. |
| `laravel/ui` | `v3.4.6` | Legacy auth scaffolding; published code may remain independent of generator. |

PHP requirement is broad (`^7.4.0|^8.0`) and Composer platform checks are disabled. The framework is outside its supported security window: Laravel’s official 8.x support table ends security fixes on **2023-01-24**. This is a planning input, not authorization to upgrade. [Official Laravel support policy](https://laravel.com/docs/8.x/releases#support-policy).

Frontend manifests list Bootstrap 4.6, Laravel Mix 6, Webpack 5, Sass, jquery, axios, lodash, Popper, PostCSS, and RTL tooling. Actual browser dependencies also include untracked-by-npm CDN versions and bundled third-party files that were excluded from implementation inspection. See ARCH-04, UI-02, UI-09, and the security advisory inventory.

## Validation performed

| Check | Result | Interpretation |
|---|---|---|
| First-party PHP syntax | 181 files passed `php -l` | Syntax only; does not execute workflows |
| Registered route inventory | 278 routes; no duplicate names | No route cache was created; unsafe routes were not requested |
| Schema metadata | 47 tables, 63 index definitions, zero foreign keys | No row contents or confidential connection values included |
| Query plans | Six representative relationship/lookups all `ALL`, no candidate key | Not `EXPLAIN ANALYZE`; no application query execution/benchmark |
| Existing PHPUnit suite | 2 tests, 2 assertions; one pass, one failure | Homepage test expects 200 but auth correctly redirects with 302 |
| Composer audit, locked graph | 64 advisory matches / 17 packages, including dev graph | Confirmed version matches; reachability still requires triage |
| npm audit, lockfile-only | 32 vulnerable nodes; 3 critical, 13 high, 11 moderate, 5 low | May overlap advisories; not a count of exploitable app endpoints |
| Earlier session UI observations | Logo, print, core responsive/table checks passed; chart containers and one shared-script null-control error observed | Clearly separated from fresh audit checks; generated assets were not reinspected |

## Top priorities

1. Restrict operational endpoints and enforce policies for users, stock, manufacturing, service, and reporting (SEC-01–SEC-04).
2. Make stock receipt/cancellation idempotent; validate postings and prevent partial writes (STK-01–STK-06, PROD-01–PROD-03).
3. Fix warranty/report defects through approved calculation changes (SERV-01, REP-01–REP-06).
4. Establish isolated MySQL characterization/security tests, then pagination/eager loading/indexes (TEST-01–TEST-03, PERF-01–PERF-04).
5. Triage dependency vulnerabilities and plan a supported runtime/framework migration with compatibility gates (SEC-11, ARCH-05).

## Finding register

Every issue has a canonical entry containing exact location/method, evidence, impact, severity, recommendation, dependencies, regression risk, and expected improvement in the linked domain document. Potential risks are separated from confirmed defects there.

**75 findings:** 52 confirmed defects and 23 potential risks. Severity totals: 3 critical, 37 high, 31 medium, 4 low.

| ID | Classification | Severity | Finding |
|---|---|---|---|
| [SEC-01](SECURITY_AUDIT.md#sec-01) | Confirmed defect | Critical | Unauthenticated operational endpoints |
| [SEC-02](SECURITY_AUDIT.md#sec-02) | Confirmed defect | Critical | Login is treated as authorization |
| [SEC-03](SECURITY_AUDIT.md#sec-03) | Confirmed defect | High | State changes use GET |
| [SEC-04](SECURITY_AUDIT.md#sec-04) | Confirmed defect | High | Product listing can create products through query parameters |
| [SEC-05](SECURITY_AUDIT.md#sec-05) | Confirmed defect | High | Custom login ignores disabled accounts and has no limiter |
| [SEC-06](SECURITY_AUDIT.md#sec-06) | Potential risk | Medium | Logout does not explicitly invalidate session state |
| [SEC-07](SECURITY_AUDIT.md#sec-07) | Confirmed defect | High | Most business writes lack server-side validation |
| [SEC-08](SECURITY_AUDIT.md#sec-08) | Confirmed defect | High | Recipe preview HTML interpolates stored names without escaping |
| [SEC-09](SECURITY_AUDIT.md#sec-09) | Confirmed defect | Medium | Lookup APIs return raw exception messages |
| [SEC-10](SECURITY_AUDIT.md#sec-10) | Confirmed defect | High | Repository seeder contains fixed credentials and personal fields |
| [SEC-11](SECURITY_AUDIT.md#sec-11) | Confirmed defect | Critical | Locked dependencies match published security advisories |
| [SEC-12](SECURITY_AUDIT.md#sec-12) | Potential risk | High | Local configuration is not a production-hardening baseline |
| [SEC-13](SECURITY_AUDIT.md#sec-13) | Confirmed defect | Medium | API guard and token schema do not agree |
| [SEC-14](SECURITY_AUDIT.md#sec-14) | Potential risk | Medium | PDF remote loading has a broad trust boundary |
| [SEC-15](SECURITY_AUDIT.md#sec-15) | Confirmed defect | High | Legacy Docker builds fetch executable installers over HTTP |
| [ARCH-01](MODULE_MAP.md#arch-01) | Confirmed defect | Medium | Business rules are concentrated in oversized controllers |
| [ARCH-02](MODULE_MAP.md#arch-02) | Confirmed defect | Medium | Duplicate code and parallel workflow implementations |
| [ARCH-03](MODULE_MAP.md#arch-03) | Potential risk | Low | Demo routes and apparently orphaned templates remain |
| [ARCH-04](MODULE_MAP.md#arch-04) | Potential risk | Low | Potentially unnecessary dependencies require usage confirmation |
| [ARCH-05](MODULE_MAP.md#arch-05) | Potential risk | High | Production build retains a theme-demo asset base path |
| [DB-01](DATABASE_RELATIONSHIPS.md#db-01) | Confirmed defect | High | No relational foreign keys protect business data |
| [DB-02](DATABASE_RELATIONSHIPS.md#db-02) | Confirmed defect | High | Quantities and prices use incompatible storage types |
| [DB-03](DATABASE_RELATIONSHIPS.md#db-03) | Confirmed defect | High | Business identifiers have no unique database constraints |
| [DB-04](DATABASE_RELATIONSHIPS.md#db-04) | Confirmed defect | Medium | Role inverse relation points to the wrong foreign key |
| [DB-05](DATABASE_RELATIONSHIPS.md#db-05) | Confirmed defect | Medium | Historical migration rollback is incomplete |
| [DB-06](DATABASE_RELATIONSHIPS.md#db-06) | Potential risk | Medium | Units and classification masters are not relationally integrated |
| [DB-07](DATABASE_RELATIONSHIPS.md#db-07) | Confirmed defect | Medium | Documented fresh setup does not provision a usable business account |
| [STK-01](WORKFLOW_MAP.md#stk-01) | Potential risk | High | Multi-record stock writes lack transactions and locks |
| [STK-02](WORKFLOW_MAP.md#stk-02) | Confirmed defect | High | Full transit receipt can be applied repeatedly |
| [STK-03](WORKFLOW_MAP.md#stk-03) | Confirmed defect | High | Partial receipt accepts invalid or excessive quantities |
| [STK-04](WORKFLOW_MAP.md#stk-04) | Confirmed defect | High | Estimate cancellation releases reservations repeatedly |
| [STK-05](WORKFLOW_MAP.md#stk-05) | Confirmed defect | High | Estimate cancellation can release more than was reserved |
| [STK-06](WORKFLOW_MAP.md#stk-06) | Confirmed defect | High | Invoice issuing does not enforce stock or line validity |
| [STK-07](WORKFLOW_MAP.md#stk-07) | Potential risk | High | Projected stock and reservation semantics are not unified |
| [STK-08](WORKFLOW_MAP.md#stk-08) | Confirmed defect | Medium | Material consumption does not create a complete movement ledger |
| [PROD-01](WORKFLOW_MAP.md#prod-01) | Confirmed defect | High | Production save trusts the client material list and consumption |
| [PROD-02](WORKFLOW_MAP.md#prod-02) | Confirmed defect | High | Partial manufacturing completion checks only submitted material IDs |
| [PROD-03](WORKFLOW_MAP.md#prod-03) | Confirmed defect | Medium | Legacy partial completion checks the wrong required amount |
| [PROD-04](WORKFLOW_MAP.md#prod-04) | Potential risk | High | Duplicate recipe components can defeat per-line availability checks |
| [PROD-05](WORKFLOW_MAP.md#prod-05) | Potential risk | Medium | Manufacturing counters and serial lifecycle are disconnected |
| [PROD-06](WORKFLOW_MAP.md#prod-06) | Potential risk | High | Testing records do not enforce a quality-release decision |
| [SERV-01](WORKFLOW_MAP.md#serv-01) | Confirmed defect | High | Repeat-service warranty uses a nonexistent field |
| [SERV-02](WORKFLOW_MAP.md#serv-02) | Confirmed defect | Medium | Service intake depends on hardcoded company and staff IDs |
| [SERV-03](WORKFLOW_MAP.md#serv-03) | Potential risk | Medium | Warranty and reporting date semantics need a single policy |
| [SERV-04](WORKFLOW_MAP.md#serv-04) | Confirmed defect | High | Accessory return updates are not scoped to the service history |
| [SERV-05](WORKFLOW_MAP.md#serv-05) | Potential risk | Medium | Service transitions and edits lack explicit state guards |
| [SALE-01](WORKFLOW_MAP.md#sale-01) | Confirmed defect | Medium | Finished-goods sale rejects selling the exact available quantity |
| [SALE-02](WORKFLOW_MAP.md#sale-02) | Potential risk | High | Current invoices are quantity-issue documents, not a full finance ledger |
| [SALE-03](WORKFLOW_MAP.md#sale-03) | Potential risk | High | Bundle replacement is destructive and not transactional |
| [REP-01](WORKFLOW_MAP.md#rep-01) | Confirmed defect | High | Dashboard event charts group by record creation day |
| [REP-02](WORKFLOW_MAP.md#rep-02) | Confirmed defect | High | Company-only custom report uses a nonexistent column |
| [REP-03](WORKFLOW_MAP.md#rep-03) | Confirmed defect | High | Custom report footer totals ignore selected filters |
| [REP-04](WORKFLOW_MAP.md#rep-04) | Confirmed defect | High | Reports silently cap their supposedly complete population |
| [REP-05](WORKFLOW_MAP.md#rep-05) | Confirmed defect | Medium | In-factory machine list includes delivered histories |
| [REP-06](WORKFLOW_MAP.md#rep-06) | Confirmed defect | Medium | Custom PDF error details are outside report scope |
| [UI-01](UI_UX_AUDIT.md#ui-01) | Confirmed defect | High | Dashboard chart source is missing |
| [UI-02](UI_UX_AUDIT.md#ui-02) | Confirmed defect | Medium | Standalone business pages mix framework versions and duplicate scripts |
| [UI-03](UI_UX_AUDIT.md#ui-03) | Confirmed defect | Medium | Pending-report filter drops the numeric zero status |
| [UI-04](UI_UX_AUDIT.md#ui-04) | Confirmed defect | Medium | Removing an invoice row leaves the displayed total stale |
| [UI-05](UI_UX_AUDIT.md#ui-05) | Confirmed defect | Low | Remember-me checkbox is not wired to authentication |
| [UI-06](UI_UX_AUDIT.md#ui-06) | Potential risk | Medium | Modal, form, and navigation accessibility needs targeted validation |
| [UI-07](UI_UX_AUDIT.md#ui-07) | Potential risk | Medium | Legacy drawers use fixed desktop widths |
| [UI-08](UI_UX_AUDIT.md#ui-08) | Confirmed defect | Medium | Shared scripts assume controls exist on every host page |
| [UI-09](UI_UX_AUDIT.md#ui-09) | Potential risk | Medium | Exports and screen assets depend on external CDN availability |
| [PERF-01](PERFORMANCE_AUDIT.md#perf-01) | Confirmed defect | High | Growing business lists are fetched and rendered without pagination |
| [PERF-02](PERFORMANCE_AUDIT.md#perf-02) | Confirmed defect | High | Relationship loading creates N+1 query paths |
| [PERF-03](PERFORMANCE_AUDIT.md#perf-03) | Confirmed defect | High | Important relationship and lookup predicates have no indexes |
| [PERF-04](PERFORMANCE_AUDIT.md#perf-04) | Confirmed defect | Medium | Invoice listing executes a discarded query set |
| [PERF-05](PERFORMANCE_AUDIT.md#perf-05) | Potential risk | Medium | Stock filters coerce strings and report searches cannot use ordinary indexes well |
| [PERF-06](PERFORMANCE_AUDIT.md#perf-06) | Potential risk | Medium | Client-side export scales with the full rendered DOM |
| [PERF-07](PERFORMANCE_AUDIT.md#perf-07) | Potential risk | Low | Every request loads all theme menu JSON and broad asset builds |
| [TEST-01](IMPROVEMENT_ROADMAP.md#test-01) | Confirmed defect | High | ERP business behavior has no automated regression coverage |
| [TEST-02](IMPROVEMENT_ROADMAP.md#test-02) | Confirmed defect | Medium | The existing feature test expects the wrong guest response |
| [TEST-03](IMPROVEMENT_ROADMAP.md#test-03) | Potential risk | High | Test database isolation is not configured |
| [ARCH-06](MODULE_MAP.md#arch-06) | Confirmed defect | Medium | Production layout selection ignores the normal custom configuration |

## Evidence rules and limits

**Confirmed defect** means the incorrect source path/control flow, missing protection, schema property, or failing test was directly established. It does not mean an exploit or production data corruption was demonstrated. **Potential risk** means the outcome depends on concurrency, data, deployment, or an unapproved business requirement. A source-level absence can be confirmed while its operational outcome remains a risk. Severity is impact priority, not a statement of proven exploitation.

No write workflows, exploit payloads, migrations/rollbacks/seeders, package installation, upgrades, generated-asset rebuilds, or cleanup operations were run. No business rows were changed or exposed. Database inspection was restricted to schema/index metadata and non-executing `EXPLAIN`; no row dumps, totals reconciliation, or concurrency experiments were performed. Tests run were the two existing non-mutating tests. Standard framework execution may create routine logs; caches/logs were excluded from inspection.

Excluded: `vendor/`, `node_modules/`, bundled third-party assets under `resources/vendors/`, generated public CSS/JS/vendor bundles/manifests, binary image/font contents, caches, logs, backups, secret values, and Git object/internal files. Handwritten `public/New/script.js`, `customerScript.js`, and `css/styles.css` are source, not generated assets, and were included. `.env` and Git configuration were inspected only through redacted flags/metadata as explicitly requested. Dependency manifests/lockfiles were included; dependency implementation trees were not.

A whole-tree source inventory and pattern scan was combined with detailed tracing of all nine business controllers, all models and migrations, route registration, providers/middleware, business Blade flows, first-party scripts/styles, build/deployment definitions, and tests. This is not a claim of exhaustive penetration testing, measured line coverage, a complete accessibility certification, or production load testing.
