#!/bin/sh
set -e

cd /app
echo "Starting production entrypoint..."

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ -z "${APP_KEY:-}" ]; then
  export APP_KEY="$(php artisan key:generate --show --no-interaction)"
  echo "APP_KEY was not provided. Generated a runtime APP_KEY for this container."
fi

php artisan package:discover --ansi --no-interaction

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
  php artisan db:seed --force --no-interaction
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
