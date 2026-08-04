<?php

namespace App\Http\Controllers\Frontend3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * frontend3 — the third, parallel front end.
 *
 * Reached only from the "Frontend 3" button in the old Blade header, exactly as
 * Frontend 1 and Frontend 2 are. Login is unchanged and the post-login redirect
 * is not touched.
 *
 * Isolation: every frontend3 response swaps Inertia's root view to `f3`, which
 * loads resources/js/f3.jsx and frontend3/f3.css instead of the frontend2
 * bundle. Doing it here rather than in a middleware alias means app/Http/Kernel.php
 * — a file shared with frontend1 and frontend2 — stays untouched.
 *
 * See docs/care-one-os/FRONTEND3/FRONTEND3-PLAN.md.
 */
class Frontend3Controller extends Controller
{
    /** Where the concept screens live, relative to the project root. */
    private const WIREFRAME_DIR = 'docs/care-one-os/FRONTEND3/wireframes';

    public function __construct()
    {
        // Everything this controller renders uses frontend3's own layout.
        Inertia::setRootView('f3');
    }

    /** The frontend3 landing page. */
    public function index(Request $request)
    {
        return Inertia::render('Home', [
            'wireframesUrl' => url('/frontend3/wireframes'),
        ]);
    }

    /**
     * Serve a concept screen from the docs folder.
     *
     * The wireframes are documentation, so they live with the rest of the
     * frontend3 paperwork rather than being duplicated into public/. This
     * serves them read-only so they can be clicked through in the browser
     * without keeping a second copy in sync.
     *
     * Only the exact filenames below are servable. The whitelist — not a path
     * sanitiser — is what makes traversal impossible.
     */
    public function wireframe(string $file = 'index.html'): BinaryFileResponse
    {
        $allowed = [
            'index.html',
            'careone-f3.css',
            'careone-dashboard-wireframe.html',
            'careone-medication-round-wireframe.html',
            'careone-mar-wireframe.html',
            'careone-person-profile-wireframe.html',
            'careone-prn-wireframe.html',
            'careone-missed-doses-wireframe.html',
            'careone-stock-pharmacy-wireframe.html',
            'careone-controlled-drugs-wireframe.html',
            'careone-handover-wireframe.html',
            'careone-manager-compliance-wireframe.html',
            'careone-admin-integrations-wireframe.html',
            'careone-ai-workspace-wireframe.html',
        ];

        abort_unless(in_array($file, $allowed, true), 404);

        $path = base_path(self::WIREFRAME_DIR.'/'.$file);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => str_ends_with($file, '.css') ? 'text/css' : 'text/html',
        ]);
    }
}
