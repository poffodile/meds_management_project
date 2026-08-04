/**
 * frontend4 component kit.
 *
 * Small, unopinionated pieces every frontend4 screen is assembled from. No
 * component library — that is what keeps frontend4's styling absolutely
 * separate from the other three front ends.
 *
 * TWO RULES THIS FILE EXISTS TO ENFORCE
 *
 * 1. Status is never carried by colour alone. Every tinted thing here also
 *    renders a word. A carer with colour-blindness, or a phone held in
 *    sunlight, must read the same information as everyone else.
 * 2. No setting is baked in. Nothing here says "resident", "room" or "care
 *    home". Nouns come from configuration with a neutral default, because the
 *    same screens serve a children's home, supported living, a domiciliary
 *    service and someone's own flat.
 *    See docs/care-one-os/FRONTEND4/FRONTEND4-DESIGN.md section 0.
 */

import React from 'react';

/* ── Terminology ──────────────────────────────────────────────────────────── */

/**
 * Neutral defaults. An organisation overrides these per service mode; the
 * spec allows renaming Person, Site and Team but not removing them.
 *
 * "Person" is the default because it is the wording that needs overriding
 * least often — it reads correctly in every mode, where "Resident" does not.
 */
export const DEFAULT_TERMS = {
    person: 'person',
    people: 'people',
    place: 'home',
    team: 'team',
};

/** Look a noun up, falling back to the neutral default rather than blank. */
export function term(terms, key) {
    return (terms && terms[key]) || DEFAULT_TERMS[key] || key;
}

/** Same, capitalised for the start of a sentence or a heading. */
export function Term({ terms, name }) {
    const word = term(terms, name);
    return <>{word.charAt(0).toUpperCase() + word.slice(1)}</>;
}

/* ── Status ───────────────────────────────────────────────────────────────── */

/**
 * The ten statuses from the visual specification, and the words for them.
 *
 * One place, so no two screens can describe the same outcome differently.
 * The key is what the stylesheet colours by; the label is what a person reads.
 */
export const STATUSES = {
    given:    'Given',
    due:      'Due now',
    upcoming: 'Upcoming',
    late:     'Late',
    overdue:  'Overdue',
    refused:  'Refused',
    omitted:  'Not available',
    witness:  'Witness needed',
    info:     'Information',
    offline:  'Offline',
};

/**
 * Outcome codes as the database stores them, mapped to a status.
 *
 * The MAR stores single letters. Nothing on screen should ever show the bare
 * code — the specification is explicit that users must be able to see the full
 * meaning, never an unexplained code.
 */
export const OUTCOME_CODES = {
    A: { status: 'given',   label: 'Given' },
    S: { status: 'omitted', label: 'Asleep' },
    R: { status: 'refused', label: 'Refused' },
    W: { status: 'omitted', label: 'Withheld' },
    N: { status: 'omitted', label: 'Not available' },
    O: { status: 'omitted', label: 'Other outcome' },
};

/**
 * A status: a small dot, a word, and restrained colour.
 *
 *     ● Overdue · 22 minutes
 *
 * This is the specification's rule made structural. There is deliberately NO
 * way to render this component as colour alone — the word is not optional, and
 * `variant="badge"` still draws a thin outline rather than a filled block.
 *
 * `note` is the quiet detail after the middle dot: how late, who recorded it.
 */
export function Status({ status = 'upcoming', label, note, variant, fill }) {
    const text = label || STATUSES[status] || status;
    if (!text) return null;

    return (
        <span className="f4-status" data-status={status} data-variant={variant} data-fill={fill}>
            <span className="f4-status-dot" aria-hidden="true" />
            {text}
            {note ? <span className="f4-status-note">· {note}</span> : null}
        </span>
    );
}

/** Render a stored MAR code as its full meaning, never as the bare letter. */
export function OutcomeCode({ code, note }) {
    const known = OUTCOME_CODES[code];
    if (!known) return null;

    return <Status status={known.status} label={known.label} note={note} />;
}

/* ── Stat ─────────────────────────────────────────────────────────────────── */

