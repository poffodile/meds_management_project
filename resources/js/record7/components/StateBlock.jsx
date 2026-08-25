import React from 'react';
import Button from './Button.jsx';

/**
 * The screen has nothing to show, and says why.
 *
 * One component for loading, empty, offline, restricted and error, because
 * they are the same shape: a word for what is happening, a plain sentence, and
 * where useful a way out. Five separate components would drift apart.
 *
 * Every one of them names the state in words. "Nothing here" and "we could not
 * reach the network" are different problems and must never look the same.
 */
const TAGS = {
    loading: 'Loading',
    empty: 'Nothing yet',
    offline: 'No connection',
    restricted: 'Not available to you',
    error: 'Something went wrong',
};

export default function StateBlock({ state = 'empty', title, children, action = null }) {
    if (state === 'loading') {
        return (
            <div className="r7-state" role="status" aria-live="polite">
                <span className="r7-state__tag">{TAGS.loading}</span>
                <span className="r7-sr-only">{title ?? 'Loading'}</span>
                <span className="r7-skeleton r7-skeleton--title" aria-hidden="true" />
                <span className="r7-skeleton r7-skeleton--line" aria-hidden="true" />
                <span className="r7-skeleton r7-skeleton--short" aria-hidden="true" />
            </div>
        );
    }

    return (
        <div
            className={`r7-state r7-state--${state}`}
            role={state === 'error' ? 'alert' : 'status'}
        >
            <span className="r7-state__tag">{TAGS[state] ?? TAGS.empty}</span>
            {title ? <span className="r7-state__title">{title}</span> : null}
            {children ? <span className="r7-state__body">{children}</span> : null}
            {action ? (
                <Button variant={state === 'error' ? 'primary' : 'quiet'} onClick={action.onClick}>
                    {action.label}
                </Button>
            ) : null}
        </div>
    );
}
