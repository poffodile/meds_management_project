import React from 'react';
import { router } from '@inertiajs/react';

/**
 * A link that navigates through Inertia.
 *
 * Rendered as a button rather than an anchor when it posts, so assistive
 * technology is told what it actually does.
 */
export default function TextLink({ href, method = 'get', quiet = false, children, ...rest }) {
    const go = () => (method === 'post' ? router.post(href) : router.get(href));

    return (
        <button
            type="button"
            className={`r7-link${quiet ? ' r7-link--quiet' : ''}`}
            onClick={go}
            {...rest}
        >
            {children}
        </button>
    );
}
