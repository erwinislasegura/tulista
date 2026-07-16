<?php
require_once __DIR__ . '/public-path.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

function paginaProductImage(string $category): string
{
    $normalized = strtolower($category);
    if (str_contains($normalized, 'oficina')) {
        return 'pagina/assets/images/prod-oficina.png';
    }
    if (str_contains($normalized, 'arte') || str_contains($normalized, 'manual')) {
        return 'pagina/assets/images/prod-pinturas.png';
    }
    if (str_contains($normalized, 'papel')) {
        return 'pagina/assets/images/prod-etiquetas.png';
    }
    if (str_contains($normalized, 'escrit')) {
        return 'pagina/assets/images/prod-lapices.png';
    }
    if (str_contains($normalized, 'libr')) {
        return 'pagina/assets/images/prod-libros.png';
    }
    if (str_contains($normalized, 'mochil')) {
        return 'pagina/assets/images/prod-mochila.png';
    }
    return 'pagina/assets/images/prod-kit.png';
}

function paginaCategoryImage(string $category): string
{
    return paginaProductImage($category);
}

$publicCategories = [];
$publicProducts = [];
$publicCatalogLoaded = false;

try {
    $categoryModel = new CategoryModel();
    $productModel = new ProductModel();
    $publicCategories = $categoryModel->publicCatalog();
    $publicProducts = array_map(static function (array $product): array {
        $category = (string) ($product['categoria'] ?? 'General');
        $price = (int) round((float) ($product['precio_venta_total'] ?? 0));
        $details = [];
        if (!empty($product['sku'])) {
            $details[] = 'SKU: ' . $product['sku'];
        }
        if (!empty($product['marca'])) {
            $details[] = 'Marca: ' . $product['marca'];
        }
        if (!empty($product['unidad'])) {
            $details[] = 'Unidad: ' . $product['unidad'];
        }

        return [
            'id' => (int) $product['id'],
            'name' => (string) $product['nombre'],
            'cat' => $category,
            'price' => $price,
            'old' => 0,
            'img' => !empty($product['imagen_principal']) ? ltrim((string) $product['imagen_principal'], '/') : paginaProductImage($category),
            'tag' => ((float) ($product['existencia'] ?? 0)) > 0 ? 'Stock' : 'Consultar',
            'desc' => $details ? implode(' · ', $details) : 'Producto disponible para cotización.',
        ];
    }, $productModel->publicCatalog());
    $publicCatalogLoaded = true;
} catch (Throwable $e) {
    error_log('[pagina/index.php] No se pudo cargar el catálogo público: ' . $e->getMessage());
}

$publicCategoryNames = array_values(array_map(static fn (array $category): string => (string) $category['nombre'], $publicCategories));
?>
<!doctype html>
<html lang="es-CL">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?= htmlspecialchars(paginaBasePath(), ENT_QUOTES, 'UTF-8') ?>">
  <title>Tu Lista | Tienda escolar y oficina para familias, colegios y empresas</title>
  <meta name="description" content="Tienda formal de útiles escolares, oficina, papelería y arte. Compra por unidad, listas escolares, volumen para empresas y cotizaciones por WhatsApp.">
  <link rel="stylesheet" href="pagina/assets/css/styles.css">
