import React from 'react';

const TAGS = { problem: 'Problem', ok: 'Done', attention: 'Check', info: 'Note' };

/**
 * A status message.
 *
 * The kind is always stated in words as well as colour, so it reads correctly
 * in greyscale and to a screen reader. Problems announce themselves; the rest
 * are polite.
 */
export default function Notice({ tone = 'info', tag = null, children }) {
    if (!children) return null;

    return (
        <div className={`r7-notice r7-notice--${tone}`} role={tone === 'problem' ? 'alert' : 'status'}>
            <span className="r7-notice__tag">{tag ?? TAGS[tone] ?? TAGS.info}</span>
            <span className="r7-notice__text">{children}</span>
        </div>
    );
}
