import React from 'react';
import StatusLabel from './StatusLabel.jsx';

/**
 * A follow-up that still needs an answer.
 *
 * Giving somebody something for pain and never asking whether it worked is how
 * a person stays in pain all afternoon and nobody notices. This is the part of
 * an as-required medicine most likely to fall down the gap between two shifts.
 *
 * WHY, not WHAT. "For back pain" is what makes "did it work?" answerable; the
 * drug name is not, and it belongs on the round screen. Whose task it is is
 * marked, but nothing is hidden — a follow-up the night shift left open must
 * not be invisible to the person now on duty.
 */
export default function TaskRow({ task }) {
    return (
        <li className={`r7-task${task.overdue ? ' r7-task--overdue' : ''}`}>
            <span className="r7-task__head">
                <span className="r7-task__who">{task.client}</span>
                {task.room ? <span className="r7-task__room">{task.room}</span> : null}
                {task.mine ? <StatusLabel tone="info">Yours</StatusLabel> : null}
            </span>

            <span className="r7-task__ask">
                Did it work?
                {task.indication ? <span className="r7-task__why">{task.indication}</span> : null}
            </span>

            <span className="r7-task__when">
                <span>Given {task.givenAt}{task.givenBy ? ` by ${task.givenBy}` : ''}</span>
                <span className="r7-task__due">
                    {task.overdue ? `Answer due ${task.waitingFor}` : `Ask ${task.waitingFor}`}
                </span>
            </span>
        </li>
    );
}
