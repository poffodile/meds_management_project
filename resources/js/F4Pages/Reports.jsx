import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { Empty, Status } from '@frontend4/components/F4Atoms';
import { allows, P } from '@frontend4/roles';

const labels = {
    overview: ['Assurance overview', 'Selected factual measures without client-level records'],
    administrations: ['Administration records', 'Recorded outcomes with source record IDs'],
    exceptions: ['Dose exceptions', 'Not-given and late records requiring review'],
    incidents: ['Medication incidents', 'Status, severity, immediate action, outcome and learning'],
    tasks: ['Follow-up actions', 'Owners, deadlines, escalation and completion evidence'],
    prescriptions: ['Prescription changes', 'Append-only creation, amendment and lifecycle events'],
    controlled_drugs: ['Controlled-drug register', 'Movements, witnesses, balances and discrepancies'],
    stock: ['Medication stock', 'Current balances, reorder levels and expiry dates'],
};

export default function Reports({ can = [], accessContext, roleLabel, place, user, metrics, exports = [], reportTypes = [], filters }) {
    const [period, setPeriod] = useState(filters);
    const [type, setType] = useState('overview');
    const csrf = typeof document === 'undefined' ? '' : document.querySelector('meta[name="csrf-token"]')?.content || '';
    const values = metrics.values || {};

    return <F4Shell area="reports" title="Reports & audit" summary="Scoped evidence and controlled exports" place={place} user={user} roleLabel={roleLabel} can={can} accessContext={accessContext}>
        <Head title="Reports and audit - Care One OS" />
        <div className="f4-stack f4-reports-page">
            <section className="f4-card f4-reports-filter"><div><p className="f4-eyebrow">Reporting period</p><h2>Live source records</h2><p>Every report is restricted to the active organisation, service and location before it is returned.</p></div><form onSubmit={(event) => { event.preventDefault(); router.get('/frontend4/reports', period, { preserveState: true }); }}><label>From<input type="date" value={period.start} onChange={(event) => setPeriod({ ...period, start: event.target.value })} /></label><label>To<input type="date" value={period.end} onChange={(event) => setPeriod({ ...period, end: event.target.value })} /></label><button className="f4-btn">Refresh</button></form></section>

            <section className="f4-report-summary"><article><small>Administration records</small><strong>{values.administrationRecords ?? 'Unavailable'}</strong></article><article><small>Not-given outcomes</small><strong>{values.notGivenRecords ?? 'Unavailable'}</strong></article><article><small>Overdue actions</small><strong>{values.overdueTaskCount ?? 'Unavailable'}</strong></article><article><small>Open incidents</small><strong>{values.openIncidentCount ?? 'Unavailable'}</strong></article><article><small>CD discrepancies</small><strong>{values.cdDiscrepancyCount ?? 'Unavailable'}</strong></article></section>

            <div className="f4-reports-layout"><section className="f4-card"><div className="f4-section-heading"><div><p className="f4-eyebrow">Report library</p><h2>Available evidence</h2></div></div><div className="f4-report-library">{reportTypes.map((key) => <button key={key} data-selected={type === key || undefined} onClick={() => setType(key)}><span><strong>{labels[key]?.[0] || key}</strong><small>{labels[key]?.[1]}</small></span><b>›</b></button>)}</div></section>

                <aside className="f4-card f4-export-card"><p className="f4-eyebrow">Secure export</p><h2>{labels[type]?.[0]}</h2><p>{labels[type]?.[1]}</p>{allows(can, P.EXPORT_REPORT) ? <form method="post" action="/frontend4/reports/export" className="f4-stack">
                    <input type="hidden" name="_token" value={csrf} /><input type="hidden" name="report_type" value={type} /><input type="hidden" name="period_start" value={filters.start} /><input type="hidden" name="period_end" value={filters.end} />
                    <label className="f4-record-field">Client detail<select name="identifiable" defaultValue="0"><option value="0">Do not include client-level detail</option><option value="1">Include identifiable client detail</option></select></label>
                    <label className="f4-record-field">Reason for export<textarea name="reason" rows="3" minLength="10" required placeholder="Explain the authorised operational purpose." /></label>
                    <label className="f4-report-confirm"><input type="checkbox" name="authorised" value="1" required /><span>I confirm I am authorised to access and export this information.</span></label>
                    <button className="f4-btn">Generate audited CSV</button><small className="f4-panel-note">The requester, scope, period, reason and row count are recorded. The generated file itself is not retained by Care One OS.</small>
                </form> : <p>You do not have permission to export reports.</p>}</aside></div>

            <section className="f4-card"><div className="f4-section-heading"><div><p className="f4-eyebrow">Access trail</p><h2>Recent report exports</h2></div><Status status="info" label="Append-only" /></div>{exports.length ? <div className="f4-export-history">{exports.map((row) => <article key={row.id}><span><strong>{labels[row.reportType]?.[0] || row.reportType}</strong><small>{row.periodStart} to {row.periodEnd} · {row.recordCount} rows · {row.identifiable ? 'Identifiable detail included' : 'Summary only'}</small><p>{row.reason}</p></span><span><b>{row.generatedAt}</b><small>requester user #{row.requestedByUserId}</small></span></article>)}</div> : <Empty title="No exports recorded" body="Generated reports will be recorded here; report contents are not stored in this audit table." />}</section>
        </div>
    </F4Shell>;
}
