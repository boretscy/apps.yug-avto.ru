const CACHE_NAME = 'otp-m-bank-v1';
const ASSETS = [
  '/pwa/',
  '/pwa/index.html',
  '/pwa/style.css',
  '/pwa/app.js',
  '/pwa/manifest.json',
  '/pwa/icon.png',
  '/pwa/icons/icon-192.png',
  '/pwa/icons/icon-512.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS);
    })
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      return cachedResponse || fetch(event.request);
    })
  );
});
