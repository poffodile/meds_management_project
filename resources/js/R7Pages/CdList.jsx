import React from 'react';
import { router } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import PersonIdentity from '@record7/components/PersonIdentity.jsx';
import AllergyWarning from '@record7/components/AllergyWarning.jsx';
import Notice from '@record7/components/Notice.jsx';
import Button from '@record7/components/Button.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Icon from '@record7/components/Icon.jsx';

/**
 * Section 2.5 — this person's controlled medicines.
 *
 * THE BALANCE IS THE POINT OF THIS SCREEN.
 * A worker about to take a controlled drug out of a cupboard needs to know what
 * the register says is in it before they open the door, not afterwards. Where
 * Record7 has never counted the stock it says so plainly rather than showing a
 * confident nothing.
 *
 * A DISAGREEMENT IS NOT TIDIED AWAY.
 * Where the count and the register do not match, that sits at the top in its
 * own right. It is the single most serious thing this screen can be telling
 * somebody, and it stays until it is resolved with evidence.
 */
export default function CdList({
    house, person, safety, medicines, witnessRule, authority, stage, urls,
}) {
    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock' },
    ];

    return (
        <AppShell urls={urls} nav={nav}>
            <div className="r7-work">

                <header className="r7-person-top">
                    {/* The way back to the work, first, because that is the one
                        a worker who has just signed for something needs. The
                        cross-link to their as-required medicines follows it: a
                        useful sideways move, but not the way out. */}
                    <TextLink className="r7-back-inline" href={urls.back ?? urls.today}>
                        <Icon name="arrow" className="r7-icon r7-icon--small" />
                        <span>Back to {urls.backLabel ?? 'Today'}</span>
                    </TextLink>

                    <TextLink className="r7-back-inline" href={urls.person}>
                        <span>{person.fullName}&rsquo;s as-required medicines</span>
                    </TextLink>

                    <p className="r7-person-top__where">
                        <span>{house.name}</span>
                        <span>Controlled drugs</span>
                    </p>
                </header>

                {authority.blocked ? (
                    <Notice tone="warning" title="You cannot record here">
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

                {/* What this house requires, said once, at the top. */}
                <Notice tone={witnessRule.required ? 'info' : 'quiet'} title="Signing">
                    {witnessRule.why}
                </Notice>

                <section className="r7-person-meds">
                    <h2 className="r7-board__title">Controlled medicines</h2>

                    {medicines.length === 0 ? (
                        <p className="r7-empty">
                            Nothing controlled is prescribed for {person.fullName}.
                        </p>
                    ) : null}

                    <ul className="r7-cd-list">
                        {medicines.map((medicine) => (
                            <li key={medicine.prescriptionId} className="r7-cd-card">
                                <div className="r7-cd-card__head">
                                    <h3 className="r7-cd-card__name">
                                        {medicine.name}
                                        {medicine.strength ? (
                                            <span className="r7-cd-card__strength">
                                                {' '}{medicine.strength}
                                            </span>
                                        ) : null}
                                    </h3>

                                    {medicine.schedule ? (
                                        <span className="r7-cd-card__schedule">
                                            Schedule {medicine.schedule}
                                        </span>
                                    ) : (
                                        <span className="r7-cd-card__schedule r7-cd-card__schedule--unknown">
                                            Schedule not recorded
                                        </span>
                                    )}
                                </div>

                                <p className="r7-cd-card__directions">
                                    {medicine.dose}, {medicine.route}
                                    {medicine.kind === 'prn' ? ', when required' : ', on a schedule'}
                                </p>

                                {/* The indication is already a phrase, and the
                                    fixture ones begin "For ...", so nothing is
                                    prepended. "For For severe agitation" is
                                    what happens when a template assumes. */}
                                {medicine.indication ? (
                                    <p className="r7-cd-card__for">{medicine.indication}</p>
                                ) : null}

                                {/* THE BALANCE. Never a confident zero where
                                    nothing has been counted. */}
                                <p className="r7-cd-card__balance">
                                    {medicine.balanceKnown ? (
                                        <>
                                            <strong>{medicine.balance} {medicine.unitWord ?? medicine.unit}</strong>
                                            {' '}in the register
                                        </>
                                    ) : (
                                        <span className="r7-cd-card__balance--unknown">
                                            Nothing counted yet. Book in what is there before giving it.
                                        </span>
                                    )}
                                </p>

                                {medicine.discrepancy ? (
                                    <Notice tone="error" title="The count does not agree">
                                        Counted {medicine.discrepancy.counted} {medicine.discrepancy.unit},
                                        register said {medicine.discrepancy.expected}, at{' '}
                                        {medicine.discrepancy.at}. This has to be resolved with
                                        evidence. Record7 will not record a dose against a balance
                                        it cannot stand behind — that is not a decision about
                                        whether the medicine should be given.
                                    </Notice>
                                ) : null}

                                <div className="r7-cd-card__actions">
                                    <Button
                                        variant="primary"
                                        disabled={authority.blocked}
                                        onClick={() => router.get(
                                            `${urls.cd}/${medicine.prescriptionId}`
                                        )}
                                    >
                                        Open
                                    </Button>
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>

                <p className="r7-person-meds__stage">{stage}</p>
            </div>
        </AppShell>
    );
}