</head>
<body>
  <div class="topbar">
    <div class="container">
      <span>🚚 Despacho y retiro según disponibilidad</span>
      <span>💬 Cotización por WhatsApp · listas escolares · empresas · mayoristas</span>
    </div>
  </div>
  <header class="header">
    <div class="container header-main">
      <a class="brand" href=""><img src="pagina/assets/images/logo.png" alt="Tu Lista"></a>
      <form class="searchbox" action="#productos">
        <input id="globalSearch" type="search" placeholder="Buscar cuadernos, resmas, témperas, lápices...">
        <button type="submit">⌕</button>
      </form>
      <div class="header-actions">
        <a class="btn ghost" href="cotizador-lista">Enviar lista</a>
        <button class="icon-btn" id="cartOpen" type="button">🛒 Carrito <span class="cart-count" id="cartCount">0</span></button>
        <button class="icon-btn mobile-toggle" id="mobileMenuToggle" type="button">☰</button>
      </div>
    </div>
    <nav class="nav">
      <div class="container">
        <div class="nav-left">
          <div class="mega-wrap">
            <button class="mega-trigger" id="megaTrigger" type="button">☰ Categorías</button>
            <div class="mega-menu" id="megaMenu">
              <div class="mega-col"><h4>Escolar</h4><a href="#productos">Cuadernos</a><a href="#productos">Reglas y geometría</a><a href="#productos">Mochilas y estuches</a><a href="cotizador-lista">Cotizar lista escolar</a></div>
              <div class="mega-col"><h4>Arte y manualidades</h4><a href="#productos">Témperas y pinturas</a><a href="#productos">Goma eva</a><a href="#productos">Cartulinas</a><a href="#productos">Material creativo</a></div>
              <div class="mega-col"><h4>Oficina</h4><a href="#productos">Resmas</a><a href="#productos">Carpetas y archivadores</a><a href="#productos">Papelería</a><a href="#productos">Escritorio</a></div>
              <div class="mega-col"><h4>Compra rápida</h4><a href="cotizador-lista">Enviar una lista</a><a href="contacto">Hablar por WhatsApp</a><a href="#mayoristas">Precios mayoristas</a><a href="sabias-que">Preguntas frecuentes</a></div>
            </div>
          </div>
          <a href="" data-nav class="active">Inicio</a><a href="nosotros" data-nav class="">Nosotros</a><a href="cotizador-lista" data-nav class="">Cotizador de lista</a><a href="sabias-que" data-nav class="">Sabías que</a><a href="contacto" data-nav class="">Contacto</a>
        </div>
        <div class="nav-note">Compra por unidad, por lista o por volumen</div>
      </div>
    </nav>
  </header>

