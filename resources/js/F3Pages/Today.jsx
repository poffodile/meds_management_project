import React from 'react';
import { Link } from '@inertiajs/react';
import F3Shell from '@frontend3/components/F3Shell';
import { Badge, Card, CardHead, Stat, Person, SafetyStrip, Empty, Progress, Note } from '@frontend3/components/F3Atoms';

/**
 * Today — the frontline dashboard (spec §5).
 *
 * Answers three questions and nothing else:
 *   what is due · what is at risk · what do I do next
 *
 * UNCLUTTERED: three stats, not eight. One primary action. The due list is the
 * page — everything else is secondary and sits beside it on desktop, below it
 * on mobile. Anything that would be a fourth column has been left out on
 * purpose, not forgotten.
 */

function DuePerson({ p, href }) {
    const cls = [
        'f3-personrow',
        p.state === 'overdue' && 'f3-personrow--alert',
        p.state === 'due' && 'f3-personrow--due',
        p.state === 'done' && 'f3-personrow--done',
    ].filter(Boolean).join(' ');

    return (
        <Link className={cls} href={href}>
            <Person
                name={p.name}
                photo={p.photo}
                meta={[
                    p.room ? `Room ${p.room}` : null,
                    p.outstanding > 0
                        ? `${p.outstanding} ${p.outstanding === 1 ? 'medicine' : 'medicines'} to give`
                        : `${p.done} recorded`,
                ].filter(Boolean).join(' · ')}
            />

            <div className="f3-spacer f3-row" style={{ justifyContent: 'flex-end', gap: 'var(--f3-s2)' }}>
                {p.allergies.length > 0 && <Badge tone="risk">Allergy</Badge>}
                {p.state === 'overdue' && <Badge tone="risk">{p.overdue} overdue</Badge>}
                {p.state === 'due' && <Badge tone="neutral">{p.due} due</Badge>}
                {p.state === 'done' && <Badge tone="good">Done</Badge>}
            </div>
        </Link>
    );
}

