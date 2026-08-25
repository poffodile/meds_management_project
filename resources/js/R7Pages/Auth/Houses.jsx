import React from 'react';
import { router } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import HouseRow from '@record7/components/HouseRow.jsx';
import Notice from '@record7/components/Notice.jsx';
import StateBlock from '@record7/components/StateBlock.jsx';
import TextLink from '@record7/components/TextLink.jsx';

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

    const choose = (houseId) => router.post(chooseUrl, { house_id: houseId });

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
            footer={<TextLink href={signOutUrl} method="post">Sign out instead</TextLink>}
        >
            <div className="r7-stack">
                <Notice tone="error">{error}</Notice>

                {houses.length ? (
                    <ul className="r7-houses">
                        {houses.map((house) => (
                            <li key={house.id}>
                                <HouseRow
                                    house={house}
                                    current={house.id === currentHouseId}
                                    onChoose={choose}
                                />
                            </li>
                        ))}
                    </ul>
                ) : (
                    <StateBlock state="restricted" title="No house is available to you">
                        Your account does not currently have access to an active house. Please
                        contact your administrator.
                    </StateBlock>
                )}
            </div>
        </AuthShell>
    );
}
