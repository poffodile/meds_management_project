import React from 'react';
import { router, usePage } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import PersonIdentity from '@record7/components/PersonIdentity.jsx';
import AllergyWarning from '@record7/components/AllergyWarning.jsx';
import PersonAvailability from '@record7/components/PersonAvailability.jsx';
import MedicineRoundItem from '@record7/components/MedicineRoundItem.jsx';
import StateBlock from '@record7/components/StateBlock.jsx';
import Notice from '@record7/components/Notice.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Button from '@record7/components/Button.jsx';
import Icon from '@record7/components/Icon.jsx';

/**
 * Section 2.1 — the check before a medicine is given.
 *
 * THE ORDER ON THIS PAGE IS THE SAFETY CHECK.
 *
 *   1  where you are      house, round, time, and the way back
 *   2  who this is        name, preferred name, date of birth, room
 *   3  what could stop you allergies, then where they actually are
 *   4  what is due        one item per medicine
 *
 * Knowing what to give before knowing who you are giving it to is how the right
 * medicine reaches the wrong person, so identity comes first and the allergies
 * are never behind a tap.
 *
 * NOTHING IS RECORDED FROM THIS SCREEN. Section 2.2 added a "Record as given"
 * control, and it does exactly one thing: it opens a confirmation screen. There
 * is no submission here, and the medicine row itself is not a control — a tap
 * while scrolling must never be able to sign for a dose.
 */
