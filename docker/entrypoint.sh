#!/bin/sh
set -eu

mkdir -p \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs

chown -R www-data:www-data storage bootstrap/cache
php artisan storage:link --force >/dev/null

if [ "${1:-}" = "php" ]; then
  exec gosu www-data "$@"
fi

exec "$@"
