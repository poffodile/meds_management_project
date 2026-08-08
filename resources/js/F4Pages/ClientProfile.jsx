/**
 * frontend4 — Client profile (Page 2).
 *
 * SLICE A: a persistent identity header (photo, name, key facts, allergies) that
 * stays put on every tab, and the Overview tab on real data. The other seven
 * tabs exist in the shell and are filled in later slices — each says so honestly
 * rather than pretending to be finished.
 *
 * The tab bar is a real ARIA tablist: arrow keys move between tabs, the panel is
 * labelled by its tab. Design reasoning: FRONTEND4-DESIGN.md, and the Page 2 spec.
 */

import React, { useEffect, useRef, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { Empty, Status, Field } from '@frontend4/components/F4Atoms';
import { allows } from '@frontend4/roles';

/** One label/value pair. */
function KV({ k, v }) {
    return (
        <div className="f4-kv">
            <span className="f4-kv-k">{k}</span>
            <span className="f4-kv-v">{v}</span>
        </div>
    );
}

const TABS = [
    { key: 'overview',    label: 'Overview' },
    { key: 'medications', label: 'Medications' },
    { key: 'prn',         label: 'PRN protocols' },
    { key: 'allergies',   label: 'Allergies' },
    { key: 'mar',         label: 'MAR history' },
    { key: 'notes',       label: 'Care notes' },
    { key: 'documents',   label: 'Documents' },
    { key: 'audit',       label: 'Audit history' },
];

/** The tab named in the URL hash (…#medications), or Overview by default. */
function tabFromHash() {
    if (typeof window === 'undefined') return 'overview';
    const key = window.location.hash.replace('#', '');
    return TABS.some((t) => t.key === key) ? key : 'overview';
}

/** Initials from a name, for the header avatar. Uses the first letters of the
 *  first and last words that actually start with a letter — so markers like a
 *  leading "#" or a trailing "(demo)" don't produce a junk avatar. */
function initials(name) {
    const p = String(name || '').trim().split(/\s+/).filter((w) => /^[a-z]/i.test(w));
    return p.length ? (p[0][0] + (p.length > 1 ? p[p.length - 1][0] : '')).toUpperCase() : '?';
}

function CareNotes({ notes }) {
    if (!notes.length) {
        return <Empty title="No care notes" body="No care-log notes are recorded for this client." />;
    }
    return (
        <div className="f4-stack">
            {notes.map((n, i) => (
                <section className="f4-card" key={i}>
                    <h3>{n.title}</h3>
                    {n.body ? <p style={{ marginTop: 'var(--f4-s2)' }}>{n.body}</p> : null}
                    <p className="f4-row-sub" style={{ marginTop: 'var(--f4-s3)' }}>
                        {[n.date, n.category, n.staff ? `by ${n.staff}` : null].filter(Boolean).join(' · ')}
                    </p>
                </section>
            ))}
        </div>
    );
}

function Documents({ docs }) {
    if (!docs.length) {
        return <Empty title="No documents" body="No documents are attached to this client's record." />;
    }
    return (
        <>
            <section className="f4-card" data-pad="none">
                <div className="f4-rows">
                    {docs.map((d, i) => (
                        <div className="f4-row" key={i}>
                            <span className="f4-row-main">
                                <span className="f4-row-title">{d.name}</span>
                                <span className="f4-row-sub">
                                    {[d.type, d.added ? `added ${d.added}` : null, d.expiry ? `expires ${d.expiry}` : null].filter(Boolean).join(' · ')}
                                </span>
                            </span>
                            {d.confidential ? (
                                <span className="f4-row-end"><span className="f4-tag" data-tone="caution">Confidential</span></span>
                            ) : null}
                        </div>
                    ))}
                </div>
            </section>
            <p className="f4-note">Opening a document is a permissioned action, added in a later slice. This lists what is on file.</p>
        </>
    );
}

function AuditHistory({ rows }) {
    if (!rows.length) {
        return (
            <Empty
                title="No corrections on record"
                body="Nothing on this client's clinical record has been amended. When a record is corrected, the original and every change appear here — nothing is ever overwritten. (The wider audit log of settings and permission changes is a separate, later feature.)"
            />
        );
    }
    return (
        <section className="f4-card" data-pad="none">
            <div className="f4-rows">
                {rows.map((r, i) => (
                    <div className="f4-row" key={i}>
                        <span className="f4-row-main">
                            <span className="f4-row-title">{r.medicine} — {r.summary}</span>
                            <span className="f4-row-sub">
                                {[r.when, r.staff ? `by ${r.staff}` : null].filter(Boolean).join(' · ')}
                                {r.reason ? ` — ${r.reason}` : ''}
                            </span>
                        </span>
                    </div>
                ))}
            </div>
        </section>
    );
}

/** A "built in a later slice" panel — honest, not a blank. */
function ComingNext({ label }) {
    return (
        <Empty
            title={`${label} — coming next`}
            body="This tab is part of a later slice of Page 2. The identity header and Overview are built first; the rest follow one slice at a time."
        />
    );
}

/**
 * Manager-only controls to pause / resume / stop a prescription.
 *
 * The reason is required and captured before the change is sent; the server
 * refuses without it and writes the change to the append-only log. Hidden for
 * roles without the permission — but that is courtesy; the server is the check.
 */
function MedManage({ clientId, med }) {
    const [open, setOpen] = useState(null);
    const form = useForm({ action: '', reason: '' });

    const actions = med.statusLabel === 'Active' ? [['pause', 'Pause'], ['stop', 'Stop']]
        : med.statusLabel === 'Paused' ? [['resume', 'Resume'], ['stop', 'Stop']]
        : [];
    if (!actions.length) return null;

    const verb = { pause: 'pausing', resume: 'resuming', stop: 'stopping' };

    const submit = (e) => {
        e.preventDefault();
        form.transform((d) => ({ ...d, action: open }));
        form.post(`/frontend4/clients/${clientId}/medications/${med.id}/status`, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => { setOpen(null); form.reset(); },
        });
    };

    return (
        <div className="f4-med-manage">
            {!open ? (
                <div className="f4-actions">
                    {actions.map(([a, label]) => (
                        <button key={a} type="button" className="f4-btn" data-variant="secondary" data-size="sm"
                                onClick={() => { form.clearErrors(); form.setData('reason', ''); setOpen(a); }}>
                            {label}
                        </button>
                    ))}
                </div>
            ) : (
                <form onSubmit={submit} className="f4-manage-form">
                    <Field id={`reason-${med.id}`} label={`Reason for ${verb[open]} this medicine`} error={form.errors.reason} required>
                        {(p) => (
                            <textarea className="f4-textarea" rows={2} value={form.data.reason}
                                      onChange={(e) => form.setData('reason', e.target.value)} {...p} />
                        )}
                    </Field>
                    <div className="f4-actions">
                        <button type="submit" className="f4-btn" disabled={form.processing}>
                            {form.processing ? 'Saving…' : `Confirm ${open}`}
                        </button>
                        <button type="button" className="f4-btn" data-variant="quiet"
                                onClick={() => { setOpen(null); form.reset(); form.clearErrors(); }}>
                            Cancel
                        </button>
                    </div>
                </form>
            )}
        </div>
    );
}

