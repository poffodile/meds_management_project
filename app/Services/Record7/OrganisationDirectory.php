<?php

namespace App\Services\Record7;

use App\Models\Record7\Organisation;

/**
 * Resolves the organisation a person types on the first sign-in screen.
 *
 * Section 0.1. Two rules matter and both are deliberate:
 *
 *   Matching is forgiving. Staff type an organisation name from memory, often
 *   on a phone keyboard, so leading and trailing space, repeated internal
 *   space and capitalisation must all be ignored. "  omega   care  group  "
 *   resolves exactly as "Omega Care Group" does.
 *
 *   Matching is silent. There is no list, no dropdown, no suggestion and no
 *   "did you mean". An anonymous visitor must not be able to use this screen
 *   to discover which organisations are customers, so an unrecognised name
 *   returns null and the caller answers with the same wording it uses for a
 *   wrong password.
 */
class OrganisationDirectory
{
    /**
     * Lower-case, trim, and collapse every run of whitespace to one space.
     *
     * This is the value stored in record7_organisations.name_normalised, so a
     * typed name and a stored name are always compared in the same shape.
     */
    public function normalise(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)));
    }

    /** The organisation for a typed name, or null. Never throws, never hints. */
    public function match(string $typedName): ?Organisation
    {
        $normalised = $this->normalise($typedName);

        if ($normalised === '') {
            return null;
        }

        $organisation = Organisation::where('name_normalised', $normalised)->first();

        // An organisation that is suspended or closed must behave exactly like
        // one that does not exist. Saying "that organisation is suspended"
        // would confirm it to an anonymous visitor.
        return $organisation && $organisation->isActive() ? $organisation : null;
    }
}
