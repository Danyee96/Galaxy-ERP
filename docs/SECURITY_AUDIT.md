# Galaxy ERP — Security Audit

> Audit date: **2026-11-05**, Asia/Karachi. This is a read-only application audit of the current working tree, including pre-existing uncommitted branding/table changes. Only these audit documents were created. No improvements described here are implemented or authorized by this report.

[Project Overview](PROJECT_OVERVIEW.md) | [Module Map](MODULE_MAP.md) | [Database Relationships](DATABASE_RELATIONSHIPS.md) | [Workflow Map](WORKFLOW_MAP.md) | [Ui Ux Audit](UI_UX_AUDIT.md) | [Security Audit](SECURITY_AUDIT.md) | [Performance Audit](PERFORMANCE_AUDIT.md) | [Improvement Roadmap](IMPROVEMENT_ROADMAP.md)

## Trust boundaries

1. Anonymous web requests can reach login, template/demo routes, locale switching, and operational closures.
2. Authenticated requests can reach almost every ERP action under the same auth-only group. Menu visibility is not an authorization boundary.
3. Posted IDs and parallel quantity arrays become Eloquent lookups and stock updates without uniform validation/ownership checks.
4. Stored names become controller-generated HTML preview attributes and dynamic DOM fragments.
5. PDF rendering can fetch remote resources and accesses a project-root chroot.
6. The API token driver does not match the installed User token trait/schema.
7. Package/build/CDN inputs form a separate supply-chain boundary.

Severity prioritizes unauthorized mutation and irreversible business impact. No exploitation, credential guessing, record writes, or dangerous endpoint requests were performed.

## Controls already present

- Web middleware includes CSRF protection, with no application exception paths configured; this protects appropriate non-GET requests, **not** the GET mutations.
- Password writes use Laravel Hash; User hides password and remember_token from serialization.
- Most business views use escaped Blade output; the unsafe fragment paths are specifically identified in SEC-08 rather than asserting all Blade is unsafe.
- Query-builder predicates generally bind request values. The observed dynamic report defects do not establish SQL injection; DB::raw expressions inspected are fixed SQL, not directly interpolated user SQL.
- API middleware includes a rate limiter, but the custom web login is outside it.
- PDF embedded PHP is disabled. CORS permits wildcard origins for API paths with credential support false; that setting alone is not a demonstrated cross-origin session compromise.
- Debug was disabled in the inspected local environment. This is not proof of production configuration.

## Redacted environment and Git inspection

| Check | Redacted observation | Boundary |
|---|---|---|
| Application key | Present; never printed | Rotation/strength not assessed |
| Debug | Disabled locally; sample enables it | Remote production settings unknown |
| Local DB posture | Privileged local account, password not set | Local setup only; no database name/host/account values copied |
| Session | File-based; secure-cookie setting not explicit | HTTPS/reverse-proxy production topology unknown |
| Queue | Synchronous locally | No persistent worker availability assessed |
| Mail credential | Configured; never printed | No delivery attempted |
| Current branch tracking | Main tracks destination origin; legacy original remote is also configured | Remote account/URL and credential contents omitted |
| Credential helper | Configured | Helper/token/keychain contents were not read |
| `.env` / backups | Untracked/ignored in current branch; backups ignored | Ignore rules do not prove old Git history was scrubbed |
| Existing local changes | Branding/table modifications predate this audit | Preserved; do not treat them as audit fixes |

Git internals and historical objects were excluded. The legacy history/remote presence is not proof that secrets were never exposed; historical exposure review is a separate controlled task. The current .env was not copied into docs or committed. A redacted source check found a fixed password hash and personal fields in UserSeeder (SEC-10); their values are intentionally omitted.

## Dependency advisory inventory

Commands executed without install/update/fix: `composer audit --locked --format=json` and `npm audit --package-lock-only --json`. Results are dated to this audit and cover development dependencies too. Scanner matches confirm affected locked versions, not application exploitability. Overlapping vulnerabilities and transitive graph nodes must not be added together as unique app exploits.

### Composer locked packages

| Package | Locked version | Matched advisories | Reported severities |
|---|---|---:|---|
| `dompdf/dompdf` | `v2.0.8` | 6 | 2 low, 4 medium |
| `firebase/php-jwt` | `v6.11.1` | 1 | 1 low |
| `guzzlehttp/guzzle` | `7.10.0` | 9 | 1 high, 8 medium |
| `guzzlehttp/psr7` | `2.8.0` | 4 | 4 medium |
| `laravel/framework` | `v8.83.29` | 5 | 1 critical, 1 high, 1 low, 2 medium |
| `league/commonmark` | `2.7.1` | 14 | 9 high, 5 medium |
| `league/flysystem` | `1.1.10` | 1 | 1 low |
| `phpseclib/phpseclib` | `3.0.46` | 5 | 2 high, 1 low, 2 medium |
| `phpunit/phpunit` | `9.6.29` | 1 | 1 high |
| `psy/psysh` | `v0.12.12` | 1 | 1 medium |
| `symfony/http-foundation` | `v5.4.48` | 4 | 2 high, 2 unrated |
| `symfony/http-kernel` | `v5.4.48` | 1 | 1 unrated |
| `symfony/mime` | `v5.4.45` | 2 | 1 high, 1 medium |
| `symfony/polyfill-intl-idn` | `v1.33.0` | 1 | 1 low |
| `symfony/process` | `v5.4.47` | 1 | 1 medium |
| `symfony/routing` | `v5.4.48` | 3 | 2 medium, 1 unrated |
| `symfony/yaml` | `v6.4.26` | 5 | 1 high, 3 low, 1 unrated |

