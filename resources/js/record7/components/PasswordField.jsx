import Icon from './Icon.jsx';
import InlineError from './InlineError.jsx';
import React, { useId, useState } from 'react';

/**
 * A password field with its reveal control inside it.
 *
 * Staff type these on phones, in corridors, sometimes wearing gloves, and a
 * mistyped password that locks an account mid-round is a real cost. The reveal
 * sits in the field where it is expected rather than as a checkbox underneath,
 * and it says "Show" or "Hide" in words.
 *
 * It starts hidden. Revealing is a deliberate act, because these screens get
 * used in front of other people.
 */
export default function PasswordField({
    label,
    // Matches Field, so a password sitting under a username lines its text up
    // with it. Without one the two boxes are the same width but their contents
    // start in different places, which reads as a mistake.
    icon = 'lock',
    hint = null,
    error = null,
    value,
    onChange,
    ...rest
}) {
    const id = useId();
    const [shown, setShown] = useState(false);

    const hintId = hint ? `${id}-hint` : undefined;
    const errorId = error ? `${id}-error` : undefined;
    const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

    return (
        <div className="r7-field">
            <label className="r7-label" htmlFor={id}>{label}</label>

            <span className="r7-secret">
                {icon ? (
                    <span className="r7-field__icon">
                        <Icon name={icon} className="r7-icon r7-icon--small" />
                    </span>
                ) : null}

                <input
                    id={id}
                    type={shown ? 'text' : 'password'}
                    className={`r7-input${icon ? ' r7-input--iconed' : ''}`}
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    aria-invalid={error ? 'true' : undefined}
                    aria-describedby={describedBy}
                    {...rest}
                />
                <button
                    type="button"
                    className="r7-reveal"
                    onClick={() => setShown((current) => !current)}
                    aria-controls={id}
                    aria-pressed={shown}
                >
                    {shown ? 'Hide' : 'Show'}
                </button>
            </span>

            {hint ? (
                <span className="r7-field__note" id={hintId}>
                    <Icon name="info" className="r7-icon r7-icon--small" />
                    <span>{hint}</span>
                </span>
            ) : null}

            {error ? <InlineError id={errorId}>{error}</InlineError> : null}
        </div>
    );
}
