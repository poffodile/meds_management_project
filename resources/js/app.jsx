import React from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { MantineProvider } from '@mantine/core';
import { Notifications } from '@mantine/notifications';
import '@mantine/core/styles.css';
import '@mantine/notifications/styles.css';
import '../css/app.css';
import { theme } from '@frontend/theme';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
        return pages[`./Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <MantineProvider theme={theme}>
                <Notifications position="bottom-center" limit={3} autoClose={3500} transitionDuration={420} />
                <App {...props} />
            </MantineProvider>
        );
    },
});
