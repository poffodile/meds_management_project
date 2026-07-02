<?php

namespace App\Http\Controllers\frontEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Frontend2 — a second app shell (its own sidebar) that sits alongside the main
 * medication frontend. Renders React/Inertia pages under resources/js/Pages/Frontend2.
 */
class Frontend2Controller extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Frontend2/Home');
    }
}
