import React from 'react';
import Icon from './Icon.jsx';
import StatusLabel from './StatusLabel.jsx';

/**
 * What this person must not be given.
 *
 * NEVER BEHIND A TAP. This is the one thing on the screen that can stop a
 * medicine being given, so it sits in the open above the medicines, in the
 * reading order, with no interaction between the reader and the words.
 *
 * SEVERITY IS A WORD AND A SHAPE, NEVER A COLOUR ALONE. This gets read on a
 * cracked phone in a corridor, in greyscale, and by people who are colour-blind.
 * The rail, the icon and the written severity all carry it.
 *
 * "NONE RECORDED" IS NOT "NONE". A reader has to be able to tell the difference
 * between somebody with no allergies and somebody nobody has ever asked, and
 * Record7 cannot currently distinguish them — so the empty state says exactly
 * that instead of a reassuring "No allergies".
 */
const TONE = {
    life_threatening: 'error',
    severe: 'error',
    moderate: 'warning',
    mild: 'neutral',
};

export default function AllergyWarning({ allergies = [], state = 'recorded', sensitivities = null }) {
    if (state === 'none_recorded') {
        return (
            <div className="r7-allergies r7-allergies--empty" role="note">
                <span className="r7-allergies__title">Allergies</span>
                <p className="r7-allergies__none">
                    None recorded. That is not the same as none — check the care plan
                    if you are unsure.
                </p>
                {sensitivities === 'not_separately_held' ? (
                    <p className="r7-allergies__note">
                        Record7 does not hold sensitivities separately from allergies.
                    </p>
                ) : null}
            </div>
        );
    }

    return (
        <div className="r7-allergies" role="alert">
            <span className="r7-allergies__title">
                <Icon name="warning" className="r7-icon r7-icon--small" />
                Allergies
            </span>

            <ul className="r7-allergies__list">
                {allergies.map((allergy) => (
                    <li
                        className={`r7-allergy-row${allergy.critical ? ' r7-allergy-row--critical' : ''}`}
                        key={allergy.id}
                    >
                        <span className="r7-allergy-row__substance">{allergy.substance}</span>
                        <StatusLabel tone={TONE[allergy.severity] ?? 'neutral'}>
                            {allergy.severityWord}
                        </StatusLabel>
                        {allergy.reaction ? (
                            <span className="r7-allergy-row__reaction">{allergy.reaction}</span>
                        ) : null}
                        {allergy.source ? (
                            <span className="r7-allergy-row__source">{allergy.source}</span>
                        ) : null}
                    </li>
                ))}
            </ul>

            {sensitivities === 'not_separately_held' ? (
                <p className="r7-allergies__note">
                    Record7 does not hold sensitivities separately. Anything recorded as mild
                    or moderate above is the nearest equivalent.
                </p>
            ) : null}
        </div>
    );
}