function Medications({ meds, clientId, canManage }) {
    if (!meds.length) {
        return <Empty title="No medicines recorded" body="This client has no prescriptions on record." />;
    }

    return (
        <div className="f4-stack">
            {meds.map((m) => {
                const detail = [
                    m.dose ? ['Dose', m.dose] : null,
                    m.route ? ['Route', m.route] : null,
                    m.frequency ? ['Frequency', m.frequency] : null,
                ].filter(Boolean);
                const line = [
                    m.prescriber ? `Prescriber: ${m.prescriber}` : null,
                    m.started ? `Started ${m.started}` : null,
                    m.ended ? `Ends ${m.ended}` : null,
                ].filter(Boolean).join(' · ');

                return (
                    <section className="f4-card" key={m.id}>
                        <div className="f4-med-top">
                            <span className="f4-med-name">{m.name}</span>
                            <span className="f4-med-tags">
                                <span className="f4-tag" data-tone={m.statusTone}>{m.statusLabel}</span>
                                {m.isControlled ? <span className="f4-tag" data-tone="info">Controlled drug</span> : null}
                            </span>
                        </div>
                        {m.strength || m.form ? (
                            <span className="f4-med-strength">{[m.strength, m.form].filter(Boolean).join(' · ')}</span>
                        ) : null}
                        {detail.length ? (
                            <p className="f4-med-detail">
                                {detail.map(([k, v], i) => (
                                    <React.Fragment key={k}>{i > 0 ? ' · ' : ''}<b>{k}:</b> {v}</React.Fragment>
                                ))}
                            </p>
                        ) : null}
                        {line ? <p className="f4-row-sub" style={{ marginTop: 4 }}>{line}</p> : null}
                        {m.instruction ? <p className="f4-med-instruction">{m.instruction}</p> : null}
                        {m.indication ? <p className="f4-row-sub" style={{ marginTop: 4 }}>Prescribed for {m.indication}</p> : null}
                        <p className="f4-med-stock" data-low={m.lowStock ? 'true' : undefined}>
                            {m.stock != null
                                ? <>Stock remaining: <b>{m.stock}{m.unit ? ` ${m.unit}` : ''}</b>{m.lowStock ? ' · low' : ''}</>
                                : 'Stock not tracked'}
                        </p>
                        {canManage ? <MedManage clientId={clientId} med={m} /> : null}
                    </section>
                );
            })}
        </div>
    );
}

