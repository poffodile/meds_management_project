<?php

namespace App\Http\Controllers\Frontend3;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

/**
 * Base for every frontend3 controller.
 *
 * Exists for one reason: to make sure every frontend3 response renders through
 * frontend3's own root view, and to keep the explanation of WHY in one place.
 */
abstract class F3Controller extends Controller
{
    /**
     * Point Inertia at frontend3's own root view (resources/views/f3.blade.php).
     *
     * MUST be called from inside an action, NOT from a constructor.
     *
     * Laravel instantiates a controller while *gathering* route middleware —
     * before the middleware pipeline runs. So a constructor call happens first,
     * and then HandleInertiaRequests::handle() overwrites the root view back to
     * 'app'. The page then boots resources/js/app.jsx, cannot find the frontend3
     * page under ./Pages/, and renders blank with
     * "Cannot read properties of undefined (reading 'default')".
     *
     * Calling it inside the action runs after that middleware. Every frontend3
     * action must call it as its first line.
     */
    protected function useF3Layout(): void
    {
        Inertia::setRootView('f3');
    }
}
