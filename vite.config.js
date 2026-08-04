import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            // Three independent entries. app.jsx is frontend1/frontend2; f3.jsx is
            // frontend3 and f4.jsx is frontend4, and neither shares anything with
            // it — each has its own theme, its own stylesheet, its own page
            // directory. Adding an entry here does not change the other bundles.
            input: ['resources/js/app.jsx', 'resources/js/f3.jsx', 'resources/js/f4.jsx'],
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: {
            // Shared component library lives in /frontend — import via '@frontend/...'
            '@frontend': fileURLToPath(new URL('./frontend', import.meta.url)),
            // Second app shell (own sidebar) lives in /frontend2 — import via '@frontend2/...'
            '@frontend2': fileURLToPath(new URL('./frontend2', import.meta.url)),
            // Third front end (own theme + own scoped CSS) lives in /frontend3 — import via '@frontend3/...'
            // Nothing under /frontend or /frontend2 may import from here, and vice versa.
            '@frontend3': fileURLToPath(new URL('./frontend3', import.meta.url)),
            // Fourth front end (own scoped CSS, no component library) lives in
            // /frontend4 — import via '@frontend4/...'. Same rule as above:
            // nothing outside may import from here, and vice versa.
            '@frontend4': fileURLToPath(new URL('./frontend4', import.meta.url)),
        },
    },
    server: {
        // Bind to IPv4 so the browser reliably reaches the dev server (avoids [::1] issues).
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },
});
