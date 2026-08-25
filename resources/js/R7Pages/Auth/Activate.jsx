import React from 'react';
import { router, useForm } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import Field from '@record7/components/Field.jsx';
import PasswordField from '@record7/components/PasswordField.jsx';
import Button from '@record7/components/Button.jsx';
import Notice from '@record7/components/Notice.jsx';

/**
 * First-time activation.
 *
 * A new account has no password at all until this screen is completed. The
 * link is single-use and expires, and an invalid one says so plainly and
 * points at the person who can issue another.
 */
export default function Activate({ valid, name, organisationName, activateUrl, signInUrl, error }) {
    const { data, setData, post, processing, errors } = useForm({
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post(activateUrl);
    };

    if (!valid) {
        return (
            <AuthShell title="This link is no longer valid">
                <div className="r7-stack">
                    <Notice tone="problem">
                        Activation links can only be used once, and they expire after three days.
                    </Notice>
                    <p className="r7-muted r7-measure">
                        Ask your manager to send a new invitation. If you have already set a
                        password, sign in as usual.
                    </p>
                    <Button variant="quiet" block onClick={() => router.get(signInUrl)}>
                        Go to sign in
                    </Button>
                </div>
            </AuthShell>
        );
    }

    return (
        <AuthShell
            title="Set up your account"
            intro={<>Welcome{name ? <>, <strong>{name}</strong></> : ''}. Choose a password to finish setting up your {organisationName} account.</>}
        >
            <form className="r7-form" onSubmit={submit} noValidate>
                <Notice tone="problem">{error}</Notice>

                <PasswordField
                    label="Choose a password"
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

                <Button type="submit" block busy={processing} busyLabel="Setting up">
                    Set password and continue
                </Button>
            </form>
        </AuthShell>
    );
}
