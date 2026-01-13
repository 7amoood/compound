import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import PWAInstaller from './Components/PWAInstaller.vue';
import InAppNotification from './Components/InAppNotification.vue';
import OpenInAppBanner from './Components/OpenInAppBanner.vue';

createInertiaApp({
    title: (title) => `${title} - خدمات المجمع`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => h('div', [
                h(OpenInAppBanner),
                h(App, props),
                h(PWAInstaller),
                h(InAppNotification)
            ])
        });
        app.use(plugin);
        app.mount(el);
        return app;
    },
    progress: {
        // Show progress bar immediately
        delay: 0,
        color: '#10b981',
        includeCSS: true,
        showSpinner: false,
    },
});

// Service worker registration is handled in firebase.js during notification permission request

// Global Service Worker Message Listener for Notification Clicks
if ('BroadcastChannel' in window) {
    const channel = new BroadcastChannel('notification_channel');

    channel.onmessage = (event) => {
        const data = event.data;
        if (!data || data.type !== 'ON_NOTIFICATION_CLICK') return;

        // console.log('[BroadcastChannel] Notification click received:', data);

        const urlString = data.url || '/dashboard';
        const urlObj = new URL(urlString, window.location.origin);
        const requestId = urlObj.searchParams.get('request_id');

        // console.log('[BroadcastChannel] Parsed URL:', urlObj.href, 'Request ID:', requestId);

        const navigateTo = (path) => {
            // console.log('[BroadcastChannel] Navigating via router.visit to:', path);
            router.visit(path);
        };

        const dispatchRequestEvent = (id) => {
            // console.log('[BroadcastChannel] Dispatching open-request-details event for ID:', id);
            window.dispatchEvent(new CustomEvent('open-request-details', {
                detail: { requestId: id }
            }));
        };

        if (requestId) {
            // Case 1: Help request
            if (urlObj.pathname.includes('/help')) {
                navigateTo(urlString);
                return;
            }

            // Case 2: Dashboard page
            const currentPath = window.location.pathname;
            const isDashboard = ['/resident', '/provider', '/dashboard'].some(prefix => currentPath.includes(prefix));

            if (isDashboard) {
                dispatchRequestEvent(Number(requestId));
            } else {
                // Case 3: Not on dashboard, navigate
                navigateTo(urlString);
            }
        } else {
            // Case 4: Fallback, navigate to URL
            const path = urlObj.pathname + urlObj.search;
            navigateTo(path);
        }
    };

    // Optional: close channel on unload to prevent leaks
    window.addEventListener('beforeunload', () => {
        channel.close();
        // console.log('[BroadcastChannel] Channel closed on unload');
    });
}
