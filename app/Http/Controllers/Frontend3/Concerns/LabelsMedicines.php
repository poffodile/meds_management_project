<?php

namespace App\Http\Controllers\Frontend3\Concerns;

/**
 * One place that decides how a medicine is named on screen.
 *
 * Shared by every frontend3 page so Today and the Round can never disagree
 * about what a medicine is called.
 */
trait LabelsMedicines
{
    /**
     * A medicine label that does not repeat itself.
     *
     * Many prescriptions already carry the strength inside the name
     * ("Risperidone 500microgram tablets"), so appending the strength column
     * produces "Risperidone 500microgram tablets 500mcg". Only add the strength
     * when the name does not already express it.
     */
    protected function medLabel(array $row): string
    {
        $name = trim((string) ($row['medication_name'] ?? 'Medicine'));
        $strength = trim((string) ($row['strength'] ?? ''));

        if ($strength === '') {
            return $name;
        }

        // Compare on digits+letters only, so "500microgram" and "500mcg" both
        // reduce to something the name can be tested against.
        $norm = fn ($s) => preg_replace('/[^a-z0-9]/', '', strtolower($s));

        if (str_contains($norm($name), $norm($strength))) {
            return $name;
        }

        // Also catch the unit-spelling mismatch: same number, different unit word.
        if (preg_match('/^(\d+(?:\.\d+)?)/', $strength, $m)) {
            $pattern = '/(?:^|[^0-9.])'.preg_quote($m[1], '/').'\s*(?:mcg|microgram|micrograms|mg|milligram|milligrams|g|ml|unit|units)/i';
            if (preg_match($pattern, $name)) {
                return $name;
            }
        }

        return $name.' '.$strength;
    }
}
