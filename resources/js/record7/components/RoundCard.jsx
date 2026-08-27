import React from 'react';
import Notice from './Notice.jsx';

/**
 * Where the current round has got to, and what comes after it.
 *
 * Three numbers and a line. Not a chart, and not the day's medication
 * schedule — "what comes next" is the next round and when, which is what
 * decides whether you have time for a cup of tea, not a list of every dose
 * between now and bedtime.
 *
 * There is no button here. Starting the round is the page's one primary action
 * and it lives in the anchor at the top; two primary actions on one screen
 * means neither is primary.
 */
export default function RoundCard({ round, overview, next, canRecord, refusal }) {
    if (!round) return null;

    const done = round.state === 'done';
    const started = round.state === 'in_progress';
    const percent = overview.total ? Math.round((overview.recorded / overview.total) * 100) : 0;

    return (
        <div className="r7-round">
            <div className="r7-round__figures">
                <span className="r7-figure">
                    <span className="r7-figure__value">{overview.remaining}</span>
                    <span className="r7-figure__label">still to record</span>
                </span>
                <span className="r7-figure">
                    <span className="r7-figure__value">{overview.recorded}</span>
                    <span className="r7-figure__label">recorded today</span>
                </span>
                {overview.notTaken ? (
                    <span className="r7-figure">
                        <span className="r7-figure__value">{overview.notTaken}</span>
                        <span className="r7-figure__label">not taken</span>
                    </span>
                ) : null}
            </div>

            <div
                className="r7-progress"
                role="progressbar"
                aria-valuenow={overview.recorded}
                aria-valuemin={0}
                aria-valuemax={overview.total}
                aria-label={`${overview.recorded} of ${overview.total} doses recorded today`}
            >
                <span className="r7-progress__fill" style={{ width: `${percent}%` }} />
            </div>

            {done ? (
                <p className="r7-round__state">Every dose planned for today has been recorded.</p>
            ) : (
                <div className="r7-round__action">
                    <div className="r7-round__what">
                        <span className="r7-round__slot">{round.slot} round</span>
                        <span className="r7-round__detail">
                            {round.remaining} of {round.total} left
                            {started ? ` — started ${round.startedAt}` : ` — due ${round.dueAt}`}
                        </span>
                    </div>

                    {next ? (
                        <div className="r7-round__next">
                            <span className="r7-label">Next</span>
                            <span>{next.slot} at {next.at}</span>
                        </div>
                    ) : null}
                </div>
            )}

            {!canRecord && refusal ? <Notice tone="warning">{refusal}</Notice> : null}
        </div>
    );
}
