<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

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
use Auth, DB, Validator;

class EducationController extends Controller
{
    public function addProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required|exists:service_user,id',
            'school_name' => 'required',
            'grade' => 'required',
            'academic_year' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $home_id = Auth::user()->home_id;
        $ex_home_id = explode(',', $home_id);
        $home_id = $ex_home_id[0];

        $profile = new SuEducationProfile();
        $profile->service_user_id = $request->service_user_id;
        $profile->school_name = $request->school_name;
        $profile->grade = $request->grade;
        $profile->subjects = $request->subjects;
        $profile->academic_year = $request->academic_year;
        $profile->home_id = $home_id;
        $profile->created_by = Auth::user()->id;
        $profile->status = 1;

        if ($profile->save()) {
            return redirect()->back()->with('success', 'Education Profile added successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function assignStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required|exists:service_user,id',
            'staff_id' => 'required|exists:user,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $assignment = SuEducationStaffAssignment::updateOrCreate(
            ['service_user_id' => $request->service_user_id, 'staff_id' => $request->staff_id],
            ['assigned_by' => Auth::user()->id, 'status' => 1]
        );

        if ($assignment) {
            return redirect()->back()->with('success', 'Staff assigned successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function monitorTimeline($service_user_id)
    {
        $tasks = SuEducationTask::with('staff')->where('service_user_id', $service_user_id)->get();
        $attendance = SuEducationAttendance::with('staff')->where('service_user_id', $service_user_id)->get();
        $notes = SuEducationNote::with('staff')->where('service_user_id', $service_user_id)->get();
        
        $profile = SuEducationProfile::with('serviceUser')->where('service_user_id', $service_user_id)->first();
        $timeline = collect();
        if($profile) {
            $timeline->push(['type' => 'profile', 'date' => $profile->created_at, 'data' => $profile]);
        }
        foreach($tasks as $task) {
            $timeline->push(['type' => 'task', 'date' => $task->created_at, 'data' => $task]);
            if($task->submitted_at) {
                $timeline->push(['type' => 'task_completion', 'date' => $task->submitted_at, 'data' => $task]);
            }
        }
        foreach($attendance as $att) {
            $timeline->push(['type' => 'attendance', 'date' => $att->created_at, 'data' => $att]);
        }
        foreach($notes as $note) {
            $timeline->push(['type' => 'note', 'date' => $note->created_at, 'data' => $note]);
        }

        $timeline = $timeline->sortByDesc('date');

        return view('frontEnd.roster.client.elements.education_timeline', compact('timeline'))->render();
    }

    public function addAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required|exists:service_user,id',
            'education_profile_id' => 'required',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $attendance = new SuEducationAttendance();
        $attendance->service_user_id = $request->service_user_id;
        $attendance->education_profile_id = $request->education_profile_id;
        $attendance->staff_id = Auth::user()->id;
        $attendance->date = $request->date;
        $attendance->status = $request->status;
        $attendance->notes = $request->notes;

        if ($attendance->save()) {
            return redirect()->back()->with('success', 'Attendance logged successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function addTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required|exists:service_user,id',
            'education_profile_id' => 'required',
            'subject' => 'required',
            'title' => 'required',
            'description' => 'required',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $task = new SuEducationTask($request->all());
        $task->staff_id = Auth::user()->id;
        $task->status = 'pending';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/education/tasks'), $fileName);
            $task->attachment = 'uploads/education/tasks/' . $fileName;
        }

        if ($task->save()) {
            return redirect()->back()->with('success', 'Task assigned successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function addNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required|exists:service_user,id',
            'notes' => 'required',
            'type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $note = new SuEducationNote($request->all());
        $note->staff_id = Auth::user()->id;

        if ($note->save()) {
            return redirect()->back()->with('success', 'Note added successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function addResource(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_user_id' => 'required|exists:service_user,id',
            'title' => 'required',
            'subject' => 'nullable|string',
            'file' => 'nullable|file',
            'link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $resource = new SuEducationResource($request->all());
        $resource->staff_id = Auth::user()->id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/education/resources'), $fileName);
            $resource->file_path = 'uploads/education/resources/' . $fileName;
        }

        if ($resource->save()) {
            return redirect()->back()->with('success', 'Resource uploaded successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function rateTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:su_education_tasks,id',
            'rating' => 'required|integer|min:1|max:5',
            'staff_feedback' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $task = SuEducationTask::find($request->task_id);
        if ($task->status !== 'completed') {
            return redirect()->back()->with('error', 'Only completed tasks can be rated.');
        }

        $task->rating = $request->rating;
        $task->staff_feedback = $request->staff_feedback;

        if ($task->save()) {
            return redirect()->back()->with('success', 'Task rated successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }
}
