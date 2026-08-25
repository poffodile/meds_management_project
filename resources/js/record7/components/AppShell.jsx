import React from 'react';
import { router, usePage } from '@inertiajs/react';
import Mark from './Mark.jsx';
import ThemeToggle from './ThemeToggle.jsx';
import useProduct from '../useProduct.js';

/**
 * The frame every signed-in Record7 screen sits in.
 *
 * The organisation and the house stay on the screen at all times. On a
 * medicines product the most dangerous ambiguity is "which house am I
 * recording against", so Record7 never lets that scroll away.
 *
 * Switch house appears only when there is more than one to switch to.
 */
export default function AppShell({ urls = {}, children }) {
    const product = useProduct();
    const { context } = usePage().props;

    const post = (url) => url && router.post(url);

    return (
        <div className="r7-shell">
            <header className="r7-top">
                <div className="r7-top__row">
                    <Mark productName={product.name} />

                    <div className="r7-top__actions">
                        <ThemeToggle />
                        {urls.lock ? (
                            <button type="button" className="r7-btn r7-btn--quiet" onClick={() => post(urls.lock)}>
                                Lock
                            </button>
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
                        <button
                            type="button"
                            className="r7-btn r7-btn--quiet"
                            onClick={() => router.get(urls.houses)}
                        >
                            Switch house
                        </button>
                    ) : null}
                </div>
            </header>

            <main className="r7-main">{children}</main>
        </div>
    );
}
