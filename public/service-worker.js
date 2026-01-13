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

// IndexedDB Helper to store User ID
const DB_NAME = 'compound-db';
const STORE_NAME = 'settings';

function getUserId() {
    return new Promise((resolve) => {
        const request = indexedDB.open(DB_NAME, 1);
        request.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME);
            }
        };
        request.onsuccess = (e) => {
            const db = e.target.result;
            const tx = db.transaction(STORE_NAME, 'readonly');
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                resolve(null);
                return;
            }
            const store = tx.objectStore(STORE_NAME);
            const getReq = store.get('user_id');
            getReq.onsuccess = () => resolve(getReq.result);
            getReq.onerror = () => resolve(null);
        };
        request.onerror = () => resolve(null);
    });
}

// Handle messages from Client (start storing user ID)
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SET_USER_ID') {
        const request = indexedDB.open(DB_NAME, 1);
        request.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME);
            }
        };
        request.onsuccess = (e) => {
            const db = e.target.result;
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            store.put(event.data.userId, 'user_id');
        };
    }
});

// 3. Handle Background Messages
messaging.onBackgroundMessage(async (payload) => {
    console.log('[SW] Background message received:', payload);

    // Check if message is from self
    if (payload.data && payload.data.sender_id) {
        const currentUserId = await getUserId();
        if (currentUserId && payload.data.sender_id == currentUserId) {
            console.log('[SW] Ignoring notification from self');
            return;
        }
    }

    // IF the payload already has a system-level notification block, 
    // the browser (Chrome/Safari) will display it automatically in the background.
    // We only show a manual one if it's a data-only message.
    // if (!payload.notification) {
        const notificationTitle = 'momo';
        const notificationOptions = {
            body: payload.data.body || '',
            icon: '/icons/icon-192x192.png',
            badge: '/icons/badge-72x72.png',
            data: payload.data,
            dir: 'rtl'
        };
        return self.registration.showNotification(notificationTitle, notificationOptions);
    // }
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

    // Get target URL from notification data (click_action)
    const urlToOpen = event.notification.data?.click_action || event.notification.data || '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            // 1. If a window is already open, focus it and send message
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if ('focus' in client) {
                    client.focus().catch(err => console.error('Focus failed:', err));

                    // Use BroadcastChannel for reliable communication
                    const channel = new BroadcastChannel('notification_channel');
                    channel.postMessage({
                        type: 'ON_NOTIFICATION_CLICK',
                        url: urlToOpen
                    });

                    // Close channel after a short delay to ensure message is sent
                    setTimeout(() => channel.close(), 1000);

                    return;
                }
            }
            // 2. Otherwise open a new window
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});