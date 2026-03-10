# Base de datos del módulo Productos

- `schema.sql`: definición completa de la base de datos y tablas para un entorno nuevo.
- `migrations/001_create_productos_module.sql`: creación inicial de tablas del módulo.
- `migrations/002_add_unique_index_to_productos_sku.sql`: actualización para índice único de SKU.

## Ejecución

```bash
mysql -u root -p < database/schema.sql
```

Luego, en actualizaciones:

```bash
mysql -u root -p tulista < database/migrations/002_add_unique_index_to_productos_sku.sql
```
