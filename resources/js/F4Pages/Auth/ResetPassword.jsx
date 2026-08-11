import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function ResetPassword({ token, submitUrl, loginUrl, invalid }) {
    const { data, setData, post, processing, errors } = useForm({
        token: token || '',
        password: '',
        password_confirmation: '',
    });

    return (
        <main className="f4-auth-page f4-auth-page-simple">
            <Head title="Choose a Care One OS password" />
            <section className="f4-auth-card">
                <div className="f4-auth-card-head">
                    <span className="f4-auth-mark" aria-hidden="true">C1</span>
                    <div><h1>{invalid ? 'Link unavailable' : 'Choose a new password'}</h1><p>Care One OS only</p></div>
                </div>
                {invalid ? (
                    <>
                        <div className="f4-auth-notice" data-tone="error" role="alert">This password link is invalid, expired or has already been used.</div>
                        <Link className="f4-auth-submit f4-auth-submit-link" href={loginUrl}>Return to sign in</Link>
                    </>
                ) : (
                    <form className="f4-auth-form" onSubmit={(event) => { event.preventDefault(); post(submitUrl); }}>
                        <p className="f4-auth-copy">Use at least 12 characters with upper and lower-case letters, a number and a symbol.</p>
                        {errors.token ? <div className="f4-auth-notice" data-tone="error">{errors.token}</div> : null}
                        <label>
                            <span>New password</span>
                            <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} autoComplete="new-password" required autoFocus />
                            {errors.password ? <small className="f4-auth-error">{errors.password}</small> : null}
                        </label>
                        <label>
                            <span>Confirm new password</span>
                            <input type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} autoComplete="new-password" required />
                        </label>
                        <button className="f4-auth-submit" type="submit" disabled={processing}>{processing ? 'Updating…' : 'Update Care One OS password'}</button>
                    </form>
                )}
            </section>
        </main>
    );
}
