
const whatsappNumber = '569XXXXXXXX';
const defaultProducts = [{"id": 1, "name": "Resma carta 500 hojas", "cat": "Oficina", "price": 4790, "old": 5690, "img": "pagina/assets/images/prod-resma.png", "tag": "Más vendido", "desc": "Papel para impresión diaria, colegio y oficina."}, {"id": 2, "name": "Cuaderno universitario 100 hojas", "cat": "Escolar", "price": 1890, "old": 0, "img": "pagina/assets/images/prod-cuadernos.png", "tag": "Escolar", "desc": "Para clases, apuntes y uso diario."}, {"id": 3, "name": "Set lápices grafito x12", "cat": "Escritura", "price": 2490, "old": 2990, "img": "pagina/assets/images/prod-lapices.png", "tag": "Pack", "desc": "Lápices básicos para colegio y escritorio."}, {"id": 4, "name": "Témpera escolar 12 colores", "cat": "Arte", "price": 3990, "old": 4990, "img": "pagina/assets/images/prod-pinturas.png", "tag": "Arte", "desc": "Colores vivos para manualidades y trabajos escolares."}, {"id": 5, "name": "Carpeta oficio con acoclip", "cat": "Oficina", "price": 590, "old": 0, "img": "pagina/assets/images/prod-carpetas.png", "tag": "Unidad", "desc": "Organización para tareas, trámites y documentos."}, {"id": 6, "name": "Goma eva colores surtidos", "cat": "Arte", "price": 1990, "old": 0, "img": "pagina/assets/images/prod-gomaeva.png", "tag": "Manualidad", "desc": "Material flexible para proyectos creativos."}, {"id": 7, "name": "Mochila escolar resistente", "cat": "Mochilas", "price": 15990, "old": 18990, "img": "pagina/assets/images/prod-mochila.png", "tag": "Nuevo", "desc": "Compartimentos cómodos para clases y útiles."}, {"id": 8, "name": "Pack oficina mensual", "cat": "Oficina", "price": 34990, "old": 39990, "img": "pagina/assets/images/prod-oficina.png", "tag": "Empresa", "desc": "Insumos de alta rotación para oficina."}, {"id": 9, "name": "Libro lectura complementaria", "cat": "Librería", "price": 6990, "old": 0, "img": "pagina/assets/images/prod-libros.png", "tag": "Lectura", "desc": "Selección para apoyo escolar y biblioteca."}, {"id": 10, "name": "Set geometría escolar", "cat": "Escolar", "price": 1990, "old": 2490, "img": "pagina/assets/images/prod-geometria.png", "tag": "Kit", "desc": "Regla, escuadras y transportador."}, {"id": 11, "name": "Papel adhesivo A4 100 hojas", "cat": "Papelería", "price": 15320, "old": 0, "img": "pagina/assets/images/prod-etiquetas.png", "tag": "Etiquetas", "desc": "Para rotulación, etiquetas y organización."}, {"id": 12, "name": "Kit escolar básico 1º ciclo", "cat": "Escolar", "price": 24990, "old": 29990, "img": "pagina/assets/images/prod-kit.png", "tag": "Kit listo", "desc": "Pack referencial para resolver rápido la lista."}];
const defaultCategories = ['Todos','Escolar','Oficina','Arte','Papelería','Escritura','Librería','Mochilas'];
const products = Array.isArray(window.TULISTA_PRODUCTS) ? window.TULISTA_PRODUCTS : defaultProducts;
const categories = ['Todos', ...new Set((Array.isArray(window.TULISTA_CATEGORIES) ? window.TULISTA_CATEGORIES : defaultCategories.slice(1)).filter(Boolean))];
let cart = [];
let activeCategory = 'Todos';
let searchTerm = '';
let currentPage = 1;
let pageSize = 12;
let searchTimer = 0;
const productQuantities = new Map();