export default function RoundPerson({
    house, round, progress, person, safety, medicines, neighbours, authority, stage, urls,
    welfareConcern = null, asRequired = null,
}) {
    const flash = usePage().props.flash ?? {};
    const recorded = flash['r7.recorded'] ?? null;
    const failed = flash['r7.error'] ?? null;
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock', current: true },
    ];

    const goToPerson = (clientId) => router.get(urls.person.replace('__ID__', clientId));

    // A fully self-managed medicine is not work waiting to be done, so it is
    // not counted as such — it stays on the page as context.
    const left = medicines.filter((m) => !m.recorded && !m.selfManaged).length;

    const outstanding = () => {
        const needing = medicines.filter((m) => !m.selfManaged).length;

        if (!medicines.length) return 'Nothing planned';
        if (!needing) return 'All self-managed';
        if (!left) return `All ${needing} recorded`;

        return `${left} of ${needing} still to record`;
    };

    const goToConfirm = (doseId) => router.get(
        urls.confirm.replace('__ID__', person.id).replace('__DOSE__', doseId)
    );

    const goToOutcome = (doseId) => router.get(
        urls.outcome.replace('__ID__', person.id).replace('__DOSE__', doseId)
    );

    const goToReoffer = (doseId) => router.get(
        urls.reoffer.replace('__ID__', person.id).replace('__DOSE__', doseId)
    );

    /**
     * Where a medicine this screen cannot record IS recorded.
     *
     * Two of the refusals in the round are not dead ends but directions, and
     * until now they read as dead ends because nothing carried the worker on.
     * A controlled drug goes to the register screen, where a witness signs; an
     * as-required medicine goes to its own list, where the reason for giving it
     * and whether it worked are recorded too.
     *
     * Driven by the refusal CODE, never by the medicine's own flags. The server
     * decides why a dose cannot be recorded here, and the door has to be the
     * one that answers the reason actually given — a medicine could be
     * controlled AND refused for something else entirely, and sending somebody
     * to fetch a colleague to witness a stopped prescription would waste two
     * people's time and teach them to distrust the screen.
     */
    const handOffFor = (medicine) => {
        if (medicine.blockedCode === 'witness_required' && medicine.prescriptionId) {
            return {
                label: 'Record with a witness',
                onClick: () => router.get(
                    urls.controlled
                        .replace('__ID__', person.id)
                        .replace('__PRESCRIPTION__', medicine.prescriptionId)
                ),
            };
        }

        if (medicine.blockedCode === 'as_required') {
            return {
                label: 'Go to as-required medicines',
                onClick: () => router.get(urls.asRequired.replace('__ID__', person.id)),
            };
        }

        return null;
    };

    return (
        <AppShell urls={urls} nav={nav}>
            <div className="r7-work">

                {/* ── 1. Where you are, and the way back ─────────────────── */}
                <header className="r7-person-top">
                    <TextLink className="r7-back-inline" href={urls.round}>
                        <Icon name="arrow" className="r7-icon r7-icon--small" />
                        <span>Back to the {round.slot} round</span>
                    </TextLink>

                    <p className="r7-person-top__where">
                        <span>{house.name}</span>
                        <span>{round.slot} round</span>
                        <span>
                            {round.window.single ? round.window.label : `${round.window.label}`}
                        </span>
                        {neighbours.position ? (
                            <span className="r7-person-top__position">
                                Person {neighbours.position} of {neighbours.total}
                            </span>
                        ) : null}
                    </p>
                </header>

                {authority.blocked ? (
                    <Notice tone="warning" title="You cannot continue this round">
                        {authority.reason}
                    </Notice>
                ) : null}

                {/* Said in the page, not in a pop-up. A message that has to be
                    dismissed is a message people dismiss without reading, and
                    that is the wrong habit to teach on a medicines product. */}
                {recorded ? (
                    <Notice
                        tone={recorded.created ? 'success' : 'info'}
                        title={recorded.created
                            ? `Recorded: ${recorded.outcome} at ${recorded.at}`
                            : 'That dose was already recorded'}
                    >
                        {recorded.created
                            ? `Signed by ${recorded.by}. It stays on this page below.`
                            : `${recorded.outcome} at ${recorded.at} by ${recorded.by}. `
                                + 'Nothing was recorded a second time.'}
                    </Notice>
                ) : null}

                {failed ? <Notice tone="error" title="Not recorded">{failed}</Notice> : null}

                {/* An unanswered "could not be found" concern. Nothing else on
                    this page closes it — not acknowledging it, and not giving
                    them a medicine later. Somebody has to say what they found. */}
                {welfareConcern ? (
                    <Notice tone="warning" title="Nobody could find them earlier">
                        This is still open. When you know where they are, record it — no other
                        action on this page will close it.
                        {' '}
                        <button
                            type="button"
                            className="r7-linkish"
                            onClick={() => router.get(welfareConcern.url)}
                        >
                            Record that they were found
                        </button>
                    </Notice>
                ) : null}

                {/* ── 2. Who this is ─────────────────────────────────────── */}
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

                    {person.photoState === 'not_held' ? (
                        <p className="r7-person-id__note">
                            Record7 does not hold photographs. Check identity by name and date of
                            birth, and ask if you are not certain.
                        </p>
                    ) : null}
                </section>

                {/* ── 3. What could stop you ─────────────────────────────── */}
                <section className="r7-person-safety">
                    <AllergyWarning
                        allergies={safety.allergies}
                        state={safety.allergiesState}
                        sensitivities={safety.sensitivitiesState}
                    />

                    <PersonAvailability
                        available={person.available}
                        statusWord={person.statusWord}
                        needsOutcome={!person.available && medicines.some((m) => !m.recorded)}
                    />

                    {person.supportNote ? (
                        <p className="r7-person-safety__note">
                            <Icon name="info" className="r7-icon r7-icon--small" />
                            <span>{person.supportNote}</span>
                        </p>
                    ) : null}
                </section>

                {/* ── 4. What is due ─────────────────────────────────────── */}
                <section className="r7-person-meds">
                    <div className="r7-person-meds__head">
                        <h2 className="r7-board__title">Due in this round</h2>
                        {/* WHAT IS LEFT, not how many exist. "2 to check" beside
                            two medicines that are both already answered is not a
                            neutral inaccuracy — it sends somebody looking for
                            work that is done, and on a round that is exactly how
                            a dose gets given twice. */}
                        <span className="r7-board__note">{outstanding()}</span>
                    </div>

                    {medicines.length ? (
                        <ul className="r7-med-items">
                            {medicines.map((medicine) => (
                                <MedicineRoundItem
                                    medicine={medicine}
                                    key={medicine.doseId}
                                    onRecord={authority.blocked
                                        ? null
                                        : () => goToConfirm(medicine.doseId)}
                                    onOutcome={authority.blocked
                                        ? null
                                        : () => goToOutcome(medicine.doseId)}
                                    onReoffer={authority.blocked
                                        ? null
                                        : () => goToReoffer(medicine.doseId)}
                                    /* Withheld from somebody who may not record
                                       in this round at all: the door leads to a
                                       screen that would refuse them anyway, and
                                       offering it would be the same defect in a
                                       new place. */
                                    handOff={authority.blocked ? null : handOffFor(medicine)}
                                />
                            ))}
                        </ul>
                    ) : (
                        <StateBlock state="empty" title="Nothing is due for this person in this round">
                            They are in the round list, but no medicine is planned for this slot.
                        </StateBlock>
                    )}

                    {/* AS-REQUIRED MEDICINES, WHICH ARE NEVER "DUE".
                        Nothing schedules them, so they cannot appear in the
                        list above and should not — but they belong to this
                        person and are needed at exactly the unplanned moments a
                        worker is standing here. Shown only when they have any,
                        because an empty list reads as "there is something here"
                        until somebody has walked into it. */}
                    {asRequired && !authority.blocked ? (
                        <p className="r7-person-meds__prn">
                            <TextLink href={asRequired.url}>
                                {asRequired.count === 1
                                    ? 'One as-required medicine'
                                    : `${asRequired.count} as-required medicines`}
                            </TextLink>
                            <span> — not due, but available if they need one.</span>
                        </p>
                    ) : null}

                    {/* Stated once, plainly, so nobody hunts for a button that
                        is not there yet. */}
                    <p className="r7-person-meds__stage">{stage}</p>
                </section>

                {/* ── Moving on, only within this round ──────────────────── */}
                <nav className="r7-person-move" aria-label="Other people in this round">
                    {neighbours.previous ? (
                        <Button variant="quiet" onClick={() => goToPerson(neighbours.previous.clientId)}>
                            Previous: {neighbours.previous.name}
                        </Button>
                    ) : <span />}

                    {neighbours.next ? (
                        <Button variant="quiet" onClick={() => goToPerson(neighbours.next.clientId)}>
                            Next: {neighbours.next.name}
                        </Button>
                    ) : null}
                </nav>
            </div>
        </AppShell>
    );
}