function Prn({ items }) {
    if (!items.length) {
        return <Empty title="No PRN medicines" body="This client has no when-required (PRN) medicines." />;
    }

    return (
        <div className="f4-stack">
            {items.map((p) => (
                <section className="f4-card" key={p.id}>
                    <div className="f4-med-top">
                        <span className="f4-med-name">{p.name}</span>
                        <span className="f4-med-tags">
                            <span className="f4-tag" data-tone="info">PRN</span>
                            {p.isControlled ? <span className="f4-tag" data-tone="info">Controlled drug</span> : null}
                        </span>
                    </div>
                    {p.strength || p.form ? (
                        <span className="f4-med-strength">{[p.strength, p.form].filter(Boolean).join(' · ')}</span>
                    ) : null}
                    {p.indication ? <p className="f4-row-sub" style={{ marginTop: 4 }}>For {p.indication}</p> : null}

                    <div className="f4-kv-grid" style={{ marginTop: 'var(--f4-s3)' }}>
                        {p.dose ? <KV k="Dose" v={p.dose} /> : null}
                        {p.route ? <KV k="Route" v={p.route} /> : null}
                        {p.minIntervalHours != null ? <KV k="Minimum interval" v={`${p.minIntervalHours} h`} /> : null}
                        {p.maxDaily != null ? <KV k="Maximum in 24h" v={`${p.maxDaily}`} /> : null}
                    </div>

                    {p.protocol ? <p className="f4-med-instruction">{p.protocol}</p> : null}
                    {p.instruction ? <p className="f4-med-instruction">{p.instruction}</p> : null}
                    <p className="f4-note">
                        Symptoms to check, non-medication steps to try first, escalation and the
                        effectiveness-review requirement are part of the fuller PRN protocol (a planned
                        data upgrade). Shown here as recorded.
                    </p>
                </section>
            ))}
        </div>
    );
}

function MarHistory({ rows, capped, clientId }) {
    const fullMar = (
        <div className="f4-actions" style={{ justifyContent: 'flex-end', marginBottom: 'var(--f4-s4)' }}>
            <Link href={`/frontend4/clients/${clientId}/mar`} className="f4-btn" data-variant="secondary" data-size="sm">
                View full MAR
            </Link>
        </div>
    );

    if (!rows.length) {
        return (
            <>
                {fullMar}
                <Empty title="No administrations recorded" body="Nothing has been recorded against this client's medicines yet. The full MAR still shows the schedule." />
            </>
        );
    }

    return (
        <>
            {fullMar}
            <section className="f4-card" data-pad="none">
                <div className="f4-rows">
                    {rows.map((r, i) => {
                        const sub = [
                            r.date, r.slot,
                            r.staff ? `by ${r.staff}` : null,
                            r.witness ? `witness ${r.witness}` : null,
                        ].filter(Boolean).join(' · ');
                        return (
                            <div className="f4-row" key={i} data-status={r.outcomeStatus}>
                                <span className="f4-row-main">
                                    <span className="f4-row-title">{r.medicine}</span>
                                    <span className="f4-row-sub">{sub}{r.reason ? ` — ${r.reason}` : ''}</span>
                                </span>
                                <span className="f4-row-end">
                                    <Status status={r.outcomeStatus} label={r.outcomeLabel} note={r.isLate ? 'late' : undefined} />
                                </span>
                            </div>
                        );
                    })}
                </div>
            </section>
            {capped ? <p className="f4-note">Showing the 60 most recent administrations.</p> : null}
        </>
    );
}

