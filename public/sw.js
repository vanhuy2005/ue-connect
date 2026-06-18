const CACHE_NAME = 'ue-connect-pwa-v2';
const OFFLINE_URL = '/offline.html';

// Assets that should be cached immediately on install
const APP_SHELL = [
    OFFLINE_URL,
    '/manifest.json',
    '/images/brand/ueconnect-mark-nobg.png',
    '/images/brand/app-icon-nobg.png',
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(APP_SHELL);
        })
    );
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
    const url = new URL(event.request.url);

    if (url.origin !== location.origin) {
        return;
    }

    if (
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/livewire/') ||
        /^\/livewire-[^/]+\//.test(url.pathname) ||
        event.request.method !== 'GET'
    ) {
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return caches.match(OFFLINE_URL);
                })
        );
        return;
    }

    if (
        event.request.destination === 'script' ||
        event.request.destination === 'style' ||
        event.request.destination === 'image' ||
        event.request.destination === 'font'
    ) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                const fetchPromise = fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(() => {});

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => caches.match(OFFLINE_URL))
    );
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Push Notifications Setup (existing)
self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (e.data) {
        var msg = e.data.json();
        e.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon || '/icons/icon-192x192.png',
            badge: msg.badge || '/icons/icon-72x72.png',
            data: { url: msg.url },
            tag: msg.tag || 'ue-connect',
        }));
    }
});

self.addEventListener('notificationclick', function (e) {
    e.notification.close();
    if (e.notification.data && e.notification.data.url) {
        e.waitUntil(clients.openWindow(e.notification.data.url));
    }
});
