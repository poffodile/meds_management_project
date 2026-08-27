import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import BoardSection from '@record7/components/BoardSection.jsx';
import HandoverPanel from '@record7/components/HandoverPanel.jsx';
import AttentionRow from '@record7/components/AttentionRow.jsx';
import RoundCard from '@record7/components/RoundCard.jsx';
import PersonDueCard from '@record7/components/PersonDueCard.jsx';
import TaskRow from '@record7/components/TaskRow.jsx';
import CompletedRow from '@record7/components/CompletedRow.jsx';
import StateBlock from '@record7/components/StateBlock.jsx';
import Notice from '@record7/components/Notice.jsx';
import Button from '@record7/components/Button.jsx';
import Icon from '@record7/components/Icon.jsx';

/**
 * Section 1.1 — Today, for a support worker, in one house.
 *
 * SIX BANDS IN A FIXED ORDER, and the order is the design:
 *
 *   1  Shift handover        what the last shift left you
 *   2  Needs attention       what is wrong right now
 *   3  Shift overview        where the day is
 *   4  People due            who is waiting, grouped by person
 *   5  My tasks and PRN      what still needs an answer
 *   6  Recently completed    what is already done, so nobody repeats it
 *
 * That is the order the questions arrive in when somebody walks onto a shift,
 * and it is the DOM order at every width. A wide screen may lay a band's
 * INSIDES out more intelligently, but no band is ever placed beside another —
 * two columns of bands would silently change which one is read first.
 *
 * The numbering, so it does not drift: this is 1.1. Manager Today is 1.2 and
 * the Medication Round is Section 2. Neither is built.
 */
export default function Today({
    name,
    house,
    greeting,
    today,
    handover,
    attention,
    overview,
    round,
    peopleDue,
    nextRound,
    tasks,
    completed,
    can,
    urls,
}) {
    // Collapsed by default: it is a record of what has happened, and the
    // question this page answers is what has not.
    const [showCompleted, setShowCompleted] = useState(false);

    // Two destinations, because two pages exist. Nothing is padded out with
    // links that go nowhere — the rail is sized by what is actually in it.
    const nav = [
        { key: 'today', label: 'Today', href: '/record7', icon: 'house', current: true },
        { key: 'people', label: 'People', href: '/record7', icon: 'person', available: can.viewPeople },
    ];

    const started = round?.state === 'in_progress';
    const roundOpen = round && round.slot !== null;

    return (
        <AppShell urls={urls} nav={nav}>
            <div className="r7-work">

                {/* ── The anchor: which shift, which day, which house, and the
                       one thing there is to do about it ──────────────────── */}
                <header className="r7-anchor">
                    <p className="r7-anchor__meta">
                        {roundOpen ? (
                            <span className="r7-anchor__shift">
                                <Icon name="clock" className="r7-icon r7-icon--small" />
                                {round.slot} round
                            </span>
                        ) : null}
                        <span>{today}</span>
                        <span className="r7-anchor__house">{house}</span>
                    </p>

                    <div className="r7-anchor__title">
                        <h1 className="r7-anchor__greeting">{greeting}, {name}</h1>
                        <p className="r7-anchor__lede">
                            {roundOpen
                                ? `${round.remaining} of ${round.total} still to record in this round.`
                                : 'Every dose planned for today has been recorded.'}
                        </p>
                    </div>

                    {roundOpen && can.record ? (
                        <div className="r7-anchor__action">
                            <Button variant="primary" arrow onClick={() => router.post(urls.startRound)}>
                                {started ? 'Resume round' : 'Start round'}
                            </Button>
                            <span className="r7-anchor__hint">
                                {started ? `Started ${round.startedAt}` : `Due ${round.dueAt}`}
                            </span>
                        </div>
                    ) : null}
                </header>

                {/* ── 1. Shift handover ─────────────────────────────────── */}
                <BoardSection
                    title="Shift handover"
                    note={handover?.urgentCount ? `${handover.urgentCount} urgent` : null}
                >
                    {handover ? (
                        <HandoverPanel handover={handover} readUrl={urls.readHandover} />
                    ) : (
                        <StateBlock state="empty" title="No handover has been written yet">
                            When the last shift writes one it will appear here, urgent notes first.
                        </StateBlock>
                    )}
                </BoardSection>

                {/* ── 2. Needs attention ────────────────────────────────── */}
                <BoardSection title="Needs attention" count={attention.length || null}>
                    {attention.length ? (
                        <ul className="r7-attentions">
                            {attention.map((item, index) => (
                                <AttentionRow item={item} key={`${item.kind}-${item.client}-${index}`} />
                            ))}
                        </ul>
                    ) : (
                        <StateBlock state="empty" title="Nothing needs chasing">
                            Nothing is late, nothing is unanswered and nothing is out of stock.
                        </StateBlock>
                    )}
                </BoardSection>

                {/* ── 3. Shift overview ─────────────────────────────────── */}
                <BoardSection title="Shift overview" note={house}>
                    <RoundCard
                        round={round}
                        overview={overview}
                        next={nextRound}
                        canRecord={can.record}
                        refusal={can.recordRefusal}
                    />
                </BoardSection>

                {/* ── 4. People due ─────────────────────────────────────── */}
                <BoardSection
                    title="People due"
                    count={peopleDue.length || null}
                    note={peopleDue.length ? 'Late, or in this round' : null}
                >
                    {peopleDue.length ? (
                        <ul className="r7-dues">
                            {peopleDue.map((person) => (
                                <PersonDueCard person={person} key={person.clientId} />
                            ))}
                        </ul>
                    ) : (
                        <StateBlock state="empty" title="Nobody is waiting right now">
                            Nothing is late and this round is finished.
                        </StateBlock>
                    )}

                </BoardSection>

                {/* ── 5. My tasks and PRN follow-ups ────────────────────── */}
                <BoardSection title="My tasks and PRN follow-ups" count={tasks.length || null}>
                    {tasks.length ? (
                        <ul className="r7-tasks">
                            {tasks.map((task) => (
                                <TaskRow task={task} key={task.id} />
                            ))}
                        </ul>
                    ) : (
                        <StateBlock state="empty" title="Nothing to follow up">
                            Every as-required medicine given today has had its answer recorded.
                        </StateBlock>
                    )}
                </BoardSection>

                {/* ── 6. Recently completed ─────────────────────────────── */}
                <BoardSection title="Recently completed" quiet>
                    <button
                        type="button"
                        className="r7-disclose"
                        onClick={() => setShowCompleted((open) => !open)}
                        aria-expanded={showCompleted}
                        aria-controls="r7-completed"
                    >
                        <span>
                            {completed.count} recorded in the last 12 hours
                            {completed.notTaken
                                ? `, ${completed.notTaken} not taken`
                                : ''}
                        </span>
                        <span className="r7-disclose__more">{showCompleted ? 'Hide' : 'Show'}</span>
                    </button>

                    {showCompleted ? (
                        <ul className="r7-dones" id="r7-completed">
                            {completed.entries.map((entry) => (
                                <CompletedRow entry={entry} key={entry.id} />
                            ))}
                        </ul>
                    ) : null}
                </BoardSection>

                {!can.record ? (
                    <Notice tone="info" title="You are signed in with review access">
                        You can see everything on this screen, but you cannot record a dose in
                        {' '}{house}.
                    </Notice>
                ) : null}
            </div>
        </AppShell>
    );
}
