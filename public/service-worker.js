/**
 * خدمات المجمع - Unified Service Worker (PWA + Firebase)
 */

// 1. Import Firebase Scripts (Compat)
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// 2. Initialize Firebase
firebase.initializeApp({
    apiKey: "AIzaSyCf6DUu21s4qhbD2fLkSypCRDkSnCbzQCo",
    authDomain: "garden-city-compound.firebaseapp.com",
    projectId: "garden-city-compound",
    storageBucket: "garden-city-compound.firebasestorage.app",
    messagingSenderId: "358088420520",
    appId: "1:358088420520:web:7cdd88dc6d98d2c6f0269d"
});

const messaging = firebase.messaging();

// 3. Handle Background Messages
messaging.onBackgroundMessage((payload) => {
    console.log('[SW] Background message received:', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/icons/icon-192x192.png',
        badge: '/icons/badge-72x72.png',
        data: payload.data?.click_action || '/'
    };
    self.registration.showNotification(notificationTitle, notificationOptions);
});

// 4. PWA Caching Logic
const CACHE_NAME = 'compound-v3'; // Incremented version to clear old problematic cache
const STATIC_ASSETS = [
    '/',
    '/login',
    '/manifest.json',
    'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap',
    'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and API requests
    if (request.method !== 'GET' || url.pathname.startsWith('/api')) return;

    // Strategy: Network-First for navigation (HTML pages) AND Inertia JSON requests
    // This prevents showing a cached dashboard/data of a different user
    const isInertia = request.headers.get('X-Inertia');
    if (request.mode === 'navigate' || isInertia) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // We only cache successful page loads to allow offline backup
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() => caches.match(request) || (request.mode === 'navigate' ? caches.match('/') : null))
        );
        return;
    }

    // Strategy: Cache-First for static assets (JS, CSS, Fonts, Images)
    event.respondWith(
        caches.match(request).then(cached => {
            return cached || fetch(request).then(response => {
                const isStatic = url.pathname.match(/\.(js|css|png|jpg|jpeg|gif|svg|woff2|woff|ttf)$/) ||
                    url.host.includes('fonts.googleapis.com') ||
                    url.host.includes('fonts.gstatic.com');

                if (response.ok && isStatic && !url.pathname.includes('hot-update')) {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
                }
                return response;
            });
        })
    );
});

// 5. Handle Notification Clicks
self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data || '/')
    );
});
