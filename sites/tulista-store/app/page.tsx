"use client";

import Image from "next/image";
import { useEffect, useMemo, useState } from "react";

const categories = [
  { name: "Escolar", image: "/images/prod-mochila.png" },
  { name: "Oficina", image: "/images/prod-oficina.png" },
  { name: "Arte", image: "/images/prod-pinturas.png" },
  { name: "Papelería", image: "/images/prod-etiquetas.png" },
  { name: "Organización", image: "/images/prod-carpetas.png" },
  { name: "Mayorista", image: "/images/prod-resma.png" },
];

const heroBackdrops = [
  { image: "/images/hero-products.jpg", label: "Vuelta a clases" },
  { image: "/images/prod-pinturas.png", label: "Arte que inspira" },
  { image: "/images/prod-oficina.png", label: "Oficina en orden" },
];

const products = [
  { id: 1, name: "Kit escolar esencial", cat: "Escolar", price: 15990, image: "/images/prod-kit.png", tag: "Más vendido", sku: "KIT-ESC-01", brand: "Tu Lista" },
  { id: 2, name: "Cuadernos universitarios", cat: "Escolar", price: 3490, image: "/images/prod-cuadernos.png", tag: "Nuevo", sku: "CUA-UNI-100", brand: "Escolar" },
  { id: 3, name: "Set de lápices de colores", cat: "Arte", price: 5290, image: "/images/prod-lapices.png", tag: "Oferta", sku: "LAP-COL-12", brand: "Creativa" },
  { id: 4, name: "Resma carta 500 hojas", cat: "Oficina", price: 5990, image: "/images/prod-resma.png", tag: "Stock", sku: "RES-CAR-500", brand: "Oficina" },
  { id: 5, name: "Pack carpetas organizadoras", cat: "Organización", price: 8490, image: "/images/prod-carpetas.png", tag: "Empresas", sku: "CAR-ORG-05", brand: "Archivo" },
  { id: 6, name: "Set de geometría", cat: "Escolar", price: 2990, image: "/images/prod-geometria.png", tag: "Stock", sku: "GEO-SET-04", brand: "Escolar" },
  { id: 7, name: "Témperas 12 colores", cat: "Arte", price: 4490, image: "/images/prod-pinturas.png", tag: "Favorito", sku: "TEM-12-COL", brand: "Creativa" },
  { id: 8, name: "Papel adhesivo A4", cat: "Papelería", price: 15320, image: "/images/prod-etiquetas.png", tag: "Oficina", sku: "PAP-ADH-A4", brand: "Papelería" },
];

const money = new Intl.NumberFormat("es-CL", { style: "currency", currency: "CLP", maximumFractionDigits: 0 });

