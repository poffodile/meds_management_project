import React from 'react';
import { router } from '@inertiajs/react';

/**
 * Record7's navigation, in both of its shapes.
 *
 * On desktop it is a sidebar. On a phone it is a bar pinned to the bottom,
 * under the thumb, because a round is worked one-handed while holding
 * something else. Same items, same order, same words — only the arrangement
 * changes, and it changes in CSS rather than by rendering two different trees.
 *
 * Items a person has no permission for are not rendered at all. That is a
 * courtesy: the server refuses them regardless.
 */
export default function AppNav({ items = [], variant = 'sidebar' }) {
    const visible = items.filter((item) => item.available !== false);

    if (variant === 'tabbar') {
        return (
            <nav className="r7-tabbar" aria-label="Sections">
                {visible.map((item) => (
                    <button
                        key={item.key}
                        type="button"
                        className={`r7-tabbar__item${item.current ? ' r7-tabbar__item--on' : ''}`}
                        onClick={() => router.get(item.href)}
                        aria-current={item.current ? 'page' : undefined}
                    >
                        {item.label}
                    </button>
                ))}
            </nav>
        );
    }

    return (
        <nav className="r7-nav" aria-label="Sections">
            {visible.map((item) => (
                <button
                    key={item.key}
                    type="button"
                    className={`r7-nav__item${item.current ? ' r7-nav__item--on' : ''}`}
                    onClick={() => router.get(item.href)}
                    aria-current={item.current ? 'page' : undefined}
                >
                    {item.label}
                </button>
            ))}
        </nav>
    );
}
