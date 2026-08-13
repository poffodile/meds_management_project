import React, { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import F4Shell from '@frontend4/components/F4Shell';
import { Empty, Status } from '@frontend4/components/F4Atoms';
import { allows, P } from '@frontend4/roles';

const priorityStatus = { urgent: 'overdue', high: 'late', normal: 'info', low: 'upcoming' };

function Field({ label, error, children }) {
    return <label className="f4-record-field"><span>{label}</span>{children}{error ? <small className="f4-field-error">{error}</small> : null}</label>;
}

function HandoverCard({ handover, can }) {
    const notes = useForm({ general_notes: handover.generalNotes || '' });
    const [open, setOpen] = useState(handover.status === 'draft');
    return (
        <article className="f4-handover-card" data-status={handover.status}>
            <button type="button" className="f4-handover-summary" onClick={() => setOpen(!open)} aria-expanded={open}>
                <span><strong>{handover.shiftStart} to {handover.shiftEnd}</strong><small>{handover.items.length} linked records · {handover.acknowledgementCount} acknowledgements</small></span>
                <Status status={handover.status === 'draft' ? 'upcoming' : handover.status === 'submitted' ? 'due' : 'given'} label={handover.status} />
            </button>
            {open ? <div className="f4-handover-body">
                {handover.items.length ? <div className="f4-handover-items">{handover.items.map((item) => (
                    <div className="f4-handover-item" key={item.id} data-priority={item.priority}>
                        <span><strong>{item.summary}</strong><small>{[item.detail, item.occurredAt].filter(Boolean).join(' · ')}</small></span>
                        <span><Status status={priorityStatus[item.priority]} label={item.priority} />{item.hasOpenTask ? <small>Task open</small> : null}</span>
                    </div>
                ))}</div> : <Empty title="No source records in this window" body="Add a clear general note before submitting, or create a draft using a window containing medication activity." />}
                {handover.status === 'draft' ? <form onSubmit={(event) => { event.preventDefault(); notes.put(`/frontend4/handover/${handover.id}`, { preserveScroll: true }); }} className="f4-stack">
                    <Field label="General shift note" error={notes.errors.general_notes}><textarea rows="3" value={notes.data.general_notes} onChange={(event) => notes.setData('general_notes', event.target.value)} /></Field>
                    <div className="f4-actions"><button className="f4-btn" data-variant="quiet" disabled={notes.processing}>Save draft note</button><button type="button" className="f4-btn" onClick={() => router.post(`/frontend4/handover/${handover.id}/submit`)}>Submit handover</button></div>
                </form> : !handover.acknowledgedByMe && allows(can, P.ACKNOWLEDGE_HANDOVER) ? <div className="f4-actions"><button className="f4-btn" onClick={() => router.post(`/frontend4/handover/${handover.id}/acknowledge`)}>Acknowledge receipt</button><small>This confirms you read it. It does not complete its tasks.</small></div> : <p className="f4-panel-note">{handover.acknowledgedByMe ? 'You acknowledged receipt. Open tasks remain separate.' : 'Submitted handover.'}</p>}
            </div> : null}
        </article>
    );
}

export default function Handover({ can = [], accessContext, roleLabel, place, user, handovers = [], tasks = [], incidents = [], staff = [], clients = [], draftDefaults }) {
    const draft = useForm(draftDefaults);
    const task = useForm({ handover_item_id: '', client_id: '', task_type: 'clinical_follow_up', title: '', instructions: '', owner_user_id: '', priority: 'normal', due_at: '', escalate_at: '' });
    const incident = useForm({ handover_item_id: '', client_id: '', category: 'administration', severity: 'moderate', description: '', immediate_action: '' });
    const [taskOpen, setTaskOpen] = useState(false);
    const [incidentOpen, setIncidentOpen] = useState(false);
    const canInvestigate = allows(can, P.INVESTIGATE_MEDICATION_INCIDENT);
    const sourceItems = handovers.flatMap((handover) => handover.items.map((item) => ({ ...item, handoverId: handover.id })));

    return <F4Shell area="handover" title="Shift handover" summary={`${tasks.length} open tasks · ${incidents.filter((i) => i.status !== 'closed').length} open incidents`} place={place} user={user} roleLabel={roleLabel} can={can} accessContext={accessContext}>
        <Head title="Shift handover - Care One OS" />
        <div className="f4-stack f4-handover-page">
            <section className="f4-card f4-handover-create"><div><p className="f4-eyebrow">Outgoing shift</p><h2>Create an automatic draft</h2><p>Pulls real medication exceptions, PRN reviews, controlled-drug discrepancies, stock concerns and prescription changes into one linked draft.</p></div>
                <form onSubmit={(event) => { event.preventDefault(); draft.post('/frontend4/handover/drafts'); }} className="f4-record-grid">
                    <Field label="Shift started" error={draft.errors.shift_start}><input type="datetime-local" value={draft.data.shift_start} onChange={(e) => draft.setData('shift_start', e.target.value)} required /></Field>
                    <Field label="Shift ended" error={draft.errors.shift_end}><input type="datetime-local" value={draft.data.shift_end} onChange={(e) => draft.setData('shift_end', e.target.value)} required /></Field>
                    <button className="f4-btn" disabled={draft.processing}>Create linked draft</button>
                </form>
            </section>

            <section><div className="f4-section-heading"><div><p className="f4-eyebrow">Incoming shift</p><h2>Handovers</h2></div></div>{handovers.length ? handovers.map((handover) => <HandoverCard key={handover.id} handover={handover} can={can} />) : <Empty title="No handovers yet" body="Create the first automatic draft when the outgoing shift is ready to hand over." />}</section>

            <section className="f4-card"><div className="f4-section-heading"><div><p className="f4-eyebrow">Work stays open until completed</p><h2>Follow-up tasks</h2></div><button className="f4-btn" data-variant="quiet" onClick={() => setTaskOpen(!taskOpen)}>Add task</button></div>
                {taskOpen ? <form className="f4-record-grid f4-inline-form" onSubmit={(e) => { e.preventDefault(); task.post('/frontend4/handover/tasks', { preserveScroll: true, onSuccess: () => setTaskOpen(false) }); }}>
                    <Field label="Linked handover record"><select value={task.data.handover_item_id} onChange={(e) => task.setData('handover_item_id', e.target.value)}><option value="">Manual task</option>{sourceItems.map((item) => <option value={item.id} key={item.id}>{item.summary}</option>)}</select></Field>
                    <Field label="Task type"><select value={task.data.task_type} onChange={(e) => task.setData('task_type', e.target.value)}><option value="clinical_follow_up">Clinical follow-up</option><option value="professional_advice">Professional advice</option><option value="stock">Stock</option><option value="incident_action">Incident action</option><option value="other">Other</option></select></Field>
                    <Field label="Client"><select value={task.data.client_id} onChange={(e) => task.setData('client_id', e.target.value)}><option value="">Service-wide</option>{clients.map((c) => <option value={c.id} key={c.id}>{c.name}</option>)}</select></Field>
                    <Field label="Title" error={task.errors.title}><input value={task.data.title} onChange={(e) => task.setData('title', e.target.value)} required /></Field>
                    <Field label="Owner" error={task.errors.owner_user_id}><select value={task.data.owner_user_id} onChange={(e) => task.setData('owner_user_id', e.target.value)} required><option value="">Choose staff member</option>{staff.map((s) => <option value={s.id} key={s.id}>{s.name}</option>)}</select></Field>
                    <Field label="Priority"><select value={task.data.priority} onChange={(e) => task.setData('priority', e.target.value)}><option>low</option><option>normal</option><option>high</option><option>urgent</option></select></Field>
                    <Field label="Due"><input type="datetime-local" value={task.data.due_at} onChange={(e) => task.setData('due_at', e.target.value)} required /></Field>
                    <Field label="Escalate if still open"><input type="datetime-local" value={task.data.escalate_at} onChange={(e) => task.setData('escalate_at', e.target.value)} required /></Field>
                    <Field label="Instructions"><textarea rows="2" value={task.data.instructions} onChange={(e) => task.setData('instructions', e.target.value)} /></Field><button className="f4-btn">Assign task</button>
                </form> : null}
                {tasks.length ? <div className="f4-task-list">{tasks.map((row) => <div className="f4-task" key={row.id} data-escalated={row.escalated || undefined}><span><strong>{row.title}</strong><small>{row.taskType.replaceAll('_', ' ')} · due {row.dueAt} · escalate {row.escalateAt}</small>{row.instructions ? <p>{row.instructions}</p> : null}</span><span><Status status={row.escalated ? 'overdue' : row.overdue ? 'late' : priorityStatus[row.priority]} label={row.escalated ? 'Escalated' : row.priority} />{row.canComplete ? <button className="f4-btn" data-size="sm" onClick={() => { const note = window.prompt('Completion note'); if (note) router.post(`/frontend4/handover/tasks/${row.id}/complete`, { completion_note: note }); }}>Complete</button> : null}</span></div>)}</div> : <Empty title="No open follow-up tasks" body="Any work assigned from a handover or incident will remain here until someone records its completion." />}
            </section>

            <section className="f4-card"><div className="f4-section-heading"><div><p className="f4-eyebrow">Separate safety record</p><h2>Medication incidents</h2></div><button className="f4-btn" data-variant="quiet" onClick={() => setIncidentOpen(!incidentOpen)}>Report incident</button></div>
                {incidentOpen ? <form className="f4-record-grid f4-inline-form" onSubmit={(e) => { e.preventDefault(); incident.post('/frontend4/handover/incidents', { preserveScroll: true, onSuccess: () => setIncidentOpen(false) }); }}>
                    <Field label="Linked handover record"><select value={incident.data.handover_item_id} onChange={(e) => incident.setData('handover_item_id', e.target.value)}><option value="">Manual incident</option>{sourceItems.map((item) => <option value={item.id} key={item.id}>{item.summary}</option>)}</select></Field>
                    <Field label="Client"><select value={incident.data.client_id} onChange={(e) => incident.setData('client_id', e.target.value)}><option value="">Service-wide or unknown</option>{clients.map((c) => <option value={c.id} key={c.id}>{c.name}</option>)}</select></Field>
                    <Field label="Category"><select value={incident.data.category} onChange={(e) => incident.setData('category', e.target.value)}><option>administration</option><option>omission</option><option value="controlled_drug">controlled drug</option><option>stock</option><option>prescription</option><option>other</option></select></Field>
                    <Field label="Severity"><select value={incident.data.severity} onChange={(e) => incident.setData('severity', e.target.value)}><option>low</option><option>moderate</option><option>high</option><option>critical</option></select></Field>
                    <Field label="What happened" error={incident.errors.description}><textarea rows="4" value={incident.data.description} onChange={(e) => incident.setData('description', e.target.value)} required /></Field>
                    <Field label="Immediate action taken" error={incident.errors.immediate_action}><textarea rows="3" value={incident.data.immediate_action} onChange={(e) => incident.setData('immediate_action', e.target.value)} required /></Field><button className="f4-btn">Report incident</button>
                </form> : null}
                <div className="f4-incident-list">{incidents.map((row) => <article className="f4-incident" key={row.id}><div><strong>{row.category.replaceAll('_', ' ')}</strong><p>{row.description}</p><small>Reported {row.reportedAt} · Immediate action: {row.immediateAction}</small>{row.outcome ? <small>Outcome: {row.outcome} · Learning: {row.learning}</small> : null}</div><div><Status status={row.severity === 'critical' ? 'overdue' : row.severity === 'high' ? 'late' : 'info'} label={`${row.severity} · ${row.status}`} />{canInvestigate && row.status === 'reported' ? <button className="f4-btn" data-size="sm" onClick={() => router.post(`/frontend4/handover/incidents/${row.id}/investigate`)}>Start investigation</button> : null}{canInvestigate && row.status !== 'closed' ? <button className="f4-btn" data-size="sm" data-variant="quiet" onClick={() => { const outcome = window.prompt('Investigation outcome'); const learning = outcome && window.prompt('Learning and prevention'); if (outcome && learning) router.post(`/frontend4/handover/incidents/${row.id}/close`, { outcome, learning }); }}>Close</button> : null}</div></article>)}</div>
            </section>
        </div>
    </F4Shell>;
}
