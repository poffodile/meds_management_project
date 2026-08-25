import React from 'react';
import { useForm } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import CodeInput from '@record7/components/CodeInput.jsx';
import Button from '@record7/components/Button.jsx';
import Notice from '@record7/components/Notice.jsx';
import TextLink from '@record7/components/TextLink.jsx';

/**
 * Step three: security verification.
 *
 * At this point the password has been accepted but nobody is signed in — the
 * server holds only a short-lived pending reference. Nothing is reachable
 * until this step passes.
 *
 * WHY THE REASON IS SHOWN
 * A code demanded with no explanation feels arbitrary, and people work around
 * controls that feel arbitrary. Saying "we have not seen this device before"
 * costs a line and buys compliance.
 *
 * THE TEST-ENVIRONMENT LABEL
 * Whenever the fictional fixed code is enabled, it is stated plainly and
 * prominently. Somebody looking at a screenshot must be able to tell at a
 * glance that this is not real security.
 */
export default function Verify({
    prompt, methodLabel, reason, canTrustDevice, recoveryCodesLeft,
    verifyUrl, cancelUrl, prototypeCode, error,
}) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        shared_device: false,
    });

    const tooShort = data.code.length < 6;

    const submit = (event) => {
        event.preventDefault();

        if (tooShort) return;

        post(verifyUrl, { onFinish: () => setData('code', '') });
    };

    return (
        <AuthShell
            step={3}
            title="Confirm it is you"
            intro={reason}
        >
            <form className="r7-form" onSubmit={submit} noValidate>
                {prototypeCode ? (
                    <Notice tone="warning" title="Test environment — this is not real security">
                        This screen only shows what verification could look like. It accepts the
                        fixed code <strong>{prototypeCode}</strong>, which cannot work in
                        production. No authenticator, passkey or message delivery is integrated
                        yet.
                    </Notice>
                ) : null}

                <Notice tone="error">{error}</Notice>

                <p className="r7-small r7-muted r7-measure">
                    {prompt}
                    {methodLabel ? <> using <strong>{methodLabel}</strong>.</> : '.'}
                </p>

                <CodeInput
                    label="Six-digit code"
                    hint="You can also use one of your recovery codes."
                    value={data.code}
                    onChange={(value) => setData('code', value)}
                    error={errors.code}
                />

                {canTrustDevice ? (
                    <label className="r7-check">
                        <input
                            type="checkbox"
                            checked={data.shared_device}
                            onChange={(event) => setData('shared_device', event.target.checked)}
                        />
                        This is a shared device, so do not remember me on it
                    </label>
                ) : (
                    <Notice tone="info" title="Shared device">
                        This device is shared, so everyone confirms their identity each time. It
                        is never remembered for one person.
                    </Notice>
                )}

                <Button type="submit" block busy={processing} busyLabel="Checking" disabled={tooShort}>
                    Confirm
                </Button>

                <div className="r7-between">
                    <TextLink href={cancelUrl} quiet>Start again</TextLink>
                    {recoveryCodesLeft > 0 ? (
                        <span className="r7-xs r7-faint">
                            {recoveryCodesLeft} recovery {recoveryCodesLeft === 1 ? 'code' : 'codes'} left
                        </span>
                    ) : null}
                </div>
            </form>
        </AuthShell>
    );
}
