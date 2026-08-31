import React from 'react';
import { router } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import Notice from '@record7/components/Notice.jsx';
import StatusLabel from '@record7/components/StatusLabel.jsx';
import TextLink from '@record7/components/TextLink.jsx';

/**
 * Section 2.7 — what this house counts, and what it does not agree with.
 *
 * THREE HONEST STATES, ALL THREE SAID OUT LOUD.
 * A medicine can be counted and correct, counted and wrong, or not counted at
 * all — and the third is the one a screen normally hides. A blank space where a
 * figure should be reads as "fine", so this screen never leaves one: an
 * untracked medicine is absent by design, an unquantified one is listed under
 * its own heading, and a balance with no reorder level says so in words rather
 * than showing a reassuring nothing.
 *
 * A DISAGREEMENT IS NOT TIDIED AWAY.
 * Where the ledger and the cupboard do not match, that sits at the top in its
 * own right and stays there until somebody reconciles it. Nobody can close it
 * from here, because closing paperwork is not the same as finding the tablets.
 *
 * CONTROLLED MEDICINES ARE SHOWN, NEVER EDITED.
 * They belong to the controlled drug register, and this page says so beside
 * every figure rather than quietly presenting two kinds of number as one.
 */
export default function Stock({ house, balances, controlled, unquantified, can, stage, urls }) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'stock', label: 'Stock', href: urls.stock, icon: 'building', current: true },
    ];

    const wrap = { maxWidth: '70rem', margin: '0 auto', padding: '0 0 4rem' };
    const card = {
        border: '1px solid var(--r7-colour-line)',
        borderRadius: '.6rem',
        padding: '1rem',
        marginBottom: '.75rem',
    };

    return (
        <AppShell urls={urls} nav={nav}>
            <div style={wrap}>
                <p style={{ textTransform: 'uppercase', letterSpacing: '.08em', fontSize: '.75rem' }}>
                    {stage}
                </p>
                <h1>Stock — {house.name}</h1>

                <p>
                    Every figure here is worked out from the movement history. Nothing on this
                    page can be typed over.
                </p>

                {balances.length === 0 ? (
                    <Notice tone="info" title="Nothing is being counted here yet">
                        No medicine in this house has an opening count, so no dose changes a
                        balance. That is not the same as everything being in stock.
                    </Notice>
                ) : null}

                <section>
                    <h2>Balances</h2>

                    {balances.map((b) => (
                        <div key={b.id} style={card} data-testid={`balance-${b.id}`}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
                                <div>
                                    <strong>{b.person}</strong>
                                    {' — '}
                                    {b.medicine}
                                    {b.strength ? ` ${b.strength}` : ''}
                                    {b.form ? ` (${b.form})` : ''}
                                </div>

                                <div style={{ textAlign: 'right' }}>
                                    <div style={{ fontVariantNumeric: 'tabular-nums' }}>
                                        {b.balance} {b.balanceWord}
                                    </div>
                                    <TextLink href={`${urls.stock}/${b.id}`}>History and actions</TextLink>
                                </div>
                            </div>

                            <p style={{ display: 'flex', gap: '.5rem', flexWrap: 'wrap', marginTop: '.5rem' }}>
                                {b.discrepancies > 0 ? (
                                    <StatusLabel tone="danger">
                                        {b.discrepancies === 1
                                            ? 'Unresolved discrepancy'
                                            : `${b.discrepancies} unresolved discrepancies`}
                                    </StatusLabel>
                                ) : null}

                                {b.out ? <StatusLabel tone="danger">None left</StatusLabel> : null}
                                {b.low ? <StatusLabel tone="warning">Below reorder level</StatusLabel> : null}
                                {b.negative ? (
                                    <StatusLabel tone="danger">
                                        More given than can be accounted for
                                    </StatusLabel>
                                ) : null}

                                {/* NULL IS NOT HEALTHY. Said in words, because a
                                    missing rule looks identical to a satisfied
                                    one if it is left blank. Said once: the line
                                    below carries the detail, not a second copy
                                    of the same sentence. */}
                                {!b.hasThreshold ? (
                                    <StatusLabel tone="neutral">No reorder level recorded</StatusLabel>
                                ) : null}
                            </p>

                            <p style={{ fontSize: '.85rem' }}>
                                {b.hasThreshold ? b.thresholdNote : 'Nothing is called low until a reorder level is recorded'}
                                {b.lastCounted ? ` — last counted ${b.lastCounted}` : ' — never counted'}
                            </p>
                        </div>
                    ))}
                </section>

                {unquantified.length > 0 ? (
                    <section>
                        <h2>Counted, but doses do not move the balance</h2>

                        <Notice tone="warning" title="These figures will drift">
                            The prescription does not record a structured dose quantity, so
                            Record7 cannot say how much a dose takes out. It will not guess from
                            the written direction.
                        </Notice>

                        <ul>
                            {unquantified.map((row, index) => (
                                <li key={index}>
                                    <strong>{row.person}</strong> — {row.medicine}
                                    {' ('}
                                    {row.dose}
                                    {') '}
                                    {row.why}
                                </li>
                            ))}
                        </ul>
                    </section>
                ) : null}

                {controlled.length > 0 ? (
                    <section>
                        <h2>Controlled medicines</h2>

                        <Notice tone="info" title="Held in the controlled drug register">
                            These balances belong to the controlled drug register and cannot be
                            changed from this page. Record a movement there instead.
                        </Notice>

                        <ul>
                            {controlled.map((row, index) => (
                                <li key={index} style={{ fontVariantNumeric: 'tabular-nums' }}>
                                    <strong>{row.person}</strong> — {row.medicine}: {row.balance}{' '}
                                    {row.unit}, held in the <em>{row.source}</em>
                                </li>
                            ))}
                        </ul>
                    </section>
                ) : null}

                <p style={{ fontSize: '.85rem', marginTop: '2rem' }}>
                    {can.manageStock
                        ? 'You may count stock and book deliveries in.'
                        : 'You may look at stock but not record anything.'}
                    {' '}
                    {can.reconcile
                        ? 'You may carry out an approved reconciliation.'
                        : 'Settling a discrepancy is somebody else’s to do.'}
                </p>
            </div>
        </AppShell>
    );
}