/**
 * One number and what it means.
 *
 * `status` gives the stat a thin left edge and the FAINT tint — never a filled
 * card. The specification is explicit: keep the status visible without turning
 * the page red. Use it sparingly; if every stat is tinted, the tint has stopped
 * meaning anything.
 */
export function Stat({ value, label, note, status }) {
    return (
        <div className="f4-stat" data-status={status}>
            <span className="f4-stat-value">{value}</span>
            <span className="f4-stat-label">{label}</span>
            {note ? <span className="f4-stat-note">{note}</span> : null}
        </div>
    );
}

/** How far through the day's scheduled doses. The percentage is also written. */
export function Progress({ percent, label }) {
    const pct = Math.max(0, Math.min(100, Number(percent) || 0));
    return (
        <div>
            <div
                className="f4-progress"
                role="progressbar"
                aria-valuenow={pct}
                aria-valuemin={0}
                aria-valuemax={100}
                aria-label={label || 'Progress'}
            >
                <div className="f4-progress-fill" style={{ width: `${pct}%` }} />
            </div>
        </div>
    );
}

/* ── Identity ─────────────────────────────────────────────────────────────── */

function initials(name) {
    const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '?';
    return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
}

/**
 * Who this is.
 *
 * `meta` is free text supplied by the caller — a room, a visit time, a unit,
 * whatever that service actually uses. This component does not decide, because
 * assuming a person has a room number is exactly the kind of care-home default
 * frontend4 is not allowed to bake in.
 *
 * On an administration screen this is never abbreviated: size="lg", and it
 * stays on screen while the outcome is recorded.
 */
export function Person({ name, photo, meta, size = 'md' }) {
    return (
        <span className="f4-person" data-size={size}>
            {photo ? (
                <img className="f4-person-photo" src={photo} alt="" />
            ) : (
                <span className="f4-person-initials" aria-hidden="true">{initials(name)}</span>
            )}
            <span className="f4-person-text">
                <span className="f4-person-name">{name}</span>
                {meta ? <span className="f4-person-meta">{meta}</span> : null}
            </span>
        </span>
    );
}

/**
 * Allergies and alerts.
 *
 * Rendered as a strip rather than a badge so it cannot be mistaken for a
 * status, and given role="note" so a screen reader announces it as standing
 * information rather than as a passing alert.
 *
 * Renders nothing when there is nothing to say — an empty red strip trains
 * people to ignore red strips.
 */
export function SafetyStrip({ allergies = [], risks = [], tone = 'risk' }) {
    const items = [...(allergies || []), ...(risks || [])].filter(Boolean);
    if (!items.length) return null;

    return (
        <span className="f4-safety" data-tone={tone} role="note">
            <span className="f4-safety-label">
                {allergies.length ? 'Allergy' : 'Alert'}
            </span>
            {items.join(' · ')}
        </span>
    );
}

/* ── Rows ─────────────────────────────────────────────────────────────────── */

/**
 * The repeated unit of the product: a thing, a detail about it, and its state.
 *
 * Renders as a link when given `href`, a button when given `onClick`, and plain
 * markup otherwise — so a row is never a clickable-looking thing that does
 * nothing, and never an interactive thing a keyboard cannot reach.
 */
export function Row({ href, onClick, status, done, title, sub, time, end, children }) {
    const inner = children || (
        <>
            <span className="f4-row-main">
                <span className="f4-row-title">{title}</span>
                {sub ? <span className="f4-row-sub">{sub}</span> : null}
            </span>
            {time || end ? (
                <span className="f4-row-end">
                    {time ? <span className="f4-row-time">{time}</span> : null}
                    {end}
                </span>
            ) : null}
        </>
    );

    const props = {
        className: 'f4-row',
        'data-status': status,
        'data-done': done ? 'true' : undefined,
    };

    if (href) return <a {...props} href={href}>{inner}</a>;
    if (onClick) return <button type="button" {...props} onClick={onClick}>{inner}</button>;
    return <div {...props}>{inner}</div>;
}