Abandoned packages reported by Composer: `fruitcake/laravel-cors`, `swiftmailer/swiftmailer`. No package was removed or upgraded.

### Advisory references for triage

These links come directly from the read-only Composer advisory metadata. Review affected-version and reachability conditions before selecting a remedy. Dev-only findings should be kept distinct from request-facing runtime code.

| Package | Advisory | Scanner severity | Publisher reference |
|---|---|---|---|
| `dompdf/dompdf` | Dompdf: Embedded SVG images can leak existence of files and directories within the filesystem | medium | [CVE-2026-59943 ](https://github.com/advisories/GHSA-j8qw-6jw8-r297) |
| `dompdf/dompdf` | Dompdf: Denial of Service (DoS) via Resource Exhaustion using Oversized Image Bitmaps | medium | [CVE-2026-59942 ](https://github.com/advisories/GHSA-f5gf-2cj8-52g2) |
| `dompdf/dompdf` | Dompdf: Uncontrolled resource consumption based on declared BMP dimensions | medium | [CVE-2026-59941 ](https://github.com/advisories/GHSA-8hg6-c449-896m) |
| `dompdf/dompdf` | Dompdf: Local file read due to improper file path validation in SVG images encoded as data-URI | medium | [CVE-2026-56722 ](https://github.com/advisories/GHSA-cx96-42px-69fm) |
| `dompdf/dompdf` | Dompdf: File existence oracle via font-face stylesheet declaration | low | [CVE-2026-55555 ](https://github.com/advisories/GHSA-7x2p-4jvh-6384) |
| `dompdf/dompdf` | Dompdf: Chroot Validation Bypass | low | [CVE-2026-55554 ](https://github.com/advisories/GHSA-wvh6-f5jh-8gw4) |
| `firebase/php-jwt` | php-jwt contains weak encryption | low | [CVE-2025-45769 ](https://github.com/advisories/GHSA-2x45-7fc3-mxwq) |
| `guzzlehttp/guzzle` | Guzzle: Noncanonical host can bypass host-based checks | high | [CVE-2026-69246 ](https://github.com/advisories/GHSA-v5mv-p594-2x33) |
| `guzzlehttp/guzzle` | Guzzle: Noncanonical cookie domain keeps subdomain scope | medium | [CVE-2026-69245 ](https://github.com/advisories/GHSA-f7vp-7xgx-4w4r) |
| `guzzlehttp/guzzle` | Guzzle: URI fragments disclosed in redirect Referer headers | medium | [CVE-2026-67354 ](https://github.com/advisories/GHSA-h95v-h523-3mw8) |
| `guzzlehttp/guzzle` | Guzzle: Host-only cookie scope is not preserved | medium | [CVE-2026-67355 ](https://github.com/advisories/GHSA-wm3w-8rrp-j577) |
| `guzzlehttp/guzzle` | Guzzle: Unbounded response cookies risk denial of service | medium | [CVE-2026-67353 ](https://github.com/advisories/GHSA-f283-ghqc-fg79) |
| `guzzlehttp/guzzle` | Guzzle: Cookie Disclosure and Injection via IP-Address Domains | medium | [CVE-2026-59883 ](https://github.com/advisories/GHSA-g446-98w2-8p5w) |
| `guzzlehttp/guzzle` | Guzzle: Proxy-Authorization headers can be sent to origin servers | medium | [CVE-2026-67339 ](https://github.com/advisories/GHSA-94pj-82f3-465w) |
| `guzzlehttp/guzzle` | Dot-only cookie domains match all hosts | medium | [CVE-2026-55767 ](https://github.com/guzzle/guzzle/security/advisories/GHSA-cwxw-98qj-8qjx) |
| `guzzlehttp/guzzle` | Silent HTTPS proxy downgrade to cleartext | medium | [CVE-2026-55568 ](https://github.com/guzzle/guzzle/security/advisories/GHSA-wpwq-4j6v-78m3) |
| `guzzlehttp/psr7` | guzzlehttp/psr7: Host Confusion via Weak URI Host Validation | medium | [CVE-2026-59882 ](https://github.com/advisories/GHSA-c2w2-prh8-qm98) |
| `guzzlehttp/psr7` | CRLF injection in HTTP start-line serialization | medium | [CVE-2026-55766 ](https://github.com/guzzle/psr7/security/advisories/GHSA-vm85-hxw5-5432) |
| `guzzlehttp/psr7` | CRLF injection via URI host component | medium | [CVE-2026-49214 ](https://github.com/guzzle/psr7/security/advisories/GHSA-hq7v-mx3g-29hw) |
| `guzzlehttp/psr7` | Host confusion via authority reinterpretation | medium | [CVE-2026-48998 ](https://github.com/guzzle/psr7/security/advisories/GHSA-34xg-wgjx-8xph) |
| `laravel/framework` | Laravel TokenGuard authentication bypass through non-string API credentials | critical | [CVE-2026-114509 ](https://github.com/advisories/GHSA-f9x3-7vq9-cph6) |
| `laravel/framework` | Laravel: XSS in Debug Page Information | low | [CVE-2026-102279 ](https://github.com/advisories/GHSA-jh5r-qr3c-85q8) |
| `laravel/framework` | Laravel Framework: Temporary Signed URL Path Confusion | medium | [PKSA-m5cs-t1y6-qpcs ](https://github.com/advisories/GHSA-crmm-hgp2-wgrp) |
| `laravel/framework` | Laravel Framework: CRLF injection in default email rule | high | [PKSA-3r5d-mb8f-1qw9 ](https://github.com/advisories/GHSA-5vg9-5847-vvmq) |
| `laravel/framework` | Laravel has a File Validation Bypass | medium | [CVE-2025-27515 ](https://github.com/advisories/GHSA-78fx-h6xr-vch4) |
| `league/commonmark` | league/commonmark: DisallowedRawHtml bypassed when a disallowed tag name ends the raw-HTML literal | medium | [PKSA-m2dq-1fhr-29b1 ](https://github.com/advisories/GHSA-97jj-33gv-5xf9) |
| `league/commonmark` | league/commonmark: Quadratic-time denial of service in the GitHub Flavored Markdown Table extension block-start scan | high | [PKSA-m4t9-vsgq-8khn ](https://github.com/advisories/GHSA-3q6v-r5mr-hxv8) |
| `league/commonmark` | league/commonmark: Denial of service via distinctly-named attributes in the Attributes extension | high | [PKSA-zyf5-hrxv-hrd7 ](https://github.com/advisories/GHSA-8rr7-cvq3-gmfh) |
| `league/commonmark` | league/commonmark: Denial of service in the SmartPunct and Attributes extensions | high | [PKSA-nv44-1b4d-6gjg ](https://github.com/advisories/GHSA-jjv6-8j6v-6j52) |
| `league/commonmark` | league/commonmark XSS: `on*` event-handler filter in `AttributesExtension` bypassed with a U+000C form feed | high | [PKSA-kr3s-894t-g5w2 ](https://github.com/advisories/GHSA-f8fg-pg57-v4j8) |
| `league/commonmark` | league/commonmark: Denial of service via crafted code fences, reference links, and emphasis delimiters | high | [PKSA-9q1p-3s19-bp1q ](https://github.com/advisories/GHSA-j8pm-gj4c-rq4x) |
| `league/commonmark` | league/commonmark: Denial of service via deeply nested XML output | medium | [PKSA-5mzr-szzf-z6cn ](https://github.com/advisories/GHSA-mj63-m3rc-8ppr) |
| `league/commonmark` | league/commonmark: Denial of service via colliding heading slugs | high | [PKSA-cqd6-fg4n-nxpf ](https://github.com/advisories/GHSA-mh25-x5hq-wrqp) |
| `league/commonmark` | league/commonmark:  Denial of service via duplicate footnote definitions | high | [PKSA-1q6p-sqkj-8mmj ](https://github.com/advisories/GHSA-jfm3-95jq-q3rf) |
| `league/commonmark` | league/commonmark: Denial of service via adjacent inline attribute blocks | high | [PKSA-mc58-w91n-f5gv ](https://github.com/advisories/GHSA-g2gp-3wwq-f4ph) |
| `league/commonmark` | league/commonmark: Quadratic-time denial of service when parsing crafted Markdown | high | [CVE-2026-71488 ](https://github.com/advisories/GHSA-2q4p-g7hv-5rgv) |
| `league/commonmark` | league/commonmark: AttributesExtension href/src unsafe-link filter bypass via embedded control bytes | medium | [CVE-2026-71478 ](https://github.com/advisories/GHSA-29pj-957v-52mc) |
| `league/commonmark` | league/commonmark has an embed extension allowed_domains bypass | medium | [CVE-2026-33347 ](https://github.com/advisories/GHSA-hh8v-hgvp-g3f5) |
| `league/commonmark` | CommonMark has DisallowedRawHtml extension bypass via whitespace in HTML tag names | medium | [CVE-2026-30838 ](https://github.com/advisories/GHSA-4v6x-c7xx-hw9f) |
| `league/flysystem` | Flysystem: WhitespacePathNormalizer's control-character (CorruptedPathDetected) check is bypassed by malformed UTF-8 in the path, affecting every adapter | low | [CVE-2026-102601 ](https://github.com/advisories/GHSA-cxf4-7mrp-vvpr) |
| `phpseclib/phpseclib` | phpseclib — non-constant-time X25519 scalar multiplication permits full private-key recovery | medium | [CVE-2026-84308 ](https://github.com/advisories/GHSA-q97c-8qh3-fpc6) |
| `phpseclib/phpseclib` | phpseclib: X.509 certificate validation sends attacker-controlled outbound requests (server-side request forgery) via Authority Information Access | medium | [CVE-2026-55599 ](https://github.com/advisories/GHSA-m557-wrgg-6rp4) |
| `phpseclib/phpseclib` | phpseclib has a CVE-2024-27355 mitigation bypass — OID amplification DoS in ASN1::decodeOID() | high | [CVE-2026-44167 ](https://github.com/advisories/GHSA-3qpq-r242-jqj7) |
| `phpseclib/phpseclib` | phpseclib has a variable-time HMAC comparison in SSH2::get_binary_packet() using != instead of hash_equals() | low | [CVE-2026-40194 ](https://github.com/advisories/GHSA-r854-jrxh-36qx) |
| `phpseclib/phpseclib` | phpseclib's AES-CBC unpadding susceptible to padding oracle timing attack | high | [CVE-2026-32935 ](https://github.com/advisories/GHSA-94g3-g5v7-q4jg) |
| `phpunit/phpunit` | Unsafe Deserialization in PHPT Code Coverage Handling | high | [CVE-2026-24765 ](https://github.com/sebastianbergmann/phpunit/security/advisories/GHSA-vvj3-c3rp-c85p) |
| `psy/psysh` | PsySH has Local Privilege Escalation via CWD .psysh.php auto-load | medium | [CVE-2026-25129 ](https://github.com/advisories/GHSA-4486-gxhx-5mg7) |
| `symfony/http-foundation` | CVE-2026-118514: HEAD Requests Bypass the methods Option of security.access_control Rules and Firewalls: Incomplete Fix for CVE-2026-45075 | unrated | [CVE-2026-118514 ](https://symfony.com/cve-2026-118514) |
| `symfony/http-foundation` | Generated URLs Can Point to a Host Chosen by the Client | unrated | [PKSA-b2gg-vk1g-6d5q ](https://github.com/symfony/symfony/security/advisories/GHSA-5mx8-mwrp-5693) |
| `symfony/http-foundation` | CVE-2026-108082: ReDoS in HeaderUtils::split() Reachable Through the Cache-Control Request Header | high | [CVE-2026-108082 ](https://symfony.com/cve-2026-108082) |
| `symfony/http-foundation` | CVE-2025-64500: Incorrect parsing of PATH_INFO can lead to limited authorization bypass | high | [CVE-2025-64500 ](https://symfony.com/blog/cve-2025-64500-incorrect-parsing-of-path-info-can-lead-to-limited-authorization-bypass) |
| `symfony/http-kernel` | Inline Fragments Let Untrusted Forwarded Headers Set the Host, Scheme and Port of the URLs Generated for the Main Request | unrated | [PKSA-3gxv-7xhq-mgmz ](https://github.com/symfony/symfony/security/advisories/GHSA-wg5q-m2ww-f3c9) |
| `symfony/mime` | CVE-2026-45070: Email Header Injection via Non-Token Characters in Mime Parameter Names | medium | [CVE-2026-45070 ](https://symfony.com/cve-2026-45070) |
| `symfony/mime` | CVE-2026-45067: Email Header / SMTP Command Injection via CRLF in Symfony\Component\Mime\Address | high | [CVE-2026-45067 ](https://symfony.com/cve-2026-45067) |
| `symfony/polyfill-intl-idn` | CVE-2026-46644: symfony/polyfill-intl-idn accepts xn-- labels whose Punycode payload decodes to ASCII-only: insecure equivalence | low | [CVE-2026-46644 ](https://symfony.com/cve-2026-46644) |
| `symfony/process` | Symfony's incorrect argument escaping under MSYS2/Git Bash can lead to destructive file operations on Windows | medium | [CVE-2026-24739 ](https://github.com/advisories/GHSA-r39x-jcww-82v6) |
| `symfony/routing` | CVE-2026-118516: Host Placeholders Can Match the Start of the Path in Compiled URL Matchers, Bypassing Path-Based Access Control | unrated | [CVE-2026-118516 ](https://symfony.com/cve-2026-118516) |
| `symfony/routing` | CVE-2026-48784: UrlGenerator Dot-Segment Encoding Skips Every Other Chained `../` or `./` → Generated URL Collapses Off-Route Under RFC 3986 Normalization | medium | [CVE-2026-48784 ](https://symfony.com/cve-2026-48784) |
| `symfony/routing` | CVE-2026-45065: UrlGenerator Route-Requirement Bypass via Unanchored Regex Alternation → Off-Site //host URL Injection | medium | [CVE-2026-45065 ](https://symfony.com/cve-2026-45065) |
| `symfony/yaml` | Exponential and Quadratic Parse Times in the YAML Parser Let a Single Document Hold a Process | unrated | [PKSA-nktx-f1n1-b4yd ](https://github.com/symfony/symfony/security/advisories/GHSA-hwfg-qwvh-2wfx) |
| `symfony/yaml` | CVE-2026-109413: Unbounded Recursion in the YAML Inline Lexer Lets a Single Document Crash the Process | high | [CVE-2026-109413 ](https://symfony.com/cve-2026-109413) |
| `symfony/yaml` | CVE-2026-45304: YAML Parser Exponential Memory Allocation via Recursive Collection-Alias Expansion ("Billion Laughs") | low | [CVE-2026-45304 ](https://symfony.com/cve-2026-45304) |
| `symfony/yaml` | CVE-2026-45305: YAML Parser ReDoS via Catastrophic Backtracking in Parser::cleanup() Regex | low | [CVE-2026-45305 ](https://symfony.com/cve-2026-45305) |
| `symfony/yaml` | CVE-2026-45133: YAML Parser Stack Exhaustion via Unbounded Recursion in Nested Blocks, Sequences, and Mappings | low | [CVE-2026-45133 ](https://symfony.com/cve-2026-45133) |

### npm lockfile summary

32 vulnerable graph nodes: **3 critical, 13 high, 11 moderate, 5 low**. The npm graph includes build dependencies, so these counts do not establish a production request-path exposure.

| Direct package flagged | Locked resolution | npm severity | Context |
|---|---|---|---|
| `axios` | `0.21.4` | high | Direct build/runtime dependency; inspect transitive via entries before changing it. |
| `laravel-mix` | `6.0.49` | moderate | Direct build/runtime dependency; inspect transitive via entries before changing it. |
| `resolve-url-loader` | `3.1.5` | moderate | Direct build/runtime dependency; inspect transitive via entries before changing it. |
| `rtlcss` | `2.6.2` | moderate | Direct build/runtime dependency; inspect transitive via entries before changing it. |
| `webpack` | `5.75.0` | critical | Direct build/runtime dependency; inspect transitive via entries before changing it. |

CDN Bootstrap/jQuery/XLSX/html2pdf copies and excluded bundled third-party source are outside npm-lock advisory coverage. Do not claim that a clean future npm audit would certify those assets. See UI-09.

## Verification boundaries and follow-up

After authorization for improvements, add synthetic-user tests for every capability and object-scope action, active/disabled authentication, request bounds and array integrity, CSRF-safe verbs, idempotent postings, HTML escaping, generic exception responses, and token guard behavior. Restrict dangerous operational routes without calling them. Test PDF resource policies in a disposable environment; do not probe the live network/filesystem through untrusted document payloads.

Laravel 8 security support ended on 2023-01-24 according to the [official Laravel release support table](https://laravel.com/docs/8.x/releases#support-policy). Plan the dependency/runtime transition with the owner; this report authorizes no upgrades or route/API changes.

## Findings

## Confirmed defects

<a id="sec-01"></a>

### SEC-01 — Unauthenticated operational endpoints

- **Classification / severity:** Confirmed defect / **Critical**.
- **Exact file and method/section:** [routes/web.php:62](../routes/web.php#L62) — `Route::get('/migrate'`; [routes/web.php:48](../routes/web.php#L48) — `Route::get('/clear'`.
- **Problem and evidence:** Both closures are outside the auth group. They invoke migration and cache/config/route/view clearing commands from GET requests.
- **Potential impact:** An anonymous visitor can attempt schema changes or invalidate application caches. Production Artisan confirmation may prevent a migration in some environments; it does not protect the endpoint itself.
- **Recommended fix:** Remove web access through an approved route change; run these operations through authenticated deployment commands. Until then restrict these paths at the web server.
- **Dependencies:** Deployment process and any existing administrative bookmarks.
- **Regression risks:** Do not call these endpoints during verification. Disabling them may affect undocumented operational tooling.
- **Expected improvement:** Eliminates public deployment and cache-control entry points.

<a id="sec-02"></a>

### SEC-02 — Login is treated as authorization

- **Classification / severity:** Confirmed defect / **Critical**.
- **Exact file and method/section:** [routes/web.php:72](../routes/web.php#L72) — `Route::group(['middleware' => 'auth']`; [app/Http/Controllers/user_portal/UserController.php:100](../app/Http/Controllers/user_portal/UserController.php#L100) — `updateUserPassword`; [app/Providers/AuthServiceProvider.php:24](../app/Providers/AuthServiceProvider.php#L24) — `boot`; [resources/views/panels/sidebar.blade.php:26](../resources/views/panels/sidebar.blade.php#L26) — `role_id`.
- **Problem and evidence:** Business routes have auth but no role/policy checks; the provider declares no policies. Password/role changes accept target user IDs without an administrative authorization check. Sidebar conditions only hide links.
- **Potential impact:** Any authenticated role can reach sensitive actions, including account takeover and privilege escalation, by constructing requests. No exploit was executed.
- **Recommended fix:** Define a signed-off capability matrix, enforce it with policies/middleware on every controller action, and scope target objects. Keep Blade checks consistent with those policies.
- **Dependencies:** Role IDs, current staff responsibilities, service accounts, and action inventory in MODULE_MAP.
- **Regression risks:** Incorrect policies can block legitimate factory or account workflows. Introduce a deny/allow test matrix before enforcing them.
- **Expected improvement:** Server-enforced least privilege for all business operations.

<a id="sec-03"></a>

### SEC-03 — State changes use GET

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [routes/web.php:122](../routes/web.php#L122) — `Route::get('/mark-as-packed`; [routes/web.php:278](../routes/web.php#L278) — `Route::get('/delete-estimate`; [app/Http/Controllers/user_portal/ModelController.php:711](../app/Http/Controllers/user_portal/ModelController.php#L711) — `markCompleteUpsModelNew`; [app/Http/Controllers/user_portal/RawMaterialController.php:811](../app/Http/Controllers/user_portal/RawMaterialController.php#L811) — `saveTransitInventory`.
- **Problem and evidence:** Packing, transit receipt, estimate cancellation, activation/deactivation, manufacturing completion, and logout are registered as GET/HEAD routes. The full candidate list is in MODULE_MAP.
- **Potential impact:** Links, navigations, previews, and crafted cross-site top-level requests can cause state changes; GET is outside normal CSRF verification.
- **Recommended fix:** Migrate mutations to POST/PATCH/DELETE with CSRF verification, authorization, and idempotency. Preserve compatibility only through explicitly approved non-mutating transitions.
- **Dependencies:** All Blade links, AJAX callers, barcode workflows, and any external consumers.
- **Regression risks:** Changing verbs is an API contract change. Coordinate clients rather than silently breaking current routes.
- **Expected improvement:** Read-only navigation and protected intentional changes.

<a id="sec-04"></a>

### SEC-04 — Product listing can create products through query parameters

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/QuickBookController.php:30](../app/Http/Controllers/user_portal/QuickBookController.php#L30) — `productService`; [routes/web.php:250](../routes/web.php#L250) — `Route::get('product-services'`.
- **Problem and evidence:** productService branches on type/non_type/s_type/b_type and performs Products::create and Bundle::create although its route accepts GET.
- **Potential impact:** A list request can write stock master records without POST CSRF protection; bookmarks/retries can duplicate products.
- **Recommended fix:** Separate validated create operations from the read-only list endpoint, authorize them, and add explicit POST forms.
- **Dependencies:** public/New/script.js generates GET forms; product and bundle create UI; current query-string integrations.
- **Regression risks:** Preserve filters on the GET route and migrate generated form actions together.
- **Expected improvement:** A genuinely read-only product page and auditable product creation.

<a id="sec-05"></a>

### SEC-05 — Custom login ignores disabled accounts and has no limiter

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/LoginController.php:19](../app/Http/Controllers/user_portal/LoginController.php#L19) — `userLogin`; [app/Providers/RouteServiceProvider.php:57](../app/Providers/RouteServiceProvider.php#L57) — `configureRateLimiting`; [routes/web.php:70](../routes/web.php#L70) — `Route::post('userLogin'`.
- **Problem and evidence:** Login validates only presence, distinguishes unknown email from wrong password, and calls Auth::attempt without status=active. Only the API limiter is configured/applied; web login has no throttle.
- **Potential impact:** Disabled users may still authenticate; credential enumeration and unbounded password guessing are possible.
- **Recommended fix:** Require an active-account condition, generic error responses, normalized credential validation, and an IP/account-aware login limiter.
- **Dependencies:** Account-status meaning, support procedures, existing sessions, and authentication tests.
- **Regression risks:** Do not lock out shared factory terminals through an excessively strict IP-only limit. Verify normal SessionGuard session rotation rather than assuming it is absent.
- **Expected improvement:** Disabled-account enforcement and bounded authentication abuse.

<a id="sec-07"></a>

### SEC-07 — Most business writes lack server-side validation

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/QuickBookController.php:890](../app/Http/Controllers/user_portal/QuickBookController.php#L890) — `saveInvoice`; [app/Http/Controllers/user_portal/RawMaterialController.php:658](../app/Http/Controllers/user_portal/RawMaterialController.php#L658) — `saveFinishedGoodsProduction`; [app/Http/Controllers/user_portal/ModelController.php:593](../app/Http/Controllers/user_portal/ModelController.php#L593) — `addInCompleteUpsModelNew`; [app/Http/Controllers/user_portal/MachineTestingController.php:24](../app/Http/Controllers/user_portal/MachineTestingController.php#L24) — `saveTesting`; [app/Http/Controllers/user_portal/UserController.php:28](../app/Http/Controllers/user_portal/UserController.php#L28) — `saveUser`; [app/Http/Controllers/user_portal/UnitController.php:31](../app/Http/Controllers/user_portal/UnitController.php#L31) — `saveUnit`; [app/Http/Controllers/user_portal/SerializationController.php:36](../app/Http/Controllers/user_portal/SerializationController.php#L36) — `saveSerialization`.
- **Problem and evidence:** Among 172 business-controller public methods, only userLogin and updateBundleProducts invoke Laravel validation. Other saves read request fields directly or perform limited ad hoc checks; there are no application FormRequest classes.
- **Potential impact:** Negative, missing, overlong, mismatched-array, invalid-ID, invalid-date, and unsupported-state payloads reach business calculations or fail as server errors.
- **Recommended fix:** Introduce operation-specific FormRequests for scalar values, exists checks, numeric bounds, dates, enums, and aligned keyed arrays. Recompute authoritative stock inputs server-side.
- **Dependencies:** SEC-02, quantity precision, business limits, legacy form field names, and JSON response contracts.
- **Regression risks:** Stricter validation can reject accepted legacy input. Characterize valid examples first and preserve documented rounding rules.
- **Expected improvement:** Invalid input fails before any write and yields usable field errors.

<a id="sec-08"></a>

### SEC-08 — Recipe preview HTML interpolates stored names without escaping

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/QuickBookController.php:598](../app/Http/Controllers/user_portal/QuickBookController.php#L598) — `getProductEstimates`; [app/Http/Controllers/user_portal/RawMaterialController.php:425](../app/Http/Controllers/user_portal/RawMaterialController.php#L425) — `getSemiRawMaterialProduction`; [app/Http/Controllers/user_portal/RawMaterialController.php:556](../app/Http/Controllers/user_portal/RawMaterialController.php#L556) — `getFinishedGoodsProduction`; [app/Http/Controllers/user_portal/ModelController.php:531](../app/Http/Controllers/user_portal/ModelController.php#L531) — `getModelProduction`; [resources/views/user_portal/New/create-estimate.blade.php:107](../resources/views/user_portal/New/create-estimate.blade.php#L107) — `.html(response.response.data)`; [public/New/script.js:805](../public/New/script.js#L805) — `openProductSidebar`.
- **Problem and evidence:** Controller-built input value attributes concatenate product/material names directly into HTML; AJAX consumers inject the returned fragment with .html(). The product drawer also interpolates DOM-derived name/SKU/type values into innerHTML template attributes without encoding.
- **Potential impact:** Stored quotes/markup can break attributes and create a stored-XSS path. Product creation accepts unvalidated names. No script payload was inserted into business data.
- **Recommended fix:** Render fragments through escaped Blade or return structured JSON and set DOM text/value properties. Audit all concatenated fragment fields.
- **Dependencies:** Preview consumers and their current nested JSON envelopes.
- **Regression risks:** A switch to JSON is a contract change; an escaped Blade partial can preserve the current envelope.
- **Expected improvement:** Names remain plain text and cannot become executable DOM.

<a id="sec-09"></a>

### SEC-09 — Lookup APIs return raw exception messages

- **Classification / severity:** Confirmed defect / **Medium**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/QuickBookController.php:406](../app/Http/Controllers/user_portal/QuickBookController.php#L406) — `getCategory`; [app/Http/Controllers/user_portal/QuickBookController.php:1005](../app/Http/Controllers/user_portal/QuickBookController.php#L1005) — `getProductQuantity`; [app/Http/Controllers/user_portal/QuickBookController.php:988](../app/Http/Controllers/user_portal/QuickBookController.php#L988) — `getInvoiceProductService`.
- **Problem and evidence:** Catch blocks return $e->getMessage() in JSON, independently of APP_DEBUG.
- **Potential impact:** Database, schema, and internal implementation details can leak to authenticated users during failures.
- **Recommended fix:** Return a stable generic error and correlation ID, while logging a redacted exception internally.
- **Dependencies:** Frontend error handling and support diagnostics.
- **Regression risks:** Retain status codes and response shape until callers are migrated.
- **Expected improvement:** Useful diagnostics without exposing internal exception text.

<a id="sec-10"></a>

### SEC-10 — Repository seeder contains fixed credentials and personal fields

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [database/seeders/UserSeeder.php:15](../database/seeders/UserSeeder.php#L15) — `run`.
- **Problem and evidence:** The user seeder contains a fixed password hash and hardcoded personal/contact fields. Values and hashes are deliberately excluded from this report.
- **Potential impact:** Public source can disclose personal data and encourage deployment of a known shared account. Current live credentials were not inspected or tested in this audit.
- **Recommended fix:** Replace with synthetic fixture data and a deliberate secure administrator-bootstrap command; assess whether previously published values require remediation.
- **Dependencies:** Fresh-install procedure, fixture consumers, repository publication history handled separately.
- **Regression risks:** Do not rewrite historical commits or rotate live accounts without an approved incident/change plan.
- **Expected improvement:** Safe bootstrap fixtures and no embedded real personal records.

<a id="sec-11"></a>

### SEC-11 — Locked dependencies match published security advisories

- **Classification / severity:** Confirmed defect / **Critical**.
- **Exact file and method/section:** [composer.lock:8](../composer.lock#L8) — `packages`; [package-lock.json:5](../package-lock.json#L5) — `packages`; [composer.json:10](../composer.json#L10) — `require`; [package.json:12](../package.json#L12) — `devDependencies`.
- **Problem and evidence:** Read-only Composer audit reports 64 advisories across 17 packages; npm reports 32 vulnerable package nodes. Composer includes one critical advisory and npm includes three critical nodes. These are scanner matches, not 96 distinct proven exploits.
- **Potential impact:** Known vulnerable library versions remain in the dependency graph. Exploitability depends on enabled features, data flow, and dev-versus-production exposure.
- **Recommended fix:** Triage advisory reachability; plan compatible patches/framework migration in a separate approved effort, with authentication, PDF, frontend, and stock characterization tests first.
- **Dependencies:** PHP runtime, Laravel 8 compatibility, Passport, PDF rendering, build toolchain, and TEST-01.
- **Regression risks:** Major upgrades and automated audit fixes can alter framework behavior and lockfiles. None were performed.
- **Expected improvement:** Removes reachable known vulnerabilities through controlled dependency changes.

<a id="sec-13"></a>

### SEC-13 — API guard and token schema do not agree

- **Classification / severity:** Confirmed defect / **Medium**.
- **Exact file and method/section:** [config/auth.php:44](../config/auth.php#L44) — `'api'`; [routes/api.php:16](../routes/api.php#L16) — `Route::middleware('auth:api')`; [app/Models/User.php:9](../app/Models/User.php#L9) — `HasApiTokens`.
- **Problem and evidence:** The api guard uses the legacy token driver, but User uses Passport tokens and the inspected users schema has no api_token column. No Passport::routes registration is present in application providers.
- **Potential impact:** The declared /api/user contract is not coherently configured for the available token storage. A token-bearing request can fail rather than authenticate as intended.
- **Recommended fix:** Decide whether this API is required. Configure one supported token system and test valid/invalid/revoked tokens before removing any package or route.
- **Dependencies:** External consumers and the SEC-11 TokenGuard advisory triage.
- **Regression risks:** Changing guards or dropping OAuth tables can invalidate integrations; do not infer non-use from absent UI links.
- **Expected improvement:** A documented, testable token-authentication contract.

<a id="sec-15"></a>

### SEC-15 — Legacy Docker builds fetch executable installers over HTTP

- **Classification / severity:** Confirmed defect / **High**.
- **Exact file and method/section:** [docker/8.0/Dockerfile:31](../docker/8.0/Dockerfile#L31) — `getcomposer.org`; [docker/7.4/Dockerfile:31](../docker/7.4/Dockerfile#L31) — `getcomposer.org`.
- **Problem and evidence:** Both recipes pipe an HTTP Composer installer into PHP and include legacy toolchain bootstrap commands.
- **Potential impact:** A build using these recipes has an unverified executable download step. Whether these Dockerfiles are deployed is unverified.
- **Recommended fix:** If retained, use authenticated HTTPS downloads with integrity verification and a maintained pinned base/toolchain; retire only after dependency review.
- **Dependencies:** Actual container usage, target PHP version, build reproducibility, and SEC-11.
- **Regression risks:** Container modernization can change extensions, UID mappings, and runtime behavior; use a separate tested image change.
- **Expected improvement:** Reproducible builds without an unauthenticated executable download.

## Potential risks and design questions

<a id="sec-06"></a>

### SEC-06 — Logout does not explicitly invalidate session state

- **Classification / severity:** Potential risk / **Medium**.
- **Exact file and method/section:** [app/Http/Controllers/user_portal/IndexController.php:119](../app/Http/Controllers/user_portal/IndexController.php#L119) — `logout`.
- **Problem and evidence:** The custom logout calls Auth::logout and redirects without explicit session invalidation or CSRF-token regeneration.
- **Potential impact:** Other session data can survive logout. This is not evidence of a login session-fixation exploit; the framework session guard performs its own authentication-session work.
- **Recommended fix:** After agreeing the logout contract, invalidate the session and regenerate the CSRF token; use POST logout.
- **Dependencies:** Language/session preferences, authentication guard behavior, and SEC-03.
- **Regression risks:** Users may lose intended session preferences; test multiple tabs and remember-me behavior.
- **Expected improvement:** Predictable session cleanup on shared workstations.

<a id="sec-12"></a>

### SEC-12 — Local configuration is not a production-hardening baseline

- **Classification / severity:** Potential risk / **High**.
- **Exact file and method/section:** [config/session.php:171](../config/session.php#L171) — `'secure'`; [config/database.php:18](../config/database.php#L18) — `'mysql'`; [.env.example:4](../.env.example#L4) — `APP_DEBUG`; [.gitignore:22](../.gitignore#L22) — `Local configuration`.
- **Problem and evidence:** Redacted inspection found debug disabled locally, a privileged local DB account without a password, and no explicit secure-cookie override. The sample environment enables debug. No confidential values are reproduced.
- **Potential impact:** Reusing local settings for an internet-facing deployment could expose data or weaken transport/session and database isolation. Current production configuration was not inspected.
- **Recommended fix:** Create a redacted deployment checklist: least-privilege DB credentials, HTTPS/cookie settings, debug disabled, restricted backups and keys, and correct document root.
- **Dependencies:** Hosting architecture, reverse proxy, secret manager, and deployment owner.
- **Regression risks:** Secure cookies on plain HTTP break local sessions; local and production settings must stay separate.
- **Expected improvement:** Production settings can be verified without exposing secrets.

<a id="sec-14"></a>

### SEC-14 — PDF remote loading has a broad trust boundary

- **Classification / severity:** Potential risk / **Medium**.
- **Exact file and method/section:** [config/dompdf.php:267](../config/dompdf.php#L267) — `enable_remote`; [config/dompdf.php:72](../config/dompdf.php#L72) — `chroot`; [app/Http/Controllers/user_portal/IndexController.php:594](../app/Http/Controllers/user_portal/IndexController.php#L594) — `customMachineDataPDF`.
- **Problem and evidence:** Remote fetching is enabled and chroot is the project root. Print templates include external CSS/scripts; uploaded arbitrary PDF HTML is not an observed application feature.
- **Potential impact:** If untrusted URLs/markup reach PDF resources, network access or excessive resource use may be possible, amplified by affected PDF dependencies. This audit did not demonstrate SSRF.
- **Recommended fix:** Inventory required PDF resources; use local assets and a narrow chroot/allowlist, disable unused remote access, and bound export workload.
- **Dependencies:** Receipt/report templates and PDF dependency remediation.
- **Regression risks:** Remote styles/fonts may disappear from output if disabled without replacing them.
- **Expected improvement:** Smaller, explicit PDF resource and filesystem access.
