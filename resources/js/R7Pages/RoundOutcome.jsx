import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import PersonIdentity from '@record7/components/PersonIdentity.jsx';
import AllergyWarning from '@record7/components/AllergyWarning.jsx';
import PersonAvailability from '@record7/components/PersonAvailability.jsx';
import SupportType from '@record7/components/SupportType.jsx';
import StatusLabel from '@record7/components/StatusLabel.jsx';
import Notice from '@record7/components/Notice.jsx';
import Button from '@record7/components/Button.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Icon from '@record7/components/Icon.jsx';

/**
 * Section 2.3 — why a medicine was not given.
 *
 * FOUR DIFFERENT THINGS, NOT ONE "NOT GIVEN".
 * They said no. They were not here. The medicine was not here. Nobody got to
 * them. Those are four different clinical facts with four different
 * consequences, and a single button covering all of them produces a record that
 * cannot answer the question anybody will later ask.
 *
 * NOTHING IS PRESELECTED — ESPECIALLY NOT FOR CALLUM.
 * His status says he is in hospital and the screen still refuses to choose for
 * the worker. A status is a fact about where somebody is. An outcome is a
 * statement about what a worker did, and only a person can make that statement.
 *
 * THE ORDER IS THE SAFETY CHECK, EXACTLY AS IN 2.1 AND 2.2.
 * Who, then what could stop you, then what the medicine is, and only then the
 * outcome. The identity does not scroll away above the decision.
 */
