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
  <title>Tu Lista Chile | Útiles escolares, papelería online y artículos de oficina</title>
  <meta name="description" content="Compra útiles escolares online en Chile, cotiza tu lista escolar y encuentra papelería online, artículos de oficina, materiales escolares y precios para empresas.">
  <link rel="canonical" href="https://tulista.cl/">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Tu Lista | Útiles escolares Chile y papelería online">
  <meta property="og:description" content="Librería online para comprar útiles escolares, cotizar listas, pedir artículos de oficina y compras mayoristas con despacho a todo Chile.">
  <meta property="og:url" content="https://tulista.cl/">
  <meta property="og:image" content="https://tulista.cl/pagina/assets/images/logo.png">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Tu Lista | Útiles escolares y artículos de oficina">
  <meta name="twitter:description" content="Cotiza listas escolares, compra papelería online y pide útiles para empresas con atención por WhatsApp.">
  <link rel="stylesheet" href="/pagina/assets/css/styles.css">
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@graph":[
      {"@type":"Organization","@id":"https://tulista.cl/#organization","name":"Tu Lista","url":"https://tulista.cl/","logo":"https://tulista.cl/pagina/assets/images/logo.png","contactPoint":{"@type":"ContactPoint","contactType":"customer service","availableLanguage":"Spanish","url":"https://wa.me/569XXXXXXXX"}},
      {"@type":"LocalBusiness","@id":"https://tulista.cl/#localbusiness","name":"Tu Lista","url":"https://tulista.cl/","image":"https://tulista.cl/pagina/assets/images/logo.png","priceRange":"$","description":"Librería online de útiles escolares, papelería y artículos de oficina con cotización de listas escolares y ventas por volumen."},
      {"@type":"WebSite","@id":"https://tulista.cl/#website","url":"https://tulista.cl/","name":"Tu Lista","publisher":{"@id":"https://tulista.cl/#organization"}},
      {"@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Inicio","item":"https://tulista.cl/"},{"@type":"ListItem","position":2,"name":"Tienda de útiles escolares y oficina","item":"https://tulista.cl/#productos"}]},
      {"@type":"Product","name":"Kit escolar básico Tu Lista","image":"https://tulista.cl/pagina/assets/images/prod-kit.png","description":"Kit referencial de útiles escolares para comprar online y cotizar listas escolares en Chile.","brand":{"@type":"Brand","name":"Tu Lista"},"offers":{"@type":"Offer","priceCurrency":"CLP","price":"24990","availability":"https://schema.org/InStock"}},
      {"@type":"FAQPage","mainEntity":[
        {"@type":"Question","name":"¿Hacen envíos a todo Chile?","acceptedAnswer":{"@type":"Answer","text":"Sí, coordinamos despacho a todo Chile según comuna, volumen, disponibilidad y condiciones del pedido."}},
        {"@type":"Question","name":"¿Puedo comprar por mayor?","acceptedAnswer":{"@type":"Answer","text":"Sí, atendemos compras por volumen para empresas, colegios, librerías, fundaciones y municipalidades."}},
        {"@type":"Question","name":"¿Cuánto demora el despacho?","acceptedAnswer":{"@type":"Answer","text":"Depende de la zona y del tamaño del pedido. Confirmamos plazo estimado antes de cerrar la compra."}},
        {"@type":"Question","name":"¿Cómo envío mi lista escolar?","acceptedAnswer":{"@type":"Answer","text":"Ingresa al cotizador, sube el archivo o envía una foto clara por WhatsApp con curso y cantidades."}},
        {"@type":"Question","name":"¿Qué pasa si un producto no tiene stock?","acceptedAnswer":{"@type":"Answer","text":"Te avisamos y proponemos alternativas similares antes de confirmar el pedido."}}
      ]}
    ]
  }
  </script>

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

