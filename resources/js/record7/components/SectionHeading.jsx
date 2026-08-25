import React from 'react';

/** A heading inside a screen, with an optional note on the right. */
export default function SectionHeading({ title, note = null, level = 2 }) {
    const Tag = `h${level}`;

    return (
        <div className="r7-section-heading">
            <Tag className="r7-heading">{title}</Tag>
            {note ? <span className="r7-label">{note}</span> : null}
        </div>
    );
}
