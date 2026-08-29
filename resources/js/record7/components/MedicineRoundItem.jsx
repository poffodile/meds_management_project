import React from 'react';
import StatusLabel from './StatusLabel.jsx';
import SupportType from './SupportType.jsx';
import Icon from './Icon.jsx';
import Button from './Button.jsx';

/**
 * One medicine, as it needs to be read a second before it is given.
 *
 * NO PERSON'S NAME IN HERE. The identity is anchored at the top of the screen
 * and repeating it on every item trains people to stop reading it — which is
 * exactly the habit the identity check exists to prevent.
 *
 * READ-ONLY BY CONSTRUCTION. There is no control, no form and no handler on
 * this component. Section 2.1 is the check before giving; recording is 2.2, and
 * a button here that did nothing would be worse than no button at all.
 *
 * Missing fields are named rather than rendered blank. An empty space where a
 * route should be reads as "no route needed"; "Route not recorded" reads as
 * what it is.
 */
/**
 * How an already-recorded outcome must LOOK.
 *
 * Not every recorded outcome is a good one. Painting them all in the success
 * colour because "something was recorded" is how a medicine that never left the
 * trolley ends up reading as a completed administration — which is precisely
 * the mistake a round screen exists to prevent.
 *
 * "Not available" also has to say WHAT was not available. Everywhere else on
 * this screen availability is about the person — "In hospital", "At home" — so
 * the bare word next to a medicine is genuinely ambiguous. It means the
 * medicine.
 */
const OUTCOME = {
    given: { tone: 'success' },
    self_administered: { tone: 'success' },
    refused: { tone: 'warning' },
    withheld: { tone: 'warning' },
    not_available: { tone: 'error' },
    missed: { tone: 'error' },
};

export default function MedicineRoundItem({ medicine, onRecord = null }) {
    const {
        name, strength, form, controlled, dose, route, dueAt, directions,
        timeSensitive, support, supportWord, supportMeaning,
        changed, recorded, recordedOutcome, recordedCode, recordedWord,
        missing = [], late, latePhrase, canBeGiven, blockedReason,
        recordedAt, recordedBy, recordedLatePhrase,
    } = medicine;

    return (
        <li className={`r7-med-item${timeSensitive ? ' r7-med-item--critical' : ''}`}>
            <div className="r7-med-item__head">
                <span className="r7-med-item__name">
                    {name}
                    {strength ? <span className="r7-med-item__strength">{strength}</span> : null}
                    {form ? <span className="r7-med-item__form">{form}</span> : null}
                </span>

                <span className="r7-med-item__due">
                    <span className="r7-med-item__time">{dueAt}</span>
                    {late ? (
                        <span className="r7-med-item__late">{latePhrase}</span>
                    ) : null}
                </span>
            </div>

            <div className="r7-med-item__what">
                <span className="r7-med-item__dose">{dose ?? 'Dose not recorded'}</span>
                <span className="r7-med-item__route">{route ?? 'Route not recorded'}</span>
            </div>

            <div className="r7-med-item__flags">
                {timeSensitive ? <StatusLabel tone="warning">Time critical</StatusLabel> : null}
                {controlled ? <StatusLabel tone="warning">Controlled drug</StatusLabel> : null}
                {/* No self-administration pill here: the arrangement is stated
                    directly below, with what it actually means. The same word
                    twice, a line apart, is noise — and it read as though the
                    pill and the panel were two different facts. */}
                {recorded ? (
                    <StatusLabel tone={OUTCOME[recordedCode]?.tone ?? 'neutral'}>
                        {recordedWord ?? recordedOutcome}
                    </StatusLabel>
                ) : null}
            </div>

            {/* The panel is for the arrangements somebody can get WRONG. Painting
                the ordinary case in the same block on every medicine trains
                people to skim past it, and then the one that says "prompted"
                gets skimmed too. */}
            <SupportType
                type={support}
                word={supportWord}
                meaning={supportMeaning}
                compact={support === 'staff_administered'}
            />

            {directions ? (
                <p className="r7-med-item__directions">
                    <Icon name="info" className="r7-icon r7-icon--small" />
                    <span>{directions}</span>
                </p>
            ) : null}

            {changed ? (
                <p className="r7-med-item__changed">
                    Changed {changed.on}{changed.note ? ` — ${changed.note}` : ''}
                </p>
            ) : null}

            {missing.length ? (
                <p className="r7-med-item__missing">
                    Not recorded in Record7: {missing.join(', ')}. Check the chart before giving.
                </p>
            ) : null}

            {/* WHAT HAPPENED STAYS ON THE SCREEN.
                A recorded medicine goes quiet — it does not disappear. A worker
                who has just signed for something must be able to look at it
                again and see the time and the name, or the only way to check is
                to record it a second time. */}
            {recorded ? (
                <p className="r7-med-item__record">
                    {recordedWord ?? recordedOutcome}
                    {recordedAt ? ` at ${recordedAt}` : ''}
                    {recordedBy ? ` by ${recordedBy}` : ''}
                    {/* Said here because the late marker above disappears the
                        moment a dose is answered, and a medicine given eight
                        hours late must not read like one given on time. */}
                    {recordedLatePhrase ? (
                        <span className="r7-med-item__recordlate">
                            {recordedLatePhrase} after it was due
                        </span>
                    ) : null}
                </p>
            ) : null}

            {/* THE ACTION IS A SEPARATE, DELIBERATE STEP.
                It opens a confirmation screen; it does not record anything. The
                whole row is not a control, so a tap while scrolling cannot sign
                for a dose, and the button sits at the end of the item where a
                thumb reaches it on purpose rather than in passing. */}
            {!recorded && onRecord ? (
                <div className="r7-med-item__action">
                    {canBeGiven ? (
                        <Button variant="secondary" size="small" onClick={onRecord}>
                            Record as given
                        </Button>
                    ) : (
                        <p className="r7-med-item__held">{blockedReason}</p>
                    )}
                </div>
            ) : null}
        </li>
    );
}
