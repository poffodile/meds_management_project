import React from 'react';
import Icon from './Icon.jsx';
import StatusLabel from './StatusLabel.jsx';

const ACCESS_WORDS = {
    standard: 'Full access',
    manager: 'Manager access',
    oversight: 'Oversight access',
    read_only: 'Review only',
    temporary: 'Temporary access',
};

/*
 * ONLY THE ACCESS THAT CARRIES A CATCH IS LABELLED.
 *
 * Somebody with review-only access who walks into a house expecting to record a
 * dose finds out at the worst moment, so that has to be said here. But full,
 * manager and oversight access all mean "you can do your job", which is what
 * anybody opening this screen already assumes — labelling those put a badge on
 * every row, and a badge on every row is wallpaper.
 *
 * So the normal cases say nothing and the row is two clean lines. When a badge
 * does appear it is because something is genuinely different about that house,
 * and it is the only one on the screen.
 */
const RESTRICTED = ['read_only', 'temporary'];

/**
 * One house to choose from.
 *
 * There is no icon. Every house had the same building glyph, so it told you
 * nothing and cost the row about seventy pixels — which is exactly what the
 * name and the access badge needed in order to share a line.
 *
 * A whole row is the target rather than a small link, because this is tapped
 * on a phone, often in a hurry. What kind of access you hold is shown here and
 * not only after you are inside, so nobody enters a house expecting to be able
 * to record and finds they cannot.
 */
export default function HouseRow({ house, current = false, onChoose }) {
    const details = [house.type, house.town].filter(Boolean);
    const restricted = RESTRICTED.includes(house.accessType);

    return (
        <button
            type="button"
            className={`r7-house${current ? ' r7-house--current' : ''}`}
            onClick={() => onChoose(house.id)}
            aria-current={current ? 'true' : undefined}
        >
            {/* Everything about the house reads as one block on the left.
                The access badge used to sit in a column of its own on the far
                right, which stretched the row to both edges and left a hole
                down the middle. It belongs with the rest of what is true about
                this house, not opposite it. */}
            <span className="r7-house__body">
                <span className="r7-house__name">{house.name}</span>
                <span className="r7-house__meta">
                    {details.map((detail) => <span key={detail}>{detail}</span>)}
                    {current ? <span className="r7-house__current">Currently open</span> : null}
                </span>

                {restricted ? (
                    <StatusLabel tone="warning">
                        <Icon name="warning" className="r7-icon r7-icon--small" />
                        <span>{ACCESS_WORDS[house.accessType] ?? house.accessType}</span>
                    </StatusLabel>
                ) : null}
            </span>

            {/* The word "Open" was doing nothing the whole row was not already
                doing. A chevron is enough, and it costs no width. */}
            <Icon name="arrow" className="r7-icon r7-house__go" />
        </button>
    );
}
