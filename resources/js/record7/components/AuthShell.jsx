import React from 'react';
import { usePage } from '@inertiajs/react';
import Mark from './Mark.jsx';
import TextLink from './TextLink.jsx';
import ThemeToggle from './ThemeToggle.jsx';
import CareOneOsBadge from './CareOneOsBadge.jsx';
import Icon from './Icon.jsx';
import useProduct from '../useProduct.js';

/**
 * The frame every Record7 sign-in screen sits in.
 *
 * TWO HALVES, NOT A CARD IN THE MIDDLE
 * A centred card on an empty page is the layout every product reaches for, and
 * it reads as unconsidered for exactly that reason. Here the deep half carries
 * the product and what it stands for, and the warm half carries the work.
 *
 * On a phone the deep half becomes a compact band across the top, because a
 * full-height brand panel above the form would push the fields below the fold,
 * and the fields are the reason anyone opened the page.
 *
 * There is no navigation before a successful sign-in, by design: nothing should
 * be reachable, or look reachable, until the person is known.
 */

/**
 * The six recognised rights of medicines administration, and Omega's seventh.
 *
 * Real content rather than decoration — this is the idea the product is named
 * after, and staff are taught these in this order. Numbering an actual
 * enumerated set is information; numbering three marketing points would not be.
 */
const RIGHTS = [
    'The right person',
    'The right medicine',
    'The right route',
    'The right dose',
    'The right time',
    'The right to decline',
];

export default function AuthShell({
    step = null,
    // Where "go back" leads, if anywhere. {href, label, method}. Screens that
    // genuinely have nothing behind them pass nothing, rather than showing a
    // control that returns you to where you already are.
    back = null,
    stepCount = null,
    title,
    intro = null,
    wide = false,
    children,
    footer = null,
}) {
    const product = useProduct();
    const { journeySteps } = usePage().props;

    // The journey is three steps, or four when verification is switched on.
    // Taken from the server so no screen can contradict another.
    const total = stepCount ?? journeySteps ?? 3;

    return (
        <div className="r7-auth">

            {/* ── The deep half ─────────────────────────────────────────── */}
            <aside className="r7-brand">
                <div className="r7-brand__top">
                    <Mark productName={product.name} strapline={product.strapline} />
                </div>

                <div className="r7-brand__middle">
                    <p className="r7-brand__lede">
                        Safe medication practice is not finished until it has been{' '}
                        <em className="r7-brand__accent">recorded</em>.
                    </p>

                    <div className="r7-rights">
                        <span className="r7-label">The seven rights</span>

                        <ol className="r7-rights__list">
                            {RIGHTS.map((right, index) => (
                                <li className="r7-right" key={right}>
                                    <span className="r7-right__number">{index + 1}</span>
                                    <span className="r7-right__name">{right}</span>
                                </li>
                            ))}

                            <li className="r7-right r7-right--seventh">
                                <span className="r7-right__number">7</span>
                                <span className="r7-right__name">{product.seventhRight}</span>
                                <span className="r7-right__tick">
                                    <Icon name="tick" className="r7-icon r7-icon--small" />
                                </span>
                            </li>
                        </ol>
                    </div>
                </div>

                <div className="r7-brand__foot">
                    <p className="r7-brand__note">
                        <Icon name="lock" className="r7-icon r7-icon--small" />
                        <span>Authorised users only. Access is recorded.</span>
                    </p>
                    <CareOneOsBadge tone="onDark" />
                </div>
            </aside>

            {/* ── The warm half ─────────────────────────────────────────── */}
            <main className="r7-auth__form">
                <div className="r7-auth__body">
                    <section className={`r7-card${wide ? ' r7-card--wide' : ''}`}>
                        <div className="r7-card__head">
                            <div className="r7-card__meta">
                                {back ? (
                                    <TextLink
                                        className="r7-back"
                                        href={back.href}
                                        method={back.method}
                                        aria-label={back.label}
                                        title={back.label}
                                    >
                                        <Icon name="arrow" className="r7-icon r7-icon--small" />
                                        <span className="r7-sr-only">{back.label}</span>
                                    </TextLink>
                                ) : step ? (
                                    <span className="r7-card__steps">
                                        <span className="r7-label">Step {step} of {total}</span>
                                        <span className="r7-steps" aria-hidden="true">
                                            {Array.from({ length: total }, (_, index) => (
                                                <span
                                                    key={index}
                                                    className={`r7-steps__bar${index < step ? ' r7-steps__bar--on' : ''}`}
                                                />
                                            ))}
                                        </span>
                                    </span>
                                ) : <span />}

                                {/* When both a step and a back exist, the step
                                    moves under the heading so the row keeps its
                                    two ends and nothing is squeezed. */}
                                {back && step ? (
                                    <span className="r7-label">Step {step} of {total}</span>
                                ) : null}

                                <ThemeToggle />
                            </div>

                            <h1 className="r7-title">{title}</h1>
                            {intro ? <p className="r7-muted r7-measure">{intro}</p> : null}
                        </div>

                        <div className="r7-card__body">{children}</div>

                        {footer ? <div className="r7-card__foot">{footer}</div> : null}
                    </section>

                    <footer className="r7-auth__foot">
                        <CareOneOsBadge tone="onLight" />
                        <span>Authorised users only. Access is recorded.</span>
                    </footer>
                </div>
            </main>
        </div>
    );
}
