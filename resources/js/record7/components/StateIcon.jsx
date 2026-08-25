import React from 'react';

/**
 * The small mark beside a message.
 *
 * It exists so a message is not carried by colour alone — the shapes differ,
 * so error, warning, success and information are still distinguishable in
 * greyscale or with a colour vision deficiency.
 *
 * It replaced an uppercase word ("PROBLEM") sitting in front of every error,
 * which shouted, read as unfinished, and pushed the actual sentence sideways.
 * The word is still there for screen readers, just not on the screen.
 */
const PATHS = {
    // A triangle: the universal "attention".
    error: 'M12 3.5 22 20H2L12 3.5Z M12 9v5 M12 16.5v.5',
    warning: 'M12 3.5 22 20H2L12 3.5Z M12 9v5 M12 16.5v.5',
    // A circle with a tick.
    success: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M8 12.5l2.5 2.5L16 9.5',
    // A circle with an i.
    info: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M12 11v5 M12 7.5v.5',
};

const WORDS = {
    error: 'Error',
    warning: 'Warning',
    success: 'Success',
    info: 'Information',
};

export default function StateIcon({ tone = 'info', className = 'r7-icon' }) {
    const path = PATHS[tone] ?? PATHS.info;

    return (
        <>
            <svg
                className={className}
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="1.8"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
                focusable="false"
            >
                {path.split(' M').map((segment, index) => (
                    <path key={index} d={index === 0 ? segment : `M${segment}`} />
                ))}
            </svg>
            <span className="r7-sr-only">{WORDS[tone] ?? WORDS.info}</span>
        </>
    );
}
