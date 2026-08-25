import React from 'react';

/**
 * A status, always stated in words.
 *
 * Colour is a second signal, never the only one — the label reads correctly in
 * greyscale, at a glance, and to a screen reader. One component with variants
 * rather than a pill per screen, so a status looks the same everywhere it
 * appears.
 */
export default function StatusLabel({ tone = 'neutral', children, ...rest }) {
    return (
        <span className={`r7-status r7-status--${tone}`} {...rest}>
            {children}
        </span>
    );
}
