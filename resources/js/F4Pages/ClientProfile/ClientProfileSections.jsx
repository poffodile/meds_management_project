import React, { useEffect, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { Empty, Status, Field } from '@frontend4/components/F4Atoms';

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
function TabPanel({ eyebrow, title, action, note, pad = false, className = '', children }) {
    return (
        <section className={`f4-panel f4-tabsec ${className}`.trim()}>
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

function PlaceholderRecord({ eyebrow, title, body, cells = [], actions = null }) {
    const fullCells = cells.length >= 6
        ? cells
        : [
            ...cells,
            ...Array.from({ length: 6 - cells.length }, (_, i) => [`Record section ${cells.length + i + 1}`, 'No information recorded', 'This field is not yet held as structured data']),
        ];

    return (
        <TabPanel eyebrow={eyebrow} title={title} className="f4-ref-placeholder">
            <div className="f4-ref-placeholder-head">
                <div>
                    <h3>{title}</h3>
                    <p>{body}</p>
                </div>
                {actions}
            </div>
            <div className="f4-ref-placeholder-grid">
                {fullCells.map(([k, v, sub]) => (
                    <div key={k}>
                        <span>{k}</span>
                        <b>{v}</b>
                        {sub ? <small>{sub}</small> : null}
                    </div>
                ))}
            </div>
            <div className="f4-ref-placeholder-body">
                <section>
                    <p className="f4-eyebrow">Record detail</p>
                    <h4>No information recorded here yet</h4>
                    <p>When the existing backend has records for this tab, they will appear in this area using the full tab layout. Until then, the page keeps the same visual weight and shows placeholders instead of manufactured data.</p>
                </section>
                <aside>
                    <p className="f4-eyebrow">Related context</p>
                    <h4>Not structured</h4>
                    <p>Linked record status, review history and actions will show here once those records exist.</p>
                </aside>
            </div>
        </TabPanel>
    );
}

function CareNotes({ notes }) {
    if (!notes.length) {
        return (
            <PlaceholderRecord
                eyebrow="Care log"
                title="Care notes"
                body="No care-log notes are recorded for this client yet."
                cells={[
                    ['Latest note', 'No records yet', 'Care notes will appear here when recorded'],
                    ['Follow-up', 'Not recorded', 'No open care-note follow-up is held'],
                    ['Recorded by', 'Not recorded', 'No author metadata exists until a note is saved'],
                ]}
            />
        );
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
        return (
            <PlaceholderRecord
                eyebrow="Secure care record"
                title="Documents"
                body="No documents are attached to this client's record."
                cells={[
                    ['Current documents', '0', 'No attached files found'],
                    ['Due for review', 'Not recorded', 'Review dates require document records'],
                    ['Storage status', 'No records yet', 'Document controls apply once files exist'],
                ]}
            />
        );
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
            <PlaceholderRecord
                eyebrow="Record history"
                title="Audit history"
                body="No corrections or prescription change events are recorded for this client yet."
                cells={[
                    ['Correction events', '0', 'Original records will be retained when corrected'],
                    ['Prescription changes', '0', 'Pause, resume and stop events will appear here'],
                    ['Wider audit log', 'Not structured', 'Settings and permission audit is a separate future record'],
                ]}
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
                    {med.coded ? <Link href={`/frontend4/clients/${clientId}/medications/${med.id}/edit`} className="f4-btn" data-variant="secondary" data-size="sm">Amend</Link> : null}
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
function stockPercent(m) {
    const stockN = m.stock != null ? Number(m.stock) : null;
    const reorderN = m.reorder != null ? Number(m.reorder) : null;
    return stockN != null
        ? (reorderN && reorderN > 0 ? Math.max(6, Math.min(100, Math.round((stockN / (reorderN * 4)) * 100))) : 100)
        : null;
}

function medSubline(m) {
    return [m.strength, m.form].filter(Boolean).join(' - ');
}

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
    const pct = stockPercent(m);

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
                <div className="f4-medcard-nm">
                    <strong>{m.name}</strong>
                    {m.strength || m.form ? <small>{[m.strength, m.form].filter(Boolean).join(' · ')}</small> : null}
                </div>
                <span className="f4-medcard-tags">
                    <span className="f4-tag" data-tone={m.statusTone}>{m.statusLabel}</span>
                    {m.isControlled ? <span className="f4-tag" data-tone="info">Controlled drug</span> : null}
                </span>
            </div>
            <dl className="f4-medcard-rows">
                {rows.map(([k, v]) => <div key={k}><dt>{k}</dt><dd>{v}</dd></div>)}
            </dl>

            <div className="f4-medcard-stock">
                <div className="f4-medcard-stockrow">
                    <div>
                        <span>Stock remaining</span>
                        {reorderN != null ? <small>Reorder at {m.reorder}{m.unit ? ` ${m.unit}` : ''}</small> : null}
                    </div>
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

function MedDesktopRow({ m, index, open, onOpen, clientId, canManage, age, weight }) {
    const pct = stockPercent(m);
    const prnCells = m.asRequired ? [
        age != null ? ['age', `${age} yrs`, 'Age band'] : null,
        weight ? ['weight', weight, 'Weight band'] : null,
        m.maxDaily != null ? ['max', `${m.maxDaily} dose${m.maxDaily === 1 ? '' : 's'}`, 'Max in 24h'] : null,
        m.minIntervalHours != null ? ['interval', `${Number(m.minIntervalHours)} h`, 'Between doses'] : null,
    ].filter(Boolean) : [];
    const detailCells = [
        ['Dose', m.dose || 'Not recorded'],
        ['Route', m.route || 'Not recorded'],
        ['Schedule', m.schedule || m.frequency || 'Not recorded'],
        ['Stock', m.stock != null ? `${m.stock}${m.unit ? ` ${m.unit}` : ''}${m.reorder != null ? `, reorder at ${m.reorder}` : ''}` : 'Not tracked'],
    ].filter(Boolean);
    const directions = m.instruction || 'Not recorded';
    const prescriptionDates = [m.started ? m.started : null, m.ended ? m.ended : 'Ongoing'].filter(Boolean).join(' - ') || 'Not recorded';
    const stockValue = m.stock != null ? `${m.stock}${m.unit ? ` ${m.unit}` : ''}` : 'Not tracked';
    const titleMeta = [
        m.asRequired ? 'PRN' : 'Regular',
        m.isControlled ? 'Controlled drug' : null,
        m.coded ? (m.dmdCode ? 'dm+d coded' : 'Catalogue item') : 'Legacy unmapped',
        m.statusLabel || null,
    ].filter(Boolean);

    return (
        <article className={`f4-medh-row${open ? ' is-open' : ''}`} data-low={m.lowStock ? 'true' : undefined} onClick={onOpen}>
            <span className="f4-medh-num">{index + 1}</span>
            <span className="f4-rx">Rx</span>
            <button type="button" className="f4-medh-name" aria-expanded={open} onClick={(e) => { e.stopPropagation(); onOpen(); }}>
                <span>
                    {titleMeta.length ? (
                        <span className="f4-medh-pills">
                            {titleMeta.map((t) => <i key={t}>{t}</i>)}
                        </span>
                    ) : null}
                    <strong>{m.name}</strong>
                    {medSubline(m) ? <small>{medSubline(m)}</small> : null}
                </span>
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </button>

            <div className="f4-medh-cell"><span>Dose</span><b>{m.doseRoute || m.dose || 'Not recorded'}</b></div>
            <div className="f4-medh-cell"><span>Schedule</span><b>{m.schedule || m.frequency || 'Not recorded'}</b></div>
            <div className="f4-medh-stock">
                {pct != null ? <span className="f4-bar"><span style={{ width: `${pct}%` }} data-low={m.lowStock ? 'true' : undefined} /></span> : null}
                <b>{m.stock != null ? `${m.stock}${m.unit ? ` ${m.unit}` : ''}` : 'Not tracked'}</b>
            </div>
            <span className="f4-tag f4-medh-status" data-tone={m.lowStock ? 'caution' : m.statusTone}>{m.lowStock ? 'Low stock' : m.statusLabel}</span>

            {open ? (
                <div className="f4-medh-detail" onClick={(e) => e.stopPropagation()}>
                    <div className={`f4-medh-warning${m.lowStock ? ' is-alert' : ''}`}>
                        <span>{m.lowStock ? '!' : 'i'}</span>
                        <strong>{m.lowStock ? 'Low stock - review stock level' : (m.asRequired ? 'PRN protocol applies - record reason and outcome' : 'Administration instructions and prescription record')}</strong>
                    </div>
                    <div className="f4-medh-record f4-medh-record--sections">
                        <section className="f4-medh-band">
                            <div className="f4-medh-bandhead">Clinical use</div>
                            <div className="f4-medh-bandgrid">
                                <div className="f4-medh-recordcell" data-wide="true"><span>Directions and special instructions</span><b>{directions}</b></div>
                                <div className="f4-medh-recordcell"><span>Reason prescribed</span><b>{m.indication || 'Not recorded'}</b></div>
                            </div>
                        </section>
                        <section className="f4-medh-band">
                            <div className="f4-medh-bandhead">Prescription record</div>
                            <div className="f4-medh-bandgrid">
                                <div className="f4-medh-recordcell"><span>Last administered</span><b>{m.lastAdministered || 'Not recorded'}</b></div>
                                <div className="f4-medh-recordcell"><span>Prescription dates</span><b>{prescriptionDates}</b></div>
                                <div className="f4-medh-recordcell"><span>Prescriber</span><b>{m.prescriber || 'Not recorded'}</b></div>
                                <div className="f4-medh-recordcell"><span>Catalogue identity</span><b>{m.dmdCode ? `dm+d ${m.dmdCode}` : (m.coded ? 'Local catalogue item' : 'Legacy - mapping required')}</b></div>
                                <div className="f4-medh-recordcell"><span>Prescription version</span><b>{m.version || 1}</b></div>
                                <div className="f4-medh-recordcell"><span>Review due</span><b>{m.reviewDue || 'Not recorded'}</b></div>
                            </div>
                        </section>
                        <section className="f4-medh-band">
                            <div className="f4-medh-bandhead">Stock and supply</div>
                            <div className="f4-medh-bandgrid">
                                <div className="f4-medh-recordcell"><span>Supplying pharmacy</span><b>{m.pharmacy || 'Not recorded'}</b></div>
                                <div className="f4-medh-recordcell" data-tone={m.lowStock ? 'risk' : undefined}><span>Current stock</span><b>{stockValue}</b></div>
                                <div className="f4-medh-recordcell"><span>Stock action</span><b>{m.lowStock ? 'Review stock level' : 'No stock action recorded'}</b></div>
                            </div>
                        </section>
                    </div>
                    {(prnCells.length || canManage) ? (
                        <div className="f4-medh-bottom">
                            {prnCells.length ? (
                                <div className="f4-medh-prn">
                                    <p className="f4-eyebrow">PRN guidance</p>
                                    <div>
                                        {prnCells.map(([kind, val, lab]) => (
                                            <span className="f4-prncell" key={lab}>
                                                <span className="f4-prnic">{prnIcon(kind)}</span>
                                                <span><b>{val}</b><small>{lab}</small></span>
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            ) : null}
                            {canManage ? <div className="f4-medh-manage"><MedManage clientId={clientId} med={m} /></div> : null}
                        </div>
                    ) : null}
                </div>
            ) : null}
        </article>
    );
}

function Medications({ meds, client, clientId, canManage, age, weight, infoStrip = {}, onGoTab }) {
    const [query, setQuery] = useState('');
    const [page, setPage] = useState(1);
    const [selected, setSelected] = useState(() => meds.find((m) => m.lowStock || m.asRequired || m.isControlled)?.id || meds[0]?.id || null);
    const rowsPerPage = 10;

    const filtered = meds.filter((m) => {
        const haystack = [m.name, m.strength, m.form, m.dose, m.route, m.schedule, m.frequency, m.indication, m.prescriber, m.instruction]
            .filter(Boolean).join(' ').toLowerCase();
        return haystack.includes(query.trim().toLowerCase());
    });
    const totalPages = Math.max(1, Math.ceil(filtered.length / rowsPerPage));
    const safePage = Math.min(page, totalPages);
    const pageRows = filtered.slice((safePage - 1) * rowsPerPage, safePage * rowsPerPage);

    useEffect(() => {
        setPage(1);
    }, [query]);

    useEffect(() => {
        if (!filtered.length) {
            setSelected(null);
            return;
        }
        if (!filtered.some((m) => m.id === selected)) setSelected(filtered[0].id);
    }, [filtered, selected]);

    if (!meds.length) {
        return (
            <PlaceholderRecord
                eyebrow="Medication profile"
                title="Prescribed medicines"
                body="This client has no prescriptions on record."
                actions={canManage ? <Link href={`/frontend4/clients/${clientId}/medications/create`} className="f4-btn" data-size="sm">Add prescription</Link> : null}
                cells={[
                    ['Current medicines', '0', 'No active or previous prescriptions found'],
                    ['Next scheduled', 'Not scheduled', 'No scheduled medicines are recorded'],
                    ['Stock attention', '0', 'No medicine stock records to check'],
                    ['Next prescription review', 'Not recorded', 'No structured review date held'],
                ]}
            />
        );
    }

    const stats = [
        ['Current medicines', meds.filter((m) => m.statusLabel === 'Active').length, `${meds.filter((m) => !m.asRequired).length} scheduled · ${meds.filter((m) => m.asRequired).length} PRN`],
        ['Next scheduled', meds.find((m) => !m.asRequired && m.schedule)?.schedule || 'Not recorded', meds.find((m) => !m.asRequired && m.name)?.name || 'No scheduled medicine'],
        ['Stock attention', meds.filter((m) => m.lowStock).length, meds.find((m) => m.lowStock)?.name || 'No low-stock medicines'],
        ['Next prescription review', meds.find((m) => m.reviewDue)?.reviewDue || 'Not recorded', meds.find((m) => m.reviewDue)?.name || 'No structured review date held'],
    ];
    const filters = [
        ['Current', meds.filter((m) => m.statusLabel === 'Active').length],
        ['Regular', meds.filter((m) => !m.asRequired).length],
        ['PRN', meds.filter((m) => m.asRequired).length],
        ['Controlled drug', meds.filter((m) => m.isControlled).length],
        ['All', meds.length],
    ];
    const safety = [
        ['!', 'Penicillin allergy', infoStrip.allergy || (client.allergies || [])[0] || 'No allergy recorded', Boolean(infoStrip.allergy || (client.allergies || []).length), 'risk'],
        ['✓', 'Swallowing', infoStrip.swallowing || 'No swallowing difficulty recorded', false, 'good'],
        ['✓', 'Consent', infoStrip.capacity || 'Capacity / consent not recorded', false, 'good'],
        ['i', 'Support level', infoStrip.medSupport || 'Medication support not recorded', false, 'info'],
    ];
    const links = [
        ['PRN', 'PRN protocols', 'Limits, intervals and outcomes', 'prn'],
        ['MAR', 'MAR history', 'Administrations and omissions', 'mar'],
        ['!', 'Allergies', 'Reactions and severity', 'allergies'],
        ['↻', 'Audit history', 'Prescription changes', 'audit'],
    ];

    return (
        <>
            <TabPanel className="f4-medh-panel">
                <div className="f4-medh">
                    <div className="f4-medh-titlebar">
                        <div>
                            <p className="f4-eyebrow">Medication profile</p>
                            <h2>Prescribed medicines</h2>
                            <p>Current prescriptions, administration instructions and medicine status for {client.name}.</p>
                        </div>
                        <div className="f4-medh-titleactions">
                            {canManage ? <Link href={`/frontend4/clients/${clientId}/medications/create`} className="f4-btn" data-size="sm">Add prescription</Link> : null}
                            <span className="f4-medh-lock">{canManage ? 'Catalogue selection required' : 'Prescription changes restricted'}</span>
                        </div>
                    </div>
                    <div className="f4-medh-summary f4-medh-kpis">
                        {stats.map(([k, v, sub]) => <div className="f4-medh-stat" data-empty={v === 'Not recorded' ? 'true' : undefined} key={k}><span>{k}</span><b>{v}</b><small>{sub}</small></div>)}
                    </div>
                    <div className="f4-medh-controls">
                        <div className="f4-medh-filters">
                            {filters.map(([label, count], i) => <button key={label} type="button" className="f4-medh-chip" data-active={i === 0 ? 'true' : undefined}>{label} <b>{count}</b></button>)}
                        </div>
                        <label className="f4-medh-search">
                            <input type="search" value={query} onChange={(e) => setQuery(e.target.value)} placeholder="Medicine, route, indication..." />
                        </label>
                    </div>
                    <div className="f4-medh-layout">
                        <section className="f4-medh-list" aria-label="Medication register">
                            {pageRows.length ? pageRows.map((m, i) => (
                                <MedDesktopRow key={m.id} m={m} index={(safePage - 1) * rowsPerPage + i} open={m.id === selected}
                                               onOpen={() => setSelected(m.id)} clientId={clientId} canManage={canManage} age={age} weight={weight} />
                            )) : <p className="f4-panel-note">No medicines match that search.</p>}
                            {totalPages > 1 ? (
                                <div className="f4-medh-pager" aria-label="Medication pages">
                                    <button type="button" className="f4-btn" data-variant="secondary" data-size="sm" disabled={safePage <= 1} onClick={() => setPage((p) => Math.max(1, p - 1))}>Previous</button>
                                    <span>Page {safePage} of {totalPages}</span>
                                    <button type="button" className="f4-btn" data-variant="secondary" data-size="sm" disabled={safePage >= totalPages} onClick={() => setPage((p) => Math.min(totalPages, p + 1))}>Next</button>
                                </div>
                            ) : null}
                        </section>
                        <aside className="f4-medh-side">
                            <section className="f4-medh-sidecard">
                                <p className="f4-eyebrow">Safety information</p>
                                <h3>Before administering</h3>
                                {safety.map(([icon, title, body, alert, tone]) => (
                                    <div className="f4-medh-safety" data-tone={tone} key={title}>
                                        <span>{icon}</span><p><b>{title}</b><small>{body}</small></p>
                                    </div>
                                ))}
                            </section>
                            <section className="f4-medh-sidecard">
                                <p className="f4-eyebrow">Quick links</p>
                                <h3>Related records</h3>
                                {links.map(([icon, title, body, tab]) => (
                                    <button key={tab} type="button" className="f4-medh-link" onClick={() => onGoTab?.(tab)}>
                                        <span>{icon}</span><p><b>{title}</b><small>{body}</small></p><i>›</i>
                                    </button>
                                ))}
                            </section>
                        </aside>
                    </div>
                </div>
            </TabPanel>

            <div className="f4-medcards f4-medcards-mobile">
                {meds.map((m) => (
                    <MedCard key={m.id} m={m} clientId={clientId} canManage={canManage} age={age} weight={weight} />
                ))}
            </div>
        </>
    );
}

function Prn({ items }) {
    if (!items.length) {
        return (
            <PlaceholderRecord
                eyebrow="When required"
                title="PRN protocols"
                body="This client has no when-required medicines recorded."
                cells={[
                    ['Active protocols', '0', 'No PRN medicines found'],
                    ['Availability', 'Not applicable', 'No PRN dose limits to check'],
                    ['Outcome follow-up', 'Not applicable', 'PRN reviews appear after PRN administrations'],
                    ['Protocol governance', 'Not structured', 'Created/reviewed dates require protocol records'],
                ]}
            />
        );
    }
    return (
        <TabPanel eyebrow="When required" title="PRN protocols"
                  note="Symptoms to check, non-medication steps to try first, escalation and the effectiveness-review requirement are part of the fuller PRN protocol (a planned data upgrade). Shown here as recorded.">
            <div className="f4-prn-list">
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
            </div>
        </TabPanel>
    );
}

function MarHistory({ rows, capped, clientId }) {
    const action = (
        <Link href={`/frontend4/clients/${clientId}/mar`} className="f4-textbtn">View full MAR →</Link>
    );
    if (!rows.length) {
        return (
            <PlaceholderRecord
                eyebrow="Administration record"
                title="MAR history"
                body="No administrations are recorded for this client yet."
                actions={action}
                cells={[
                    ['Recorded administrations', '0', 'Given, refused and omitted entries will appear here'],
                    ['Exceptions', '0', 'Late, refused or omitted doses will be flagged when recorded'],
                    ['PRN administrations', '0', 'Reason and outcome records appear after PRN use'],
                    ['Correction history', 'Not recorded', 'Corrections remain visible in the audit trail'],
                ]}
            />
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
        return (
            <PlaceholderRecord
                eyebrow="Clinical safety record"
                title="Allergies and adverse reactions"
                body="No allergies are recorded for this client."
                cells={[
                    ['Drug allergy status', 'None recorded', 'No active allergy text is held'],
                    ['Reaction history', 'Not recorded', 'No reaction rows exist until an allergy is recorded'],
                    ['Next review', 'Not recorded', 'No structured review date held'],
                ]}
            />
        );
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

function AllergiesRich({ allergies, infoStrip = {}, onGoTab }) {
    if (!allergies.length) {
        return (
            <PlaceholderRecord
                eyebrow="Clinical safety record"
                title="Allergies and adverse reactions"
                body="No allergies or adverse reactions are recorded for this client."
                actions={<button type="button" className="f4-btn" data-variant="destructive" data-size="sm">Report new reaction</button>}
                cells={[
                    ['Drug allergy status', 'None recorded', 'No active allergy text is held'],
                    ['Clinical verification', 'Not structured', 'Verification source is not held until an allergy is recorded'],
                    ['Other adverse reactions', 'None recorded', 'No intolerance records are held separately yet'],
                    ['Next review', 'Not recorded', 'No structured review date held'],
                    ['Medicine safety', 'Check at prescribing', 'Confirm allergy status before giving a new medicine'],
                    ['Related records', 'Use context tabs', 'Medication, MAR and audit links appear when relevant records exist'],
                ]}
            />
        );
    }
    const allergyName = allergies.join(', ');
    const reaction = infoStrip.allergyReaction || 'Reaction details not recorded';
    const summary = [
        ['!', 'Drug allergy status', 'Known drug allergy', `${allergies.length} active allerg${allergies.length === 1 ? 'y' : 'ies'} - ${allergyName}`, 'risk'],
        ['OK', 'Clinical verification', 'Not structured', 'Source verification is not yet held as structured data', 'good'],
        ['0', 'Other adverse reactions', 'None recorded', 'No intolerance records are held separately yet', 'info'],
        ['R', 'Next review', 'Not recorded', 'No structured review date held', 'info'],
    ];
    const responseSteps = [
        ['1', 'Stop and assess', 'Do not give further dose. Stay with the person and assess symptoms.'],
        ['2', 'Call emergency services for severe symptoms', 'Breathing difficulty, swelling, collapse, severe wheeze or rapidly worsening symptoms.'],
        ['3', 'Seek clinical advice', 'For non-emergency symptoms, contact the shift lead and an appropriate clinician promptly.'],
        ['4', 'Record and report', 'Document the medicine, dose, time, symptoms, action and outcome.'],
    ];
    const safetyChecks = [
        ['OK', 'Allergen identified', allergyName],
        [reaction === 'Reaction details not recorded' ? '!' : 'OK', 'Reaction documented', reaction],
        ['!', 'Source verified', 'Verification source not structured'],
        ['!', 'Review current', 'Next review not recorded'],
    ];
    return (
        <div className="f4-allergy-page">
            <div className="f4-allergy-title">
                <div>
                    <p className="f4-eyebrow">Clinical safety record</p>
                    <h2>Allergies and adverse reactions</h2>
                    <p>Verified allergy status, reaction history and instructions that must be checked before prescribing or administering medicines.</p>
                </div>
                <div className="f4-allergy-actions">
                    <button type="button" className="f4-btn" data-variant="secondary" data-size="sm">View allergy audit</button>
                    <button type="button" className="f4-btn" data-variant="destructive" data-size="sm">Report new reaction</button>
                </div>
            </div>

            <div className="f4-allergy-summary">
                {summary.map(([icon, k, v, sub, tone]) => (
                    <div className="f4-allergy-stat" data-tone={tone} key={k}>
                        <span>{icon}</span>
                        <p><small>{k}</small><b>{v}</b><em>{sub}</em></p>
                    </div>
                ))}
            </div>

            <div className="f4-allergy-layout">
                <main className="f4-allergy-main">
                    {allergies.map((allergy) => (
                        <section className="f4-allergy-card" key={allergy}>
                            <div className="f4-allergy-cardhead">
                                <span className="f4-allergy-alert">!</span>
                                <div>
                                    <div className="f4-medh-pills"><i>Drug allergy</i><i>Severity not recorded</i><i>Active</i></div>
                                    <h3>{allergy}</h3>
                                    <p>Ingredient class and related medicines are not yet structured in the record.</p>
                                </div>
                                <div className="f4-allergy-recordstatus"><span>Record status</span><b>Needs structured verification</b><small>Shown from free-text client record</small></div>
                            </div>
                            <div className="f4-allergy-warning"><b>Do not administer medicines containing {allergy.toLowerCase()} without clinical authorisation</b><small>Check the active ingredient, prescribing information and allergy status during medication reconciliation and before administration.</small></div>
                            <div className="f4-allergy-grid">
                                <div><span>Signs and symptoms</span><b>{reaction}</b><small>Structured severity is not yet recorded.</small></div>
                                <div><span>Severity recorded</span><b>Not recorded</b><small>Severity is a planned structured-data upgrade.</small></div>
                                <div><span>Reaction date</span><b>Not recorded</b><small>Exact date not held.</small></div>
                                <div><span>Drug taken</span><b>{allergy}</b><small>Specific triggering medicine not separately recorded.</small></div>
                                <div><span>Indication at the time</span><b>Not recorded</b><small>Context is not yet structured.</small></div>
                                <div><span>Care received</span><b>Not recorded</b><small>Treatment/advice not yet structured.</small></div>
                            </div>
                        </section>
                    ))}

                    <section className="f4-allergy-section">
                        <div className="f4-allergy-sectionhead">
                            <div><p className="f4-eyebrow">Evidence and certainty</p><h3>How was this allergy confirmed?</h3></div>
                            <span className="f4-tag" data-tone="caution">Needs verification fields</span>
                        </div>
                        <div className="f4-allergy-four">
                            <div><span>Primary source</span><b>Not recorded</b><small>Add GP / pharmacy / hospital source when available.</small></div>
                            <div><span>Person's account</span><b>{reaction}</b><small>Shown only if reaction text exists.</small></div>
                            <div><span>Last reconciliation</span><b>Not recorded</b><small>No structured reconciliation date held.</small></div>
                            <div><span>Recorded by</span><b>Not recorded</b><small>Free-text allergy field has no author metadata.</small></div>
                        </div>
                    </section>

                    <section className="f4-allergy-section">
                        <div><p className="f4-eyebrow">Medicine safety</p><h3>What must staff check?</h3></div>
                        <div className="f4-allergy-checklist">
                            <div data-tone="risk"><span>!</span><p><b>Avoid matching allergy class</b><small>Do not administer matching medicines unless a prescriber has clinically reviewed and authorised it.</small></p></div>
                            <div><span>i</span><p><b>Do not assume unrelated medicines are unsafe</b><small>Check the exact active ingredient and seek pharmacist or prescriber advice where class or cross-reactivity is unclear.</small></p></div>
                            <div><span>OK</span><p><b>Confirm at transitions of care</b><small>Reconcile and communicate allergy status after hospital discharge, GP changes or any new prescription.</small></p></div>
                        </div>
                    </section>

                    <section className="f4-allergy-section">
                        <div className="f4-allergy-sectionhead">
                            <div><p className="f4-eyebrow">Reaction history</p><h3>Recorded allergy events</h3></div>
                        </div>
                        <div className="f4-allergy-event"><b>No structured allergy event history yet</b><small>The current schema stores the allergy and reaction text, but not event timeline rows.</small></div>
                    </section>
                </main>

                <aside className="f4-allergy-side">
                    <section className="f4-allergy-response">
                        <p className="f4-eyebrow">Emergency response</p>
                        <h3>If a reaction is suspected</h3>
                        {responseSteps.map(([n, title, body]) => (
                            <div className="f4-allergy-step" key={n}><span>{n}</span><p><b>{title}</b><small>{body}</small></p></div>
                        ))}
                        <button type="button" className="f4-btn" data-variant="destructive">Report suspected reaction</button>
                    </section>

                    <section className="f4-medh-sidecard">
                        <p className="f4-eyebrow">Safety checks</p>
                        <h3>Record completeness</h3>
                        {safetyChecks.map(([icon, title, body]) => (
                            <div className="f4-medh-safety" data-tone={icon === '!' ? 'risk' : 'good'} key={title}>
                                <span>{icon}</span><p><b>{title}</b><small>{body}</small></p>
                            </div>
                        ))}
                    </section>

                    <section className="f4-medh-sidecard">
                        <p className="f4-eyebrow">Related records</p>
                        <h3>Check in context</h3>
                        {[
                            ['Rx', 'Current medicines', 'Review active ingredients', 'medications'],
                            ['MAR', 'MAR history', 'Check administrations', 'mar'],
                            ['R', 'Allergy audit', 'Who changed what and when', 'audit'],
                        ].map(([icon, title, body, tab]) => (
                            <button key={tab} type="button" className="f4-medh-link" onClick={() => onGoTab?.(tab)}>
                                <span>{icon}</span><p><b>{title}</b><small>{body}</small></p><i>&gt;</i>
                            </button>
                        ))}
                    </section>
                </aside>
            </div>
        </div>
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



export {
    TABS,
    tabFromHash,
    initials,
    CareNotes,
    Documents,
    AuditHistory,
    ComingNext,
    Medications,
    Prn,
    MarHistory,
    AllergiesRich,
    Overview,
};


