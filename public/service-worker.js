self.addEventListener('install', (event) => {
  console.log('[SW] install');
  event.waitUntil(
    caches.open('botble-offline-cache').then((cache) => {
      // Cache the offline route and common logo assets so the offline page can show branding
      const toCache = ['/offline', '/offline.html', '/storage/saniso.png', '/storage/saniso-300x300.png'];
      return cache.addAll(toCache).then(() => {
        console.log('[SW] cached offline assets:', toCache);
      }).catch((err) => {
        console.warn('[SW] some offline assets failed to cache', err);
      });
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  console.log('[SW] activate');
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
  const { request } = event;

  // Determine if the request expects HTML (navigation requests or Accept header)
  const acceptsHTML = request.mode === 'navigate' || (request.headers && request.headers.get('accept') && request.headers.get('accept').includes('text/html'));

  if (acceptsHTML) {
    // For navigation/HTML requests, try network then fallback to offline page
    event.respondWith(
      fetch(request).catch(() => {
        console.log('[SW] navigation request failed, serving offline page for:', request.url);
        return caches.match('/offline').then((res) => res || caches.match('/offline.html'));
      })
    );
    return;
  }

  // For other resources (CSS/JS/images), try network then cache; if not available, return a 503 minimal response
  event.respondWith(
    fetch(request).then((response) => {
      return response;
    }).catch(() => caches.match(request).then((cached) => {
      if (cached) return cached;
      return new Response(null, { status: 503, statusText: 'Service Unavailable' });
    }))
  );
});
