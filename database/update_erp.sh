#!/usr/bin/env bash
set -euo pipefail

DB_NAME="${1:-tulista}"
DB_USER="${DB_USER:-root}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

run_migration() {
  local file="$1"
  echo "==> Ejecutando ${file}"
  mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p "${DB_NAME}" < "${file}"
}

run_migration "database/migrations/003_create_usuarios_clientes_cotizaciones.sql"
run_migration "database/migrations/004_seed_super_admin_usuario.sql"
run_migration "database/migrations/005_expand_erp_core.sql"

echo "Migraciones ERP aplicadas correctamente en ${DB_NAME}."