function Allergies({ allergies }) {
    if (!allergies.length) {
        return <Empty title="No allergies recorded" body="No allergies are recorded for this client. If that is wrong, add them on the client's record." />;
    }

    return (
        <section className="f4-card">
            <h3>Recorded allergies</h3>
            <div className="f4-tag-list">
                {allergies.map((a) => <span className="f4-tag" data-tone="risk" key={a}>{a}</span>)}
            </div>
            <p className="f4-note">
                Reaction, severity, source and who recorded each allergy are not yet held as structured
                data — that is a planned upgrade (D1). Shown here exactly as recorded.
            </p>
        </section>
    );
}

function Contact({ role, data }) {
    return (
        <div className="f4-contact">
            <dt>{role}</dt>
            <dd>{data.name}{data.sub ? <small>{data.sub}</small> : null}</dd>
        </div>
    );
}

/** A collapsible dashboard card. The header is an accordion button; the body
 *  hides when collapsed. Open by default. */
function Panel({ eyebrow, title, className = '', children }) {
    const [open, setOpen] = useState(true);
    return (
        <section className={`f4-panel ${className}`.trim()} data-open={open ? 'true' : 'false'}>
            <h2 className="f4-panel-title">
                <button type="button" className="f4-panel-toggle" aria-expanded={open} onClick={() => setOpen((o) => !o)}>
                    <span className="f4-panel-heads">
                        {eyebrow ? <span className="f4-eyebrow">{eyebrow}</span> : null}
                        {title ? <span className="f4-panel-name">{title}</span> : null}
                    </span>
                    <svg className="f4-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>
            </h2>
            <div className="f4-panel-body" hidden={!open}>{children}</div>
        </section>
    );
}

