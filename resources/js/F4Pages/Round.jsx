/**
 * frontend4 - medication round.
 *
 * Layout follows the supplied medication-round reference package, while the
 * data and recording actions remain wired to the existing Inertia props and
 * Laravel medication round endpoint.
 */

import React, { useMemo, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { Empty, Field, Progress, SafetyStrip, Status } from '@frontend4/components/F4Atoms';
import * as Icon from '@frontend4/components/F4Icons';

function initials(name) {
    const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
}

function queueState(p) {
    if (p.needsAttention) return { status: 'late', label: 'Attention', note: 'Review' };
    if (p.overdue > 0) return { status: 'overdue', label: 'Overdue', note: `${p.overdue} overdue` };
    if (p.due > 0) return { status: 'due', label: 'Due now', note: `${p.due} due` };
    if (p.later > 0) return { status: 'upcoming', label: 'Upcoming', note: `${p.later} later` };
    return { status: 'given', label: 'Completed', note: 'All recorded' };
}

function medicineState(medicine) {
    if (medicine.code) {
        return {
            status: medicine.outcomeStatus || 'given',
            label: medicine.outcome || 'Recorded',
            note: medicine.recordedAt ? `at ${medicine.recordedAt}` : null,
        };
    }
    if (medicine.status === 'overdue') return { status: 'overdue', label: 'Overdue' };
    if (medicine.status === 'due_now' || medicine.status === 'due') return { status: 'due', label: 'Due now' };
    return { status: 'upcoming', label: 'Upcoming' };
}

function stockText(medicine) {
    if (medicine.stock === null || medicine.stock === undefined || medicine.stock === '') return 'Not recorded';
    return `${medicine.stock} ${medicine.unit || ''}`.trim();
}

function medicineDetail(medicine) {
    return [medicine.strength, medicine.form, medicine.route].filter(Boolean).join(' - ') || 'Prescription details not recorded';
}

function RecordPanel({ medicine, outcomes, date, round, clientId, recordUrl, onDone }) {
    const form = useForm({
        mar_sheet_id: medicine.mar_sheet_id,
        date,
        round,
        client: clientId,
        time_slot: medicine.slot || 'PRN',
        code: '',
        dose_given: medicine.dose || '',
        witnessed_by: '',
        reason: '',
        notes: '',
    });

    const chosen = outcomes.find((o) => o.code === form.data.code);
    const needsReason = chosen?.needsReason;
    const needsWitness = medicine.isControlled && form.data.code === 'A';

    function submit(e) {
        e.preventDefault();
        form.post(recordUrl, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onDone?.();
            },
        });
    }

    return (
        <form className="f4-round-record" onSubmit={submit}>
            <div className="f4-round-record-grid">
                <Field
                    id={`outcome-${medicine.mar_sheet_id}-${medicine.slot || 'prn'}`}
                    label="Outcome"
                    error={form.errors.code}
                    required
                >
                    {(props) => (
                        <select
                            {...props}
                            className="f4-select"
                            value={form.data.code}
                            onChange={(e) => {
                                form.setData('code', e.target.value);
                                const next = outcomes.find((o) => o.code === e.target.value);
                                if (!next?.needsReason) form.setData('reason', '');
                            }}
                        >
                            <option value="">Choose outcome</option>
                            {outcomes.map((o) => (
                                <option key={o.code} value={o.code}>{o.label}</option>
                            ))}
                        </select>
                    )}
                </Field>

                <Field
                    id={`dose-${medicine.mar_sheet_id}-${medicine.slot || 'prn'}`}
                    label="Dose given"
                    error={form.errors.dose_given}
                >
                    {(props) => (
                        <input
                            {...props}
                            className="f4-input"
                            type="text"
                            value={form.data.dose_given}
                            onChange={(e) => form.setData('dose_given', e.target.value)}
                        />
                    )}
                </Field>
            </div>

            {needsWitness ? (
                <Field
                    id={`witness-${medicine.mar_sheet_id}-${medicine.slot || 'prn'}`}
                    label="Witness"
                    hint="Required by the server when this controlled drug is administered."
                    error={form.errors.witnessed_by}
                    required
                >
                    {(props) => (
                        <input
                            {...props}
                            className="f4-input"
                            type="text"
                            value={form.data.witnessed_by}
                            onChange={(e) => form.setData('witnessed_by', e.target.value)}
                            placeholder="Witness name"
                        />
                    )}
                </Field>
            ) : null}

            {needsReason ? (
                <Field
                    id={`reason-${medicine.mar_sheet_id}-${medicine.slot || 'prn'}`}
                    label="Reason"
                    hint={chosen.hint}
                    error={form.errors.reason}
                    required
                >
                    {(props) => (
                        <input
                            {...props}
                            className="f4-input"
                            type="text"
                            value={form.data.reason}
                            onChange={(e) => form.setData('reason', e.target.value)}
                        />
                    )}
                </Field>
            ) : null}

            <Field id={`notes-${medicine.mar_sheet_id}-${medicine.slot || 'prn'}`} label="Notes" error={form.errors.notes}>
                {(props) => (
                    <textarea
                        {...props}
                        className="f4-textarea"
                        rows={2}
                        value={form.data.notes}
                        onChange={(e) => form.setData('notes', e.target.value)}
                    />
                )}
            </Field>

            {form.errors.mar_sheet_id || form.errors.code_general ? (
                <p className="f4-field-error" role="alert">
                    {form.errors.mar_sheet_id || form.errors.code_general}
                </p>
            ) : null}

            <div className="f4-round-record-actions">
                <button type="button" className="f4-btn" data-variant="secondary" onClick={onDone}>
                    Cancel
                </button>
                <button type="submit" className="f4-btn" disabled={!form.data.code || form.processing}>
                    {form.processing ? 'Recording...' : 'Record outcome'}
                </button>
            </div>
        </form>
    );
}

