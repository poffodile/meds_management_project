import React, { useMemo, useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import F3Shell from '@frontend3/components/F3Shell';
import { Badge, Card, CardHead, Person, SafetyStrip, Empty, Note } from '@frontend3/components/F3Atoms';

/**
 * Medication round (spec §6) — "make the safe path the shortest path while
 * documenting every deviation".
 *
 * UNCLUTTERED, via three levels rather than one crowded screen:
 *   1. people in this round        — who needs something
 *   2. that person's medicines     — what they need
 *   3. one medicine, full attention — identity, safety, evidence, outcome, confirm
 *
 * On desktop levels 2–3 sit in the right-hand workspace beside the list.
 * On mobile each level replaces the previous one, with a Back control.
 *
 * NOTHING here decides whether a dose is allowed. Blocking comes from the
 * server (PRN maximum, interval, controlled-drug quantity, round closure); this
 * page reflects it and explains it. Disabling a button is a courtesy, not a
 * control — the server refuses regardless.
 */

/** Reason is mandatory on these. Kept in step with applyRecord() on the server. */
const REASON_REQUIRED = ['R', 'W', 'N', 'O'];

const OUTCOMES = [
    { code: 'A', name: 'Given',         hint: 'Taken as prescribed' },
    { code: 'R', name: 'Refused',       hint: 'Reason required' },
    { code: 'N', name: 'Not available', hint: 'Reason required' },
    { code: 'W', name: 'Withheld',      hint: 'Clinical decision' },
    { code: 'S', name: 'Asleep',        hint: 'Not woken' },
    { code: 'O', name: 'Other',         hint: 'Reason required' },
];

const CODE_LABEL = {
    A: 'Given', S: 'Asleep', R: 'Refused', W: 'Withheld', N: 'Not available', O: 'Other',
};

/**
 * ONE definition of "still to record", used by the tabs, the person rows and
 * the page summary so they can never disagree.
 *
 * A when-required medicine is AVAILABLE, not outstanding — nobody is behind
 * because a PRN dose has not been given. It is still listed and still givable;
 * it just is not counted as work.
 */
const isOutstanding = (row) => !row.code && !row.as_required;

const rowTone = (row) => {
    if (row.code) return row.code === 'A' ? 'good' : 'caution';
    if (row.status === 'overdue') return 'risk';
    if (row.status === 'due_now') return 'neutral';
    return 'ghost';
};

const rowLabel = (row) => {
    if (row.code) return CODE_LABEL[row.code] ?? 'Recorded';
    if (row.status === 'overdue') return 'Overdue';
    if (row.status === 'due_now') return 'Due now';
    if (row.as_required) return 'When required';
    return row.slot || 'Later';
};

/* ------------------------------------------------------------------ level 3 */

function Administer({ resident, row, date, roundKey, locked, staff, onBack, onDone }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        mar_sheet_id: row.mar_sheet_id,
        date,
        time_slot: row.slot || 'PRN',
        code: '',
        dose_given: row.dose || '',
        reason: '',
        notes: '',
        witnessed_by: '',
        witness_user_id: '',
    });

    const needsReason = REASON_REQUIRED.includes(data.code);
    const prnBlocked = row.as_required && row.prn?.blocked;
    const cdBlocked = row.is_controlled && row.cd_needs_quantity;
    const noStock = row.stock !== null && row.stock !== undefined && row.stock <= 0;

    // "Given" is the only outcome these conditions can block. Recording that a
    // dose was NOT given must never be prevented.
    const giveBlockedReason = locked
        ? 'This round has been ended and locked.'
        : cdBlocked
            ? 'This controlled drug needs its dose set as a quantity before it can be given.'
            : prnBlocked
                ? (row.prn?.block_reason || 'Not due yet.')
                : null;

    const witnessNeeded = row.is_controlled && data.code === 'A';
    const canSubmit = data.code
        && !locked
        && (!needsReason || data.reason.trim() !== '')
        && (!witnessNeeded || data.witness_user_id)
        && !(data.code === 'A' && giveBlockedReason);

    const submit = (e) => {
        e.preventDefault();
        post('/frontend3/round/record', {
            preserveScroll: true,
            onSuccess: () => { reset(); onDone(); },
        });
    };

    return (
        <form onSubmit={submit} className="f3-stack">

            <div className="f3-row">
                <button type="button" className="f3-btn f3-btn--sm" onClick={onBack}>← Back</button>
                <span className="f3-spacer f3-xs f3-mut">
                    {resident.name} · {row.slot || 'when required'}
                </span>
            </div>

            {/* Identity stays visible through the whole action. */}
            <Person
                large
                name={resident.name}
                photo={resident.photo}
                meta={[resident.room ? `Room ${resident.room}` : null, resident.dob].filter(Boolean).join(' · ')}
            />

            <SafetyStrip allergies={resident.allergies} risks={resident.risk_flags} />

            {/* The medicine. */}
            <Card flat style={{ padding: 'var(--f3-s4)' }}>
                <div className="f3-row" style={{ gap: 'var(--f3-s2)', marginBottom: 6 }}>
                    <Badge tone={rowTone(row)}>{rowLabel(row)}</Badge>
                    {row.is_controlled && <Badge tone="risk">Controlled drug</Badge>}
                    {row.as_required && <Badge tone="info">When required</Badge>}
                </div>
                <h3>{row.label || row.medication_name}</h3>
                <p className="f3-sm f3-mut" style={{ marginTop: 2 }}>
                    {[row.dose, row.route, row.slot ? `at ${row.slot}` : 'when required'].filter(Boolean).join(' · ')}
                </p>

                <dl className="f3-dl" style={{ marginTop: 'var(--f3-s4)' }}>
                    {row.indication && (<><dt>For</dt><dd>{row.indication}</dd></>)}
                    {row.instruction && (<><dt>Instructions</dt><dd>{row.instruction}</dd></>)}
                    <dt>Stock</dt>
                    <dd>
                        {row.stock === null || row.stock === undefined
                            ? 'Not tracked'
                            : `${row.stock} ${row.unit || ''}`.trim()}
                        {noStock && ' — none recorded'}
                    </dd>
                    {row.as_required && row.prn && (
                        <>
                            <dt>Given today</dt>
                            <dd>
                                {row.prn.given_today}
                                {row.prn.max_daily !== null ? ` of max ${row.prn.max_daily}` : ''}
                                {row.prn.last_given ? ` · last ${row.prn.last_given}` : ''}
                            </dd>
                        </>
                    )}
                </dl>
            </Card>

            {/* Already recorded? Say so plainly rather than letting someone double-dose. */}
            {row.code && (
                <div className="f3-alert f3-alert--info">
                    <span className="f3-alert-mark" aria-hidden="true">◐</span>
                    <div>
                        <div className="f3-alert-title">Already recorded as {CODE_LABEL[row.code]}</div>
                        <div className="f3-alert-text">
                            {row.recorded_by ? `By ${row.recorded_by}` : 'Recorded'}
                            {row.recorded_at ? ` at ${row.recorded_at}` : ''}
                            {row.reason ? ` · ${row.reason}` : ''}
                            . Recording again will replace this entry, and the change is audited.
                        </div>
                    </div>
                </div>
            )}

            {giveBlockedReason && (
                <div className="f3-alert">
                    <span className="f3-alert-mark" aria-hidden="true">▲</span>
                    <div>
                        <div className="f3-alert-title">This dose cannot be given</div>
                        <div className="f3-alert-text">
                            {giveBlockedReason} You can still record another outcome.
                        </div>
                    </div>
                </div>
            )}

            {/* The action. */}
            <div>
                <h4 style={{ marginBottom: 'var(--f3-s3)' }}>What happened?</h4>
                <div className="f3-outcomes">
                    {OUTCOMES.map((o) => (
                        <button
                            key={o.code}
                            type="button"
                            className="f3-outcome"
                            aria-pressed={data.code === o.code}
                            disabled={locked || (o.code === 'A' && !!giveBlockedReason)}
                            onClick={() => setData('code', o.code)}
                        >
                            <span className="f3-outcome-name">{o.name}</span>
                            <span className="f3-outcome-hint">{o.hint}</span>
                        </button>
                    ))}
                </div>
                {errors.code && <p className="f3-error" style={{ marginTop: 8 }}>{errors.code}</p>}
            </div>

            {data.code && (
                <div className="f3-stack" style={{ gap: 'var(--f3-s3)' }}>

                    {data.code === 'A' && (
                        <div className="f3-field">
                            <label className="f3-label" htmlFor="dose">Dose given</label>
                            <input
                                id="dose" className="f3-input" value={data.dose_given}
                                onChange={(e) => setData('dose_given', e.target.value)}
                            />
                            {errors.dose_given && <p className="f3-error">{errors.dose_given}</p>}
                        </div>
                    )}

                    {needsReason && (
                        <div className="f3-field">
                            <label className="f3-label" htmlFor="reason">
                                Reason <span className="f3-req" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="reason" className="f3-input" value={data.reason}
                                placeholder="Why was it not given?"
                                onChange={(e) => setData('reason', e.target.value)}
                            />
                            <span className="f3-hint">
                                A non-administration must say why. This becomes part of the permanent record.
                            </span>
                            {errors.reason && <p className="f3-error">{errors.reason}</p>}
                        </div>
                    )}

                    {witnessNeeded && (
                        <div className="f3-field">
                            <label className="f3-label" htmlFor="witness">
                                Witness <span className="f3-req" aria-hidden="true">*</span>
                            </label>
                            <select
                                id="witness" className="f3-select" value={data.witness_user_id}
                                onChange={(e) => {
                                    const opt = staff.find((s) => s.value === e.target.value);
                                    setData((d) => ({ ...d, witness_user_id: e.target.value, witnessed_by: opt?.label || '' }));
                                }}
                            >
                                <option value="">Choose a witness…</option>
                                {staff.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                            </select>
                            <span className="f3-hint">
                                You do not appear in this list. A controlled drug cannot be self-witnessed.
                            </span>
                            {errors.witness_user_id && <p className="f3-error">{errors.witness_user_id}</p>}
                        </div>
                    )}

                    <div className="f3-field">
                        <label className="f3-label" htmlFor="notes">Note <span className="f3-mut">(optional)</span></label>
                        <textarea
                            id="notes" className="f3-textarea" value={data.notes}
                            placeholder="Anything the next person should know…"
                            onChange={(e) => setData('notes', e.target.value)}
                        />
                    </div>

                    {/* Confirmation — what is actually being written. */}
                    <div className="f3-confirm">
                        <h4 style={{ marginBottom: 'var(--f3-s3)' }}>You are about to record</h4>
                        <dl className="f3-dl">
                            <dt>Person</dt><dd>{resident.name}</dd>
                            <dt>Medicine</dt><dd>{row.label || row.medication_name}</dd>
                            <dt>Outcome</dt><dd>{CODE_LABEL[data.code]}</dd>
                            <dt>Time</dt><dd>{row.slot || 'when required'} · {date}</dd>
                            {data.code === 'A' && row.stock !== null && row.stock !== undefined && (
                                <><dt>Stock</dt><dd>{row.stock} → {Math.max(0, row.stock - 1)} {row.unit || ''}</dd></>
                            )}
                            {witnessNeeded && data.witnessed_by && (<><dt>Witnessed by</dt><dd>{data.witnessed_by}</dd></>)}
                        </dl>
                        <p className="f3-xs f3-mut" style={{ marginTop: 'var(--f3-s3)' }}>
                            This is a permanent clinical record. It cannot be deleted — a mistake is
                            corrected by a further entry, and both remain in the audit history.
                        </p>
                    </div>

                    {errors.mar_sheet_id && <p className="f3-error">{errors.mar_sheet_id}</p>}

                    <button className="f3-btn f3-btn--primary f3-btn--block" disabled={!canSubmit || processing}>
                        {processing ? 'Recording…' : 'Confirm and record'}
                    </button>
                </div>
            )}
        </form>
    );
}

