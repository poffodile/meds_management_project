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
 * Section 2.4 — going back and asking whether it worked.
 *
 * TWO QUESTIONS, NEVER ONE.
 * Did it work, and did anything about them worry you. A medicine can settle
 * somebody completely and still leave a rash; it can do nothing at all with no
 * reaction whatsoever. Folding those into a single answer loses whichever one
 * mattered, and the one that matters is rarely the one anybody expected.
 *
 * A CONCERN HAS TO SAY WHAT WAS SEEN AND WHAT WAS DONE.
 * "Something worried me" with nothing after it is not something the next shift
 * can act on. Record7 does not attempt to say what the reaction was — that is
 * for a clinician — and it does not decide anything is safeguarding.
 */
export default function PrnReview({
    house, person, safety, given, followUp, concernActions, authority, stage, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock' },
    ];

    const [concerned, setConcerned] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        outcome: '',
        notes: '',
        concerning_response: false,
        concern_observed: '',
        concern_action_code: '',
    });

    const answers = [
        { code: 'effective', word: 'It worked', tone: 'success' },
        { code: 'partly_effective', word: 'It helped a bit', tone: 'info' },
        { code: 'not_effective', word: 'It did not work', tone: 'warning' },
    ];

    const ready = Boolean(data.outcome)
        && (!concerned || (data.concern_observed.trim() && data.concern_action_code));

    const submit = () => {
        if (!ready || processing || authority.blocked) return;
        post(urls.record, { preserveScroll: true });
    };

    const setConcern = (on) => {
        setConcerned(on);
        setData({
            ...data,
            concerning_response: on,
            concern_observed: on ? data.concern_observed : '',
            concern_action_code: on ? data.concern_action_code : '',
        });
    };

    return (
        <AppShell urls={urls} nav={nav}>
            <div className="r7-work">

                <header className="r7-person-top">
                    {/* A follow-up is most often opened straight from Today,
                        not from inside the round, so the way back is wherever
                        the worker actually belongs — their round screen while
                        one is open, Today otherwise. */}
                    <TextLink className="r7-back-inline" href={urls.back ?? urls.person}>
                        <Icon name="arrow" className="r7-icon r7-icon--small" />
                        <span>Back to {urls.backLabel ?? person.fullName}</span>
                    </TextLink>

                    <p className="r7-person-top__where">
                        <span>{house.name}</span>
                        <span>Follow-up due {followUp.dueAt}</span>
                    </p>
                </header>

                {authority.blocked ? (
                    <Notice tone="warning" title="You cannot record this">
                        {authority.reason}
                    </Notice>
                ) : null}

                {errors.outcome ? (
                    <Notice tone="error" title="Not recorded">{errors.outcome}</Notice>
                ) : null}

                {followUp.answered ? (
                    <Notice tone="info" title="This has already been answered">
                        Nothing further is needed here.
                    </Notice>
                ) : null}

                <section className="r7-person-id">
                    <PersonIdentity
                        name={person.fullName}
                        size="large"
                        photo={person.photo}
                        photoState={person.photoState}
                        details={[
                            person.preferredName ? `Known as ${person.preferredName}` : null,
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

                {/* What was given, kept in front of whoever is answering. */}
                <section className="r7-outcome">
                    <div className="r7-reoffer">
                        <span className="r7-reoffer__title">What they were given</span>
                        <p className="r7-reoffer__first">
                            {given.amount} {given.unitWord ?? given.unit} of {given.medicine}
                            {given.strength ? ` ${given.strength}` : ''} at {given.at}
                            {given.by ? ` by ${given.by}` : ''}.
                        </p>
                        {given.observed ? (
                            <p className="r7-reoffer__said">Given because: {given.observed}</p>
                        ) : null}
                        {given.notes ? (
                            <p className="r7-reoffer__said">&ldquo;{given.notes}&rdquo;</p>
                        ) : null}
                    </div>

                    {! followUp.answered ? (
                        <>
                            <h2 className="r7-board__title">Did it work?</h2>

                            <ul className="r7-outcome__list">
                                {answers.map((answer) => (
                                    <li key={answer.code}>
                                        <button
                                            type="button"
                                            className={`r7-outcome__choice r7-outcome__choice--${answer.tone}`
                                                + (data.outcome === answer.code
                                                    ? ' r7-outcome__choice--chosen' : '')}
                                            aria-pressed={data.outcome === answer.code}
                                            onClick={() => setData('outcome', answer.code)}
                                        >
                                            <span className="r7-outcome__word">{answer.word}</span>
                                        </button>
                                    </li>
                                ))}
                            </ul>

                            {data.outcome ? (
                                <div className="r7-outcome__detail">
                                    <label className="r7-give__notes">
                                        <span className="r7-label">
                                            Anything worth adding (optional)
                                        </span>
                                        <textarea
                                            className="r7-input r7-textarea"
                                            rows={3}
                                            maxLength={500}
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                        />
                                    </label>

                                    {/* A SEPARATE QUESTION. Not a severity dial on
                                        the one above — a different fact entirely. */}
                                    <label className="r7-outcome__cd">
                                        <input
                                            type="checkbox"
                                            checked={concerned}
                                            onChange={(e) => setConcern(e.target.checked)}
                                        />
                                        <span>
                                            <strong>Something about them worried me.</strong> This is
                                            separate from whether the medicine worked — tick it even
                                            if it did.
                                        </span>
                                    </label>

                                    {concerned ? (
                                        <>
                                            <label className="r7-give__notes">
                                                <span className="r7-label">What did you see?</span>
                                                <textarea
                                                    className="r7-input r7-textarea"
                                                    rows={3}
                                                    maxLength={500}
                                                    value={data.concern_observed}
                                                    onChange={(e) => setData(
                                                        'concern_observed', e.target.value
                                                    )}
                                                />
                                                <span className="r7-give__noteshint">
                                                    Describe it plainly. Do not try to say what
                                                    caused it — that is for a clinician.
                                                </span>
                                            </label>

                                            <fieldset className="r7-outcome__reasons">
                                                <legend className="r7-label">What did you do?</legend>
                                                {concernActions.map((action) => (
                                                    <label
                                                        className="r7-outcome__reason"
                                                        key={action.code}
                                                    >
                                                        <input
                                                            type="radio"
                                                            name="concern_action_code"
                                                            value={action.code}
                                                            checked={data.concern_action_code
                                                                === action.code}
                                                            onChange={() => setData(
                                                                'concern_action_code', action.code
                                                            )}
                                                        />
                                                        <span>{action.word}</span>
                                                    </label>
                                                ))}
                                            </fieldset>
                                        </>
                                    ) : null}

                                    <div className="r7-give__buttons">
                                        <Button
                                            variant={concerned ? 'warning' : 'primary'}
                                            busy={processing}
                                            busyLabel="Recording"
                                            onClick={submit}
                                            disabled={!ready}
                                        >
                                            Record this
                                        </Button>

                                        <Button
                                            variant="quiet"
                                            onClick={() => router.get(urls.today)}
                                        >
                                            Cancel
                                        </Button>
                                    </div>

                                    <p className="r7-give__only">
                                        Recording a concern does not decide it is a safeguarding
                                        matter. Raise that with your manager if you think it is one.
                                    </p>
                                </div>
                            ) : null}
                        </>
                    ) : null}
                </section>

                <p className="r7-person-meds__stage">{stage}</p>
            </div>
        </AppShell>
    );
}
