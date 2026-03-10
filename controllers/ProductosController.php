<?php

require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BrandModel.php';
require_once __DIR__ . '/../models/UnitModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

class ProductosController
{
    private CategoryModel $categories;
    private BrandModel $brands;
    private UnitModel $units;
    private ProductModel $products;

    public function __construct()
    {
        $this->categories = new CategoryModel();
        $this->brands = new BrandModel();
        $this->units = new UnitModel();
        $this->products = new ProductModel();

        if (!isset($_SESSION['productos_flash'])) {
            $_SESSION['productos_flash'] = [];
        }
    }

    public function handleRequest(): array
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
                } elseif ($action === 'import_products') {
                    $this->importProducts();
                }
            } catch (Throwable $e) {
                $this->flash('danger', 'Error: ' . $e->getMessage());
            }

            header('Location: apps-productos.php' . $this->buildTabHash());
            exit;
        }

        $flash = $_SESSION['productos_flash'];
        $_SESSION['productos_flash'] = [];

        return [
            'categories' => $this->categories->all(),
            'brands' => $this->brands->all(),
            'units' => $this->units->all(),
            'products' => $this->products->all(),
            'flash' => $flash,
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

        $this->categories->create($name);
        $this->flash('success', 'Categoría creada correctamente.');
    }

    private function addBrand(): void
    {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flash('warning', 'Debes indicar el nombre de la marca.');
            return;
        }

        $this->brands->findOrCreate($name);
        $this->flash('success', 'Marca guardada correctamente.');
    }

    private function addUnit(): void
    {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flash('warning', 'Debes indicar la unidad de medida.');
            return;
        }

        $this->units->findOrCreate($name);
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

        $this->products->create($this->mapProductData($_POST));
        $this->flash('success', 'Producto guardado correctamente.');
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
        $imported = 0;

        foreach ($rows as $row) {
            $categoryName = trim((string) ($row['Categoria'] ?? ''));
            $name = trim((string) ($row['Nombre'] ?? ''));

            if ($categoryName === '' || $name === '') {
                continue;
            }

            $category = $this->categories->findByName($categoryName);
            if (!$category) {
                $missingCategories[$categoryName] = true;
                continue;
            }

            $brandId = $this->brands->findOrCreate((string) ($row['Marca'] ?? ''));
            $unitId = $this->units->findOrCreate((string) ($row['Unidad'] ?? ''));

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

        $message = "Importación finalizada. Productos importados: {$imported}.";
        if (!empty($missingCategories)) {
            $message .= ' Categorías faltantes: ' . implode(', ', array_keys($missingCategories)) . '.';
            $this->flash('warning', $message);
            return;
        }

        $this->flash('success', $message);
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

    private function buildTabHash(): string
    {
        $tab = trim($_POST['return_tab'] ?? '');
        return $tab !== '' ? '#' . $tab : '';
    }
}
