import React, { useId } from 'react';
import Icon from './Icon.jsx';
import InlineError from './InlineError.jsx';

/**
 * A labelled input.
 *
 * The help sits BELOW the field rather than above it, which is where people
 * look once they have read the label and seen the box — and it carries a small
 * mark so it never reads as an error.
 *
 * An optional icon inside the field gives the box a subject at a glance, which
 * matters on a phone where the label can scroll out of view above the keyboard.
 */
export default function Field({
    label,
    hint = null,
    error = null,
    icon = null,
    type = 'text',
    value,
    onChange,
    ...rest
}) {
    const id = useId();
    const hintId = hint ? `${id}-hint` : undefined;
    const errorId = error ? `${id}-error` : undefined;
    const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

    return (
        <div className="r7-field">
            <label className="r7-label" htmlFor={id}>{label}</label>

            <span className="r7-field__wrap">
                {icon ? (
                    <span className="r7-field__icon">
                        <Icon name={icon} className="r7-icon r7-icon--small" />
                    </span>
                ) : null}

                <input
                    id={id}
                    type={type}
                    className={`r7-input${icon ? ' r7-input--iconed' : ''}`}
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    aria-invalid={error ? 'true' : undefined}
                    aria-describedby={describedBy}
                    {...rest}
                />
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