/* ------------------------------------------------------------------ level 2 */

function PersonMedicines({ resident, locked, onBack, onPick }) {
    const rows = resident.rows;
    const outstanding = rows.filter(isOutstanding);
    const prn = rows.filter((r) => !r.code && r.as_required);
    const done = rows.filter((r) => r.code);

    return (
        <div className="f3-stack">
            <div className="f3-row">
                <button type="button" className="f3-btn f3-btn--sm f3-only-sm" onClick={onBack}>← People</button>
                <span className="f3-spacer f3-xs f3-mut">
                    {outstanding.length} to record · {done.length} recorded
                    {prn.length > 0 ? ` · ${prn.length} when required` : ''}
                </span>
            </div>

            <Person
                large
                name={resident.name}
                photo={resident.photo}
                meta={[resident.room ? `Room ${resident.room}` : null, resident.dob].filter(Boolean).join(' · ')}
            />

            <SafetyStrip allergies={resident.allergies} risks={resident.risk_flags} />

            {outstanding.length > 0 && (
                <div className="f3-stack" style={{ gap: 'var(--f3-s2)' }}>
                    {outstanding.map((row) => (
                        <button
                            key={`${row.mar_sheet_id}-${row.slot || 'prn'}`}
                            type="button"
                            className={[
                                'f3-medrow',
                                row.status === 'overdue' && 'f3-medrow--overdue',
                                row.status === 'due_now' && 'f3-medrow--due',
                            ].filter(Boolean).join(' ')}
                            onClick={() => onPick(row)}
                            aria-label={`${row.label || row.medication_name}, ${[row.dose, row.route].filter(Boolean).join(', ')}, ${rowLabel(row)}`}
                        >
                            <span style={{ flex: 1, minWidth: 0 }}>
                                <span className="f3-medname">{row.label || row.medication_name}</span>
                                <span className="f3-meddose" style={{ display: 'block' }}>
                                    {[row.dose, row.route].filter(Boolean).join(' · ')}
                                </span>
                            </span>
                            <Badge tone={rowTone(row)}>{rowLabel(row)}</Badge>
                        </button>
                    ))}
                </div>
            )}

            {outstanding.length === 0 && (
                <Empty title="Nothing outstanding for this person">
                    {done.length > 0
                        ? `All ${done.length} scheduled ${done.length === 1 ? 'medicine has' : 'medicines have'} an outcome for this round.`
                        : 'No scheduled medicines in this round.'}
                </Empty>
            )}

            {/* When-required medicines. Available, not owed — so they sit apart
                from the work list and are never counted as outstanding. */}
            {prn.length > 0 && (
                <>
                    <h4>When required · not owed</h4>
                    <div className="f3-stack" style={{ gap: 'var(--f3-s2)' }}>
                        {prn.map((row) => (
                            <button
                                key={`${row.mar_sheet_id}-prn`}
                                type="button"
                                className="f3-medrow"
                                onClick={() => onPick(row)}
                                aria-label={`${row.label || row.medication_name}, when required${row.prn?.blocked ? `, not available: ${row.prn.block_reason}` : ''}`}
                            >
                                <span style={{ flex: 1, minWidth: 0 }}>
                                    <span className="f3-medname">{row.label || row.medication_name}</span>
                                    <span className="f3-meddose" style={{ display: 'block' }}>
                                        {row.prn?.blocked
                                            ? row.prn.block_reason
                                            : [row.dose, row.route].filter(Boolean).join(' · ')}
                                    </span>
                                </span>
                                <Badge tone={row.prn?.blocked ? 'caution' : 'info'}>
                                    {row.prn?.blocked ? 'Not yet' : 'Available'}
                                </Badge>
                            </button>
                        ))}
                    </div>
                </>
            )}

            {done.length > 0 && (
                <>
                    <h4>Recorded</h4>
                    <div className="f3-stack" style={{ gap: 'var(--f3-s2)' }}>
                        {done.map((row) => (
                            <button
                                key={`${row.mar_sheet_id}-${row.slot || 'prn'}-done`}
                                type="button"
                                className="f3-medrow f3-medrow--done"
                                onClick={() => onPick(row)}
                                aria-label={`${row.label || row.medication_name}, recorded as ${rowLabel(row)}`}
                            >
                                <span style={{ flex: 1, minWidth: 0 }}>
                                    <span className="f3-medname">{row.label || row.medication_name}</span>
                                    <span className="f3-meddose" style={{ display: 'block' }}>
                                        {row.recorded_at ? `at ${row.recorded_at}` : ''}
                                        {row.recorded_by ? ` · ${row.recorded_by}` : ''}
                                    </span>
                                </span>
                                <Badge tone={rowTone(row)}>{rowLabel(row)}</Badge>
                            </button>
                        ))}
                    </div>
                </>
            )}

            {locked && (
                <div className="f3-alert">
                    <span className="f3-alert-mark" aria-hidden="true">▲</span>
                    <div>
                        <div className="f3-alert-title">This round is ended</div>
                        <div className="f3-alert-text">No further doses can be recorded against it.</div>
                    </div>
                </div>
            )}
        </div>
    );
}

