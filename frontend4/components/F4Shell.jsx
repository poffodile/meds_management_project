/**
 * frontend4 page shell — top bar + centred content column.
 *
 * Deliberately plain. frontend4 starts with no navigation because it has one
 * page; add a nav here when there is a second screen to navigate to, not before.
 *
 * Every class name is defined in frontend4/f4.css under .f4-root. No component
 * library, no global stylesheet.
 */

import React from 'react';

export default function F4Shell({ title = 'Frontend 4', meta, children }) {
    return (
        <>
            <header className="f4-topbar">
                <span className="f4-mark" aria-hidden="true">F4</span>
                <span className="f4-topbar-title">{title}</span>
                {meta ? <span className="f4-topbar-meta">{meta}</span> : null}
            </header>
            <main className="f4-main">{children}</main>
        </>
    );
}
