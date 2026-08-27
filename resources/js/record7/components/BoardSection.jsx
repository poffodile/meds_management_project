import React from 'react';

/**
 * One band of the Today screen.
 *
 * Six of these stacked in a fixed order ARE the dashboard. The order is
 * clinical, not visual: handover, then what is wrong, then where the day is,
 * then who is waiting, then what is on you, then what is already done. On a
 * phone that is also the scroll order, which is why nothing is in a column
 * beside anything else.
 *
 * The count sits in the heading rather than inside a badge on a card, because
 * the question "how many" is asked before the section is read, not after.
 */
export default function BoardSection({ title, count = null, note = null, quiet = false, children }) {
    return (
        <section className={`r7-board${quiet ? ' r7-board--quiet' : ''}`}>
            <div className="r7-board__head">
                <h2 className="r7-board__title">{title}</h2>
                {count !== null ? <span className="r7-board__count">{count}</span> : null}
                {note ? <span className="r7-board__note">{note}</span> : null}
            </div>

            <div className="r7-board__body">{children}</div>
        </section>
    );
}