/**
 * A medicine, with the reading order the specification sets out.
 *
 *   Amlodipine          ← strongest
 *   5 mg tablet
 *   Dose: One tablet · Route: Oral · Due: 09:00
 *   Take with water. Do not crush.
 *
 * The medicine NAME is the strongest thing here. Strength, dose, route and time
 * stay clearly visible but must not compete equally with it — a screen where
 * they all shout the same volume is a screen nobody reads carefully.
 *
 * `instruction` is the "how to give it" directive. `indication` is why it is
 * prescribed. They are different things and are deliberately not merged — an
 * indication rendered as a directive is a real hazard, not a formatting choice.
 */
export function Medicine({ name, strength, form, dose, route, due, instruction, indication, children }) {
    const detail = [
        dose ? ['Dose', dose] : null,
        route ? ['Route', route] : null,
        due ? ['Due', due] : null,
    ].filter(Boolean);

    return (
        <div>
            <span className="f4-med-name">{name}</span>
            {strength || form ? (
                <span className="f4-med-strength">{[strength, form].filter(Boolean).join(' · ')}</span>
            ) : null}

            {detail.length ? (
                <p className="f4-med-detail">
                    {detail.map(([k, v], i) => (
                        <React.Fragment key={k}>
                            {i > 0 ? ' · ' : ''}
                            <b>{k}:</b> {v}
                        </React.Fragment>
                    ))}
                </p>
            ) : null}

            {instruction ? <p className="f4-med-instruction">{instruction}</p> : null}
            {indication ? (
                <p className="f4-row-sub" style={{ marginTop: 4 }}>Prescribed for {indication}</p>
            ) : null}
            {children}
        </div>
    );
}

/* ── Forms ────────────────────────────────────────────────────────────────── */

/**
 * A form field.
 *
 * The label is a real label, above the control, always. Placeholder text is
 * never the only label — that is a specification rule and an accessibility one:
 * a placeholder disappears the moment someone starts typing, which is exactly
 * when a distracted person most needs to know what they are filling in.
 *
 * Errors render immediately below the field and are tied to it by `id`, so a
 * screen reader announces them rather than leaving a red border to speak for
 * itself.
 */
export function Field({ id, label, hint, error, required, children }) {
    const describedBy = [hint ? `${id}-hint` : null, error ? `${id}-error` : null]
        .filter(Boolean)
        .join(' ') || undefined;

    return (
        <div className="f4-field" data-invalid={error ? 'true' : undefined}>
            <label className="f4-field-label" htmlFor={id}>
                {label}
                {required ? <span aria-hidden="true"> *</span> : null}
                {required ? <span className="f4-sr"> (required)</span> : null}
            </label>
            {hint ? <span className="f4-field-hint" id={`${id}-hint`}>{hint}</span> : null}

            {typeof children === 'function'
                ? children({ id, 'aria-describedby': describedBy, 'aria-invalid': error ? true : undefined })
                : children}

            {error ? (
                <span className="f4-field-error" id={`${id}-error`} role="alert">{error}</span>
            ) : null}
        </div>
    );
}

/* ── Tables — the MAR, stock ledger, CD register, audit trail ─────────────── */

/**
 * A table that scrolls inside its own container.
 *
 * The wrapper is what stops a wide MAR grid pushing the whole page sideways.
 * `tabIndex` makes the scroll region reachable by keyboard, which is required
 * once a region scrolls.
 */
export function Table({ caption, children }) {
    return (
        <div className="f4-table-wrap" tabIndex={0} role="region" aria-label={caption}>
            <table className="f4-table">
                {caption ? <caption className="f4-sr">{caption}</caption> : null}
                {children}
            </table>
        </div>
    );
}

/** A card whose body is rows, so the rows meet the card edges. */
export function RowCard({ title, note, children, footer }) {
    return (
        <section className="f4-card" data-pad="none">
            {title ? (
                <header className="f4-card-head">
                    <h2>{title}</h2>
                    {note ? <span className="f4-card-head-note">{note}</span> : null}
                </header>
            ) : null}
            <div className="f4-rows">{children}</div>
            {footer}
        </section>
    );
}

/* ══ THE SIX STATES ══════════════════════════════════════════════════════════
   Section 6 of the design doc. Built with the first screen rather than
   retrofitted, because retrofitting is how states get skipped.
   ========================================================================= */

