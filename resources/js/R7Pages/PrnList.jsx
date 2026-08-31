import React from 'react';
import { router, usePage } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import PersonIdentity from '@record7/components/PersonIdentity.jsx';
import AllergyWarning from '@record7/components/AllergyWarning.jsx';
import StatusLabel from '@record7/components/StatusLabel.jsx';
import Notice from '@record7/components/Notice.jsx';
import Button from '@record7/components/Button.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Icon from '@record7/components/Icon.jsx';

/**
 * Section 2.4 — what this person can have when they need it.
 *
 * EVERY PRN THEY HAVE, INCLUDING THE ONES THAT CANNOT BE GIVEN HERE.
 * Hiding a controlled drug would leave a worker believing there is nothing for
 * somebody in pain. It is shown, plainly, with the reason it cannot be given
 * through this screen — because "there is nothing" and "there is something you
 * cannot reach from here" lead to completely different next actions.
 */
export default function PrnList({
    house, person, safety, medicines, authority, stage, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock' },
    ];

    const flash = usePage().props.flash ?? {};
    const recorded = flash['r7.recorded'] ?? null;
    const failed = flash['r7.error'] ?? null;

    const goToGive = (prescriptionId) => router.get(
        urls.give.replace('__PRESCRIPTION__', prescriptionId)
    );

    return (
        <AppShell urls={urls} nav={nav}>
            <div className="r7-work">

                <header className="r7-person-top">
                    <TextLink className="r7-back-inline" href={urls.back ?? urls.today}>
                        <Icon name="arrow" className="r7-icon r7-icon--small" />
                        <span>Back to {urls.backLabel ?? 'Today'}</span>
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

                {recorded ? (
                    <Notice tone="success" title={`Recorded: ${recorded.outcome} at ${recorded.at}`}>
                        {`Signed by ${recorded.by}.`}
                        {recorded.reviewAt
                            ? ` Go back and ask whether it worked at about ${recorded.reviewAt}.`
                            : ' No follow-up time is stated on this prescription.'}
                    </Notice>
                ) : null}

                {failed ? <Notice tone="error" title="Not recorded">{failed}</Notice> : null}

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

                <section className="r7-person-meds">
                    <div className="r7-person-meds__head">
                        <h2 className="r7-board__title">When they need it</h2>
                        <span className="r7-board__note">
                            {medicines.length
                                ? `${medicines.filter((m) => m.canGive).length} of ${
                                    medicines.length} available now`
                                : 'None prescribed'}
                        </span>
                    </div>

                    {medicines.length ? (
                        <ul className="r7-med-items">
                            {medicines.map((medicine) => (
                                <li className="r7-med-item" key={medicine.prescriptionId}>
                                    <div className="r7-med-item__head">
                                        <span className="r7-med-item__name">
                                            {medicine.name}
                                            {medicine.strength ? (
                                                <span className="r7-med-item__strength">
                                                    {medicine.strength}
                                                </span>
                                            ) : null}
                                            {medicine.form ? (
                                                <span className="r7-med-item__form">
                                                    {medicine.form}
                                                </span>
                                            ) : null}
                                        </span>
                                    </div>

                                    <div className="r7-med-item__what">
                                        <span className="r7-med-item__dose">
                                            {medicine.directions}
                                        </span>
                                        <span className="r7-med-item__route">
                                            {medicine.route}
                                        </span>
                                    </div>

                                    {/* The indication is already a phrase — the
                                        fixture ones begin "For …" — so nothing
                                        is prepended. "For For back pain" is what
                                        happens when a template assumes. */}
                                    <p className="r7-med-item__said">
                                        {medicine.indication ?? 'No indication recorded'}
                                    </p>

                                    <div className="r7-med-item__flags">
                                        {medicine.controlled ? (
                                            <StatusLabel tone="warning">Controlled drug</StatusLabel>
                                        ) : null}
                                        {medicine.tooSoon ? (
                                            <StatusLabel tone="info">
                                                Next due {medicine.nextAllowedAt}
                                            </StatusLabel>
                                        ) : null}
                                    </div>

                                    {medicine.lastGivenAt ? (
                                        <p className="r7-med-item__record">
                                            Last given {medicine.lastGivenAt} on{' '}
                                            {medicine.lastGivenOn}
                                            {medicine.lastGivenBy
                                                ? ` by ${medicine.lastGivenBy}` : ''}
                                        </p>
                                    ) : (
                                        <p className="r7-med-item__record">
                                            No record of this being given before.
                                        </p>
                                    )}

                                    <div className="r7-med-item__action">
                                        {medicine.canGive && !authority.blocked ? (
                                            <Button
                                                variant="secondary"
                                                size="small"
                                                onClick={() => goToGive(medicine.prescriptionId)}
                                            >
                                                Give this now
                                            </Button>
                                        ) : (
                                            <p className="r7-med-item__held">
                                                {medicine.blockedReason}
                                            </p>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="r7-med-item__held">
                            Nothing is prescribed for them on an as-required basis.
                        </p>
                    )}
                </section>

                <p className="r7-person-meds__stage">{stage}</p>
            </div>
        </AppShell>
    );
}
