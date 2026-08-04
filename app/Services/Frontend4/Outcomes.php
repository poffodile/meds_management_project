<?php

namespace App\Services\Frontend4;

use App\Services\Medication\DoseOutcome;

/**
 * What can happen to a dose, in frontend4.
 *
 * THE DECISION BEHIND THIS FILE (C1, settled 2026-08-04)
 * Three vocabularies existed and none of them agreed: the UX Specification's
 * eight outcomes, the visual specification's eight different ones, and the
 * database's six letter codes. This is the merge.
 *
 * The UX Specification's taxonomy was taken as the base because it is the only
 * one that separates a CLINICAL omission from an OPERATIONAL one — its own rule
 * says never to label every non-administration "missed", because doing so
 * erases the context that reporting depends on. "Asleep" and "away" were added
 * from the visual specification; both are real and neither was covered.
 *
 * LATE IS NOT AN OUTCOME.
 * A dose given at 10:30 for a 09:00 slot is *given, late* — not *late*. The
 * specification lists Late among the outcomes, but the database already keeps
 * `is_late` as a flag separate from `code`, and that is the right shape: an
 * outcome says what happened, lateness says when.
 *
 * NEVER SHOW A BARE CODE.
 * The specification is explicit that a user must be able to see the full
 * meaning, not an unexplained letter. Use label() — there is no path here that
 * renders the raw code.
 */
class Outcomes
{
    /**
     * The outcomes frontend4 offers, in the order a carer meets them.
     *
     * `given`  — did the medicine actually go in? Drives stock movement,
     *            controlled-drug witnessing and the PRN maximum.
     * `reason` — must the person say why? Enforced on the server in
     *            BuildsMedicationRound::applyRecord, not just here.
     */
    public const ALL = [
        'A'  => ['label' => 'Given',                 'status' => 'given',    'given' => true,  'reason' => false],
        'R'  => ['label' => 'Declined',              'status' => 'refused',  'given' => false, 'reason' => true],
        'S'  => ['label' => 'Asleep',                'status' => 'omitted',  'given' => false, 'reason' => false],
        'AW' => ['label' => 'Away',                  'status' => 'omitted',  'given' => false, 'reason' => false],
        'N'  => ['label' => 'Not available',         'status' => 'omitted',  'given' => false, 'reason' => true],
        'W'  => ['label' => 'Omitted — clinical',    'status' => 'omitted',  'given' => false, 'reason' => true],
        'OP' => ['label' => 'Omitted — operational', 'status' => 'omitted',  'given' => false, 'reason' => true],
        'VO' => ['label' => 'Vomited or spat out',   'status' => 'omitted',  'given' => false, 'reason' => true],
        'NR' => ['label' => 'Not required',          'status' => 'upcoming', 'given' => false, 'reason' => false],
        'O'  => ['label' => 'Other outcome',         'status' => 'omitted',  'given' => false, 'reason' => true],
    ];

    /**
     * Part administered — recognised, deliberately not offered yet.
     *
     * Some medicine went in, so stock must move and a controlled drug still
     * needs a witness. But seven places in BuildsMedicationRound decide that by
     * comparing `code === 'A'` directly, so a part administration would skip
     * every one of those gates — the same class of bug as counting a sleeping
     * resident's dose as given. Those comparisons must be replaced with one
     * shared helper first.
     */
    public const DEFERRED = ['PA' => 'Part administered'];

    /**
     * Helper wording for the outcomes where "why" is not obvious.
     *
     * Shown under the reason field, so a carer under time pressure is told what
     * the record actually needs rather than being left to guess.
     */
    public const REASON_HINTS = [
        'R'  => 'What was offered, and what they said. Note any advice given.',
        'N'  => 'Why it was unavailable, and whether the pharmacy has been contacted.',
        'W'  => 'Who made the clinical decision, and what they instructed.',
        'OP' => 'What went wrong, and who is following it up.',
        'VO' => 'How long after the dose, and how much was seen. Do not redose without advice.',
        'O'  => 'Describe what happened.',
    ];

    /** Every code frontend4 may record. */
    public function codes(): array
    {
        return array_keys(self::ALL);
    }

    /** The full meaning of a stored code — never the bare letter. */
    public function label(?string $code): ?string
    {
        return $code === null ? null : (self::ALL[$code]['label'] ?? self::DEFERRED[$code] ?? $code);
    }

    /**
     * Did the resident actually receive this dose?
     *
     * Delegates to {@see DoseOutcome}, which every other part of the
     * application now uses too. Deliberately NOT answered from the `given`
     * column in ALL above — two lists that must agree is the problem this was
     * built to end, and the `given` flag there is documentation of intent, not
     * a second source of truth.
     */
    public function isGiven(?string $code): bool
    {
        return DoseOutcome::isGiven($code);
    }

    /** Must this outcome carry a reason? Mirrors the server-side check. */
    public function needsReason(?string $code): bool
    {
        return (bool) (self::ALL[$code]['reason'] ?? false);
    }

    /** Which frontend4 status tint and word this outcome displays as. */
    public function status(?string $code): string
    {
        return self::ALL[$code]['status'] ?? 'upcoming';
    }

    /** The list for the React side, ready to render as choices. */
    public function forClient(): array
    {
        $out = [];

        foreach (self::ALL as $code => $meta) {
            $out[] = [
                'code' => $code,
                'label' => $meta['label'],
                'status' => $meta['status'],
                'given' => $meta['given'],
                'needsReason' => $meta['reason'],
                'hint' => self::REASON_HINTS[$code] ?? null,
            ];
        }

        return $out;
    }
}
