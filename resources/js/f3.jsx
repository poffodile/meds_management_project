/**
 * frontend3 Inertia entry point.
 *
 * Deliberately separate from resources/js/app.jsx so the two bundles can never
 * collide. Note what is NOT imported here: no '@frontend/theme', no
 * '@frontend/lib/font', no '../css/app.css'. frontend3 brings its own.
 *
 * Pages resolve from resources/js/F3Pages/ — a different glob from app.jsx's
 * ./Pages/, so neither entry can accidentally pull in the other's screens.
 *
 * See docs/care-one-os/FRONTEND3/FRONTEND3-PLAN.md.
 */

import React from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { MantineProvider } from '@mantine/core';
import '@mantine/core/styles.css';

import { theme } from '@frontend3/theme';
import '@frontend3/f3.css';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./F3Pages/**/*.jsx', { eager: true });
        return pages[`./F3Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        // Everything frontend3 renders sits inside .f3-root, which is what makes
        // the scoped stylesheet work. Do not remove this wrapper.
        createRoot(el).render(
            <MantineProvider theme={theme} defaultColorScheme="light">
                <div className="f3-root">
                    <App {...props} />
                </div>
            </MantineProvider>
        );
    },
});