<main class="storefront-main">
  <section class="hero-v3" data-parallax>
    <div class="hero-v3-orbit orbit-a"></div><div class="hero-v3-orbit orbit-b"></div>
    <div class="container hero-v3-grid">
      <div class="hero-v3-copy"><span class="kicker">Escolar · arte · oficina</span><h1>Todo lo que tu día necesita, <em>en una sola lista.</em></h1><p>Desde el primer cuaderno hasta la oficina completa. Compra por producto, envíanos tu lista o cotiza por volumen.</p><div class="hero-actions"><a class="btn orange" href="#productos">Explorar productos →</a><a class="btn ghost" href="cotizador-lista">Cotizar mi lista</a></div><div class="hero-v3-proof"><span>✓ Despacho coordinado</span><span>✓ Atención real</span><span>✓ Precios por volumen</span></div></div>
      <div class="hero-v3-collage"><div class="collage-shape"></div><img class="bag" src="sites/tulista-store/public/images/prod-mochila.png" alt="Mochila escolar"><img class="books" src="sites/tulista-store/public/images/prod-cuadernos.png" alt="Cuadernos"><img class="pencils" src="sites/tulista-store/public/images/prod-lapices.png" alt="Lápices de colores"><div class="collage-label"><strong>Vuelta a clases</strong><span>Todo listo, sin vueltas</span></div></div>
    </div>
  </section>
  <div class="color-ribbon"><span>Cuadernos</span><i>✦</i><span>Arte y color</span><i>✦</i><span>Oficina</span><i>✦</i><span>Listas escolares</span><i>✦</i><span>Mayoristas</span></div>

  <section class="seo-commerce-section">
    <div class="container seo-commerce-grid">
      <div class="seo-commerce-image"><img src="assets/source/images/24.png" alt="Útiles escolares, papelería y productos de oficina"></div>
      <div><span class="kicker">Una compra, múltiples soluciones</span><h2>Útiles escolares y de oficina para cada etapa del año.</h2><p>En Tu Lista encuentras cuadernos, lápices, materiales de arte, resmas, carpetas, etiquetas y kits escolares. Reunimos productos esenciales para familias, colegios, empresas y librerías que necesitan comprar de forma simple y ordenada.</p><ul><li><strong>Temporada escolar:</strong> productos por unidad y listas completas.</li><li><strong>Oficinas y empresas:</strong> reposición frecuente y facturación.</li><li><strong>Mayoristas:</strong> atención comercial y precios por volumen.</li></ul><a class="btn orange" href="#productos">Descubrir el catálogo</a></div>
    </div>
  </section>

  <section class="how-commerce-section">
    <div class="container"><div class="section-head"><div><span class="kicker">Fácil de principio a fin</span><h2 class="section-title">Tu pedido en tres pasos.</h2></div></div><div class="how-commerce-grid"><article><b>1</b><h3>Elige o sube tu lista</h3><p>Compra desde el catálogo o envíanos el documento completo.</p></article><article><b>2</b><h3>Revisamos disponibilidad</h3><p>Confirmamos productos, cantidades, alternativas y despacho.</p></article><article><b>3</b><h3>Recibe tu pedido</h3><p>Coordinamos la entrega para que tengas todo a tiempo.</p></article></div></div>
  </section>

  <section class="store-band" aria-label="Beneficios de tienda">
    <div class="container store-band-grid">
      <div><strong>Compra formal</strong><span>Precios visibles, cantidades y carrito.</span></div>
      <div><strong>Cotización guiada</strong><span>Para listas escolares y pedidos grandes.</span></div>
      <div><strong>Canal empresas</strong><span>Reposición de oficina e instituciones.</span></div>
      <div><strong>Mayoristas</strong><span>Librerías, comercio y compras recurrentes.</span></div>
    </div>
  </section>

  <section class="worlds-v3">
    <div class="container"><div class="section-head"><div><span class="kicker">Compra según lo que necesitas</span><h2 class="section-title">Tres mundos, una sola tienda.</h2></div><p class="section-copy">Productos elegidos para estudiar, crear y trabajar mejor.</p></div><div class="worlds-v3-grid"><article data-parallax><img src="sites/tulista-store/public/images/hero-products.jpg" alt="Útiles escolares"><div><span>01 / Escolar</span><h3>Todo para volver a clases</h3><p>Cuadernos, mochilas, geometría y kits listos.</p><a href="#productos">Ver escolares →</a></div></article><article data-parallax><img src="sites/tulista-store/public/images/prod-pinturas.png" alt="Materiales de arte"><div><span>02 / Creatividad</span><h3>Color para grandes ideas</h3><p>Témperas, lápices, papeles y manualidades.</p><a href="#productos">Explorar arte →</a></div></article><article data-parallax><img src="sites/tulista-store/public/images/prod-oficina.png" alt="Productos de oficina"><div><span>03 / Oficina</span><h3>Orden para hacer más</h3><p>Resmas, carpetas y esenciales de escritorio.</p><a href="#productos">Equipar oficina →</a></div></article></div></div>
  </section>

  <section class="list-v3" data-parallax><img class="list-v3-bg" src="sites/tulista-store/public/images/hero-products.jpg" alt="Lista escolar y útiles"><div class="container"><div class="list-v3-panel"><span class="kicker">Tu lista, resuelta</span><h2>Sube una foto. Nosotros ordenamos el resto.</h2><p>Recibimos tu lista escolar en PDF, Excel o fotografía. Revisamos productos y cantidades para entregarte una cotización clara.</p><div><a class="btn orange" href="cotizador-lista">Enviar mi lista →</a><a href="#productos">Prefiero comprar yo</a></div></div><div class="list-v3-stat"><strong>1 archivo</strong><span>es todo lo que necesitas para comenzar</span></div></div></section>

  <section id="productos" class="catalog-section">
    <div class="container">
      <div class="section-head"><div><span class="kicker">Catálogo comercial</span><h2 class="section-title">Productos listos para agregar al pedido.</h2><p class="section-copy">Filtra por departamento, ajusta cantidades y arma un pedido con presentación de ecommerce profesional. Confirmas disponibilidad y despacho por WhatsApp.</p></div><button class="btn ghost" id="clearFilters" type="button">Limpiar filtros</button></div>
      <div class="shop-layout elevated-shop">
        <aside class="filters"><h3>Departamentos</h3><div id="sideCategories"></div><hr style="border:0;border-top:1px solid var(--line);margin:14px 0"><a class="filter-btn" href="cotizador-lista">Cotizar lista <span>rápido</span></a><a class="filter-btn" href="#mayoristas">Mayoristas <span>especial</span></a></aside>
        <div>
          <div class="toolbar"><div class="tabs" id="tabs"></div><select class="sort" id="sortSelect"><option value="featured">Destacados</option><option value="priceAsc">Menor precio</option><option value="priceDesc">Mayor precio</option><option value="name">Nombre A-Z</option></select></div>
          <p class="result-note" id="resultNote"></p>
          <div class="product-grid" id="productGrid"></div>
        </div>
      </div>
    </div>
  </section>

  <section id="mayoristas" class="section-bg wholesale-section" style="--section-bg:url('assets/source/images/21.png')">
    <div class="container banner-grid">
      <article class="banner big"><div class="banner-copy"><span class="kicker">Canal mayorista e institucional</span><h3>Abastecimiento serio para librerías, comercio, colegios y oficinas.</h3><p>Cotiza por volumen productos escolares, papelería, arte y oficina. Ideal para reposición, temporada escolar y compras institucionales con atención comercial.</p><a class="btn orange" href="cotizador-lista">Solicitar precio mayorista</a></div><img src="assets/source/images/22.png" alt="Compra mayorista"></article>
      <div style="display:grid;gap:16px">
        <article class="banner"><div class="banner-copy"><h3>Listas escolares</h3><p>Envíanos tu lista y te respondemos ordenado.</p><a class="btn ghost" href="cotizador-lista">Enviar lista</a></div><img src="assets/source/images/23.png" alt="Lista escolar"></article>
        <article class="banner"><div class="banner-copy"><h3>Arte y trabajos</h3><p>Pinturas, goma eva, papel y materiales creativos.</p><a class="btn ghost" href="#productos">Ver arte</a></div><img src="assets/source/images/28.png" alt="Arte escolar"></article>
      </div>
    </div>
  </section>

  <section class="final-commerce-cta"><div class="container"><div><span>¿Tienes una lista por resolver?</span><h2>Envíala hoy y te ayudamos a ordenar la compra.</h2></div><a class="btn orange" href="cotizador-lista">Cotizar mi lista →</a></div></section>
