import React from 'react';
import { router, useForm } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import Field from '@record7/components/Field.jsx';
import Button from '@record7/components/Button.jsx';
import Notice from '@record7/components/Notice.jsx';

/**
 * Password recovery, step one.
 *
 * The answer is the same whether or not the account exists. Saying "no such
 * user" here would turn this form into a way of discovering who works for an
 * organisation, so it never does.
 */
export default function ForgotPassword({ submitUrl, signInUrl, status, localLink }) {
    const { data, setData, post, processing, errors } = useForm({
        organisation: '',
        username: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post(submitUrl);
    };

    return (
        <AuthShell
            title="Reset your password"
            intro="Tell us your organisation and username. If they match an account, we will send a reset link to its work email address."
            footer={
                <button type="button" className="r7-btn r7-btn--bare" onClick={() => router.get(signInUrl)}>
                    Back to sign in
                </button>
            }
        >
            <form className="r7-form" onSubmit={submit} noValidate>
                <Notice tone="ok">{status}</Notice>

                {localLink ? (
                    <Notice tone="attention" tag="Test environment">
                        No email is sent locally. Open the reset link directly:
                        <br />
                        <a href={localLink}>{localLink}</a>
                    </Notice>
                ) : null}

                <Field
                    label="Organisation"
                    value={data.organisation}
                    onChange={(value) => setData('organisation', value)}
                    error={errors.organisation}
                    autoComplete="organization"
                    autoFocus
                    required
                />

                <Field
                    label="Username"
                    value={data.username}
                    onChange={(value) => setData('username', value)}
                    error={errors.username}
                    autoComplete="username"
                    autoCapitalize="none"
                    spellCheck="false"
                    enterKeyHint="go"
                    required
                />

                <Button type="submit" block busy={processing} busyLabel="Sending">
                    Send reset link
                </Button>
            </form>
        </AuthShell>
    );
}
