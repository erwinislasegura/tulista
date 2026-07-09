<?php

require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BrandModel.php';
require_once __DIR__ . '/../models/UnitModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';

class ProductosController
{
    private $categories ;
    private $brands ;
    private $units ;
    private $products ;

    public function __construct()
    {
        AuthService::startSession();
        AuthorizationService::requirePermission('productos.view');
        $this->categories = new CategoryModel();
        $this->brands = new BrandModel();
        $this->units = new UnitModel();
        $this->products = new ProductModel();

        if (!isset($_SESSION['productos_flash'])) {
            $_SESSION['productos_flash'] = [];
        }
        $_SESSION['productos_last'] = $_SESSION['productos_last'] ?? [];
    }

    public function handleRequest(string $section): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            try {
                if ($action === 'add_category') {
                    $this->addCategory();
                } elseif ($action === 'add_brand') {
                    $this->addBrand();
                } elseif ($action === 'add_unit') {
                    $this->addUnit();
                } elseif ($action === 'add_product') {
                    $this->addProduct();
                } elseif ($action === 'update_product') {
                    $this->updateProduct();
                } elseif ($action === 'delete_product') {
                    $this->deleteProduct();
                } elseif ($action === 'bulk_delete_products') {
                    $this->bulkDeleteProducts();
                } elseif ($action === 'bulk_update_product_category') {
                    $this->bulkUpdateProductCategory();
                } elseif ($action === 'import_products') {
                    $this->importProducts();
                }
            } catch (Throwable $e) {
                $this->flash('danger', 'Error: ' . $e->getMessage());
            }

