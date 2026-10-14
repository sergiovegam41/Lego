#!/bin/sh
# Runs pending database migrations before starting the main process.
# Migrations are idempotent, so restarts and redeploys are safe.
# Set LEGO_MIGRATE_ON_START=false to skip them.
set -e

if [ "${LEGO_MIGRATE_ON_START:-true}" = "true" ] && php /app/lego list --raw 2>/dev/null | grep -q '^migrate '; then
  php /app/lego migrate --no-interaction
fi

exec "$@"
