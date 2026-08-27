import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AuthShell from '@record7/components/AuthShell.jsx';
import Field from '@record7/components/Field.jsx';
import PasswordField from '@record7/components/PasswordField.jsx';
import Button from '@record7/components/Button.jsx';
import Notice from '@record7/components/Notice.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Icon from '@record7/components/Icon.jsx';
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
    const [showHelp, setShowHelp] = useState(false);

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
                    icon="house"
                    placeholder="e.g. Willowbrook House"
                    hint="It is usually on your invitation email or in your staff handbook."
                    value={data.organisation}
                    onChange={(value) => setData('organisation', value)}
                    error={errors.organisation}
                    autoComplete="organization"
                    autoCapitalize="words"
                    enterKeyHint="next"
                    autoFocus
                    required
                />

                <div className="r7-stack-s">
                    <Button type="submit" block arrow busy={processing} busyLabel="Checking">
                        Continue
                    </Button>
                    {/* People move faster through a journey whose length they
                        can see, and slower through one that keeps surprising
                        them with another step. */}
                    <span className="r7-next">Next: your username and password</span>
                </div>

                <div className="r7-help-row">
                    <span>Do not know your organisation name?</span>
                    <TextLink onClick={() => setShowHelp((open) => !open)} aria-expanded={showHelp}>
                        Ask your manager
                    </TextLink>
                </div>

                {showHelp ? (
                    <Notice tone="info" title="Finding your organisation name">
                        Your manager or organisation administrator has the exact name. It is the
                        name of the company you work for, not the name of the house you work in.
                    </Notice>
                ) : null}
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
            footer={
                <div className="r7-between">
                    <span>Cannot get in?</span>
                    <TextLink href={urls.forgot}>I have forgotten my password</TextLink>
                </div>
            }
        >
            <form className="r7-form" onSubmit={submit} noValidate>
                {/* The organisation is settled, so it is shown as a fact with a
                    way to change it rather than as a sentence to read. On a
                    shared device the wrong organisation is a real mistake. */}
                <div className="r7-settled">
                    <span className="r7-settled__tick">
                        <Icon name="tick" className="r7-icon r7-icon--small" />
                    </span>
                    <span className="r7-settled__body">
                        <span className="r7-settled__label">Organisation</span>
                        <span className="r7-settled__value">{organisationName}</span>
                    </span>
                    <span className="r7-settled__change">
                        <TextLink href={urls.change} quiet>Change</TextLink>
                    </span>
                </div>

                <Notice tone="error">{error}</Notice>
                <Notice tone="success">{status}</Notice>

                <Field
                    label="Username"
                    icon="person"
                    placeholder="e.g. j.smith"
                    hint="The username your manager set up, which is usually not your email address."
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

                <div className="r7-stack-s">
                    <Button type="submit" block arrow busy={processing} busyLabel="Signing in">
                        Sign in
                    </Button>
                    <span className="r7-next">Next: choose the house you are working in</span>
                </div>
            </form>
        </AuthShell>
    );
}
