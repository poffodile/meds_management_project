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

/** A static section card for the profile tabs — same look as the Overview
 *  panels (soft-lifted surface, eyebrow + title header), then edge-to-edge rows
 *  or, with `pad`, a padded body. Keeps every tab visually consistent. */
function TabPanel({ eyebrow, title, action, note, pad = false, children }) {
    return (
        <section className="f4-panel f4-tabsec">
            {(eyebrow || title || action) ? (
                <div className="f4-tabsec-head">
                    <div>
                        {eyebrow ? <p className="f4-eyebrow">{eyebrow}</p> : null}
                        {title ? <h2 className="f4-panel-name">{title}</h2> : null}
                    </div>
                    {action || null}
                </div>
            ) : null}
            {pad ? <div className="f4-tabsec-body">{children}</div> : children}
            {note ? <p className="f4-panel-note">{note}</p> : null}
        </section>
    );
}

function CareNotes({ notes }) {
    if (!notes.length) {
        return <Empty title="No care notes" body="No care-log notes are recorded for this client." />;
    }
    return (
        <TabPanel eyebrow="Care log" title="Care notes">
            {notes.map((n, i) => (
                <div className="f4-noterow" key={i}>
                    <div className="f4-noterow-title">{n.title}</div>
                    {n.body ? <p className="f4-noterow-body">{n.body}</p> : null}
                    <div className="f4-noterow-meta">{[n.date, n.category, n.staff ? `by ${n.staff}` : null].filter(Boolean).join(' · ')}</div>
                </div>
            ))}
        </TabPanel>
    );
}

