"use client";

import Image from "next/image";
import { useMemo, useState } from "react";

const categories = [
  { name: "Escolar", image: "/images/prod-mochila.png" },
  { name: "Oficina", image: "/images/prod-oficina.png" },
  { name: "Arte", image: "/images/prod-pinturas.png" },
  { name: "Papelería", image: "/images/prod-etiquetas.png" },
  { name: "Organización", image: "/images/prod-carpetas.png" },
  { name: "Mayorista", image: "/images/prod-resma.png" },
];

const products = [
  { id: 1, name: "Kit escolar esencial", cat: "Escolar", price: 15990, image: "/images/prod-kit.png", tag: "Más vendido" },
  { id: 2, name: "Cuadernos universitarios", cat: "Escolar", price: 3490, image: "/images/prod-cuadernos.png", tag: "Nuevo" },
  { id: 3, name: "Set de lápices de colores", cat: "Arte", price: 5290, image: "/images/prod-lapices.png", tag: "Oferta" },
  { id: 4, name: "Resma carta 500 hojas", cat: "Oficina", price: 5990, image: "/images/prod-resma.png", tag: "Stock" },
  { id: 5, name: "Pack carpetas organizadoras", cat: "Organización", price: 8490, image: "/images/prod-carpetas.png", tag: "Empresas" },
  { id: 6, name: "Set de geometría", cat: "Escolar", price: 2990, image: "/images/prod-geometria.png", tag: "Stock" },
];

const money = new Intl.NumberFormat("es-CL", { style: "currency", currency: "CLP", maximumFractionDigits: 0 });

