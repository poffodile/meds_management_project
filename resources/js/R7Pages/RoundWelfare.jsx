import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import PersonIdentity from '@record7/components/PersonIdentity.jsx';
import AllergyWarning from '@record7/components/AllergyWarning.jsx';
import Notice from '@record7/components/Notice.jsx';
import Button from '@record7/components/Button.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Icon from '@record7/components/Icon.jsx';

/**
 * Section 2.3 — recording that somebody who could not be found has been found.
 *
 * THE ONLY THING THAT ANSWERS THAT CONCERN.
 * Not acknowledging it, not owning it, not writing a note, not closing a review
 * item, and not recording an unrelated medicine for them later. A concern about
 * where a person is can only be answered by somebody saying what they found.
 *
 * IT DOES NOT DECIDE ANYTHING IS SAFEGUARDING.
 * Saying you found somebody says exactly that. Whether it becomes a
 * safeguarding matter is a judgement for a manager and the provider's own
 * policy, and a medicines round has no business making it on their behalf.
 *
 * NOTHING IS PRESELECTED, and the note can never stand in for saying what
 * actually happened.
 */
export default function RoundWelfare({
    house, person, safety, concern, resolutions, authority, stage, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock', current: true },
    ];

    const [chosen, setChosen] = useState(null);
    const { data, setData, post, processing, errors } = useForm({
        resolution_type: '',
        note: '',
    });

    const choose = (code) => {
        setChosen(code);
        setData('resolution_type', code);
    };

    const submit = () => {
        if (!data.resolution_type || processing || authority.blocked) return;
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
                        <span>Welfare</span>
                    </p>
                </header>

                {authority.blocked ? (
                    <Notice tone="warning" title="You cannot continue this round">
                        {authority.reason}
                    </Notice>
                ) : null}

                {errors.resolution_type ? (
                    <Notice tone="error" title="Not recorded">{errors.resolution_type}</Notice>
                ) : null}

                {/* Who this is about, before anything else. */}
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

                <section className="r7-person-safety">
                    <AllergyWarning
                        allergies={safety.allergies}
                        state={safety.allergiesState}
                        sensitivities={safety.sensitivitiesState}
                    />
                </section>

                {/* What was reported, kept in front of the person answering it. */}
                <section className="r7-outcome">
                    <div className="r7-reoffer">
                        <span className="r7-reoffer__title">What was reported</span>
                        <p className="r7-reoffer__first">
                            {person.fullName} could not be found here
                            {concern.at ? ` at ${concern.at}` : ''}
                            {concern.by ? `, reported by ${concern.by}` : ''}.
                        </p>
                        {concern.note ? (
                            <p className="r7-reoffer__said">&ldquo;{concern.note}&rdquo;</p>
                        ) : null}
                        <p className="r7-reoffer__note">
                            That report stays on the record exactly as it is. This adds what
                            happened next.
                        </p>
                    </div>

                    <h2 className="r7-board__title">What did you find?</h2>
                    <p className="r7-outcome__lead">
                        Only say this once you actually know. Nothing else closes this — not
                        acknowledging it, not a note, and not giving them a medicine later.
                    </p>

                    <ul className="r7-outcome__list">
                        {resolutions.map((resolution) => (
                            <li key={resolution.code}>
                                <button
                                    type="button"
                                    className={'r7-outcome__choice r7-outcome__choice--info'
                                        + (chosen === resolution.code
                                            ? ' r7-outcome__choice--chosen' : '')}
                                    aria-pressed={chosen === resolution.code}
                                    onClick={() => choose(resolution.code)}
                                >
                                    <span className="r7-outcome__word">{resolution.word}</span>
                                </button>
                            </li>
                        ))}
                    </ul>

                    {chosen ? (
                        <div className="r7-outcome__detail">
                            <label className="r7-give__notes">
                                <span className="r7-label">Anything worth adding (optional)</span>
                                <textarea
                                    className="r7-input r7-textarea"
                                    rows={3}
                                    maxLength={500}
                                    value={data.note}
                                    onChange={(event) => setData('note', event.target.value)}
                                />
                            </label>

                            <div className="r7-give__buttons">
                                <Button
                                    variant="primary"
                                    busy={processing}
                                    busyLabel="Recording"
                                    onClick={submit}
                                >
                                    Record this
                                </Button>

                                <Button variant="quiet" onClick={() => router.get(urls.person)}>
                                    Cancel
                                </Button>
                            </div>

                            <p className="r7-give__only">
                                This records what you found. It does not decide that anything is a
                                safeguarding matter — raise that with your manager if you think it
                                is one.
                            </p>
                        </div>
                    ) : null}
                </section>

                <p className="r7-person-meds__stage">{stage}</p>
            </div>
        </AppShell>
    );
}
