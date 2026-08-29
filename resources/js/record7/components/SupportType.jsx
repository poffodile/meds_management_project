import React from 'react';

/**
 * Whose hands the medicine passes through.
 *
 * PER MEDICINE, NEVER PER PERSON. Somebody can be handed one tablet and watched
 * taking another, and a single label across both is wrong about one of them.
 * Section 2.0 could only summarise; this is the resolved answer for one item.
 *
 * The meaning is spelled out rather than left to the label, because "prompted"
 * and "assisted" are words a new or agency worker will guess at, and guessing
 * wrong means either doing something they should not or leaving somebody
 * without help.
 */
export default function SupportType({ type, word, meaning = null, compact = false }) {
    return (
        <span className={`r7-support r7-support--${type}${compact ? ' r7-support--compact' : ''}`}>
            <span className="r7-support__word">{word}</span>
            {meaning && !compact ? <span className="r7-support__meaning">{meaning}</span> : null}
        </span>
    );
}
