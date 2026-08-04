import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import F3Shell from '@frontend3/components/F3Shell';
import { Badge, Card, CardHead, Empty, Note } from '@frontend3/components/F3Atoms';

/**
 * Controlled-drug signatures awaiting you (spec §12).
 *
 * "Two distinct authenticated users for witness-required actions; self-witnessing
 * is impossible." This is the second signature happening — the colleague who was
 * named as witness confirms it on their OWN account, not by leaning over and
 * tapping someone else's screen.
 *
 * UNCLUTTERED: one card per signature, each showing exactly what is being
 * co-signed and nothing else. The manager override is deliberately tucked behind
 * a disclosure — it is the rare path, and it must never look as easy as the
 * correct one.
 */

function Signature({ item, isManager }) {
    const [overriding, setOverriding] = useState(false);

    const confirmForm = useForm({});
    const overrideForm = useForm({ override_reason: '' });

    const confirm = (e) => {
        e.preventDefault();
        confirmForm.post(`/frontend3/signatures/${item.id}/confirm`, { preserveScroll: true });
    };

    const override = (e) => {
        e.preventDefault();
        overrideForm.post(`/frontend3/signatures/${item.id}/override`, {
            preserveScroll: true,
            onSuccess: () => setOverriding(false),
        });
    };

    const movement = [
        item.dose_quantity ? `${item.dose_quantity}${item.unit ? ` ${item.unit}` : ''}` : null,
        item.action_type,
    ].filter(Boolean).join(' · ');

    return (
        <Card>
            <CardHead
                title={item.medication_name}
                sub={[item.client_name, item.entry_date, item.entry_time].filter(Boolean).join(' · ')}
            >
                <Badge tone="risk">Controlled drug</Badge>
            </CardHead>

            <dl className="f3-dl">
                {movement && (<><dt>Movement</dt><dd>{movement}</dd></>)}
                {item.balance_after !== null && item.balance_after !== undefined && (
                    <><dt>Balance after</dt><dd className="f3-tabnum">{item.balance_after}{item.unit ? ` ${item.unit}` : ''}</dd></>
                )}
                <dt>Recorded by</dt><dd>{item.recorded_by || '—'}</dd>
                <dt>Named witness</dt><dd>{item.witness_name || '—'} <span className="f3-mut">(you)</span></dd>
            </dl>

            <div className="f3-alert f3-alert--info" style={{ marginTop: 'var(--f3-s4)' }}>
                <span className="f3-alert-mark" aria-hidden="true">◐</span>
                <div>
                    <div className="f3-alert-title">What you are confirming</div>
                    <div className="f3-alert-text">
                        That you witnessed this movement as recorded. This is your own signature on
                        the controlled-drug register — sign it only if you were there.
                    </div>
                </div>
            </div>

            <form onSubmit={confirm} style={{ marginTop: 'var(--f3-s4)' }}>
                <button className="f3-btn f3-btn--primary f3-btn--block" disabled={confirmForm.processing}>
                    {confirmForm.processing ? 'Confirming…' : 'I witnessed this — confirm my signature'}
                </button>
            </form>

            {isManager && !overriding && (
                <button
                    type="button"
                    className="f3-btn f3-btn--ghost f3-btn--sm"
                    style={{ marginTop: 'var(--f3-s2)' }}
                    onClick={() => setOverriding(true)}
                >
                    Manager override instead…
                </button>
            )}

            {isManager && overriding && (
                <form onSubmit={override} className="f3-stack" style={{ marginTop: 'var(--f3-s4)', gap: 'var(--f3-s3)' }}>
                    <div className="f3-alert">
                        <span className="f3-alert-mark" aria-hidden="true">▲</span>
                        <div>
                            <div className="f3-alert-title">This is not a witness signature</div>
                            <div className="f3-alert-text">
                                An override records that a manager resolved this signature <b>on the
                                witness's behalf</b>. The register will show it as manager-overridden,
                                not witness-confirmed. Use it only when the witness genuinely cannot sign.
                            </div>
                        </div>
                    </div>

                    <div className="f3-field">
                        <label className="f3-label" htmlFor={`reason-${item.id}`}>
                            Why is the witness not signing? <span className="f3-req" aria-hidden="true">*</span>
                        </label>
                        <input
                            id={`reason-${item.id}`}
                            className="f3-input"
                            value={overrideForm.data.override_reason}
                            onChange={(e) => overrideForm.setData('override_reason', e.target.value)}
                            placeholder="e.g. left the service, long-term absence"
                        />
                        {overrideForm.errors.override_reason && (
                            <p className="f3-error">{overrideForm.errors.override_reason}</p>
                        )}
                    </div>

                    <div className="f3-row">
                        <button
                            className="f3-btn"
                            disabled={overrideForm.processing || !overrideForm.data.override_reason.trim()}
                        >
                            {overrideForm.processing ? 'Recording…' : 'Record manager override'}
                        </button>
                        <button type="button" className="f3-btn f3-btn--ghost" onClick={() => setOverriding(false)}>
                            Cancel
                        </button>
                    </div>
                </form>
            )}
        </Card>
    );
}

export default function Witness({ auth, pending = [], isManager }) {
    return (
        <F3Shell
            title="Signatures awaiting you"
            area="operations"
            user={auth?.user}
            heading="Signatures awaiting you"
            summary={
                pending.length === 0
                    ? 'Nothing is waiting for your signature.'
                    : `${pending.length} controlled-drug ${pending.length === 1 ? 'movement needs' : 'movements need'} your confirmation as the named witness.`
            }
            context={[{ label: 'Controlled drugs', strong: true }]}
            action={<a className="f3-btn" href="/frontend3">← Today</a>}
        >

            {pending.length === 0 ? (
                <Empty title="Nothing awaiting your signature">
                    When a colleague records a controlled drug and names you as the witness, it
                    appears here for you to confirm on your own account.
                </Empty>
            ) : (
                <div className="f3-stack" style={{ maxWidth: 720 }}>
                    {pending.map((item) => (
                        <Signature key={item.id} item={item} isManager={isManager} />
                    ))}
                </div>
            )}

            <Note>
                Only the named witness can confirm a signature — a colleague cannot sign on your
                behalf, and you cannot sign for them. Self-witnessing is impossible: the witness
                list at administration never includes the person recording the dose.
            </Note>

        </F3Shell>
    );
}
