import React from 'react';
import StateIcon from './StateIcon.jsx';

/**
 * The message under a field that was refused.
 *
 * A small mark and the sentence. No uppercase label in front of it: a shouted
 * word before every message reads as a system talking to itself rather than to
 * a person, and it pushes the sentence that actually matters out of the way.
 *
 * The word "Error" is still announced to screen readers by StateIcon.
 */
export default function InlineError({ id = undefined, children }) {
    if (!children) return null;

    return (
        <span className="r7-field__error" id={id}>
            <StateIcon tone="error" className="r7-icon r7-icon--small" />
            <span>{children}</span>
        </span>
    );
}