const money = new Intl.NumberFormat('es-CL', {style:'currency', currency:'CLP', maximumFractionDigits:0});

function setActiveNav(){
  const current = location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('[data-nav]').forEach(a=>{
    if(a.getAttribute('href') === current) a.classList.add('active');
  });
}

function toggleMega(){
  const menu = document.getElementById('megaMenu');
  if(menu) menu.classList.toggle('active');
}

function closeMega(){
  const menu = document.getElementById('megaMenu');
  if(menu) menu.classList.remove('active');
}

function renderTabs(){
  const tabs = document.getElementById('tabs');
  if(!tabs) return;
  tabs.innerHTML = categories.map(c=>`<button class="tab ${c===activeCategory?'active':''}" data-cat="${c}">${c}</button>`).join('');
  tabs.querySelectorAll('[data-cat]').forEach(btn=>btn.addEventListener('click',()=>setCategory(btn.dataset.cat)));
}

function renderSideCategories(){
  const el = document.getElementById('sideCategories');
  if(!el) return;
  el.innerHTML = categories.map(c=>`<button class="filter-btn ${c===activeCategory?'active':''}" data-cat="${c}">${c} <span>${c==='Todos'?products.length:products.filter(p=>p.cat===c).length}</span></button>`).join('');
  el.querySelectorAll('[data-cat]').forEach(btn=>btn.addEventListener('click',()=>setCategory(btn.dataset.cat)));
}

function setCategory(cat){
  activeCategory = cat;
  currentPage = 1;
  searchTerm = '';
  const search = document.getElementById('globalSearch');
  if(search) search.value = '';
  renderTabs();
  renderSideCategories();
  renderProducts();
  closeMega();
  const target = document.getElementById('productos');
  if(target) target.scrollIntoView({behavior:'smooth', block:'start'});
}

function filteredProducts(){
  let list = products.filter(p => (activeCategory==='Todos' || p.cat===activeCategory) && (
    p.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    p.cat.toLowerCase().includes(searchTerm.toLowerCase()) ||
    p.desc.toLowerCase().includes(searchTerm.toLowerCase())
  ));
  const sort = document.getElementById('sortSelect')?.value || 'featured';
  if(sort==='priceAsc') list.sort((a,b)=>a.price-b.price);
  if(sort==='priceDesc') list.sort((a,b)=>b.price-a.price);
  if(sort==='name') list.sort((a,b)=>a.name.localeCompare(b.name));
  if(sort==='featured') list.sort((a,b)=>(b.old>0)-(a.old>0));
  return list;
}

function normalizeQty(value, fallback = 1){
  const qty = parseInt(value, 10);
  return Number.isFinite(qty) && qty > 0 ? qty : fallback;
}

function getProductQty(id){
  return normalizeQty(productQuantities.get(id), 1);
}

function setProductQty(id, value){
  const qty = normalizeQty(value, 1);
  productQuantities.set(id, qty);
  const el = document.getElementById('qty-'+id);
  if(el) el.value = qty;
  const modalQty = document.getElementById('modalQty');
  if(modalQty && Number(modalQty.dataset.productId) === Number(id)) modalQty.value = qty;
  return qty;
}

function handleQuantityInput(id, value){
  const qty = parseInt(value, 10);
  if(Number.isFinite(qty) && qty > 0) productQuantities.set(id, qty);
}

function commitProductQty(id, value){
  return setProductQty(id, value);
}

function qtyValue(id){
  const el = document.getElementById('qty-'+id);
  return setProductQty(id, el?.value ?? getProductQty(id));
}

function changeCardQty(id, delta){
  setProductQty(id, getProductQty(id) + delta);
}

