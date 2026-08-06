/**
 * frontend4 — MAR sheet (Page 4), Slice A: the grid.
 *
 * The official record for one client: medicines down, days across, a coded box
 * per scheduled dose, a legend that names every code, week navigation and a
 * period summary. Reached from the client's profile (MAR history → View full
 * MAR), not the sidebar. Read-only for now.
 *
 * The wide grid scrolls inside its own region so the page body never scrolls
 * sideways — the design doc's rule, and what keeps it usable on a phone.
 */

import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { Person, SafetyStrip, Stat, Empty, Status, Field } from '@frontend4/components/F4Atoms';
import { allows } from '@frontend4/roles';

/** Correct an entry (lead+): a new outcome + reason (if needed) + why. */
function CorrectForm({ clientId, sheetId, date, slot, weekStart, outcomes }) {
    const [open, setOpen] = useState(false);
    const form = useForm({ date, time_slot: slot, code: '', reason: '', amendment_reason: '', week_start: weekStart });
    const needsReason = outcomes.find((o) => o.code === form.data.code)?.needsReason;

    const submit = (e) => {
        e.preventDefault();
        form.post(`/frontend4/clients/${clientId}/mar/${sheetId}/correct`, {
            preserveScroll: true,
            onSuccess: () => { setOpen(false); form.reset(); },
        });
    };

    if (!open) {
        return (
            <div className="f4-actions" style={{ marginTop: 'var(--f4-s4)' }}>
                <button type="button" className="f4-btn" data-variant="secondary" data-size="sm" onClick={() => setOpen(true)}>
                    Correct this entry
                </button>
            </div>
        );
    }

    return (
        <form onSubmit={submit} className="f4-correct-form">
            <Field id="c-code" label="Corrected outcome" error={form.errors.code} required>
                {(p) => (
                    <select className="f4-select" value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} {...p}>
                        <option value="">Choose an outcome…</option>
                        {outcomes.map((o) => <option key={o.code} value={o.code}>{o.label}</option>)}
                    </select>
                )}
            </Field>
            {needsReason ? (
                <Field id="c-reason" label="Reason for that outcome" error={form.errors.reason} required>
                    {(p) => <textarea className="f4-textarea" rows={2} value={form.data.reason} onChange={(e) => form.setData('reason', e.target.value)} {...p} />}
                </Field>
            ) : null}
            <Field id="c-amend" label="Why is this being corrected?" error={form.errors.amendment_reason} required>
                {(p) => <textarea className="f4-textarea" rows={2} value={form.data.amendment_reason} onChange={(e) => form.setData('amendment_reason', e.target.value)} {...p} />}
            </Field>
            <div className="f4-actions">
                <button type="submit" className="f4-btn" disabled={form.processing}>{form.processing ? 'Saving…' : 'Save correction'}</button>
                <button type="button" className="f4-btn" data-variant="quiet" onClick={() => { setOpen(false); form.reset(); form.clearErrors(); }}>Cancel</button>
            </div>
        </form>
    );
}

/** One label/value pair. */
function KV({ k, v }) {
    return <div className="f4-kv"><span className="f4-kv-k">{k}</span><span className="f4-kv-v">{v}</span></div>;
}

/** One administration box — a coded letter whose meaning is always available. */
function Cell({ cell, onOpen }) {
    if (!cell) return <span className="f4-mar-dot" aria-hidden="true">·</span>;
    const title = `${cell.label}${cell.late ? ' (late)' : ''}`;
    return (
        <button type="button" className="f4-mar-cell f4-mar-cellbtn" data-status={cell.status} title={title} onClick={onOpen}>
            {cell.code}
            {cell.late ? <span className="f4-mar-late" aria-hidden="true" /> : null}
            <span className="f4-sr">{title} — open detail</span>
        </button>
    );
}

/** The full record for one administration, and its correction chain. */
function RegDetail({ cell, ctx }) {
    const d = cell.detail || {};
    return (
        <>
            <div style={{ marginBottom: 'var(--f4-s3)' }}>
                <Status status={cell.status} label={cell.label} note={d.late ? 'late' : undefined} />
            </div>
            <div className="f4-kv-grid">
                {d.scheduled ? <KV k="Scheduled" v={d.scheduled} /> : null}
                {d.actual ? <KV k="Recorded at" v={d.actual} /> : null}
                {d.staff ? <KV k="By" v={d.staff} /> : null}
                {d.witness ? <KV k="Witness" v={d.witness} /> : null}
                {d.dose ? <KV k="Dose given" v={d.dose} /> : null}
            </div>
            {d.reason ? <p className="f4-note"><b>Reason:</b> {d.reason}</p> : null}
            {d.notes ? <p className="f4-note"><b>Notes:</b> {d.notes}</p> : null}
            {d.history && d.history.length ? (
                <div style={{ marginTop: 'var(--f4-s4)' }}>
                    <p className="f4-subhead">Correction history — the original is never lost</p>
                    <section className="f4-card" data-pad="none">
                        <div className="f4-rows">
                            {d.history.map((h, i) => (
                                <div className="f4-row" key={i}>
                                    <span className="f4-row-main">
                                        <span className="f4-row-title">{h.label} · {h.isCurrent ? 'current' : 'superseded'}</span>
                                        <span className="f4-row-sub">
                                            {[h.when, h.staff ? `by ${h.staff}` : null].filter(Boolean).join(' · ')}
                                            {h.amendmentReason ? ` — ${h.amendmentReason}` : ''}
                                        </span>
                                    </span>
                                </div>
                            ))}
                        </div>
                    </section>
                </div>
            ) : null}

            {ctx && ctx.canCorrect ? (
                ctx.isControlled
                    ? <p className="f4-note">This is a controlled drug — corrections go through the controlled-drug register, not here.</p>
                    : <CorrectForm clientId={ctx.clientId} sheetId={ctx.sheetId} date={ctx.date} slot={ctx.slot} weekStart={ctx.weekStart} outcomes={ctx.outcomes} />
            ) : null}
        </>
    );
}