function MedicineCard({ medicine, index, outcomes, date, round, clientId, recordUrl, canRecord, prn = false }) {
    const [open, setOpen] = useState(false);
    const recorded = Boolean(medicine.code);
    const state = medicineState(medicine);
    const prnMeta = medicine.prn || {};

    return (
        <article className="f4-round-med" data-status={state.status} data-recorded={recorded ? 'true' : undefined}>
            <header className="f4-round-med-head">
                <span className="f4-round-med-number">{recorded ? <Icon.Given /> : (prn ? 'P' : index + 1)}</span>
                <span className="f4-round-med-title">
                    <span>
                        {prn ? <i className="f4-round-chip">PRN</i> : null}
                        {medicine.isControlled ? <i className="f4-round-chip" data-tone="witness">Controlled drug</i> : null}
                    </span>
                    <strong>{medicine.name}</strong>
                    <small>{medicineDetail(medicine)}</small>
                </span>
                <span className="f4-round-med-time">
                    <small>{prn ? 'Availability' : 'Scheduled'}</small>
                    <strong>{prn ? (prnMeta.blocked ? prnMeta.block_reason : 'Available when required') : (medicine.slot || 'Not recorded')}</strong>
                </span>
                <span className="f4-round-med-stock">
                    <small>Stock</small>
                    <strong>{stockText(medicine)}</strong>
                </span>
                <Status status={state.status} label={state.label} note={state.note} fill="soft" />
            </header>

            <section className="f4-round-instruction">
                <div>
                    <small>{prn ? 'PRN protocol' : 'Directions'}</small>
                    <strong>{medicine.instruction || 'No administration instruction recorded.'}</strong>
                </div>
                <div>
                    <small>Reason prescribed</small>
                    <strong>{medicine.indication || 'Not recorded'}</strong>
                </div>
            </section>

            <section className="f4-round-med-meta">
                <span><small>Dose</small><strong>{medicine.dose || 'Not recorded'}</strong></span>
                <span><small>Route</small><strong>{medicine.route || 'Not recorded'}</strong></span>
                <span><small>Last recorded</small><strong>{medicine.recordedAt || prnMeta.last_given || 'Not recorded'}</strong></span>
                <span><small>Current stock</small><strong>{stockText(medicine)}</strong></span>
            </section>

            {prn ? (
                <section className="f4-round-prn-rules">
                    <span><small>Given today</small><strong>{prnMeta.given_today ?? 0}</strong></span>
                    <span><small>Max in 24h</small><strong>{prnMeta.max_daily ?? 'Not recorded'}</strong></span>
                    <span><small>Minimum interval</small><strong>{prnMeta.interval_hours ? `${prnMeta.interval_hours} h` : 'Not recorded'}</strong></span>
                    <span><small>Next available</small><strong>{prnMeta.next_available || 'Now'}</strong></span>
                </section>
            ) : null}

            {recorded ? (
                <div className="f4-round-result" data-status={state.status}>
                    <span><Icon.Given /></span>
                    <div>
                        <strong>{medicine.outcome || 'Recorded'}</strong>
                        <small>
                            {[medicine.reason ? `Reason: ${medicine.reason}` : null, medicine.notes, medicine.witnessed_by ? `Witness: ${medicine.witnessed_by}` : null]
                                .filter(Boolean)
                                .join(' - ') || 'No additional note recorded.'}
                        </small>
                        <em>{[medicine.recordedAt ? `Recorded at ${medicine.recordedAt}` : null, medicine.recordedBy ? `by ${medicine.recordedBy}` : null].filter(Boolean).join(' ')}</em>
                    </div>
                    {canRecord ? (
                        <button type="button" onClick={() => setOpen((v) => !v)}>
                            {open ? 'Close' : 'Change'}
                        </button>
                    ) : null}
                </div>
            ) : (
                <div className="f4-round-med-actions">
                    <button
                        type="button"
                        className="f4-btn"
                        disabled={!canRecord || prnMeta.blocked}
                        onClick={() => setOpen((v) => !v)}
                    >
                        {open ? 'Close recording' : prn ? 'Start PRN assessment' : 'Record medicine'}
                    </button>
                    {prnMeta.blocked ? <span>{prnMeta.block_reason}</span> : null}
                </div>
            )}

            {open && canRecord ? (
                <RecordPanel
                    medicine={medicine}
                    outcomes={outcomes}
                    date={date}
                    round={round}
                    clientId={clientId}
                    recordUrl={recordUrl}
                    onDone={() => setOpen(false)}
                />
            ) : null}
        </article>
    );
}

