import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

// Separate config for tests so the Laravel/Vite dev plugin doesn't run here.
export default defineConfig({
    plugins: [react()],
    resolve: {
        alias: {
            '@frontend': fileURLToPath(new URL('./frontend', import.meta.url)),
        },
    },
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: './frontend/test/setup.js',
    },
});
