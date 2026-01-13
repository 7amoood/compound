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
navigator.serviceWorker?.addEventListener('message', (event) => {
    if (!event.data || event.data.type !== 'ON_NOTIFICATION_CLICK') return;

    const urlString = event.data.url;
    const urlObj = new URL(urlString, window.location.origin);
    const requestId = urlObj.searchParams.get('request_id');

    console.log('notification click', requestId);

    if (requestId && urlObj.pathname.includes('/help')) {
        // Inertia navigation مباشرة
        router.visit(urlString);
        return;
    }

    const currentPath = window.location.pathname;
    const isDashboard =
        currentPath.includes('/resident') ||
        currentPath.includes('/provider') ||
        currentPath.includes('/dashboard');

    if (requestId && isDashboard) {
        window.dispatchEvent(new CustomEvent('open-request-details', {
            detail: { requestId: Number(requestId) }
        }));
    } else {
        router.visit(urlObj.pathname + urlObj.search);
    }
});
