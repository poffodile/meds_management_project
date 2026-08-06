/**
 * frontend4 — Clients (the service users).
 *
 * Page 1 of the build. A searchable list of the people this user is responsible
 * for; tapping one opens their profile. Read-only — nothing here records or
 * edits anything.
 *
 * Design reasoning: docs/care-one-os/FRONTEND4/RECORD7-BUILD-PLAN.md (Page 1),
 * FRONTEND4-DESIGN.md. Clients data all arrives with the page, so search and
 * filtering happen here in the browser rather than as another round-trip.
 */

import React, { useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { Person, Empty, term } from '@frontend4/components/F4Atoms';

/** The chevron that says a row opens something, without shouting. */
function Chevron() {
    return (
        <svg className="f4-row-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <path d="m9 18 6-6-6-6" />
        </svg>
    );
}

export default function Clients({
    clients = [], total = 0, place = null, user = null, roleLabel = null, can = [], terms = {},
}) {
    const [query, setQuery] = useState('');
    const [location, setLocation] = useState('');
    const [status, setStatus] = useState('active');

    const peopleWord = term(terms, 'people');
    const personWord = term(terms, 'person');

    // Locations that actually exist in this home — nothing assumes a room.
    const locations = useMemo(() => {
        const set = new Set();
        clients.forEach((c) => { if (c.location) set.add(c.location); });
        return Array.from(set).sort();
    }, [clients]);

    const filtered = useMemo(() => {
        const needle = query.trim().toLowerCase();
        return clients.filter((c) => {
            if (status !== 'all' && (c.active ? 'active' : 'inactive') !== status) return false;
            if (location && c.location !== location) return false;
            if (needle && !c.name.toLowerCase().includes(needle)) return false;
            return true;
        });
    }, [clients, query, location, status]);

    // Grouped A–Z so a long list reads as ordered rather than endless.
    const groups = useMemo(() => {
        const map = {};
        filtered.forEach((c) => { (map[c.letter] ||= []).push(c); });
        return Object.keys(map).sort().map((letter) => ({ letter, items: map[letter] }));
    }, [filtered]);

    const summary = `${total} ${total === 1 ? personWord : peopleWord}`;

    return (
        <F4Shell area="clients" title="Clients" summary={summary}
                 place={place} user={user} roleLabel={roleLabel} can={can}>
            <Head title="Clients — Care One OS" />

            <div className="f4-page-enter">
            <div className="f4-toolbar">
                <div className="f4-search">
                    <svg className="f4-search-icon" width="17" height="17" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3" />
                    </svg>
                    <input
                        className="f4-input f4-search-input"
                        type="search"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder={`Search ${peopleWord} by name`}
                        aria-label={`Search ${peopleWord} by name`}
                    />
                </div>
                {locations.length > 0 ? (
                    <select
                        className="f4-filter-select"
                        value={location}
                        onChange={(e) => setLocation(e.target.value)}
                        aria-label="Filter by location"
                    >
                        <option value="">All locations</option>
                        {locations.map((l) => <option key={l} value={l}>{l}</option>)}
                    </select>
                ) : null}
                <select
                    className="f4-filter-select"
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    aria-label="Filter by status"
                >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="all">All statuses</option>
                </select>
            </div>

            {filtered.length === 0 ? (
                clients.length === 0 ? (
                    <Empty
                        title={`No ${peopleWord} yet`}
                        body={`There are no ${peopleWord} in ${place || 'this home'} yet. When someone is added to this home, they will appear here.`}
                    />
                ) : (
                    <Empty
                        title={`No ${peopleWord} match your search`}
                        action={(
                            <button type="button" className="f4-btn" onClick={() => { setQuery(''); setLocation(''); setStatus('active'); }}>
                                Clear filters
                            </button>
                        )}
                    />
                )
            ) : (
                <div className="f4-clients-groups">
                    {groups.map((g) => (
                        <div className="f4-az-group" key={g.letter}>
                            <div className="f4-az-head" aria-hidden="true">{g.letter}</div>
                            <section className="f4-card" data-pad="none">
                                <div className="f4-rows">
                                {g.items.map((c) => {
                                    const meta = [
                                        c.age != null ? `${c.age} yrs` : null,
                                        c.location,
                                    ].filter(Boolean).join(' · ') || null;

                                    return (
                                        <Link key={c.id} href={`/frontend4/clients/${c.id}`} className="f4-row f4-client-row">
                                            <span className="f4-row-main">
                                                <Person name={c.name} photo={c.photo} meta={meta} />
                                            </span>
                                            <span className="f4-row-end">
                                                {c.hasAllergy ? <span className="f4-tag" data-tone="risk">Allergy</span> : null}
                                                {!c.active ? <span className="f4-tag" data-tone="muted">Inactive</span> : null}
                                                <Chevron />
                                            </span>
                                        </Link>
                                    );
                                })}
                                </div>
                            </section>
                        </div>
                    ))}
                </div>
            )}
            </div>
        </F4Shell>
    );
}
