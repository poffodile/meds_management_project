import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';

/**
 * Section 1.2 — Manager Today. FUNCTIONAL SCAFFOLD, NOT A DESIGN.
 *
 * This page is deliberately plain. It uses headings, definition lists, tables
 * and plain buttons, and it introduces no new classes, tokens or components
 * into the Record7 design system. The real Manager Today interface is being
 * designed separately, and anything decorative built here would have to be
 * thrown away — worse, it would quietly become the thing everybody compares the
 * real design against.
 *
 * What it IS for: proving the data, the house scoping, the permissions and the
 * five manager actions work, and showing exactly what shape the eventual design
 * will receive. Every value rendered here is in the contract documented on
 * ManagerController.
 *
 * It reuses AppShell only because the shell carries which house you are in, and
 * a manager screen that does not say which house it is talking about would be
 * dangerous rather than merely unfinished.
 */
function Section({ title, count = null, children }) {
    return (
        <section style={{ marginTop: '2rem' }}>
            <h2>
                {title}
                {count !== null ? ` (${count})` : ''}
            </h2>
            {children}
        </section>
    );
}

function post(url, payload) {
    router.post(url, payload, { preserveScroll: true });
}

export default function Manager({
    house, today, name, attention, rounds, staff, outcomes, review, stock, handovers, can, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'manager', label: 'Manager', href: '/record7/manager', icon: 'person', current: true },
        { key: 'stock', label: 'Stock', href: urls.stock, icon: 'building', available: can.viewStock },
        { key: 'audit', label: 'Audit', href: urls.audit, icon: 'clock', available: can.viewAudit },
    ];

    // Which closed round is having a reopening reason typed for it, if any.
    const [reopenFor, setReopenFor] = useState(null);
    const [reopenReason, setReopenReason] = useState('');

    return (
        <AppShell urls={urls} nav={nav}>
            <div style={{ maxWidth: '70rem', margin: '0 auto', padding: '0 0 4rem' }}>
                <p style={{ textTransform: 'uppercase', letterSpacing: '.08em', fontSize: '.75rem' }}>
                    Manager Today — functional scaffold
                </p>
                <h1>{house.name}</h1>
                <p>
                    {today} — signed in as {name}. Everything below is {house.name} only.
                    Use Switch house to change it.
                </p>

                {/* 1 ── Which safety issues need management now? */}
                <Section title="Management attention" count={attention.length}>
                    {attention.length === 0 ? <p>Nothing unresolved needs a manager.</p> : (
                        <ol>
                            {attention.map((item) => (
                                <li key={item.key} style={{ marginBottom: '1rem' }}>
                                    <strong>{item.issue}</strong>
                                    <br />
                                    House: {item.house} — {item.subjectKind}: {item.subject}
                                    <br />
                                    Severity: {item.severity}
                                    {item.at ? ` — ${item.at}` : ''}
                                    {item.minutes ? ` — waiting ${item.minutes} min` : ''}
                                    <br />
                                    Why a manager: {item.why}
                                    <br />
                                    Next action: {item.next}
                                    <br />
                                    Status: <strong>{item.status}</strong>
                                    {item.conditionActive ? '' : ' — condition cleared'}
                                    <br />
                                    Acknowledged: {item.acknowledged ? 'yes' : 'no'} — Owner:{' '}
                                    {item.owner ?? 'unassigned'} — Escalated:{' '}
                                    {item.escalated ? `yes, ${item.escalatedAt}` : 'no'} — Action recorded:{' '}
                                    {item.actionRecorded ? 'yes' : 'no'} — Closed:{' '}
                                    {item.closed ? `yes, by ${item.closedBy} ${item.closedAt}` : 'no'}
                                    {item.actionNote ? <><br />Action: {item.actionNote}</> : null}
                                    {item.closureReason ? <><br />Closure reason: {item.closureReason}</> : null}
                                    {item.evidenceReference ? <><br />Evidence: {item.evidenceReference}</> : null}
                                    <br />
                                    {/* The way to the work the row describes.
                                        Resolved on the server from the record
                                        itself, so it is always inside this
                                        house, and absent where there is nowhere
                                        honest to go. */}
                                    {item.destination ? (
                                        <>
                                            <button
                                                type="button"
                                                onClick={() => router.get(item.destination.url)}
                                            >
                                                {item.destination.label}
                                            </button>{' '}
                                        </>
                                    ) : null}
                                    <button type="button" onClick={() => post(urls.acknowledge, { issue_key: item.key })}>
                                        Acknowledge
                                    </button>{' '}
                                    <button type="button" onClick={() => post(urls.own, { issue_key: item.key })}>
                                        Take ownership
                                    </button>{' '}
                                    {can.reviewIncidents ? (
                                        <button
                                            type="button"
                                            onClick={() => post(urls.escalate, { issue_key: item.key })}
                                        >
                                            Escalate
                                        </button>
                                    ) : null}{' '}
                                    <button
                                        type="button"
                                        onClick={() => post(urls.recordAction, {
                                            issue_key: item.key,
                                            note: 'Spoke to the staff on shift and followed it up.',
                                        })}
                                    >
                                        Record action
                                    </button>{' '}
                                    <button
                                        type="button"
                                        onClick={() => post(urls.close, {
                                            issue_key: item.key,
                                            reason: 'Closed from Manager Today.',
                                            // Safety-critical issues are refused without this,
                                            // which is the point of the field.
                                            evidence_reference: item.needsEvidenceToClose
                                                ? 'INCIDENT-LOG-REF'
                                                : null,
                                        })}
                                    >
                                        Close{item.needsEvidenceToClose ? ' (needs evidence)' : ''}
                                    </button>
                                </li>
                            ))}
                        </ol>
                    )}
                </Section>

                {/* 2 ── Are the rounds running safely and on time? */}
                <Section title="Round oversight" count={rounds.length}>
                    <table>
                        <thead>
                            <tr>
                                <th>Round</th><th>Due</th><th>State</th>
                                <th>Expected</th><th>Completed</th><th>Remaining</th>
                                <th>Late</th><th>Time critical late</th>
                                <th>Opened by</th><th>Started</th><th>Intervention</th><th />
                            </tr>
                        </thead>
                        <tbody>
                            {rounds.map((round) => (
                                <tr key={round.slot}>
                                    <td>{round.slot}</td>
                                    <td>{round.dueAt}</td>
                                    <td>{round.state}</td>
                                    <td>{round.expectedPeople}</td>
                                    <td>{round.completedPeople}</td>
                                    <td>{round.remainingPeople}</td>
                                    <td>{round.lateCount}</td>
                                    <td>{round.timeCriticalLate}</td>
                                    <td>{round.openedBy ?? '—'}</td>
                                    <td>{round.startedAt ?? '—'}</td>
                                    <td>{round.interventionNeeded ? 'yes' : 'no'}</td>
                                    <td>
                                        {/* Section 2.6. What is actually being
                                            signed off, said before signing it
                                            rather than discovered afterwards. */}
                                        {round.accountability ? (
                                            <span className="r7-round-count">
                                                {round.accountability.accounted} of{' '}
                                                {round.accountability.planned} doses accounted for
                                            </span>
                                        ) : null}

                                        {round.unresolved?.length ? (
                                            <span className="r7-round-unresolved">
                                                Still unresolved: {round.unresolved.join(', ')}
                                            </span>
                                        ) : null}

                                        {round.lifecycle?.length ? (
                                            <ul className="r7-round-history">
                                                {round.lifecycle.map((entry) => (
                                                    <li key={entry.id}>
                                                        {entry.word} {entry.at}
                                                        {entry.by ? ` by ${entry.by}` : ''}
                                                        {entry.reason ? ` — ${entry.reason}` : ''}
                                                        {entry.imported ? ' (imported)' : ''}
                                                    </li>
                                                ))}
                                            </ul>
                                        ) : null}

                                        {round.roundId && round.state !== 'closed' ? (
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    const warn = round.unresolved?.length
                                                        ? `Closing does not resolve: ${round.unresolved.join(', ')}. `
                                                          + 'Those stay open. Continue?'
                                                        : 'Close this round?';

                                                    if (window.confirm(warn)) {
                                                        post(urls.closeRound, { round_id: round.roundId });
                                                    }
                                                }}
                                            >
                                                Close round
                                            </button>
                                        ) : null}

                                        {/* SECTION 2.6, THE OTHER HALF.
                                            Reopening was built, audited and
                                            tested, and could only ever happen
                                            against an approved request that
                                            nothing could raise. This raises
                                            one. It does NOT reopen the round —
                                            that still needs somebody holding
                                            reopen_medication_round to approve
                                            it in the queue below, which is why
                                            there is no direct button here even
                                            for people who hold it. */}
                                        {round.roundId && round.state === 'closed'
                                            && !round.reopenRequested ? (
                                            reopenFor === round.roundId ? (
                                                <>
                                                    {/* Asked for in writing, in
                                                        place. Whoever decides
                                                        this was not there, and
                                                        a reason typed into a
                                                        browser dialogue is one
                                                        nobody can read back. */}
                                                    <label>
                                                        Why should it be opened again?
                                                        <textarea
                                                            rows={2}
                                                            value={reopenReason}
                                                            maxLength={500}
                                                            onChange={(e) => setReopenReason(e.target.value)}
                                                        />
                                                    </label>
                                                    <button
                                                        type="button"
                                                        disabled={reopenReason.trim().length < 10}
                                                        onClick={() => {
                                                            post(urls.requestReopen, {
                                                                round_id: round.roundId,
                                                                reason: reopenReason.trim(),
                                                            });
                                                            setReopenFor(null);
                                                            setReopenReason('');
                                                        }}
                                                    >
                                                        Send the request
                                                    </button>{' '}
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setReopenFor(null);
                                                            setReopenReason('');
                                                        }}
                                                    >
                                                        Cancel
                                                    </button>
                                                </>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setReopenFor(round.roundId);
                                                        setReopenReason('');
                                                    }}
                                                >
                                                    Request reopening
                                                </button>
                                            )
                                        ) : null}

                                        {round.reopenRequested ? (
                                            <em>Reopening requested — waiting on a decision</em>
                                        ) : null}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </Section>

                {/* 3 ── Who cannot administer, and why? */}
                <Section title="Staff readiness" count={staff.length}>
                    <table>
                        <thead>
                            <tr>
                                <th>Staff</th><th>Employment role</th><th>Access</th>
                                <th>Permission</th><th>Competency</th><th>Expires</th>
                                <th>Restriction</th><th>May administer</th><th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            {staff.map((member) => (
                                <tr key={member.userId}>
                                    <td>{member.fullName}</td>
                                    <td>{member.role}</td>
                                    <td>{member.accessType} ({member.accessStatus})</td>
                                    <td>{member.hasPermission ? 'granted' : 'not granted'}</td>
                                    <td>
                                        {member.competencyStatus}
                                        {member.competencyExpiringSoon ? ' (expiring soon)' : ''}
                                    </td>
                                    <td>{member.competencyExpires ?? '—'}</td>
                                    <td>{member.restriction ?? '—'}</td>
                                    <td>{member.mayAdminister ? 'yes' : 'no'}</td>
                                    <td>{member.reason ?? '—'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </Section>

                {/* 4 ── What remains unresolved? */}
                <Section title="Outstanding outcomes and follow-ups">
                    <h3>Omissions ({outcomes.omissions.length})</h3>
                    <ul>
                        {outcomes.omissions.map((o) => (
                            <li key={o.id}>
                                {o.client} — {o.slot} round, due {o.dueAt}, {o.minutesLate} min late
                                {o.timeCritical ? ' — TIME CRITICAL' : ''}
                            </li>
                        ))}
                    </ul>

                    <h3>Refusals not followed up ({outcomes.refusals.length})</h3>
                    <ul>
                        {outcomes.refusals.map((r) => (
                            <li key={r.id}>{r.client} — {r.at} — {r.note}</li>
                        ))}
                    </ul>

                    <h3>Other not-taken outcomes ({outcomes.notTaken.length})</h3>
                    <ul>
                        {outcomes.notTaken.map((n) => (
                            <li key={n.id}>{n.client} — {n.outcome} at {n.at} — {n.note}</li>
                        ))}
                    </ul>

                    <h3>Incomplete records ({outcomes.incompleteRecords.length})</h3>
                    <ul>
                        {outcomes.incompleteRecords.map((r) => (
                            <li key={r.id}>{r.client} — {r.outcome} at {r.at}, no reason recorded</li>
                        ))}
                    </ul>

                    <h3>PRN effectiveness checks ({outcomes.prnFollowUps.length})</h3>
                    <ul>
                        {outcomes.prnFollowUps.map((p) => (
                            <li key={p.id}>
                                {p.client} — given {p.givenAt} by {p.givenBy}, answer due {p.dueAt}
                                {p.overdue ? ' — overdue' : ''}
                            </li>
                        ))}
                    </ul>
                </Section>

                {/* 5 ── What needs a decision? */}
                <Section title="Manager review queue" count={review.open.length}>
                    {review.open.length === 0 ? <p>Nothing waiting for a decision.</p> : (
                        <ol>
                            {review.open.map((item) => (
                                <li key={item.id} style={{ marginBottom: '1rem' }}>
                                    <strong>{item.kindWord}: {item.title}</strong> ({item.severity})
                                    <br />
                                    {item.detail}
                                    <br />
                                    Raised by {item.raisedBy}, waiting {item.waitingMinutes} min
                                    <br />
                                    {/* SECTION 2.7. THE BUTTONS COME FROM THE SERVER.
                                        They used to be hard-coded here: "Approve as missed" for
                                        anything correction-shaped, and Decline for everyone,
                                        always. That offered Decline to people who would meet a
                                        403, and offered "approve as missed" for a stock
                                        reconciliation, which asks for a quantity and not an
                                        outcome. What a person may do with a request depends on
                                        its kind, its subject, its status and their authority in
                                        THIS house — all of which the server knows and this
                                        screen does not. */}
                                    {item.actions.length === 0 ? (
                                        <em>You cannot decide this in this house. </em>
                                    ) : item.actions.map((action) => (
                                        <React.Fragment key={action.key}>
                                            <button
                                                type="button"
                                                onClick={() => post(urls.decide, {
                                                    review_id: item.id,
                                                    decision: action.decision,
                                                    ...(action.correctedOutcome
                                                        ? { corrected_outcome: action.correctedOutcome }
                                                        : {}),
                                                    note: action.decision === 'approved'
                                                        ? 'Approved from Manager Today.'
                                                        : 'Declined from Manager Today.',
                                                })}
                                            >
                                                {action.label}
                                            </button>{' '}
                                        </React.Fragment>
                                    ))}
                                </li>
                            ))}
                        </ol>
                    )}

                    <h3>Already decided ({review.decided.length})</h3>
                    <ul>
                        {review.decided.map((item) => (
                            <li key={item.id}>
                                {item.kindWord}: {item.title} — {item.status} by {item.decidedBy} on{' '}
                                {item.decidedAt} — {item.decisionNote}
                            </li>
                        ))}
                    </ul>
                </Section>

                {/* ── DELETED HERE: A SECOND COPY OF THE FOUR SECTIONS ABOVE.
                    Round oversight, Staff readiness, Outstanding outcomes and
                    the review queue were each rendered twice, and the two
                    copies were not the same. The first is the Section 2.7
                    version, where what a person may do with a request comes
                    from the server. The second was the code that version
                    replaced: it never read item.actions, and it drew Decline
                    outside every authority check — so somebody holding no
                    correction authority was still offered the button, which is
                    the defect Section 2.7 was rejected for. The server refused
                    them either way, but offering an action nobody may take is
                    its own fault. One authoritative rendering now. */}

                {/* 6 ── Urgent stock and controlled drugs */}
                <Section title="Stock and controlled-drug concerns" count={stock.length}>
                    <ul>
                        {stock.map((concern) => (
                            <li key={concern.key} style={{ marginBottom: '.75rem' }}>
                                <strong>{concern.issue}</strong> — {concern.medicine}
                                {concern.controlled ? ' (controlled drug)' : ''}
                                {concern.difference !== null ? ` — out by ${concern.difference}` : ''}
                                <br />
                                Why a manager: {concern.why}
                                <br />
                                Next action: {concern.next}
                                {concern.note ? <><br />{concern.note}</> : null}
                            </li>
                        ))}
                    </ul>
                </Section>

                {/* 7 ── Has the handover reached everybody? */}
                <Section title="Handover oversight" count={handovers.length}>
                    {handovers.map((handover) => (
                        <div key={handover.id} style={{ marginBottom: '1rem' }}>
                            <strong>{handover.shift}</strong> to {handover.coversTo}, written by{' '}
                            {handover.writtenBy} — {handover.urgentCount} urgent
                            {handover.escalated ? ' — ESCALATED' : ''}
                            <br />
                            Acknowledged ({handover.acknowledged.length}):{' '}
                            {handover.acknowledged.map((a) => `${a.name} at ${a.at}`).join(', ') || 'nobody'}
                            <br />
                            Outstanding ({handover.outstanding.length}):{' '}
                            {handover.outstanding.map((o) => `${o.name} (${o.role})`).join(', ') || 'nobody'}
                        </div>
                    ))}
                </Section>
            </div>
        </AppShell>
    );
}