<main class="storefront-main">
  <section class="hero hero-storefront hero-premium">
    <div class="hero-bg" id="heroParallax" aria-hidden="true">
      <?php for ($i = 1; $i <= 31; $i++): ?>
        <div class="hero-slide<?= $i === 1 ? ' is-active' : '' ?>" style="background-image:url('../assets/source/images/<?= $i ?>.png')"></div>
      <?php endfor; ?>
    </div>
    <div class="container hero-premium-grid">
      <div class="hero-copy hero-premium-copy">
        <span class="eyebrow"><b>Tu Lista Pro</b> tienda escolar, oficina y compras institucionales</span>
        <h1>Una tienda completa para resolver listas escolares, oficinas y compras por volumen.</h1>
        <p>Diseño de compra moderno con catálogo, carrito, cotización asistida y atención comercial. Todo preparado para transmitir confianza, orden y escala profesional.</p>
        <div class="hero-actions"><a class="btn orange" href="#productos">Comprar catálogo</a><a class="btn primary" href="/cotizador-lista">Subir lista o pedido</a><a class="btn ghost" href="#mayoristas">Canal mayorista</a></div>
        <div class="hero-assurance" aria-label="Garantías comerciales">
          <span>✓ Atención por WhatsApp</span><span>✓ Compra por unidad o volumen</span><span>✓ Catálogo filtrable</span>
        </div>
      </div>
    </div>
  </section>

  <section class="store-band" aria-label="Beneficios de tienda">
    <div class="container store-band-grid">
      <div><strong>Compra formal</strong><span>Precios visibles, cantidades y carrito.</span></div>
      <div><strong>Cotización guiada</strong><span>Para listas escolares y pedidos grandes.</span></div>
      <div><strong>Canal empresas</strong><span>Reposición de oficina e instituciones.</span></div>
      <div><strong>Mayoristas</strong><span>Librerías, comercio y compras recurrentes.</span></div>
    </div>
  </section>

  <nav class="breadcrumb" aria-label="Breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
    <div class="container">
      <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="/"><span itemprop="name">Inicio</span></a><meta itemprop="position" content="1"></span>
      <span aria-hidden="true">/</span>
      <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">Tienda de útiles escolares y oficina</span><meta itemprop="position" content="2"></span>
    </div>
  </nav>

  <section class="why-section seo-section" aria-labelledby="why-title">
    <div class="container">
      <div class="section-head"><div><span class="kicker">Compra con confianza</span><h2 id="why-title" class="section-title">¿Por qué comprar en Tu Lista?</h2><p class="section-copy">Tu Lista reúne útiles escolares, artículos de oficina, papelería online y materiales escolares en una experiencia pensada para comprar rápido, comparar con calma y pedir apoyo cuando la lista viene larga. Atendemos familias, colegios, oficinas y compras por volumen con el mismo foco: productos claros, respuesta humana y una cotización que no te haga perder la tarde.</p></div><a class="btn orange" href="#productos">Comprar ahora</a></div>
      <div class="benefit-grid">
        <article><strong>✓ Productos originales</strong><p>Trabajamos el catálogo con productos reconocibles, información ordenada y alternativas útiles para colegio, oficina y manualidades.</p></article>
        <article><strong>✓ Amplio catálogo</strong><p>Encuentra cuadernos, lápices, mochilas, arte, papelería, oficina, tecnología escolar y materiales de alta rotación.</p></article>
        <article><strong>✓ Envíos a todo Chile</strong><p>Gestionamos despacho a todo Chile según disponibilidad, zona y volumen del pedido, con confirmación antes del cierre.</p></article>
        <article><strong>✓ Compra segura</strong><p>Arma tu carrito, solicita confirmación y paga con el método acordado cuando el stock esté revisado.</p></article>
        <article><strong>✓ Atención por WhatsApp</strong><p>Si necesitas ayuda, puedes hablar con una persona para resolver dudas, marcas, cantidades o reemplazos.</p></article>
        <article><strong>✓ Precios para empresas</strong><p>Cotizamos útiles para empresas, colegios, fundaciones, municipalidades y compras recurrentes de oficina.</p></article>
        <article><strong>✓ Cotización rápida</strong><p>Envíanos tu lista de útiles escolares en imagen, PDF, Word o Excel y la ordenamos para responderte con claridad.</p></article>
        <article><strong>✓ Más de 500 productos disponibles</strong><p>La tienda está preparada para crecer con nuevas líneas, packs de temporada, reposición y productos para mayoristas.</p></article>
      </div>
    </div>
  </section>

  <section class="how-section seo-section" aria-labelledby="how-title">
    <div class="container split-layout">
      <div><span class="kicker">Cómo comprar</span><h2 id="how-title" class="section-title">Comprar útiles escolares online debe ser simple.</h2><p class="section-copy">El flujo de Tu Lista mantiene la compra clara desde el primer clic. Puedes elegir productos uno a uno, enviar una lista de útiles escolares completa o pedir una cotización mayorista de útiles escolares para tu empresa o institución.</p><a class="btn primary" href="/cotizador-lista">Cotizar mi lista</a></div>
      <div class="step-grid">
        <article><span>1</span><h3>Elegir productos</h3><p>Busca por categoría, nombre o uso: útiles para colegios, materiales escolares, artículos de oficina o papelería online.</p></article>
        <article><span>2</span><h3>Agregar al carrito</h3><p>Ajusta cantidades antes de comprar. Esto ayuda cuando necesitas varios cuadernos, packs de lápices o insumos por curso.</p></article>
        <article><span>3</span><h3>Enviar pedido</h3><p>El carrito se envía por WhatsApp para revisar detalles, cantidades, marcas disponibles y datos de despacho.</p></article>
        <article><span>4</span><h3>Confirmar stock</h3><p>Validamos stock y alternativas. Si falta un producto, proponemos reemplazos similares antes de confirmar.</p></article>
        <article><span>5</span><h3>Pago</h3><p>Coordinamos el pago según el tipo de compra: minorista, empresa, colegio o pedido por volumen.</p></article>
        <article><span>6</span><h3>Despacho</h3><p>Preparamos el pedido y coordinamos despacho a todo Chile o retiro, según disponibilidad operativa.</p></article>
      </div>
    </div>
  </section>

  <section class="school-list-section seo-section" aria-labelledby="school-list-title">
    <div class="container">
      <div class="section-head"><div><span class="kicker">Listas escolares</span><h2 id="school-list-title" class="section-title">Cotiza tu lista de útiles escolares sin recorrer varias tiendas.</h2><p class="section-copy">Sabemos que una lista de útiles escolares puede traer marcas específicas, formatos distintos y productos que cambian por curso. Por eso el servicio de cotización está pensado para recibir tu archivo, ordenar los ítems y responder con una propuesta comprensible.</p></div><a class="btn orange" href="/cotizador-lista">Enviar lista escolar</a></div>
      <div class="content-columns">
        <article><h3>¿Cómo cotizar una lista escolar?</h3><p>Sube o envía una foto clara de la lista, un PDF del colegio, un documento Word o una planilla Excel. También puedes copiar los productos en un mensaje si la lista es corta. Revisamos cantidades, tipo de cuaderno, tamaño, colores, marcas sugeridas y materiales especiales para arte o tecnología escolar.</p><p>Cuando la lista incluye productos difíciles de encontrar, proponemos alternativas equivalentes antes de cerrar el pedido. La idea es que compres útiles escolares Chile con menos vueltas y con una respuesta clara.</p></article>
        <article><h3>Archivos aceptados</h3><p>Aceptamos imágenes JPG o PNG tomadas con el celular, PDF enviados por colegios, documentos Word, planillas Excel y mensajes escritos por WhatsApp. Lo ideal es que el archivo se vea completo, sin sombras y con el nombre del curso visible si aplica.</p><p>Si tienes más de un estudiante, puedes enviar todas las listas juntas y separarlas por nombre o curso. Así evitamos duplicidades y armamos una cotización más ordenada.</p></article>
        <article><h3>Beneficios y tiempo de respuesta</h3><p>Cotizar con Tu Lista te ayuda a ahorrar tiempo, comparar productos y centralizar la compra en un solo canal. En temporada alta respondemos por orden de llegada y avisamos si algún ítem requiere confirmación especial.</p><p>El tiempo de respuesta puede variar según la extensión de la lista, stock y volumen. Para listas simples, la respuesta suele ser más rápida; para colegios, empresas o compras por curso, revisamos con más detalle.</p></article>
      </div>
      <aside class="mini-faq"><h3>Preguntas rápidas sobre listas escolares</h3><details><summary>¿Puedo enviar una lista escrita a mano?</summary><p>Sí, siempre que la foto sea legible y aparezcan cantidades, formatos y curso.</p></details><details><summary>¿Pueden cotizar productos alternativos?</summary><p>Sí. Si una marca no está disponible, sugerimos opciones similares antes de confirmar.</p></details><details><summary>¿Sirve para colegios completos?</summary><p>Sí. Podemos revisar pedidos por curso, docentes, oficinas administrativas o compras institucionales.</p></details></aside>
    </div>
  </section>

  <section class="featured-categories seo-section" aria-labelledby="cat-title">
    <div class="container">
      <div class="section-head"><div><span class="kicker">Categorías destacadas</span><h2 id="cat-title" class="section-title">Todo para colegio, oficina y papelería en un mismo lugar.</h2><p class="section-copy">Estas categorías ayudan a resolver compras frecuentes sin perder tiempo. Cada bloque reúne productos pensados para estudiantes, profesores, oficinas, emprendedores y familias que buscan una librería online ordenada.</p></div><a class="btn ghost" href="#productos">Ver categorías</a></div>
      <div class="seo-category-grid">
        <article><img loading="lazy" src="/pagina/assets/images/prod-cuadernos.png" alt="Cuadernos escolares y universitarios"><h3>Cuadernos</h3><p>Los cuadernos son la base de cualquier lista de útiles escolares. En Tu Lista puedes buscar opciones universitarias, college, composición, cuadriculadas o de líneas, pensando en básica, media, educación superior y oficina. Elegir bien el formato ayuda a mantener apuntes ordenados, separar asignaturas y evitar compras duplicadas a mitad de semestre. También son productos clave para colegios que compran por volumen, talleres y profesores que necesitan material de apoyo para clases.</p></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-lapices.png" alt="Lápices grafito colores y escritura escolar"><h3>Lápices</h3><p>Los lápices acompañan tareas, dibujo técnico, escritura diaria y trabajos creativos. Una buena compra considera grafito, colores, destacadores, marcadores, portaminas y repuestos. Para estudiantes pequeños conviene priorizar agarre cómodo y resistencia; para dibujo o arte, la intensidad del color y la suavidad del trazo marcan diferencia. También son productos ideales para empresas que necesitan reponer artículos de oficina de forma recurrente sin comprar de más.</p></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-mochila.png" alt="Mochilas escolares resistentes"><h3>Mochilas</h3><p>Una mochila escolar debe equilibrar diseño, capacidad y resistencia. Para niños y jóvenes, el espacio interior, los cierres firmes y los bolsillos hacen que la rutina sea más cómoda. En compras escolares conviene revisar tamaño, peso, distribución y uso esperado: no es lo mismo una mochila para preescolar que una para enseñanza media. También puedes sumar estuches, loncheras y accesorios para armar un pedido escolar más completo.</p></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-pinturas.png" alt="Materiales de arte escolar y pinturas"><h3>Arte</h3><p>La categoría de arte reúne témperas, pinceles, cartulinas, lápices de colores, goma eva y materiales para trabajos manuales. Es una de las áreas donde más se nota la diferencia entre comprar apurado y comprar con asesoría, porque cada colegio puede pedir formatos específicos. Para talleres, profesores y estudiantes, tener buenos materiales mejora el resultado de maquetas, afiches, actividades de sala y proyectos creativos durante el año.</p></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-etiquetas.png" alt="Papelería online etiquetas papel y organización"><h3>Papelería</h3><p>La papelería online permite resolver compras de hojas, etiquetas, carpetas, archivadores, separadores, sobres y productos de organización desde un solo catálogo. Es una categoría útil tanto para estudiantes como para oficinas, contadores, tiendas y emprendimientos. Una compra ordenada evita quedarse sin insumos básicos en momentos de alta demanda y ayuda a mantener documentos, trabajos y materiales clasificados de forma simple.</p></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-oficina.png" alt="Artículos de oficina para empresas"><h3>Oficina</h3><p>Los artículos de oficina incluyen resmas, carpetas, archivadores, notas adhesivas, lápices, clips, corcheteras y productos de escritorio. Para empresas, comprar con planificación reduce quiebres de stock interno y evita pedidos pequeños repetidos. Tu Lista puede apoyar compras por volumen, reposición mensual y cotizaciones para equipos administrativos, colegios, municipalidades, fundaciones y negocios que necesitan insumos confiables durante todo el año.</p></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-gomaeva.png" alt="Manualidades escolares goma eva cartulinas y creatividad"><h3>Manualidades</h3><p>Las manualidades combinan creatividad, aprendizaje y presentación. Goma eva, pegamentos, tijeras, papeles de colores, cartulinas y materiales decorativos son habituales en listas escolares y talleres. Para cursos pequeños conviene revisar seguridad y facilidad de uso; para proyectos más grandes, la variedad de colores y formatos ayuda mucho. Esta categoría también sirve para cumpleaños, ferias, actividades de aula y trabajos de temporada.</p></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-kit.png" alt="Tecnología escolar y accesorios para estudiantes"><h3>Tecnología escolar</h3><p>La tecnología escolar incluye accesorios y materiales que complementan el estudio: calculadoras, audífonos, organizadores, pendrives, etiquetas y productos de apoyo según disponibilidad. Cada vez más listas incorporan elementos prácticos para presentaciones, clases híbridas o trabajos digitales. La recomendación es cotizar con anticipación para confirmar compatibilidad, stock y alternativas, especialmente si el colegio solicita modelos o características específicas.</p></article>
      </div>
    </div>
  </section>

  <section class="featured-products seo-section" aria-labelledby="featured-products-title">
    <div class="container">
      <div class="section-head"><div><span class="kicker">Productos destacados</span><h2 id="featured-products-title" class="section-title">Compra útiles escolares online con productos claros y comparables.</h2><p class="section-copy">Esta selección referencial muestra productos frecuentes para listas, oficina y papelería. Usa los botones para comprar ahora o consultar por WhatsApp antes de confirmar stock.</p></div><a class="btn orange" href="#productos">Comprar ahora</a></div>
      <div class="seo-products-grid">
        <article itemscope itemtype="https://schema.org/Product"><img loading="lazy" itemprop="image" src="/pagina/assets/images/prod-resma.png" alt="Resma carta 500 hojas para oficina"><h3 itemprop="name">Resma carta 500 hojas</h3><p class="brand">Marca: Torre</p><p itemprop="description">Papel blanco para impresión diaria, tareas, informes y artículos de oficina.</p><div class="rating">★★★★★ <span>4.8</span></div><span class="stock">Stock disponible</span><div class="prices" itemprop="offers" itemscope itemtype="https://schema.org/Offer"><span class="old">$5.690</span><strong itemprop="price" content="4790">$4.790</strong><meta itemprop="priceCurrency" content="CLP"></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-cuadernos.png" alt="Cuaderno universitario 100 hojas"><h3>Cuaderno universitario 100 hojas</h3><p class="brand">Marca: Colón</p><p>Cuaderno práctico para apuntes, tareas y materiales escolares de uso diario.</p><div class="rating">★★★★☆ <span>4.7</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$2.390</span><strong>$1.890</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-lapices.png" alt="Set lápices grafito x12"><h3>Set lápices grafito x12</h3><p class="brand">Marca: Faber-Castell</p><p>Lápices para escritura escolar, dibujo y reposición básica de escritorio.</p><div class="rating">★★★★★ <span>4.9</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$2.990</span><strong>$2.490</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-pinturas.png" alt="Témpera escolar 12 colores"><h3>Témpera escolar 12 colores</h3><p class="brand">Marca: Artel</p><p>Material de arte para trabajos, láminas, manualidades y actividades escolares.</p><div class="rating">★★★★★ <span>4.8</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$4.990</span><strong>$3.990</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-carpetas.png" alt="Carpeta oficio con acoclip"><h3>Carpeta oficio con acoclip</h3><p class="brand">Marca: Rhein</p><p>Organización simple para guías, documentos, trámites y útiles para colegios.</p><div class="rating">★★★★☆ <span>4.6</span></div><span class="stock">Stock bajo</span><div class="prices"><span class="old">$790</span><strong>$590</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-gomaeva.png" alt="Goma eva colores surtidos"><h3>Goma eva colores surtidos</h3><p class="brand">Marca: Proarte</p><p>Manualidades escolares, decoración, maquetas y trabajos creativos por curso.</p><div class="rating">★★★★☆ <span>4.7</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$2.390</span><strong>$1.990</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-mochila.png" alt="Mochila escolar resistente"><h3>Mochila escolar resistente</h3><p class="brand">Marca: Head</p><p>Mochila con espacio para cuadernos, estuche y materiales escolares diarios.</p><div class="rating">★★★★★ <span>4.8</span></div><span class="stock">Consultar stock</span><div class="prices"><span class="old">$18.990</span><strong>$15.990</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-oficina.png" alt="Pack oficina mensual"><h3>Pack oficina mensual</h3><p class="brand">Marca: Mixta</p><p>Artículos de oficina para reposición, escritorios, recepción y administración.</p><div class="rating">★★★★★ <span>4.9</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$39.990</span><strong>$34.990</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-libros.png" alt="Libro lectura complementaria"><h3>Libro lectura complementaria</h3><p class="brand">Marca: Zig-Zag</p><p>Lecturas escolares para apoyar comprensión, biblioteca y trabajos de lenguaje.</p><div class="rating">★★★★☆ <span>4.6</span></div><span class="stock">Consultar stock</span><div class="prices"><span class="old">$7.990</span><strong>$6.990</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-geometria.png" alt="Set geometría escolar"><h3>Set geometría escolar</h3><p class="brand">Marca: Maped</p><p>Regla, escuadras y transportador para matemáticas, dibujo y geometría.</p><div class="rating">★★★★★ <span>4.8</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$2.490</span><strong>$1.990</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-etiquetas.png" alt="Papel adhesivo A4 100 hojas"><h3>Papel adhesivo A4 100 hojas</h3><p class="brand">Marca: Adetec</p><p>Papelería online para etiquetas, organización, rotulación y oficina.</p><div class="rating">★★★★☆ <span>4.7</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$16.990</span><strong>$15.320</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-kit.png" alt="Kit escolar básico primer ciclo"><h3>Kit escolar básico 1º ciclo</h3><p class="brand">Marca: Tu Lista</p><p>Selección escolar referencial para resolver compras rápidas por curso.</p><div class="rating">★★★★★ <span>4.9</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$29.990</span><strong>$24.990</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-cuadernos.png" alt="Cuaderno college matemáticas"><h3>Cuaderno college matemáticas</h3><p class="brand">Marca: Torre</p><p>Formato cómodo para estudiantes de básica y listas escolares Chile.</p><div class="rating">★★★★☆ <span>4.6</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$1.890</span><strong>$1.490</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-lapices.png" alt="Lápices de colores 24 unidades"><h3>Lápices de colores 24 unidades</h3><p class="brand">Marca: Giotto</p><p>Colores intensos para arte, tareas, mapas y trabajos escolares creativos.</p><div class="rating">★★★★★ <span>4.8</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$5.490</span><strong>$4.790</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-oficina.png" alt="Archivador oficio lomo ancho"><h3>Archivador oficio lomo ancho</h3><p class="brand">Marca: Rhein</p><p>Archivo para empresas, colegios y oficinas con alta carga documental.</p><div class="rating">★★★★☆ <span>4.5</span></div><span class="stock">Stock bajo</span><div class="prices"><span class="old">$3.990</span><strong>$3.290</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-pinturas.png" alt="Pinceles escolares surtidos"><h3>Pinceles escolares surtidos</h3><p class="brand">Marca: Artel</p><p>Set para témpera, acuarela, trabajos de arte y manualidades escolares.</p><div class="rating">★★★★☆ <span>4.6</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$2.990</span><strong>$2.390</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-carpetas.png" alt="Pack carpetas colores"><h3>Pack carpetas colores</h3><p class="brand">Marca: Proarte</p><p>Carpetas para separar asignaturas, guías, pruebas y documentos.</p><div class="rating">★★★★☆ <span>4.7</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$3.490</span><strong>$2.990</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-etiquetas.png" alt="Etiquetas escolares personalizables"><h3>Etiquetas escolares</h3><p class="brand">Marca: Adetec</p><p>Etiquetas para cuadernos, libros, carpetas y organización escolar.</p><div class="rating">★★★★★ <span>4.8</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$2.290</span><strong>$1.890</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-gomaeva.png" alt="Cartulina española colores"><h3>Cartulina española colores</h3><p class="brand">Marca: Proarte</p><p>Cartulinas para afiches, presentaciones, manualidades y salas de clase.</p><div class="rating">★★★★☆ <span>4.5</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$890</span><strong>$690</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-kit.png" alt="Estuche escolar completo"><h3>Estuche escolar completo</h3><p class="brand">Marca: Torre</p><p>Estuche con básicos para clases, escritura, dibujo y uso diario.</p><div class="rating">★★★★★ <span>4.9</span></div><span class="stock">Consultar stock</span><div class="prices"><span class="old">$8.990</span><strong>$7.490</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-oficina.png" alt="Notas adhesivas oficina"><h3>Notas adhesivas colores</h3><p class="brand">Marca: 3M</p><p>Organización rápida para escritorios, planificación, estudio y oficina.</p><div class="rating">★★★★☆ <span>4.7</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$2.990</span><strong>$2.490</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-resma.png" alt="Papel fotocopia oficio"><h3>Papel fotocopia oficio</h3><p class="brand">Marca: Chamex</p><p>Papel de oficina para informes, impresiones, colegios y empresas.</p><div class="rating">★★★★★ <span>4.8</span></div><span class="stock">Stock disponible</span><div class="prices"><span class="old">$6.490</span><strong>$5.790</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-geometria.png" alt="Calculadora escolar básica"><h3>Calculadora escolar básica</h3><p class="brand">Marca: Casio</p><p>Apoyo para matemáticas, tareas, comercio y tecnología escolar.</p><div class="rating">★★★★★ <span>4.9</span></div><span class="stock">Consultar stock</span><div class="prices"><span class="old">$9.990</span><strong>$8.490</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
        <article><img loading="lazy" src="/pagina/assets/images/prod-mochila.png" alt="Lonchera escolar térmica"><h3>Lonchera escolar térmica</h3><p class="brand">Marca: Head</p><p>Accesorio práctico para estudiantes, colaciones y rutina escolar diaria.</p><div class="rating">★★★★☆ <span>4.6</span></div><span class="stock">Stock bajo</span><div class="prices"><span class="old">$12.990</span><strong>$10.990</strong></div><a class="btn orange" href="#productos">Comprar</a><a class="btn ghost" href="https://wa.me/569XXXXXXXX" target="_blank">WhatsApp</a></article>
      </div>
    </div>
  </section>

  <section class="companies-section seo-section" aria-labelledby="companies-title">
    <div class="container split-layout">
      <div><span class="kicker">Empresas e instituciones</span><h2 id="companies-title" class="section-title">Útiles para empresas, colegios y compras por volumen.</h2><p class="section-copy">Tu Lista también atiende compras corporativas y mayoristas. Podemos cotizar artículos de oficina, materiales escolares, papelería, útiles para colegios y productos de temporada para empresas, municipalidades, fundaciones, librerías y equipos administrativos.</p><p>Si necesitas facturación, compras por volumen o reposición periódica, envía el detalle del pedido. Revisamos cantidades, marcas, alternativas y disponibilidad. Para colegios, apoyamos compras por curso, salas de profesores, biblioteca, arte, administración y actividades especiales.</p><a class="btn orange" href="/cotizador-lista">Comprar por mayor</a></div>
      <aside class="company-panel"><h3>Atendemos</h3><ul><li>Colegios y jardines infantiles.</li><li>Empresas, oficinas y coworks.</li><li>Municipalidades y programas sociales.</li><li>Fundaciones, academias y talleres.</li><li>Librerías y comercios que buscan mayorista de útiles escolares.</li></ul></aside>
    </div>
  </section>

  <section class="blog-section seo-section" aria-labelledby="blog-title">
    <div class="container">
      <div class="section-head"><div><span class="kicker">Guías útiles</span><h2 id="blog-title" class="section-title">Consejos para comprar mejor útiles escolares y oficina.</h2><p class="section-copy">Creamos estas guías como apoyo rápido para familias, profesores y equipos administrativos. No son páginas separadas: son tarjetas de lectura pensadas para orientar la compra.</p></div><a class="btn ghost" href="#faq">Ver preguntas frecuentes</a></div>
      <div class="blog-grid">
        <article><h3>Cómo ahorrar en la lista escolar</h3><p>Revisa cantidades reales, reutiliza materiales en buen estado y cotiza packs cuando varios productos se repiten.</p></article>
        <article><h3>Qué útiles necesita un estudiante de básica</h3><p>Cuadernos, lápices, pegamento, tijeras, carpetas y materiales de arte suelen ser la base del año escolar.</p></article>
        <article><h3>Cómo elegir un cuaderno</h3><p>Elige tamaño, tipo de hoja y espiral según edad, asignatura y forma de tomar apuntes.</p></article>
        <article><h3>Los mejores lápices para dibujo</h3><p>Para dibujo conviene mirar pigmentación, suavidad, resistencia de mina y variedad de tonos.</p></article>
        <article><h3>Guía para comprar materiales de oficina</h3><p>Planifica resmas, archivadores, lápices y notas adhesivas según consumo mensual del equipo.</p></article>
        <article><h3>Diferencia entre cuaderno universitario y college</h3><p>El universitario ofrece más espacio; el college suele ser más cómodo para estudiantes pequeños.</p></article>
        <article><h3>Cómo organizar un escritorio</h3><p>Usa separadores, bandejas, etiquetas y una reposición simple de artículos de oficina básicos.</p></article>
        <article><h3>Materiales indispensables para profesores</h3><p>Marcadores, carpetas, etiquetas, papelería y recursos de arte ayudan a preparar clases con orden.</p></article>
      </div>
    </div>
  </section>

  <section class="trust-section seo-section" aria-labelledby="trust-title">
    <div class="container">
      <div class="section-head centered"><div><span class="kicker">Confianza</span><h2 id="trust-title" class="section-title">Una librería online preparada para compras pequeñas y grandes.</h2><p class="section-copy">La confianza se gana con respuestas claras. Por eso mostramos stock referencial, confirmamos disponibilidad, cuidamos el proceso de pago y mantenemos comunicación por WhatsApp durante la compra.</p></div></div>
      <div class="trust-layout"><article><h3>Opiniones de clientes</h3><p>“Me ayudaron a ordenar la lista de mi hijo y propusieron reemplazos cuando faltaba una marca.”</p><p>“Para la oficina compramos resmas y carpetas por volumen, con respuesta rápida.”</p></article><article><h3>Marcas y categorías</h3><div class="brand-cloud"><span>Torre</span><span>Artel</span><span>Rhein</span><span>Faber-Castell</span><span>Proarte</span><span>Maped</span></div></article><article><h3>Pago, despacho y garantía</h3><p>Coordinamos métodos de pago, despacho a todo Chile, compras con confirmación de stock, sitio con SSL y garantía según producto y condición informada.</p></article></div>
    </div>
  </section>

  <section id="faq" class="faq-section seo-section" aria-labelledby="faq-title">
    <div class="container">
      <div class="section-head"><div><span class="kicker">FAQ</span><h2 id="faq-title" class="section-title">Preguntas frecuentes sobre Tu Lista.</h2><p class="section-copy">Respuestas claras para comprar útiles escolares online, cotizar listas, pedir por mayor o resolver dudas de despacho.</p></div><a class="btn orange" href="https://wa.me/569XXXXXXXX" target="_blank">Hablar por WhatsApp</a></div>
      <div class="faq-grid">
        <details><summary>¿Hacen envíos a todo Chile?</summary><p>Sí, coordinamos despacho a todo Chile según comuna, volumen, disponibilidad y condiciones del pedido.</p></details>
        <details><summary>¿Puedo comprar por mayor?</summary><p>Sí, atendemos compras por volumen para empresas, colegios, librerías, fundaciones y municipalidades.</p></details>
        <details><summary>¿Cuánto demora el despacho?</summary><p>Depende de la zona y del tamaño del pedido. Confirmamos plazo estimado antes de cerrar la compra.</p></details>
        <details><summary>¿Cómo envío mi lista escolar?</summary><p>Ingresa al cotizador, sube el archivo o envía una foto clara por WhatsApp con curso y cantidades.</p></details>
        <details><summary>¿Aceptan transferencia?</summary><p>La forma de pago se confirma al revisar stock y datos del pedido. La transferencia puede estar disponible según compra.</p></details>
        <details><summary>¿Qué pasa si un producto no tiene stock?</summary><p>Te avisamos y proponemos alternativas similares antes de confirmar el pedido.</p></details>
        <details><summary>¿Puedo pedir marcas específicas?</summary><p>Sí. Indica la marca solicitada y revisamos disponibilidad o equivalentes.</p></details>
        <details><summary>¿Cotizan listas de varios estudiantes?</summary><p>Sí, puedes enviar varias listas separadas por nombre o curso.</p></details>
        <details><summary>¿Venden artículos de oficina?</summary><p>Sí, tenemos resmas, carpetas, archivadores, lápices, papelería y otros insumos.</p></details>
        <details><summary>¿Atienden colegios?</summary><p>Sí, podemos cotizar útiles para colegios, cursos, profesores y compras institucionales.</p></details>
        <details><summary>¿Tienen facturación?</summary><p>Para compras de empresas o instituciones, consulta los datos requeridos al momento de cotizar.</p></details>
        <details><summary>¿Puedo retirar mi pedido?</summary><p>El retiro depende de la disponibilidad operativa y se confirma antes del pago.</p></details>
        <details><summary>¿Los precios incluyen IVA?</summary><p>Los precios publicados son referenciales y se confirman al revisar stock, tipo de compra y condiciones.</p></details>
        <details><summary>¿Puedo cambiar productos de una lista?</summary><p>Sí, puedes elegir alternativas por marca, precio o disponibilidad.</p></details>
        <details><summary>¿Atienden por WhatsApp?</summary><p>Sí, WhatsApp es el canal principal para cerrar pedidos, resolver dudas y cotizar listas.</p></details>
        <details><summary>¿Qué archivos aceptan para cotizar?</summary><p>Fotos JPG o PNG, PDF, Word, Excel y mensajes escritos con detalle de productos.</p></details>
        <details><summary>¿Tienen productos para manualidades?</summary><p>Sí, trabajamos materiales de arte, cartulinas, goma eva, pinturas y complementos escolares.</p></details>
        <details><summary>¿Puedo comprar para una empresa pequeña?</summary><p>Sí, atendemos desde pequeños equipos hasta compras de mayor volumen.</p></details>
        <details><summary>¿Qué significa stock referencial?</summary><p>Significa que el producto puede estar disponible, pero siempre confirmamos antes de pedir pago.</p></details>
        <details><summary>¿Cómo puedo ahorrar en útiles escolares?</summary><p>Envía la lista completa, revisa alternativas y compra con anticipación para evitar urgencias de temporada.</p></details>
      </div>
    </div>
  </section>

  <section id="productos" class="catalog-section">
    <div class="container">
      <div class="section-head"><div><span class="kicker">Catálogo comercial</span><h2 class="section-title">Productos listos para agregar al pedido.</h2><p class="section-copy">Filtra por departamento, ajusta cantidades y arma un pedido con presentación de ecommerce profesional. Confirmas disponibilidad y despacho por WhatsApp.</p></div><button class="btn ghost" id="clearFilters" type="button">Limpiar filtros</button></div>
      <div class="shop-layout elevated-shop">
        <aside class="filters"><h3>Departamentos</h3><div id="sideCategories"></div><hr style="border:0;border-top:1px solid var(--line);margin:14px 0"><a class="filter-btn" href="/cotizador-lista">Cotizar lista <span>rápido</span></a><a class="filter-btn" href="#mayoristas">Mayoristas <span>especial</span></a></aside>
        <div>
          <div class="toolbar"><div class="tabs" id="tabs"></div><select class="sort" id="sortSelect"><option value="featured">Destacados</option><option value="priceAsc">Menor precio</option><option value="priceDesc">Mayor precio</option><option value="name">Nombre A-Z</option></select></div>
          <p class="result-note" id="resultNote"></p>
          <div class="product-grid" id="productGrid"></div>
        </div>
      </div>
    </div>
  </section>

  <section id="mayoristas" class="section-bg wholesale-section" style="--section-bg:url('../assets/source/images/21.png')">
    <div class="container banner-grid">
      <article class="banner big"><div class="banner-copy"><span class="kicker">Canal mayorista e institucional</span><h3>Abastecimiento serio para librerías, comercio, colegios y oficinas.</h3><p>Cotiza por volumen productos escolares, papelería, arte y oficina. Ideal para reposición, temporada escolar y compras institucionales con atención comercial.</p><a class="btn orange" href="/cotizador-lista">Solicitar precio mayorista</a></div><img src="../assets/source/images/22.png" alt="Compra mayorista"></article>
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