export default function Home() {
  const [query, setQuery] = useState("");
  const [category, setCategory] = useState("Todos");
  const [cart, setCart] = useState<Record<number, number>>({});
  const [cartOpen, setCartOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [quoteOpen, setQuoteOpen] = useState(false);

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
      <div className="utility"><span>▣ Envíos a todo Chile</span><span>Atención mayorista para empresas y colegios</span></div>
      <header>
        <div className="header-main shell">
          <a className="brand" href="#inicio" aria-label="Tu Lista, inicio"><Image unoptimized src="/images/logo.png" width={178} height={55} alt="Tu Lista" priority /></a>
          <label className="search"><span>⌕</span><input value={query} onChange={(event) => setQuery(event.target.value)} onFocus={() => scrollToCatalog()} placeholder="¿Qué producto buscas hoy?" aria-label="Buscar productos" /></label>
          <div className="header-actions"><button className="account">Mi cuenta</button><button className="cart-button" onClick={() => setCartOpen(true)}>Carrito <b>{itemCount}</b></button><button className="hamburger" onClick={() => setMenuOpen(!menuOpen)} aria-label="Abrir menú">☰</button></div>
        </div>
        <nav className={menuOpen ? "open" : ""}><div className="shell"><a href="#inicio">Inicio</a>{["Escolar", "Oficina", "Arte", "Papelería", "Organización"].map((item) => <button key={item} onClick={() => scrollToCatalog(item)}>{item}</button>)}<a href="#mayorista">Mayorista</a></div></nav>
      </header>

      <section className="hero" id="inicio">
        <Image unoptimized className="hero-image" src="/images/hero-products.jpg" fill sizes="100vw" alt="Mochila, cuadernos y útiles escolares y de oficina" priority />
        <div className="shell hero-inner">
          <div className="hero-copy"><span className="eyebrow">Escolar · oficina · empresas</span><h1>Todo lo que necesitas, <em>en una sola lista.</em></h1><p>Compra útil, rápido y sin vueltas. Para familias, colegios y empresas de todo Chile.</p><div className="hero-buttons"><button className="primary" onClick={() => scrollToCatalog()}>Ver productos</button><button className="secondary" onClick={() => setQuoteOpen(true)}>Cotizar mi lista</button></div><div className="hero-proof"><span>✓ Compra segura</span><span>✓ Atención personalizada</span></div></div>
        </div>
      </section>

      <section className="category-strip shell" aria-label="Categorías destacadas">
        {categories.map((item) => <button key={item.name} className="category-card" onClick={() => item.name === "Mayorista" ? document.getElementById("mayorista")?.scrollIntoView({ behavior: "smooth" }) : scrollToCatalog(item.name)}><Image unoptimized src={item.image} width={180} height={120} alt="" /><span>{item.name}</span><b>›</b></button>)}
      </section>

      <section className="benefits shell"><div><b>▣</b><span><strong>Envíos a todo Chile</strong><small>Rápidos y seguros</small></span></div><div><b>%</b><span><strong>Precios mayoristas</strong><small>Descuentos por volumen</small></span></div><div><b>✓</b><span><strong>Compra confiable</strong><small>Asesoría antes de pagar</small></span></div><div><b>◎</b><span><strong>Atención personalizada</strong><small>Te ayudamos a elegir</small></span></div></section>

      <section className="catalog shell" id="catalogo">
        <div className="section-heading"><div><span className="eyebrow">Productos destacados</span><h2>Resuelve tu lista en minutos</h2><p>Compra por unidad, arma tu pedido o solicita una cotización completa.</p></div><a href="#catalogo">Ver todo el catálogo →</a></div>
        <div className="filters"><button className={category === "Todos" ? "active" : ""} onClick={() => setCategory("Todos")}>Todos</button>{["Escolar", "Oficina", "Arte", "Organización"].map(item => <button className={category === item ? "active" : ""} key={item} onClick={() => setCategory(item)}>{item}</button>)}</div>
        <div className="product-grid">
          {visible.map(product => <article className="product-card" key={product.id}><div className="product-image"><span>{product.tag}</span><Image unoptimized src={product.image} fill sizes="(max-width: 700px) 50vw, 25vw" alt={product.name} /></div><small>{product.cat}</small><h3>{product.name}</h3><div className="price-row"><strong>{money.format(product.price)}</strong><button onClick={() => add(product.id)} aria-label={`Agregar ${product.name}`}>+</button></div></article>)}
        </div>
        {!visible.length && <p className="empty">No encontramos productos con esa búsqueda. Prueba con otra palabra.</p>}
      </section>

      <section className="wholesale" id="mayorista"><div className="shell wholesale-inner"><div><span className="eyebrow">Canal empresas y mayoristas</span><h2>Abastecimiento simple para compras grandes.</h2><p>Atendemos colegios, oficinas, instituciones y librerías con precios por volumen, respuesta rápida y pedidos organizados.</p><div className="wholesale-tags"><span>Facturación</span><span>Precios por volumen</span><span>Atención dedicada</span></div><button className="yellow" onClick={() => setQuoteOpen(true)}>Solicitar cotización</button></div><div className="wholesale-card"><strong>¿Tienes una lista?</strong><p>Envíala en PDF, fotografía o Excel. Nosotros ordenamos los productos y preparamos la cotización.</p><button onClick={() => setQuoteOpen(true)}>Subir mi lista →</button></div></div></section>

      <footer><div className="shell footer-grid"><div><Image src="/images/logo.png" width={150} height={46} alt="Tu Lista" /><p>Útiles escolares, oficina, arte y compras por volumen en un solo lugar.</p></div><div><strong>Tienda</strong><a href="#catalogo">Productos</a><button onClick={() => setQuoteOpen(true)}>Cotizar lista</button><a href="#mayorista">Mayoristas</a></div><div><strong>Ayuda</strong><a href="#inicio">Despachos</a><a href="#inicio">Preguntas frecuentes</a><a href="#inicio">Contacto</a></div><div><strong>Hablemos</strong><a href="mailto:contacto@tulista.cl">contacto@tulista.cl</a><p>Lunes a viernes<br />09:00 a 18:00</p></div></div><div className="shell copyright">© 2026 Tu Lista. Todos los derechos reservados.</div></footer>

      {cartOpen && <><button className="overlay" onClick={() => setCartOpen(false)} aria-label="Cerrar carrito" /><aside className="drawer"><div className="drawer-head"><h2>Tu carrito</h2><button onClick={() => setCartOpen(false)}>×</button></div>{itemCount === 0 ? <div className="cart-empty"><b>Tu carrito está vacío</b><p>Agrega productos y arma tu pedido.</p></div> : <div className="cart-items">{products.filter(p => cart[p.id]).map(p => <div className="cart-item" key={p.id}><Image src={p.image} width={70} height={55} alt="" /><span><strong>{p.name}</strong><small>{cart[p.id]} × {money.format(p.price)}</small></span><button onClick={() => setCart(c => ({ ...c, [p.id]: Math.max(0, c[p.id] - 1) }))}>−</button></div>)}</div>}<div className="drawer-total"><span>Subtotal estimado</span><strong>{money.format(subtotal)}</strong></div><button className="primary full">Continuar pedido</button></aside></>}

      {quoteOpen && <><button className="overlay" onClick={() => setQuoteOpen(false)} aria-label="Cerrar cotización" /><section className="quote-modal"><button className="modal-close" onClick={() => setQuoteOpen(false)}>×</button><span className="eyebrow">Cotización asistida</span><h2>Envíanos tu lista</h2><p>Aceptamos PDF, Excel o una fotografía clara. Te responderemos con disponibilidad y valores.</p><label className="upload"><input type="file" accept=".pdf,.xlsx,.xls,image/*" /><b>↑</b><strong>Seleccionar archivo</strong><small>PDF, Excel o imagen · máximo 10 MB</small></label><div className="quote-fields"><input placeholder="Nombre y apellido" /><input placeholder="Correo o teléfono" /></div><button className="primary full" onClick={() => setQuoteOpen(false)}>Solicitar cotización</button></section></>}
    </main>
  );
}
