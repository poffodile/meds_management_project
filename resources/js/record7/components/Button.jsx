import React from 'react';

/**
 * Record7's button.
 *
 * `busy` disables the control and changes the label, so a slow network cannot
 * produce a second submission — which on a clinical record means a duplicate.
 */
export default function Button({
    variant = 'primary',
    block = false,
    busy = false,
    busyLabel = 'Working',
    children,
    ...rest
}) {
    return (
        <button
            type="button"
            {...rest}
            disabled={busy || rest.disabled}
            aria-busy={busy || undefined}
            className={['r7-btn', `r7-btn--${variant}`, block ? 'r7-btn--block' : '']
                .filter(Boolean).join(' ')}
        >
            {busy ? busyLabel : children}
        </button>
    );
}