            header('Location: ' . $this->buildReturnUrl($section));
            exit;
        }

        $flash = $_SESSION['productos_flash'];
        $_SESSION['productos_flash'] = [];
        $last = $_SESSION['productos_last'];
        $_SESSION['productos_last'] = [];

        $products = $this->products->all();

        return [
            'section' => $section,
            'categories' => $this->categories->all(),
            'brands' => $this->brands->all(),
            'units' => $this->units->all(),
            'products' => $products,
            'product_images' => $this->products->imagesByProductIds(array_column($products, 'id')),
            'flash' => $flash,
            'last' => $last,
        ];
    }

    private function addCategory(): void
    {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flash('warning', 'Debes indicar el nombre de la categoría.');
            return;
        }

        if ($this->categories->findByName($name)) {
            $this->flash('warning', 'La categoría ya existe.');
            return;
        }

        $categoryId = $this->categories->create($name);
        $_SESSION['productos_last']['categoria_id'] = $categoryId;
        $this->flash('success', 'Categoría creada correctamente.');
    }

    private function addBrand(): void
    {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flash('warning', 'Debes indicar el nombre de la marca.');
            return;
        }

        if ($this->brands->findByName($name)) {
            $this->flash('warning', 'La marca ya existe.');
            return;
        }

        $brandId = $this->brands->create($name);
        $_SESSION['productos_last']['marca_id'] = $brandId;
        $this->flash('success', 'Marca guardada correctamente.');
    }

    private function addUnit(): void
    {
        $description = trim($_POST['descripcion'] ?? '');
        $abbreviation = strtoupper(trim($_POST['abreviatura'] ?? ''));

        if ($description === '' || $abbreviation === '') {
            $this->flash('warning', 'Debes indicar descripción y abreviatura.');
            return;
        }

        if ($this->units->findByAbbreviation($abbreviation)) {
            $this->flash('warning', 'La abreviatura de unidad ya existe.');
            return;
        }

        $unitId = $this->units->create($description, $abbreviation);
        $_SESSION['productos_last']['unidad_id'] = $unitId;
        $this->flash('success', 'Unidad guardada correctamente.');
    }

    private function addProduct(): void
    {
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');

        if ($categoriaId <= 0 || $nombre === '') {
            $this->flash('warning', 'Categoría y nombre son obligatorios para el producto.');
            return;
        }

        $productId = $this->products->create($this->mapProductData($_POST));
        $images = $this->uploadedProductImages();
        if (!empty($images)) {
            $this->products->insertImages($productId, $images);
        }
        $this->flash('success', 'Producto guardado correctamente.');
    }

    private function updateProduct(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');

        if ($id <= 0) {
            $this->flash('danger', 'Producto inválido.');
            return;
        }

        if ($categoriaId <= 0 || $nombre === '') {
            $this->flash('warning', 'Categoría y nombre son obligatorios para el producto.');
            return;
        }

        $this->products->update($id, $this->mapProductData($_POST));
        $images = $this->uploadedProductImages();
        if (!empty($images)) {
            $oldPaths = $this->products->imagePaths($id);
            $this->products->replaceImages($id, $images);
            $this->deleteStoredFiles($oldPaths);
        } elseif (!empty($_POST['principal_image_id'])) {
            $this->products->setPrincipalImage($id, (int) $_POST['principal_image_id']);
        }
        $this->flash('success', 'Producto actualizado correctamente.');
    }

    private function deleteProduct(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('danger', 'Producto inválido.');
            return;
        }

        $paths = $this->products->imagePaths($id);
        $this->products->delete($id);
        $this->deleteStoredFiles($paths);
        $this->flash('success', 'Producto eliminado correctamente.');
    }


    private function bulkDeleteProducts(): void
    {
        $criteria = [
            'categoria_id' => (int) ($_POST['bulk_categoria_id'] ?? 0),
            'marca_id' => (int) ($_POST['bulk_marca_id'] ?? 0),
            'modelo' => trim((string) ($_POST['bulk_modelo'] ?? '')),
            'nombre' => trim((string) ($_POST['bulk_nombre'] ?? '')),
            'sku' => trim((string) ($_POST['bulk_sku'] ?? '')),
            'codigo_barras' => trim((string) ($_POST['bulk_codigo_barras'] ?? '')),
        ];
        $hasCriteria = array_filter($criteria, static fn ($value): bool => is_string($value) ? $value !== '' : (int) $value > 0);

        if (empty($hasCriteria)) {
            $this->flash('warning', 'Indica al menos un criterio para eliminar productos por lote.');
            return;
        }

        if (trim((string) ($_POST['bulk_confirm'] ?? '')) !== 'ELIMINAR') {
            $this->flash('warning', 'Para confirmar la eliminación por lote debes escribir ELIMINAR.');
            return;
        }

        $matches = $this->products->findForBulkDelete($criteria);
        if (empty($matches)) {
            $this->flash('info', 'No se encontraron productos que coincidan con los criterios indicados.');
            return;
        }

        $paths = $this->products->deleteMany(array_column($matches, 'id'));
        $this->deleteStoredFiles($paths);
        $this->flash('success', 'Eliminación por lote completada. Productos eliminados: ' . count($matches) . '.');
    }


    private function bulkUpdateProductCategory(): void
    {
        $newCategoryId = (int) ($_POST['bulk_new_categoria_id'] ?? 0);
        $criteria = [
            'categoria_id' => (int) ($_POST['bulk_filter_categoria_id'] ?? 0),
            'marca_id' => (int) ($_POST['bulk_filter_marca_id'] ?? 0),
            'modelo' => trim((string) ($_POST['bulk_filter_modelo'] ?? '')),
            'nombre' => trim((string) ($_POST['bulk_filter_nombre'] ?? '')),
            'sku' => trim((string) ($_POST['bulk_filter_sku'] ?? '')),
            'codigo_barras' => trim((string) ($_POST['bulk_filter_codigo_barras'] ?? '')),
        ];
        $hasCriteria = array_filter($criteria, static fn ($value): bool => is_string($value) ? $value !== '' : (int) $value > 0);

        if ($newCategoryId <= 0) {
            $this->flash('warning', 'Selecciona la categoría que quieres asignar a los productos por lote.');
            return;
        }

        if (empty($hasCriteria)) {
            $this->flash('warning', 'Indica al menos un criterio para seleccionar los productos que serán categorizados por lote.');
            return;
        }

        $matches = $this->products->findForBulkCategoryUpdate($criteria, $newCategoryId);
        if (empty($matches)) {
            $this->flash('info', 'No se encontraron productos pendientes de cambiar a la categoría seleccionada con los criterios indicados.');
            return;
        }

        $updated = $this->products->updateCategoryMany(array_column($matches, 'id'), $newCategoryId);
        $this->flash('success', 'Categoría asociada por lote. Productos actualizados: ' . $updated . '.');
    }

    private function importProducts(): void
    {
        $payload = trim($_POST['import_payload'] ?? '');
        if ($payload === '') {
            $this->flash('warning', 'No hay datos para importar.');
            return;
        }

        $rows = json_decode($payload, true);
        if (!is_array($rows)) {
            $this->flash('danger', 'El archivo no pudo procesarse.');
            return;
        }

        $missingCategories = [];
        $missingBrands = [];
        $missingUnits = [];
        $rowsWithMissingData = [];
        $createdBrands = [];
        $createMissingBrands = (int) ($_POST['create_missing_brands'] ?? 0) === 1;
        $imported = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $categoryName = trim((string) ($row['Categoria'] ?? ''));
            $brandName = trim((string) ($row['Marca'] ?? ''));
            $name = trim((string) ($row['Nombre'] ?? ''));

            $missingFields = [];
            if ($categoryName === '') {
                $missingFields[] = 'Categoria';
            }
            if ($name === '') {
                $missingFields[] = 'Nombre';
            }
            if (!empty($missingFields)) {
                $rowsWithMissingData[] = "fila {$rowNumber}: " . implode(', ', $missingFields);
                continue;
            }

            $category = $this->categories->findByName($categoryName);
            if (!$category) {
                $missingCategories[$categoryName] = true;
                continue;
            }

            $brandId = null;
            if ($brandName !== '') {
                $brand = $this->brands->findByName($brandName);
                if (!$brand) {
                    if ($createMissingBrands) {
                        $brandId = $this->brands->create($brandName);
                        $createdBrands[$brandName] = true;
                    } else {
                        $missingBrands[$brandName] = true;
                        continue;
                    }
                } else {
                    $brandId = (int) $brand['id'];
                }
            }

            $unitId = null;
            $abbreviation = strtoupper(trim((string) ($row['Unidad'] ?? '')));
            if ($abbreviation !== '') {
                $unit = $this->units->findByAbbreviation($abbreviation);
                if ($unit) {
                    $unitId = (int) $unit['id'];
                } else {
                    $missingUnits[$abbreviation] = true;
                }
            }

            $data = $this->mapProductData([
                'categoria_id' => $category['id'],
                'nombre' => $name,
                'sku' => $row['SKU'] ?? '',
                'marca_id' => $brandId,
                'modelo' => $row['Modelo'] ?? '',
                'unidad_id' => $unitId,
                'codigo_barras' => $row['Código de barras'] ?? '',
                'tipo_item' => $row['Producto / Servicio'] ?? '',
                'costo_neto' => $row['Costo neto'] ?? 0,
                'precio_venta_neto' => $row['Venta: Precio neto'] ?? 0,
                'precio_venta_total' => $row['Venta: Precio total'] ?? 0,
                'stock_minimo' => $row['Stock mínimo'] ?? 0,
                'comision_vendedor' => $row['Comisión vendedor'] ?? 0,
                'existencia' => $row['Existencia'] ?? 0,
            ]);

            $this->products->create($data);
            $imported++;
        }

        $messages = ["Importación finalizada. Productos importados: {$imported}."];
        if (!empty($missingCategories)) {
            $messages[] = 'Categorías faltantes: ' . implode(', ', array_keys($missingCategories)) . '.';
        }
        if (!empty($missingBrands)) {
            $messages[] = 'Marcas faltantes: ' . implode(', ', array_keys($missingBrands)) . '.';
        }
        if (!empty($createdBrands)) {
            $messages[] = 'Marcas creadas automáticamente: ' . implode(', ', array_keys($createdBrands)) . '.';
        }
        if (!empty($missingUnits)) {
            $messages[] = 'Unidades no encontradas (se importaron sin unidad): ' . implode(', ', array_keys($missingUnits)) . '.';
        }
        if (!empty($rowsWithMissingData)) {
            $messages[] = 'Filas con datos obligatorios faltantes: ' . implode('; ', $rowsWithMissingData) . '.';
        }

        $hasWarnings = !empty($missingCategories) || !empty($missingBrands) || !empty($missingUnits) || !empty($rowsWithMissingData);
        $this->flash($hasWarnings ? 'warning' : 'success', implode(' ', $messages));
    }

    private function uploadedProductImages(): array
    {
        if (empty($_FILES['product_images']) || !is_array($_FILES['product_images']['name'] ?? null)) {
            return [];
        }

        $principalIndex = (int) ($_POST['principal_image_index'] ?? 0);
        $files = $_FILES['product_images'];
        $images = [];
        $maxFiles = 3;
        $maxBytes = 8 * 1024 * 1024;
        $uploadDir = __DIR__ . '/../uploads/productos';

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            $this->flash('danger', 'No fue posible crear la carpeta de imágenes de productos. Revisa permisos de uploads/productos.');
            return [];
        }

        if (!is_writable($uploadDir)) {
            $this->flash('danger', 'La carpeta uploads/productos no tiene permisos de escritura.');
            return [];
        }

        foreach ($files['name'] as $index => $originalName) {
            if (count($images) >= $maxFiles) {
                $this->flash('warning', 'Solo se permiten hasta 3 fotos por producto. Se omitieron archivos adicionales.');
                break;
            }

            $originalName = (string) $originalName;
            $error = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                $this->flash('warning', $this->uploadErrorMessage($error, $originalName));
                continue;
            }

            $tmpName = (string) ($files['tmp_name'][$index] ?? '');
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                $this->flash('warning', 'No se pudo validar la carga temporal de ' . ($originalName ?: 'una imagen') . '. Intenta nuevamente.');
                continue;
            }

            $size = (int) ($files['size'][$index] ?? 0);
            if ($size <= 0 || $size > $maxBytes) {
                $this->flash('warning', 'La imagen ' . ($originalName ?: '') . ' supera el máximo permitido de 8 MB.');
                continue;
            }

            $imageInfo = @getimagesize($tmpName);
            $mime = (string) ($imageInfo['mime'] ?? mime_content_type($tmpName) ?: '');
            $extensions = [
                'image/jpeg' => 'jpg',
                'image/pjpeg' => 'jpg',
                'image/png' => 'png',
                'image/x-png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];

            if (!isset($extensions[$mime])) {
                $this->flash('warning', 'La imagen ' . ($originalName ?: '') . ' no tiene un formato válido. Usa JPG, PNG, WEBP o GIF.');
                continue;
            }

            $filename = bin2hex(random_bytes(10)) . '.' . $extensions[$mime];
            $target = $uploadDir . '/' . $filename;
            if (!move_uploaded_file($tmpName, $target)) {
                $this->flash('warning', 'No fue posible guardar ' . ($originalName ?: 'una imagen') . ' en uploads/productos. Revisa permisos de escritura.');
                continue;
            }

            @chmod($target, 0664);
            $images[] = [
                'ruta' => 'uploads/productos/' . $filename,
                'es_principal' => $index === $principalIndex,
            ];
        }

        if (!empty($images) && !array_filter($images, static fn (array $image): bool => !empty($image['es_principal']))) {
            $images[0]['es_principal'] = true;
        }

        return $images;
    }

    private function uploadErrorMessage(int $error, string $filename): string
    {
        $name = $filename !== '' ? '"' . $filename . '"' : 'la imagen';
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'No se cargó ' . $name . ' porque supera el tamaño permitido por el servidor. Máximo recomendado: 8 MB.',
            UPLOAD_ERR_PARTIAL => 'La carga de ' . $name . ' quedó incompleta. Intenta subirla nuevamente.',
            UPLOAD_ERR_NO_TMP_DIR => 'No se cargó ' . $name . ' porque el servidor no tiene carpeta temporal configurada.',
            UPLOAD_ERR_CANT_WRITE => 'No se cargó ' . $name . ' porque el servidor no pudo escribir el archivo temporal.',
            UPLOAD_ERR_EXTENSION => 'No se cargó ' . $name . ' porque una extensión de PHP bloqueó la carga.',
            default => 'No se cargó ' . $name . '. Código de error de carga: ' . $error . '.',
        };
    }

    private function deleteStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $fullPath = __DIR__ . '/../' . ltrim((string) $path, '/');
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    private function mapProductData(array $source): array
    {
        return [
            'categoria_id' => (int) ($source['categoria_id'] ?? 0),
            'nombre' => trim((string) ($source['nombre'] ?? '')),
            'sku' => trim((string) ($source['sku'] ?? '')),
            'marca_id' => !empty($source['marca_id']) ? (int) $source['marca_id'] : null,
            'modelo' => trim((string) ($source['modelo'] ?? '')),
            'unidad_id' => !empty($source['unidad_id']) ? (int) $source['unidad_id'] : null,
            'codigo_barras' => trim((string) ($source['codigo_barras'] ?? '')),
            'tipo_item' => trim((string) ($source['tipo_item'] ?? '')),
            'costo_neto' => $this->toDecimal($source['costo_neto'] ?? 0),
            'precio_venta_neto' => $this->toDecimal($source['precio_venta_neto'] ?? 0),
            'precio_venta_total' => $this->toDecimal($source['precio_venta_total'] ?? 0),
            'stock_minimo' => (int) ($source['stock_minimo'] ?? 0),
            'comision_vendedor' => $this->toDecimal($source['comision_vendedor'] ?? 0),
            'existencia' => (int) ($source['existencia'] ?? 0),
        ];
    }

    private function toDecimal($value): float
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['productos_flash'][] = ['type' => $type, 'message' => $message];
    }

    private function buildReturnUrl(string $fallback): string
    {
        $url = trim($_POST['return_url'] ?? '');
        return $url !== '' ? $url : $fallback;
    }
}
