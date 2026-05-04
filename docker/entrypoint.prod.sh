#!/bin/sh
set -eu

cd /app
echo "Starting production entrypoint..."

wait_for_db() {
  echo "Waiting for database to be ready..."

  max_attempts="${DB_READY_MAX_ATTEMPTS:-30}"
  sleep_seconds="${DB_READY_SLEEP_SECONDS:-2}"
  attempt=1

  while [ "$attempt" -le "$max_attempts" ]; do
    if php -r '
      $host = getenv("DB_HOST");
      $port = getenv("DB_PORT") ?: 5432;
      $db   = getenv("DB_DATABASE");
      $user = getenv("DB_USERNAME");
      $pass = getenv("DB_PASSWORD");
      $ssl  = getenv("DB_SSLMODE") ?: null;

      $dsn = "pgsql:host={$host};port={$port};dbname={$db}";
      if ($ssl) {
          $dsn .= ";sslmode={$ssl}";
      }

      try {
          new PDO($dsn, $user, $pass, [
              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
              PDO::ATTR_TIMEOUT => 5,
          ]);
          exit(0);
      } catch (Throwable $e) {
          fwrite(STDERR, $e->getMessage() . PHP_EOL);
          exit(1);
      }
    '; then
      echo "Database is ready."
      return 0
    fi

    echo "DB not ready yet (attempt ${attempt}/${max_attempts}). Retrying in ${sleep_seconds}s..."
    attempt=$((attempt + 1))
    sleep "$sleep_seconds"
  done

  echo "Database did not become ready in time."
  return 1
}

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is missing."
  echo "Set APP_KEY in Render for a persistent application key."
  export APP_KEY="$(php artisan key:generate --show --no-interaction)"
  echo "Generated a temporary APP_KEY for this container."
fi

php artisan package:discover --ansi --no-interaction

wait_for_db

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "Running migrations..."
  php artisan migrate --force --no-interaction
fi

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
  echo "Running seeders..."
  php artisan db:seed --force --no-interaction
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