/* ------------------------------------------------------- end / re-open round */

/**
 * Ending a round locks it.
 *
 * Deliberately possible with doses still unrecorded — a round can honestly end
 * with gaps (someone was out, a medicine was unavailable), and forcing a false
 * "given" to close it would be far worse than an honest gap. So the control
 * states plainly what is being left behind, and asks once.
 */
function EndRound({ date, roundKey, roundLabel, outstanding, locked, closure, canReopen }) {
    const [asking, setAsking] = useState(false);
    const endForm = useForm({ date, round: roundKey });
    const reopenForm = useForm({ date, round: roundKey });

    if (locked) {
        return (
            <div className="f3-stack" style={{ gap: 'var(--f3-s3)' }}>
                <p className="f3-sm f3-mut">
                    Ended{closure?.by ? ` by ${closure.by}` : ''}{closure?.at ? ` at ${closure.at}` : ''}.
                </p>
                {canReopen ? (
                    <button
                        type="button"
                        className="f3-btn f3-btn--sm"
                        disabled={reopenForm.processing}
                        onClick={() => reopenForm.post('/frontend3/round/reopen', { preserveScroll: true })}
                    >
                        {reopenForm.processing ? 'Re-opening…' : 'Re-open round'}
                    </button>
                ) : (
                    <p className="f3-xs f3-mut">A manager can re-open it if something still needs recording.</p>
                )}
            </div>
        );
    }

    if (!asking) {
        return (
            <button type="button" className="f3-btn f3-btn--sm" onClick={() => setAsking(true)}>
                End {roundLabel.toLowerCase()} round
            </button>
        );
    }

    return (
        <div className="f3-confirm f3-stack" style={{ gap: 'var(--f3-s3)' }}>
            <div>
                <h4 style={{ marginBottom: 6 }}>End the {roundLabel.toLowerCase()} round?</h4>
                <p className="f3-sm">
                    {outstanding > 0 ? (
                        <>
                            <b>{outstanding} {outstanding === 1 ? 'medicine' : 'medicines'} will be left
                            without an outcome.</b> That is allowed — but the gap stays on the record,
                            and the round locks. Nobody can record against it until a manager re-opens it.
                        </>
                    ) : (
                        <>Everything in this round has an outcome. The round will lock.</>
                    )}
                </p>
            </div>
            <div className="f3-row">
                <button
                    type="button"
                    className="f3-btn f3-btn--primary f3-btn--sm"
                    disabled={endForm.processing}
                    onClick={() => endForm.post('/frontend3/round/end', { preserveScroll: true })}
                >
                    {endForm.processing ? 'Ending…' : 'Yes, end the round'}
                </button>
                <button type="button" className="f3-btn f3-btn--ghost f3-btn--sm" onClick={() => setAsking(false)}>
                    Cancel
                </button>
            </div>
        </div>
    );
}

