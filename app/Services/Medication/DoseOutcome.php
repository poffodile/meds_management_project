<?php

namespace App\Services\Medication;

/**
 * The one definition of "did the resident actually receive this dose?"
 *
 * WHY THIS CLASS EXISTS
 * That question was answered in TEN separate places by comparing the outcome
 * letter inline (`$code === 'A'`). They all agreed, which is exactly what makes
 * the arrangement dangerous: ten independent definitions that happen to match
 * until one of them is updated and the others are not.
 *
 * They were not decorative. In order, they gated:
 *   1. BuildsMedicationRound:151 — how many PRN doses count toward the daily maximum
 *   2. BuildsMedicationRound:452 — whether a controlled drug needs a witness
 *   3. BuildsMedicationRound:467 — blocking a controlled drug with no numeric dose
 *   4. BuildsMedicationRound:479 — refusing to record against zero stock
 *   5. BuildsMedicationRound:533 — the PRN maximum and minimum-interval check
 *   6. BuildsMedicationRound:578 — whether this amends a dose already given
 *   7. BuildsMedicationRound:593 — deducting stock
 *   8. MARSheetService:184      — the persisted `given` column
 *   9. MARSheetService:237      — the persisted `given` column
 *  10. MarChartController:123   — counting given doses on the MAR chart
 *
 * Eight and nine are the ones that make this urgent: they write a boolean into
 * the database, so a disagreement there is not a display bug, it is a wrong
 * clinical record.
 *
 * THE FAILURE THIS PREVENTS
 * frontend/lib/medicationCodes.js records it happening already. 'S' (asleep)
 * was once counted as given: a sleeping child burned their PRN daily allowance
 * and restarted the interval clock, so when they woke in pain the system
 * refused them their pain relief. It also let a controlled drug be recorded as
 * administered with no witness and no stock movement, because those gates
 * checked for 'A'.
 *
 * ADDING A CODE HERE CHANGES BEHAVIOUR EVERYWHERE
 * That is the point, and it is also the danger. Anything added to GIVEN_CODES
 * will start requiring a witness, deducting stock and counting against PRN
 * maximums. Do not add one without meaning all of that.
 *
 * Mirrored by GIVEN_CODES in frontend/lib/medicationCodes.js. Change together.
 */
final class DoseOutcome
{
    /**
     * Outcome codes where the medicine actually went into the resident.
     *
     * 'A' only, today. Not 'S' (asleep), not 'R' (declined), not 'AW' (away),
     * not any other non-administration — in every one of those the medicine did
     * not go in, whatever the reason.
     *
     * 'PA' (part administered) belongs here when it is introduced, because some
     * of the dose DID go in: stock must move and a controlled drug still needs
     * a witness. It also needs a quantity, which is why it is not simply added.
     */
    public const GIVEN_CODES = ['A'];

    /**
     * Did the resident actually receive this dose?
     *
     * Anything needing that answer must ask HERE rather than comparing codes
     * inline. There is no second definition to drift from.
     */
    public static function isGiven(?string $code): bool
    {
        return $code !== null && in_array($code, self::GIVEN_CODES, true);
    }

    /** For a query: the codes that count as given. */
    public static function givenCodes(): array
    {
        return self::GIVEN_CODES;
    }
}
