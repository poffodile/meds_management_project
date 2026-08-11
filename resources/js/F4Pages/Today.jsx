/**
 * frontend4 — Today.
 *
 * The frontline dashboard. It answers three questions in this order, because
 * that is the order they matter in during a shift:
 *
 *   1. Where is the round up to?      → the stat row
 *   2. What needs a decision?         → Needs attention
 *   3. Who do I go to next?           → This round
 *
 * Everything here is read-only. Nothing on this page records a dose.
 *
 * Design reasoning: docs/care-one-os/FRONTEND4/FRONTEND4-DESIGN.md
 */

import React from 'react';
import { Head } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import {
    Empty,
    Person,
    Progress,
    Row,
    RowCard,
    SafetyStrip,
    Stat,
    Status,
    term,
} from '@frontend4/components/F4Atoms';

/**
 * The controller's `tone` vocabulary, mapped onto the ten statuses.
 *
 * The attention list groups problems by kind (supply, outcome, overdue) and
 * gives each a tone. Here that becomes a real status word so the row reads
 * "Out of stock" rather than relying on a colour to say it.
 */
const TONE_STATUS = { risk: 'overdue', caution: 'late', good: 'given', info: 'due' };

/** "3 people" / "1 person" — the noun comes from configuration, not from here. */
function count(n, terms) {
    return `${n} ${n === 1 ? term(terms, 'person') : term(terms, 'people')}`;
}

/**
 * The one-line state summary in the page header.
 *
 * Written as a sentence rather than a row of numbers, because the header is
 * read at a glance and "3 overdue" out of context does not say overdue what.
 */
function headerSummary(summary, round, terms) {
    if (summary.overdue > 0) {
        return `${summary.overdue} overdue · ${summary.due} due now · ${count(summary.people, terms)} waiting`;
    }
    if (summary.due > 0) {
        return `${summary.due} due now across ${count(summary.people, terms)}`;
    }
    return `Nothing outstanding in the ${round.label.toLowerCase()} round`;
}

/** What a person's remaining work amounts to, in words. */
function personState(p) {
    if (p.overdue > 0) return { status: 'overdue', sub: `${p.overdue} overdue` };
    if (p.due > 0) return { status: 'due', sub: `${p.due} due now` };
    if (p.later > 0) return { status: 'upcoming', sub: `${p.later} later` };
    return { status: 'given', sub: `${p.done} recorded` };
}

