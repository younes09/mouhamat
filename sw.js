const CACHE_NAME = 'blida-lawyers-app-v3';
const ASSETS = [
  '/mouhamat/',
  '/mouhamat/index.php',
  '/mouhamat/manifest.json',
  '/mouhamat/style.css',
  '/mouhamat/js/state.js',
  '/mouhamat/js/api.js',
  '/mouhamat/js/ui.js',
  '/mouhamat/js/app.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
  'https://unpkg.com/lucide@latest',
  'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
  'https://cdn.jsdelivr.net/npm/flatpickr',
  'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js',
  'https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
        }
        // Cache dynamic assets if they are basic GETs
        const responseToCache = response.clone();
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, responseToCache);
        });
        return response;
      })
      .catch(() => {
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }
          if (event.request.mode === 'navigate') {
            return caches.match('/mouhamat/index.php');
          }
        });
      })
  );
});
