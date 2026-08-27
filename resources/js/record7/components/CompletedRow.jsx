import React from 'react';
import StatusLabel from './StatusLabel.jsx';

/**
 * One line of the activity record.
 *
 * This band is collapsed by default and most people never open it, so a row is
 * the barest possible answer to "has that been done?" — who, when, and the
 * outcome, only when the outcome was not simply "given".
 *
 * No medicine names: this is a log of what happened, not a record to check a
 * dose against. Checking a dose is the round screen's job.
 */
const TONES = {
    refused: 'warning',
    withheld: 'warning',
    not_available: 'warning',
    missed: 'error',
};

export default function CompletedRow({ entry }) {
    return (
        <li className="r7-done">
            <span className="r7-done__time">{entry.at}</span>
            <span className="r7-done__who">{entry.client}</span>

            <span className="r7-done__side">
                {entry.taken ? null : (
                    <StatusLabel tone={TONES[entry.outcome] ?? 'neutral'}>{entry.outcomeWord}</StatusLabel>
                )}
                {entry.by ? <span className="r7-done__by">{entry.by}</span> : null}
            </span>
        </li>
    );
}
