import React from 'react';
import { router, useForm } from '@inertiajs/react';
import Mark from '@record7/components/Mark.jsx';
import ThemeToggle from '@record7/components/ThemeToggle.jsx';
import PasswordField from '@record7/components/PasswordField.jsx';
import Button from '@record7/components/Button.jsx';
import Notice from '@record7/components/Notice.jsx';
import useProduct from '@record7/useProduct.js';

/**
 * The lock screen.
 *
 * Locking is not signing out. The session and the chosen house survive, and
 * only the password is asked for — which is the difference between a control
 * staff actually use on a round and one they work around.
 *
 * The house stays named on screen while locked, so whoever picks the device up
 * can see what it is open on before they unlock it. The person's name is shown
 * because they are already known to this device; nothing else about the house
 * or its people is.
 */
export default function Lock({ name, fullName, house, organisationName, unlockUrl, signOutUrl, error }) {
    const product = useProduct();
    const { data, setData, post, processing, errors } = useForm({ password: '' });

    const submit = (event) => {
        event.preventDefault();
        post(unlockUrl, { onFinish: () => setData('password', '') });
    };

    return (
        <div className="r7-auth">
            <header className="r7-auth__head">
                <Mark productName={product.name} strapline={product.strapline} />
                <ThemeToggle />
            </header>

            <main className="r7-auth__body">
                <section className="r7-card">
                    <div className="r7-card__head">
                        <span className="r7-label">Screen locked</span>
                        <h1 className="r7-title">Welcome back{name ? <>, {name}</> : ''}</h1>
                        <p className="r7-muted">Enter your password to carry on where you left off.</p>
                    </div>

                    <div className="r7-card__body">
                        <form className="r7-form" onSubmit={submit} noValidate>
                            <Notice tone="error">{error}</Notice>

                            <div className="r7-where">
                                <span className="r7-where__cell">
                                    <span className="r7-where__label">Organisation</span>
                                    <span className="r7-where__value">{organisationName ?? 'Not set'}</span>
                                </span>
                                <span className="r7-where__cell">
                                    <span className="r7-where__label">House</span>
                                    <span className="r7-where__value">{house ?? 'Not chosen'}</span>
                                </span>
                            </div>

                            <PasswordField
                                label={`Password for ${fullName ?? name ?? 'this account'}`}
                                value={data.password}
                                onChange={(value) => setData('password', value)}
                                error={errors.password}
                                autoComplete="current-password"
                                enterKeyHint="go"
                                autoFocus
                                required
                            />

                            <Button type="submit" block busy={processing} busyLabel="Unlocking">
                                Unlock
                            </Button>
                        </form>
                    </div>

                    <div className="r7-card__foot">
                        <div className="r7-between">
                            <span>Not you?</span>
                            <button
                                type="button"
                                className="r7-btn r7-btn--bare"
                                onClick={() => router.post(signOutUrl)}
                            >
                                Sign out
                            </button>
                        </div>
                    </div>
                </section>
            </main>

            <footer className="r7-auth__foot">
                <span>{product.seventhRight}</span>
                <span>Locking and unlocking are recorded.</span>
            </footer>
        </div>
    );
}
