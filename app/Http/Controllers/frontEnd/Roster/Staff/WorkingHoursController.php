<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Staff\WorkingHoursService;

class WorkingHoursController extends Controller
{
    public function saveWorkingHours(Request $request, WorkingHoursService $service)
    {
        $request->validate([
            'pattern' => 'required|in:weekly,alternate,specific_dates'
        ]);

        $service->save(auth()->user(), $request);

        return response()->json(['success' => true]);
    }
}
