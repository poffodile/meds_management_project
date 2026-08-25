import React from 'react';

const ACCESS_WORDS = {
    standard: 'Full access',
    manager: 'Manager access',
    oversight: 'Oversight access',
    read_only: 'Review only',
    temporary: 'Temporary access',
};

/**
 * One house to choose from.
 *
 * A whole row is the target rather than a small link, because this is tapped
 * on a phone, often in a hurry. What kind of access you hold is shown here and
 * not only after you are inside, so nobody enters a house expecting to be able
 * to record and finds they cannot.
 */
export default function HouseRow({ house, current = false, onChoose }) {
    const details = [house.type, house.town, ACCESS_WORDS[house.accessType] ?? house.accessType];

    return (
        <button
            type="button"
            className={`r7-house${current ? ' r7-house--current' : ''}`}
            onClick={() => onChoose(house.id)}
            aria-current={current ? 'true' : undefined}
        >
            <span className="r7-house__body">
                <span className="r7-house__name">{house.name}</span>
                <span className="r7-house__meta">
                    {details.filter(Boolean).map((detail) => <span key={detail}>{detail}</span>)}
                </span>
            </span>

            <span className="r7-house__go">{current ? 'Currently open' : 'Open'}</span>
        </button>
    );
}