function renderProducts(){
  const grid = document.getElementById('productGrid');
  if(!grid) return;
  const list = filteredProducts();
  const totalPages = Math.max(1, Math.ceil(list.length / pageSize));
  currentPage = Math.min(currentPage, totalPages);
  const start = (currentPage - 1) * pageSize;
  const pageItems = list.slice(start, start + pageSize);
  const resultNote = document.getElementById('resultNote');
  if(resultNote) resultNote.textContent = list.length ? `Mostrando ${start + 1}–${Math.min(start + pageSize, list.length)} de ${list.length} productos${activeCategory!=='Todos'?' en '+activeCategory:''}.` : 'No encontramos coincidencias. Prueba otra búsqueda o envíanos tu lista.';
  grid.innerHTML = pageItems.map(p=>`
    <article class="product-card">
      <div class="product-image" onclick="openModal(${p.id})">
        <span class="badge">${p.tag}</span>
        ${p.old ? `<span class="discount">-${Math.round((1-p.price/p.old)*100)}%</span>` : ''}
        <img src="${p.img}" alt="${p.name}">
      </div>
      <div class="product-info">
        <div class="product-meta"><span>${p.cat}</span><span>Stock</span></div>
        <h3 class="product-title">${p.name}</h3>
        <p class="product-desc">${p.desc}</p>
        <div class="price-row"><span class="price">${money.format(p.price)}</span>${p.old ? `<span class="old-price">${money.format(p.old)}</span>` : ''}</div>
        <div class="card-actions">
          <div class="qty-box"><button type="button" onclick="changeCardQty(${p.id},-1)">−</button><input id="qty-${p.id}" type="number" min="1" step="1" inputmode="numeric" value="${getProductQty(p.id)}" onchange="commitProductQty(${p.id}, this.value)" onblur="commitProductQty(${p.id}, this.value)" oninput="handleQuantityInput(${p.id}, this.value)"><button type="button" onclick="changeCardQty(${p.id},1)">+</button></div>
          <button class="add-btn" onclick="addToCart(${p.id}, qtyValue(${p.id}))">Agregar</button>
        </div>
        <button class="whatsapp-card" onclick="consultProduct(${p.id})">Consultar por WhatsApp</button>
      </div>
    </article>`).join('');
  renderPagination(totalPages);
}

function renderPagination(totalPages){
  const nav = document.getElementById('catalogPagination');
  if(!nav) return;
  if(totalPages <= 1){ nav.innerHTML = ''; return; }
  const from = Math.max(1, currentPage - 2);
  const to = Math.min(totalPages, currentPage + 2);
  let html = `<button ${currentPage===1?'disabled':''} data-page="${currentPage-1}">← Anterior</button>`;
  for(let page=from; page<=to; page++) html += `<button class="${page===currentPage?'active':''}" data-page="${page}" aria-current="${page===currentPage?'page':'false'}">${page}</button>`;
  html += `<button ${currentPage===totalPages?'disabled':''} data-page="${currentPage+1}">Siguiente →</button>`;
  nav.innerHTML = html;
  nav.querySelectorAll('[data-page]').forEach(button=>button.addEventListener('click',()=>{currentPage=Number(button.dataset.page);renderProducts();document.getElementById('productos')?.scrollIntoView({behavior:'smooth'});}));
}

function renderSearchSuggestions(value){
  const panel = document.getElementById('searchSuggestions');
  const input = document.getElementById('globalSearch');
  if(!panel || !input) return;
  const term = value.trim().toLowerCase();
  if(term.length < 2){ panel.classList.remove('open'); panel.innerHTML=''; input.setAttribute('aria-expanded','false'); return; }
  const matches = products.filter(p=>`${p.name} ${p.cat} ${p.desc}`.toLowerCase().includes(term)).slice(0,6);
  panel.innerHTML = matches.length ? matches.map(p=>`<button type="button" role="option" data-product-search="${p.name.replace(/"/g,'&quot;')}"><img src="${p.img}" alt=""><span><strong>${p.name}</strong><small>${p.cat} · ${p.desc}</small></span><b>${money.format(p.price)}</b></button>`).join('') + `<button type="button" class="all-results" data-all-results>Ver todos los resultados para “${value}” →</button>` : `<div class="no-suggestions"><strong>Sin coincidencias directas</strong><span>Prueba otra palabra o envíanos tu lista para cotizar.</span></div>`;
  panel.classList.add('open'); input.setAttribute('aria-expanded','true');
  panel.querySelectorAll('[data-product-search]').forEach(button=>button.addEventListener('click',()=>{input.value=button.dataset.productSearch;searchTerm=button.dataset.productSearch;currentPage=1;panel.classList.remove('open');renderProducts();document.getElementById('productos')?.scrollIntoView({behavior:'smooth'});}));
  panel.querySelector('[data-all-results]')?.addEventListener('click',()=>{searchTerm=value;currentPage=1;panel.classList.remove('open');renderProducts();document.getElementById('productos')?.scrollIntoView({behavior:'smooth'});});
}