/**
 * Empty.
 *
 * Requires `body` as well as `title`, because "No results" on its own is the
 * failure this component exists to prevent: it does not say why the list is
 * empty, and it does not say what to do next.
 */
export function Empty({ title, body, action }) {
    return (
        <div className="f4-state">
            <span className="f4-state-title">{title}</span>
            <p className="f4-state-body">{body}</p>
            {action ? <div className="f4-actions">{action}</div> : null}
        </div>
    );
}

/**
 * Error. Plain-language cause, a retry, and a reference the office can quote
 * when they ring about it.
 */
export function ErrorState({ title = 'That did not load', body, onRetry, reference }) {
    return (
        <div className="f4-state" data-tone="risk" role="alert">
            <span className="f4-state-title">{title}</span>
            <p className="f4-state-body">{body}</p>
            {onRetry ? (
                <div className="f4-actions">
                    <button type="button" className="f4-btn" onClick={onRetry}>Try again</button>
                </div>
            ) : null}
            {reference ? <span className="f4-state-ref">Reference {reference}</span> : null}
        </div>
    );
}

/**
 * No permission.
 *
 * States the restriction without revealing what is behind it, and gives the
 * approved route — telling someone "ask your manager" is more use than a wall.
 */
export function NoPermission({ body = 'You do not have access to this.', route }) {
    return (
        <div className="f4-state">
            <span className="f4-state-title">Not available to you</span>
            <p className="f4-state-body">{body}</p>
            {route ? <span className="f4-state-ref">{route}</span> : null}
        </div>
    );
}

/**
 * Loading.
 *
 * Takes the geometry of the thing it stands in for, so nothing jumps when the
 * real content arrives. `aria-hidden` because a skeleton is decoration; the
 * live region announcing "loading" belongs to the page, not to each block.
 */
export function Skeleton({ height = 16, width = '100%', radius }) {
    return (
        <span
            className="f4-skeleton"
            aria-hidden="true"
            style={{
                display: 'block',
                height: typeof height === 'number' ? `${height}px` : height,
                width: typeof width === 'number' ? `${width}px` : width,
                borderRadius: radius,
            }}
        />
    );
}

/** Several skeleton rows, matching the shape of a RowCard's contents. */
export function SkeletonRows({ rows = 4 }) {
    return (
        <div className="f4-rows" aria-hidden="true">
            {Array.from({ length: rows }, (_, i) => (
                <div className="f4-row" key={i}>
                    <span className="f4-person-initials" />
                    <span className="f4-row-main">
                        <Skeleton height={14} width="40%" />
                        <span style={{ display: 'block', height: 6 }} />
                        <Skeleton height={11} width="25%" />
                    </span>
                </div>
            ))}
        </div>
    );
}

/**
 * Conflict.
 *
 * Both versions, who changed what and when, and no default choice — resolution
 * has to be a deliberate act. Never auto-merge a clinical record.
 */
export function Conflict({ mine, theirs, onKeepMine, onKeepTheirs }) {
    return (
        <div className="f4-state" data-tone="risk" role="alert" style={{ textAlign: 'left' }}>
            <span className="f4-state-title">This was changed by someone else</span>
            <p className="f4-state-body" style={{ margin: 0 }}>
                Two versions of this record exist. Choose which one is correct — nothing is
                saved until you do.
            </p>
            <div className="f4-stack" style={{ marginTop: 16 }}>
                <div className="f4-card">
                    <h3>Your version</h3>
                    <p>{mine?.summary}</p>
                    <p className="f4-row-sub">{mine?.by} · {mine?.at}</p>
                    <div className="f4-actions">
                        <button type="button" className="f4-btn" data-size="sm" onClick={onKeepMine}>
                            Keep mine
                        </button>
                    </div>
                </div>
                <div className="f4-card">
                    <h3>Their version</h3>
                    <p>{theirs?.summary}</p>
                    <p className="f4-row-sub">{theirs?.by} · {theirs?.at}</p>
                    <div className="f4-actions">
                        <button type="button" className="f4-btn" data-size="sm" onClick={onKeepTheirs}>
                            Keep theirs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
