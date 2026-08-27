import React from 'react';
import { router } from '@inertiajs/react';
import StatusLabel from './StatusLabel.jsx';
import Button from './Button.jsx';
import Icon from './Icon.jsx';

/**
 * What the last shift left behind — at most three things.
 *
 * Three because a briefing somebody actually reads standing in a corridor is
 * three lines long. The fourteen-line version gets scrolled past, including
 * the two that mattered. Anything beyond three is counted, not hidden: you are
 * told it exists.
 *
 * And it can be acknowledged. A handover nobody confirms reading is a handover
 * that was not handed over — the confirmation is what turns notes on a screen
 * into a transfer of responsibility between two people.
 */
const PRIORITY = {
    urgent: { tone: 'error', word: 'Urgent' },
    important: { tone: 'warning', word: 'Important' },
    routine: { tone: 'neutral', word: 'Routine' },
};

export default function HandoverPanel({ handover, readUrl }) {
    if (!handover) return null;

    const notes = handover.notes ?? [];

    return (
        <div className="r7-handover">
            <p className="r7-handover__from">
                <strong>{handover.shift}</strong>
                <span>ended {handover.endedAt}</span>
                {handover.writtenBy ? <span>written by {handover.writtenBy}</span> : null}
            </p>

            {handover.summary ? <p className="r7-handover__summary">{handover.summary}</p> : null}

            <ul className="r7-notes">
                {notes.map((note) => (
                    <li className={`r7-note r7-note--${note.priority}`} key={note.id}>
                        <span className="r7-note__head">
                            <StatusLabel tone={PRIORITY[note.priority]?.tone ?? 'neutral'}>
                                {PRIORITY[note.priority]?.word ?? note.priority}
                            </StatusLabel>
                            {note.client ? <span className="r7-note__who">{note.client}</span> : null}
                        </span>
                        <span className="r7-note__text">{note.note}</span>
                    </li>
                ))}
            </ul>

            <div className="r7-handover__foot">
                {handover.moreCount ? (
                    <span className="r7-handover__more">
                        {handover.moreCount} more {handover.moreCount === 1 ? 'note' : 'notes'} in the full handover
                    </span>
                ) : <span />}

                {handover.readAt ? (
                    <span className="r7-handover__read">
                        <Icon name="tick" className="r7-icon r7-icon--small" />
                        You confirmed this at {handover.readAt}
                    </span>
                ) : (
                    <Button
                        variant="secondary"
                        size="small"
                        onClick={() => router.post(readUrl, { handover_id: handover.id })}
                    >
                        I have read this
                    </Button>
                )}
            </div>
        </div>
    );
}
