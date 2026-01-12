import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        // Optimize for production
        target: 'es2020',
        // Use esbuild for minification (faster, built-in)
        minify: 'esbuild',
        rollupOptions: {
            output: {
                // Manual chunk splitting for better caching
                manualChunks: {
                    'vendor': ['vue', '@inertiajs/vue3', 'axios'],
                },
            },
        },
        // Reduce chunk size warnings
        chunkSizeWarningLimit: 1000,
    },
});

