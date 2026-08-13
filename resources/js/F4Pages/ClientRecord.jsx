import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';

const empty = {
    name: '', preferred_name: '', pronouns: '', date_of_birth: '', gender: '', nhs_number: '',
    admission_number: '', start_date: '', room_number: '', home_area_id: '', email: '', mobile: '',
    address: '', primary_language: '', communication_needs: '', medication_support: '',
    capacity_consent: '', key_worker: '', allergies: '', allergy_reaction: '', gp_name: '',
    gp_practice: '', pharmacy_name: '', pharmacy_phone: '', em_name: '', relationship: '', em_phone: '',
};

function Field({ label, error, required = false, children }) {
    return (
        <label className="f4-record-field">
            <span>{label}{required ? ' *' : ''}</span>
            {children}
            {error ? <small className="f4-field-error" role="alert">{error}</small> : null}
        </label>
    );
}

function Text({ form, field, label, type = 'text', required = false }) {
    return (
        <Field label={label} error={form.errors[field]} required={required}>
            <input type={type} value={form.data[field] || ''} required={required}
                   onChange={(event) => form.setData(field, event.target.value)} />
        </Field>
    );
}

export default function ClientRecord({
    mode = 'create', client = null, events = [], pendingTransfer = null,
    locations = [], targetServices = [], place = null, user = null,
    roleLabel = null, can = [], accessContext = null,
}) {
    const isEdit = mode === 'edit';
    const initial = { ...empty, ...(client || {}) };
    Object.keys(initial).forEach((key) => { if (initial[key] == null) initial[key] = ''; });
    const form = useForm(initial);
    const lifecycle = useForm({
        lifecycle_status: '',
        effective_at: new Date().toISOString().slice(0, 16),
        reason: '',
    });
    const restore = useForm({ reason: '' });
    const transfer = useForm({ to_service_id: '', reason: '' });

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) form.put(`/frontend4/clients/${client.id}`, { preserveScroll: true });
        else form.post('/frontend4/clients');
    };
    const submitLifecycle = (event) => {
        event.preventDefault();
        lifecycle.post(`/frontend4/clients/${client.id}/lifecycle`, {
            preserveScroll: true,
            onSuccess: () => lifecycle.reset('lifecycle_status', 'reason'),
        });
    };
    const submitRestore = (event) => {
        event.preventDefault();
        restore.post(`/frontend4/clients/${client.id}/restore`, { preserveScroll: true, onSuccess: () => restore.reset() });
    };
    const submitTransfer = (event) => {
        event.preventDefault();
        transfer.post(`/frontend4/clients/${client.id}/transfer`, { preserveScroll: true, onSuccess: () => transfer.reset() });
    };

    return (
        <F4Shell area="clients" title={isEdit ? 'Manage client' : 'Add client'} summary={place}
                 place={place} user={user} roleLabel={roleLabel} can={can} accessContext={accessContext}>
            <Head title={`${isEdit ? 'Manage' : 'Add'} client - Care One OS`} />
            <div className="f4-page-enter f4-record-page">
                <header className="f4-record-heading">
                    <div>
                        <Link className="f4-backlink" href={isEdit ? `/frontend4/clients/${client.id}` : '/frontend4/clients'}>Back to clients</Link>
                        <p className="f4-eyebrow">Client identity and support record</p>
                        <h1>{isEdit ? client.name : 'Add a client'}</h1>
                        <p>Unknown information stays blank. The system will not invent measurements, units, account details or clinical notes.</p>
                    </div>
                    {isEdit ? <span className="f4-tag" data-tone={client.lifecycle_status === 'active' ? 'good' : 'muted'}>{client.lifecycle_status}</span> : null}
                </header>

                <form onSubmit={submit} className="f4-record-form">
                    <section className="f4-record-card">
                        <div className="f4-record-card-head"><h2>Identity</h2><p>Use verified identifiers where available.</p></div>
                        <div className="f4-record-grid">
                            <Text form={form} field="name" label="Legal or recorded name" required />
                            <Text form={form} field="preferred_name" label="Preferred name" />
                            <Text form={form} field="pronouns" label="Pronouns" />
                            <Text form={form} field="date_of_birth" label="Date of birth" type="date" />
                            <Field label="Sex recorded in the existing record" error={form.errors.gender}>
                                <select value={form.data.gender} onChange={(event) => form.setData('gender', event.target.value)}>
                                    <option value="">Not recorded</option><option value="F">Female</option><option value="M">Male</option>
                                </select>
                            </Field>
                            <Text form={form} field="nhs_number" label="NHS number" />
                            <Text form={form} field="admission_number" label="Admission number" />
                            <Text form={form} field="start_date" label="Admission date" type="date" required />
                        </div>
                    </section>

                    <section className="f4-record-card">
                        <div className="f4-record-card-head"><h2>Placement and communication</h2><p>The location must belong to {place} and to your assignment.</p></div>
                        <div className="f4-record-grid">
                            <Field label="Location" error={form.errors.home_area_id}>
                                <select value={form.data.home_area_id} onChange={(event) => form.setData('home_area_id', event.target.value)}>
                                    <option value="">No location assigned</option>
                                    {locations.map((location) => <option key={location.id} value={location.id}>{location.name}</option>)}
                                </select>
                            </Field>
                            <Text form={form} field="room_number" label="Room or placement reference" />
                            <Text form={form} field="primary_language" label="Primary language" />
                            <Text form={form} field="email" label="Email" type="email" />
                            <Text form={form} field="mobile" label="Telephone" />
                            <Field label="Address" error={form.errors.address}><textarea rows="3" value={form.data.address} onChange={(e) => form.setData('address', e.target.value)} /></Field>
                            <Field label="Communication needs" error={form.errors.communication_needs}><textarea rows="3" value={form.data.communication_needs} onChange={(e) => form.setData('communication_needs', e.target.value)} /></Field>
                        </div>
                    </section>

                    <section className="f4-record-card">
                        <div className="f4-record-card-head"><h2>Care and medicine context</h2><p>Record facts only. Weight and other measurements belong in their dated measurement records.</p></div>
                        <div className="f4-record-grid">
                            <Text form={form} field="medication_support" label="Medication support" />
                            <Text form={form} field="capacity_consent" label="Capacity and consent" />
                            <Text form={form} field="key_worker" label="Key worker" />
                            <Field label="Allergies" error={form.errors.allergies}><textarea rows="3" value={form.data.allergies} onChange={(e) => form.setData('allergies', e.target.value)} /></Field>
                            <Field label="Allergy reaction" error={form.errors.allergy_reaction}><textarea rows="3" value={form.data.allergy_reaction} onChange={(e) => form.setData('allergy_reaction', e.target.value)} /></Field>
                        </div>
                    </section>

                    <section className="f4-record-card">
                        <div className="f4-record-card-head"><h2>Contacts</h2></div>
                        <div className="f4-record-grid">
                            <Text form={form} field="gp_name" label="GP name" />
                            <Text form={form} field="gp_practice" label="GP practice" />
                            <Text form={form} field="pharmacy_name" label="Pharmacy" />
                            <Text form={form} field="pharmacy_phone" label="Pharmacy telephone" />
                            <Text form={form} field="em_name" label="Emergency contact" />
                            <Text form={form} field="relationship" label="Relationship" />
                            <Text form={form} field="em_phone" label="Emergency telephone" />
                        </div>
                    </section>
                    {form.errors.client ? <p className="f4-field-error" role="alert">{form.errors.client}</p> : null}
                    <div className="f4-record-actions">
                        <button type="submit" className="f4-btn" disabled={form.processing}>{form.processing ? 'Saving...' : isEdit ? 'Save verified changes' : 'Create client record'}</button>
                        <Link className="f4-btn" data-variant="quiet" href="/frontend4/clients">Cancel</Link>
                    </div>
                </form>

                {isEdit ? (
                    <div className="f4-record-operations">
                        <section className="f4-record-card">
                            <div className="f4-record-card-head"><h2>Lifecycle</h2><p>Changes never erase history and always require a reason.</p></div>
                            {client.lifecycle_status === 'archived' ? (
                                <form onSubmit={submitRestore} className="f4-record-inline">
                                    <Field label="Reason for restoring" error={restore.errors.reason} required><textarea rows="2" value={restore.data.reason} onChange={(e) => restore.setData('reason', e.target.value)} required /></Field>
                                    <button className="f4-btn" type="submit" disabled={restore.processing}>Restore record</button>
                                </form>
                            ) : (
                                <form onSubmit={submitLifecycle} className="f4-record-inline">
                                    <Field label="New status" error={lifecycle.errors.lifecycle_status} required>
                                        <select required value={lifecycle.data.lifecycle_status} onChange={(e) => lifecycle.setData('lifecycle_status', e.target.value)}>
                                            <option value="">Choose status</option>
                                            {['active', 'inactive', 'discharged', 'deceased', 'archived'].filter((s) => s !== client.lifecycle_status).map((s) => <option key={s} value={s}>{s}</option>)}
                                        </select>
                                    </Field>
                                    <Field label="Effective date and time" error={lifecycle.errors.effective_at} required><input type="datetime-local" required value={lifecycle.data.effective_at} onChange={(e) => lifecycle.setData('effective_at', e.target.value)} /></Field>
                                    <Field label="Reason" error={lifecycle.errors.reason} required><textarea rows="2" required value={lifecycle.data.reason} onChange={(e) => lifecycle.setData('reason', e.target.value)} /></Field>
                                    <button className="f4-btn" data-variant={lifecycle.data.lifecycle_status === 'archived' ? 'destructive' : undefined} type="submit" disabled={lifecycle.processing}>Record status change</button>
                                </form>
                            )}
                        </section>

                        <section className="f4-record-card">
                            <div className="f4-record-card-head"><h2>Transfer to another service</h2><p>A request does not move the client. Medication, stock, care and document records must be reconciled before a separate approval workflow can apply it.</p></div>
                            {pendingTransfer ? <div className="f4-record-notice">Transfer request #{pendingTransfer.id} is awaiting review.</div> : targetServices.length ? (
                                <form onSubmit={submitTransfer} className="f4-record-inline">
                                    <Field label="Destination service" error={transfer.errors.to_service_id} required>
                                        <select required value={transfer.data.to_service_id} onChange={(e) => transfer.setData('to_service_id', e.target.value)}>
                                            <option value="">Choose an assigned service</option>
                                            {targetServices.map((service) => <option key={service.id} value={service.id}>{service.title}</option>)}
                                        </select>
                                    </Field>
                                    <Field label="Reason for transfer" error={transfer.errors.reason} required><textarea rows="2" required value={transfer.data.reason} onChange={(e) => transfer.setData('reason', e.target.value)} /></Field>
                                    <button className="f4-btn" type="submit" disabled={transfer.processing}>Request transfer review</button>
                                </form>
                            ) : <p>No other assigned service is available.</p>}
                        </section>

                        <section className="f4-record-card">
                            <div className="f4-record-card-head"><h2>Lifecycle history</h2><p>Append-only events for this client.</p></div>
                            {events.length ? <ol className="f4-record-history">{events.map((event) => (
                                <li key={event.id}><strong>{String(event.type).replaceAll('_', ' ')}</strong><span>{[event.from && event.to ? `${event.from} to ${event.to}` : null, event.effectiveAt, event.reason].filter(Boolean).join(' - ')}</span></li>
                            ))}</ol> : <p>No Frontend 4 lifecycle events have been recorded yet.</p>}
                        </section>
                    </div>
                ) : null}
            </div>
        </F4Shell>
    );
}
