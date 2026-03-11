# Base de datos ERP (Librería Mayorista)

- `schema.sql`: definición completa para instalación nueva.
- `migrations/001_create_productos_module.sql`: módulo productos inicial.
- `migrations/002_add_unique_index_to_productos_sku.sql`: índice único SKU.
- `migrations/003_create_usuarios_clientes_cotizaciones.sql`: usuarios, clientes, cotizaciones.
- `migrations/004_seed_super_admin_usuario.sql`: super administrador por defecto.
- `migrations/005_expand_erp_core.sql`: expansión ERP (pedidos, comisiones, inventario, auditoría, mantenedores y configuración).
- `migrations/006_add_usuario_profile_fields.sql`: agrega teléfono, dirección, cargo y notas en usuarios.
- `migrations/010_create_proveedores_module.sql`: crea módulo de proveedores con permisos por rol.

## Notas de estructura

- `usuarios` soporta roles (`admin`, `supervisor`, `vendedor`, `bodega`) y comisión por vendedor.
- `clientes` permite login con `rut + password` y acceso por `token_acceso`.
- `cotizaciones` registra responsable (`usuario_id`) y estados de ciclo comercial.
- `pedidos` concentra operación logística y venta.
- `ventas_resumen` y `comisiones` habilitan dashboard financiero.
- `movimientos_stock` y `log_sistema` entregan trazabilidad operativa.

## Instalación completa

```bash
mysql -u root -p < database/schema.sql
```

## Actualización incremental

```bash
mysql -u root -p tulista < database/migrations/003_create_usuarios_clientes_cotizaciones.sql
mysql -u root -p tulista < database/migrations/004_seed_super_admin_usuario.sql
mysql -u root -p tulista < database/migrations/005_expand_erp_core.sql
mysql -u root -p tulista < database/migrations/006_add_usuario_profile_fields.sql
mysql -u root -p tulista < database/migrations/010_create_proveedores_module.sql
```
