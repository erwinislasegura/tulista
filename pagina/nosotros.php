<?php require_once __DIR__ . '/public-path.php'; ?>
<!doctype html>
<html lang="es-CL">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?= htmlspecialchars(paginaBasePath(), ENT_QUOTES, 'UTF-8') ?>">
  <title>Nosotros | Tu Lista</title>
  <meta name="description" content="Conoce Tu Lista, tienda enfocada en útiles escolares, oficina, papelería y cotización por volumen.">
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
          <a href="" data-nav class="">Inicio</a><a href="nosotros" data-nav class="active">Nosotros</a><a href="cotizador-lista" data-nav class="">Cotizador de lista</a><a href="sabias-que" data-nav class="">Sabías que</a><a href="contacto" data-nav class="">Contacto</a>
        </div>
        <div class="nav-note">Compra por unidad, por lista o por volumen</div>
      </div>
    </nav>
  </header>

<main>
  <section class="page-hero page-hero-bg" style="--section-bg:url('assets/source/images/2.png')">
    <div class="container">
      <div><div class="breadcrumb">Inicio / Nosotros</div><span class="kicker">Nosotros</span><h1>Una tienda pensada para resolver compras escolares y de oficina sin complicaciones.</h1><p>Tu Lista organiza productos de alta rotación para apoderados, empresas, colegios, librerías y comercios que necesitan comprar rápido, cotizar por volumen o resolver listas escolares completas.</p></div>
      <img src="assets/source/images/2.png" alt="Tienda de útiles y productos de oficina">
    </div>
  </section>
  <section class="section-bg" style="--section-bg:url('assets/source/images/9.png')">
    <div class="container content-grid">
      <div class="card"><h3>Qué hacemos</h3><p>Centralizamos útiles escolares, materiales de arte, papelería y productos de oficina para que cada cliente encuentre lo que necesita de forma clara. La experiencia está preparada para compra rápida, cotización por WhatsApp y venta mayorista.</p></div>
      <div class="feature-list">
        <div class="feature"><span class="check">✓</span><div><strong>Listas escolares</strong><p>Recepción de listas por formulario o WhatsApp para cotizar productos de manera ordenada.</p></div></div>
        <div class="feature"><span class="check">✓</span><div><strong>Empresas y oficinas</strong><p>Productos recurrentes como resmas, carpetas, archivadores, papelería y escritorio.</p></div></div>
        <div class="feature"><span class="check">✓</span><div><strong>Mayoristas y comercio</strong><p>Atención para librerías, negocios y compras por volumen durante temporada escolar.</p></div></div>
      </div>
    </div>
  </section>
</main>

  <footer class="footer">
    <div class="container footer-grid">
      <div>
        <img src="pagina/assets/images/logo.png" alt="Tu Lista">
        <p>Ecommerce enfocado en útiles escolares, materiales de oficina, papelería, arte, listas escolares y atención a mayoristas.</p>
      </div>
      <div><h4>Tienda</h4><a href="#productos">Productos</a><a href="#categorias">Categorías</a><a href="cotizador-lista">Cotizador de lista</a><a href="#mayoristas">Mayoristas</a></div>
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
  <script src="pagina/assets/js/main.js"></script>
</body>
</html>
