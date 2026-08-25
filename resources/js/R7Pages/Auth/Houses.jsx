import React from 'react';
import { router } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import Notice from '@record7/components/Notice.jsx';

const ACCESS_WORDS = {
    standard: 'Full access',
    manager: 'Manager access',
    oversight: 'Oversight access',
    read_only: 'Review only',
    temporary: 'Temporary access',
};

/**
 * Choosing, or switching, the house you are working in.
 *
 * Only houses this account currently holds usable access to appear, and only
 * active ones. The list is a convenience — the server refuses any house the
 * person does not hold, so a crafted request cannot reach one that was never
 * offered.
 */
export default function Houses({
    organisationName, name, houses, currentHouseId, chooseUrl, signOutUrl, error,
}) {
    const switching = Boolean(currentHouseId);

    return (
        <AuthShell
            step={switching ? null : 4}
            stepCount={4}
            wide
            title={switching ? 'Switch house' : 'Which house are you working in?'}
            intro={
                switching
                    ? 'Everything you record from here on is filed against the house you choose.'
                    : <>Signed in as <strong>{name}</strong> at <strong>{organisationName}</strong>. Everything you record is filed against the house you choose.</>
            }
            footer={
                <button type="button" className="r7-btn r7-btn--bare" onClick={() => router.post(signOutUrl)}>
                    Sign out instead
                </button>
            }
        >
            <div className="r7-stack">
                <Notice tone="problem">{error}</Notice>

                <ul className="r7-houses">
                    {houses.map((house) => {
                        const current = house.id === currentHouseId;

                        return (
                            <li key={house.id}>
                                <button
                                    type="button"
                                    className={`r7-house${current ? ' r7-house--current' : ''}`}
                                    onClick={() => router.post(chooseUrl, { house_id: house.id })}
                                >
                                    <span className="r7-house__body">
                                        <span className="r7-house__name">{house.name}</span>
                                        <span className="r7-house__meta">
                                            {[house.type, house.town, ACCESS_WORDS[house.accessType]]
                                                .filter(Boolean)
                                                .join(', ')}
                                        </span>
                                    </span>
                                    <span className="r7-house__go">
                                        {current ? 'Currently open' : 'Open'}
                                    </span>
                                </button>
                            </li>
                        );
                    })}
                </ul>
            </div>
        </AuthShell>
    );
}
