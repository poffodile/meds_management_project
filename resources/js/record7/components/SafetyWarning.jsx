import React from 'react';

/**
 * Something that could harm someone.
 *
 * Deliberately louder than an error notice. An error is something that went
 * wrong; a safety warning is something that could hurt a person, so it gets a
 * heavier border, its own heading and an assertive announcement.
 *
 * Use it sparingly. A warning people see on every screen is a warning nobody
 * reads.
 */
export default function SafetyWarning({ title, children, action = null }) {
    return (
        <div className="r7-safety" role="alert">
            <span className="r7-safety__tag">Safety warning</span>
            <span className="r7-safety__title">{title}</span>
            {children ? <span className="r7-safety__body">{children}</span> : null}
            {action}
        </div>
    );
}
