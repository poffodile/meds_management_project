/**
 * Medication administration (MAR) outcome codes — the single source of truth.
 * Must stay in sync with the controller's `code` validation rule
 * (MedicationRoundController@record). Previously duplicated as `CODE_LABELS`
 * in MedicationRound.jsx and `CODE_OPTIONS` in RecordDoseModal.jsx.
 */
export const MED_CODES = [
    { value: 'A', label: 'Given' },
    { value: 'S', label: 'Sleeping' },
    { value: 'R', label: 'Refused' },
    { value: 'W', label: 'Withheld' },
    { value: 'N', label: 'Not available' },
    { value: 'O', label: 'Omitted' },
];

/** code -> label lookup, e.g. CODE_LABELS.A === 'Given'. */
export const CODE_LABELS = MED_CODES.reduce((m, c) => ({ ...m, [c.value]: c.label }), {});

/** Outcome codes that require a structured reason (refused / not given / omitted). */
export const REASON_REQUIRED_CODES = ['R', 'N', 'O'];

/** Common reasons offered when a dose is refused. */
export const REFUSAL_REASONS = [
    'Resident refused',
    'Resident asleep',
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
