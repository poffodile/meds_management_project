import React from 'react';

/**
 * Who someone is.
 *
 * Used for staff now and for clients from Section 1. The name is never
 * abbreviated and the supporting details sit on their own line, because a
 * misread identity on a medicines product is the first of the seven rights to
 * go wrong.
 *
 * Details are separate elements rather than one run of text joined by
 * punctuation, so no two can be read as one.
 */
export default function PersonIdentity({ name, initials = null, details = [], status = null, size = 'default' }) {
    const letters = initials ?? name
        ?.split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();

    return (
        <div className={`r7-identity${size === 'large' ? ' r7-identity--large' : ''}`}>
            <span className="r7-identity__avatar" aria-hidden="true">{letters}</span>

            <span className="r7-identity__body">
                <span className="r7-identity__name">{name}</span>
                {details.length ? (
                    <span className="r7-identity__meta">
                        {details.filter(Boolean).map((detail) => (
                            <span key={detail}>{detail}</span>
                        ))}
                    </span>
                ) : null}
            </span>

            {status}
        </div>
    );
}
