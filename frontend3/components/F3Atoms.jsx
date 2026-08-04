import React from 'react';

/**
 * frontend3 shared atoms.
 *
 * Small, boring, reused everywhere. The rule they all obey: status is carried
 * by a WORD and a SHAPE as well as a tint, never by colour alone (spec §17/§18).
 * The shape comes from the ::before content in frontend3/f3.css.
 */

/** Tone → the five muted status tints. Nothing outside this set. */
export const TONES = ['neutral', 'good', 'caution', 'risk', 'info', 'ghost'];

export function Badge({ tone = 'neutral', children }) {
    return <span className={`f3-badge f3-badge--${tone}`}>{children}</span>;
}

export function Chip({ quiet = false, strong = false, children }) {
    return (
        <span className={`f3-chip${quiet ? ' f3-chip--quiet' : ''}`}>
            {strong ? <b>{children}</b> : children}
        </span>
    );
}

export function Card({ tint = false, flat = false, className = '', children, ...rest }) {
    const cls = ['f3-card', tint && 'f3-card--tint', flat && 'f3-card--flat', className]
        .filter(Boolean).join(' ');
    return <section className={cls} {...rest}>{children}</section>;
}

/** A card header: title on the left, everything else pushed right. */
export function CardHead({ title, sub, children }) {
    return (
        <div className="f3-cardhead">
            <div>
                <h2>{title}</h2>
                {sub && <p className="f3-cardsub">{sub}</p>}
            </div>
            {children}
        </div>
    );
}

/**
 * A number that opens the records behind it.
 *
 * Spec §5: no "dashboard wallpaper". Every stat states its range and whether it
 * is informational or actionable — so `meta` is required, not optional.
 */
export function Stat({ label, value, meta, tone, onClick, as: As = 'button' }) {
    const cls = ['f3-stat', tone === 'alert' && 'f3-stat--alert', tone === 'quiet' && 'f3-stat--quiet']
        .filter(Boolean).join(' ');
    return (
        <As className={cls} onClick={onClick} type={As === 'button' ? 'button' : undefined}>
            <span className="f3-stat-label">{label}</span>
            <span className="f3-stat-value f3-tabnum">{value}</span>
            <span className="f3-stat-meta">{meta}</span>
        </As>
    );
}

/** Resident identity. Photo when there is one, initials when there isn't. */
export function Person({ name, photo, meta, large = false }) {
    const initials = (name || '')
        .split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]).join('').toUpperCase() || '·';
    return (
        <div className="f3-person">
            <div className={`f3-photo${large ? ' f3-photo--lg' : ''}`} aria-hidden="true">
                {photo ? <img src={photo} alt="" /> : initials}
            </div>
            <div style={{ minWidth: 0 }}>
                <div className="f3-person-name">{name}</div>
                {meta && <div className="f3-person-meta">{meta}</div>}
            </div>
        </div>
    );
}

/** Allergies and risks. Labelled, so it is never "the red bit". */
export function SafetyStrip({ allergies = [], risks = [] }) {
    if (!allergies.length && !risks.length) return null;
    const items = [
        ...allergies.map((a) => (typeof a === 'string' ? a : a?.label ?? '')),
        ...risks.map((r) => (typeof r === 'string' ? r : r?.label ?? '')),
    ].filter(Boolean);
    if (!items.length) return null;

    return (
        <div className="f3-safety">
            <span className="f3-safety-label">{allergies.length ? 'Allergies' : 'Risks'}</span>
            <span>{items.join(' · ')}</span>
        </div>
    );
}

export function Empty({ title, children, action }) {
    return (
        <div className="f3-empty">
            <div className="f3-empty-mark" aria-hidden="true">◇</div>
            <h3>{title}</h3>
            {children && <p>{children}</p>}
            {action && <div style={{ marginTop: 'var(--f3-s4)' }}>{action}</div>}
        </div>
    );
}

export function Progress({ value, max, label }) {
    const pct = max > 0 ? Math.round((value / max) * 100) : 0;
    return (
        <div
            className="f3-progress"
            role="progressbar"
            aria-valuenow={value}
            aria-valuemin={0}
            aria-valuemax={max}
            aria-label={label}
        >
            <span style={{ width: `${pct}%` }} />
        </div>
    );
}

export function Note({ children }) {
    return <p className="f3-note">{children}</p>;
}
