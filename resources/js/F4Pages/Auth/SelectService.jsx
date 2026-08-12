import React from 'react';
import { Head, router, useForm } from '@inertiajs/react';

export default function SelectService({ organisationName, services, selectUrl, logoutUrl }) {
    const { data, setData, post, processing, errors } = useForm({ service_id: '' });

    function submit(event) {
        event.preventDefault();
        post(selectUrl, { preserveScroll: true });
    }

    return (
        <main className="f4-auth-page f4-auth-page-simple">
            <Head title="Choose service" />
            <section className="f4-auth-card" aria-labelledby="f4-service-title">
                <div className="f4-auth-card-head">
                    <span className="f4-auth-mark" aria-hidden="true">C1</span>
                    <div><h1 id="f4-service-title">Choose your service</h1><p>Step 3 of 3</p></div>
                </div>

                <div className="f4-auth-progress" aria-label="Sign-in step 3 of 3">
                    <span data-active="true">Organisation</span>
                    <span data-active="true">Account</span>
                    <span data-active="true">Service</span>
                </div>

                <p className="f4-auth-copy">Signed in to <strong>{organisationName}</strong>. Choose where you are working today.</p>

                <form onSubmit={submit} className="f4-auth-form">
                    <label>
                        <span>Allocated service</span>
                        <select value={data.service_id} onChange={(event) => setData('service_id', event.target.value)} required autoFocus>
                            <option value="">Select your service</option>
                            {services.map((service) => <option key={service.id} value={service.id}>{service.name}</option>)}
                        </select>
                        {errors.service_id ? <small className="f4-auth-error">{errors.service_id}</small> : null}
                    </label>

                    <button className="f4-auth-submit" type="submit" disabled={processing}>
                        {processing ? 'Opening service…' : 'Continue to Care One OS'}
                    </button>
                </form>

                <button className="f4-auth-signout" type="button" onClick={() => router.post(logoutUrl)}>Sign out</button>
            </section>
        </main>
    );
}
