/**
 * frontend4 — the medication round.
 *
 * Two panes on a desktop: the queue of people on the left, the chosen person's
 * medicines on the right. On a phone it becomes one column — the queue, and
 * tapping someone opens their medicines below it, which is what the
 * specification means by "clicking a person can open their medicines on the
 * same page".
 *
 * SCOPE — M2. Queue, medicines, recording. When-required medicines, witnessing,
 * stock deduction and sign-off arrive in M3, one at a time.
 *
 * Recording goes to the server, which is where every rule actually lives. If it
 * refuses — no reason given, a controlled drug with no witness, a PRN maximum
 * reached — the server's own message is shown against the field. This page
 * never guesses at a rule or pre-empts one.
 */

import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import {
    Empty,
    Field,
    Medicine,
    Person,
    Progress,
    Row,
    RowCard,
    SafetyStrip,
    Status,
} from '@frontend4/components/F4Atoms';
import * as Icon from '@frontend4/components/F4Icons';

/** What a person's remaining work amounts to, in words as well as a tint. */
function queueState(p) {
    if (p.overdue > 0) return { status: 'overdue', note: `${p.overdue} overdue` };
    if (p.due > 0) return { status: 'due', note: `${p.due} due now` };
    if (p.later > 0) return { status: 'upcoming', note: `${p.later} later` };
    return { status: 'given', note: 'All recorded' };
}

/**
 * The panel for recording one medicine's outcome.
 *
 * The order is deliberate and is the safe sequence: the medicine and its
 * details are read first, then an outcome is chosen, and only then does the
 * reason appear — before the confirm button, never after it. A carer must not
 * be able to commit and then be told what they should have typed.
 */
function RecordPanel({ medicine, outcomes, date, round, clientId, recordUrl, onDone }) {
    const form = useForm({
        mar_sheet_id: medicine.mar_sheet_id,
        date,
        round,
        client: clientId,
        time_slot: medicine.slot || 'PRN',
        code: '',
        reason: '',
        notes: '',
    });

    const chosen = outcomes.find((o) => o.code === form.data.code);
    const needsReason = chosen?.needsReason;

    function submit(e) {
        e.preventDefault();
        form.post(recordUrl, {
            preserveScroll: true,
            onSuccess: () => { form.reset(); onDone?.(); },
        });
    }

    return (
        <form onSubmit={submit} style={{ marginTop: 16 }}>
            <Field
                id={`outcome-${medicine.mar_sheet_id}`}
                label="What happened?"
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
                            // Clear a reason typed for a different outcome, so
                            // nobody submits an explanation that no longer fits.
                            const next = outcomes.find((o) => o.code === e.target.value);
                            if (!next?.needsReason) form.setData('reason', '');
                        }}
                    >
                        <option value="">Choose an outcome…</option>
                        {outcomes.map((o) => (
                            <option key={o.code} value={o.code}>{o.label}</option>
                        ))}
                    </select>
                )}
            </Field>

            {/* The reason appears BEFORE the confirm button, not after it. The
                server refuses without one too — both halves, or neither. */}
            {needsReason ? (
                <Field
                    id={`reason-${medicine.mar_sheet_id}`}
                    label="Why?"
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

            <Field id={`notes-${medicine.mar_sheet_id}`} label="Notes" error={form.errors.notes}>
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

            {/* Anything the server refused that is not tied to a single field —
                a PRN maximum, a missing witness, a closed round. Shown in the
                server's own words rather than a generic failure. */}
            {form.errors.mar_sheet_id || form.errors.witnessed_by || form.errors.code_general ? (
                <p className="f4-field-error" role="alert" style={{ marginBottom: 12 }}>
                    {form.errors.mar_sheet_id || form.errors.witnessed_by || form.errors.code_general}
                </p>
            ) : null}

            <div className="f4-actions" style={{ marginTop: 0 }}>
                <button
                    type="submit"
                    className="f4-btn"
                    disabled={!form.data.code || form.processing}
                >
                    {form.processing ? 'Recording…' : 'Record'}
                </button>
                <button type="button" className="f4-btn" data-variant="quiet" onClick={onDone}>
                    Cancel
                </button>
            </div>
        </form>
    );
}

