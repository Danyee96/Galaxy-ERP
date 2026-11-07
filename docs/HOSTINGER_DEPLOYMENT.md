# Persistent Hostinger deployment

GitHub Actions (`.github/workflows/deploy-hostinger.yml`) is the sole automatic deployment owner. Disable Hostinger's old Git auto-deployment, which clones the repository into the web root.

The workflow synchronizes a private checkout into `.galaxy-deploy/checkout`, then runs:

```bash
PHP_BIN=/opt/alt/php82/usr/bin/php COMPOSER_BIN=/usr/local/bin/composer \
  bash .galaxy-deploy/checkout/scripts/deploy-hostinger.sh \
  /home/u889134032/domains/erp.galaxyinverter.com https://erp.galaxyinverter.com
```

The checkout must be outside `laravel_app` and `public_html`. The script synchronizes application code into `laravel_app`, installs only locked production dependencies with PHP 8.2, and publishes only real public assets into `public_html`. `index.php` supports both standard Laravel public/ and this sibling layout. Laravel's `public_path()` points at the actual served directory, including CLI cache commands.

Production `.env` must already exist privately with the existing APP_KEY and `APP_ENV=production`, `APP_DEBUG=false`, `WAREHOUSES_ENABLED=false` for an uninitialized ledger. An explicitly activated `true` value is preserved when warehouse state is ready. Deployments verify these flags and the environment file's hash, never replace it, and never generate keys, migrate, seed, backfill or reset databases. Storage, vendor dependencies and uploaded public files are preserved. Obsolete private web-root entries are quarantined under the private `.galaxy-deploy/backups/` directory rather than deleted. Public copies have normal file/directory permissions; public storage links expose only `storage/app/public`.

Required GitHub secrets: `HOSTINGER_SSH_KEY`, `HOSTINGER_KNOWN_HOSTS` (the verified SSH host key). Variables: `HOSTINGER_HOST`, `HOSTINGER_PORT`, `HOSTINGER_USER`, `HOSTINGER_DOMAIN_DIR`, `HOSTINGER_PHP_BIN`, `HOSTINGER_DEPLOY_ENABLED=true`. The workflow serializes production deployments and can also be dispatched manually from `main`. Never enable Hostinger's old Git auto-deployment alongside it.

Each deployment verifies `/login`, `/css/bootstrap.css`, `/brand/erp.js`, private-file denials, and PHP 8.2 plus required extensions in the actual web runtime. The short-lived unguessable health probe is removed immediately and displays no secrets. Production database verification uses read-only hashes stored privately; it never restores or rewrites records.

Local two-deployment regression (temporary SQLite, configuration and uploads only):

```bash
python3 tests/Deployment/hostinger-deploy-test.py
```

Backups consume disk space; retain the needed rollback snapshots and remove older ones manually after verification. For rollback, restore application/public code from a private snapshot while preserving the current `.env`, storage and uploads, install that snapshot's locked dependencies, then clear compiled views and rebuild configuration cache. Do not restore database records as part of code rollback.