/* ------------------------------------------------------------------ level 1 */

export default function Round({
    auth, rounds, grid, date, currentRound, closures, home, staff = [], canReopen = false,
}) {
    const [roundKey, setRoundKey] = useState(currentRound);
    const [clientId, setClientId] = useState(null);
    const [row, setRow] = useState(null);

    const residents = grid[roundKey] || [];
    const locked = Boolean(closures?.[roundKey]);
    // `rounds` is a LIST of {key, label, window} — not a map keyed by round.
    // Treating it as a map silently yields undefined, which loses the round
    // name, the tab selection and the counts without throwing anything.
    const round = rounds.find((r) => r.key === roundKey) || {};

    const resident = useMemo(
        () => residents.find((r) => r.client_id === clientId) || null,
        [residents, clientId],
    );

    // A round's counts, so the tabs say where the work is.
    const counts = useMemo(() => {
        const out = {};
        rounds.forEach(({ key }) => {
            out[key] = (grid[key] || []).reduce(
                (n, r) => n + r.rows.filter(isOutstanding).length, 0,
            );
        });
        return out;
    }, [grid, rounds]);

    const outstanding = residents.reduce((n, r) => n + r.rows.filter(isOutstanding).length, 0);

    const pickRound = (k) => { setRoundKey(k); setClientId(null); setRow(null); };
    const pickPerson = (id) => { setClientId(id); setRow(null); };

    return (
        <F3Shell
            title="Medication round"
            area="today"
            user={auth?.user}
            heading={`${round.label || 'Medication'} round`}
            summary={
                outstanding === 0
                    ? 'Everything in this round has an outcome recorded.'
                    : `${outstanding} ${outstanding === 1 ? 'medicine' : 'medicines'} still to record across ${residents.length} ${residents.length === 1 ? 'person' : 'people'}.`
            }
            sync={`${round.window || ''}${round.window ? ' · ' : ''}${date}`}
            context={[{ label: home || 'Your home', strong: true }]}
            action={<Link className="f3-btn" href="/frontend3">← Today</Link>}
        >

            <div className="f3-segment" role="group" aria-label="Round">
                {rounds.map((r) => (
                    <button
                        key={r.key}
                        type="button"
                        aria-pressed={r.key === roundKey}
                        onClick={() => pickRound(r.key)}
                    >
                        {r.label}
                        {counts[r.key] > 0 && (
                            <span className="f3-segcount">
                                {counts[r.key]}
                                <span className="f3-sr-only"> still to record</span>
                            </span>
                        )}
                    </button>
                ))}
            </div>

            {locked && (
                <div className="f3-alert">
                    <span className="f3-alert-mark" aria-hidden="true">▲</span>
                    <div>
                        <div className="f3-alert-title">
                            {round.label} round ended{closures[roundKey]?.by ? ` by ${closures[roundKey].by}` : ''}
                            {closures[roundKey]?.at ? ` at ${closures[roundKey].at}` : ''}
                        </div>
                        <div className="f3-alert-text">
                            It is locked. A manager can re-open it if something still needs recording.
                        </div>
                    </div>
                </div>
            )}

            <div className="f3-grid f3-grid--main">

                {/* Level 1 — people. Hidden on mobile once you are inside someone. */}
                <Card className={clientId ? 'f3-hide-sm' : undefined}>
                    <CardHead
                        title="People in this round"
                        sub="Sorted by urgency, not alphabetically"
                    >
                        <Badge tone={outstanding > 0 ? 'neutral' : 'good'}>
                            {outstanding > 0 ? `${outstanding} to record` : 'Complete'}
                        </Badge>
                    </CardHead>

                    {residents.length === 0 ? (
                        <Empty title="Nobody has medicines in this round">
                            Try another round using the tabs above.
                        </Empty>
                    ) : (
                        <div className="f3-stack" style={{ gap: 'var(--f3-s2)' }}>
                            {residents.map((r) => {
                                const left = r.rows.filter(isOutstanding).length;
                                const over = r.rows.filter((x) => isOutstanding(x) && x.status === 'overdue').length;
                                const state = over > 0 ? 'overdue' : left > 0 ? 'due' : 'done';
                                return (
                                    <button
                                        key={r.client_id}
                                        type="button"
                                        className={[
                                            'f3-personrow',
                                            state === 'overdue' && 'f3-personrow--alert',
                                            state === 'due' && 'f3-personrow--due',
                                            state === 'done' && 'f3-personrow--done',
                                            r.client_id === clientId && 'f3-personrow--open',
                                        ].filter(Boolean).join(' ')}
                                        onClick={() => pickPerson(r.client_id)}
                                        aria-label={
                                            `${r.name}${r.room ? `, room ${r.room}` : ''}, `
                                            + (left > 0 ? `${left} to record` : 'all recorded')
                                            + (over > 0 ? `, ${over} overdue` : '')
                                            + (r.allergies?.length ? ', has allergies' : '')
                                        }
                                    >
                                        <Person
                                            name={r.name}
                                            photo={r.photo}
                                            meta={[
                                                r.room ? `Room ${r.room}` : null,
                                                left > 0 ? `${left} to record` : 'all recorded',
                                            ].filter(Boolean).join(' · ')}
                                        />
                                        <span className="f3-spacer f3-row" style={{ justifyContent: 'flex-end', gap: 'var(--f3-s2)' }}>
                                            {r.allergies?.length > 0 && <Badge tone="risk">Allergy</Badge>}
                                            {over > 0 && <Badge tone="risk">{over} overdue</Badge>}
                                            {over === 0 && left > 0 && <Badge tone="neutral">{left} due</Badge>}
                                            {left === 0 && <Badge tone="good">Done</Badge>}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>
                    )}

                    {residents.length > 0 && (
                        <>
                            <hr className="f3-hr" style={{ margin: 'var(--f3-s5) 0 var(--f3-s4)' }} />
                            <EndRound
                                date={date}
                                roundKey={roundKey}
                                roundLabel={round.label || 'this'}
                                outstanding={outstanding}
                                locked={locked}
                                closure={closures?.[roundKey]}
                                canReopen={canReopen}
                            />
                        </>
                    )}
                </Card>

                {/* Levels 2 and 3 — the workspace. */}
                <Card className="f3-workspace">
                    {!resident && (
                        <Empty title="Choose a person">
                            Their medicines appear here. You record one medicine at a time — identity,
                            safety, evidence, outcome, then confirm.
                        </Empty>
                    )}

                    {resident && !row && (
                        <PersonMedicines
                            resident={resident}
                            locked={locked}
                            onBack={() => setClientId(null)}
                            onPick={setRow}
                        />
                    )}

                    {resident && row && (
                        <Administer
                            resident={resident}
                            row={row}
                            date={date}
                            roundKey={roundKey}
                            locked={locked}
                            staff={staff}
                            onBack={() => setRow(null)}
                            onDone={() => { setRow(null); router.reload({ only: ['grid'] }); }}
                        />
                    )}
                </Card>
            </div>

            <Note>
                Every dose recorded here goes through the same server checks as the existing round
                screen — prescription locking, when-required maximums, controlled-drug rules and
                round closure. This page shows those decisions; it does not make them.
            </Note>

        </F3Shell>
    );
}