export default function Today({
    can,
    accessContext,
    roleLabel,
    terms,
    place,
    date,
    now,
    greeting,
    firstName,
    user,
    round,
    summary,
    dueNow,
    attention,
    attentionCap,
    upcoming,
}) {
    const shown = attention.slice(0, attentionCap);
    const hidden = attention.length - shown.length;

    // People still to see, then the finished ones — kept, not hidden, because
    // watching the round empty out is how you know where you are.
    const outstanding = dueNow.filter((p) => p.state !== 'done');
    const finished = dueNow.filter((p) => p.state === 'done');

    return (
        <F4Shell
            area="today"
            title="Today"
            summary={headerSummary(summary, round, terms)}
            place={place}
            placeSub={round.window ? `${round.label} · ${round.window}` : round.label}
            user={user}
            roleLabel={roleLabel}
            can={can}
            accessContext={accessContext}
            lastSync={now}
        >
            <Head title="Today" />

            <div className="f4-stack">
                {/* ── Where the day is up to ──────────────────────────────── */}
                <div className="f4-stats">
                    <Stat
                        value={summary.overdue}
                        label="Overdue"
                        note={summary.overdue > 0 ? 'Needs recording now' : 'Nothing running late'}
                        status={summary.overdue > 0 ? 'overdue' : undefined}
                    />
                    <Stat
                        value={summary.due}
                        label="Due now"
                        note={`${round.label} round`}
                        status={summary.due > 0 ? 'due' : undefined}
                    />
                    <Stat
                        value={summary.people}
                        label={`${term(terms, 'people').charAt(0).toUpperCase() + term(terms, 'people').slice(1)} waiting`}
                        note="In this round"
                    />
                    <Stat
                        value={`${summary.percent}%`}
                        label="Recorded today"
                        note={`${summary.completedToday} of ${summary.scheduledToday} scheduled doses`}
                    />
                </div>

                <section className="f4-card">
                    <h2>
                        {greeting}{firstName ? `, ${firstName}` : ''}
                    </h2>
                    <p style={{ marginTop: 4, marginBottom: 16 }}>
                        {place} · {date} · {round.label} round
                        {round.window ? ` (${round.window})` : ''}
                    </p>
                    <Progress
                        percent={summary.percent}
                        label={`${summary.completedToday} of ${summary.scheduledToday} scheduled doses recorded today`}
                    />
                    <p className="f4-row-sub" style={{ marginTop: 8 }}>
                        {summary.completedToday} of {summary.scheduledToday} scheduled doses recorded
                        {' · '}
                        {summary.scheduledToday - summary.completedToday} still to record today
                    </p>
                </section>

                <div className="f4-cols">
                    {/* ── What needs a decision ───────────────────────────── */}
                    <RowCard
                        title="Needs attention"
                        note={attention.length ? `${attention.length} to resolve` : undefined}
                    >
                        {shown.length === 0 ? (
                            <Empty
                                title="Nothing outstanding"
                                body={
                                    'No refusals, supply problems or overdue doses need a decision right now. ' +
                                    'Anything recorded as not given will appear here.'
                                }
                            />
                        ) : (
                            <>
                                {shown.map((item, i) => (
                                    <Row
                                        key={`${item.kind}-${item.label}-${item.name}-${i}`}
                                        status={TONE_STATUS[item.tone]}
                                        title={item.name}
                                        sub={
                                            [
                                                item.detail,
                                                item.note,
                                                item.count > 1 ? `${item.count} doses` : null,
                                            ]
                                                .filter(Boolean)
                                                .join(' · ')
                                        }
                                        time={item.at || undefined}
                                        end={<Status status={TONE_STATUS[item.tone]} label={item.label} />}
                                    />
                                ))}
                                {hidden > 0 ? (
                                    <Row
                                        title={`${hidden} more to resolve`}
                                        sub="Shown here once the ones above are dealt with"
                                    />
                                ) : null}
                            </>
                        )}
                    </RowCard>

                    {/* ── Later in this round ─────────────────────────────── */}
                    <div className="f4-stack">
                        <RowCard title="Later in this round">
                            {upcoming.length === 0 ? (
                                <Empty
                                    title="Nothing else scheduled"
                                    body={`No further doses are due in the ${round.label.toLowerCase()} round.`}
                                />
                            ) : (
                                upcoming.map((u) => (
                                    <Row
                                        key={u.slot}
                                        title={u.slot}
                                        sub={`${u.doses} ${u.doses === 1 ? 'dose' : 'doses'} · ${count(u.people, terms)}`}
                                    />
                                ))
                            )}
                        </RowCard>
                    </div>
                </div>

                {/* ── Who to go to next ───────────────────────────────────── */}
                <RowCard
                    title={`${round.label} round`}
                    note={
                        outstanding.length
                            ? `${count(outstanding.length, terms)} still to see`
                            : 'All recorded'
                    }
                >
                    {dueNow.length === 0 ? (
                        <Empty
                            title="Nobody is scheduled in this round"
                            body={
                                `No ${term(terms, 'person')} has a medicine scheduled in the ` +
                                `${round.label.toLowerCase()} round today. Check the date and the ` +
                                `${term(terms, 'place')} in the bar above if that looks wrong.`
                            }
                        />
                    ) : (
                        [...outstanding, ...finished].map((p) => {
                            const state = personState(p);

                            return (
                                <Row key={p.client_id} status={state.status} done={p.state === 'done'}>
                                    <span className="f4-row-main">
                                        <Person name={p.name} photo={p.photo} meta={p.meta} />
                                    </span>

                                    {/* Allergies travel with the person everywhere they appear. */}
                                    <SafetyStrip allergies={p.allergies} risks={p.risks} />

                                    <span className="f4-row-end">
                                        {p.nextSlot ? <span className="f4-row-time">{p.nextSlot}</span> : null}
                                        <Status status={state.status} note={state.sub} />
                                    </span>
                                </Row>
                            );
                        })
                    )}
                </RowCard>
            </div>
        </F4Shell>
    );
}