function addToCart(id, qty=1){
  const p = products.find(x=>x.id===id);
  if(!p) return;
  qty = normalizeQty(qty, 1);
  const existing = cart.find(i=>i.id===id);
  if(existing) existing.qty += qty;
  else cart.push({...p, qty});
  renderCart();
  openCart();
}

function updateQty(id, delta){
  const item = cart.find(i=>i.id===id);
  if(!item) return;
  item.qty += delta;
  if(item.qty <= 0) cart = cart.filter(i=>i.id!==id);
  renderCart();
}

function removeItem(id){
  cart = cart.filter(i=>i.id!==id);
  renderCart();
}

function renderCart(){
  const count = cart.reduce((s,i)=>s+i.qty,0);
  const subtotal = cart.reduce((s,i)=>s+i.price*i.qty,0);
  const countEl = document.getElementById('cartCount');
  const subtotalEl = document.getElementById('cartSubtotal');
  const bodyEl = document.getElementById('cartBody');
  if(countEl) countEl.textContent = count;
  if(subtotalEl) subtotalEl.textContent = money.format(subtotal);
  if(bodyEl) {
    bodyEl.innerHTML = cart.length ? cart.map(i=>`
      <div class="cart-item">
        <img src="${i.img}" alt="${i.name}">
        <div><h4>${i.name}</h4><p>${money.format(i.price)} · ${i.cat}</p><div class="qty"><button onclick="updateQty(${i.id},-1)">−</button><span>${i.qty}</span><button onclick="updateQty(${i.id},1)">+</button></div></div>
        <button class="remove" onclick="removeItem(${i.id})">×</button>
      </div>`).join('') : '<div class="cart-empty">Tu carrito está vacío. Agrega productos o solicita una cotización por WhatsApp.</div>';
  }
  const text = cart.length ? 'Hola Tu Lista, quiero cotizar este pedido:%0A%0A' + cart.map(i=>`• ${i.qty} x ${i.name} - ${money.format(i.price*i.qty)}`).join('%0A') + `%0A%0ASubtotal estimado: ${money.format(subtotal)}` : 'Hola Tu Lista, quiero cotizar productos.';
  const checkout = document.getElementById('checkoutWhatsApp');
  if(checkout) checkout.href = `https://wa.me/${whatsappNumber}?text=${text}`;
}

function openCart(){
  document.getElementById('cartDrawer')?.classList.add('active');
  document.getElementById('drawerOverlay')?.classList.add('active');
}
function closeCart(){
  document.getElementById('cartDrawer')?.classList.remove('active');
  document.getElementById('drawerOverlay')?.classList.remove('active');
}
function consultProduct(id){
  const p = products.find(x=>x.id===id);
  window.open(`https://wa.me/${whatsappNumber}?text=Hola%20Tu%20Lista,%20quiero%20consultar%20por%20${encodeURIComponent(p.name)}`, '_blank');
}

