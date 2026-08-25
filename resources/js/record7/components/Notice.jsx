import React from 'react';
import StateIcon from './StateIcon.jsx';

/**
 * A message about the whole screen or form.
 *
 * Inline, in the flow of the page, never a pop-up. A message that interrupts
 * is a message people dismiss without reading, and on a medicines product that
 * is exactly the wrong habit to teach.
 *
 * A small mark carries the kind alongside the colour, so it still reads in
 * greyscale, and the state is named for screen readers. Errors announce
 * themselves assertively; everything else is polite, so a status update does
 * not interrupt someone mid-sentence.
 */
export default function Notice({ tone = 'info', title = null, children }) {
    if (!children && !title) return null;

    return (
        <div className={`r7-notice r7-notice--${tone}`} role={tone === 'error' ? 'alert' : 'status'}>
            <span className="r7-notice__mark">
                <StateIcon tone={tone} />
            </span>

            <span className="r7-notice__body">
                {title ? <span className="r7-notice__title">{title}</span> : null}
                {children ? <span className="r7-notice__text">{children}</span> : null}
            </span>
        </div>
    );
}