/** The Overview: a two-column dashboard of collapsible cards. */
function Overview({ keyDetails, activeMeds, careInstructions, nextMed, contacts, recent, roundUrl, onViewMeds, onViewMar }) {
    const hasContacts = contacts.gp || contacts.pharmacy || contacts.nextOfKin;
    return (
        <div className="f4-dash">
            <div className="f4-dash-main">
                {keyDetails.length ? (
                    <Panel eyebrow="Client record" title="Key details" className="f4-keydetails">
                        <dl className="f4-kd">
                            {keyDetails.map((f) => (
                                <div key={f.label}><dt>{f.label}</dt><dd>{f.value}</dd></div>
                            ))}
                        </dl>
                    </Panel>
                ) : null}

                <Panel eyebrow="Current prescriptions" title="Active medications" className="f4-medpanel">
                    {activeMeds.length ? activeMeds.map((m) => (
                        <article className="f4-medrow" key={m.id}>
                            <span className="f4-rx">Rx</span>
                            <div className="f4-medname"><strong>{m.name}</strong>{m.strength || m.form ? <small>{[m.strength, m.form].filter(Boolean).join(' · ')}</small> : null}</div>
                            <div className="f4-medcol"><small>Dose &amp; route</small><strong>{m.doseRoute || '—'}</strong></div>
                            <div className="f4-medcol"><small>Schedule</small><strong>{m.schedule || '—'}</strong></div>
                            <span className="f4-stock" data-low={m.lowStock ? 'true' : undefined}>{m.stock != null ? `${m.stock}${m.unit ? ` ${m.unit}` : ''}` : '—'}</span>
                        </article>
                    )) : <p className="f4-panel-note">No active prescriptions on record.</p>}
                    <button type="button" className="f4-textbtn f4-viewmar" onClick={onViewMeds}>View all medications →</button>
                </Panel>

                <Panel eyebrow="Support information" title="Important care instructions" className="f4-instructions">
                    {careInstructions.length ? careInstructions.map((c, i) => (
                        <article key={i}><span>{String(i + 1).padStart(2, '0')}</span><p><strong>{c.title}</strong>{c.body}</p></article>
                    )) : <p className="f4-panel-note">No care instructions recorded yet — these are provided at onboarding.</p>}
                </Panel>
            </div>

            <aside className="f4-dash-side">
                <Panel eyebrow="Next medication" className="f4-nextmed">
                    {nextMed ? (
                        <>
                            <div className="f4-nextrow"><strong>{nextMed.time}</strong><span>{nextMed.count} due{nextMed.nextDay ? ' · next day' : ''}</span></div>
                            <p className="f4-nextmeds">{nextMed.meds.join(', ')}</p>
                        </>
                    ) : <p className="f4-nextmeds">No scheduled medicine — when-required only.</p>}
                    <a href={roundUrl} className="f4-btn f4-block-btn">Open medication round →</a>
                </Panel>

                <Panel eyebrow="Care network" title="Contacts" className="f4-contacts">
                    {hasContacts ? (
                        <dl>
                            {contacts.gp ? <Contact role="GP" data={contacts.gp} /> : null}
                            {contacts.pharmacy ? <Contact role="Pharmacy" data={contacts.pharmacy} /> : null}
                            {contacts.nextOfKin ? <Contact role="Next of kin" data={contacts.nextOfKin} /> : null}
                        </dl>
                    ) : <p className="f4-panel-note">No contacts recorded yet — added at onboarding.</p>}
                </Panel>

                <Panel eyebrow="Recent activity" title="MAR and notes" className="f4-recent">
                    {recent.length ? recent.map((r, i) => (
                        <article key={i}>
                            <span className={r.outcomeStatus === 'given' ? undefined : 'f4-warning'}>{r.outcomeStatus === 'given' ? '✓' : '!'}</span>
                            <p><strong>{r.medicine} — {r.outcomeLabel}</strong><small>{[r.date, r.slot, r.staff ? `by ${r.staff}` : null].filter(Boolean).join(' · ')}</small></p>
                        </article>
                    )) : <p className="f4-panel-note">Nothing recorded yet.</p>}
                    <button type="button" className="f4-textbtn f4-viewmar" onClick={onViewMar}>View MAR history →</button>
                </Panel>
            </aside>
        </div>
    );
}

