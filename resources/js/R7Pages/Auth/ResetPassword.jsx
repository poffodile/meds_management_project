import React from 'react';
import { router, useForm } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import Field from '@record7/components/Field.jsx';
import PasswordField from '@record7/components/PasswordField.jsx';
import Button from '@record7/components/Button.jsx';
import Notice from '@record7/components/Notice.jsx';

/**
 * Password recovery, step two.
 *
 * Completing a reset also clears a security lock: someone who can prove
 * control of their recovery route has answered the question the lock asked.
 */
export default function ResetPassword({ valid, resetUrl, requestUrl, signInUrl, error }) {
    const { data, setData, post, processing, errors } = useForm({
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post(resetUrl);
    };

    if (!valid) {
        return (
            <AuthShell title="This reset link has expired">
                <div className="r7-stack">
                    <Notice tone="problem">
                        Reset links last thirty minutes and can only be used once.
                    </Notice>
                    <Button block onClick={() => router.get(requestUrl)}>
                        Request a new link
                    </Button>
                    <Button variant="quiet" block onClick={() => router.get(signInUrl)}>
                        Back to sign in
                    </Button>
                </div>
            </AuthShell>
        );
    }

    return (
        <AuthShell title="Choose a new password">
            <form className="r7-form" onSubmit={submit} noValidate>
                <Notice tone="problem">{error}</Notice>

                <PasswordField
                    label="New password"
                    hint="At least twelve characters, including a letter and a number."
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    error={errors.password}
                    autoComplete="new-password"
                    autoFocus
                    required
                />

                <PasswordField
                    label="Enter it again"
                    value={data.password_confirmation}
                    onChange={(value) => setData('password_confirmation', value)}
                    autoComplete="new-password"
                    enterKeyHint="go"
                    required
                />

                <Button type="submit" block busy={processing} busyLabel="Saving">
                    Save new password
                </Button>
            </form>
        </AuthShell>
    );
}
