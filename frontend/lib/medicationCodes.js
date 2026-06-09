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