export default function RoundOutcome({
    house, round, person, safety, medicine, outcomes, reasonWords,
    missedActions, missedActionWords, authority, stage, urls, blockedReason = null,
    reoffer = null,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock', current: true },
    ];

    const [chosen, setChosen] = useState(null);

    const { data, setData, post, processing, errors } = useForm({
        outcome: '',
        reason_code: '',
        notes: '',
        action_taken: '',
        immediate_action_code: '',
        controlled_drug_no_quantity_removed: false,
    });

    const choose = (outcome) => {
        setChosen(outcome);
        setData({
            ...data,
            outcome: outcome.code,
            // Never carry a reason across from another outcome — the reasons
            // belong to the outcome, and a stale one would be a false record.
            reason_code: '',
            action_taken: '',
            immediate_action_code: '',
        });
    };

    const isMissed = chosen?.code === 'missed';
    const needsCdAnswer = Boolean(medicine?.controlled);

    const needsReason = (chosen?.reasons?.length ?? 0) > 0;

    const ready = Boolean(data.outcome)
        && (!needsReason || Boolean(data.reason_code))
        && (!isMissed || (data.notes.trim() && data.action_taken.trim() && data.immediate_action_code))
        && (!needsCdAnswer || data.controlled_drug_no_quantity_removed);

    const submit = () => {
        if (!ready || processing || authority.blocked) return;
        post(urls.record, { preserveScroll: true });
    };

    return (
        <AppShell urls={urls} nav={nav}>
            <div className="r7-work">

                <header className="r7-person-top">
                    <TextLink className="r7-back-inline" href={urls.person}>
                        <Icon name="arrow" className="r7-icon r7-icon--small" />
                        <span>Back to {person.fullName}</span>
                    </TextLink>

                    <p className="r7-person-top__where">
                        <span>{house.name}</span>
                        <span>{round.slot} round</span>
                        <span>{round.date}</span>
                    </p>
                </header>

                {authority.blocked ? (
                    <Notice tone="warning" title="You cannot continue this round">
                        {authority.reason}
                    </Notice>
                ) : null}

                {errors.outcome || errors.reason_code ? (
                    <Notice tone="error" title="Not recorded">
                        {errors.outcome ?? errors.reason_code}
                    </Notice>
                ) : null}

                {/* Said BEFORE anything is filled in. Letting somebody choose an
                    outcome, pick a reason and write a note, only to be told at
                    the end that this medicine belongs to a workflow that does
                    not exist yet, wastes the one thing a round has least of. */}
                {blockedReason ? (
                    <Notice tone="warning" title="This cannot be answered here">
                        {blockedReason}
                    </Notice>
                ) : null}

                {/* ── 1. Who ─────────────────────────────────────────────── */}
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

                {/* ── 2. What could stop you ─────────────────────────────── */}
                <section className="r7-person-safety">
                    <AllergyWarning
                        allergies={safety.allergies}
                        state={safety.allergiesState}
                        sensitivities={safety.sensitivitiesState}
                    />

                    <PersonAvailability
                        available={person.available}
                        statusWord={person.statusWord}
                        needsOutcome={!person.available}
                    />
                </section>

                {/* ── 3. Which medicine ──────────────────────────────────── */}
                {medicine ? (
                    <section className="r7-give">
                        <h2 className="r7-board__title">Which medicine this is about</h2>

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
                                    <dt className="r7-label">Dose</dt>
                                    <dd className="r7-def__value">{medicine.dose ?? 'Not recorded'}</dd>
                                </div>
                                <div className="r7-def">
                                    <dt className="r7-label">Route</dt>
                                    <dd className="r7-def__value">{medicine.route ?? 'Not recorded'}</dd>
                                </div>
                                <div className="r7-def">
                                    <dt className="r7-label">Due</dt>
                                    <dd className="r7-def__value">
                                        {medicine.dueAt}
                                        {medicine.late ? (
                                            <span className="r7-give__late">{medicine.latePhrase}</span>
                                        ) : null}
                                    </dd>
                                </div>
                            </dl>

                            <div className="r7-med-item__flags">
                                {medicine.timeSensitive ? (
                                    <StatusLabel tone="warning">Time critical</StatusLabel>
                                ) : null}
                                {medicine.controlled ? (
                                    <StatusLabel tone="warning">Controlled drug</StatusLabel>
                                ) : null}
                            </div>

                            <SupportType
                                type={medicine.support}
                                word={medicine.supportWord}
                                meaning={medicine.supportMeaning}
                            />
                        </div>
                    </section>
                ) : null}

                {/* ── 4. What actually happened ──────────────────────────── */}
                {blockedReason ? null : (
                <section className="r7-outcome">
                    {/* THE FIRST ANSWER, BEFORE THE SECOND ONE IS GIVEN.
                        A re-offer screen that hid the refusal would read as
                        though it never happened — and the whole point of an
                        append-only record is that it did. */}
                    {reoffer ? (
                        <div className="r7-reoffer">
                            <span className="r7-reoffer__title">
                                This is the same planned dose, offered again
                            </span>
                            <p className="r7-reoffer__first">
                                {reoffer.word}
                                {reoffer.reason ? ` — ${reoffer.reason.toLowerCase()}` : ''}
                                {reoffer.at ? ` at ${reoffer.at}` : ''}
                                {reoffer.by ? ` by ${reoffer.by}` : ''}.
                            </p>
                            {reoffer.notes ? (
                                <p className="r7-reoffer__said">&ldquo;{reoffer.notes}&rdquo;</p>
                            ) : null}
                            {reoffer.stillLate ? (
                                <p className="r7-reoffer__late">
                                    It was due at {medicine?.dueAt} — {reoffer.stillLate} ago.
                                </p>
                            ) : null}

                            <p className="r7-reoffer__note">
                                That refusal stays on the record exactly as it is. This is not a
                                correction and not a new dose — it is a second attempt at the
                                same one.
                            </p>
                        </div>
                    ) : null}

                    <h2 className="r7-board__title">
                        {reoffer ? 'How did the second offer go?' : 'What happened?'}
                    </h2>
                    <p className="r7-outcome__lead">
                        Choose the one that is true. These are different things and the record
                        keeps them apart — nothing is chosen for you.
                    </p>

                    <ul className="r7-outcome__list">
                        {outcomes.map((outcome) => (
                            <li key={outcome.code}>
                                <button
                                    type="button"
                                    className={`r7-outcome__choice r7-outcome__choice--${outcome.tone}`
                                        + (chosen?.code === outcome.code
                                            ? ' r7-outcome__choice--chosen' : '')}
                                    aria-pressed={chosen?.code === outcome.code}
                                    onClick={() => choose(outcome)}
                                >
                                    <span className="r7-outcome__word">{outcome.word}</span>
                                    <span className="r7-outcome__meaning">{outcome.meaning}</span>
                                </button>
                            </li>
                        ))}
                    </ul>

                    {chosen ? (
                        <div className="r7-outcome__detail">
                            {/* A controlled drug cannot take this path at all until
                                somebody says the cupboard was not opened. If any came
                                out, it is accountable and Section 2.5 owns it. */}
                            {needsCdAnswer ? (
                                <label className="r7-outcome__cd">
                                    <input
                                        type="checkbox"
                                        checked={data.controlled_drug_no_quantity_removed}
                                        onChange={(event) => setData(
                                            'controlled_drug_no_quantity_removed',
                                            event.target.checked
                                        )}
                                    />
                                    <span>
                                        <strong>No quantity was taken out of the controlled drugs
                                        cupboard.</strong> If any was taken out, stop here — it has to
                                        be accounted for with a witness, which Record7 cannot do yet.
                                    </span>
                                </label>
                            ) : null}

                            {chosen.reasons.length ? (
                            <fieldset className="r7-outcome__reasons">
                                <legend className="r7-label">Why</legend>

                                {chosen.reasons.map((code) => (
                                    <label className="r7-outcome__reason" key={code}>
                                        <input
                                            type="radio"
                                            name="reason_code"
                                            value={code}
                                            checked={data.reason_code === code}
                                            onChange={() => setData('reason_code', code)}
                                        />
                                        <span>{reasonWords[code] ?? code}</span>
                                    </label>
                                ))}
                            </fieldset>
                            ) : null}

                            <label className="r7-give__notes">
                                <span className="r7-label">
                                    {isMissed ? 'What happened, in your words' : 'Notes (optional)'}
                                </span>
                                <textarea
                                    className="r7-input r7-textarea"
                                    rows={3}
                                    maxLength={500}
                                    value={data.notes}
                                    onChange={(event) => setData('notes', event.target.value)}
                                />
                            </label>

                            {/* A missed dose is a medication error. It asks for more,
                                because somebody will have to answer for it later. */}
                            {isMissed ? (
                                <>
                                    <label className="r7-give__notes">
                                        <span className="r7-label">What you did about it</span>
                                        <textarea
                                            className="r7-input r7-textarea"
                                            rows={2}
                                            maxLength={500}
                                            value={data.action_taken}
                                            onChange={(event) => setData('action_taken', event.target.value)}
                                        />
                                    </label>

                                    <fieldset className="r7-outcome__reasons">
                                        <legend className="r7-label">Who you told</legend>
                                        {missedActions.map((code) => (
                                            <label className="r7-outcome__reason" key={code}>
                                                <input
                                                    type="radio"
                                                    name="immediate_action_code"
                                                    value={code}
                                                    checked={data.immediate_action_code === code}
                                                    onChange={() => setData('immediate_action_code', code)}
                                                />
                                                <span>{missedActionWords[code] ?? code}</span>
                                            </label>
                                        ))}
                                    </fieldset>
                                </>
                            ) : null}

                            <div className="r7-give__buttons">
                                <Button
                                    variant={isMissed ? 'warning' : 'primary'}
                                    busy={processing}
                                    busyLabel="Recording"
                                    onClick={submit}
                                    disabled={!ready}
                                >
                                    Record this
                                </Button>

                                <Button variant="quiet" onClick={() => router.get(urls.person)}>
                                    Cancel
                                </Button>
                            </div>

                            <p className="r7-give__only">
                                This does not give the medicine. It records what happened to the
                                planned dose, and it cannot be edited afterwards.
                            </p>
                        </div>
                    ) : null}
                </section>
                )}

                <p className="r7-person-meds__stage">{stage}</p>
            </div>
        </AppShell>
    );
}
