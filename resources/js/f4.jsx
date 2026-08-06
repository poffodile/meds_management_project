/**
 * frontend4 Inertia entry point.
 *
 * Separate from resources/js/app.jsx (frontend1/frontend2) and resources/js/f3.jsx
 * (frontend3) so the three bundles can never collide.
 *
 * Note what is NOT imported here: no '@frontend/theme', no '../css/app.css', and
 * no component-library stylesheet. frontend4's only CSS is its own scoped file.
 *
 * Pages resolve from resources/js/F4Pages/ — a different glob from both other
 * entries, so none of them can accidentally pull in another's screens.
 */

import React from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

import '@frontend4/f4.css';

createInertiaApp({
    // A thin indigo loading bar at the top of the page during every navigation,
    // so tapping a client gives instant feedback instead of a silent wait. It
    // only appears once a request runs longer than `delay`, so quick pages don't
    // flash it.
    progress: { color: '#3F4FD6', delay: 120, showSpinner: false },
    resolve: (name) => {
        const pages = import.meta.glob('./F4Pages/**/*.jsx', { eager: true });
        return pages[`./F4Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        // Everything frontend4 renders sits inside .f4-root, which is what makes
        // the scoped stylesheet work. Do not remove this wrapper.
        createRoot(el).render(
            <div className="f4-root">
                <App {...props} />
            </div>
        );
    },
});
