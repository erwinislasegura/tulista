(function () {
  if (!('serviceWorker' in navigator)) {
    return;
  }

  function getServiceWorkerPath() {
    const base = document.querySelector('base')?.getAttribute('href') || '/';
    const normalizedBase = base.endsWith('/') ? base : `${base}/`;
    return `${normalizedBase}sw.js`;
  }

  window.addEventListener('load', () => {
    const swPath = getServiceWorkerPath();
    navigator.serviceWorker.register(swPath).catch(() => {
      // El registro PWA no debe bloquear la navegación principal.
    });
  });
})();
