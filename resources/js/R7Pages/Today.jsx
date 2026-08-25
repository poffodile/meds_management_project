import React from 'react';
import { router, usePage } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import Notice from '@record7/components/Notice.jsx';
import StatusLabel from '@record7/components/StatusLabel.jsx';
import PageHeading from '@record7/components/PageHeading.jsx';
import useProduct from '@record7/useProduct.js';

const COMPETENCY_WORDS = {
    current: 'Current',
    review_due: 'Review due',
    expired: 'Expired',
    suspended: 'Suspended',
    not_assessed: 'Not assessed',
    not_required: 'Not required',
};

const COMPETENCY_TONE = {
    current: 'success',
    not_required: 'success',
    review_due: 'warning',
    expired: 'error',
    suspended: 'error',
    not_assessed: 'error',
};

const ACCESS_WORDS = {
    standard: 'Full access',
    manager: 'Manager access',
    oversight: 'Oversight access',
    read_only: 'Review only',
    temporary: 'Temporary access',
};

/**
 * The house's Today screen.
 *
 * Section 0 ends here. There is no clinical content yet, so rather than
 * pretending to hold a round it cannot run, this screen reports the access
 * decision plainly: what you may do in this house, what you may not, and why
 * not. That is what a Section 0 review actually needs to see, and it is honest
 * about the state of the build.
 */
export default function Today({
    name, fullName, employmentType, accessEndsAt, granted, decisions, competencies, urls,
}) {
    const product = useProduct();
    const { context } = usePage().props;

    const refused = decisions.filter((d) => !d.allowed);
    const canAudit = granted.includes('view_access_audit');
    const readOnly = context?.accessType === 'read_only';
    const temporary = context?.accessType === 'temporary';

    return (
        <AppShell urls={urls}>
            <div className="r7-page-heading">
                <span className="r7-label">Today</span>
                <h1 className="r7-display">Good to go{name ? <>, {name}</> : ''}</h1>
                <p className="r7-lede r7-measure">
                    You are signed in to {context?.house ?? 'this house'}. {product.seventhRight} is
                    the seventh safeguard, so everything you do from here is recorded against
                    this house and your name.
                </p>
            </div>

            <div className="r7-stack">
                {readOnly ? (
                    <Notice tone="info" title="Review only">
                        Your access to this house is review only. You can look at records but
                        cannot record, change or approve anything.
                    </Notice>
                ) : null}

                {temporary && accessEndsAt ? (
                    <Notice tone="warning" title="Temporary">
                        Your access to this house is temporary and ends on {accessEndsAt}.
                    </Notice>
                ) : null}

                <div className="r7-grid r7-grid--two">
                    <section className="r7-panel">
                        <header className="r7-panel__head">
                            <h2 className="r7-heading">You</h2>
                            <span className="r7-label">This session</span>
                        </header>
                        <div className="r7-panel__body">
                            <dl className="r7-defs">
                                <Def label="Name" value={fullName} />
                                <Def label="Role" value={context?.role} />
                                <Def
                                    label="Access to this house"
                                    value={ACCESS_WORDS[context?.accessType] ?? context?.accessType}
                                />
                                <Def label="Employment" value={titleCase(employmentType)} />
                                {accessEndsAt ? <Def label="Access ends" value={accessEndsAt} /> : null}
                            </dl>
                        </div>
                    </section>

                    <section className="r7-panel">
                        <header className="r7-panel__head">
                            <h2 className="r7-heading">Competency</h2>
                            <span className="r7-label">
                                {competencies.length ? `${competencies.length} recorded` : 'None recorded'}
                            </span>
                        </header>
                        <div className="r7-panel__body">
                            {competencies.length ? (
                                <ul className="r7-list">
                                    {competencies.map((competency) => (
                                        <li className="r7-list__row" key={competency.name}>
                                            <span className="r7-list__body">
                                                <span className="r7-strong">{competency.name}</span>
                                                {competency.reviewDue ? (
                                                    <span className="r7-list__why">
                                                        Review due {competency.reviewDue}
                                                    </span>
                                                ) : null}
                                            </span>
                                            <StatusLabel tone={COMPETENCY_TONE[competency.status] ?? 'info'}>
                                                {COMPETENCY_WORDS[competency.status] ?? competency.status}
                                            </StatusLabel>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="r7-muted">
                                    No competency has been recorded for this account. Actions that
                                    need one will be refused until it is assessed.
                                </p>
                            )}
                        </div>
                    </section>
                </div>

                <section className="r7-panel">
                    <header className="r7-panel__head">
                        <h2 className="r7-heading">What you can do here</h2>
                        <span className="r7-label">
                            {granted.length} of {decisions.length} allowed
                        </span>
                    </header>
                    <div className="r7-panel__body r7-stack">
                        <p className="r7-small r7-muted r7-measure">
                            Every one of these is checked again on the server for each request.
                            Hiding a control is a courtesy, never the check.
                        </p>

                        <ul className="r7-list">
                            {decisions.map((decision) => (
                                <li className="r7-list__row" key={decision.code}>
                                    <span className="r7-list__body">
                                        <span className="r7-strong">{decision.name}</span>
                                        {decision.allowed ? (
                                            <span className="r7-code">{decision.code}</span>
                                        ) : (
                                            <span className="r7-list__why">{decision.reason}</span>
                                        )}
                                    </span>
                                    <StatusLabel tone={decision.allowed ? 'success' : 'error'}>
                                        {decision.allowed ? 'Allowed' : 'Refused'}
                                    </StatusLabel>
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>

                {canAudit ? (
                    <section className="r7-panel">
                        <header className="r7-panel__head">
                            <h2 className="r7-heading">Access audit</h2>
                            <span className="r7-label">Manager view</span>
                        </header>
                        <div className="r7-panel__body r7-stack">
                            <p className="r7-muted r7-measure">
                                Every sign-in, refusal, house change, lock and unlock across the
                                houses you oversee, in the order they happened.
                            </p>
                            <div>
                                <button
                                    type="button"
                                    className="r7-btn r7-btn--primary"
                                    onClick={() => router.get(urls.audit)}
                                >
                                    Open access audit
                                </button>
                            </div>
                        </div>
                    </section>
                ) : null}

                {refused.length ? (
                    <p className="r7-small r7-faint r7-measure">
                        {refused.length} action{refused.length === 1 ? '' : 's'} on this screen are
                        refused for this account. That is the access model working, not a fault.
                    </p>
                ) : null}
            </div>
        </AppShell>
    );
}

function Def({ label, value }) {
    return (
        <div className="r7-def">
            <dt className="r7-label">{label}</dt>
            <dd className="r7-def__value">{value ?? 'Not recorded'}</dd>
        </div>
    );
}

function titleCase(value) {
    return value ? value.charAt(0).toUpperCase() + value.slice(1) : null;
}
