import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ servicesUrl, loginUrl, forgotUrl, status, error }) {
    const { data, setData, post, processing, errors } = useForm({
        company_name: '',
        home: '',
        username: '',
        password: '',
    });
    const [services, setServices] = useState([]);
    const [serviceMessage, setServiceMessage] = useState('Enter your organisation and username, then load your services.');
    const [loadingServices, setLoadingServices] = useState(false);

    async function loadServices() {
        const company = data.company_name.trim();
        const username = data.username.trim();
        if (!company || !username) {
            setServices([]);
            setServiceMessage('Enter your organisation and username first.');
            return;
        }

        setLoadingServices(true);
        setServiceMessage('Loading services…');
        try {
            const response = await fetch(`${servicesUrl}?company_name=${encodeURIComponent(company)}&username=${encodeURIComponent(username)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const payload = response.ok ? await response.json() : { services: [] };
            const available = Array.isArray(payload.services) ? payload.services : [];
            setServices(available);
            setData('home', available.length === 1 ? String(available[0].id) : '');
            setServiceMessage(available.length ? 'Choose the service you are working in.' : 'No services matched that organisation.');
        } catch {
            setServices([]);
            setServiceMessage('Services could not be loaded. Please try again.');
        } finally {
            setLoadingServices(false);
        }
    }

    function submit(event) {
        event.preventDefault();
        post(loginUrl, { preserveScroll: true });
    }

    return (
        <main className="f4-auth-page">
            <Head title="Sign in" />
            <section className="f4-auth-intro" aria-labelledby="f4-auth-title">
                <span className="f4-auth-brand">Care One OS</span>
                <p className="f4-auth-kicker">Medication management</p>
                <h1 id="f4-auth-title">Safe medicines start with the right account.</h1>
                <p>Sign in to the service where you are working. Your role and permissions are checked before any clinical information is shown.</p>
                <ul className="f4-auth-assurance" aria-label="Security information">
                    <li>Separate from the legacy system login</li>
                    <li>Role and service access checked at every visit</li>
                    <li>Sign-in activity recorded for assurance</li>
                </ul>
            </section>

            <section className="f4-auth-card" aria-label="Care One OS sign in">
                <div className="f4-auth-card-head">
                    <span className="f4-auth-mark" aria-hidden="true">C1</span>
                    <div><h2>Welcome back</h2><p>Use your Care One OS details</p></div>
                </div>

                {status ? <div className="f4-auth-notice" data-tone="success" role="status">{status}</div> : null}
                {error ? <div className="f4-auth-notice" data-tone="error" role="alert">{error}</div> : null}

                <form onSubmit={submit} className="f4-auth-form">
                    <label>
                        <span>Organisation</span>
                        <div className="f4-auth-combo">
                            <input
                                value={data.company_name}
                                onChange={(e) => { setData('company_name', e.target.value); setServices([]); setData('home', ''); }}
                                onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); loadServices(); } }}
                                autoComplete="organization"
                                required
                            />
                            <button type="button" onClick={loadServices} disabled={loadingServices}>
                                {loadingServices ? 'Loading' : 'Load'}
                            </button>
                        </div>
                        {errors.company_name ? <small className="f4-auth-error">{errors.company_name}</small> : null}
                    </label>

                    <label>
                        <span>Username</span>
                        <input value={data.username} onChange={(e) => { setData('username', e.target.value); setServices([]); setData('home', ''); }} autoComplete="username" required />
                        {errors.username ? <small className="f4-auth-error">{errors.username}</small> : null}
                    </label>

                    <label>
                        <span>Service</span>
                        <select value={data.home} onChange={(e) => setData('home', e.target.value)} required disabled={!services.length}>
                            <option value="">Select your service</option>
                            {services.map((service) => <option key={service.id} value={service.id}>{service.name}</option>)}
                        </select>
                        <small className={errors.home ? 'f4-auth-error' : 'f4-auth-help'}>{errors.home || serviceMessage}</small>
                    </label>

                    <label>
                        <span>Password</span>
                        <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} autoComplete="current-password" required />
                        {errors.password ? <small className="f4-auth-error">{errors.password}</small> : null}
                    </label>

                    <div className="f4-auth-actions">
                        <Link href={forgotUrl}>Forgot password?</Link>
                        <button className="f4-auth-submit" type="submit" disabled={processing}>{processing ? 'Signing in…' : 'Sign in securely'}</button>
                    </div>
                </form>
            </section>
        </main>
    );
}
