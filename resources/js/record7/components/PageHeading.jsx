import React from 'react';

/** The heading block at the top of a screen. */
export default function PageHeading({ eyebrow = null, title, lede = null, actions = null }) {
    return (
        <div className="r7-page-heading">
            {eyebrow ? <span className="r7-label">{eyebrow}</span> : null}
            <h1 className="r7-display">{title}</h1>
            {lede ? <p className="r7-lede r7-measure">{lede}</p> : null}
            {actions ? <div className="r7-btn-row">{actions}</div> : null}
        </div>
    );
}
