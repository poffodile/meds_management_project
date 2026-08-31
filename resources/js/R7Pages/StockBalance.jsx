import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import Notice from '@record7/components/Notice.jsx';
import Button from '@record7/components/Button.jsx';
import StatusLabel from '@record7/components/StatusLabel.jsx';
import TextLink from '@record7/components/TextLink.jsx';

/**
 * Section 2.7 — one person's one medicine, and everything that happened to it.
 *
 * A COUNT OBSERVES. IT DOES NOT CORRECT.
 * Recording a count never moves the running figure, however far out it is. Both
 * numbers are kept side by side and a disagreement opens, and only a
 * reconciliation a manager has approved can settle it. That is deliberate: a
 * count that silently overwrote the ledger would let anybody make an awkward
 * figure disappear by writing down what they wished were true.
 *
 * EVERY DISAGREEMENT IS LISTED SEPARATELY.
 * Two short counts on one balance are two problems, each needing its own
 * approved figure. They are grouped on the manager board so one shortage does
 * not flood it, but here — where somebody is actually going to settle them —
 * they are shown one by one, because settling the first says nothing about the
 * second.
 */
export default function StockBalance({
    house, balance, history, unresolved, tokens, can, stage, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'stock', label: 'Stock', href: urls.stock, icon: 'building', current: true },
    ];

    const [quantity, setQuantity] = useState('');
    const [counted, setCounted] = useState('');
    const [threshold, setThreshold] = useState(balance.threshold ?? '');
    const [deltas, setDeltas] = useState({});

    const wrap = { maxWidth: '60rem', margin: '0 auto', padding: '0 0 4rem' };
    const card = {
        border: '1px solid var(--r7-colour-line)',
        borderRadius: '.6rem',
        padding: '1rem',
        marginBottom: '.75rem',
    };

    const post = (url, data) => url && router.post(url, data, { preserveScroll: true });

    return (
        <AppShell urls={urls} nav={nav}>
            <div style={wrap}>
                <p style={{ textTransform: 'uppercase', letterSpacing: '.08em', fontSize: '.75rem' }}>
                    {stage}
                </p>

                <TextLink href={urls.stock}>All stock in {house.name}</TextLink>

                <h1>
                    {balance.person} — {balance.medicine}
                    {balance.strength ? ` ${balance.strength}` : ''}
                </h1>

                <p style={{ fontSize: '1.25rem', fontVariantNumeric: 'tabular-nums' }}>
                    {balance.balance} {balance.balanceWord}
                </p>

                <p style={{ display: 'flex', gap: '.5rem', flexWrap: 'wrap' }}>
                    {balance.out ? <StatusLabel tone="danger">None left</StatusLabel> : null}
                    {balance.low ? <StatusLabel tone="warning">Below reorder level</StatusLabel> : null}
                    {balance.negative ? (
                        <StatusLabel tone="danger">More given than can be accounted for</StatusLabel>
                    ) : null}
                    {!balance.hasThreshold ? (
                        <StatusLabel tone="neutral">No reorder level recorded</StatusLabel>
                    ) : null}
                </p>

                {unresolved.length > 0 ? (
                    <section>
                        <h2>
                            {unresolved.length === 1
                                ? 'An unresolved disagreement'
                                : `${unresolved.length} unresolved disagreements`}
                        </h2>

                        <Notice tone="warning" title="Medicine may still be given">
                            Medicine may still be administered if physical availability is
                            verified. Reconciliation required.
                        </Notice>

                        {unresolved.map((entry) => (
                            <div key={entry.id} style={card} data-testid={`discrepancy-${entry.id}`}>
                                <p>
                                    {entry.cause === 'count'
                                        ? `Counted ${entry.counted} against an expected ${entry.expected}.`
                                        : `A dose was given that left the record at ${entry.balanceAfter}.`}
                                    {' '}
                                    <span style={{ fontVariantNumeric: 'tabular-nums' }}>
                                        Out by {entry.difference}.
                                    </span>
                                </p>

                                <p style={{ fontSize: '.85rem' }}>
                                    Recorded by {entry.by} at {entry.at}.
                                </p>

                                {entry.approved ? (
                                    <div>
                                        <p>
                                            A manager approved an adjustment of{' '}
                                            <strong>{entry.requestedDelta}</strong>.
                                        </p>
                                        {can.reconcile ? (
                                            <Button
                                                onClick={() => post(
                                                    `${urls.stock}/movement/${entry.id}/correct`, {}
                                                )}
                                            >
                                                Carry out the approved reconciliation
                                            </Button>
                                        ) : (
                                            <p>Somebody with reconciliation rights has to carry it out.</p>
                                        )}
                                    </div>
                                ) : entry.pending ? (
                                    <p>Waiting for a manager to approve a figure.</p>
                                ) : (
                                    <div>
                                        <label>
                                            What should the balance be adjusted by?{' '}
                                            <input
                                                type="number"
                                                step="0.001"
                                                value={deltas[entry.id] ?? ''}
                                                onChange={(e) => setDeltas({
                                                    ...deltas, [entry.id]: e.target.value,
                                                })}
                                            />
                                        </label>

                                        <Button
                                            onClick={() => post(
                                                `${urls.stock}/movement/${entry.id}/correction-request`,
                                                { quantity_delta: deltas[entry.id] }
                                            )}
                                        >
                                            Ask a manager to approve this
                                        </Button>
                                    </div>
                                )}
                            </div>
                        ))}
                    </section>
                ) : null}

                {can.manageStock ? (
                    <section>
                        <h2>Record something</h2>

                        {tokens.opening ? (
                            <div style={card}>
                                <h3>Opening count</h3>
                                <label>
                                    How many are there?{' '}
                                    <input
                                        type="number"
                                        step="0.001"
                                        value={quantity}
                                        onChange={(e) => setQuantity(e.target.value)}
                                    />
                                </label>
                                <Button onClick={() => post(urls.opening, {
                                    quantity, attempt_token: tokens.opening,
                                })}>
                                    Record the opening count
                                </Button>
                            </div>
                        ) : (
                            <div style={card}>
                                <h3>A delivery came in</h3>
                                <label>
                                    How many?{' '}
                                    <input
                                        type="number"
                                        step="0.001"
                                        value={quantity}
                                        onChange={(e) => setQuantity(e.target.value)}
                                    />
                                </label>
                                <Button onClick={() => post(urls.receipt, {
                                    quantity, attempt_token: tokens.receipt,
                                })}>
                                    Book it in
                                </Button>
                            </div>
                        )}

                        <div style={card}>
                            <h3>Count what is there</h3>

                            <p style={{ fontSize: '.85rem' }}>
                                A count records what you found. It does not change the running
                                figure — if the two disagree, both are kept and a manager has to
                                approve the adjustment.
                            </p>

                            <label>
                                How many did you count?{' '}
                                <input
                                    type="number"
                                    step="0.001"
                                    value={counted}
                                    onChange={(e) => setCounted(e.target.value)}
                                />
                            </label>
                            <Button onClick={() => post(urls.count, {
                                counted, attempt_token: tokens.count,
                            })}>
                                Record the count
                            </Button>
                        </div>

                        <div style={card}>
                            <h3>Reorder level</h3>

                            <p style={{ fontSize: '.85rem' }}>
                                {balance.hasThreshold
                                    ? balance.thresholdNote
                                    : 'Nothing is recorded, so nothing is called low. Leave it '
                                      + 'blank if no level has been agreed.'}
                            </p>

                            <label>
                                Low below{' '}
                                <input
                                    type="number"
                                    step="0.001"
                                    value={threshold}
                                    onChange={(e) => setThreshold(e.target.value)}
                                />
                            </label>
                            <Button onClick={() => post(urls.threshold, { low_threshold: threshold })}>
                                Save the reorder level
                            </Button>
                        </div>
                    </section>
                ) : null}

                <section>
                    <h2>History</h2>

                    <ul>
                        {history.map((m) => (
                            <li key={m.id} style={{ fontVariantNumeric: 'tabular-nums' }}>
                                <strong>{m.word}</strong>
                                {m.received ? `, in ${m.received}` : ''}
                                {m.given ? `, given ${m.given}` : ''}
                                {m.returned ? `, back ${m.returned}` : ''}
                                {m.wasted ? `, disposed ${m.wasted}` : ''}
                                {m.delta ? `, adjusted by ${m.delta}` : ''}
                                {m.counted !== null && m.counted !== undefined
                                    ? `, counted ${m.counted} against ${m.expected}`
                                    : ''}
                                {', balance '}
                                {m.balance} {m.balanceUnit}
                                {m.discrepancy ? ' — DISAGREEMENT' : ''}
                                {' — '}
                                {m.by} at {m.at}
                                {m.shortfallBasis ? (
                                    <div style={{ fontSize: '.85rem' }}>
                                        Checked before recording: {m.shortfallBasis}. “{m.shortfallStatement}”
                                        {m.shortfallObserved ? ` They saw ${m.shortfallObserved}.` : ''}
                                    </div>
                                ) : null}
                                {m.notes ? <div style={{ fontSize: '.85rem' }}>{m.notes}</div> : null}
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </AppShell>
    );
}
