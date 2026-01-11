import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import PWAInstaller from './Components/PWAInstaller.vue';

createInertiaApp({
    title: (title) => `${title} - خدمات المجمع`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => h('div', [
                h(App, props),
                h(PWAInstaller)
            ])
        });
        app.use(plugin);
        app.mount(el);
        return app;
    },
    progress: {
        color: '#137fec',
    },
});

// Service worker registration is handled in firebase.js during notification permission request
