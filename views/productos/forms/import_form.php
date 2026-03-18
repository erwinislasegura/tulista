<form method="post" id="import-form" class="row g-3 tl-minimal-form">
    <input type="hidden" name="action" value="import_products">
    <input type="hidden" name="return_url" value="apps-productos-importacion.php">
    <input type="hidden" name="import_payload" id="import_payload">
    <div class="col-md-8"><label class="form-label">Planilla Excel/CSV</label><input class="form-control" id="excel-file" type="file" accept=".xlsx,.xls,.csv" required></div>
    <div class="col-md-4 d-flex align-items-end"><button class="btn btn-success w-100" type="submit">Importar planilla</button></div>
    <div class="col-12 d-flex flex-column flex-md-row gap-2 align-items-md-center">
        <button class="btn btn-outline-primary btn-sm" id="download-import-template" type="button">Descargar formato Excel</button>
        <small class="text-muted">Usa este formato para completar la información y subirla sin errores de columnas (campos obligatorios: Categoria y Nombre).</small>
    </div>
    <div class="col-12"><small class="text-muted">La importación notificará categorías y marcas faltantes en el sistema.</small></div>
</form>
