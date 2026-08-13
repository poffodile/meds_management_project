import React from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { Empty, Status } from '@frontend4/components/F4Atoms';
import { allows, P } from '@frontend4/roles';

const cards = [
    ['administrationRecords', 'Administration records', 'Recorded outcomes in the selected period'],
    ['notGivenRecords', 'Not-given outcomes', 'Refused, withheld, unavailable or other'],
    ['lateRecords', 'Late records', 'Administrations explicitly marked late'],
    ['prnRecords', 'PRN administrations', 'Given as-required medicine records'],
    ['lowStockCount', 'Low stock now', 'Active prescriptions at or below reorder level'],
    ['cdDiscrepancyCount', 'CD discrepancies', 'Flagged register entries in the period'],
    ['openTaskCount', 'Open actions', 'Follow-up tasks still open'],
    ['overdueTaskCount', 'Overdue actions', 'Open tasks past their due time'],
    ['openIncidentCount', 'Open incidents', 'Reported or under investigation'],
    ['highRiskIncidentCount', 'High-risk incidents', 'Open high or critical incidents'],
    ['prescriptionEventCount', 'Prescription changes', 'Recorded lifecycle events in the period'],
];

function Period({ filters }) {
    const form = useForm({ start: filters.start, end: filters.end });
    return <form className="f4-assurance-period" onSubmit={(event) => { event.preventDefault(); router.get('/frontend4/assurance', form.data, { preserveState: true }); }}>
        <label>From<input type="date" value={form.data.start} onChange={(event) => form.setData('start', event.target.value)} /></label>
        <label>To<input type="date" value={form.data.end} onChange={(event) => form.setData('end', event.target.value)} /></label>
        <button className="f4-btn">Refresh evidence</button>
    </form>;
}

export default function Assurance({ can = [], accessContext, roleLabel, place, user, metrics, history = [], filters }) {
    const review = useForm({ period_start: filters.start, period_end: filters.end, review_note: '', action_summary: '' });
    const values = metrics.values || {};
    const canReview = allows(can, P.COMPLETE_ASSURANCE_REVIEW);

    return <F4Shell area="assurance" title="Assurance" summary="Factual medication governance evidence" place={place} user={user} roleLabel={roleLabel} can={can} accessContext={accessContext}>
        <Head title="Assurance - Care One OS" />
        <div className="f4-stack f4-assurance-page">
            <section className="f4-card f4-assurance-intro">
                <div><p className="f4-eyebrow">Medication governance</p><h2>Review evidence, not a synthetic score</h2><p>These figures point back to live source records. A zero means no matching records were found; “Data unavailable” means the source could not be read.</p></div>
                <Period filters={filters} />
            </section>

            {!metrics.allAvailable ? <div className="f4-assurance-warning" role="alert"><strong>Some source data is unavailable.</strong><span>The affected measures are labelled below and this period cannot be signed until the source is restored.</span></div> : null}

            <section className="f4-assurance-grid" aria-label="Assurance evidence">
                {cards.map(([key, label, description]) => <article className="f4-card f4-assurance-metric" key={key} data-alert={values[key] > 0 && ['notGivenRecords', 'lateRecords', 'lowStockCount', 'cdDiscrepancyCount', 'overdueTaskCount', 'highRiskIncidentCount'].includes(key) || undefined}>
                    <small>{label}</small><strong>{values[key] === null ? 'Unavailable' : values[key]}</strong><span>{description}</span>
                </article>)}
            </section>

            {canReview ? <section className="f4-card"><div className="f4-section-heading"><div><p className="f4-eyebrow">Manager review</p><h2>Sign this evidence snapshot</h2></div><Status status={metrics.allAvailable ? 'due' : 'overdue'} label={metrics.allAvailable ? 'Ready for review' : 'Source unavailable'} /></div>
                <p className="f4-panel-note">Signing records what was reviewed and the actions identified. It does not certify the service as compliant or clinically safe.</p>
                <form className="f4-record-grid f4-assurance-review" onSubmit={(event) => { event.preventDefault(); review.post('/frontend4/assurance/reviews'); }}>
                    <label className="f4-record-field">Review note<textarea rows="4" value={review.data.review_note} onChange={(event) => review.setData('review_note', event.target.value)} required />{review.errors.review_note ? <small className="f4-field-error">{review.errors.review_note}</small> : null}</label>
                    <label className="f4-record-field">Actions and owners<textarea rows="4" value={review.data.action_summary} onChange={(event) => review.setData('action_summary', event.target.value)} placeholder="Record actions, owners and target dates where applicable." /></label>
                    <div className="f4-record-actions"><button className="f4-btn" disabled={review.processing || !metrics.allAvailable}>Sign append-only review</button></div>
                </form>
            </section> : null}

            <section className="f4-card"><div className="f4-section-heading"><div><p className="f4-eyebrow">Append-only history</p><h2>Previous assurance reviews</h2></div></div>
                {history.length ? <div className="f4-assurance-history">{history.map((row) => <article key={row.id}><div><strong>{row.periodStart} to {row.periodEnd}</strong><small>Signed {row.reviewedAt} · reviewer user #{row.reviewedByUserId}</small><p>{row.reviewNote}</p>{row.actionSummary ? <p><b>Actions:</b> {row.actionSummary}</p> : null}</div><dl><div><dt>Exceptions</dt><dd>{row.snapshot.notGivenRecords}</dd></div><div><dt>Overdue actions</dt><dd>{row.snapshot.overdueTaskCount}</dd></div><div><dt>Open incidents</dt><dd>{row.snapshot.openIncidentCount}</dd></div></dl></article>)}</div> : <Empty title="No signed reviews yet" body="The first signed review will appear here without replacing or editing earlier evidence." />}
            </section>
        </div>
    </F4Shell>;
}
