#!/bin/sh
# Prepares the database before starting the main process. Both steps are
# idempotent, so restarts and redeploys are safe.
#
# - LEGO_MIGRATE_ON_START=false skips migrations.
# - LEGO_ADMIN_EMAIL creates the first administrator when no user exists.
#   Its password comes from LEGO_ADMIN_PASSWORD, or is generated and printed
#   once in the container log.
set -e

if [ "${LEGO_MIGRATE_ON_START:-true}" = "true" ]; then
  php /app/lego migrate --no-interaction
fi

if [ -n "${LEGO_ADMIN_EMAIL:-}" ]; then
  php /app/lego install --no-interaction --email="${LEGO_ADMIN_EMAIL}"
fi

exec "$@"
