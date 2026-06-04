import React from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { MantineProvider, createTheme } from '@mantine/core';
import '@mantine/core/styles.css';

const theme = createTheme({
    primaryColor: 'indigo',
    fontFamily: 'Inter, -apple-system, Segoe UI, Roboto, sans-serif',
    defaultRadius: 'md',
});

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
        return pages[`./Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <MantineProvider theme={theme} defaultColorScheme="light">
                <App {...props} />
            </MantineProvider>
        );
    },
});
