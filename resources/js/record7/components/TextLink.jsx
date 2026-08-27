import React from 'react';
import { router } from '@inertiajs/react';

/**
 * A link that navigates through Inertia.
 *
 * Rendered as a button rather than an anchor when it posts, so assistive
 * technology is told what it actually does.
 */
export default function TextLink({ href = null, method = 'get', quiet = false, onClick = null, className = '', children, ...rest }) {
    // Either it navigates, or it does something on this page. Both look the
    // same to a reader and both are a real button, so neither is a dead link.
    const go = () => {
        if (onClick) return onClick();

        return method === 'post' ? router.post(href) : router.get(href);
    };

    return (
        <button
            type="button"
            // A caller's class is added, not swapped in: the link behaviour
            // stays and the caller only adds to how it looks. Spreading rest
            // over a hard-coded className silently dropped it before.
            className={`r7-link${quiet ? ' r7-link--quiet' : ''}${className ? ` ${className}` : ''}`}
            onClick={go}
            {...rest}
        >
            {children}
        </button>
    );
}
