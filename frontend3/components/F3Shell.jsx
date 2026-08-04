import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';

/**
 * The frontend3 application shell.
 *
 * Built once, inherited by every frontend3 page. Handles the whole responsive
 * model from spec §4 and §18 in one place:
 *
 *   ≥1200px  labelled sidebar + content (three-zone where a page wants it)
 *   900–1199 sidebar collapses to an icon rail — the current area is NEVER hidden
 *   600–899  single column
 *   <600     sidebar gone, bottom navigation, sticky primary action in thumb reach
 *
 * All of that lives in frontend3/f3.css. This component only supplies structure.
 *
 * Uncluttered rule: the shell takes ONE primary action and shows it once —
 * top-right on desktop, in the sticky bar on mobile. Not both, not three.
 */

/** Spec §3 — the six areas. Stable order; this is the mental model. */
const AREAS = [
    { key: 'today',      label: 'Today',      icon: 'T', href: '/frontend3' },
    { key: 'people',     label: 'People',     icon: 'P', href: '/frontend3/people' },
    { key: 'medicines',  label: 'Medicines',  icon: 'M', href: '/frontend3/medicines' },
    { key: 'operations', label: 'Operations', icon: 'O', href: '/frontend3/operations' },
    { key: 'assurance',  label: 'Assurance',  icon: 'A', href: '/frontend3/assurance' },
    { key: 'settings',   label: 'Settings',   icon: 'S', href: '/frontend3/settings' },
];

/** Bottom navigation — five items maximum, the ones a carer actually uses on shift. */
const MOBILE = [
    { key: 'today',     label: 'Today',  icon: 'T', href: '/frontend3' },
    { key: 'people',    label: 'People', icon: 'P', href: '/frontend3/people' },
    { key: 'round',     label: 'Round',  icon: 'R', href: '/frontend3' },
    { key: 'operations',label: 'Alerts', icon: 'O', href: '/frontend3/operations' },
    { key: 'more',      label: 'More',   icon: '···', href: '/frontend3/start' },
];

function NavLink({ area, active, count }) {
    return (
        <Link
            className="f3-navlink"
            href={area.href}
            aria-current={active ? 'page' : undefined}
            title={area.label}
        >
            <span className="f3-navicon" aria-hidden="true">{area.icon}</span>
            <span className="f3-navlabel">{area.label}</span>
            {count > 0 && (
                <span className="f3-navcount">
                    {count}
                    <span className="f3-sr-only"> items needing attention</span>
                </span>
            )}
        </Link>
    );
}

export default function F3Shell({
    title,
    area,
    heading,
    summary,
    sync,
    context = [],
    action = null,
    stickyAction = null,
    counts = {},
    user = null,
    children,
}) {
    const initials = (user?.name || '')
        .split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]).join('').toUpperCase() || '·';

    // Controlled-drug signatures waiting on THIS user. Shared from the server on
    // every page, so it is always current wherever you are. Shown only when there
    // is something to do — a permanent zero would be clutter.
    const { witnessPending = 0 } = usePage().props;

    return (
        <>
            <Head title={title || heading} />

            <div className="f3-shell">

                <aside className="f3-nav">
                    <div className="f3-brand">
                        <div className="f3-mark" aria-hidden="true">C1</div>
                        <div className="f3-brand-text">
                            <div className="f3-brand-name">Care One OS</div>
                            <div className="f3-brand-sub">Frontend 3</div>
                        </div>
                    </div>

                    <nav className="f3-navgroup" aria-label="Main areas">
                        <h4>Areas</h4>
                        {AREAS.map((a) => (
                            <NavLink key={a.key} area={a} active={area === a.key} count={counts[a.key]} />
                        ))}
                    </nav>

                    <nav className="f3-navgroup">
                        <h4>Reference</h4>
                        <NavLink
                            area={{ label: 'Concept screens', icon: '◇', href: '/frontend3/wireframes' }}
                            active={false}
                        />
                        <NavLink
                            area={{ label: 'About this build', icon: 'i', href: '/frontend3/start' }}
                            active={area === 'start'}
                        />
                    </nav>
                </aside>

                <div className="f3-main">

                    <header className="f3-topbar">
                        <div className="f3-context">
                            {context.map((c, i) => (
                                <span key={i} className={`f3-chip${c.quiet ? ' f3-chip--quiet' : ''}`}>
                                    {c.strong ? <b>{c.label}</b> : c.label}
                                </span>
                            ))}
                        </div>
                        <div className="f3-topbar-actions">
                            {witnessPending > 0 && (
                                <Link className="f3-btn f3-btn--sm" href="/frontend3/signatures">
                                    <span className="f3-badge f3-badge--risk">{witnessPending}</span>
                                    <span className="f3-hide-sm">
                                        {witnessPending === 1 ? 'signature' : 'signatures'} awaiting you
                                    </span>
                                    <span className="f3-sr-only">Controlled drug signatures awaiting your confirmation</span>
                                </Link>
                            )}
                            <div className="f3-avatar" title={user?.name || ''} aria-hidden="true">{initials}</div>
                        </div>
                    </header>

                    <div className="f3-pagehead">
                        <div className="f3-pagehead-text">
                            <h1>{heading}</h1>
                            {summary && <p className="f3-pagehead-sum">{summary}</p>}
                            {sync && <p className="f3-sync">{sync}</p>}
                        </div>
                        {/* One primary action, shown once. On mobile it moves to the sticky bar. */}
                        {action && <div className="f3-hide-sm">{action}</div>}
                    </div>

                    <main className="f3-page">{children}</main>
                </div>
            </div>

            {stickyAction && <div className="f3-stickybar">{stickyAction}</div>}

            <nav className="f3-bottomnav" aria-label="Main areas">
                {MOBILE.map((m) => (
                    <Link key={m.key} href={m.href} aria-current={area === m.key ? 'page' : undefined}>
                        <span className="f3-navicon" aria-hidden="true">{m.icon}</span>
                        {m.label}
                        {counts[m.key] > 0 && <span className="f3-navcount">{counts[m.key]}</span>}
                    </Link>
                ))}
            </nav>
        </>
    );
}
