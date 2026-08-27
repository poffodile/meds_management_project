import React, { useEffect, useRef, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import Mark from './Mark.jsx';
import ThemeToggle from './ThemeToggle.jsx';
import AppNav from './AppNav.jsx';
import Button from './Button.jsx';
import Icon from './Icon.jsx';
import useProduct from '../useProduct.js';

/**
 * The frame every signed-in Record7 screen sits in.
 *
 * WHERE YOU ARE NEVER SCROLLS AWAY.
 * The organisation and the house stay on screen at all times. On a medicines
 * product the most dangerous ambiguity is "which house am I recording
 * against", so Record7 never lets that leave the viewport.
 *
 * NAVIGATION THAT TAKES WHAT IT NEEDS AND NO MORE.
 * On desktop a slim rail of icons, which widens on hover or keyboard focus and
 * pushes the workspace across instead of covering it. On a phone the same list
 * opens as a drawer from the side — never a bar across the bottom, which on
 * this product would sit under the one thing a person is trying to read while
 * standing at a trolley.
 */
export default function AppShell({ urls = {}, nav = [], children }) {
    const product = useProduct();
    const { context } = usePage().props;

    const [drawerOpen, setDrawerOpen] = useState(false);
    const drawerRef = useRef(null);
    const openerRef = useRef(null);

    const post = (url) => url && router.post(url);

    // Escape closes it, and focus goes back to the control that opened it —
    // otherwise a keyboard user is left at the top of the document.
    useEffect(() => {
        if (!drawerOpen) return undefined;

        const onKey = (event) => {
            if (event.key === 'Escape') {
                setDrawerOpen(false);
                openerRef.current?.focus();
            }
        };

        document.addEventListener('keydown', onKey);
        drawerRef.current?.querySelector('button')?.focus();

        return () => document.removeEventListener('keydown', onKey);
    }, [drawerOpen]);

    const hasNav = nav.filter((item) => item.available !== false).length > 0;

    return (
        <div className={`r7-shell${drawerOpen ? ' r7-shell--drawer' : ''}`}>
            <header className="r7-top">
                <div className="r7-top__row">
                    {hasNav ? (
                        <button
                            type="button"
                            ref={openerRef}
                            className="r7-burger"
                            onClick={() => setDrawerOpen(true)}
                            aria-expanded={drawerOpen}
                            aria-controls="r7-drawer"
                            aria-label="Open the menu"
                        >
                            <Icon name="menu" className="r7-icon" />
                        </button>
                    ) : null}

                    <Mark productName={product.name} />

                    <div className="r7-top__actions">
                        <ThemeToggle />
                        {urls.lock ? (
                            <Button variant="quiet" size="small" onClick={() => post(urls.lock)}>
                                Lock
                            </Button>
                        ) : null}
                        <button type="button" className="r7-btn r7-btn--bare" onClick={() => post(urls.signOut)}>
                            Sign out
                        </button>
                    </div>
                </div>

                <div className="r7-top__row">
                    <div className="r7-where">
                        <span className="r7-where__cell">
                            <span className="r7-where__label">Organisation</span>
                            <span className="r7-where__value">{context?.organisation ?? 'Not set'}</span>
                        </span>
                        <span className="r7-where__cell">
                            <span className="r7-where__label">House</span>
                            <span className="r7-where__value">{context?.house ?? 'Not chosen'}</span>
                        </span>
                        {context?.role ? (
                            <span className="r7-where__cell">
                                <span className="r7-where__label">Your role</span>
                                <span className="r7-where__value">{context.role}</span>
                            </span>
                        ) : null}
                    </div>

                    {context?.houseCount > 1 && urls.houses ? (
                        <Button variant="quiet" size="small" onClick={() => router.get(urls.houses)}>
                            Switch house
                        </Button>
                    ) : null}
                </div>
            </header>

            <div className="r7-body">
                {hasNav ? (
                    <>
                        {/* The desktop rail. Widening is pure CSS on :hover and
                            :focus-within, so it opens for a pointer and for a
                            tab key alike, and closes when either leaves. */}
                        <nav className="r7-rail" aria-label="Record7 sections">
                            <AppNav items={nav} />
                        </nav>

                        {/* The phone drawer. Same items, same order, same words. */}
                        <div
                            className={`r7-scrim${drawerOpen ? ' r7-scrim--on' : ''}`}
                            onClick={() => setDrawerOpen(false)}
                            aria-hidden="true"
                        />

                        <nav
                            className={`r7-drawer${drawerOpen ? ' r7-drawer--open' : ''}`}
                            id="r7-drawer"
                            ref={drawerRef}
                            aria-label="Record7 sections"
                            aria-hidden={!drawerOpen}
                            {...(drawerOpen ? {} : { inert: '' })}
                        >
                            <div className="r7-drawer__head">
                                <Mark productName={product.name} />
                                <button
                                    type="button"
                                    className="r7-drawer__close"
                                    onClick={() => {
                                        setDrawerOpen(false);
                                        openerRef.current?.focus();
                                    }}
                                    aria-label="Close the menu"
                                >
                                    <Icon name="close" className="r7-icon" />
                                </button>
                            </div>

                            <AppNav items={nav} onNavigate={() => setDrawerOpen(false)} />
                        </nav>
                    </>
                ) : null}

                <main className="r7-main">{children}</main>
            </div>
        </div>
    );
}