/** The PRN doses given on one day. */
function PrnDetail({ prn }) {
    return (
        <>
            <p className="f4-row-sub" style={{ marginBottom: 'var(--f4-s3)' }}>Given {prn.count}× as needed</p>
            <section className="f4-card" data-pad="none">
                <div className="f4-rows">
                    {prn.doses.map((x, i) => (
                        <div className="f4-row" key={i}>
                            <span className="f4-row-main">
                                <span className="f4-row-title">{x.time || '—'}</span>
                                {x.staff || x.reason ? (
                                    <span className="f4-row-sub">
                                        {x.staff ? `by ${x.staff}` : ''}{x.reason ? `${x.staff ? ' — ' : ''}${x.reason}` : ''}
                                    </span>
                                ) : null}
                            </span>
                            <span className="f4-row-end"><Status status={x.status} label={x.label} /></span>
                        </div>
                    ))}
                </div>
            </section>
        </>
    );
}

function MarDetail({ detail, onClose, canCorrect, outcomes, clientId, weekStart }) {
    const { med, day } = detail;
    const ctx = detail.kind === 'reg' ? {
        canCorrect,
        outcomes,
        clientId,
        weekStart,
        sheetId: med.id,
        isControlled: med.isControlled,
        date: day.date,
        slot: (detail.cell.detail && detail.cell.detail.scheduled) || null,
    } : null;

    return (
        <section className="f4-card f4-mar-detail" role="region" aria-label="Administration detail">
            <div className="f4-mar-detail-head">
                <div>
                    <h3>{med.name}</h3>
                    <p className="f4-row-sub">
                        {[med.strength, med.form].filter(Boolean).join(' · ')}
                        {(med.strength || med.form) ? ' · ' : ''}{day.dow} {day.day}
                    </p>
                </div>
                <button type="button" className="f4-btn" data-variant="quiet" data-size="sm" onClick={onClose}>Close</button>
            </div>
            {detail.kind === 'prn' ? <PrnDetail prn={detail.prn} /> : <RegDetail cell={detail.cell} ctx={ctx} />}
        </section>
    );
}

