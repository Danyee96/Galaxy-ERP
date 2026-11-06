# Galaxy Inverter — Galaxy ERP

Laravel application for inventory, production, machine testing, warranty service, estimates, and invoicing.

## Local setup

1. Run `composer install` and `npm ci`.
2. Copy `.env.example` to `.env`, set your MySQL connection, and run `php artisan key:generate`.
3. Run `php artisan migrate` and `npm run development`.
4. Run `php artisan serve`.

Brand assets live in `public/brand`. The shared theme source is `resources/sass/branding/galaxy.scss`. Set `BRAND_EMAIL`, `BRAND_PHONE`, and `BRAND_ADDRESS` in `.env` for seller contact details on printed documents.
