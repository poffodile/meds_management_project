import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import PersonIdentity from '@record7/components/PersonIdentity.jsx';
import AllergyWarning from '@record7/components/AllergyWarning.jsx';
import Notice from '@record7/components/Notice.jsx';
import Button from '@record7/components/Button.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Icon from '@record7/components/Icon.jsx';

/**
 * Section 2.5 — giving a controlled drug, with a witness and a register.
 *
 * WHAT BOTH PEOPLE HAVE TO SEE BEFORE THEY SIGN.
 * The person, their allergies, the medicine and its strength, the dose, the
 * route, what the register currently says, and what it will say afterwards. A
 * witness who cannot see the balance cannot witness the balance, so the
 * arithmetic is on the screen rather than behind it.
 *
 * REFUSING TO RECORD IS NOT REFUSING THE MEDICINE.
 * Where Record7 cannot account for the stock it says so in exactly those terms.
 * Whether somebody should have their medicine is a clinical decision made by a
 * person, and a piece of software that blurs those two things would be telling
 * workers something untrue at the worst possible moment.
 */
export default function CdGive({
    house, person, safety, medicine, history, witnesses, observedReasons,
    attemptToken, prnGuard, authority, stage, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock' },
    ];

    const isPrn = medicine.kind === 'prn';
    const [panel, setPanel] = useState('give');

    const give = useForm({
        dose_amount: medicine.doseMin !== null ? String(medicine.doseMin) : '',
        witness_id: '',
        observed_reason: '',
        notes: '',
        attempt_token: attemptToken ?? '',
    });

    const book = useForm({ quantity: '', witness_id: '', notes: '' });
    const count = useForm({ counted: '', witness_id: '', notes: '' });

    // A correction adds an entry. It never edits one, so it needs to know
    // which entry it is putting right and what the true figure is.
    const fix = useForm({
        corrects_register_id: '', true_balance: '', why: '', witness_id: '',
    });

    const needsWitness = medicine.witnessRequired;
    const witnessChosen = !needsWitness || Boolean(give.data.witness_id);

    const canGive = !authority.blocked
        && medicine.balanceKnown
        && !medicine.discrepancy
        && (!isPrn || Boolean(give.data.observed_reason))
        && Boolean(give.data.dose_amount)
        && witnessChosen;

    const remaining = medicine.balanceKnown && give.data.dose_amount
        ? (Number(medicine.balance) - Number(give.data.dose_amount))
        : null;

    const witnessField = (form, label) => (
        <label className="r7-give__notes">
            <span className="r7-label">
                {needsWitness ? 'Witness (required here)' : 'Witness (not required here)'}
            </span>
            <select
                className="r7-input"
                value={form.data.witness_id}
                onChange={(e) => form.setData('witness_id', e.target.value)}
            >
                <option value="">
                    {needsWitness ? 'Choose a colleague' : 'No witness'}
                </option>
                {witnesses.map((w) => (
                    <option key={w.id} value={w.id}>{w.name}</option>
                ))}
            </select>
            <span className="r7-give__noteshint">{label}</span>
        </label>
    );

    return (
        <AppShell urls={urls} nav={nav}>
            <div className="r7-work">

                <header className="r7-person-top">
                    <TextLink className="r7-back-inline" href={urls.cd}>
                        <Icon name="arrow" className="r7-icon r7-icon--small" />
                        <span>Back to {person.fullName}&rsquo;s controlled medicines</span>
                    </TextLink>

                    <p className="r7-person-top__where">
                        <span>{house.name}</span>
                        <span>{medicine.witnessWhy}</span>
                    </p>
                </header>

                {authority.blocked ? (
                    <Notice tone="warning" title="You cannot record this">
                        {authority.reason}
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

                {/* The medicine, in full, because a witness is checking it too. */}
                <section className="r7-cd-detail">
                    <h2 className="r7-cd-detail__name">
                        {medicine.name}{medicine.strength ? ` ${medicine.strength}` : ''}
                        {medicine.form ? <span className="r7-cd-detail__form"> {medicine.form}</span> : null}
                    </h2>

                    <p className="r7-cd-detail__directions">
                        {medicine.dose}, {medicine.route}
                        {medicine.schedule
                            ? `, Schedule ${medicine.schedule}`
                            : ', schedule not recorded'}
                    </p>

                    <p className="r7-cd-detail__balance">
                        {medicine.balanceKnown
                            ? <>Register says <strong>{medicine.balance} {medicine.unitWord ?? medicine.unit}</strong></>
                            : <>Nothing has been counted for this medicine yet.</>}
                        {remaining !== null && remaining >= 0 ? (
                            <span className="r7-cd-detail__after">
                                {' '}&rarr; {remaining} {remaining === 1 ? medicine.unit : (medicine.unitWord ?? medicine.unit)} after this
                            </span>
                        ) : null}
                    </p>
                </section>

                {medicine.discrepancy ? (
                    <Notice tone="error" title="The count does not agree with the register">
                        Counted {medicine.discrepancy.counted} {medicine.discrepancy.unit} against{' '}
                        {medicine.discrepancy.expected} at {medicine.discrepancy.at}. Until that is
                        resolved with evidence, Record7 cannot account for a dose.{' '}
                        <strong>This is not a decision about whether the medicine should be
                        given</strong> — speak to your manager.
                    </Notice>
                ) : null}

                {!medicine.balanceKnown ? (
                    <Notice tone="warning" title="Nothing counted yet">
                        Book in what is physically there first. Record7 will not record a dose out
                        of a cupboard it has never counted.
                    </Notice>
                ) : null}

                {isPrn && prnGuard?.tooSoon ? (
                    <Notice tone="warning" title="Too soon">
                        The last dose was less than {prnGuard.minGapMinutes} minutes ago.
                        The next one is not due until {prnGuard.nextAllowedAt}.
                    </Notice>
                ) : null}

                {/* Three things you can do here, one at a time. */}
                <nav className="r7-cd-tabs">
                    {[
                        ['give', 'Give it'],
                        ['book', 'Book stock in'],
                        ['count', 'Count it'],
                        ['fix', 'Put something right'],
                    ].map(([key, word]) => (
                        <button
                            key={key}
                            type="button"
                            className={`r7-cd-tab${panel === key ? ' r7-cd-tab--on' : ''}`}
                            aria-pressed={panel === key}
                            onClick={() => setPanel(key)}
                        >
                            {word}
                        </button>
                    ))}
                </nav>

                {panel === 'give' ? (
                    <section className="r7-give">
                        {isPrn ? (
                            <fieldset className="r7-outcome__reasons">
                                <legend className="r7-label">Why now?</legend>
                                {observedReasons.map((reason) => (
                                    <label className="r7-outcome__reason" key={reason.code}>
                                        <input
                                            type="radio"
                                            name="observed_reason"
                                            value={reason.code}
                                            checked={give.data.observed_reason === reason.code}
                                            onChange={() => give.setData('observed_reason', reason.code)}
                                        />
                                        <span>{reason.word}</span>
                                    </label>
                                ))}
                            </fieldset>
                        ) : null}

                        <label className="r7-give__notes">
                            <span className="r7-label">How much are you giving?</span>
                            <input
                                className="r7-input"
                                type="number"
                                step="0.001"
                                min="0"
                                value={give.data.dose_amount}
                                onChange={(e) => give.setData('dose_amount', e.target.value)}
                            />
                            <span className="r7-give__noteshint">
                                In {medicine.unit}. This comes out of the register.
                            </span>
                        </label>

                        {witnessField(give, needsWitness
                            ? 'They confirm the person, the medicine, the dose and the balance.'
                            : medicine.witnessWhy)}

                        <label className="r7-give__notes">
                            <span className="r7-label">Anything worth adding (optional)</span>
                            <textarea
                                className="r7-input r7-textarea"
                                rows={3}
                                maxLength={500}
                                value={give.data.notes}
                                onChange={(e) => give.setData('notes', e.target.value)}
                            />
                        </label>

                        <div className="r7-give__buttons">
                            <Button
                                variant="primary"
                                busy={give.processing}
                                busyLabel="Recording"
                                disabled={!canGive}
                                onClick={() => canGive && !give.processing
                                    && give.post(urls.record, { preserveScroll: true })}
                            >
                                Record this
                            </Button>
                        </div>
                    </section>
                ) : null}

                {panel === 'book' ? (
                    <section className="r7-give">
                        <label className="r7-give__notes">
                            <span className="r7-label">How much came in?</span>
                            <input
                                className="r7-input"
                                type="number"
                                step="0.001"
                                min="0"
                                value={book.data.quantity}
                                onChange={(e) => book.setData('quantity', e.target.value)}
                            />
                            <span className="r7-give__noteshint">In {medicine.unit}.</span>
                        </label>

                        {witnessField(book, 'Both people count what arrived.')}

                        <div className="r7-give__buttons">
                            <Button
                                variant="primary"
                                busy={book.processing}
                                disabled={!book.data.quantity || (needsWitness && !book.data.witness_id)}
                                onClick={() => book.post(urls.receipt, { preserveScroll: true })}
                            >
                                Book it in
                            </Button>
                        </div>
                    </section>
                ) : null}

                {panel === 'count' ? (
                    <section className="r7-give">
                        <label className="r7-give__notes">
                            <span className="r7-label">How much is actually there?</span>
                            <input
                                className="r7-input"
                                type="number"
                                step="0.001"
                                min="0"
                                value={count.data.counted}
                                onChange={(e) => count.setData('counted', e.target.value)}
                            />
                            <span className="r7-give__noteshint">
                                Write down what you can see, not what you expect. If it does not
                                agree, that is exactly what this is for.
                            </span>
                        </label>

                        {witnessField(count, 'Both people count it.')}

                        <div className="r7-give__buttons">
                            <Button
                                variant="primary"
                                busy={count.processing}
                                disabled={count.data.counted === ''
                                    || (needsWitness && !count.data.witness_id)}
                                onClick={() => count.post(urls.count, { preserveScroll: true })}
                            >
                                Record the count
                            </Button>
                        </div>
                    </section>
                ) : null}

                {panel === 'fix' ? (
                    <section className="r7-give">
                        <Notice tone="info" title="A correction adds an entry">
                            The original stays exactly as it is. This adds a new entry saying
                            what the true figure is and why, so the mistake and the fix are
                            both on the record.
                        </Notice>

                        <label className="r7-give__notes">
                            <span className="r7-label">Which entry is wrong?</span>
                            <select
                                className="r7-input"
                                value={fix.data.corrects_register_id}
                                onChange={(e) => fix.setData('corrects_register_id', e.target.value)}
                            >
                                <option value="">Choose an entry</option>
                                {history.map((entry) => (
                                    <option key={entry.id} value={entry.id}>
                                        {entry.word} &ndash; {entry.at} &ndash; left {entry.balance} {entry.unit}
                                    </option>
                                ))}
                            </select>
                        </label>

                        <label className="r7-give__notes">
                            <span className="r7-label">What is actually there now?</span>
                            <input
                                className="r7-input"
                                type="number"
                                step="0.001"
                                min="0"
                                value={fix.data.true_balance}
                                onChange={(e) => fix.setData('true_balance', e.target.value)}
                            />
                            <span className="r7-give__noteshint">
                                The balance moves to this figure. In {medicine.unit}.
                            </span>
                        </label>

                        <label className="r7-give__notes">
                            <span className="r7-label">What was wrong, and how do you know?</span>
                            <textarea
                                className="r7-input r7-textarea"
                                rows={3}
                                maxLength={500}
                                value={fix.data.why}
                                onChange={(e) => fix.setData('why', e.target.value)}
                            />
                            <span className="r7-give__noteshint">
                                This is the evidence. &ldquo;Checked&rdquo; on its own explains nothing
                                to whoever reads this next month.
                            </span>
                        </label>

                        {witnessField(fix, 'Both people agree the true figure.')}

                        <div className="r7-give__buttons">
                            <Button
                                variant="warning"
                                busy={fix.processing}
                                disabled={!fix.data.corrects_register_id
                                    || fix.data.true_balance === ''
                                    || fix.data.why.trim().length < 3
                                    || (needsWitness && !fix.data.witness_id)}
                                onClick={() => fix.post(urls.correct, { preserveScroll: true })}
                            >
                                Record the correction
                            </Button>
                        </div>
                    </section>
                ) : null}

                {/* The register itself, so nothing is taken on trust. */}
                <section className="r7-cd-history">
                    <h2 className="r7-board__title">The register</h2>

                    {history.length === 0 ? (
                        <p className="r7-empty">Nothing recorded for this medicine yet.</p>
                    ) : (
                        <ul className="r7-cd-history__list">
                            {history.map((entry) => (
                                <li
                                    key={entry.id}
                                    className={`r7-cd-history__row${
                                        entry.discrepancy ? ' r7-cd-history__row--flag' : ''}`}
                                >
                                    <span className="r7-cd-history__what">{entry.word}</span>
                                    <span className="r7-cd-history__figures">
                                        {entry.given ? `${entry.given} ${entry.givenUnit ?? entry.unit} given` : null}
                                        {entry.wasted && Number(entry.wasted) > 0
                                            ? `, ${entry.wasted} disposed of` : null}
                                        {entry.returned && Number(entry.returned) > 0
                                            ? `, ${entry.returned} put back` : null}
                                        {entry.counted
                                            ? `counted ${entry.counted}, register said ${entry.expected}`
                                            : null}
                                    </span>
                                    <span className="r7-cd-history__balance">
                                        {entry.balance} {entry.balanceUnit ?? entry.unit} left
                                    </span>
                                    <span className="r7-cd-history__who">
                                        {entry.at} by {entry.by}
                                        {entry.witness
                                            ? `, witnessed by ${entry.witness}`
                                            : entry.unwitnessed ? `, ${entry.unwitnessed}` : ''}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <p className="r7-person-meds__stage">{stage}</p>
            </div>
        </AppShell>
    );
}
