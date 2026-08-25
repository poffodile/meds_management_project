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

/**
 * The accent, read from r7-tokens.css.
 *
 * Inertia's progress bar wants a colour string at start-up, before any page
 * has mounted, so a hidden .r7-root is measured and discarded. If anything
 * goes wrong the value is undefined and Inertia falls back to its own default,
 * which is better than baking a second copy of the brand colour into JS.
 */
function accentColour() {
    try {
        const probe = document.createElement('div');

        probe.className = 'r7-root';
        probe.style.display = 'none';
        document.body.appendChild(probe);

        const value = getComputedStyle(probe).getPropertyValue('--r7-colour-accent').trim();

        probe.remove();

        return value || undefined;
    } catch (error) {
        return undefined;
    }
}

createInertiaApp({
    // The progress rule takes its colour from the token file rather than
    // repeating a hex here — read once, from a throwaway .r7-root element, so
    // there is exactly one place a Record7 colour is ever defined.
    progress: { color: accentColour(), delay: 140, showSpinner: false },

    resolve: (name) => {
        const pages = import.meta.glob('./R7Pages/**/*.jsx', { eager: true });
        const page = pages[`./R7Pages/${name}.jsx`];

        if (!page) {
            throw new Error(`Record7: no page component at resources/js/R7Pages/${name}.jsx`);
        }

        return page;
    },

    setup({ el, App, props }) {
        // The stored theme is read synchronously here, not in an effect, so the
        // first paint is already correct. An effect runs AFTER paint, which is
        // precisely when a flash of the wrong theme happens.
        let theme = null;

        try {
            theme = window.localStorage.getItem('record7.theme') === 'dark' ? 'dark' : null;
        } catch (error) {
            theme = null;
        }

        // Everything Record7 renders sits inside .r7-root, which is what makes
        // the scoped stylesheet work. Do not remove this wrapper.
        createRoot(el).render(
            <div className="r7-root" data-theme={theme ?? undefined}>
                <App {...props} />
            </div>
        );
    },
});
