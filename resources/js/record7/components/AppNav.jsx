import React from 'react';
import { router } from '@inertiajs/react';
import Icon from './Icon.jsx';

/**
 * Record7's navigation.
 *
 * ONE TREE, TWO SHAPES.
 * On desktop it is a slim rail that widens on hover or keyboard focus and
 * pushes the workspace across rather than covering it — so nothing you were
 * reading is ever hidden by the thing you are navigating with. On a phone the
 * same list opens as a drawer from the side.
 *
 * IT IS BUILT TO GROW.
 * Two destinations exist today because two pages exist. Nothing here is padded
 * out with links that go nowhere: an item a person cannot reach is not
 * rendered, and the rail is sized by what is in it. Adding the Medication Round
 * later is one more entry in the array.
 *
 * The label is always in the DOM, never swapped in and out. Collapsing hides it
 * with width and opacity, so a screen reader always has the words and the
 * button always has an accessible name — a rail whose labels only exist while
 * hovered is unusable to anyone not using a mouse.
 */
export default function AppNav({ items = [], onNavigate = null }) {
    const visible = items.filter((item) => item.available !== false);

    const go = (item) => {
        if (onNavigate) onNavigate();
        router.get(item.href);
    };

    return (
        <ul className="r7-nav" role="list">
            {visible.map((item) => (
                <li key={item.key ?? item.label}>
                    <button
                        type="button"
                        className={`r7-nav__item${item.current ? ' r7-nav__item--on' : ''}`}
                        onClick={() => go(item)}
                        aria-current={item.current ? 'page' : undefined}
                    >
                        <span className="r7-nav__icon" aria-hidden="true">
                            <Icon name={item.icon ?? 'house'} className="r7-icon" />
                        </span>
                        <span className="r7-nav__label">{item.label}</span>
                    </button>
                </li>
            ))}
        </ul>
    );
}