/** One medicine: what it is, what was recorded, and how to record it. */
function MedicineRow({ medicine, outcomes, date, round, clientId, recordUrl, canRecord }) {
    const [open, setOpen] = useState(false);
    const recorded = Boolean(medicine.code);

    return (
        <div className="f4-row" data-status={recorded ? medicine.outcomeStatus : medicine.status === 'overdue' ? 'overdue' : undefined} style={{ display: 'block' }}>
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 16, flexWrap: 'wrap' }}>
                <div style={{ flex: '1 1 320px', minWidth: 0 }}>
                    <Medicine
                        name={medicine.name}
                        strength={medicine.strength}
                        form={medicine.form}
                        dose={medicine.dose}
                        route={medicine.route}
                        due={medicine.slot}
                        instruction={medicine.instruction}
                        indication={medicine.indication}
                    />

                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 10, marginTop: 10, alignItems: 'center' }}>
                        {medicine.isControlled ? (
                            <Status status="witness" label="Controlled drug" variant="badge" />
                        ) : null}
                        {medicine.lowStock ? (
                            <Status
                                status={medicine.stock !== null && medicine.stock <= 0 ? 'overdue' : 'late'}
                                label={medicine.stock !== null && medicine.stock <= 0 ? 'Out of stock' : 'Low stock'}
                                note={medicine.stock !== null ? `${medicine.stock} ${medicine.unit || ''} left`.trim() : undefined}
                                variant="badge"
                            />
                        ) : (
                            medicine.stock !== null ? (
                                <span className="f4-row-sub">
                                    {medicine.stock} {medicine.unit || ''} in stock
                                </span>
                            ) : null
                        )}
                    </div>
                </div>

                <div style={{ flex: '0 0 auto', display: 'flex', flexDirection: 'column', gap: 8, alignItems: 'flex-end' }}>
                    {recorded ? (
                        <>
                            <Status status={medicine.outcomeStatus} label={medicine.outcome} />
                            <span className="f4-row-sub" style={{ textAlign: 'right' }}>
                                {medicine.recordedAt ? `at ${medicine.recordedAt}` : null}
                                {medicine.recordedBy ? ` by ${medicine.recordedBy}` : null}
                            </span>
                            {medicine.reason ? (
                                <span className="f4-row-sub" style={{ textAlign: 'right' }}>{medicine.reason}</span>
                            ) : null}
                        </>
                    ) : (
                        <>
                            <Status
                                status={medicine.status === 'overdue' ? 'overdue' : medicine.status === 'due_now' ? 'due' : 'upcoming'}
                            />
                            {canRecord ? (
                                <button
                                    type="button"
                                    className="f4-btn"
                                    data-size="sm"
                                    data-variant={open ? 'quiet' : undefined}
                                    onClick={() => setOpen((v) => !v)}
                                >
                                    {open ? 'Close' : 'Record'}
                                </button>
                            ) : null}
                        </>
                    )}
                </div>
            </div>

            {open && !recorded ? (
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
        </div>
    );
}

