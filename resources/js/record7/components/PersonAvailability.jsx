import React from 'react';
import Icon from './Icon.jsx';

/**
 * Where this person actually is.
 *
 * Shown only when they are NOT here. "At home" on every card would be noise;
 * "In hospital" on one is the thing that stops somebody walking to an empty
 * flat with a pot of tablets.
 *
 * TWO FACTS, SAID SEPARATELY, BECAUSE THEY PULL IN OPPOSITE DIRECTIONS.
 *
 *   1  this service is not giving the medicine while they are away
 *   2  the planned dose still has to be answered for
 *
 * Run together — or left to a free-text note saying something is "on hold" —
 * the first swallows the second, and "on hold" gets read at the end of a long
 * shift as "nothing to do here". The dose does not disappear because the person
 * did, and a planned dose with no outcome is exactly the gap an inspection
 * finds months later with nobody able to say what happened.
 *
 * So the obligation gets its own line, its own weight, and the word "must".
 */
export default function PersonAvailability({ available, statusWord, needsOutcome = false }) {
    if (available) return null;

    return (
        <div className="r7-away" role="note">
            <span className="r7-away__state">
                <Icon name="info" className="r7-icon r7-icon--small" />
                {statusWord}
            </span>

            <span className="r7-away__note">
                This service is not giving their medicines while they are away, so do not go
                looking for them.
            </span>

            {needsOutcome ? (
                <span className="r7-away__obligation">
                    The doses below are still planned and have not been cancelled. Each one must
                    still have an outcome recorded saying why it was not given.
                </span>
            ) : null}
        </div>
    );
}
