import React from 'react';
import { router, useForm } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import Field from '@record7/components/Field.jsx';
import Button from '@record7/components/Button.jsx';
import Notice from '@record7/components/Notice.jsx';

/**
 * Step three: security verification.
 *
 * At this point the password has been accepted but nobody is signed in — the
 * server holds only a short-lived pending reference. Nothing is reachable
 * until this step passes.
 *
 * The prototype code is shown on screen ONLY when the environment supplies
 * one, so a real deployment never advertises a code.
 */
export default function Verify({ prompt, methodLabel, verifyUrl, cancelUrl, prototypeCode, error }) {
    const { data, setData, post, processing, errors } = useForm({ code: '' });

    const submit = (event) => {
        event.preventDefault();
        post(verifyUrl, { onFinish: () => setData('code', '') });
    };

    return (
        <AuthShell
            step={3}
            title="Confirm it is you"
            intro={<>{prompt}{methodLabel ? <> and enter the six-digit code from <strong>{methodLabel}</strong>.</> : ' and enter your six-digit code.'}</>}
            footer={
                <button type="button" className="r7-btn r7-btn--bare" onClick={() => router.get(cancelUrl)}>
                    Start again
                </button>
            }
        >
            <form className="r7-form" onSubmit={submit} noValidate>
                <Notice tone="problem">{error}</Notice>

                {prototypeCode ? (
                    <Notice tone="attention" tag="Test environment">
                        This environment accepts the fixed code <strong>{prototypeCode}</strong>. Real
                        verification replaces this before any live use.
                    </Notice>
                ) : null}

                <Field
                    label="Six-digit code"
                    value={data.code}
                    onChange={(value) => setData('code', value.replace(/\D/g, '').slice(0, 6))}
                    error={errors.code}
                    inputClassName="r7-input r7-input--code"
                    inputMode="numeric"
                    autoComplete="one-time-code"
                    enterKeyHint="go"
                    maxLength={6}
                    autoFocus
                    required
                />

                <Button type="submit" block busy={processing} busyLabel="Checking" disabled={data.code.length < 6}>
                    Confirm
                </Button>
            </form>
        </AuthShell>
    );
}
