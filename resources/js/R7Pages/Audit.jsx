import React from 'react';
import { router } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import Notice from '@record7/components/Notice.jsx';

const RESULT_WORDS = {
    success: 'Success',
    failure: 'Failed',
    denied: 'Refused',
    warning: 'Warning',
    information: 'Note',
};

const RESULT_TONE = {
    success: 'yes',
    failure: 'warn',
    denied: 'no',
    warning: 'warn',
    information: 'info',
};

/**
 * The manager's access-audit screen.
 *
 * Reaching this needs view_access_audit, which Service Managers, Organisation
 * Administrators, Medication Leads and the Quality and Compliance Reviewer
 * hold and support workers do not. It is scoped to the houses the reader can
 * actually reach, so a manager of two houses cannot read a third one's record.
 *
 * Reading it is itself recorded. Looking at who accessed what is an access
 * event like any other.
 */
export default function Audit({ events, houses, filters, total, shown, integrity, urls }) {
    const setFilter = (key, value) => {
        const next = { ...filters };

        if (!value || next[key] === value) {
            delete next[key];
        } else {
            next[key] = value;
        }

        router.get(urls.self, next, { preserveScroll: true });
    };

    return (
        <AppShell urls={urls}>
            <div className="r7-page-head">
                <span className="r7-label">Access audit</span>
                <h1 className="r7-display">Who reached what</h1>
                <p className="r7-lede r7-measure">
                    Every sign-in, refusal, house change, lock and unlock across the houses you
                    oversee. Records can be added but never changed or removed.
                </p>
            </div>

            <div className="r7-stack">
                {integrity.brokenLinks > 0 ? (
                    <Notice tone="problem" tag="Integrity">
                        {integrity.brokenLinks} record{integrity.brokenLinks === 1 ? '' : 's'} do not
                        follow the expected sequence. Report this to your system administrator.
                    </Notice>
                ) : (
                    <Notice tone="ok" tag="Integrity">
                        The record is complete and in sequence. Access events are append only and
                        the database refuses any attempt to change or remove one.
                    </Notice>
                )}

                <section className="r7-panel">
                    <header className="r7-panel__head">
                        <h2 className="r7-heading">Filter</h2>
                        <span className="r7-label">
                            Showing {shown} of {total}
                        </span>
                    </header>
                    <div className="r7-panel__body r7-stack">
                        <div className="r7-stack-s">
                            <span className="r7-label">Result</span>
                            <div className="r7-filters">
                                {['success', 'denied', 'failure', 'information'].map((result) => (
                                    <button
                                        key={result}
                                        type="button"
                                        className={`r7-filter${filters.result === result ? ' r7-filter--on' : ''}`}
                                        onClick={() => setFilter('result', result)}
                                    >
                                        {RESULT_WORDS[result]}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {houses.length > 1 ? (
                            <div className="r7-stack-s">
                                <span className="r7-label">House</span>
                                <div className="r7-filters">
                                    {houses.map((house) => (
                                        <button
                                            key={house.id}
                                            type="button"
                                            className={`r7-filter${Number(filters.house) === house.id ? ' r7-filter--on' : ''}`}
                                            onClick={() => setFilter('house', house.id)}
                                        >
                                            {house.name}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        ) : null}
                    </div>
                </section>

                {events.length ? (
                    <ul className="r7-events">
                        {events.map((event) => (
                            <li key={event.id} className={`r7-event r7-event--${event.result}`}>
                                <span className="r7-event__top">
                                    <span className="r7-event__type">{event.typeLabel}</span>
                                    <span className="r7-event__time">{event.time}</span>
                                </span>

                                <span className="r7-event__meta">
                                    <span className="r7-event__who">
                                        {event.staff ?? 'Not signed in'}
                                        {event.role ? <span className="r7-faint"> {event.role}</span> : null}
                                    </span>
                                    {event.house ? <span>{event.house}</span> : null}
                                    {event.reason ? <span>{event.reason}</span> : null}
                                </span>

                                <span className={`r7-state r7-state--${RESULT_TONE[event.result] ?? 'info'}`}>
                                    {RESULT_WORDS[event.result] ?? event.result}
                                </span>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <section className="r7-panel">
                        <div className="r7-panel__body">
                            <p className="r7-muted">
                                No access events match that filter yet.
                            </p>
                        </div>
                    </section>
                )}

                <div>
                    <button
                        type="button"
                        className="r7-btn r7-btn--quiet"
                        onClick={() => router.get(urls.today)}
                    >
                        Back to Today
                    </button>
                </div>
            </div>
        </AppShell>
    );
}
