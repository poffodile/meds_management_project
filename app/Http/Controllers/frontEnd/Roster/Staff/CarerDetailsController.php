<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Staff\StaffService;
use App\Models\Staff\Documents;
use App\Models\Staff\UserNote;
use Illuminate\Support\Facades\Storage;
use App\LoginInActivity;
use App\Models\Staff\StaffSupervision;

class CarerDetailsController extends Controller
{

    protected StaffService $staffService;

    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function carer_details($carer_id)
    {
        if (!$carer_id) {
            abort(400, 'User ID is required.');
        }
        // dd($carer_id);
        $staffDetails = $this->staffService->getStaffDetails($carer_id);
        $staffDetails->load([
            'working_hours',
            'work_preferences',
            'specific_working_hours'
        ]);

        $type = $staffDetails->working_hours->pluck('type')->unique()->implode(',');

        if (empty($type) && count($staffDetails->specific_working_hours) > 0) {
            $type = "specific";
        }

        $staffDetails->working_hours_type = $type;
        $data['staffDetails'] = $staffDetails;
        $data['superVisionCount'] = StaffSupervision::where('member_id', $carer_id)->count();
        $data['selectedCourseIds'] = $data['staffDetails']->qualifications
            ->pluck('course_id')
            ->toArray();
        // dd($data['staffDetails']);
        $data['user_documents'] = Documents::where('user_id', $carer_id)->get()->toArray();
        $data['courses'] = $this->staffService->courses();
        $data['user_notes'] = UserNote::where('user_id', $carer_id)->orderBy('created_at', 'DESC')->get()->toArray();
        $data['shiftCount'] = \App\Models\ScheduledShift::where('staff_id', $carer_id)->count();

        // dd($data['user_notes']);
        return view('frontEnd.roster.staff.carer_details', $data);
    }

    public function saveDouments(Request $request)
    {
        $request->validate([
            'user_id'        => 'required',
            'document_title' => 'required|string',
            'document_file'  => 'required|file|max:2048',
        ]);

        $filePath = $request->file('document_file')->store('staff-documents', 'public');

        Documents::create([
            'user_id'        => $request->user_id,
            'title'          => $request->document_title,
            'file_path'      => $filePath,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy_documents($id)
    {
        $document = Documents::findOrFail($id);

        // Delete physical file if exists
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Soft delete DB record
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully'
        ]);
    }

    // Notes Section Start
    public function saveNote(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:user,id',
            'note'    => 'required|string',
        ]);

        UserNote::create([
            'user_id' => $request->user_id,
            'note'    => $request->note,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Note added successfully'
        ]);
    }

    public function destroy_notes($id)
    {
        $note = UserNote::findOrFail($id);

        $note->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully'
        ]);
    }
    // Notes Section End

    public function fetch_staff_shifts(Request $request)
    {
        $carer_id = $request->carer_id;
        if (!$carer_id) {
            return response()->json(['success' => false, 'message' => 'Carer ID is required.']);
        }

        $shifts = \App\Models\ScheduledShift::where('staff_id', $carer_id)
            ->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        $data = $shifts->map(function ($shift) {
            $start = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->start_time);
            $end = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->end_time);

            // Handle shifts crossing midnight if end_time < start_time
            if ($end->lt($start)) {
                $end->addDay();
            }

            $durationMinutes = $start->diffInMinutes($end);
            $hours = floor($durationMinutes / 60);
            $minutes = $durationMinutes % 60;
            $durationStr = $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '');

            return [
                'id' => $shift->id,
                'date' => \Carbon\Carbon::parse($shift->start_date)->format('D, M j, Y'),
                'time_range' => \Carbon\Carbon::parse($shift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($shift->end_time)->format('H:i'),
                'hours' => $durationStr,
                'status' => $shift->status,
                'shift_type' => $shift->shift_type,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
