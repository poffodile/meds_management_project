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

        $shifts = ScheduledShift::where('staff_id', $staffId)
            ->select('id', 'start_date', 'start_time', 'end_time', 'shift_type', 'notes', 'tasks')
            ->where('start_date', $validated['date'])
            ->orderBy('start_time')
            ->get();
        $data = $shifts->map(function ($shift) {
            $shift->day = \Carbon\Carbon::parse($shift->start_date)->format('l');
            $shift->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
            $shift->end_time   = \Carbon\Carbon::parse($shift->end_time)->format('H:i');
            return $shift;
        });

        return response()->json([
            'success' => true,
            'message' => 'Shifts retrieved successfully.',
            'data' => $data,
            'total_shifts' => $data->count(),
        ]);
    }

    public function schedule_shifts_details(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => ['required', 'integer', 'exists:scheduled_shifts,id'],
        ]);

        $shiftId = $validated['shift_id'];

        $shifts = ScheduledShift::with(['recurrence', 'assessments', 'documents'])
            ->where('id', $shiftId)
            ->get();

        $data = $shifts->map(function ($shift) {
            $shift->day = \Carbon\Carbon::parse($shift->start_date)->format('l');
            $shift->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
            $shift->end_time   = \Carbon\Carbon::parse($shift->end_time)->format('H:i');

            if ($shift->assessments) {
                foreach ($shift->assessments as $assessment) {
                    $assessment->assessment_doc_url = $assessment->assessment_doc ? url('public/' . $assessment->assessment_doc) : '';
                }
            }

            if ($shift->documents) {
                foreach ($shift->documents as $document) {
                    $document->doc_file_url = $document->doc_file ? url('public/' . $document->doc_file) : '';
                    $document->fileType = 'Document';
                    $document->form_url = '';
                    if ($document->form_id) {
                        $document->fileType = 'Form';
                        $document->form_url = url('roster/schedule-shift/form_template/view/' . $document->id);
                    }
                }
            }

            return $shift;
        });

        return response()->json([
            'success' => true,
            'message' => 'Shift details retrieved successfully.',
            'data' => $data->first(),
        ]);
    }

    public function schedule_shifts_update_status(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'shift_id' => ['required', 'integer', 'exists:scheduled_shifts,id'],
            'status' => ['required', 'string', 'in:completed,in_progress'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $validated = $validator->validated();
        $shiftId = $validated['shift_id'];
        $status = $validated['status'];

        try {
            $updated = ScheduledShift::where('id', $shiftId)
                ->update(['status' => $status]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift status updated successfully.',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift status was not updated.',
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating shift: ' . $e->getMessage(),
            ], 200);
        }
    }
    public function get_unassigned_shifts(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'home_id' => ['required', 'integer', 'exists:home,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $validated = $validator->validated();
        $homeId = $validated['home_id'];

        $shifts = ScheduledShift::whereNull('service_user_id')
            ->whereNull('staff_id')
            ->where('home_id', $homeId)
            ->select('id', 'start_date', 'start_time', 'end_time', 'shift_type', 'notes', 'tasks')
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        if ($shifts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No unassigned shifts found.',
                'data'    => [],
                'total_shifts' => 0,
            ], 200);
        }

        $data = $shifts->map(function ($shift) {
            $shift->day = \Carbon\Carbon::parse($shift->start_date)->format('l');
            $shift->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
            $shift->end_time   = \Carbon\Carbon::parse($shift->end_time)->format('H:i');
            return $shift;
        });

        return response()->json([
            'success' => true,
            'message' => 'Unassigned shifts retrieved successfully.',
            'data' => $data,
            'total_shifts' => $data->count(),
        ]);
    }

    public function assignShift(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'shift_id' => ['required', 'integer', 'exists:scheduled_shifts,id'],
            'staff_id' => ['nullable', 'integer', 'exists:user,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $shift = ScheduledShift::find($request->shift_id);

        $shift->staff_id = $request->staff_id;
        $shift->status   = 'assigned';
        $shift->save();

        return response()->json([
            'success' => true,
            'message' => 'Shift assigned successfully.',
        ], 200);
    }
}
