import React from 'react';
import Mark from './Mark.jsx';
import ThemeToggle from './ThemeToggle.jsx';
import useProduct from '../useProduct.js';

/**
 * The frame every Record7 sign-in screen sits in.
 *
 * One centred card on warm cream, mark above, footing below. There is no
 * sidebar and no navigation before a successful sign-in, by design: nothing
 * should be reachable, or look reachable, until the person is known.
 */
export default function AuthShell({
    step = null,
    stepCount = 3,
    title,
    intro = null,
    wide = false,
    children,
    footer = null,
}) {
    const product = useProduct();

    return (
        <div className="r7-auth">
            <header className="r7-auth__head">
                <Mark productName={product.name} strapline={product.strapline} />
                <ThemeToggle />
            </header>

            <main className="r7-auth__body">
                <section className={`r7-card${wide ? ' r7-card--wide' : ''}`}>
                    <div className="r7-card__head">
                        {step ? (
                            <div className="r7-row" style={{ gap: 12 }}>
                                <span className="r7-label">Step {step} of {stepCount}</span>
                                <span className="r7-steps" aria-hidden="true">
                                    {Array.from({ length: stepCount }, (_, index) => (
                                        <span
                                            key={index}
                                            className={`r7-steps__bar${index < step ? ' r7-steps__bar--on' : ''}`}
                                        />
                                    ))}
                                </span>
                            </div>
                        ) : null}

                        <h1 className="r7-title">{title}</h1>
                        {intro ? <p className="r7-muted r7-measure">{intro}</p> : null}
                    </div>

                    <div className="r7-card__body">{children}</div>

                    {footer ? <div className="r7-card__foot">{footer}</div> : null}
                </section>
            </main>

            <footer className="r7-auth__foot">
                <span>{product.seventhRight}</span>
                <span>Authorised users only. Access is recorded.</span>
            </footer>
        </div>
    );
}
