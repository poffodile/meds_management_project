import React from 'react';
import { Head } from '@inertiajs/react';

/**
 * frontend3 landing page.
 *
 * The only entry point into frontend3 — reached from the "Frontend 3" button in
 * the old Blade header, exactly like Frontend 1 and Frontend 2. Login is
 * untouched: you still sign in through the Blade login and land on the normal
 * Blade page.
 *
 * Styling comes entirely from frontend3/f3.css (scoped under .f3-root, applied
 * by resources/js/f3.jsx). No frontend1 or frontend2 stylesheet is involved.
 */

const AREAS = [
    {
        n: '01',
        name: 'Today',
        purpose: 'Immediate work and risk',
        pages: 'Dashboard · medication round · tasks · alerts · handover',
        screen: 'careone-dashboard-wireframe.html',
    },
    {
        n: '02',
        name: 'People',
        purpose: 'The person-centred record',
        pages: 'Directory · overview · medication profile · allergies · documents · contacts',
        screen: 'careone-person-profile-wireframe.html',
    },
    {
        n: '03',
        name: 'Medicines',
        purpose: 'Medication record and workflow',
        pages: 'MAR · PRN · prescriptions · stock · orders · returns · disposal',
        screen: 'careone-mar-wireframe.html',
    },
    {
        n: '04',
        name: 'Operations',
        purpose: 'Cross-shift control',
        pages: 'Exceptions · controlled drugs · handover · incidents · pharmacy messages',
        screen: 'careone-missed-doses-wireframe.html',
    },
    {
        n: '05',
        name: 'Assurance',
        purpose: 'Management and evidence',
        pages: 'Reports · audits · compliance · trends · clinical-safety log',
        screen: 'careone-manager-compliance-wireframe.html',
    },
    {
        n: '06',
        name: 'Settings',
        purpose: 'Organisation configuration',
        pages: 'Sites · roles · permissions · terminology · integrations · AI policy',
        screen: 'careone-admin-integrations-wireframe.html',
    },
];

export default function Home({ auth, wireframesUrl }) {
    return (
        <>
            <Head title="Frontend 3" />

            <div className="f3-page">

                <div className="f3-header" style={{ marginBottom: 'var(--f3-s5)' }}>
                    <div className="f3-mark" aria-hidden="true">C1</div>
                    <div style={{ flex: 1, minWidth: 240 }}>
                        <h1 className="f3-title">Frontend 3</h1>
                        <p className="f3-sub">
                            Care One OS · built to the UX Specification · Quiet Clinical Luxury
                        </p>
                    </div>
                    <div className="f3-row">
                        <span className="f3-badge f3-badge--info">Experimental</span>
                        {auth?.user && (
                            <span className="f3-chip">
                                Signed in as <b>&nbsp;{auth.user.name}</b>
                            </span>
                        )}
                    </div>
                </div>

                <div className="f3-note" style={{ marginBottom: 'var(--f3-s5)' }}>
                    <b>You are inside frontend3.</b> It runs on the same Laravel + React + Inertia +
                    Mantine stack as the rest of the app, but it loads its own layout, its own theme and
                    its own stylesheet — every rule scoped under <code>.f3-root</code>. Nothing you change
                    here can reach a Frontend 1 or Frontend 2 page. Your login and the normal Blade landing
                    page are untouched.
                </div>

                <div className="f3-stack" style={{ marginBottom: 'var(--f3-s7)' }}>
                    <div className="f3-row">
                        <h2>The six areas</h2>
                        <span className="f3-badge f3-badge--ghost">
                            52 page templates · 26 in the MVP
                        </span>
                    </div>

                    <div className="f3-grid f3-grid--cards">
                        {AREAS.map((area) => (
                            <a
                                key={area.n}
                                className="f3-tile"
                                href={`${wireframesUrl}/${area.screen}`}
                            >
                                <span className="f3-tile-num">{area.n}</span>
                                <h3>{area.name}</h3>
                                <p className="f3-tile-text">{area.purpose}</p>
                                <p className="f3-xs f3-mut" style={{ marginTop: 'auto' }}>
                                    {area.pages}
                                </p>
                            </a>
                        ))}
                    </div>
                </div>

                <div className="f3-card">
                    <h2 style={{ marginBottom: 'var(--f3-s3)' }}>Where things stand</h2>
                    <p className="f3-sm f3-mut" style={{ marginBottom: 'var(--f3-s4)' }}>
                        The specification is agreed and twelve concept screens are drawn. No production
                        page has been built yet — the first build slice has not been chosen.
                    </p>

                    <div className="f3-stack" style={{ gap: 'var(--f3-s2)' }}>
                        <div className="f3-row">
                            <span className="f3-badge f3-badge--good">Done</span>
                            <span className="f3-sm">Branch, specification, isolation rules, design tokens, scoped stylesheet</span>
                        </div>
                        <div className="f3-row">
                            <span className="f3-badge f3-badge--good">Done</span>
                            <span className="f3-sm">Twelve concept screens, mobile and desktop</span>
                        </div>
                        <div className="f3-row">
                            <span className="f3-badge f3-badge--good">Done</span>
                            <span className="f3-sm">This shell — own root view, own Vite entry, own theme</span>
                        </div>
                        <div className="f3-row">
                            <span className="f3-badge f3-badge--caution">Next</span>
                            <span className="f3-sm">Choose the first slice — the spec suggests global shell, dashboard, then medication round</span>
                        </div>
                    </div>

                    <hr className="f3-hr" style={{ margin: 'var(--f3-s5) 0' }} />

                    <div className="f3-row">
                        <a className="f3-btn f3-btn--primary" href={`${wireframesUrl}/index.html`}>
                            Open the twelve concept screens
                        </a>
                        <a className="f3-btn" href="/">
                            Back to the main app
                        </a>
                    </div>
                </div>

                <p className="f3-xs f3-mut" style={{ marginTop: 'var(--f3-s5)', textAlign: 'center' }}>
                    Concept screens use fictional data only. Nothing here is clinical advice or a compliance claim.
                </p>

            </div>
        </>
    );
}
