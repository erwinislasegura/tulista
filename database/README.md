# Base de datos ERP (Librería Mayorista)

- `schema.sql`: definición completa para instalación nueva.
- `migrations/001_create_productos_module.sql`: módulo productos inicial.
- `migrations/002_add_unique_index_to_productos_sku.sql`: índice único SKU.
- `migrations/003_create_usuarios_clientes_cotizaciones.sql`: usuarios, clientes, cotizaciones.
- `migrations/004_seed_super_admin_usuario.sql`: super administrador por defecto.
- `migrations/005_expand_erp_core.sql`: expansión ERP (pedidos, comisiones, inventario, auditoría, mantenedores y configuración).
- `migrations/006_add_usuario_profile_fields.sql`: agrega teléfono, dirección, cargo y notas en usuarios.
- `migrations/010_create_proveedores_module.sql`: crea módulo de proveedores con permisos por rol.
- `migrations/012_portal_cliente_performance.sql`: índices para acelerar dashboard, cotizaciones y seguimiento en portal cliente.
- `migrations/013_cliente_portal_pedidos_historial_hardening.sql`: asegura columnas e índice de historial en `pedidos` para portal cliente.

## Notas de estructura

- `usuarios.rol` ahora es `VARCHAR(30)` y queda relacionado por FK con `roles_usuario(codigo)`.
- Los roles son mantenibles desde la UI y persisten en `roles_usuario`.
- Los permisos se guardan en `role_permissions` por rol.

## Instalación completa

```bash
mysql -u root -p < database/schema.sql
```

## Actualización incremental (recomendada)

```bash
mysql -u root -p tulista < database/migrations/003_create_usuarios_clientes_cotizaciones.sql
mysql -u root -p tulista < database/migrations/004_seed_super_admin_usuario.sql
mysql -u root -p tulista < database/migrations/005_expand_erp_core.sql
mysql -u root -p tulista < database/migrations/006_add_usuario_profile_fields.sql
mysql -u root -p tulista < database/migrations/010_create_proveedores_module.sql
mysql -u root -p tulista < database/migrations/012_portal_cliente_performance.sql
mysql -u root -p tulista < database/migrations/013_cliente_portal_pedidos_historial_hardening.sql
```

Variables opcionales:

```bash
DB_HOST=127.0.0.1 DB_PORT=3306 DB_USER=root DB_PASS=secret ./database/update_erp.sh tulista
```

El script `update_erp.sh` ahora registra migraciones aplicadas en `schema_migrations`, por lo que no vuelve a ejecutar una migración ya aplicada.
