<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScheduledShift;

class ScheduleController extends Controller
{
    public function schedule_shifts(Request $request)
    {
        // Validate staff_id – must be present and correspond to an existing staff user
        $validated = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:user,id'],
            'date' => ['required', 'date'],
        ]);

        $staffId = $validated['staff_id'];

        // Retrieve all shifts for the staff member for the specific date
        $shifts = ScheduledShift::where('staff_id', $staffId)
            ->select('id', 'start_date', 'start_time', 'end_time', 'shift_type', 'notes', 'tasks')
            ->where('start_date', $validated['date'])
            ->orderBy('start_time')
            ->get();

        // Transform each shift: add day name and format times to HH:mm (24-hour)
        $data = $shifts->map(function ($shift) {
            $shift->day = \Carbon\Carbon::parse($shift->start_date)->format('l');
            $shift->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
            $shift->end_time   = \Carbon\Carbon::parse($shift->end_time)->format('H:i');
            return $shift;
        });

        return response()->json([
            'success' => true,
            'total_shifts' => $data->count(),
            'data' => $data,
            'message' => 'Shifts retrieved successfully.',
        ]);
    }
}
