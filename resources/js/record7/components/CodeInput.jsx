import InlineError from './InlineError.jsx';
import React, { useId } from 'react';

/**
 * The security code.
 *
 * Given its own component because it behaves unlike a text field: digits only,
 * a fixed length, a numeric keypad on a phone, and one-time-code autofill so
 * the code can come straight from the device rather than being retyped.
 *
 * Wide, spaced and tabular, because it is read aloud and copied across from
 * another screen.
 */
export default function CodeInput({ label, length = 6, value, onChange, error = null, hint = null }) {
    const id = useId();
    const hintId = hint ? `${id}-hint` : undefined;
    const errorId = error ? `${id}-error` : undefined;

    return (
        <div className="r7-field">
            <label className="r7-label" htmlFor={id}>{label}</label>
            {hint ? <span className="r7-field__hint" id={hintId}>{hint}</span> : null}

            <input
                id={id}
                className="r7-code-input"
                value={value}
                onChange={(event) => onChange(event.target.value.replace(/\D/g, '').slice(0, length))}
                inputMode="numeric"
                autoComplete="one-time-code"
                enterKeyHint="go"
                maxLength={length}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={[hintId, errorId].filter(Boolean).join(' ') || undefined}
                autoFocus
            />

            {error ? <InlineError id={errorId}>{error}</InlineError> : null}
        </div>
    );
}
