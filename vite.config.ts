import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/web/main.ts',
                'resources/js/dashboard/main.ts',
            ],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            '@shared': fileURLToPath(new URL('./resources/js/_shared', import.meta.url)),
            '@web': fileURLToPath(new URL('./resources/js/web', import.meta.url)),
            '@dashboard': fileURLToPath(new URL('./resources/js/dashboard', import.meta.url)),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id: string) => {
                    if (id.includes('node_modules/primevue') || id.includes('node_modules/@primevue')) return 'primevue-core';
                    if (id.includes('node_modules/vue') || id.includes('node_modules/pinia')) return 'vendor-vue';
                    return undefined;
                },
            },
        },
    },
});
