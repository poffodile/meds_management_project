import React from 'react';
import { router, useForm } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import Field from '@record7/components/Field.jsx';
import PasswordField from '@record7/components/PasswordField.jsx';
import Button from '@record7/components/Button.jsx';
import Notice from '@record7/components/Notice.jsx';
import AccountUnavailable from '@record7/components/AccountUnavailable.jsx';

/**
 * Sign in, steps one and two.
 *
 * The server decides which step this is. The browser cannot skip ahead: the
 * credential fields do not exist until the server has confirmed and remembered
 * an organisation, because asking for a password before knowing whose password
 * it is would mean checking it against every organisation at once.
 *
 * There is deliberately no organisation list, no dropdown and no suggestion.
 * An unrecognised name gives exactly the same answer as a wrong password, so
 * this screen cannot be used to discover who is a customer.
 */
export default function SignIn({ step, organisationName, urls, supportUrl, status, error }) {
    if (step === 'unavailable') {
        return (
            <AuthShell title="You can't sign in right now">
                <AccountUnavailable signInUrl={urls.change} supportUrl={supportUrl} />
            </AuthShell>
        );
    }

    return step === 'credentials'
        ? <Credentials organisationName={organisationName} urls={urls} status={status} error={error} />
        : <OrganisationStep urls={urls} status={status} error={error} />;
}

function OrganisationStep({ urls, status, error }) {
    const { data, setData, post, processing, errors } = useForm({ organisation: '' });

    const submit = (event) => {
        event.preventDefault();
        post(urls.organisation);
    };

    return (
        <AuthShell
            step={1}
            title="Which organisation do you work for?"
            intro="Enter the name exactly as your manager gave it to you. Capital letters and extra spaces do not matter."
        >
            <form className="r7-form" onSubmit={submit} noValidate>
                <Notice tone="error">{error}</Notice>
                <Notice tone="success">{status}</Notice>

                <Field
                    label="Organisation"
                    value={data.organisation}
                    onChange={(value) => setData('organisation', value)}
                    error={errors.organisation}
                    autoComplete="organization"
                    autoCapitalize="words"
                    enterKeyHint="next"
                    autoFocus
                    required
                />

                <Button type="submit" block busy={processing} busyLabel="Checking">
                    Continue
                </Button>
            </form>
        </AuthShell>
    );
}

function Credentials({ organisationName, urls, status, error }) {
    const { data, setData, post, processing, errors } = useForm({ username: '', password: '' });

    const submit = (event) => {
        event.preventDefault();
        post(urls.credentials, { onFinish: () => setData('password', '') });
    };

    return (
        <AuthShell
            step={2}
            title="Sign in"
            intro={<>You are signing in to <strong>{organisationName}</strong>.</>}
            footer={
                <div className="r7-between">
                    <button type="button" className="r7-btn r7-btn--bare" onClick={() => router.get(urls.forgot)}>
                        I have forgotten my password
                    </button>
                    <button type="button" className="r7-btn r7-btn--bare" onClick={() => router.get(urls.change)}>
                        Change organisation
                    </button>
                </div>
            }
        >
            <form className="r7-form" onSubmit={submit} noValidate>
                <Notice tone="error">{error}</Notice>
                <Notice tone="success">{status}</Notice>

                <Field
                    label="Username"
                    value={data.username}
                    onChange={(value) => setData('username', value)}
                    error={errors.username}
                    autoComplete="username"
                    autoCapitalize="none"
                    autoCorrect="off"
                    spellCheck="false"
                    enterKeyHint="next"
                    autoFocus
                    required
                />

                <PasswordField
                    label="Password"
                    value={data.password}
                    onChange={(value) => setData('password', value)}
                    error={errors.password}
                    autoComplete="current-password"
                    enterKeyHint="go"
                    required
                />

                <Button type="submit" block busy={processing} busyLabel="Signing in">
                    Sign in
                </Button>
            </form>
        </AuthShell>
    );
}
