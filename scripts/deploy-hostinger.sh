#!/usr/bin/env bash
# Usage: PHP_BIN=/path/to/php82 COMPOSER_BIN=/path/to/composer bash scripts/deploy-hostinger.sh DOMAIN_DIRECTORY SITE_URL
set -Eeuo pipefail
umask 027
SOURCE=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)
DOMAIN=$(cd "${1:?Provide the domain directory containing laravel_app and public_html}" && pwd -P)
SITE_URL=${2:-}
APP="$DOMAIN/laravel_app"
WEB="$DOMAIN/public_html"
PHP_BIN=${PHP_BIN:-$(command -v php)}
COMPOSER_BIN=${COMPOSER_BIN:-$(command -v composer)}
[[ "$DOMAIN" != / && "$SOURCE" != "$APP" && "$SOURCE" != "$WEB" && "$SOURCE" != "$WEB/"* && "$APP" != "$SOURCE/"* ]] || { echo 'Use a separate private checkout outside laravel_app and public_html.' >&2; exit 1; }
[[ ! -L "$APP" && ! -L "$WEB" ]] || { echo 'Inspect symlinked deployment directories before deployment.' >&2; exit 1; }
"$PHP_BIN" -r 'if(PHP_VERSION_ID<80200||PHP_VERSION_ID>=80300){fwrite(STDERR,"PHP 8.2 is required for this deployment.\n");exit(1);}foreach(["sodium","dom","fileinfo","pdo_mysql"]as$e)if(!extension_loaded($e)){fwrite(STDERR,"Missing PHP extension: ".$e."\n");exit(1);}'
command -v rsync >/dev/null
[[ -f "$APP/.env" ]] || { echo 'Provision the private production .env first; deployment will never create or replace it.' >&2; exit 1; }
mkdir -p "$DOMAIN/.galaxy-deploy"
chmod 700 "$DOMAIN/.galaxy-deploy"
mkdir "$DOMAIN/.galaxy-deploy/active.lock" 2>/dev/null || { echo 'Another deployment is running.' >&2; exit 1; }
trap 'rmdir "$DOMAIN/.galaxy-deploy/active.lock" 2>/dev/null || true' EXIT
ENV_HASH=$("$PHP_BIN" -r 'echo hash_file("sha256",$argv[1]);' "$APP/.env")
# A previous installation must have valid production settings. Never rewrite them.
if [[ -f "$APP/vendor/autoload.php" ]]; then
  "$PHP_BIN" "$SOURCE/scripts/validate-production-env.php" "$APP"
fi
BACKUP="$DOMAIN/.galaxy-deploy/backups/$(date -u +%Y%m%dT%H%M%SZ)-$$"
mkdir -p "$BACKUP"
chmod 700 "$BACKUP"
# Private rollback copies; databases and runtime directories are not rewritten.
rsync -a --exclude='/.git/' --exclude='/vendor/' --exclude='/storage/' --exclude='/public/uploads/' --exclude='/public/upload/' --exclude='/public/storage/' "$APP/" "$BACKUP/application/"
rsync -a --exclude='/uploads/' --exclude='/upload/' --exclude='/storage/' "$WEB/" "$BACKUP/web/"
# Copy uploads back to the private public tree before publishing. Never delete user files.
mkdir -p "$APP/public" "$WEB"
for entry in uploads upload storage; do
  if [[ -L "$WEB/$entry" ]]; then
    [[ "$entry" == storage ]] || { echo 'Unexpected symlink in public uploads.' >&2; exit 1; }
  elif [[ "$entry" == storage && ( -d "$WEB/storage/framework" || -d "$WEB/storage/logs" ) ]]; then
    mv "$WEB/storage" "$BACKUP/private-storage"
  elif [[ -d "$WEB/$entry" ]]; then
    mkdir -p "$APP/public/$entry"
    rsync -a "$WEB/$entry/" "$APP/public/$entry/"
  fi
done
# Remove private remnants from the web root into a private backup, never delete them.
for entry in .env .env.example .env.backup .git app bootstrap config database resources routes vendor node_modules artisan composer.json composer.lock package.json package-lock.json phpunit.xml webpack.mix.js tests docs scripts README.md LICENSE .editorconfig .gitattributes .gitignore .php-version .rnd; do
  if [[ -e "$WEB/$entry" || -L "$WEB/$entry" ]]; then mv "$WEB/$entry" "$BACKUP/$entry"; fi
done
if [[ -d "$WEB/public" && ! -L "$WEB/public" ]]; then
  for entry in uploads upload; do
    if [[ -d "$WEB/public/$entry" && ! -L "$WEB/public/$entry" ]]; then mkdir -p "$APP/public/$entry"; rsync -a "$WEB/public/$entry/" "$APP/public/$entry/"; fi
  done
  mv "$WEB/public" "$BACKUP/nested-public"
fi
cp -p "$WEB/index.php" "$BACKUP/index.php" 2>/dev/null || true
cp -p "$WEB/.htaccess" "$BACKUP/web.htaccess" 2>/dev/null || true
# Synchronize code only. Runtime configuration, dependencies, caches and all public files survive.
rsync -a --delete --exclude='/.git/' --exclude='/.github/' --exclude='/.env*' --exclude='/vendor/' --exclude='/node_modules/' --exclude='/storage/' --exclude='/bootstrap/cache/' --exclude='/public/' "$SOURCE/" "$APP/"
mkdir -p "$APP/bootstrap/cache" "$APP/storage/framework/cache/data" "$APP/storage/framework/sessions" "$APP/storage/framework/views" "$APP/storage/logs"
rsync -a --exclude='/uploads/' --exclude='/upload/' --exclude='/storage/' "$SOURCE/public/" "$APP/public/"
# Avoid serving a stale configuration during Composer's package discovery.
rm -f "$APP/bootstrap/cache/config.php"
(cd "$APP" && APP_ENV=production APP_DEBUG=false WAREHOUSES_ENABLED=false "$PHP_BIN" "$COMPOSER_BIN" install --no-dev --prefer-dist --optimize-autoloader --no-interaction)
"$PHP_BIN" "$SOURCE/scripts/validate-production-env.php" "$APP"
# Fail before publishing if the real assets are missing; no placeholders.
"$PHP_BIN" "$SOURCE/scripts/verify-public-assets.php" "$APP/public"
# No --delete: preserve uploaded files even outside the conventional uploads folders.
rsync -a --exclude='/.env*' --exclude='/storage/' "$APP/public/" "$WEB/"
mkdir -p "$APP/storage/app/public"
if [[ ! -e "$WEB/storage" && ! -L "$WEB/storage" ]]; then ln -s "$APP/storage/app/public" "$WEB/storage"; fi
find "$WEB" -type d -exec chmod 755 {} +
find "$WEB" -type f -exec chmod 644 {} +
chmod 600 "$APP/.env"
chmod -R u+rwX "$APP/storage" "$APP/bootstrap/cache"
(cd "$APP" && "$PHP_BIN" artisan view:clear && "$PHP_BIN" artisan config:cache)
[[ "$ENV_HASH" == "$("$PHP_BIN" -r 'echo hash_file("sha256",$argv[1]);' "$APP/.env")" ]] || { echo 'Production .env unexpectedly changed.' >&2; exit 1; }
"$PHP_BIN" "$SOURCE/scripts/verify-deployed-layout.php" "$DOMAIN"
if [[ -n "$SITE_URL" ]]; then "$PHP_BIN" "$SOURCE/scripts/verify-deployment-http.php" "$SITE_URL" "$WEB"; fi
echo 'Deployment complete. Production configuration, application key, uploads and database records were not replaced.'
