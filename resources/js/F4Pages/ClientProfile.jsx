/**
 * frontend4 - Client profile (Page 2).
 *
 * Shell and data router for the profile. Tab implementations live in
 * ClientProfile/ClientProfileSections.jsx so the page stays maintainable while
 * preserving the reference Clients-section workflow.
 */

import React, { useEffect, useRef, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { allows } from '@frontend4/roles';
import {
    TABS,
    tabFromHash,
    initials,
    Overview,
    Medications,
    Prn,
    MarHistory,
    AllergiesRich,
    CareNotes,
    Documents,
    AuditHistory,
    ComingNext,
} from './ClientProfile/ClientProfileSections';
export default function ClientProfile({
    client, medications = [], prn = [], marHistory = [], marCapped = false,
    careNotes = [], documents = [], audit = [],
    nextMed = null, keyDetails = [], activeMeds = [], contacts = {}, careInstructions = [], recent = [],
    infoStrip = {}, headerMeta = [], headerStats = [], roundUrl = '/frontend4/round',
    place = null, user = null, roleLabel = null, can = [], accessContext = null,
}) {
    const [active, setActive] = useState(tabFromHash);
    const tabRefs = useRef({});
    const canManage = allows(can, 'manage_prescription');
    const canManageClient = allows(can, 'manage_clients');

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
                 place={place} user={user} roleLabel={roleLabel} can={can} accessContext={accessContext}>
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
                {canManageClient ? <Link href={`/frontend4/clients/${client.id}/edit`} className="f4-btn" data-size="sm">Manage client record</Link> : null}
            </div>
            {/* Identity, the info strip, and the tabs. */}
            <div className="f4-profile-head">
                <div className="f4-profile-id">
                    <span className="f4-profile-av" aria-hidden="true">{initials(client.name)}</span>
                    <div className="f4-profile-idmain">
                        <div className="f4-profile-nmrow">
                            <span className="f4-profile-nm">{client.name}</span>
                            {client.status ? <span className="f4-tag" data-tone={client.status === 'Active' ? 'good' : 'muted'}>{client.status}</span> : null}
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
                    : active === 'medications' ? <Medications meds={medications} client={client} clientId={client.id} canManage={canManage} age={client.age} weight={client.weight} infoStrip={infoStrip} onGoTab={setActive} />
                    : active === 'prn' ? <Prn items={prn} />
                    : active === 'allergies' ? <AllergiesRich allergies={client.allergies || []} infoStrip={infoStrip} onGoTab={setActive} />
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
