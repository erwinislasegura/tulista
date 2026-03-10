#!/usr/bin/env bash
set -euo pipefail

DB_NAME="${1:-tulista}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

run_migration() {
  local file="$1"
  echo "==> Ejecutando ${file}"
  if [[ -n "${DB_PASS}" ]]; then
    MYSQL_PWD="${DB_PASS}" mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" < "${file}"
  else
    mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" < "${file}"
  fi
}

while IFS= read -r migration; do
  run_migration "$migration"
done < <(find database/migrations -maxdepth 1 -type f -name '*.sql' | sort)

echo "Migraciones ERP aplicadas correctamente en ${DB_NAME}."
