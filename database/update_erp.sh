#!/usr/bin/env bash
set -euo pipefail

DB_NAME="${1:-tulista}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

mysql_exec() {
  local sql="$1"
  if [[ -n "${DB_PASS}" ]]; then
    MYSQL_PWD="${DB_PASS}" mysql -N -s -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" -e "${sql}"
  else
    mysql -N -s -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" -e "${sql}"
  fi
}

run_file() {
  local file="$1"
  if [[ -n "${DB_PASS}" ]]; then
    MYSQL_PWD="${DB_PASS}" mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" < "${file}"
  else
    mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" < "${file}"
  fi
}

mysql_exec "
CREATE TABLE IF NOT EXISTS schema_migrations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  migration VARCHAR(255) NOT NULL UNIQUE,
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);"

while IFS= read -r migration; do
  base_name="$(basename "${migration}")"
  already_applied="$(mysql_exec "SELECT COUNT(*) FROM schema_migrations WHERE migration = '${base_name}'")"

  if [[ "${already_applied}" == "0" ]]; then
    echo "==> Ejecutando ${migration}"
    run_file "${migration}"
    mysql_exec "INSERT INTO schema_migrations (migration) VALUES ('${base_name}')"
  else
    echo "==> Omitiendo ${migration} (ya aplicada)"
  fi
done < <(find database/migrations -maxdepth 1 -type f -name '*.sql' | sort)

echo "Migraciones ERP aplicadas correctamente en ${DB_NAME}."
