import React from 'react';

/**
 * The small set of marks Record7 uses.
 *
 * Drawn inline rather than pulled from an icon font or a package: there are
 * only a handful, they inherit currentColor so they follow the theme without a
 * second copy, and Record7 stays free of another front end's dependencies.
 *
 * Every one is decorative — the meaning is always in the words beside it — so
 * they are hidden from assistive technology.
 */
const PATHS = {
    house: 'M3 10.5 12 3l9 7.5 M5 9.5V20h14V9.5 M10 20v-5h4v5',
    info: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M12 11v5 M12 7.5v.5',
    lock: 'M6 10.5V8a6 6 0 0 1 12 0v2.5 M4.5 10.5h15v10h-15z M12 14.5v2',
    tick: 'M4 12.5 9.5 18 20 6.5',
    arrow: 'M4 12h15 M13 6l6 6-6 6',
    person: 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z M4.5 20.5a7.5 7.5 0 0 1 15 0',
    building: 'M4 21V6l7-3v18 M11 9h8v12 M15 13h.5 M15 17h.5 M7 9h.5 M7 13h.5 M7 17h.5',
    warning: 'M12 3.5 22 20.5H2L12 3.5Z M12 10v4.5 M12 17.5v.5',
    menu: 'M4 7h16 M4 12h16 M4 17h16',
    close: 'M6 6l12 12 M18 6 6 18',
    clock: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M12 7v5.5l3.5 2',
};

export default function Icon({ name, className = 'r7-icon' }) {
    const path = PATHS[name];

    if (!path) return null;

    return (
        <svg
            className={className}
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.7"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
            focusable="false"
        >
            {path.split(' M').map((segment, index) => (
                <path key={index} d={index === 0 ? segment : `M${segment}`} />
            ))}
        </svg>
    );
}
