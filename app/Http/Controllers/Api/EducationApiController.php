<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SuEducationProfile;
use App\Models\SuEducationStaffAssignment;
use App\Models\SuEducationTask;
use App\Models\SuEducationAttendance;
use App\Models\SuEducationNote;
use App\Models\SuEducationResource;
use App\ServiceUser;
use App\User;
use Validator, Carbon\Carbon;

class EducationApiController extends Controller
{
    // STEP 3: Staff logs in and sees assigned children
    public function getAssignedChildren(Request $request, $staff_id = null)
    {
        $id = $staff_id ?? $request->staff_id;
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'staff_id is required'], 400);
        }

        $assignments = SuEducationStaffAssignment::where('staff_id', $id)
            ->where('status', 1)
            ->with('serviceUser:id,name,image')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $assignments
        ]);
    }

    // STEP 4: Staff opens child -> Education section
    public function getEducationProfile(Request $request, $service_user_id = null)
    {
        $id = $service_user_id ?? $request->service_user_id;
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'service_user_id is required'], 400);
        }

        $profile = SuEducationProfile::where('service_user_id', $id)
            ->with('serviceUser:id,name')
            ->where('status', 1)
            ->first();

        if (!$profile) {
            return response()->json(['status' => 'error', 'message' => 'Education profile not found'], 404);
        }

        $profileData = $profile->toArray();
        $profileData['service_user_name'] = $profile->serviceUser->name ?? null;

        $tasks = SuEducationTask::where('education_profile_id', $profile->id)
            ->with('staff:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) {
                $taskData = $task->toArray();
                $staffName = $task->staff->name ?? 'Unknown Staff';
                $dateStr = $task->due_date ? Carbon::parse($task->due_date)->format('M d, Y') : '';
                $taskData['formatted_date_info'] = $dateStr . ' • by ' . $staffName;
                $taskData['staff_name'] = $staffName;
                return $taskData;
            });
        $attendance = SuEducationAttendance::where('education_profile_id', $profile->id)
            ->with('staff:id,name')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($att) {
                $attData = $att->toArray();
                $staffName = $att->staff->name ?? 'Unknown Staff';
                $dateStr = $att->date ? Carbon::parse($att->date)->format('M d, Y') : '';
                $attData['formatted_date'] = $dateStr;
                $attData['formatted_date_info'] = $dateStr . ' • by ' . $staffName;
                $attData['staff_name'] = $staffName;
                return $attData;
            });

        $notes = SuEducationNote::where('education_profile_id', $profile->id)
            ->with('staff:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($note) {
                $noteData = $note->toArray();
                $staffName = $note->staff->name ?? 'Unknown Staff';
                $dateStr = $note->created_at ? Carbon::parse($note->created_at)->format('M d, Y') : '';
                $noteData['formatted_date_info'] = $dateStr . ' • by ' . $staffName;
                $noteData['staff_name'] = $staffName;
                return $noteData;
            });
        $resources = SuEducationResource::where('education_profile_id', $profile->id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'profile' => $profileData,
                'tasks' => $tasks,
                'attendance' => $attendance,
                'notes' => $notes,
                'resources' => $resources
            ]
        ]);
    }
    // Get subjects for a specific education profile (for Add Task dropdown)
    public function getSubjects(Request $request, $profile_id = null)
    {
        $id = $profile_id ?? $request->education_profile_id;
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'education_profile_id is required'], 400);
        }

        $profile = SuEducationProfile::find($id);

        if (!$profile) {
            return response()->json(['status' => 'error', 'message' => 'Education profile not found'], 404);
        }

        $subjects = [];
        if (!empty($profile->subjects)) {
            $subjectsArray = explode(',', $profile->subjects);
            foreach ($subjectsArray as $subject) {
                if (trim($subject) !== '') {
                    $subjects[] = trim($subject);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $subjects
        ]);
    }
    // STEP 5: Staff creates Homework / Task
    public function addTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required',
            'education_profile_id' => 'required',
            'staff_id' => 'required',
            'subject' => 'required',
            'description' => 'required',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
        }

        $task = new SuEducationTask($request->all());

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/education/tasks'), $fileName);
            $task->attachment = 'uploads/education/tasks/' . $fileName;
        }

        if ($task->save()) {
            // STEP 6: System Trigger - Notification would go here
            return response()->json(['status' => 'success', 'message' => 'Task created successfully', 'data' => $task]);
        }
        return response()->json(['status' => 'error', 'message' => 'Could not save task'], 500);
    }

    // STEP 7: Child opens app -> views task & completes it
    public function getChildTasks(Request $request, $service_user_id = null)
    {
        $id = $service_user_id ?? $request->service_user_id;
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'service_user_id is required'], 400);
        }

        $tasks = SuEducationTask::where('service_user_id', $id)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $tasks]);
    }

    public function completeTask(Request $request, $task_id)
    {
        $task = SuEducationTask::find($task_id);
        if (!$task) {
            return response()->json(['status' => 'error', 'message' => 'Task not found'], 404);
        }

        $task->status = 'completed';
        $task->submitted_at = Carbon::now();

        if ($request->hasFile('submission_file')) {
            $file = $request->file('submission_file');
            $fileName = time() . '_sub_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/education/submissions'), $fileName);
            $task->submission_file = 'uploads/education/submissions/' . $fileName;
        }

        if ($task->save()) {
            return response()->json(['status' => 'success', 'message' => 'Task completed successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Could not update task'], 500);
    }

    // Additional flows: Attendance, Notes, Resources
    public function addAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required',
            'education_profile_id' => 'required',
            'staff_id' => 'required',
            'date' => 'required|date',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
        }

        $attendance = SuEducationAttendance::create($request->all());
        return response()->json(['status' => 'success', 'data' => $attendance, 'message' => 'Attendance added successfully']);
    }

    public function addNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required',
            'education_profile_id' => 'required',
            'staff_id' => 'required',
            'notes' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
        }

        $note = SuEducationNote::create($request->all());
        return response()->json(['status' => 'success', 'data' => $note, 'message' => 'Note added successfully']);
    }

    public function addResource(Request $request)
    {
        $resource = new SuEducationResource($request->all());
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/education/resources'), $fileName);
            $resource->file_path = 'uploads/education/resources/' . $fileName;
        }
        $resource->save();
        return response()->json(['status' => 'success', 'data' => $resource]);
    }
}
