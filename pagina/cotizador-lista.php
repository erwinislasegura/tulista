<!doctype html>
<html lang="es-CL">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cotizador de lista | Tu Lista</title>
  <meta name="description" content="Cotizador de listas escolares y pedidos de oficina para enviar por WhatsApp.">
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
      <a class="brand" href="index.php"><img src="/pagina/assets/images/logo.png" alt="Tu Lista"></a>
      <form class="searchbox" action="index.php#productos">
        <input id="globalSearch" type="search" placeholder="Buscar cuadernos, resmas, témperas, lápices...">
        <button type="submit">⌕</button>
      </form>
      <div class="header-actions">
        <a class="btn ghost" href="cotizador-lista.php">Enviar lista</a>
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
              <div class="mega-col"><h4>Escolar</h4><a href="index.php#productos">Cuadernos</a><a href="index.php#productos">Reglas y geometría</a><a href="index.php#productos">Mochilas y estuches</a><a href="cotizador-lista.php">Cotizar lista escolar</a></div>
              <div class="mega-col"><h4>Arte y manualidades</h4><a href="index.php#productos">Témperas y pinturas</a><a href="index.php#productos">Goma eva</a><a href="index.php#productos">Cartulinas</a><a href="index.php#productos">Material creativo</a></div>
              <div class="mega-col"><h4>Oficina</h4><a href="index.php#productos">Resmas</a><a href="index.php#productos">Carpetas y archivadores</a><a href="index.php#productos">Papelería</a><a href="index.php#productos">Escritorio</a></div>
              <div class="mega-col"><h4>Compra rápida</h4><a href="cotizador-lista.php">Enviar una lista</a><a href="contacto.php">Hablar por WhatsApp</a><a href="index.php#mayoristas">Precios mayoristas</a><a href="sabias-que.php">Preguntas frecuentes</a></div>
            </div>
          </div>
          <a href="index.php" data-nav class="">Inicio</a><a href="nosotros.php" data-nav class="">Nosotros</a><a href="cotizador-lista.php" data-nav class="active">Cotizador de lista</a><a href="sabias-que.php" data-nav class="">Sabías que</a><a href="contacto.php" data-nav class="">Contacto</a>
        </div>
        <div class="nav-note">Compra por unidad, por lista o por volumen</div>
      </div>
    </nav>
  </header>

<main>
  <section class="page-hero page-hero-bg" style="--section-bg:url('../assets/source/images/6.png')">
    <div class="container">
      <div><div class="breadcrumb">Inicio / Cotizador de lista</div><span class="kicker">Cotizador</span><h1>Envía tu lista escolar o pedido de oficina.</h1><p>Completa los datos y cuéntanos qué productos necesitas. El formulario prepara un mensaje directo a WhatsApp para responder más rápido.</p></div>
      <img src="/pagina/assets/images/quote-list.png" alt="Cotizador de lista">
    </div>
  </section>
  <section class="section-bg" style="--section-bg:url('../assets/source/images/13.png')">
    <div class="container content-grid">
      <div class="card"><h3>Cómo funciona</h3><div class="feature-list"><div class="feature"><span class="check">1</span><div><strong>Envía tu lista o detalle</strong><p>Puedes escribir los productos, curso, colegio o tipo de compra.</p></div></div><div class="feature"><span class="check">2</span><div><strong>Revisamos disponibilidad</strong><p>Armamos una propuesta según stock, cantidades y productos equivalentes si corresponde.</p></div></div><div class="feature"><span class="check">3</span><div><strong>Confirmas por WhatsApp</strong><p>Finalizas el pedido con datos de despacho, retiro o forma de pago.</p></div></div></div></div>
      <div class="card"><h3>Formulario de cotización</h3><form class="form" data-whatsapp-form data-message="Hola Tu Lista, quiero cotizar una lista o pedido."><div class="field"><label>Nombre</label><input name="Nombre" required></div><div class="field"><label>WhatsApp</label><input name="WhatsApp" required></div><div class="field"><label>Tipo de compra</label><select name="Tipo"><option>Lista escolar</option><option>Compra por mayor</option><option>Oficina / empresa</option><option>Productos por unidad</option></select></div><div class="field"><label>Comuna / Ciudad</label><input name="Comuna"></div><div class="field full"><label>Colegio, curso o empresa</label><input name="Referencia" placeholder="Ej: 3º básico, oficina, librería..."></div><div class="field full"><label>Detalle de la lista</label><textarea name="Detalle" placeholder="Pega aquí la lista o describe los productos que necesitas..."></textarea></div><div class="field full"><button class="btn orange full" type="submit">Enviar cotización por WhatsApp</button></div></form></div>
    </div>
  </section>
</main>

  <footer class="footer">
    <div class="container footer-grid">
      <div>
        <img src="/pagina/assets/images/logo.png" alt="Tu Lista">
        <p>Ecommerce enfocado en útiles escolares, materiales de oficina, papelería, arte, listas escolares y atención a mayoristas.</p>
      </div>
      <div><h4>Tienda</h4><a href="index.php#productos">Productos</a><a href="index.php#categorias">Categorías</a><a href="cotizador-lista.php">Cotizador de lista</a><a href="index.php#mayoristas">Mayoristas</a></div>
      <div><h4>Empresa</h4><a href="nosotros.php">Nosotros</a><a href="contacto.php">Contacto</a><a href="sabias-que.php">Sabías que</a><a href="condiciones-politicas.php">Condiciones</a></div>
      <div><h4>Categorías</h4><a href="index.php#productos">Escolar</a><a href="index.php#productos">Oficina</a><a href="index.php#productos">Arte</a><a href="index.php#productos">Papelería</a></div>
      <div><h4>Atención</h4><a href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a><a href="mailto:contacto@tulista.cl">contacto@tulista.cl</a><a href="contacto.php">Formulario</a><a href="cotizador-lista.php">Subir lista</a><a href="../dashboard.php">Panel administración</a></div>
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
  <script src="/pagina/assets/js/main.js"></script>
</body>
</html>