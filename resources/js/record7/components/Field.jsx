import React, { useId } from 'react';

/**
 * A labelled input with its hint and error wired to assistive technology.
 *
 * The error is referenced by aria-describedby and the input marked
 * aria-invalid, so a screen-reader user hears why a field was refused rather
 * than only seeing a coloured border.
 */
export default function Field({
    label,
    hint = null,
    error = null,
    type = 'text',
    value,
    onChange,
    inputClassName = 'r7-input',
    ...rest
}) {
    const id = useId();
    const hintId = hint ? `${id}-hint` : undefined;
    const errorId = error ? `${id}-error` : undefined;
    const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

    return (
        <div className="r7-field">
            <label className="r7-label" htmlFor={id}>{label}</label>
            {hint ? <span className="r7-field__hint" id={hintId}>{hint}</span> : null}
            <input
                id={id}
                type={type}
                className={inputClassName}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={describedBy}
                {...rest}
            />
            {error ? <span className="r7-field__error" id={errorId}>{error}</span> : null}
        </div>
    );
}
