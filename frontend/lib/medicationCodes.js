/**
 * Medication administration (MAR) outcome codes — the single source of truth.
 * Must stay in sync with the controller's `code` validation rule
 * (BuildsMedicationRound::applyRecord). Previously duplicated as `CODE_LABELS`
 * in MedicationRound.jsx and `CODE_OPTIONS` in RecordDoseModal.jsx.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * TWO LISTS, ON PURPOSE (added 2026-08-04)
 *
 * `MED_CODES` is what a user is OFFERED. `CODE_LABELS` is what any stored code
 * MEANS. They used to be the same thing, and that was fine while one front end
 * wrote the records.
 *
 * frontend4 records a wider outcome set (away, omitted-operational, vomited,
 * not required) taken from the Care One OS UX Specification. Those codes reach
 * the same `mar_administrations` table that frontends 1 and 2 read from, and
 * `CODE_LABELS[code]` is a plain lookup used across about twenty of their
 * screens — so a code they had never heard of rendered BLANK where an outcome
 * should be.
 *
 * Widening `CODE_LABELS` fixes that: every screen can now name every code.
 * `MED_CODES` is deliberately left at the original six, so no dropdown in
 * frontend1 or frontend2 changes and no existing journey behaves differently.
 *
 * If you add a code here, add it to the server's validation rule too.
 * ─────────────────────────────────────────────────────────────────────────────
 */
export const MED_CODES = [
    { value: 'A', label: 'Given' },
    { value: 'S', label: 'Asleep — not given' },
    { value: 'R', label: 'Refused' },
    { value: 'W', label: 'Withheld' },
    { value: 'N', label: 'Not available' },
    { value: 'O', label: 'Omitted' },
];

/**
 * code -> label lookup for EVERY code that can appear in the database,
 * e.g. CODE_LABELS.A === 'Given'.
 *
 * Covers the six offered above plus the codes frontend4 can write. Read-only
 * consumers should use this; anything building a list of choices should use
 * `MED_CODES` (frontends 1 and 2) or frontend4's own list.
 */
export const CODE_LABELS = {
    ...MED_CODES.reduce((m, c) => ({ ...m, [c.value]: c.label }), {}),

    // Written by frontend4 only. Present here so every screen can name them.
    AW: 'Away — not given',
    OP: 'Omitted — operational',
    VO: 'Vomited / spat out',
    NR: 'Not required',
};

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
 *
 * Same principle applied to the codes added 2026-08-04:
 *   · 'OP' (omitted — operational) REQUIRES one. An operational omission is a
 *     failure of the service, not of the person, and the specification asks for
 *     the reason and a responsible owner.
 *   · 'VO' (vomited / spat out) REQUIRES one — timing and observed amount are
 *     what a clinician needs before they can advise, and the system must never
 *     suggest redosing.
 *   · 'AW' (away) and 'NR' (not required) do NOT. The code already states the
 *     reason in both cases, exactly as it does for 'S'.
 */
export const REASON_REQUIRED_CODES = ['R', 'W', 'N', 'O', 'OP', 'VO'];

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
