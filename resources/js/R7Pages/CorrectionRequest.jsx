import React from 'react';
import { usePage, useForm } from '@inertiajs/react';
import AppShell from '@record7/components/AppShell.jsx';
import PersonIdentity from '@record7/components/PersonIdentity.jsx';
import Notice from '@record7/components/Notice.jsx';
import Button from '@record7/components/Button.jsx';
import TextLink from '@record7/components/TextLink.jsx';
import Icon from '@record7/components/Icon.jsx';

/**
 * Asking for a recorded administration to be corrected.
 *
 * THE ORIGINAL IS SHOWN, NOT EDITED. What is on the screen is a copy of what
 * was recorded, above a request about it. Nothing on this page can change that
 * record, and the record itself is append-only in the database, so nothing on
 * any other page can either.
 *
 * ASKING IS NOT DECIDING, and the screen says so out loud rather than letting
 * somebody submit a request and assume it has taken effect. Even a manager who
 * happens to hold the approval permission is told their request still has to be
 * decided — from here it is a request like any other.
 *
 * WHY IT ASKS FOR AN OUTCOME AND NOT JUST A NOTE. The approval path will only
 * carry out what the requester asked for: a manager may approve or decline, and
 * may not substitute a different outcome. A request that does not say what the
 * record should say instead is one nobody can approve, so it is refused here,
 * where the person who was there can still answer it.
 */
export default function CorrectionRequest({
    house, person, record, outcomes, blockedReason, authority, stage, urls,
}) {
    const failed = usePage().props.flash?.['r7.error'] ?? null;

    const nav = [
        { key: 'today', label: 'Today', href: urls.today, icon: 'house' },
        { key: 'round', label: 'Round', href: urls.round, icon: 'clock' },
    ];

    const { data, setData, post, processing, errors } = useForm({
        requested_outcome: '',
        detail: '',
    });

    const ready = data.requested_outcome !== '' && data.detail.trim().length >= 10;

    const submit = () => {
        if (!ready || processing || blockedReason) return;

        post(urls.record, { preserveScroll: true });
    };

    return (
        <AppShell urls={urls} nav={nav}>
            <div className="r7-person">
                <header className="r7-person-top">
                    <TextLink className="r7-back-inline" href={urls.back}>
                        <Icon name="arrow" className="r7-icon r7-icon--small" />
                        <span>Back to {urls.backLabel}</span>
                    </TextLink>

                    <p className="r7-person-top__where">
                        <span>{house.name}</span>
                        <span>Correction request</span>
                    </p>
                </header>

                {failed ? (
                    <Notice tone="error" title="Not requested">{failed}</Notice>
                ) : null}

                {blockedReason ? (
                    <Notice tone="warning" title="This cannot be asked about">
                        {blockedReason}
                    </Notice>
                ) : null}

                {person ? (
                    <section className="r7-person-id">
                        <PersonIdentity
                            name={person.fullName}
                            size="large"
                            photo={person.photo}
                            photoState={person.photoState}
                            bornOn={person.bornOn}
                            room={person.room}
                        />
                    </section>
                ) : null}

                {/* ── 1. What the record says now ─────────────────────────── */}
                <section className="r7-person-meds">
                    <div className="r7-person-meds__head">
                        <h2 className="r7-board__title">What the record says now</h2>
                        <span className="r7-board__note">{record.reference}</span>
                    </div>

                    <p>
                        <strong>{record.medicine}{record.strength ? ` ${record.strength}` : ''}</strong>
                        {record.dose ? <> — {record.dose}</> : null}
                        <br />
                        <strong>{record.outcomeWord}</strong>
                        {record.recordedAt ? <> at {record.recordedAt}</> : null}
                        {record.recordedBy ? <> by {record.recordedBy}</> : null}
                        {record.notes ? <><br />{record.notes}</> : null}
                    </p>

                    <Notice tone="info" title="This record will not be changed">
                        A correction adds a new record beside this one and leaves it exactly as it
                        is. Both stay, and the two together are the history.
                    </Notice>
                </section>

                {/* ── 2. What it should say instead ───────────────────────── */}
                {!blockedReason ? (
                    <section className="r7-person-meds">
                        <div className="r7-person-meds__head">
                            <h2 className="r7-board__title">What should it say instead?</h2>
                        </div>

                        {outcomes.map((outcome) => (
                            <label className="r7-outcome__reason" key={outcome.code}>
                                <input
                                    type="radio"
                                    name="requested_outcome"
                                    value={outcome.code}
                                    checked={data.requested_outcome === outcome.code}
                                    onChange={() => setData('requested_outcome', outcome.code)}
                                />
                                <span>{outcome.word}</span>
                            </label>
                        ))}

                        {errors.requested_outcome ? (
                            <Notice tone="error" title="Choose one">{errors.requested_outcome}</Notice>
                        ) : null}

                        <label className="r7-give__notes">
                            <span>What happened? Whoever decides this was not there.</span>
                            <textarea
                                value={data.detail}
                                maxLength={1000}
                                rows={4}
                                onChange={(event) => setData('detail', event.target.value)}
                            />
                        </label>

                        {errors.detail ? (
                            <Notice tone="error" title="Say what happened">{errors.detail}</Notice>
                        ) : null}

                        {/* Said before the button, not after it. Somebody who
                            thinks pressing this fixes the record will not check
                            that it was actually corrected. */}
                        <Notice tone="info" title="This is a request, not a correction">
                            {authority.mayApprove
                                ? 'It goes to the manager queue and still has to be decided there '
                                  + '— including by you, if it is yours to decide. Nothing changes '
                                  + 'until it is approved.'
                                : 'A manager with correction authority will decide it. Nothing '
                                  + 'changes until then.'}
                        </Notice>

                        <div className="r7-give__actions">
                            <Button
                                variant="primary"
                                disabled={!ready || processing}
                                onClick={submit}
                            >
                                Request the correction
                            </Button>

                            <TextLink href={urls.back}>Cancel</TextLink>
                        </div>
                    </section>
                ) : null}

                <p className="r7-person-meds__stage">{stage}</p>
            </div>
        </AppShell>
    );
}
