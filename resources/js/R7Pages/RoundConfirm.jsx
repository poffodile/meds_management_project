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
 * Section 2.2 — the last screen before a medicine is signed for.
 *
 * THIS IS NOT AN "ARE YOU SURE?" DIALOGUE.
 * A confirmation that hides what is being confirmed teaches people to press
 * through it. Everything needed to catch a wrong-person or wrong-medicine error
 * is on this screen, in the order it has to be checked:
 *
 *   1  who        name, date of birth, room, and where they actually are
 *   2  what could stop you   allergies, in the open, above the medicine
 *   3  what        medicine, strength, form, dose, route, due time
 *   4  the sentence   what YOU are recording, in the first person
 *   5  the control  one button, at the end, reached on purpose
 *
 * ONE OUTCOME EXISTS HERE, AND IT IS THE TRUE ONE.
 * There is no "refuse", no "not available", no "omit". Those are Section 2.3.
 * A half-built refusal button would collect worse records than none at all, so
 * the screen says plainly that it can only record a medicine as given.
 *
 * THE BUTTON IS NOT THE SAFETY MECHANISM.
 * Disabling it while a request is in flight stops the honest double-tap, and
 * nothing more. A retry, a second tab or a colleague on another phone is
 * settled by a unique constraint in the database, and this page is written on
 * the assumption that it will sometimes lose that race.
 */
export default function RoundConfirm({
    house, round, person, safety, medicine, confirmation, authority, stage, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock', current: true },
    ];

    const [showNotes, setShowNotes] = useState(false);
    const { data, setData, post, processing, errors } = useForm({ notes: '' });

    const blocked = authority.blocked || !medicine?.canBeGiven;

    const submit = () => {
        if (blocked || processing) return;
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

                {!authority.blocked && medicine && !medicine.canBeGiven ? (
                    <Notice
                        tone={medicine.recorded ? 'info' : 'warning'}
                        title={medicine.recorded
                            ? 'This dose has already been recorded'
                            : 'This cannot be recorded as given'}
                    >
                        {medicine.recorded
                            ? `${medicine.recordedWord}`
                                + `${medicine.recordedAt ? ` at ${medicine.recordedAt}` : ''}`
                                + `${medicine.recordedBy ? ` by ${medicine.recordedBy}` : ''}.`
                                + ' Nothing further is needed here.'
                            : medicine.blockedReason}
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

                {/* ── 3. What ────────────────────────────────────────────── */}
                {medicine ? (
                    <section className="r7-give">
                        {/* The heading must not promise what the screen is
                            refusing. Arriving here on a medicine that cannot be
                            recorded — already answered, controlled, as-required
                            — under the words "You are recording this medicine"
                            reads as a system that has changed its mind. */}
                        <h2 className="r7-board__title">
                            {blocked ? 'This medicine' : 'You are recording this medicine'}
                        </h2>

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
                                    <dd className="r7-def__value">
                                        {medicine.dose ?? 'Not recorded'}
                                    </dd>
                                </div>
                                <div className="r7-def">
                                    <dt className="r7-label">Route</dt>
                                    <dd className="r7-def__value">
                                        {medicine.route ?? 'Not recorded'}
                                    </dd>
                                </div>
                                <div className="r7-def">
                                    <dt className="r7-label">Due</dt>
                                    <dd className="r7-def__value">
                                        {medicine.dueAt}
                                        {medicine.late ? (
                                            <span className="r7-give__late">
                                                {medicine.latePhrase}
                                            </span>
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

                            {medicine.directions ? (
                                <p className="r7-med-item__directions">
                                    <Icon name="info" className="r7-icon r7-icon--small" />
                                    <span>{medicine.directions}</span>
                                </p>
                            ) : null}

                            {medicine.changed ? (
                                <p className="r7-med-item__changed">
                                    Changed {medicine.changed.on}
                                    {medicine.changed.note ? ` — ${medicine.changed.note}` : ''}
                                </p>
                            ) : null}
                        </div>

                        {/* ── 4. The sentence, then the control ──────────── */}
                        {!blocked ? (
                            <div className="r7-give__action">
                                <p className="r7-give__sentence">{confirmation}</p>

                                {/* LATE IS SAID AGAIN, HERE.
                                    Not to stop the record — a late medicine
                                    still has to be given and signed for — but
                                    so nobody signs for it without noticing, and
                                    so the due time is not quietly forgotten. */}
                                {medicine.late ? (
                                    <p className="r7-give__latenote">
                                        This was due at {medicine.dueAt} and is {medicine.latePhrase}.
                                        The record keeps both the time it was due and the time you
                                        record it now.
                                    </p>
                                ) : null}

                                {showNotes ? (
                                    <label className="r7-give__notes">
                                        <span className="r7-label">Notes (optional)</span>
                                        <textarea
                                            className="r7-input r7-textarea"
                                            rows={3}
                                            maxLength={500}
                                            value={data.notes}
                                            onChange={(event) => setData('notes', event.target.value)}
                                        />
                                        <span className="r7-give__noteshint">
                                            Only if there is something worth knowing. Nothing is
                                            required for a medicine given as prescribed.
                                        </span>
                                    </label>
                                ) : (
                                    <button
                                        type="button"
                                        className="r7-linkish"
                                        onClick={() => setShowNotes(true)}
                                    >
                                        Add a note
                                    </button>
                                )}

                                {errors.notes ? (
                                    <Notice tone="error">{errors.notes}</Notice>
                                ) : null}

                                <div className="r7-give__buttons">
                                    <Button
                                        variant="primary"
                                        busy={processing}
                                        busyLabel="Recording"
                                        onClick={submit}
                                    >
                                        Record as given
                                    </Button>

                                    <Button
                                        variant="quiet"
                                        onClick={() => router.get(urls.person)}
                                    >
                                        Cancel
                                    </Button>
                                </div>

                                <p className="r7-give__only">
                                    This screen can only record that a medicine was given. Refusals,
                                    omissions and medicines that were not available are recorded in
                                    a later part of Record7 that is not built yet.
                                </p>
                            </div>
                        ) : null}
                    </section>
                ) : (
                    <Notice tone="error" title="That medicine is not in this round">
                        Go back and choose it from the person&rsquo;s own list.
                    </Notice>
                )}

                <p className="r7-person-meds__stage">{stage}</p>
            </div>
        </AppShell>
    );
}