function openModal(id){
  const p = products.find(x=>x.id===id);
  const modal = document.getElementById('modalContent');
  if(!modal) return;
  modal.innerHTML = `
    <div class="modal-img"><img src="${p.img}" alt="${p.name}"></div>
    <div class="modal-content">
      <span class="modal-kicker">${p.cat}</span>
      <h3>${p.name}</h3>
      <p class="modal-desc">${p.desc} Disponible para compra por unidad, lista escolar o pedido por volumen.</p>
      <div class="modal-price-box">
        <div><small>Precio unitario</small><div class="price-row"><span class="price" style="font-size:30px">${money.format(p.price)}</span>${p.old ? `<span class="old-price">${money.format(p.old)}</span>` : ''}</div></div>
        ${p.old ? `<span class="badge">Ahorra ${money.format(p.old-p.price)}</span>` : ''}
      </div>
      <div class="modal-info-grid">
        <div class="modal-info"><strong>Compra flexible</strong><span>Pide desde 1 unidad o ajusta la cantidad.</span></div>
        <div class="modal-info"><strong>Ideal para</strong><span>Escolar, oficina, listas y mayoristas.</span></div>
      </div>
      <div class="card-actions">
        <div class="qty-box"><button type="button" onclick="changeModalQty(-1)">−</button><input id="modalQty" type="number" min="1" step="1" inputmode="numeric" value="${getProductQty(p.id)}" data-product-id="${p.id}" onchange="commitProductQty(${p.id}, this.value)" onblur="commitProductQty(${p.id}, this.value)" oninput="handleQuantityInput(${p.id}, this.value)"><button type="button" onclick="changeModalQty(1)">+</button></div>
        <button class="add-btn" onclick="addToCart(${p.id}, commitProductQty(${p.id}, document.getElementById('modalQty')?.value)); closeModal();">Agregar</button>
      </div>
      <a class="btn ghost full" href="https://wa.me/${whatsappNumber}?text=Hola%20Tu%20Lista,%20quiero%20consultar%20por%20${encodeURIComponent(p.name)}" target="_blank">Consultar por WhatsApp</a>
    </div>`;
  document.getElementById('productModal')?.classList.add('active');
  document.getElementById('modalOverlay')?.classList.add('active');
}
function closeModal(){
  document.getElementById('productModal')?.classList.remove('active');
  document.getElementById('modalOverlay')?.classList.remove('active');
}
function changeModalQty(delta){
  const el = document.getElementById('modalQty');
  if(!el) return;
  const id = Number(el.dataset.productId);
  if(id) setProductQty(id, normalizeQty(el.value, 1) + delta);
  else el.value = normalizeQty(el.value, 1) + delta;
}

function setupHeroParallax(){
  const hero = document.querySelector('.hero');
  const bg = document.getElementById('heroParallax');
  const slides = bg ? Array.from(bg.querySelectorAll('.hero-slide')) : [];
  if(!hero || !bg || !slides.length) return;

  let current = 0;
  window.setInterval(()=>{
    slides[current].classList.remove('is-active');
    current = (current + 1) % slides.length;
    slides[current].classList.add('is-active');
  }, 4200);

  if(window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  hero.addEventListener('pointermove', event=>{
    const rect = hero.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width - .5) * -24;
    const y = ((event.clientY - rect.top) / rect.height - .5) * -18;
    bg.style.setProperty('--hero-parallax-x', `${x.toFixed(2)}px`);
    bg.style.setProperty('--hero-parallax-y', `${y.toFixed(2)}px`);
  });

  hero.addEventListener('pointerleave', ()=>{
    bg.style.setProperty('--hero-parallax-x', '0px');
    bg.style.setProperty('--hero-parallax-y', '0px');
  });
}

function setupForms(){
  document.querySelectorAll('[data-whatsapp-form]').forEach(form=>{
    form.addEventListener('submit', e=>{
      e.preventDefault();
      const data = new FormData(form);
      let text = form.dataset.message || 'Hola Tu Lista, quiero solicitar información.';
      for(const [key,value] of data.entries()) {
        if(String(value).trim()) text += `%0A${encodeURIComponent(key)}: ${encodeURIComponent(String(value).trim())}`;
      }
      window.open(`https://wa.me/${whatsappNumber}?text=${text}`, '_blank');
    });
  });
}

