import React from 'react';
import AppShell from '@record7/components/AppShell.jsx';

/**
 * Section 2.0 — the round workspace. FUNCTIONAL SCAFFOLD, NOT A DESIGN.
 *
 * Plain headings, a table and text. No new tokens, no new components, nothing
 * that could become the thing the real design gets compared against. The
 * permanent interface is being made separately.
 *
 * AND THERE IS NOTHING HERE TO PRESS.
 * No administer button, no outcome control, no PRN, no witness. Section 2.0
 * gets the right person safely into the right round and shows them who is
 * waiting; 2.1 shows what each person is due and 2.2 records it. A control that
 * looked as though it recorded something would be worse than no control at all.
 */
function Row({ label, value }) {
    return (
        <p style={{ margin: '.15rem 0' }}>
            <strong>{label}:</strong> {value ?? '—'}
        </p>
    );
}

export default function Round({
    house, round, participants, progress, queue, authority, stage, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: '/record7/round', icon: 'clock', current: true },
    ];

    return (
        <AppShell urls={urls} nav={nav}>
            <div style={{ maxWidth: '70rem', margin: '0 auto', padding: '0 0 4rem' }}>
                <p style={{ textTransform: 'uppercase', letterSpacing: '.08em', fontSize: '.75rem' }}>
                    {stage}
                </p>

                <h1>{round.slot} round — {house.name}</h1>

                {/* The blocked state. Authority is re-checked on every load, so
                    this appears the moment a competency lapses or access is
                    suspended — without the round being closed or lost. */}
                {authority.blocked ? (
                    <div role="alert" style={{ border: '2px solid currentColor', padding: '1rem', margin: '1rem 0' }}>
                        <h2>You cannot continue this round</h2>
                        <p>{authority.reason}</p>
                        {authority.competencyExpires ? (
                            <p>Your medication competency review was due {authority.competencyExpires}.</p>
                        ) : null}
                        <p>
                            The round is still open and nothing has been lost. Everything already
                            recorded remains exactly as it was.
                        </p>
                    </div>
                ) : null}

                <section style={{ marginTop: '1.5rem' }}>
                    <h2>This round</h2>
                    <Row label="House" value={house.name} />
                    <Row label="Round" value={round.slot} />
                    <Row label="Date" value={round.date} />
                    <Row
                        label="Scheduled window"
                        value={round.window.from ? `${round.window.from} to ${round.window.to}` : null}
                    />
                    <Row label="Status" value={round.status} />
                    <Row label="Opened by" value={round.openedBy} />
                    <Row label="Started at" value={round.startedAt} />
                    <Row
                        label="Progress"
                        value={`${progress.recorded} of ${progress.people} people recorded, `
                            + `${progress.remaining} remaining, ${progress.late} late, `
                            + `${progress.timeSensitive} time-sensitive`}
                    />
                </section>

                {/* Who opened it and who joined, kept separate on purpose. */}
                <section style={{ marginTop: '1.5rem' }}>
                    <h2>On this round ({participants.length})</h2>
                    <ul>
                        {participants.map((person) => (
                            <li key={person.fullName}>
                                {person.fullName} ({person.roleAtJoin}) —{' '}
                                {person.openedIt ? 'opened it' : 'joined'} at {person.joinedAt}
                                {person.lastActedAt ? `, last active ${person.lastActedAt}` : ''}
                            </li>
                        ))}
                    </ul>
                </section>

                <section style={{ marginTop: '1.5rem' }}>
                    <h2>People in this round ({queue.length})</h2>
                    <p>
                        Ordered by urgency, not by name: late and time-sensitive first.
                        Medicines and recording belong to Sections 2.1 and 2.2.
                    </p>

                    <table>
                        <thead>
                            <tr>
                                <th>Person</th><th>Room</th><th>Due</th>
                                <th>Items</th><th>Time sensitive</th><th>Late</th>
                                <th>Progress</th><th>Safety warning</th>
                                <th>Support</th><th>Availability</th>
                            </tr>
                        </thead>
                        <tbody>
                            {queue.map((person) => (
                                <tr key={person.clientId}>
                                    <td>{person.name} ({person.fullName})</td>
                                    <td>{person.room ?? '—'}</td>
                                    <td>{person.dueAt}</td>
                                    <td>{person.itemCount}</td>
                                    <td>{person.timeSensitive ? 'yes' : 'no'}</td>
                                    <td>{person.late ? `yes, ${person.minutesLate} min` : 'no'}</td>
                                    <td>{person.progress}</td>
                                    <td>{person.hasSafetyWarning ? 'yes' : 'no'}</td>
                                    <td>{person.support.word}</td>
                                    <td>{person.clientStatusWord}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </section>
            </div>
        </AppShell>
    );
}
