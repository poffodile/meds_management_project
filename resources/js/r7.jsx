/**
 * Record7 Inertia entry point.
 *
 * Separate from resources/js/app.jsx (frontend1/frontend2), resources/js/f3.jsx
 * (frontend3) and resources/js/f4.jsx (frontend4) so the four bundles can never
 * collide.
 *
 * Note what is NOT imported here: no '@frontend/theme', no '@frontend4/...',
 * no '../css/app.css', no component-library stylesheet. Record7's only CSS is
 * its own scoped file.
 *
 * Pages resolve from resources/js/R7Pages/ — a different glob from all three
 * other entries, so none of them can pull in another's screens.
 */

import React from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

import '../css/record7/r7.css';

createInertiaApp({
    // A hairline moss progress rule during navigation, matching Record7's own
    // accent. It only appears once a request runs longer than `delay`, so quick
    // pages do not flash it.
    progress: { color: '#2C6070', delay: 140, showSpinner: false },

    resolve: (name) => {
        const pages = import.meta.glob('./R7Pages/**/*.jsx', { eager: true });
        const page = pages[`./R7Pages/${name}.jsx`];

        if (!page) {
            throw new Error(`Record7: no page component at resources/js/R7Pages/${name}.jsx`);
        }

        return page;
    },

    setup({ el, App, props }) {
        // Everything Record7 renders sits inside .r7-root, which is what makes
        // the scoped stylesheet work. Do not remove this wrapper.
        createRoot(el).render(
            <div className="r7-root">
                <App {...props} />
            </div>
        );
    },
});
