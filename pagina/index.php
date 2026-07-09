<?php
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

function paginaProductImage(string $category): string
{
    $normalized = strtolower($category);
    if (str_contains($normalized, 'oficina')) {
        return '/pagina/assets/images/prod-oficina.png';
    }
    if (str_contains($normalized, 'arte') || str_contains($normalized, 'manual')) {
        return '/pagina/assets/images/prod-pinturas.png';
    }
    if (str_contains($normalized, 'papel')) {
        return '/pagina/assets/images/prod-etiquetas.png';
    }
    if (str_contains($normalized, 'escrit')) {
        return '/pagina/assets/images/prod-lapices.png';
    }
    if (str_contains($normalized, 'libr')) {
        return '/pagina/assets/images/prod-libros.png';
    }
    if (str_contains($normalized, 'mochil')) {
        return '/pagina/assets/images/prod-mochila.png';
    }
    return '/pagina/assets/images/prod-kit.png';
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
            'img' => !empty($product['imagen_principal']) ? '../' . ltrim((string) $product['imagen_principal'], '/') : paginaProductImage($category),
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
  <title>Tu Lista | Útiles escolares, oficina y listas escolares</title>
  <meta name="description" content="Ecommerce de útiles escolares, materiales de oficina, papelería, arte y cotización de listas escolares.">
  <link rel="stylesheet" href="/pagina/assets/css/styles.css">
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
      <a class="brand" href="/"><img src="/pagina/assets/images/logo.png" alt="Tu Lista"></a>
      <form class="searchbox" action="/#productos">
        <input id="globalSearch" type="search" placeholder="Buscar cuadernos, resmas, témperas, lápices...">
        <button type="submit">⌕</button>
      </form>
      <div class="header-actions">
        <a class="btn ghost" href="/cotizador-lista">Enviar lista</a>
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
              <div class="mega-col"><h4>Escolar</h4><a href="/#productos">Cuadernos</a><a href="/#productos">Reglas y geometría</a><a href="/#productos">Mochilas y estuches</a><a href="/cotizador-lista">Cotizar lista escolar</a></div>
              <div class="mega-col"><h4>Arte y manualidades</h4><a href="/#productos">Témperas y pinturas</a><a href="/#productos">Goma eva</a><a href="/#productos">Cartulinas</a><a href="/#productos">Material creativo</a></div>
              <div class="mega-col"><h4>Oficina</h4><a href="/#productos">Resmas</a><a href="/#productos">Carpetas y archivadores</a><a href="/#productos">Papelería</a><a href="/#productos">Escritorio</a></div>
              <div class="mega-col"><h4>Compra rápida</h4><a href="/cotizador-lista">Enviar una lista</a><a href="/contacto">Hablar por WhatsApp</a><a href="/#mayoristas">Precios mayoristas</a><a href="/sabias-que">Preguntas frecuentes</a></div>
            </div>
          </div>
          <a href="/" data-nav class="active">Inicio</a><a href="/nosotros" data-nav class="">Nosotros</a><a href="/cotizador-lista" data-nav class="">Cotizador de lista</a><a href="/sabias-que" data-nav class="">Sabías que</a><a href="/contacto" data-nav class="">Contacto</a>
        </div>
        <div class="nav-note">Compra por unidad, por lista o por volumen</div>
      </div>
    </nav>
  </header>

<main>
  <section class="hero">
    <div class="hero-bg" id="heroParallax" aria-hidden="true">
      <?php for ($i = 1; $i <= 31; $i++): ?>
        <div class="hero-slide<?= $i === 1 ? ' is-active' : '' ?>" style="background-image:url('../assets/source/images/<?= $i ?>.png')"></div>
      <?php endfor; ?>
    </div>
    <div class="container">
      <div>
        <span class="eyebrow"><b>Compra simple</b> útiles escolares, arte y oficina</span>
        <h1>Útiles escolares, arte y oficina en un solo lugar.</h1>
        <p>Encuentra cuadernos, pinturas, lápices, papelería y productos de oficina. Compra por unidad, por lista escolar o por volumen.</p>
        <div class="hero-actions"><a class="btn orange" href="#productos">Ver productos</a><a class="btn ghost" href="/cotizador-lista">Enviar mi lista</a><a class="btn primary" href="#mayoristas">Comprar por mayor</a></div>
        <div class="pills"><span><i></i> Listas escolares</span><span><i></i> Oficina y empresas</span><span><i></i> Mayoristas</span><span><i></i> WhatsApp directo</span></div>
      </div>
      <aside class="hero-panel">
        <div class="panel-head"><h2>Compra rápida</h2><p>Accede por tipo de necesidad y arma tu pedido sin perder tiempo.</p></div>
        <div class="quick-grid">
          <button class="quick-card" data-filter="Escolar"><img src="/pagina/assets/images/prod-kit.png" alt="Escolar"><span><strong>Escolar</strong><span>Cuadernos y kits</span></span></button>
          <button class="quick-card" data-filter="Oficina"><img src="/pagina/assets/images/prod-oficina.png" alt="Oficina"><span><strong>Oficina</strong><span>Resmas y archivo</span></span></button>
          <button class="quick-card" data-filter="Arte"><img src="/pagina/assets/images/prod-pinturas.png" alt="Arte"><span><strong>Arte</strong><span>Pinturas y trabajos</span></span></button>
          <button class="quick-card" onclick="location.href='/cotizador-lista'"><img src="/pagina/assets/images/quote-list.png" alt="Cotizar"><span><strong>Cotizar lista</strong><span>Envíanos el pedido</span></span></button>
        </div>
      </aside>
    </div>
  </section>
  <div class="trust section-bg" style="--section-bg:url('../assets/source/images/4.png')">
    <div class="container trust-grid">
      <div class="trust-item"><div class="trust-icon">✓</div><div><strong>Fácil de comprar</strong><span>Cantidad por producto.</span></div></div>
      <div class="trust-item"><div class="trust-icon">💬</div><div><strong>WhatsApp directo</strong><span>Pedido o cotización.</span></div></div>
      <div class="trust-item"><div class="trust-icon">📦</div><div><strong>Mayoristas</strong><span>Librerías y comercio.</span></div></div>
      <div class="trust-item"><div class="trust-icon">🎒</div><div><strong>Listas escolares</strong><span>Envíala y cotiza.</span></div></div>
    </div>
  </div>
  <section id="productos" class="section-bg" style="--section-bg:url('../assets/source/images/14.png')">
    <div class="container">
      <div class="section-head"><div><span class="kicker">Más pedidos</span><h2 class="section-title">Productos listos para agregar al pedido.</h2><p class="section-copy">Selecciona la cantidad antes de agregar al carrito. Después finalizas por WhatsApp.</p></div><button class="btn ghost" id="clearFilters" type="button">Limpiar filtros</button></div>
      <div class="shop-layout">
        <aside class="filters"><h3>Departamentos</h3><div id="sideCategories"></div><hr style="border:0;border-top:1px solid var(--line);margin:14px 0"><a class="filter-btn" href="/cotizador-lista">Cotizar lista <span>rápido</span></a><a class="filter-btn" href="#mayoristas">Mayoristas <span>especial</span></a></aside>
        <div>
          <div class="toolbar"><div class="tabs" id="tabs"></div><select class="sort" id="sortSelect"><option value="featured">Destacados</option><option value="priceAsc">Menor precio</option><option value="priceDesc">Mayor precio</option><option value="name">Nombre A-Z</option></select></div>
          <p class="result-note" id="resultNote"></p>
          <div class="product-grid" id="productGrid"></div>
        </div>
      </div>
    </div>
  </section>
  <section id="mayoristas" class="section-bg" style="--section-bg:url('../assets/source/images/21.png')">
    <div class="container banner-grid">
      <article class="banner big"><div class="banner-copy"><span class="kicker">Mayoristas</span><h3>Precios especiales para librerías, comercio, colegios y oficinas.</h3><p>Cotiza por volumen productos escolares, papelería, arte y oficina. Ideal para reposición, temporada escolar y compras institucionales.</p><a class="btn orange" href="/cotizador-lista">Solicitar precio mayorista</a></div><img src="../assets/source/images/22.png" alt="Compra mayorista"></article>
      <div style="display:grid;gap:16px">
        <article class="banner"><div class="banner-copy"><h3>Listas escolares</h3><p>Envíanos tu lista y te respondemos ordenado.</p><a class="btn ghost" href="/cotizador-lista">Enviar lista</a></div><img src="../assets/source/images/23.png" alt="Lista escolar"></article>
        <article class="banner"><div class="banner-copy"><h3>Arte y trabajos</h3><p>Pinturas, goma eva, papel y materiales creativos.</p><a class="btn ghost" href="#productos">Ver arte</a></div><img src="../assets/source/images/28.png" alt="Arte escolar"></article>
      </div>
    </div>
  </section>
</main>

  <footer class="footer">
    <div class="container footer-grid">
      <div>
        <img src="/pagina/assets/images/logo.png" alt="Tu Lista">
        <p>Ecommerce enfocado en útiles escolares, materiales de oficina, papelería, arte, listas escolares y atención a mayoristas.</p>
      </div>
      <div><h4>Tienda</h4><a href="/#productos">Productos</a><a href="/cotizador-lista">Cotizador de lista</a><a href="/#mayoristas">Mayoristas</a></div>
      <div><h4>Empresa</h4><a href="/nosotros">Nosotros</a><a href="/contacto">Contacto</a><a href="/sabias-que">Sabías que</a><a href="/condiciones-politicas">Condiciones</a></div>
      <div><h4>Categorías</h4><a href="/#productos">Escolar</a><a href="/#productos">Oficina</a><a href="/#productos">Arte</a><a href="/#productos">Papelería</a></div>
      <div><h4>Atención</h4><a href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a><a href="mailto:contacto@tulista.cl">contacto@tulista.cl</a><a href="/contacto">Formulario</a><a href="/cotizador-lista">Subir lista</a><a href="../dashboard.php">Panel administración</a></div>
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
  <script src="/pagina/assets/js/main.js"></script>
</body>
</html>
