/**
 * frontend4 - Clients directory.
 *
 * Adapted from the Care One OS Clients reference package, connected to the
 * Laravel/Inertia data supplied by Frontend4\ClientsController. No prototype
 * client data is embedded here.
 */

import React, { useEffect, useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { Empty, term } from '@frontend4/components/F4Atoms';
import { allows } from '@frontend4/roles';

function initials(name) {
    const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    return parts.length ? (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase() : '?';
}

function SummaryButton({ active, label, count, onClick }) {
    return (
        <button type="button" className="f4-client-summary" data-active={active ? 'true' : undefined} aria-pressed={active} onClick={onClick}>
            <span>{label}</span>
            <strong>{count}</strong>
        </button>
    );
}

function ClientPreview({ client, onClose, can = [] }) {
    if (!client) return null;
    const meta = [client.age != null ? `${client.age} years` : null, client.dob, client.location, client.nhs ? `NHS ${client.nhs}` : null].filter(Boolean);
    const meds = client.medicines || [];
    return (
        <>
            <button type="button" className="f4-preview-scrim" aria-label="Close client preview" onClick={onClose} />
            <aside className="f4-client-preview" role="dialog" aria-modal="true" aria-labelledby="f4-client-preview-title">
                <header>
                    <p className="f4-eyebrow">Client quick preview</p>
                    <button type="button" onClick={onClose} aria-label="Close client preview">x</button>
                </header>
                <div className="f4-preview-person">
                    <span className="f4-profile-av" aria-hidden="true">{initials(client.name)}</span>
                    <div>
                        <h2 id="f4-client-preview-title">{client.name}</h2>
                        <span className="f4-tag" data-tone={client.active ? 'good' : 'muted'}>{client.statusLabel}</span>
                    </div>
                </div>
                <dl className="f4-preview-details">
                    <div><dt>Client details</dt><dd>{meta.join(' - ') || 'Not recorded'}</dd></div>
                    <div><dt>Medication support</dt><dd>{client.support || 'Not recorded'}</dd></div>
                    <div><dt>Allergies and reactions</dt><dd data-risk={client.hasAllergy ? 'true' : undefined}>{client.allergyText || 'No known allergies'}{client.reaction ? ` - ${client.reaction}` : ''}</dd></div>
                    <div><dt>Key worker</dt><dd>{client.keyWorker || 'Not recorded'}</dd></div>
                </dl>
                <section className="f4-preview-card">
                    <p className="f4-eyebrow">Medication schedule</p>
                    <div><span>Next scheduled</span><strong>{client.nextMed || 'Not scheduled'}</strong></div>
                    <small>{meds.length ? meds.join(' - ') : 'No active medicines recorded'}</small>
                </section>
                <section className="f4-preview-card">
                    <p className="f4-eyebrow">Open medication concerns</p>
                    <strong data-risk={client.attention ? 'true' : undefined}>{client.concern || 'No open medication concern recorded'}</strong>
                </section>
                <section className="f4-preview-contacts">
                    <div><span>GP</span><strong>{client.gp?.name || 'Not recorded'}</strong>{client.gp?.sub ? <small>{client.gp.sub}</small> : null}</div>
                    <div><span>Pharmacy</span><strong>{client.pharmacy?.name || 'Not recorded'}</strong>{client.pharmacy?.sub ? <small>{client.pharmacy.sub}</small> : null}</div>
                    <div><span>Next of kin</span><strong>{client.nextOfKin?.name || 'Not recorded'}</strong>{client.nextOfKin?.sub ? <small>{client.nextOfKin.sub}</small> : null}</div>
                </section>
                <div className="f4-preview-actions">
                    {client.lifecycleStatus !== 'archived' ? <Link className="f4-btn" href={`/frontend4/clients/${client.id}`}>Open full profile</Link> : null}
                    {allows(can, 'manage_clients') ? <Link className="f4-btn" data-variant="secondary" href={`/frontend4/clients/${client.id}/edit`}>Manage record</Link> : null}
                    <Link className="f4-btn" data-variant="secondary" href={`/frontend4/clients/${client.id}#medications`}>View medications</Link>
                    <Link className="f4-btn" data-variant="secondary" href={`/frontend4/clients/${client.id}#mar`}>View MAR</Link>
                </div>
            </aside>
        </>
    );
}

export default function Clients({
    clients = [], total = 0, place = null, user = null, roleLabel = null, can = [], accessContext = null, terms = {},
}) {
    const [query, setQuery] = useState('');
    const [location, setLocation] = useState('');
    const [status, setStatus] = useState('active');
    const [support, setSupport] = useState('');
    const [summary, setSummary] = useState('all');
    const [selected, setSelected] = useState(null);

    const peopleWord = term(terms, 'people');
    const personWord = term(terms, 'person');

    useEffect(() => {
        const onKey = (event) => { if (event.key === 'Escape') setSelected(null); };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, []);

    const locations = useMemo(() => Array.from(new Set(clients.map((c) => c.location).filter(Boolean))).sort(), [clients]);
    const supportLevels = useMemo(() => Array.from(new Set(clients.map((c) => c.support).filter(Boolean))).sort(), [clients]);

    const counts = useMemo(() => ({
        all: clients.length,
        due: clients.filter((c) => Number(c.due || 0) > 0).length,
        attention: clients.filter((c) => c.attention).length,
        allergy: clients.filter((c) => c.hasAllergy).length,
        inactive: clients.filter((c) => !c.active).length,
    }), [clients]);

    const filtered = useMemo(() => {
        const needle = query.trim().toLowerCase();
        return clients.filter((c) => {
            const searchable = [c.name, c.preferred, c.location, c.nhs, c.support, c.keyWorker, ...(c.medicines || [])].filter(Boolean).join(' ').toLowerCase();
            if (status !== 'all' && (c.active ? 'active' : 'inactive') !== status) return false;
            if (location && c.location !== location) return false;
            if (support && c.support !== support) return false;
            if (summary === 'due' && Number(c.due || 0) <= 0) return false;
            if (summary === 'attention' && !c.attention) return false;
            if (summary === 'allergy' && !c.hasAllergy) return false;
            if (summary === 'inactive' && c.active) return false;
            if (needle && !searchable.includes(needle)) return false;
            return true;
        });
    }, [clients, query, location, status, support, summary]);

    const groups = useMemo(() => {
        const map = {};
        filtered.forEach((c) => { (map[c.letter] ||= []).push(c); });
        return Object.keys(map).sort().map((letter) => ({ letter, items: map[letter] }));
    }, [filtered]);

    const clearFilters = () => {
        setQuery('');
        setLocation('');
        setStatus('active');
        setSupport('');
        setSummary('all');
    };

    const pageSummary = `${total} ${total === 1 ? personWord : peopleWord}`;

    return (
        <F4Shell area="clients" title="Clients" summary={pageSummary}
                 place={place} user={user} roleLabel={roleLabel} can={can} accessContext={accessContext}>
            <Head title="Clients - Care One OS" />

            <div className="f4-page-enter f4-clients-page">
                <header className="f4-clients-heading">
                    <div>
                        <p className="f4-eyebrow">People we support</p>
                        <h1>Clients</h1>
                        <p>People supported across {place || 'your assigned home'}</p>
                    </div>
                    <div className="f4-client-heading-actions">
                        <span className="f4-visible-count"><strong>{filtered.length}</strong> visible</span>
                        {allows(can, 'manage_clients') ? <Link className="f4-btn" href="/frontend4/clients/create">Add client</Link> : null}
                    </div>
                </header>

                <div className="f4-client-toolbar">
                    <label className="f4-client-search">
                        <span aria-hidden="true">?</span>
                        <input value={query} onChange={(e) => setQuery(e.target.value)} placeholder={`Search ${peopleWord}, NHS number or medication`} aria-label={`Search ${peopleWord}`} />
                        {query ? <button type="button" onClick={() => setQuery('')} aria-label="Clear search">x</button> : null}
                    </label>
                    {locations.length ? (
                        <select value={location} onChange={(e) => setLocation(e.target.value)} aria-label="Filter by location">
                            <option value="">All locations</option>
                            {locations.map((l) => <option key={l} value={l}>{l}</option>)}
                        </select>
                    ) : null}
                    <select value={status} onChange={(e) => setStatus(e.target.value)} aria-label="Filter by status">
                        <option value="active">Active</option>
                        <option value="inactive">Not active</option>
                        <option value="all">All statuses</option>
                    </select>
                    {supportLevels.length ? (
                        <select value={support} onChange={(e) => setSupport(e.target.value)} aria-label="Filter by support level">
                            <option value="">All support levels</option>
                            {supportLevels.map((s) => <option key={s} value={s}>{s}</option>)}
                        </select>
                    ) : null}
                </div>

                <section className="f4-client-summary-row" aria-label="Client summary filters">
                    <SummaryButton label="All clients" count={counts.all} active={summary === 'all'} onClick={() => setSummary('all')} />
                    <SummaryButton label="Medication due" count={counts.due} active={summary === 'due'} onClick={() => setSummary('due')} />
                    <SummaryButton label="Needs attention" count={counts.attention} active={summary === 'attention'} onClick={() => setSummary('attention')} />
                    <SummaryButton label="Allergy recorded" count={counts.allergy} active={summary === 'allergy'} onClick={() => setSummary('allergy')} />
                    <SummaryButton label="Inactive" count={counts.inactive} active={summary === 'inactive'} onClick={() => setSummary('inactive')} />
                </section>

                {filtered.length === 0 ? (
                    clients.length === 0 ? (
                        <Empty title={`No ${peopleWord} yet`} body={`There are no ${peopleWord} in ${place || 'this home'} yet. When someone is added to this home, they will appear here.`} />
                    ) : (
                        <Empty title={`No ${peopleWord} match this view`} action={<button type="button" className="f4-btn" onClick={clearFilters}>Clear filters</button>} />
                    )
                ) : (
                    <section className="f4-client-directory" aria-label="Client directory">
                        <div className="f4-client-list-head"><span>Client</span><span>Support and allergies</span><span>Status</span><span>Next medication</span><span>Due</span><span /></div>
                        {groups.map((g) => (
                            <div className="f4-client-alpha" key={g.letter}>
                                <h2>{g.letter}</h2>
                                {g.items.map((c) => {
                                    const meta = [c.age != null ? `${c.age} years` : null, c.location, c.nhs ? `NHS ${c.nhs}` : null].filter(Boolean).join(' - ');
                                    return (
                                        <button type="button" className="f4-client-dir-row" key={c.id} onClick={() => setSelected(c)}>
                                            <span className="f4-client-identity">
                                                <span className="f4-profile-av" aria-hidden="true">{initials(c.name)}</span>
                                                <span><strong>{c.name}</strong><small>{meta || 'Details not recorded'}</small></span>
                                            </span>
                                            <span className="f4-client-support"><strong>{c.support || 'Support not recorded'}</strong><small data-risk={c.hasAllergy ? 'true' : undefined}>{c.allergyText || 'No known allergies'}</small></span>
                                            <span><i className="f4-status-label" data-status={c.active ? 'active' : 'inactive'}>{c.statusLabel}</i></span>
                                            <span className="f4-client-medtime"><strong>{c.nextMed || 'Not scheduled'}</strong><small>{(c.medicines || [])[0] || 'No active medicine recorded'}</small></span>
                                            <span className="f4-client-due" data-due={Number(c.due || 0) > 0 ? 'true' : undefined}><strong>{c.due || 0}</strong><small>due</small></span>
                                            <span className="f4-row-chevron" aria-hidden="true">&gt;</span>
                                        </button>
                                    );
                                })}
                            </div>
                        ))}
                    </section>
                )}
            </div>
            <ClientPreview client={selected} onClose={() => setSelected(null)} can={can} />
        </F4Shell>
    );
}