export default function MarSheet({
    client, days = [], meds = [], summary = {}, legend = [], outcomes = [],
    weekLabel, weekStart, prevWeek, nextWeek, isThisWeek,
    place = null, user = null, roleLabel = null, can = [],
}) {
    const marUrl = (week) => `/frontend4/clients/${client.id}/mar?week_start=${week}`;
    const [detail, setDetail] = useState(null);
    const canCorrect = allows(can, 'correct_record');
    const canExport = allows(can, 'export_report');

    return (
        <F4Shell area="clients" title="MAR sheet" summary={client.name} place={place} user={user} roleLabel={roleLabel} can={can}>
            <Head title={`MAR — ${client.name}`} />

            {/* Identity + allergies stay in view — this is a clinical record. */}
            <div className="f4-profile-head">
                <div className="f4-profile-id">
                    <Person name={client.name} photo={client.photo}
                            meta={[client.age != null ? `${client.age} yrs` : null, client.location, client.nhs ? `NHS ${client.nhs}` : null].filter(Boolean).join(' · ') || null}
                            size="lg" />
                    <span className="f4-mar-headactions">
                        {canExport ? (
                            <button type="button" className="f4-btn" data-variant="secondary" data-size="sm" onClick={() => window.print()}>
                                Print / PDF
                            </button>
                        ) : null}
                        <Link href="/frontend4/clients" className="f4-btn" data-variant="quiet" data-size="sm">All clients</Link>
                    </span>
                </div>
                {client.allergies && client.allergies.length ? (
                    <div className="f4-profile-safety"><SafetyStrip allergies={client.allergies} /></div>
                ) : null}
            </div>

            <div className="f4-tabpanel">
                {/* Week navigation */}
                <div className="f4-mar-weeknav">
                    <Link href={marUrl(prevWeek)} className="f4-btn" data-variant="secondary" data-size="sm" preserveScroll aria-label="Previous week">‹ Previous</Link>
                    <span className="lbl">{weekLabel}</span>
                    {isThisWeek
                        ? <span className="f4-btn" data-variant="secondary" data-size="sm" aria-disabled="true" style={{ opacity: 0.5, pointerEvents: 'none' }}>Next ›</span>
                        : <Link href={marUrl(nextWeek)} className="f4-btn" data-variant="secondary" data-size="sm" preserveScroll aria-label="Next week">Next ›</Link>}
                </div>

                {/* Summary */}
                <div className="f4-mar-summary">
                    <Stat value={summary.given ?? 0} label="Given" />
                    <Stat value={summary.notGiven ?? 0} label="Not given" status={summary.notGiven ? 'refused' : undefined} />
                    <Stat value={summary.late ?? 0} label="Late" status={summary.late ? 'late' : undefined} />
                    <Stat value={summary.outstanding ?? 0} label="Outstanding" status={summary.outstanding ? 'overdue' : undefined} />
                    <Stat value={summary.prn ?? 0} label="PRN given" />
                </div>

                {meds.length === 0 ? (
                    <Empty title="No medicines to show" body="This client has no active prescriptions, and nothing was recorded in this week." />
                ) : (
                    <div className="f4-mar-wrap" tabIndex={0} role="region" aria-label={`MAR grid, week of ${weekLabel}`}>
                        <table className="f4-mar">
                            <thead>
                                <tr>
                                    <th className="f4-mar-med">Medicine</th>
                                    <th>Time</th>
                                    {days.map((d) => (
                                        <th key={d.date} data-today={d.today ? 'true' : undefined}>{d.dow}<br />{d.day}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {meds.map((m) => {
                                    const label = (
                                        <>
                                            {m.name}
                                            <small>{[m.strength, m.form, m.dose, m.route].filter(Boolean).join(' · ')}</small>
                                        </>
                                    );

                                    if (m.asRequired) {
                                        return (
                                            <tr key={m.id}>
                                                <td className="f4-mar-med">{label}</td>
                                                <td className="f4-mar-time">PRN</td>
                                                {days.map((d) => {
                                                    const p = m.prnByDay[d.date];
                                                    let inner = <span className="f4-mar-dot" aria-hidden="true">·</span>;
                                                    if (p) {
                                                        const given = p.count > 0;
                                                        const first = p.doses[0];
                                                        // Given → a count. Recorded-but-not-given (e.g. refused) → that
                                                        // outcome's code, so a declined PRN is visible on the grid too.
                                                        const disp = given ? `${p.count}×` : (first ? first.code : '·');
                                                        const st = given ? 'given' : (first ? first.status : 'omitted');
                                                        const title = given ? `Given ${p.count}× as needed` : (first ? first.label : 'PRN');
                                                        inner = (
                                                            <button type="button" className="f4-mar-cell f4-mar-cellbtn" data-status={st} title={title}
                                                                    onClick={() => setDetail({ kind: 'prn', med: m, day: d, prn: p })}>{disp}</button>
                                                        );
                                                    }
                                                    return <td key={d.date} data-today={d.today ? 'true' : undefined}>{inner}</td>;
                                                })}
                                            </tr>
                                        );
                                    }

                                    const slots = m.slots.length ? m.slots : ['—'];
                                    return slots.map((slot, si) => (
                                        <tr key={`${m.id}-${slot}`}>
                                            {si === 0 ? <td className="f4-mar-med" rowSpan={slots.length}>{label}</td> : null}
                                            <td className="f4-mar-time">{slot}</td>
                                            {days.map((d) => {
                                                const c = m.grid[slot] ? m.grid[slot][d.date] : null;
                                                return (
                                                    <td key={d.date} data-today={d.today ? 'true' : undefined}>
                                                        <Cell cell={c} onOpen={() => setDetail({ kind: 'reg', med: m, day: d, cell: c })} />
                                                    </td>
                                                );
                                            })}
                                        </tr>
                                    ));
                                })}
                            </tbody>
                        </table>
                    </div>
                )}

                {detail ? (
                    <MarDetail detail={detail} onClose={() => setDetail(null)}
                               canCorrect={canCorrect} outcomes={outcomes} clientId={client.id} weekStart={weekStart} />
                ) : null}

                {/* Legend — every code named. */}
                <div className="f4-mar-legend">
                    {legend.map((c) => (
                        <span key={c.code}>
                            <span className="f4-mar-cell" data-status={c.status}>{c.code}</span>
                            {c.label}
                        </span>
                    ))}
                </div>

                <p className="f4-note">
                    This is the record. Doses are recorded on the medication round; a dot means nothing was
                    scheduled or recorded for that time. Opening an entry for its full detail and corrections
                    comes next.
                </p>
            </div>
        </F4Shell>
    );
}
