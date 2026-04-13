/* Rentkia vendor area — minimal service worker for PWA install (no fetch interception). */
const CACHE = 'rentkia-vendor-v4';

self.addEventListener('install', (event) => {
    const base = new URL('.', self.location.href).href;
    const urls = [
        base + 'icons/icon-192.png',
        base + 'icons/icon-512.png',
    ];
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(urls)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

/* Do not intercept fetch: avoids broken navigations / dead clicks when cache has no match. */
self.addEventListener('fetch', (event) => {
    event.respondWith(fetch(event.request));
});
