# Galaxy Inverter — Galaxy ERP

Laravel application for inventory, production, machine testing, warranty service, estimates, and invoicing.

## Local setup

1. Run `composer install` and `npm ci`.
2. Copy `.env.example` to `.env`, set your MySQL connection, and run `php artisan key:generate`.
3. Run `php artisan migrate` and `npm run development`.
4. Run `php artisan serve`.

Brand assets live in `public/brand`. The shared theme source is `resources/sass/branding/galaxy.scss`. Set `BRAND_EMAIL`, `BRAND_PHONE`, and `BRAND_ADDRESS` in `.env` for seller contact details on printed documents.

## Hostinger deployment

- PHP **8.2** is the locally tested version. The locked dependency range also permits PHP 8.3 and 8.4. Use compatible versions for both the build and website runtime; `.php-version` is only a hint. Composer validates the actual environment without a simulated PHP override.
- Production dependencies: `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`.
- Frontend build: `npm ci && npm run build`. The generated `public/` assets and `mix-manifest.json` must be deployed together.
- Serve only the `public/` directory. Keep `.env` and the application outside the public document root.
- Configure the server `.env`, including `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, database connection and the existing application key when moving existing data.
- Back up/import the intended database before reviewing and running required migrations. Do not seed, reset stock or automatically run warehouse backfill on deployment.
- Keep `WAREHOUSES_ENABLED=false` until this server's warehouse schema and stock mapping have been verified. Local activation does not activate Hostinger.
- `storage/` and `bootstrap/cache/` must be writable. Run `php artisan storage:link` and `php artisan config:cache` after server environment configuration.
