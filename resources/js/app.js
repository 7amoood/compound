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
        color: '#137fec',
        includeCSS: true,
        showSpinner: false,
    },
});

// Service worker registration is handled in firebase.js during notification permission request

// Global Service Worker Message Listener for Notification Clicks
if ('BroadcastChannel' in window) {
    const channel = new BroadcastChannel('notification_channel');
    channel.onmessage = (event) => {
        if (event.data && event.data.type === 'ON_NOTIFICATION_CLICK') {
            const urlString = event.data.url;

            // Parse URL
            const urlObj = new URL(urlString, window.location.origin);
            const requestId = urlObj.searchParams.get('request_id');

            if (requestId) {
                // Check if we are on a dashboard page
                const currentPath = window.location.pathname;
                const isDashboard = currentPath.includes('/resident') ||
                    currentPath.includes('/provider') ||
                    currentPath.includes('/dashboard');

                if (isDashboard) {
                    // Dispatch a custom event that dashboards are listening to
                    window.dispatchEvent(new CustomEvent('open-request-details', {
                        detail: { requestId: parseInt(requestId) }
                    }));
                } else {
                    // Not on dashboard, navigate to it (Inertia handles this smoothly)
                    router.visit(urlString);
                }
            } else {
                // Fallback: navigate using Inertia to avoid reload
                const path = urlObj.pathname + urlObj.search;
                router.visit(path);
            }
        }
    };
}