export default function ClientProfile({
    client, medications = [], prn = [], marHistory = [], marCapped = false,
    careNotes = [], documents = [], audit = [],
    nextMed = null, keyDetails = [], activeMeds = [], contacts = {}, careInstructions = [], recent = [],
    infoStrip = {}, headerMeta = [], headerStats = [], roundUrl = '/frontend4/round',
    place = null, user = null, roleLabel = null, can = [],
}) {
    const [active, setActive] = useState(tabFromHash);
    const tabRefs = useRef({});
    const canManage = allows(can, 'manage_prescription');

    // Keep the open tab in the URL hash so Back and refresh return to it. Uses
    // replaceState (no new history entry, no scroll, no server round-trip).
    useEffect(() => {
        if (typeof window === 'undefined') return;
        const url = window.location.pathname + window.location.search + '#' + active;
        window.history.replaceState(window.history.state, '', url);
    }, [active]);

    // Arrow-key navigation across the tablist, as an ARIA tablist requires.
    const onKeyDown = (e) => {
        const i = TABS.findIndex((t) => t.key === active);
        let next = null;
        if (e.key === 'ArrowRight') next = TABS[(i + 1) % TABS.length];
        else if (e.key === 'ArrowLeft') next = TABS[(i - 1 + TABS.length) % TABS.length];
        else if (e.key === 'Home') next = TABS[0];
        else if (e.key === 'End') next = TABS[TABS.length - 1];
        if (next) {
            e.preventDefault();
            setActive(next.key);
            tabRefs.current[next.key]?.focus();
        }
    };

    return (
        <F4Shell area="clients" title={client.name} summary="Client profile" bare
                 place={place} user={user} roleLabel={roleLabel} can={can}>
            <Head title={`${client.name} — Care One OS`} />

            <div className="f4-page-enter">
            <div className="f4-profile-back">
                <Link href="/frontend4/clients" className="f4-backlink">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Back to clients
                </Link>
            </div>
            {/* Identity, the info strip, and the tabs. */}
            <div className="f4-profile-head">
                <div className="f4-profile-id">
                    <span className="f4-profile-av" aria-hidden="true">{initials(client.name)}</span>
                    <div className="f4-profile-idmain">
                        <div className="f4-profile-nmrow">
                            <span className="f4-profile-nm">{client.name}</span>
                            {client.status ? <span className="f4-tag" data-tone="good">{client.status}</span> : null}
                        </div>
                        {headerMeta.length ? (
                            <div className="f4-idmeta">{headerMeta.map((m, i) => <span key={i}>{m}</span>)}</div>
                        ) : null}
                        {headerStats.length ? (
                            <div className="f4-idstats">
                                {headerStats.map((s, i) => (
                                    <span key={i}><i>{s.label}</i><b>{s.value}</b></span>
                                ))}
                            </div>
                        ) : null}
                    </div>
                </div>

                <div className="f4-infostrip">
                    <div className="f4-infocell" data-allergy={infoStrip.allergy ? 'true' : undefined}>
                        {infoStrip.allergy ? <span className="f4-alerticon" aria-hidden="true">!</span> : null}
                        <div className="f4-infobody">
                            <span className="f4-infolab">Allergy</span>
                            <span className="f4-infobig">{infoStrip.allergy || 'None recorded'}</span>
                            {infoStrip.allergy ? <span className="f4-infosm">Reaction: {infoStrip.allergyReaction || 'not recorded'}</span> : null}
                        </div>
                    </div>
                    <div className="f4-infocell">
                        <span className="f4-infolab">Medication support</span>
                        <span className={`f4-infobig${infoStrip.medSupport ? '' : ' f4-muted-v'}`}>{infoStrip.medSupport || 'Not recorded'}</span>
                    </div>
                    <div className="f4-infocell">
                        <span className="f4-infolab">Capacity &amp; consent</span>
                        <span className={`f4-infobig${infoStrip.capacity ? '' : ' f4-muted-v'}`}>{infoStrip.capacity || 'Not recorded'}</span>
                    </div>
                    <div className="f4-infocell">
                        <span className="f4-infolab">Key worker</span>
                        <span className={`f4-infobig${infoStrip.keyWorker ? '' : ' f4-muted-v'}`}>{infoStrip.keyWorker || 'Not recorded'}</span>
                    </div>
                </div>

                <div className="f4-tabs" role="tablist" aria-label="Client record" onKeyDown={onKeyDown}>
                    {TABS.map((t) => {
                        const selected = t.key === active;
                        return (
                            <button
                                key={t.key}
                                ref={(el) => { tabRefs.current[t.key] = el; }}
                                type="button"
                                role="tab"
                                id={`tab-${t.key}`}
                                aria-selected={selected}
                                aria-controls={`panel-${t.key}`}
                                tabIndex={0}
                                className="f4-tab"
                                onClick={() => setActive(t.key)}
                            >
                                {t.label}
                            </button>
                        );
                    })}
                </div>
            </div>

            <div
                key={active}
                className="f4-tabpanel f4-tab-anim"
                role="tabpanel"
                id={`panel-${active}`}
                aria-labelledby={`tab-${active}`}
                tabIndex={0}
            >
                {active === 'overview' ? (
                    <Overview
                        keyDetails={keyDetails} activeMeds={activeMeds} careInstructions={careInstructions} nextMed={nextMed}
                        contacts={contacts} recent={recent} roundUrl={roundUrl}
                        onViewMeds={() => setActive('medications')} onViewMar={() => setActive('mar')}
                    />
                )
                    : active === 'medications' ? <Medications meds={medications} clientId={client.id} canManage={canManage} />
                    : active === 'prn' ? <Prn items={prn} />
                    : active === 'allergies' ? <Allergies allergies={client.allergies || []} />
                    : active === 'mar' ? <MarHistory rows={marHistory} capped={marCapped} clientId={client.id} />
                    : active === 'notes' ? <CareNotes notes={careNotes} />
                    : active === 'documents' ? <Documents docs={documents} />
                    : active === 'audit' ? <AuditHistory rows={audit} />
                    : <ComingNext label={TABS.find((t) => t.key === active).label} />}
            </div>

            </div>
        </F4Shell>
    );
}