function setupFAQ(){
  document.querySelectorAll('.faq-q').forEach(q=>{
    q.addEventListener('click',()=>q.closest('.faq-item').classList.toggle('active'));
  });
}

function setupImmersiveParallax(){
  const sections = [...document.querySelectorAll('[data-parallax]')];
  if(!sections.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  let frame = 0;
  const update = ()=>{
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(()=>{
      sections.forEach(section=>{
        const rect = section.getBoundingClientRect();
        const progress = (window.innerHeight - rect.top) / (window.innerHeight + rect.height);
        section.style.setProperty('--parallax', Math.max(0, Math.min(1, progress)).toFixed(3));
      });
    });
  };
  update();
  window.addEventListener('scroll', update, {passive:true});
}

document.addEventListener('DOMContentLoaded',()=>{
  setActiveNav();
  renderTabs();
  renderSideCategories();
  renderProducts();
  renderCart();
  setupForms();
  setupFAQ();
  setupHeroParallax();
  setupImmersiveParallax();
  document.querySelectorAll('[data-cat-link]').forEach(el=>el.addEventListener('click',e=>{e.preventDefault(); location.href = '#productos';}));
  document.querySelectorAll('[data-filter]').forEach(el=>el.addEventListener('click',e=>{e.preventDefault(); setCategory(el.dataset.filter);}));
  document.getElementById('globalSearch')?.addEventListener('input',e=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>{searchTerm=e.target.value;activeCategory='Todos';currentPage=1;renderTabs();renderSideCategories();renderProducts();renderSearchSuggestions(e.target.value);},280);});
  document.getElementById('globalSearch')?.addEventListener('keydown',e=>{if(e.key==='Escape'){document.getElementById('searchSuggestions')?.classList.remove('open');e.currentTarget.setAttribute('aria-expanded','false');}});
  document.getElementById('pageSizeSelect')?.addEventListener('change',e=>{pageSize=Number(e.target.value)||12;currentPage=1;renderProducts();});
  document.getElementById('sortSelect')?.addEventListener('change',renderProducts);
  document.getElementById('clearFilters')?.addEventListener('click',()=>{activeCategory='Todos';searchTerm='';const s=document.getElementById('globalSearch'); if(s) s.value=''; renderTabs(); renderSideCategories(); renderProducts();});
  document.getElementById('megaTrigger')?.addEventListener('click',toggleMega);
  document.getElementById('mobileMenuToggle')?.addEventListener('click',toggleMega);
  document.addEventListener('click',e=>{if(!e.target.closest('.mega-wrap') && !e.target.closest('#mobileMenuToggle')) closeMega();});
  document.getElementById('cartOpen')?.addEventListener('click',openCart);
  document.getElementById('cartClose')?.addEventListener('click',closeCart);
  document.getElementById('drawerOverlay')?.addEventListener('click',closeCart);
  document.getElementById('clearCart')?.addEventListener('click',()=>{cart=[];renderCart();});
  document.getElementById('modalClose')?.addEventListener('click',closeModal);
  document.getElementById('modalOverlay')?.addEventListener('click',closeModal);
  document.addEventListener('keydown',e=>{ if(e.key==='Escape'){closeCart();closeModal();closeMega();} });
});

window.addToCart=addToCart; window.changeCardQty=changeCardQty; window.setProductQty=setProductQty; window.handleQuantityInput=handleQuantityInput; window.commitProductQty=commitProductQty; window.qtyValue=qtyValue; window.updateQty=updateQty; window.removeItem=removeItem; window.openModal=openModal; window.changeModalQty=changeModalQty; window.consultProduct=consultProduct;
