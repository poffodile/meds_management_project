import React from 'react';
import useTheme from '../useTheme.js';

/**
 * Switches between the warm cream and midnight grounds.
 *
 * Labelled in words rather than an icon alone, so what it does is readable
 * without decoding a symbol, and announced properly to assistive technology.
 */
export default function ThemeToggle() {
    const { isDark, toggle } = useTheme();

    return (
        <button
            type="button"
            className="r7-theme"
            onClick={toggle}
            aria-pressed={isDark}
            title={isDark ? 'Switch to the light theme' : 'Switch to the dark theme'}
        >
            <span className="r7-theme__dot" aria-hidden="true" />
            {isDark ? 'Light' : 'Dark'}
        </button>
    );
}