export default function Today({
    auth, home, date, now, greeting, firstName, round,
    summary, dueNow, attention, attentionCap = 8, upcoming, roundUrl,
}) {
    const outstanding = summary.due + summary.overdue;
    const people = dueNow.filter((p) => p.state !== 'done');
    const finished = dueNow.filter((p) => p.state === 'done');

    const headline = outstanding === 0
        ? `Nothing outstanding in the ${round.label.toLowerCase()} round.`
        : `${outstanding} ${outstanding === 1 ? 'medicine' : 'medicines'} to give across ${summary.people} ${summary.people === 1 ? 'person' : 'people'}${summary.overdue > 0 ? ` — ${summary.overdue} overdue` : ''}.`;

    const primary = (
        <Link className="f3-btn f3-btn--primary" href={roundUrl}>
            {outstanding > 0 ? `Start ${round.label.toLowerCase()} round` : 'Open medication round'}
        </Link>
    );

    return (
        <F3Shell
            title="Today"
            area="today"
            user={auth?.user}
            heading={`${greeting}${firstName ? `, ${firstName}` : ''}`}
            summary={headline}
            sync={`${round.label} round${round.window ? ` · ${round.window}` : ''} · updated ${now}`}
            context={[
                { label: home, strong: true },
                { label: new Date(date + 'T00:00:00').toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' }), quiet: true },
            ]}
            counts={{ operations: attention.length }}
            action={primary}
            stickyAction={primary}
        >

            {/* Three numbers. Each opens what is behind it. */}
            <div className="f3-grid f3-grid--3">
                <Stat
                    as="div"
                    label="Due this round"
                    value={outstanding}
                    tone={summary.overdue > 0 ? 'alert' : undefined}
                    meta={summary.overdue > 0
                        ? <><b>{summary.overdue} overdue</b> · {round.label.toLowerCase()} window</>
                        : <>{round.label} window{round.window ? ` · ${round.window}` : ''}</>}
                />
                <Stat
                    as="div"
                    label="Needs attention"
                    value={attention.length}
                    tone={attention.length > 0 ? 'alert' : 'quiet'}
                    meta={attention.length > 0 ? 'Not given, supply, or over 1h late' : 'Nothing outstanding today'}
                />
                <Stat
                    as="div"
                    label="Recorded today"
                    value={summary.completedToday}
                    tone="quiet"
                    meta={<>of <b>{summary.scheduledToday}</b> scheduled · all rounds</>}
                />
            </div>

            <div className="f3-grid f3-grid--main">

                {/* ---------------- The page: who needs medicines now ---------------- */}
                <Card>
                    <CardHead
                        title={`${round.label} round`}
                        sub={round.window ? `Window ${round.window} · sorted by urgency, not alphabetically` : 'Sorted by urgency'}
                    >
                        <Badge tone={summary.overdue > 0 ? 'risk' : outstanding > 0 ? 'neutral' : 'good'}>
                            {outstanding > 0 ? `${outstanding} to give` : 'Round complete'}
                        </Badge>
                    </CardHead>

                    {people.length === 0 && finished.length === 0 && (
                        <Empty title="No medicines scheduled in this round">
                            Nothing is due between now and the end of the {round.label.toLowerCase()} window.
                            Upcoming rounds are listed on the right.
                        </Empty>
                    )}

                    {people.length === 0 && finished.length > 0 && (
                        <Empty title={`${round.label} round complete`}>
                            All {finished.length} {finished.length === 1 ? 'person' : 'people'} in this round have been recorded.
                        </Empty>
                    )}

                    {people.length > 0 && (
                        <div className="f3-stack f3-stack--sm">
                            {people.map((p) => (
                                <DuePerson key={p.client_id} p={p} href={roundUrl} />
                            ))}
                        </div>
                    )}

                    {finished.length > 0 && people.length > 0 && (
                        <>
                            <h4 style={{ margin: 'var(--f3-s5) 0 var(--f3-s3)' }}>
                                Recorded · {finished.length}
                            </h4>
                            <div className="f3-stack f3-stack--sm">
                                {finished.map((p) => (
                                    <DuePerson key={p.client_id} p={p} href={roundUrl} />
                                ))}
                            </div>
                        </>
                    )}
                </Card>

                {/* ---------------- Secondary: risk, then what's next ---------------- */}
                <div className="f3-stack">

                    <Card>
                        <CardHead
                            title="Needs attention"
                            sub={attention.length > 0 ? 'One entry per problem, not per dose' : undefined}
                        >
                            {attention.length > 0 && <Badge tone="risk">{attention.length}</Badge>}
                        </CardHead>

                        {attention.length === 0 ? (
                            <p className="f3-sm f3-mut">
                                Nothing today recorded as refused, withheld or unavailable, nothing more
                                than an hour past its window, and no medicine low on stock.
                            </p>
                        ) : (
                            <ul className="f3-list">
                                {attention.slice(0, attentionCap).map((a, i) => (
                                    <li key={i} className="f3-listitem" style={{ alignItems: 'flex-start' }}>
                                        <div className="f3-listbody">
                                            <div className="f3-row" style={{ gap: 'var(--f3-s2)' }}>
                                                <Badge tone={a.tone}>{a.label}</Badge>
                                                {/* One entry per problem — so say how many doses it covers. */}
                                                {a.count > 1 && (
                                                    <span className="f3-xs f3-mut">
                                                        {a.count} doses
                                                    </span>
                                                )}
                                                {a.at && <span className="f3-xs f3-mut f3-tabnum">from {a.at}</span>}
                                            </div>
                                            <div className="f3-person-name" style={{ marginTop: 6, fontSize: '.9rem' }}>
                                                {a.name}
                                            </div>
                                            <div className="f3-listmeta">
                                                {a.detail}{a.note ? ` · ${a.note}` : ''}
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}

                        {attention.length > attentionCap && (
                            <p className="f3-xs f3-mut" style={{ marginTop: 'var(--f3-s3)' }}>
                                Showing the {attentionCap} most urgent of {attention.length}.
                                Nothing is hidden — the rest are lower risk, not dismissed.
                            </p>
                        )}
                    </Card>

                    <Card flat>
                        <CardHead title="Later in this round" />
                        {upcoming.length === 0 ? (
                            <p className="f3-sm f3-mut">Nothing further scheduled in this window.</p>
                        ) : (
                            <ul className="f3-list">
                                {upcoming.map((u) => (
                                    <li key={u.slot} className="f3-listitem" style={{ padding: 'var(--f3-s3) 0' }}>
                                        <span className="f3-person-name f3-tabnum" style={{ fontSize: '.9rem' }}>{u.slot}</span>
                                        <span className="f3-spacer f3-sm f3-mut">
                                            {u.doses} {u.doses === 1 ? 'dose' : 'doses'} · {u.people} {u.people === 1 ? 'person' : 'people'}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Card>

                    <Card tint>
                        <CardHead title="Today so far" />
                        <Progress
                            value={summary.completedToday}
                            max={summary.scheduledToday}
                            label={`${summary.completedToday} of ${summary.scheduledToday} scheduled doses recorded`}
                        />
                        <p className="f3-sm f3-mut" style={{ marginTop: 'var(--f3-s3)' }}>
                            <b className="f3-tabnum">{summary.completedToday}</b> of{' '}
                            <b className="f3-tabnum">{summary.scheduledToday}</b> scheduled doses recorded
                            across all rounds today. Excludes when-required medicines, which have no
                            scheduled time.
                        </p>
                    </Card>

                </div>
            </div>

            <Note>
                The medication round still opens in the existing screen — the frontend3 round page is
                the next one to be built. Everything on this page is live data from your home.
            </Note>

        </F3Shell>
    );
}