export default function Home() {
  const [query, setQuery] = useState("");
  const [category, setCategory] = useState("Todos");
  const [cart, setCart] = useState<Record<number, number>>({});
  const [cartOpen, setCartOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [quoteOpen, setQuoteOpen] = useState(false);
  const [heroSlide, setHeroSlide] = useState(0);

  useEffect(() => {
    const timer = window.setInterval(() => setHeroSlide((current) => (current + 1) % heroBackdrops.length), 5200);
    return () => window.clearInterval(timer);
  }, []);

  useEffect(() => {
    let frame = 0;
    const updateParallax = () => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => {
        document.querySelectorAll<HTMLElement>("[data-parallax]").forEach((section) => {
          const rect = section.getBoundingClientRect();
          const progress = (window.innerHeight - rect.top) / (window.innerHeight + rect.height);
          section.style.setProperty("--parallax", `${Math.max(0, Math.min(1, progress))}`);
        });
      });
    };
    updateParallax();
    window.addEventListener("scroll", updateParallax, { passive: true });
    return () => { cancelAnimationFrame(frame); window.removeEventListener("scroll", updateParallax); };
  }, []);

  const visible = useMemo(() => products.filter((product) => {
    const matchesCategory = category === "Todos" || product.cat === category;
    const matchesQuery = product.name.toLowerCase().includes(query.toLowerCase());
    return matchesCategory && matchesQuery;
  }), [category, query]);

  const itemCount = Object.values(cart).reduce((sum, quantity) => sum + quantity, 0);
  const subtotal = products.reduce((sum, product) => sum + product.price * (cart[product.id] || 0), 0);

  function add(id: number) {
    setCart((current) => ({ ...current, [id]: (current[id] || 0) + 1 }));
  }

  function scrollToCatalog(cat = "Todos") {
    setCategory(cat);
    document.getElementById("catalogo")?.scrollIntoView({ behavior: "smooth" });
  }

  return (
    <main>
      <div className="utility"><span>Envíos a todo Chile</span><span>Compra por unidad, lista o volumen</span><a href="#mayorista">Atención empresas y colegios →</a></div>
      <header>
        <div className="header-main shell">
          <a className="brand" href="#inicio" aria-label="Tu Lista, inicio"><Image unoptimized src="/images/logo.png" width={178} height={55} alt="Tu Lista" priority /></a>
          <label className="search"><span>⌕</span><input value={query} onChange={(event) => setQuery(event.target.value)} onFocus={() => scrollToCatalog()} placeholder="¿Qué producto buscas hoy?" aria-label="Buscar productos" /></label>
          <div className="header-actions"><button className="quote-header" onClick={() => setQuoteOpen(true)}>Cotizar lista</button><button className="cart-button" onClick={() => setCartOpen(true)}>Carrito <b>{itemCount}</b></button><button className="hamburger" onClick={() => setMenuOpen(!menuOpen)} aria-label="Abrir menú">☰</button></div>
        </div>
        <nav className={menuOpen ? "open" : ""}><div className="shell"><a href="#inicio">Inicio</a>{["Escolar", "Oficina", "Arte", "Papelería", "Organización"].map((item) => <button key={item} onClick={() => scrollToCatalog(item)}>{item}</button>)}<a href="#mayorista">Mayorista</a></div></nav>
      </header>

      <section className="hero-v2" id="inicio" data-parallax>
        <div className="hero-backdrops" aria-hidden="true">{heroBackdrops.map((slide, index) => <div key={slide.image} className={index === heroSlide ? "hero-backdrop active" : "hero-backdrop"}><Image unoptimized src={slide.image} fill sizes="100vw" alt="" priority={index === 0} /></div>)}</div>
        <div className="hero-backdrop-overlay" />
        <div className="hero-orbit orbit-one" /><div className="hero-orbit orbit-two" />
        <div className="shell hero-v2-grid">
          <div className="hero-v2-copy"><span className="eyebrow">Escolar · arte · oficina</span><h1>Todo lo que tu día necesita, <em>en una sola lista.</em></h1><p>Desde el primer cuaderno hasta la oficina completa. Compra por producto, envíanos tu lista o cotiza por volumen.</p><div className="hero-buttons"><button className="primary" onClick={() => scrollToCatalog()}>Explorar productos <span>→</span></button><button className="secondary" onClick={() => setQuoteOpen(true)}>Cotizar mi lista</button></div><div className="hero-proof"><span>✓ Despacho coordinado</span><span>✓ Atención real</span><span>✓ Precios por volumen</span></div></div>
          <div className="hero-collage" aria-label="Selección de productos Tu Lista"><div className="collage-back" /><Image className="collage-bag" unoptimized src="/images/prod-mochila.png" width={500} height={500} alt="Mochila escolar" priority /><Image className="collage-books" unoptimized src="/images/prod-cuadernos.png" width={320} height={280} alt="Cuadernos" priority /><Image className="collage-pencils" unoptimized src="/images/prod-lapices.png" width={280} height={250} alt="Lápices de colores" priority /><div className="collage-note"><b>{heroBackdrops[heroSlide].label}</b><span>Todo listo, sin vueltas</span></div></div>
          <div className="hero-pagination" aria-label="Imágenes destacadas">{heroBackdrops.map((slide, index) => <button key={slide.image} className={index === heroSlide ? "active" : ""} onClick={() => setHeroSlide(index)} aria-label={`Ver ${slide.label}`}><span>{String(index + 1).padStart(2, "0")}</span></button>)}</div>
        </div>
      </section>

      <div className="color-ticker" aria-label="Categorías"><div><span>Cuadernos</span><i>✦</i><span>Arte y color</span><i>✦</i><span>Oficina</span><i>✦</i><span>Listas escolares</span><i>✦</i><span>Mayoristas</span></div></div>

      <section className="catalog shell" id="catalogo">
        <div className="section-heading"><div><span className="eyebrow">Productos destacados</span><h2>Resuelve tu lista en minutos</h2><p>Compra por unidad, arma tu pedido o solicita una cotización completa.</p></div><a href="#catalogo">Ver todo el catálogo →</a></div>
        <div className="filters"><button className={category === "Todos" ? "active" : ""} onClick={() => setCategory("Todos")}>Todos</button>{["Escolar", "Oficina", "Arte", "Organización"].map(item => <button className={category === item ? "active" : ""} key={item} onClick={() => setCategory(item)}>{item}</button>)}</div>
        <div className="product-grid">
          {visible.map(product => <article className="product-card" key={product.id}><div className="product-image"><span>{product.tag}</span><Image unoptimized src={product.image} fill sizes="(max-width: 700px) 50vw, 25vw" alt={product.name} /></div><div className="product-kicker"><small>{product.brand}</small><i>Disponible</i></div><h3>{product.name}</h3><p className="product-sku">SKU {product.sku}</p><div className="price-row"><strong>{money.format(product.price)}</strong></div><button className="product-add" onClick={() => add(product.id)}>Agregar al carrito <span>+</span></button></article>)}
        </div>
        {!visible.length && <p className="empty">No encontramos productos con esa búsqueda. Prueba con otra palabra.</p>}
      </section>

      <section className="category-strip shell" aria-label="Categorías destacadas">
        {categories.map((item) => <button key={item.name} className="category-card" onClick={() => item.name === "Mayorista" ? document.getElementById("mayorista")?.scrollIntoView({ behavior: "smooth" }) : scrollToCatalog(item.name)}><Image unoptimized src={item.image} width={180} height={120} alt="" /><span>{item.name}</span><b>›</b></button>)}
      </section>

      <section className="benefits shell"><div><b>▣</b><span><strong>Envíos a todo Chile</strong><small>Rápidos y seguros</small></span></div><div><b>%</b><span><strong>Precios mayoristas</strong><small>Descuentos por volumen</small></span></div><div><b>✓</b><span><strong>Compra confiable</strong><small>Asesoría antes de pagar</small></span></div><div><b>◎</b><span><strong>Atención personalizada</strong><small>Te ayudamos a elegir</small></span></div></section>

      <section className="seo-section creative-editorial" data-parallax>
        <div className="shell seo-grid">
          <div className="seo-image"><Image className="editorial-image" unoptimized src="/images/hero-products.png" fill sizes="(max-width: 800px) 100vw, 48vw" alt="Selección de útiles escolares, papelería y productos de oficina" /><span className="image-caption">Elegir · combinar · crear</span></div>
          <div><span className="eyebrow">Una compra, múltiples soluciones</span><h2>Útiles escolares y de oficina para cada etapa del año.</h2><p>En Tu Lista encuentras cuadernos, lápices, materiales de arte, resmas, carpetas, etiquetas y kits escolares. Reunimos productos esenciales para familias, colegios, empresas y librerías que necesitan comprar de forma simple y ordenada.</p><ul><li><b>Temporada escolar:</b> productos por unidad y listas completas.</li><li><b>Oficinas y empresas:</b> reposición frecuente y facturación.</li><li><b>Mayoristas:</b> atención comercial y precios por volumen.</li></ul><button className="primary" onClick={() => scrollToCatalog()}>Descubrir el catálogo</button></div>
        </div>
      </section>

      <section className="how shell"><div className="section-heading"><div><span className="eyebrow">Fácil de principio a fin</span><h2>Tu pedido en tres pasos</h2></div></div><div className="steps"><article><b>1</b><h3>Elige o sube tu lista</h3><p>Compra desde el catálogo o envíanos el documento completo.</p></article><article><b>2</b><h3>Revisamos disponibilidad</h3><p>Confirmamos productos, cantidades, alternativas y despacho.</p></article><article><b>3</b><h3>Recibe tu pedido</h3><p>Coordinamos la entrega para que tengas todo a tiempo.</p></article></div></section>

      <section className="wholesale" id="mayorista"><div className="shell wholesale-inner"><div><span className="eyebrow">Canal empresas y mayoristas</span><h2>Abastecimiento simple para compras grandes.</h2><p>Atendemos colegios, oficinas, instituciones y librerías con precios por volumen, respuesta rápida y pedidos organizados.</p><div className="wholesale-tags"><span>Facturación</span><span>Precios por volumen</span><span>Atención dedicada</span></div><button className="yellow" onClick={() => setQuoteOpen(true)}>Solicitar cotización</button></div><div className="wholesale-card"><strong>¿Tienes una lista?</strong><p>Envíala en PDF, fotografía o Excel. Nosotros ordenamos los productos y preparamos la cotización.</p><button onClick={() => setQuoteOpen(true)}>Subir mi lista →</button></div></div></section>

      <section className="faq shell"><div><span className="eyebrow">Preguntas frecuentes</span><h2>Comprar informado también es comprar mejor.</h2><p>Resolvemos las dudas más comunes antes de que armes tu pedido.</p><button className="secondary" onClick={() => setQuoteOpen(true)}>Hablar con un asesor</button></div><div className="faq-list"><details open><summary>¿Puedo enviar una foto de mi lista escolar?<b>+</b></summary><p>Sí. Puedes subir una fotografía clara, PDF o Excel. Revisamos el contenido y te respondemos con disponibilidad y valores.</p></details><details><summary>¿Venden a empresas, colegios y librerías?<b>+</b></summary><p>Sí. Contamos con atención para compras institucionales, reposición y pedidos por volumen.</p></details><details><summary>¿Realizan despachos fuera de la ciudad?<b>+</b></summary><p>Coordinamos envíos según destino, tamaño del pedido y disponibilidad. Confirma cobertura al cotizar.</p></details></div></section>

      <section className="final-cta"><div className="shell"><div><span>¿Tienes una lista por resolver?</span><h2>Envíala hoy y te ayudamos a ordenar la compra.</h2></div><button className="yellow" onClick={() => setQuoteOpen(true)}>Cotizar mi lista <span>→</span></button></div></section>

      <footer><div className="shell footer-grid"><div><Image src="/images/logo.png" width={150} height={46} alt="Tu Lista" /><p>Útiles escolares, oficina, arte y compras por volumen en un solo lugar.</p></div><div><strong>Tienda</strong><a href="#catalogo">Productos</a><button onClick={() => setQuoteOpen(true)}>Cotizar lista</button><a href="#mayorista">Mayoristas</a></div><div><strong>Ayuda</strong><a href="#inicio">Despachos</a><a href="#inicio">Preguntas frecuentes</a><a href="#inicio">Contacto</a></div><div><strong>Hablemos</strong><a href="mailto:contacto@tulista.cl">contacto@tulista.cl</a><p>Lunes a viernes<br />09:00 a 18:00</p></div></div><div className="shell copyright">© 2026 Tu Lista. Todos los derechos reservados.</div></footer>

      {cartOpen && <><button className="overlay" onClick={() => setCartOpen(false)} aria-label="Cerrar carrito" /><aside className="drawer"><div className="drawer-head"><h2>Tu carrito</h2><button onClick={() => setCartOpen(false)}>×</button></div>{itemCount === 0 ? <div className="cart-empty"><b>Tu carrito está vacío</b><p>Agrega productos y arma tu pedido.</p></div> : <div className="cart-items">{products.filter(p => cart[p.id]).map(p => <div className="cart-item" key={p.id}><Image src={p.image} width={70} height={55} alt="" /><span><strong>{p.name}</strong><small>{cart[p.id]} × {money.format(p.price)}</small></span><button onClick={() => setCart(c => ({ ...c, [p.id]: Math.max(0, c[p.id] - 1) }))}>−</button></div>)}</div>}<div className="drawer-total"><span>Subtotal estimado</span><strong>{money.format(subtotal)}</strong></div><button className="primary full">Continuar pedido</button></aside></>}

      {quoteOpen && <><button className="overlay" onClick={() => setQuoteOpen(false)} aria-label="Cerrar cotización" /><section className="quote-modal"><button className="modal-close" onClick={() => setQuoteOpen(false)}>×</button><span className="eyebrow">Cotización asistida</span><h2>Envíanos tu lista</h2><p>Aceptamos PDF, Excel o una fotografía clara. Te responderemos con disponibilidad y valores.</p><label className="upload"><input type="file" accept=".pdf,.xlsx,.xls,image/*" /><b>↑</b><strong>Seleccionar archivo</strong><small>PDF, Excel o imagen · máximo 10 MB</small></label><div className="quote-fields"><input placeholder="Nombre y apellido" /><input placeholder="Correo o teléfono" /></div><button className="primary full" onClick={() => setQuoteOpen(false)}>Solicitar cotización</button></section></>}
    </main>
  );
}