export default function Round({
    can,
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
    const canRecord = Array.isArray(can) && can.includes('record_administration');

    const filtered = search.trim()
        ? queue.filter((p) => p.name.toLowerCase().includes(search.trim().toLowerCase()))
        : queue;

    const outstanding = queue.filter((p) => p.state === 'overdue' || p.state === 'due').length;

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
                    : `${progress.recorded} of ${progress.doses} doses recorded · ${outstanding} ${outstanding === 1 ? 'client' : 'clients'} still to see`
            }
            place={place}
            placeSub={round.window ? `${round.label} · ${round.window}` : round.label}
            user={user}
            roleLabel={roleLabel}
            can={can}
            lastSync={now}
        >
            <Head title="Medication round" />

            {/* Which round. A carer may be preparing the next one, so this is a
                choice rather than a fact — but it defaults to the one we are in. */}
            <div className="f4-actions" style={{ marginTop: 0, marginBottom: 16 }}>
                {rounds.map((r) => (
                    <Link
                        key={r.key}
                        href={`/frontend4/round?date=${date}&round=${r.key}`}
                        className="f4-btn"
                        data-size="sm"
                        data-variant={r.key === round.key ? undefined : 'quiet'}
                    >
                        {r.label}
                    </Link>
                ))}
            </div>

            {closure ? (
                <div className="f4-offline" role="status" style={{ marginBottom: 16, borderRadius: 10 }}>
                    <Icon.Shield />
                    <span>
                        This round has been closed. Nothing further can be recorded against it
                        until it is reopened.
                    </span>
                </div>
            ) : null}

            <section className="f4-card" style={{ marginBottom: 16 }}>
                <Progress
                    percent={progress.percent}
                    label={`${progress.recorded} of ${progress.doses} doses recorded in the ${round.label.toLowerCase()} round`}
                />
                <p className="f4-row-sub" style={{ marginTop: 8 }}>
                    {progress.recorded} of {progress.doses} doses recorded ·{' '}
                    {progress.peopleDone} of {progress.people} clients complete ·{' '}
                    {progress.outstanding} still to record
                </p>
            </section>

            <div className="f4-cols">
                {/* ── The queue ───────────────────────────────────────────── */}
                <div>
                    <RowCard
                        title="Clients"
                        note={outstanding ? `${outstanding} to see` : 'All recorded'}
                    >
                        <div style={{ padding: '12px 16px', borderBottom: '1px solid var(--f4-line)' }}>
                            <label className="f4-sr" htmlFor="round-search">Search clients</label>
                            <input
                                id="round-search"
                                className="f4-input"
                                type="search"
                                placeholder="Search by name"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>

                        {filtered.length === 0 ? (
                            <Empty
                                title={search ? 'Nobody matches that' : 'Nobody is scheduled in this round'}
                                body={
                                    search
                                        ? 'Try part of the name, or clear the search to see everyone in this round.'
                                        : `No client has a medicine scheduled in the ${round.label.toLowerCase()} round today. Check the date and the home in the bar above if that looks wrong.`
                                }
                            />
                        ) : (
                            filtered.map((p) => {
                                const state = queueState(p);
                                const isOpen = p.client_id === selectedClientId;

                                return (
                                    <button
                                        key={p.client_id}
                                        type="button"
                                        className="f4-row"
                                        data-status={state.status}
                                        data-done={p.state === 'given' ? 'true' : undefined}
                                        onClick={() => choose(p.client_id)}
                                        aria-current={isOpen ? 'true' : undefined}
                                        style={isOpen ? { background: 'var(--f4-info-b, var(--f4-sunken))' } : undefined}
                                    >
                                        <span className="f4-row-main">
                                            <Person name={p.name} photo={p.photo} meta={p.meta} />
                                            {p.allergies.length ? (
                                                <span style={{ display: 'block', marginTop: 6 }}>
                                                    <SafetyStrip allergies={p.allergies} />
                                                </span>
                                            ) : null}
                                        </span>
                                        <span className="f4-row-end">
                                            {p.nextSlot ? <span className="f4-row-time">{p.nextSlot}</span> : null}
                                            <Status status={state.status} note={state.note} />
                                        </span>
                                    </button>
                                );
                            })
                        )}
                    </RowCard>
                </div>

                {/* ── The chosen person's medicines ───────────────────────── */}
                <div>
                    {!selected ? (
                        <section className="f4-card">
                            <Empty
                                title="Choose a client"
                                body="Pick someone from the list to see their medicines for this round."
                            />
                        </section>
                    ) : (
                        <>
                            {/* Identity and allergies stay above the medicines the
                                whole time they are being recorded. */}
                            <section className="f4-card" style={{ marginBottom: 16 }}>
                                <Person
                                    name={selected.name}
                                    photo={selected.photo}
                                    meta={[selected.meta, selected.dob ? `Born ${selected.dob}` : null].filter(Boolean).join(' · ')}
                                    size="lg"
                                />
                                {selected.allergies.length || selected.risks.length ? (
                                    <div style={{ marginTop: 12, display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                                        <SafetyStrip allergies={selected.allergies} />
                                        {selected.risks.length ? (
                                            <SafetyStrip risks={selected.risks} tone="caution" />
                                        ) : null}
                                    </div>
                                ) : null}
                            </section>

                            <RowCard
                                title="Medicines due"
                                note={`${selected.medicines.length} in this round`}
                            >
                                {selected.medicines.length === 0 ? (
                                    <Empty
                                        title="Nothing scheduled"
                                        body={`${selected.name} has no medicine scheduled in the ${round.label.toLowerCase()} round.`}
                                    />
                                ) : (
                                    selected.medicines.map((m) => (
                                        <MedicineRow
                                            key={`${m.mar_sheet_id}-${m.slot || 'prn'}`}
                                            medicine={m}
                                            outcomes={outcomes}
                                            date={date}
                                            round={round.key}
                                            clientId={selected.client_id}
                                            recordUrl={recordUrl}
                                            canRecord={canRecord && !closure}
                                        />
                                    ))
                                )}
                            </RowCard>
                        </>
                    )}
                </div>
            </div>
        </F4Shell>
    );
}
