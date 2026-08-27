import React from 'react';
import useTheme from '../useTheme.js';

/**
 * Switches between the warm cream and midnight grounds.
 *
 * A sign rather than a labelled control. The half-filled circle is the
 * convention for this and needs no word beside it, which keeps it out of the
 * way of the sign-in journey — it is a preference, not a step.
 *
 * The word is still there for anyone who cannot see the shape: the button
 * carries an accessible name and hidden text, and aria-pressed says which way
 * it is currently set.
 */
export default function ThemeToggle() {
    const { isDark, toggle } = useTheme();
    const action = isDark ? 'Switch to the light theme' : 'Switch to the dark theme';

    return (
        <button
            type="button"
            className="r7-theme"
            onClick={toggle}
            aria-pressed={isDark}
            aria-label={action}
            title={action}
        >
            <span className="r7-theme__swatch" aria-hidden="true" />
            <span className="r7-sr-only">{action}</span>
        </button>
    );
}
