import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function ForgotPassword({ submitUrl, loginUrl, status }) {
    const { data, setData, post, processing, errors } = useForm({ company_name: '', email: '' });

    return (
        <main className="f4-auth-page f4-auth-page-simple">
            <Head title="Reset Care One OS password" />
            <section className="f4-auth-card">
                <div className="f4-auth-card-head">
                    <span className="f4-auth-mark" aria-hidden="true">C1</span>
                    <div><h1>Reset your password</h1><p>Care One OS only</p></div>
                </div>
                <p className="f4-auth-copy">Enter your organisation and work email. If they match an active Care One OS account, we will send a time-limited link.</p>
                {status ? <div className="f4-auth-notice" data-tone="success" role="status">{status}</div> : null}
                <form className="f4-auth-form" onSubmit={(event) => { event.preventDefault(); post(submitUrl); }}>
                    <label>
                        <span>Organisation</span>
                        <input value={data.company_name} onChange={(e) => setData('company_name', e.target.value)} autoComplete="organization" required autoFocus />
                        {errors.company_name ? <small className="f4-auth-error">{errors.company_name}</small> : null}
                    </label>
                    <label>
                        <span>Work email</span>
                        <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} autoComplete="email" required />
                        {errors.email ? <small className="f4-auth-error">{errors.email}</small> : null}
                    </label>
                    <button className="f4-auth-submit" type="submit" disabled={processing}>{processing ? 'Sending…' : 'Send secure link'}</button>
                </form>
                <Link className="f4-auth-back" href={loginUrl}>Back to Care One OS sign in</Link>
            </section>
        </main>
    );
}