function Documents({ docs }) {
    if (!docs.length) {
        return <Empty title="No documents" body="No documents are attached to this client's record." />;
    }
    return (
        <TabPanel eyebrow="On file" title="Documents"
                  note="Opening a document is a permissioned action, added in a later slice. This lists what is on file.">
            {docs.map((d, i) => (
                <div className="f4-listrow" key={i}>
                    <div className="f4-listrow-main">
                        <span className="f4-listrow-title">{d.name}</span>
                        <span className="f4-listrow-sub">{[d.type, d.added ? `added ${d.added}` : null, d.expiry ? `expires ${d.expiry}` : null].filter(Boolean).join(' · ')}</span>
                    </div>
                    {d.confidential ? <span className="f4-listrow-end"><span className="f4-tag" data-tone="caution">Confidential</span></span> : null}
                </div>
            ))}
        </TabPanel>
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
        <TabPanel eyebrow="Record history" title="Corrections">
            {rows.map((r, i) => (
                <div className="f4-listrow" key={i}>
                    <div className="f4-listrow-main">
                        <span className="f4-listrow-title">{r.medicine} — {r.summary}</span>
                        <span className="f4-listrow-sub">{[r.when, r.staff ? `by ${r.staff}` : null].filter(Boolean).join(' · ')}{r.reason ? ` — ${r.reason}` : ''}</span>
                    </div>
                </div>
            ))}
        </TabPanel>
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

/** One detailed medicine block inside a tab panel — Rx tile, name, status,
 *  a small meta grid, then the fuller detail. Shared by Medications and PRN. */
function MedFull({ m, tone, tagLabel, extraTag, grid, sub, lines, note, children }) {
    return (
        <div className="f4-medfull">
            <div className="f4-medfull-top">
                <span className="f4-rx">Rx</span>
                <div className="f4-medfull-nm">
                    <strong>{m.name}</strong>
                    {m.strength || m.form ? <small>{[m.strength, m.form].filter(Boolean).join(' · ')}</small> : null}
                </div>
                <span className="f4-medfull-tags">
                    <span className="f4-tag" data-tone={tone}>{tagLabel}</span>
                    {m.isControlled ? <span className="f4-tag" data-tone="info">Controlled drug</span> : null}
                    {extraTag || null}
                </span>
            </div>
            {sub ? <p className="f4-medfull-sub">{sub}</p> : null}
            {grid.length ? (
                <div className="f4-medfull-grid">
                    {grid.map(([k, v]) => <div key={k}><span className="k">{k}</span><span className="v">{v}</span></div>)}
                </div>
            ) : null}
            {lines.map((l, i) => <p className="f4-medfull-sub" key={i}>{l}</p>)}
            {children}
            {note ? <p className="f4-medfull-note">{note}</p> : null}
        </div>
    );
}

/** Small line icons for the PRN guidance cells. */
function prnIcon(kind) {
    const p = { width: 15, height: 15, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 1.8, strokeLinecap: 'round', strokeLinejoin: 'round', 'aria-hidden': true };
    if (kind === 'age') return <svg {...p}><circle cx="12" cy="8" r="4" /><path d="M4 20.5c0-4 4-6 8-6s8 2 8 6" /></svg>;
    if (kind === 'weight') return <svg {...p}><path d="M12 3v3M6 6h12l-2.2 8H8.2L6 6zM7.5 20.5h9" /></svg>;
    if (kind === 'max') return <svg {...p}><circle cx="12" cy="12" r="9" /><path d="M12 7.5V12l3 2" /></svg>;
    return <svg {...p}><path d="M7 8l-3.5 4L7 16M17 8l3.5 4L17 16M4.5 12h15" /></svg>; // interval
}

/** A detailed medicine card — label→value rows, a stock bar, and (for PRN
 *  medicines) a guidance box. Matches the mobile design; works at any width. */
function MedCard({ m, clientId, canManage, age, weight }) {
    const rows = [
        m.dose ? ['Dose', m.dose] : null,
        m.route ? ['Route', m.route] : null,
        m.frequency ? ['Frequency', m.frequency] : null,
        m.indication ? ['Prescribed for', m.indication] : null,
    ].filter(Boolean);

    // Stock bar: how much headroom above the reorder level. Rough, but honest —
    // full when comfortably stocked, short as it nears reorder.
    const stockN = m.stock != null ? Number(m.stock) : null;
    const reorderN = m.reorder != null ? Number(m.reorder) : null;
    const pct = stockN != null
        ? (reorderN && reorderN > 0 ? Math.max(6, Math.min(100, Math.round((stockN / (reorderN * 4)) * 100))) : 100)
        : null;

    const prnCells = m.asRequired ? [
        age != null ? ['age', `${age} yrs`, 'Age band'] : null,
        weight ? ['weight', weight, 'Weight band'] : null,
        m.maxDaily != null ? ['max', `${m.maxDaily} dose${m.maxDaily === 1 ? '' : 's'}`, 'Max in 24h'] : null,
        m.minIntervalHours != null ? ['interval', `${Number(m.minIntervalHours)} h`, 'Between doses'] : null,
    ].filter(Boolean) : [];

    return (
        <section className="f4-medcard">
            <div className="f4-medcard-top">
                <span className="f4-rx">Rx</span>
                <span className="f4-medcard-tags">
                    <span className="f4-tag" data-tone={m.statusTone}>{m.statusLabel}</span>
                    {m.isControlled ? <span className="f4-tag" data-tone="info">Controlled drug</span> : null}
                </span>
            </div>
            <div className="f4-medcard-nm">
                <strong>{m.name}</strong>
                {m.strength || m.form ? <small>{[m.strength, m.form].filter(Boolean).join(' · ')}</small> : null}
            </div>

            <dl className="f4-medcard-rows">
                {rows.map(([k, v]) => <div key={k}><dt>{k}</dt><dd>{v}</dd></div>)}
            </dl>

            <div className="f4-medcard-stock">
                <div className="f4-medcard-stockrow">
                    <span>Stock remaining</span>
                    <b data-low={m.lowStock ? 'true' : undefined}>{stockN != null ? `${m.stock}${m.unit ? ` ${m.unit}` : ''}` : 'Not tracked'}</b>
                </div>
                {pct != null ? (
                    <div className="f4-bar"><span style={{ width: `${pct}%` }} data-low={m.lowStock ? 'true' : undefined} /></div>
                ) : null}
            </div>

            {prnCells.length ? (
                <div className="f4-prnbox">
                    <p className="f4-eyebrow">PRN guidance</p>
                    <div className="f4-prngrid">
                        {prnCells.map(([kind, val, lab]) => (
                            <div className="f4-prncell" key={lab}>
                                <span className="f4-prnic">{prnIcon(kind)}</span>
                                <div><b>{val}</b><small>{lab}</small></div>
                            </div>
                        ))}
                    </div>
                </div>
            ) : null}

            {m.instruction ? <p className="f4-medcard-instr">{m.instruction}</p> : null}
            {canManage ? <MedManage clientId={clientId} med={m} /> : null}
        </section>
    );
}

function Medications({ meds, clientId, canManage, age, weight }) {
    if (!meds.length) {
        return <Empty title="No medicines recorded" body="This client has no prescriptions on record." />;
    }
    return (
        <div className="f4-medcards">
            {meds.map((m) => (
                <MedCard key={m.id} m={m} clientId={clientId} canManage={canManage} age={age} weight={weight} />
            ))}
        </div>
    );
}

function Prn({ items }) {
    if (!items.length) {
        return <Empty title="No PRN medicines" body="This client has no when-required (PRN) medicines." />;
    }
    return (
        <TabPanel eyebrow="When required" title="PRN protocols"
                  note="Symptoms to check, non-medication steps to try first, escalation and the effectiveness-review requirement are part of the fuller PRN protocol (a planned data upgrade). Shown here as recorded.">
            {items.map((p) => {
                const grid = [
                    p.dose ? ['Dose', p.dose] : null,
                    p.route ? ['Route', p.route] : null,
                    p.minIntervalHours != null ? ['Minimum interval', `${p.minIntervalHours} h`] : null,
                    p.maxDaily != null ? ['Maximum in 24h', `${p.maxDaily}`] : null,
                ].filter(Boolean);
                return (
                    <MedFull
                        key={p.id} m={p} tone="info" tagLabel="PRN" grid={grid}
                        sub={p.indication ? `For ${p.indication}` : null}
                        lines={[p.protocol || null, p.instruction || null].filter(Boolean)}
                    />
                );
            })}
        </TabPanel>
    );
}

function MarHistory({ rows, capped, clientId }) {
    const action = (
        <Link href={`/frontend4/clients/${clientId}/mar`} className="f4-textbtn">View full MAR →</Link>
    );
    if (!rows.length) {
        return (
            <TabPanel eyebrow="Administration record" title="MAR history" action={action}>
                <p className="f4-panel-note">No administrations recorded yet — the full MAR still shows the schedule.</p>
            </TabPanel>
        );
    }
    return (
        <TabPanel eyebrow="Administration record" title="MAR history" action={action}
                  note={capped ? 'Showing the 60 most recent administrations.' : null}>
            {rows.map((r, i) => {
                const sub = [
                    r.date, r.slot,
                    r.staff ? `by ${r.staff}` : null,
                    r.witness ? `witness ${r.witness}` : null,
                ].filter(Boolean).join(' · ');
                return (
                    <div className="f4-listrow" key={i} data-status={r.outcomeStatus}>
                        <div className="f4-listrow-main">
                            <span className="f4-listrow-title">{r.medicine}</span>
                            <span className="f4-listrow-sub">{sub}{r.reason ? ` — ${r.reason}` : ''}</span>
                        </div>
                        <span className="f4-listrow-end">
                            <Status status={r.outcomeStatus} label={r.outcomeLabel} note={r.isLate ? 'late' : undefined} />
                        </span>
                    </div>
                );
            })}
        </TabPanel>
    );
}

function Allergies({ allergies }) {
    if (!allergies.length) {
        return <Empty title="No allergies recorded" body="No allergies are recorded for this client. If that is wrong, add them on the client's record." />;
    }
    return (
        <TabPanel eyebrow="Safety information" title="Recorded allergies" pad
                  note="Reaction, severity, source and who recorded each allergy are not yet held as structured data — that is a planned upgrade (D1). Shown here exactly as recorded.">
            <div className="f4-alglist">
                {allergies.map((a) => <span className="f4-tag" data-tone="risk" key={a}>{a}</span>)}
            </div>
        </TabPanel>
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
                    : active === 'medications' ? <Medications meds={medications} clientId={client.id} canManage={canManage} age={client.age} weight={client.weight} />
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
