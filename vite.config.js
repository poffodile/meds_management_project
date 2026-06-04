import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: {
            // Shared component library lives in /frontend — import via '@frontend/...'
            '@frontend': fileURLToPath(new URL('./frontend', import.meta.url)),
        },
    },
    server: {
        // Bind to IPv4 so the browser reliably reaches the dev server (avoids [::1] issues).
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },
});
