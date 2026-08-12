import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({
    step,
    organisationName,
    organisationUrl,
    loginUrl,
    changeOrganisationUrl,
    forgotUrl,
    status,
    error,
}) {
    const organisation = useForm({ company_name: '' });
    const credentials = useForm({ username: '', password: '' });
    const isCredentialsStep = step === 'credentials';

    function submitOrganisation(event) {
        event.preventDefault();
        organisation.post(organisationUrl, { preserveScroll: true });
    }

    function submitCredentials(event) {
        event.preventDefault();
        credentials.post(loginUrl, { preserveScroll: true, onFinish: () => credentials.reset('password') });
    }

    return (
        <main className="f4-auth-page">
            <Head title="Sign in" />
            <section className="f4-auth-intro" aria-labelledby="f4-auth-title">
                <span className="f4-auth-brand">Care One OS</span>
                <p className="f4-auth-kicker">Medication management</p>
                <h1 id="f4-auth-title">Safe medicines start with the right account.</h1>
                <p>First confirm your organisation, then sign in. Your allocated service is shown only after your password has been verified.</p>
                <ul className="f4-auth-assurance" aria-label="Security information">
                    <li>Separate from the legacy system login</li>
                    <li>Services hidden until authentication</li>
                    <li>Role and access checked at every visit</li>
                </ul>
            </section>

            <section className="f4-auth-card" aria-label="Care One OS sign in">
                <div className="f4-auth-card-head">
                    <span className="f4-auth-mark" aria-hidden="true">C1</span>
                    <div>
                        <h2>{isCredentialsStep ? 'Sign in securely' : 'Find your organisation'}</h2>
                        <p>Step {isCredentialsStep ? '2' : '1'} of 3</p>
                    </div>
                </div>

                <div className="f4-auth-progress" aria-label={`Sign-in step ${isCredentialsStep ? '2' : '1'} of 3`}>
                    <span data-active="true">Organisation</span>
                    <span data-active={isCredentialsStep}>Account</span>
                    <span>Service</span>
                </div>

                {status ? <div className="f4-auth-notice" data-tone="success" role="status">{status}</div> : null}
                {error ? <div className="f4-auth-notice" data-tone="error" role="alert">{error}</div> : null}

                {!isCredentialsStep ? (
                    <form onSubmit={submitOrganisation} className="f4-auth-form">
                        <label>
                            <span>Organisation name or code</span>
                            <input
                                value={organisation.data.company_name}
                                onChange={(event) => organisation.setData('company_name', event.target.value)}
                                autoComplete="organization"
                                autoFocus
                                required
                            />
                            {organisation.errors.company_name ? <small className="f4-auth-error">{organisation.errors.company_name}</small> : null}
                        </label>
                        <button className="f4-auth-submit" type="submit" disabled={organisation.processing}>
                            {organisation.processing ? 'Checking…' : 'Continue securely'}
                        </button>
                    </form>
                ) : (
                    <form onSubmit={submitCredentials} className="f4-auth-form">
                        <div className="f4-auth-organisation">
                            <span>Organisation</span>
                            <strong>{organisationName}</strong>
                            <Link href={changeOrganisationUrl}>Change</Link>
                        </div>

                        <label>
                            <span>Username</span>
                            <input
                                value={credentials.data.username}
                                onChange={(event) => credentials.setData('username', event.target.value)}
                                autoComplete="username"
                                autoFocus
                                required
                            />
                            {credentials.errors.username ? <small className="f4-auth-error">{credentials.errors.username}</small> : null}
                        </label>

                        <label>
                            <span>Password</span>
                            <input
                                type="password"
                                value={credentials.data.password}
                                onChange={(event) => credentials.setData('password', event.target.value)}
                                autoComplete="current-password"
                                required
                            />
                            {credentials.errors.password ? <small className="f4-auth-error">{credentials.errors.password}</small> : null}
                        </label>

                        <div className="f4-auth-actions">
                            <Link href={forgotUrl}>Forgot password?</Link>
                            <button className="f4-auth-submit" type="submit" disabled={credentials.processing}>
                                {credentials.processing ? 'Signing in…' : 'Sign in securely'}
                            </button>
                        </div>
                    </form>
                )}
            </section>
        </main>
    );
}