</main>

  <footer class="footer">
    <div class="container footer-grid">
      <div>
        <img src="pagina/assets/images/logo.png" alt="Tu Lista">
        <p>Ecommerce enfocado en útiles escolares, materiales de oficina, papelería, arte, listas escolares y atención a mayoristas.</p>
      </div>
      <div><h4>Tienda</h4><a href="#productos">Productos</a><a href="cotizador-lista">Cotizador de lista</a><a href="#mayoristas">Mayoristas</a></div>
      <div><h4>Empresa</h4><a href="nosotros">Nosotros</a><a href="contacto">Contacto</a><a href="sabias-que">Sabías que</a><a href="condiciones-politicas">Condiciones</a></div>
      <div><h4>Categorías</h4><a href="#productos">Escolar</a><a href="#productos">Oficina</a><a href="#productos">Arte</a><a href="#productos">Papelería</a></div>
      <div><h4>Atención</h4><a href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a><a href="mailto:contacto@tulista.cl">contacto@tulista.cl</a><a href="contacto">Formulario</a><a href="cotizador-lista">Subir lista</a><a href="dashboard.php">Panel administración</a></div>
    </div>
    <div class="container footer-bottom">
      <span>© 2026 Tu Lista. Todos los derechos reservados.</span>
      <span>Diseño preparado para ecommerce, cotización y venta por WhatsApp.</span>
    </div>
  </footer>
  <a class="whatsapp-float" href="https://wa.me/569XXXXXXXX" target="_blank" aria-label="WhatsApp">💬</a>
  <div class="drawer-overlay" id="drawerOverlay"></div>
  <aside class="cart-drawer" id="cartDrawer" aria-label="Carrito">
    <div class="cart-head"><h3>Tu pedido</h3><button class="cart-close" id="cartClose" type="button">×</button></div>
    <div class="cart-body" id="cartBody"></div>
    <div class="cart-foot"><div class="subtotal"><span>Subtotal estimado</span><strong id="cartSubtotal">$0</strong></div><a class="btn orange full" id="checkoutWhatsApp" target="_blank">Finalizar por WhatsApp</a><button class="btn ghost full" id="clearCart" type="button">Vaciar carrito</button></div>
  </aside>
  <div class="modal-overlay" id="modalOverlay"></div>
  <article class="modal" id="productModal"><button class="modal-close" id="modalClose" type="button">×</button><div class="modal-grid" id="modalContent"></div></article>
  <script>
    window.TULISTA_CATEGORIES = <?= $publicCatalogLoaded ? json_encode($publicCategoryNames, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'null' ?>;
    window.TULISTA_PRODUCTS = <?= $publicCatalogLoaded ? json_encode($publicProducts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'null' ?>;
  </script>
  <script src="pagina/assets/js/main.js"></script>
</body>
</html>
