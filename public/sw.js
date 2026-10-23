const CACHE_NAME = 'absensi-lhk-v2';
const urlsToCache = [
  '/manifest.json'
];

self.addEventListener('install', function(event) {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(function(cache) {
      return cache.addAll(urlsToCache);
    })
  );
});

self.addEventListener('activate', function(event) {
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    }).then(function() {
      return self.clients.claim();
    })
  );
});

self.addEventListener('fetch', function(event) {
  // JANGAN PERNAH cache halaman HTML atau Livewire request!
  const url = new URL(event.request.url);
  if (
    event.request.method !== 'GET' || 
    event.request.mode === 'navigate' || 
    url.pathname.startsWith('/livewire') || 
    url.pathname.startsWith('/mobile')
  ) {
    return;
  }

  event.respondWith(
    fetch(event.request).catch(function() {
      return caches.match(event.request);
    })
  );
});
