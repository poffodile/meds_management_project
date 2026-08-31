import React from 'react';
import Icon from './Icon.jsx';
import TextLink from './TextLink.jsx';

/**
 * One thing that is wrong, and what to do about it.
 *
 * Four things and no more: who, what is wrong in plain words, how urgent, and
 * what to do next. No medicine names — knowing it is co-careldopa does not
 * change the next action, and carrying it pushed the action itself off the
 * bottom of a phone.
 *
 * Urgency is carried by the word and by a rail down the left edge, never by
 * colour alone: this gets read on a cracked phone in a corridor, and some of
 * the people reading it are colour-blind.
 */
const KINDS = {
    late_time_critical: { rail: 'critical', label: 'Now' },
    late: { rail: 'warning', label: 'Late' },
    follow_up: { rail: 'warning', label: 'Unanswered' },
    not_available: { rail: 'warning', label: 'Out of stock' },
    refused: { rail: 'notice', label: 'Refused' },
    changed: { rail: 'notice', label: 'Changed' },
};

function waited(minutes) {
    if (minutes < 1) return null;
    if (minutes < 60) return `${minutes} min`;

    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;

    return rest ? `${hours}h ${rest}m` : `${hours}h`;
}

/**
 * The way to the thing, where there is one.
 *
 * A row that says what to do and offers no way to do it teaches people to work
 * around the screen. Not every kind has a destination yet, so the control is
 * rendered only when one was supplied — an absent link says "not from here",
 * which is honest, where a dead one would not be.
 *
 * It is a control of its own and not the whole row: this list is read far more
 * often than it is acted on, and a row that navigates under a thumb resting on
 * a phone in a corridor is the wrong habit to teach on a medicines product.
 */
export default function AttentionRow({ item, href = null, action = 'Open' }) {
    const kind = KINDS[item.kind] ?? KINDS.late;
    const ago = waited(item.minutes);

    return (
        <li className={`r7-attention r7-attention--${kind.rail}`}>
            <span className="r7-attention__label">
                {kind.rail === 'critical' ? (
                    <Icon name="warning" className="r7-icon r7-icon--small" />
                ) : null}
                {kind.label}
            </span>

            <span className="r7-attention__who">
                {item.client}
                {item.room ? <span className="r7-attention__room">{item.room}</span> : null}
            </span>

            <span className="r7-attention__problem">{item.problem}</span>

            <span className="r7-attention__next">{item.next}</span>

            {href ? (
                <TextLink className="r7-attention__go" href={href}>
                    {action}
                </TextLink>
            ) : null}

            <span className="r7-attention__when">
                {item.dueAt ? <span>due {item.dueAt}</span> : null}
                {ago ? <span className="r7-attention__waiting">{ago} ago</span> : null}
            </span>
        </li>
    );
}
