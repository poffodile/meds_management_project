import React from 'react';

/**
 * Record7's button.
 *
 * One component with variants rather than a button per screen, so "primary"
 * means the same thing everywhere and changing it changes it once.
 *
 *   primary    the action the screen is for
 *   secondary  a real alternative to it
 *   quiet      something available but not being urged
 *   warning    proceed, but know what you are doing
 *   dangerous  cannot be undone
 *   bare       reads as a link, sized as a control
 *
 * `busy` disables the control and changes the label, so a slow network cannot
 * produce a second submission — which on a clinical record means a duplicate
 * entry, and a duplicate entry on a medicines record is a real incident.
 */
export default function Button({
    variant = 'primary',
    size = 'default',
    block = false,
    busy = false,
    busyLabel = 'Working',
    children,
    ...rest
}) {
    const classes = [
        'r7-btn',
        `r7-btn--${variant}`,
        size === 'small' ? 'r7-btn--small' : '',
        block ? 'r7-btn--block' : '',
    ].filter(Boolean).join(' ');

    return (
        <button
            type="button"
            {...rest}
            disabled={busy || rest.disabled}
            aria-busy={busy || undefined}
            className={classes}
        >
            {busy ? busyLabel : children}
        </button>
    );
}
