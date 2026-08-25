<?php

namespace App\Http\Controllers\Record7;

use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * The Record7 component showcase.
 *
 * Every component and every important state on one page, so the design system
 * can be reviewed as a system rather than discovered a screen at a time. It is
 * also where a change to r7-tokens.css is checked: alter one token and the
 * effect is visible here immediately, across everything.
 *
 * DEVELOPMENT ONLY, ENFORCED THREE WAYS
 *   1. The route is not registered at all when the environment is production.
 *   2. This action aborts 404 if it is somehow reached there.
 *   3. Record7ShowcaseTest asserts both.
 *
 * It carries no real data, reads nothing from the database and needs no
 * sign-in, so there is nothing here to leak — but it still must not exist in
 * production, because a component gallery tells an attacker what the product
 * is made of.
 */
class ShowcaseController extends R7Controller
{
    public function index(Request $request)
    {
        abort_if(app()->environment('production'), 404);

        $this->useR7Layout();

        return Inertia::render('Showcase', [
            'environment' => app()->environment(),
        ]);
    }
}
