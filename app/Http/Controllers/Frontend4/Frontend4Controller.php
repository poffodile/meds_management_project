<?php

namespace App\Http\Controllers\Frontend4;

use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * frontend4 — the fourth, parallel front end.
 *
 * Reached only from the "Frontend 4" button in the old Blade header, exactly as
 * Frontends 1, 2 and 3 are. Login is unchanged and the post-login redirect is
 * not touched.
 *
 * Isolation: every frontend4 response swaps Inertia's root view to `f4`, which
 * loads resources/js/f4.jsx and frontend4/f4.css instead of any other bundle.
 * Doing it here rather than in a middleware alias means app/Http/Kernel.php —
 * a file shared with the other front ends — stays untouched.
 */
class Frontend4Controller extends F4Controller
{
    /** The landing page. One screen for now; more go alongside it in F4Pages/. */
    public function index(Request $request)
    {
        $this->useF4Layout();
        $this->requirePermission(\App\Services\Frontend4\Permissions::MANAGE_SETTINGS);

        return Inertia::render('Home', [
            'buildLabel' => 'Starter build',
        ]);
    }
}
