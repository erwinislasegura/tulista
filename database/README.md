# Base de datos del sistema (Productos + Usuarios + Clientes + Cotizaciones)

- `schema.sql`: definición completa de la base de datos y tablas para un entorno nuevo.
- `migrations/001_create_productos_module.sql`: creación inicial de tablas de productos.
- `migrations/002_add_unique_index_to_productos_sku.sql`: actualización para índice único de SKU.
- `migrations/003_create_usuarios_clientes_cotizaciones.sql`: creación de usuarios, clientes, cotizaciones y detalle.
- `migrations/004_seed_super_admin_usuario.sql`: crea usuario super administrador por defecto (si no existe).

## Notas de estructura

- `usuarios` usa hash seguro en `password` (`password_hash`).
- `clientes` soporta acceso por login (`rut` + `password`) y por URL pública (`url_token`).
- `cotizaciones` se relaciona a `clientes` y `cotizacion_detalle` a `cotizaciones`/`productos`.

## Ejecución

Instalación completa:

```bash
mysql -u root -p < database/schema.sql
```

Actualización incremental:

```bash
mysql -u root -p tulista < database/migrations/003_create_usuarios_clientes_cotizaciones.sql
mysql -u root -p tulista < database/migrations/004_seed_super_admin_usuario.sql
```