function QueueItem({ person, selected, onChoose }) {
    const state = queueState(person);
    return (
        <button
            type="button"
            className="f4-round-person"
            data-active={selected ? 'true' : undefined}
            data-status={state.status}
            onClick={onChoose}
            aria-current={selected ? 'true' : undefined}
        >
            <b>{initials(person.name)}</b>
            <span>
                <strong>{person.name}</strong>
                <small>{[person.meta, `${person.total} medicines`].filter(Boolean).join(' - ')}</small>
                <em>{person.needsAttention ? 'Needs attention' : state.note}</em>
            </span>
            <time>
                {person.nextSlot || '--:--'}
                <small>{state.label}</small>
            </time>
        </button>
    );
}

function CompletionReview({ selected, round, progress, onClose }) {
    if (!selected) return null;
    const medicines = selected.medicines || [];
    const prns = selected.prnMedicines || [];
    const recorded = medicines.filter((m) => m.code).length;
    const outstanding = medicines.length - recorded;

    return (
        <div className="f4-round-modal" role="dialog" aria-modal="true" aria-labelledby="round-complete-title">
            <section className="f4-round-sheet">
                <header>
                    <button type="button" onClick={onClose} aria-label="Close review"><Icon.Close /></button>
                    <div>
                        <p className="f4-round-eyebrow">Final safety review</p>
                        <h2 id="round-complete-title">Complete {selected.name}</h2>
                        <span>{round.label} round - {progress.recorded} of {progress.doses} doses recorded</span>
                    </div>
                    <b>{initials(selected.name)}</b>
                </header>

                {selected.allergies?.length ? (
                    <div className="f4-round-modal-alert">
                        <Icon.Alert />
                        <span><small>Known allergy</small><strong>{selected.allergies.join(' - ')}</strong></span>
                    </div>
                ) : null}

                <div className="f4-round-review-stats">
                    <article><small>Scheduled</small><strong>{medicines.length}</strong><span>medicines</span></article>
                    <article><small>Recorded</small><strong>{recorded}</strong><span>outcomes</span></article>
                    <article><small>Outstanding</small><strong data-warn={outstanding > 0 ? 'true' : undefined}>{outstanding}</strong><span>require action</span></article>
                    <article><small>PRN</small><strong>{prns.length}</strong><span>available</span></article>
                </div>

                <section className="f4-round-review-list">
                    <div><p className="f4-round-eyebrow">Administration summary</p><h3>Scheduled medicines</h3></div>
                    {medicines.length ? medicines.map((medicine) => {
                        const state = medicineState(medicine);
                        return (
                            <article key={`${medicine.mar_sheet_id}-${medicine.slot || 'scheduled'}`}>
                                <Status status={state.status} label={state.label} fill="soft" />
                                <span><strong>{medicine.name}</strong><small>{medicineDetail(medicine)}</small></span>
                                <time>{medicine.recordedAt || 'Not recorded'}</time>
                            </article>
                        );
                    }) : <p>No scheduled medicines for this person in this round.</p>}
                </section>

                <footer>
                    <button type="button" className="f4-btn" data-variant="secondary" onClick={onClose}>Return to medicines</button>
                    <button type="button" className="f4-btn" disabled={outstanding > 0}>Ready for sign-off</button>
                </footer>
            </section>
        </div>
    );
}

