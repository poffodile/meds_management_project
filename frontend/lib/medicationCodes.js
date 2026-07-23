/**
 * Medication administration (MAR) outcome codes — the single source of truth.
 * Must stay in sync with the controller's `code` validation rule
 * (MedicationRoundController@record). Previously duplicated as `CODE_LABELS`
 * in MedicationRound.jsx and `CODE_OPTIONS` in RecordDoseModal.jsx.
 */
export const MED_CODES = [
    { value: 'A', label: 'Given' },
    { value: 'S', label: 'Asleep — not given' },
    { value: 'R', label: 'Refused' },
    { value: 'W', label: 'Withheld' },
    { value: 'N', label: 'Not available' },
    { value: 'O', label: 'Omitted' },
];

/** code -> label lookup, e.g. CODE_LABELS.A === 'Given'. */
export const CODE_LABELS = MED_CODES.reduce((m, c) => ({ ...m, [c.value]: c.label }), {});

/**
 * Codes where the resident actually received the medicine.
 *
 * 'A' ONLY. 'S' was previously counted here and it was wrong on the plain facts:
 * if someone was asleep, the medicine did not go in. Counting it as given meant a
 * sleeping child burned a PRN daily allowance and restarted the minimum-interval
 * clock, so when they woke in pain the system refused them their pain relief. It
 * also let a controlled drug be logged as administered with no witness and no
 * stock movement, since those gates check for 'A'.
 *
 * Anything that needs "did the resident get this?" must use this — never an
 * inline code comparison. There were four separate definitions of "given" in this
 * codebase and they had already drifted apart.
 */
export const GIVEN_CODES = ['A'];

/** Did the resident actually receive this dose? */
export const isGivenCode = (code) => GIVEN_CODES.includes(code);

/**
 * Outcome codes that require a structured reason.
 *
 * 'W' (Withheld) is included: withholding is a deliberate clinical decision to not
 * give a prescribed dose, so the record must say WHY (REQ-MED-06 names it explicitly).
 * It was previously absent, which let a dose — including a controlled drug — be
 * withheld with no auditable explanation.
 *
 * 'S' is deliberately absent: the code already states the reason ("asleep"), so
 * demanding a second explanation is busywork during a round. It is still a
 * NOT-given outcome — see GIVEN_CODES.
 */
export const REASON_REQUIRED_CODES = ['R', 'W', 'N', 'O'];

/**
 * Common reasons offered when a dose is refused.
 *
 * "Resident asleep" was removed: asleep is its own outcome code ('S'), not a
 * refusal. Offering both meant the same real event could be recorded two
 * contradictory ways — as a refusal, or (formerly) as a dose given.
 */
export const REFUSAL_REASONS = [
    'Resident refused',
    'Resident unwell / nauseous',
    'Spat out / not swallowed',
    'Resident absent',
    'Other (see notes)',
];

/** Common reasons offered when a dose is not given / omitted / withheld. */
export const OMISSION_REASONS = [
    'Out of stock / unavailable',
    'Resident absent',
    'Nil by mouth',
    'Withheld — clinical advice',
    'Dose already given',
    'Other (see notes)',
];
