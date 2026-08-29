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
 *
 * PHOTOGRAPHS. Record7 holds none — the concept is not in the schema, so this
 * is not an empty column waiting to be filled. Passing photoState="not_held"
 * makes the avatar say so to a screen reader instead of implying that a photo
 * was looked for and missing. Initials are a placeholder for a name, never a
 * substitute for identifying somebody.
 */
export default function PersonIdentity({
    name,
    initials = null,
    details = [],
    status = null,
    size = 'default',
    photo = null,
    photoState = null,
}) {
    const letters = initials ?? name
        ?.split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();

    return (
        <div className={`r7-identity${size === 'large' ? ' r7-identity--large' : ''}`}>
            {photo ? (
                <img className="r7-identity__avatar r7-identity__avatar--photo" src={photo} alt="" />
            ) : (
                <span
                    className="r7-identity__avatar"
                    role={photoState === 'not_held' ? 'img' : undefined}
                    aria-label={photoState === 'not_held' ? 'No photograph is held for this person' : undefined}
                    aria-hidden={photoState === 'not_held' ? undefined : 'true'}
                >
                    {letters}
                </span>
            )}

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
