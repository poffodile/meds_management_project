import React from 'react';
import { useForm } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import PasswordField from '@record7/components/PasswordField.jsx';
import Button from '@record7/components/Button.jsx';
import Notice from '@record7/components/Notice.jsx';
import TextLink from '@record7/components/TextLink.jsx';

/**
 * The lock screen.
 *
 * Locking is not signing out. The session and the chosen house survive, and
 * only the password is asked for — which is the difference between a control
 * staff actually use on a round and one they work around.
 *
 * The house stays named on screen while locked, so whoever picks the device up
 * can see what it is open on before they unlock it. The person's name is shown
 * because they are already known to this device; nothing else about the house
 * or the people in it is.
 */
export default function Lock({ name, fullName, house, organisationName, unlockUrl, signOutUrl, error }) {
    const { data, setData, post, processing, errors } = useForm({ password: '' });

    const submit = (event) => {
        event.preventDefault();
        post(unlockUrl, { onFinish: () => setData('password', '') });
    };

    return (
        <AuthShell
            title={`Welcome back${name ? `, ${name}` : ''}`}
            intro="Your screen locked itself. Enter your password to carry on where you left off."
            footer={
                <div className="r7-between">
                    <span>Not you?</span>
                    <TextLink href={signOutUrl} method="post">Sign out</TextLink>
                </div>
            }
        >
            <form className="r7-form" onSubmit={submit} noValidate>
                <Notice tone="error">{error}</Notice>

                <div className="r7-where">
                    <span className="r7-where__cell">
                        <span className="r7-where__label">Organisation</span>
                        <span className="r7-where__value">{organisationName ?? 'Not set'}</span>
                    </span>
                    <span className="r7-where__cell">
                        <span className="r7-where__label">House</span>
                        <span className="r7-where__value">{house ?? 'Not chosen'}</span>
                    </span>
                </div>

                <PasswordField
                    label={`Password for ${fullName ?? name ?? 'this account'}`}
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    error={errors.password}
                    autoComplete="current-password"
                    enterKeyHint="go"
                    autoFocus
                    required
                />

                <Button type="submit" block busy={processing} busyLabel="Unlocking">
                    Unlock
                </Button>
            </form>
        </AuthShell>
    );
}
