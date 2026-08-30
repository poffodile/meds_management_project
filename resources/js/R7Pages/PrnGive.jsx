import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import PersonIdentity from '@record7/components/PersonIdentity.jsx';
import AllergyWarning from '@record7/components/AllergyWarning.jsx';
import SupportType from '@record7/components/SupportType.jsx';
import StatusLabel from '@record7/components/StatusLabel.jsx';
import Notice from '@record7/components/Notice.jsx';
import Button from '@record7/components/Button.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Icon from '@record7/components/Icon.jsx';

/**
 * Section 2.4 — giving a medicine because somebody needs it now.
 *
 * SHOW THE ARITHMETIC, NOT A VERDICT.
 * "You cannot give this" tells a worker nothing they can act on and nothing
 * they can question. When the last dose was, when the next is due, how many
 * they have had and how much — those are the facts somebody needs to decide
 * whether to wait, call the GP, or try something that is not a medicine.
 *
 * WHERE THE PRESCRIPTION IS SILENT, SO IS THIS SCREEN.
 * No invented maximum, no assumed interval, no default review time. A limit
 * nobody wrote down is a limit nobody agreed to, and showing one would be
 * telling a worker something untrue about somebody's prescription.
 */
export default function PrnGive({
    house, person, safety, medicine, observedReasons, authority, attemptToken,
    stage, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock' },
    ];

    const fixedDose = medicine.doseMin !== null && !medicine.doseIsRange;

    const { data, setData, post, processing, errors } = useForm({
        dose_amount: fixedDose ? String(medicine.doseMin) : '',
        observed_reason: '',
        notes: '',

        // Issued by the server for this attempt. Sent back untouched so a
        // double-click or a retry is recognised as the same attempt rather
        // than a second dose. The button being disabled is a courtesy; this
        // is what actually prevents it.
        attempt_token: attemptToken ?? '',
    });

    const blocked = authority.blocked || !medicine.canGive || !attemptToken;
    const ready = Boolean(data.dose_amount) && Boolean(data.observed_reason);

    const submit = () => {
        if (blocked || !ready || processing) return;
        post(urls.record, { preserveScroll: true });
    };

    const unit = medicine.doseUnitWord ?? medicine.doseUnit ?? '';
    const limitUnit = medicine.limitUnitWord ?? unit;

    return (
        <AppShell urls={urls} nav={nav}>
            <div className="r7-work">

                <header className="r7-person-top">
                    <TextLink className="r7-back-inline" href={urls.list}>
                        <Icon name="arrow" className="r7-icon r7-icon--small" />
                        <span>Back to {person.fullName}&rsquo;s as-required medicines</span>
                    </TextLink>

                    <p className="r7-person-top__where">
                        <span>{house.name}</span>
                        <span>As required</span>
                    </p>
                </header>

                {authority.blocked ? (
                    <Notice tone="warning" title="You cannot give medicines here">
                        {authority.reason}
                    </Notice>
                ) : null}

                {!authority.blocked && !medicine.canGive ? (
                    <Notice
                        tone={medicine.nextSection ? 'warning' : 'error'}
                        title="This cannot be given now"
                    >
                        {medicine.blockedReason}
                    </Notice>
                ) : null}

                {errors.dose_amount || errors.observed_reason ? (
                    <Notice tone="error" title="Not recorded">
                        {errors.dose_amount ?? errors.observed_reason}
                    </Notice>
                ) : null}

                {/* ── Who ────────────────────────────────────────────────── */}
                <section className="r7-person-id">
                    <PersonIdentity
                        name={person.fullName}
                        size="large"
                        photo={person.photo}
                        photoState={person.photoState}
                        details={[
                            person.preferredName ? `Known as ${person.preferredName}` : null,
                            person.bornOn ? `Born ${person.bornOn}` : 'Date of birth not recorded',
                            person.room,
                        ]}
                    />
                </section>

                {/* ── What could stop you ────────────────────────────────── */}
                <section className="r7-person-safety">
                    <AllergyWarning
                        allergies={safety.allergies}
                        state={safety.allergiesState}
                        sensitivities={safety.sensitivitiesState}
                    />
                </section>

                {/* ── What it is, and what the prescription says ──────────── */}
                <section className="r7-give">
                    <h2 className="r7-board__title">{medicine.name}</h2>

                    <div className="r7-give__medicine">
                        <span className="r7-give__name">
                            {medicine.name}
                            {medicine.strength ? (
                                <span className="r7-give__strength">{medicine.strength}</span>
                            ) : null}
                            {medicine.form ? (
                                <span className="r7-give__form">{medicine.form}</span>
                            ) : null}
                        </span>

                        <dl className="r7-give__facts">
                            <div className="r7-def">
                                <dt className="r7-label">Given for</dt>
                                <dd className="r7-def__value">
                                    {medicine.indication ?? 'Not recorded'}
                                </dd>
                            </div>
                            <div className="r7-def">
                                <dt className="r7-label">Permitted dose</dt>
                                <dd className="r7-def__value">
                                    {medicine.doseMin === null
                                        ? medicine.directions
                                        : medicine.doseIsRange
                                            ? `${medicine.doseMin} to ${medicine.doseMax} ${unit}`
                                            : `${medicine.doseMin} ${unit}`}
                                </dd>
                            </div>
                            <div className="r7-def">
                                <dt className="r7-label">Route</dt>
                                <dd className="r7-def__value">{medicine.route ?? 'Not recorded'}</dd>
                            </div>
                        </dl>

                        <div className="r7-med-item__flags">
                            {medicine.controlled ? (
                                <StatusLabel tone="warning">Controlled drug</StatusLabel>
                            ) : null}
                        </div>

                        <SupportType
                            type={medicine.support}
                            word={medicine.supportWord}
                            meaning={null}
                        />

                        {medicine.instructions ? (
                            <p className="r7-med-item__directions">
                                <Icon name="info" className="r7-icon r7-icon--small" />
                                <span>{medicine.instructions}</span>
                            </p>
                        ) : null}
                    </div>

                    {/* ── The arithmetic, in the open ────────────────────── */}
                    <div className="r7-prn-window">
                        <span className="r7-prn-window__title">What they have already had</span>

                        <p className="r7-prn-window__line">
                            {medicine.lastGivenAt
                                ? `Last given ${medicine.lastDoseAmount ?? ''} ${
                                    medicine.lastDoseUnitWord ?? medicine.lastDoseUnit ?? ''} at ${medicine.lastGivenAt} on ${
                                    medicine.lastGivenOn}${
                                    medicine.lastGivenBy ? ` by ${medicine.lastGivenBy}` : ''}.`
                                : 'No record of this being given before.'}
                        </p>

                        {medicine.minGapMinutes ? (
                            <p className="r7-prn-window__line">
                                At least {medicine.minGapMinutes} minutes between doses
                                {medicine.nextAllowedAt
                                    ? ` — the next is due at ${medicine.nextAllowedAt}.`
                                    : '.'}
                            </p>
                        ) : (
                            <p className="r7-prn-window__silent">
                                The prescription does not state a minimum gap.
                            </p>
                        )}

                        {medicine.maxAdministrations !== null ? (
                            <p className="r7-prn-window__line">
                                {medicine.givenInWindow} of {medicine.maxAdministrations} doses
                                {' '}{medicine.limitPeriodWord}.
                            </p>
                        ) : null}

                        {medicine.maxTotalAmount !== null ? (
                            <p className="r7-prn-window__line">
                                {medicine.amountInWindow} of {medicine.maxTotalAmount} {limitUnit}
                                {' '}{medicine.limitPeriodWord}.
                            </p>
                        ) : null}

                        {medicine.maxAdministrations === null
                            && medicine.maxTotalAmount === null ? (
                            <p className="r7-prn-window__silent">
                                The prescription does not state a maximum. Record7 will not invent
                                one — use your judgement and ask if you are unsure.
                            </p>
                        ) : null}
                    </div>

                    {/* ── What you are about to record ───────────────────── */}
                    {!blocked ? (
                        <div className="r7-give__action">
                            <label className="r7-field">
                                <span className="r7-label">
                                    How much are you giving?{unit ? ` (${unit})` : ''}
                                </span>
                                <input
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    className="r7-input"
                                    value={data.dose_amount}
                                    onChange={(event) => setData('dose_amount', event.target.value)}
                                />
                                {medicine.doseIsRange ? (
                                    <span className="r7-give__noteshint">
                                        Anything from {medicine.doseMin} to {medicine.doseMax} {unit}.
                                    </span>
                                ) : null}
                            </label>

                            <fieldset className="r7-outcome__reasons">
                                <legend className="r7-label">Why now?</legend>

                                {observedReasons.map((reason) => (
                                    <label className="r7-outcome__reason" key={reason.code}>
                                        <input
                                            type="radio"
                                            name="observed_reason"
                                            value={reason.code}
                                            checked={data.observed_reason === reason.code}
                                            onChange={() => setData('observed_reason', reason.code)}
                                        />
                                        <span>{reason.word}</span>
                                    </label>
                                ))}
                            </fieldset>

                            <label className="r7-give__notes">
                                <span className="r7-label">Anything worth adding</span>
                                <textarea
                                    className="r7-input r7-textarea"
                                    rows={3}
                                    maxLength={500}
                                    value={data.notes}
                                    onChange={(event) => setData('notes', event.target.value)}
                                />
                            </label>

                            <div className="r7-give__buttons">
                                <Button
                                    variant="primary"
                                    busy={processing}
                                    busyLabel="Recording"
                                    onClick={submit}
                                    disabled={!ready}
                                >
                                    Record this
                                </Button>

                                <Button variant="quiet" onClick={() => router.get(urls.list)}>
                                    Cancel
                                </Button>
                            </div>

                            {medicine.reviewAfterMinutes ? (
                                <p className="r7-give__only">
                                    You will be asked whether it worked in about
                                    {' '}{medicine.reviewAfterMinutes} minutes.
                                </p>
                            ) : (
                                <p className="r7-give__only">
                                    The prescription does not say when to check whether this worked,
                                    so no follow-up will be set. Worth raising with the manager.
                                </p>
                            )}
                        </div>
                    ) : null}
                </section>

                <p className="r7-person-meds__stage">{stage}</p>
            </div>
        </AppShell>
    );
}
