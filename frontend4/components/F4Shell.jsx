/**
 * frontend4 page shell.
 *
 * Context bar across the top, navigation down the left, content in the middle,
 * and on a phone a bottom bar instead of the left nav — not a hamburger. A
 * carer mid-round should not have to open a menu to get back to the round.
 *
 * Every class name is defined in frontend4/f4.css under .f4-root. No component
 * library, no global stylesheet. Design reasoning:
 * docs/care-one-os/FRONTEND4/FRONTEND4-DESIGN.md sections 2 and 3.
 */

import React from 'react';
import { Link } from '@inertiajs/react';
import * as Icon from '@frontend4/components/F4Icons';
import { navFor } from '@frontend4/roles';

/**
 * Icons for the navigation, keyed to frontend4/roles.js.
 *
 * The navigation itself lives in roles.js, not here, because what a person sees
 * is a permission question rather than a layout one. A support worker gets five
 * items; everything else is added by role. Nothing is deleted — it is gated.
 */
const NAV_ICONS = {
    today:    Icon.Today,
    round:    Icon.Round,
    missed:   Icon.Alert,
    clients:  Icon.People,
    handover: Icon.Message,
    cd:       Icon.Shield,
    stock:    Icon.Operations,
    reports:  Icon.Assurance,
    admin:    Icon.Settings,
};

/**
 * Phone bottom bar. Four fixed items — a bottom bar that changed length by role
 * would move the target a carer reaches for without looking.
 */
const BOTTOM = [
    { key: 'today',    label: 'Today',   Icon: Icon.Today,   href: '/frontend4' },
    { key: 'round',    label: 'Round',   Icon: Icon.Round,   href: null },
    { key: 'missed',   label: 'Missed',  Icon: Icon.Alert,   href: null },
    { key: 'handover', label: 'Handover',Icon: Icon.Message, href: null },
];

/** Initials for an avatar, without assuming how many parts a name has. */
function initials(name) {
    const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
}

function NavItem({ item, current }) {
    const isCurrent = item.key === current;
    const Glyph = item.Icon || NAV_ICONS[item.key] || Icon.Today;

    // No href yet — show it, but do not pretend it is a link.
    if (!item.href) {
        return (
            <span className="f4-nav-item" aria-disabled="true">
                <span className="f4-nav-icon"><Glyph /></span>
                {item.label}
            </span>
        );
    }

    return (
        <Link
            href={item.href}
            className="f4-nav-item"
            aria-current={isCurrent ? 'page' : undefined}
        >
            <span className="f4-nav-icon"><Glyph /></span>
            {item.label}
            {item.count ? (
                <span className="f4-nav-count" data-tone={item.countTone}>{item.count}</span>
            ) : null}
        </Link>
    );
}

export default function F4Shell({
    /** Which of the six areas is active — matches an AREAS key. */
    area = 'today',
    /** Page title, state summary and the single primary action. */
    title,
    summary,
    action = null,
    lastSync = null,
    /** Context bar: the place whose data is on screen, and the shift. */
    place = null,
    placeSub = null,
    user = null,
    /**
     * What this person may do, from F4Controller::roleProps().
     *
     * Decides which navigation items appear. Display only — the server refuses
     * anything they are not permitted to do, whether or not it was on screen.
     */
    can = [],
    roleLabel = null,
    /** Offline is a persistent banner, never a toast. */
    offline = false,
    queued = 0,
    /** Prose-shaped pages read better in a narrower column. */
    width = 'wide',
    children,
}) {
    return (
        <div className="f4-app">
            <header className="f4-context">
                <span className="f4-mark" aria-hidden="true">F4</span>

                <span className="f4-place">
                    <span className="f4-place-name">{place || 'Care One OS'}</span>
                    {placeSub ? <span className="f4-place-sub">{placeSub}</span> : null}
                </span>

                <span className="f4-context-end">
                    <span className="f4-conn" data-state={offline ? 'offline' : 'online'}>
                        {offline ? 'Offline' : 'Online'}
                    </span>
                    {user ? (
                        <span className="f4-who">
                            <span className="f4-avatar" aria-hidden="true">{initials(user)}</span>
                            {/* The role is shown, not just the name — someone
                                covering a shift needs to know which account
                                they are on before they record anything. */}
                            <span className="f4-who-name">
                                {user}{roleLabel ? ` · ${roleLabel}` : ''}
                            </span>
                        </span>
                    ) : null}
                </span>
            </header>

            {/*
              * Queued writes are stated as a number, not implied. "Working
              * offline" on its own leaves someone guessing whether what they
              * just recorded actually landed.
              */}
            {offline ? (
                <div className="f4-offline" role="status">
                    <span>Working offline — recorded doses are saved on this device and will sync.</span>
                    {queued > 0 ? (
                        <span className="f4-offline-count">
                            {queued} waiting to sync
                        </span>
                    ) : null}
                </div>
            ) : null}

            <div className="f4-body">
                <nav className="f4-nav" aria-label="Main">
                    {navFor(can).map((group, i) => (
                        <div className="f4-nav-group" key={group.group || `g${i}`}>
                            <span className="f4-nav-label">{group.group || 'Care'}</span>
                            {group.items.map((item) => (
                                <NavItem key={item.key} item={item} current={area} />
                            ))}
                        </div>
                    ))}
                </nav>

                <div className="f4-content">
                    <header className="f4-page-header">
                        <div className="f4-page-header-text">
                            <h1>{title}</h1>
                            {summary ? <p className="f4-page-summary">{summary}</p> : null}
                        </div>
                        {action || lastSync ? (
                            <div className="f4-page-header-action">
                                {action}
                                {lastSync ? (
                                    <span className="f4-sync">Last updated {lastSync}</span>
                                ) : null}
                            </div>
                        ) : null}
                    </header>

                    <main className="f4-main" data-width={width}>{children}</main>
                </div>
            </div>

            <nav className="f4-bottomnav" aria-label="Main">
                {BOTTOM.map((item) => {
                    const Glyph = item.Icon;

                    return item.href ? (
                        <Link
                            key={item.key}
                            href={item.href}
                            className="f4-bottomnav-item"
                            aria-current={item.key === area ? 'page' : undefined}
                        >
                            <Glyph size={20} />
                            {item.label}
                        </Link>
                    ) : (
                        <span
                            key={item.key}
                            className="f4-bottomnav-item"
                            aria-disabled="true"
                            style={{ opacity: 0.45 }}
                        >
                            <Glyph size={20} />
                            {item.label}
                        </span>
                    );
                })}
            </nav>
        </div>
    );
}