export default function Round({
    can,
    accessContext,
    roleLabel,
    place,
    date,
    now,
    user,
    rounds,
    round,
    closure,
    queue,
    selectedClientId,
    selected,
    progress,
    outcomes,
    recordUrl,
}) {
    const [search, setSearch] = useState('');
    const [filter, setFilter] = useState('all');
    const [showComplete, setShowComplete] = useState(false);
    const canRecord = Array.isArray(can) && can.includes('record_administration');

    const filters = [
        { key: 'all', label: 'All' },
        { key: 'due', label: 'Due now' },
        { key: 'late', label: 'Attention' },
        { key: 'given', label: 'Completed' },
    ];

    const filtered = useMemo(() => {
        const needle = search.trim().toLowerCase();
        return (queue || []).filter((p) => {
            const state = queueState(p).status;
            const matchesFilter = filter === 'all' || state === filter || (filter === 'due' && state === 'overdue');
            const matchesSearch = !needle || String(p.name || '').toLowerCase().includes(needle);
            return matchesFilter && matchesSearch;
        });
    }, [queue, search, filter]);

    const outstanding = (queue || []).filter((p) => p.state === 'overdue' || p.state === 'due' || p.needsAttention).length;
    const selectedMedicines = selected?.medicines || [];
    const selectedPrns = selected?.prnMedicines || [];
    const recordedForSelected = selectedMedicines.filter((m) => m.code).length;

    function choose(clientId) {
        router.get(
            '/frontend4/round',
            { date, round: round.key, client: clientId },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }

    return (
        <F4Shell
            area="round"
            title="Medication round"
            summary={
                closure
                    ? `${round.label} round closed by ${closure.by || 'a colleague'}${closure.at ? ` at ${closure.at}` : ''}`
                    : `${progress.recorded} of ${progress.doses} doses recorded - ${outstanding} ${outstanding === 1 ? 'client' : 'clients'} still to see`
            }
            place={place}
            placeSub={round.window ? `${round.label} - ${round.window}` : round.label}
            user={user}
            roleLabel={roleLabel}
            can={can}
            accessContext={accessContext}
            lastSync={now}
        >
            <Head title="Medication round" />

            <section className="f4-round-page">
                <div className="f4-round-switcher" aria-label="Medication rounds">
                    {rounds.map((r) => (
                        <Link
                            key={r.key}
                            href={`/frontend4/round?date=${date}&round=${r.key}`}
                            className="f4-round-tab"
                            data-active={r.key === round.key ? 'true' : undefined}
                        >
                            <strong>{r.label}</strong>
                            <small>{r.window || 'No window'}</small>
                        </Link>
                    ))}
                </div>

                <section className="f4-round-hero">
                    <div>
                        <p className="f4-round-eyebrow">{date} - {round.window || 'Round window not recorded'}</p>
                        <h2>{round.label} round</h2>
                        <p>Record each medicine immediately after administration.</p>
                    </div>
                    <div className="f4-round-progress">
                        <span><b>{progress.recorded}</b> of {progress.doses} doses recorded</span>
                        <Progress percent={progress.percent} label={`${progress.recorded} of ${progress.doses} doses recorded`} />
                    </div>
                </section>

                {closure ? (
                    <section className="f4-round-alert" data-tone="locked">
                        <Icon.Shield />
                        <div><strong>Round closed</strong><span>Nothing further can be recorded until this round is reopened.</span></div>
                    </section>
                ) : (
                    <section className="f4-round-alert">
                        <Icon.Shield />
                        <div><strong>Safety check</strong><span>Confirm the right person, medicine, dose, route, time and right to refuse before recording.</span></div>
                    </section>
                )}

                <div className="f4-round-layout">
                    <aside className="f4-round-queue">
                        <header>
                            <div><p className="f4-round-eyebrow">Round queue</p><h2>Clients</h2></div>
                            <span>{outstanding ? `${outstanding} to see` : 'All recorded'}</span>
                        </header>
                        <label className="f4-round-search" htmlFor="round-search">
                            <Icon.Search />
                            <input
                                id="round-search"
                                type="search"
                                placeholder="Search clients"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </label>
                        <div className="f4-round-filters">
                            {filters.map((item) => (
                                <button
                                    key={item.key}
                                    type="button"
                                    className={filter === item.key ? 'active' : ''}
                                    onClick={() => setFilter(item.key)}
                                >
                                    {item.label}
                                </button>
                            ))}
                        </div>
                        <div className="f4-round-people">
                            {filtered.length ? filtered.map((p) => (
                                <QueueItem
                                    key={p.client_id}
                                    person={p}
                                    selected={p.client_id === selectedClientId}
                                    onChoose={() => choose(p.client_id)}
                                />
                            )) : (
                                <Empty
                                    title={search ? 'Nobody matches that' : 'Nobody is scheduled'}
                                    body={search ? 'Try a different name.' : `No client has a medicine scheduled in the ${round.label.toLowerCase()} round.`}
                                />
                            )}
                        </div>
                    </aside>

                    <section className="f4-round-work">
                        {!selected ? (
                            <div className="f4-round-empty">
                                <Empty title="Choose a client" body="Pick someone from the queue to see their medicines for this round." />
                            </div>
                        ) : (
                            <>
                                <article className="f4-round-person-banner">
                                    <b>{initials(selected.name)}</b>
                                    <div>
                                        <p className="f4-round-eyebrow">Selected client</p>
                                        <h2>{selected.name}</h2>
                                        <span>{[selected.meta, selected.dob ? `Born ${selected.dob}` : null].filter(Boolean).join(' - ') || 'Profile details not recorded'}</span>
                                    </div>
                                    <div className="f4-round-person-alerts">
                                        <SafetyStrip allergies={selected.allergies || []} />
                                        <SafetyStrip risks={selected.risks || []} tone="caution" />
                                    </div>
                                </article>

                                <div className="f4-round-summary">
                                    <span><small>Scheduled time</small><strong>{round.window || 'Not recorded'}</strong></span>
                                    <span><small>Medicines</small><strong>{selectedMedicines.length}</strong></span>
                                    <span><small>Recorded</small><strong>{recordedForSelected}</strong></span>
                                    <span><small>PRN available</small><strong>{selectedPrns.length}</strong></span>
                                </div>

                                <section className="f4-round-section">
                                    <header className="f4-round-section-head">
                                        <div><p className="f4-round-eyebrow">Medication administration</p><h2>Medicines due now</h2></div>
                                        <span>{recordedForSelected}/{selectedMedicines.length} recorded</span>
                                    </header>
                                    <div className="f4-round-meds">
                                        {selectedMedicines.length ? selectedMedicines.map((medicine, index) => (
                                            <MedicineCard
                                                key={`${medicine.mar_sheet_id}-${medicine.slot || 'scheduled'}`}
                                                medicine={medicine}
                                                index={index}
                                                outcomes={outcomes}
                                                date={date}
                                                round={round.key}
                                                clientId={selected.client_id}
                                                recordUrl={recordUrl}
                                                canRecord={canRecord && !closure}
                                            />
                                        )) : (
                                            <Empty title="Nothing scheduled" body={`${selected.name} has no scheduled medicine in this round.`} />
                                        )}
                                    </div>
                                </section>

                                <section className="f4-round-section">
                                    <header className="f4-round-section-head">
                                        <div><p className="f4-round-eyebrow">When required medicine</p><h2>PRN available</h2></div>
                                        <span>{selectedPrns.length} available</span>
                                    </header>
                                    <div className="f4-round-meds">
                                        {selectedPrns.length ? selectedPrns.map((medicine, index) => (
                                            <MedicineCard
                                                key={`${medicine.mar_sheet_id}-prn-${index}`}
                                                medicine={medicine}
                                                index={index}
                                                prn
                                                outcomes={outcomes}
                                                date={date}
                                                round={round.key}
                                                clientId={selected.client_id}
                                                recordUrl={recordUrl}
                                                canRecord={canRecord && !closure}
                                            />
                                        )) : (
                                            <Empty title="No PRN medicines" body="No when-required medicine is available for this client from the current record." />
                                        )}
                                    </div>
                                </section>

                                <section className="f4-round-audit">
                                    <header><div><p className="f4-round-eyebrow">Permanent record</p><h2>Activity and change log</h2></div><span>Live MAR record</span></header>
                                    <article>
                                        <time>{now || '--:--'}</time>
                                        <i />
                                        <div><strong>Current round loaded</strong><p>Administration records are written to the existing MAR service when an outcome is submitted.</p><small>{user || 'Current user'}</small></div>
                                    </article>
                                </section>

                                <footer className="f4-round-complete">
                                    <div>
                                        <strong>{selectedMedicines.length - recordedForSelected ? `${selectedMedicines.length - recordedForSelected} medicines still require an outcome` : 'All scheduled medicines recorded'}</strong>
                                        <span>Review the client before moving on.</span>
                                    </div>
                                    <button type="button" className="f4-btn" onClick={() => setShowComplete(true)}>
                                        Review and complete
                                    </button>
                                </footer>
                            </>
                        )}
                    </section>
                </div>
            </section>

            {showComplete ? (
                <CompletionReview
                    selected={selected}
                    round={round}
                    progress={progress}
                    onClose={() => setShowComplete(false)}
                />
            ) : null}
        </F4Shell>
    );
}
