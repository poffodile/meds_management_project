import React, { useEffect, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';

const blank = {
    medicine_id: '', medication_name_as_written: '', dose_amount: '', dose_unit: '', route: '',
    frequency: '', time_slots: '', as_required: false, prn_details: '', prn_max_daily: '',
    prn_min_interval_hours: '', reason_for_medication: '', administration_instructions: '',
    prescriber: '', pharmacy: '', start_date: new Date().toISOString().slice(0, 10), end_date: '',
    review_due_date: '', prescription_source: '', amendment_reason: '',
};

function Field({ label, error, required = false, hint = null, children }) {
    return (
        <label className="f4-record-field">
            <span>{label}{required ? ' *' : ''}</span>
            {children}
            {hint ? <small>{hint}</small> : null}
            {error ? <small className="f4-field-error" role="alert">{error}</small> : null}
        </label>
    );
}

function Text({ form, field, label, type = 'text', required = false, hint = null }) {
    return (
        <Field label={label} error={form.errors[field]} required={required} hint={hint}>
            <input type={type} step={type === 'number' ? 'any' : undefined} value={form.data[field] ?? ''} required={required}
                   onChange={(event) => form.setData(field, event.target.value)} />
        </Field>
    );
}

function MedicineSummary({ medicine }) {
    if (!medicine) return null;
    return (
        <div className="f4-rx-medicine" data-controlled={medicine.isControlled ? 'true' : undefined}>
            <div><span className="f4-eyebrow">Selected catalogue medicine</span><h3>{medicine.name}</h3></div>
            <dl>
                <div><dt>dm+d / SNOMED CT</dt><dd>{medicine.dmdCode || 'Local uncoded item'}</dd></div>
                <div><dt>Concept</dt><dd>{medicine.conceptLevel || 'Not supplied'}</dd></div>
                <div><dt>Strength</dt><dd>{medicine.strength || 'Not structured'}</dd></div>
                <div><dt>Form</dt><dd>{medicine.form || 'Not structured'}</dd></div>
                <div><dt>Stock unit</dt><dd>{medicine.countableUnit || 'Not structured'}</dd></div>
                <div><dt>Controlled drug</dt><dd>{medicine.isControlled ? `Yes - Schedule ${medicine.cdSchedule}` : 'No'}</dd></div>
            </dl>
        </div>
    );
}

export default function PrescriptionRecord({
    mode = 'create', client, prescription = null, medicine: initialMedicine = null,
    catalogueLoaded = false, place = null, user = null, roleLabel = null, can = [], accessContext = null,
}) {
    const isEdit = mode === 'edit';
    const initial = { ...blank, ...(prescription || {}) };
    if (Array.isArray(initial.time_slots)) initial.time_slots = initial.time_slots.join(', ');
    Object.keys(initial).forEach((key) => { if (initial[key] == null) initial[key] = ''; });
    initial.as_required = Boolean(initial.as_required);

    const form = useForm(initial);
    const [medicine, setMedicine] = useState(initialMedicine);
    const [query, setQuery] = useState('');
    const [results, setResults] = useState([]);
    const [searching, setSearching] = useState(false);

    useEffect(() => {
        if (isEdit || query.trim().length < 2) {
            setResults([]);
            return undefined;
        }
        const controller = new AbortController();
        const timer = setTimeout(async () => {
            setSearching(true);
            try {
                const response = await fetch(`/frontend4/catalogue/medicines?q=${encodeURIComponent(query.trim())}`, {
                    credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: controller.signal,
                });
                const body = response.ok ? await response.json() : { medicines: [] };
                setResults(body.medicines || []);
            } catch (error) {
                if (error.name !== 'AbortError') setResults([]);
            } finally {
                setSearching(false);
            }
        }, 250);
        return () => { clearTimeout(timer); controller.abort(); };
    }, [query, isEdit]);

    const chooseMedicine = (item) => {
        setMedicine(item);
        form.setData({
            ...form.data,
            medicine_id: item.id,
        });
        setQuery('');
        setResults([]);
    };

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) form.put(`/frontend4/clients/${client.id}/medications/${prescription.id}`, { preserveScroll: true });
        else form.post(`/frontend4/clients/${client.id}/medications`, { preserveScroll: true });
    };

    return (
        <F4Shell area="clients" title={isEdit ? 'Amend prescription' : 'Add prescription'} summary={client.name}
                 place={place} user={user} roleLabel={roleLabel} can={can} accessContext={accessContext}>
            <Head title={`${isEdit ? 'Amend' : 'Add'} prescription - Care One OS`} />
            <div className="f4-page-enter f4-record-page f4-rx-page">
                <header className="f4-record-heading">
                    <div>
                        <Link className="f4-backlink" href={`/frontend4/clients/${client.id}#medications`}>Back to medications</Link>
                        <p className="f4-eyebrow">Catalogue identity and prescribed directions</p>
                        <h1>{isEdit ? `Amend version ${prescription.version}` : `New prescription for ${client.name}`}</h1>
                        <p>The medicine identity comes from the shared catalogue. Dose, route and schedule must be copied from the prescription. The system does not supply or check a clinical dose.</p>
                    </div>
                </header>

                <form onSubmit={submit} className="f4-record-form">
                    <section className="f4-record-card">
                        <div className="f4-record-card-head"><h2>1. Identify the medicine</h2><p>No fuzzy matching and no free-text medicine identity.</p></div>
                        {!isEdit ? (
                            catalogueLoaded ? (
                                <div className="f4-rx-picker">
                                    <Field label="Search catalogue by medicine name or exact dm+d code" error={form.errors.medicine_id} required>
                                        <input type="search" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Type at least 2 characters" autoComplete="off" />
                                    </Field>
                                    {searching ? <p className="f4-panel-note">Searching catalogue...</p> : null}
                                    {results.length ? <div className="f4-rx-results" role="listbox">{results.map((item) => (
                                        <button type="button" key={item.id} onClick={() => chooseMedicine(item)}>
                                            <strong>{item.name}</strong><span>{[item.strength, item.form, item.dmdCode ? `dm+d ${item.dmdCode}` : 'Local uncoded item'].filter(Boolean).join(' - ')}</span>
                                        </button>
                                    ))}</div> : null}
                                </div>
                            ) : <div className="f4-record-notice">The catalogue structure is ready, but no selectable catalogue release has been loaded. Import an approved dm+d source before creating new prescriptions.</div>
                        ) : null}
                        <MedicineSummary medicine={medicine} />
                        <Text form={form} field="medication_name_as_written" label="Name exactly as written on the prescription or label" hint="Provenance only - this does not become the medicine identity." />
                    </section>

                    <section className="f4-record-card">
                        <div className="f4-record-card-head"><h2>2. Record the prescribed dose</h2><p>Copy these values from the authorised prescription.</p></div>
                        <div className="f4-record-grid">
                            <Text form={form} field="dose_amount" label="Dose amount" type="number" required />
                            <Text form={form} field="dose_unit" label="Dose unit" required />
                            <Text form={form} field="route" label="Route" required />
                            <Text form={form} field="frequency" label="Frequency wording" required />
                            <Text form={form} field="time_slots" label="Administration times" hint="24-hour times separated by commas, for example 08:00, 20:00. Leave blank only for PRN." />
                            <Field label="Prescription type" error={form.errors.as_required}>
                                <span className="f4-rx-check"><input type="checkbox" checked={form.data.as_required} onChange={(event) => form.setData('as_required', event.target.checked)} /> When required (PRN)</span>
                            </Field>
                            {form.data.as_required ? <>
                                <Text form={form} field="prn_max_daily" label="Maximum doses in 24 hours" type="number" required />
                                <Text form={form} field="prn_min_interval_hours" label="Minimum interval in hours" type="number" required />
                                <Field label="PRN protocol" error={form.errors.prn_details}><textarea rows="3" value={form.data.prn_details} onChange={(event) => form.setData('prn_details', event.target.value)} /></Field>
                            </> : null}
                            <Field label="Administration instructions" error={form.errors.administration_instructions}><textarea rows="3" value={form.data.administration_instructions} onChange={(event) => form.setData('administration_instructions', event.target.value)} /></Field>
                            <Field label="Indication / reason prescribed" error={form.errors.reason_for_medication}><textarea rows="3" value={form.data.reason_for_medication} onChange={(event) => form.setData('reason_for_medication', event.target.value)} /></Field>
                        </div>
                    </section>

                    <section className="f4-record-card">
                        <div className="f4-record-card-head"><h2>3. Source and dates</h2><p>These establish where the prescription came from; they are not a clinical verification by the software.</p></div>
                        <div className="f4-record-grid">
                            <Field label="Prescription source" error={form.errors.prescription_source} required>
                                <select required value={form.data.prescription_source} onChange={(event) => form.setData('prescription_source', event.target.value)}>
                                    <option value="">Choose source</option>
                                    <option value="paper_prescription">Paper prescription</option><option value="gp_record">GP record</option>
                                    <option value="hospital_discharge">Hospital discharge</option><option value="pharmacy_label">Pharmacy label</option><option value="other">Other verified source</option>
                                </select>
                            </Field>
                            <Text form={form} field="prescriber" label="Prescriber" required />
                            <Text form={form} field="pharmacy" label="Pharmacy" />
                            <Text form={form} field="start_date" label="Start date" type="date" required />
                            <Text form={form} field="end_date" label="End date" type="date" />
                            <Text form={form} field="review_due_date" label="Review due" type="date" />
                            {isEdit ? <Field label="Reason for this amendment" error={form.errors.amendment_reason} required><textarea rows="3" required value={form.data.amendment_reason} onChange={(event) => form.setData('amendment_reason', event.target.value)} /></Field> : null}
                        </div>
                    </section>

                    {form.errors.prescription ? <p className="f4-field-error" role="alert">{form.errors.prescription}</p> : null}
                    <div className="f4-record-actions">
                        <button type="submit" className="f4-btn" disabled={form.processing || (!isEdit && !medicine)}>{form.processing ? 'Saving...' : isEdit ? 'Record amendment' : 'Create prescription'}</button>
                        <Link className="f4-btn" data-variant="quiet" href={`/frontend4/clients/${client.id}#medications`}>Cancel</Link>
                    </div>
                </form>
            </div>
        </F4Shell>
    );
}
