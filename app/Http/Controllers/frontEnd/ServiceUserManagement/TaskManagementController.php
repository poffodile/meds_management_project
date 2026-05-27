<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ServiceUser;
use App\Models\ServiceUserManagement\ServiceUserTask;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TaskManagementController extends Controller
{
    public function index($service_user_id)
    {
        // dd($service_user_id);
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $data['page'] = 'task_management';
        $data['service_user_id'] = $service_user_id;
        $service_user = ServiceUser::select('home_id', 'name')->where('id', $service_user_id)->first();

        if (!empty($service_user)) {
            if ($service_user->home_id != $home_id) {
                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }
        }

        $data['page_title'] = trim($service_user->name) . "'s Task";

        // Fetch tasks for this home_id, service_user_id, and current user
        $user_id = Auth::user()->id;
        $tasks = ServiceUserTask::where('home_id', $home_id)
            ->where('service_user_id', $service_user_id)
            ->where('user_id', $user_id)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->get();
        $data['tasks'] = $tasks;

        return view('frontEnd.serviceUserManagement.taskManagement.task', $data);
    }

    public function store(Request $request, $service_user_id)
    {
        // Support both AJAX (JSON/form) and normal POST
        $data = $request->all();

        $rules = [
            'task' => 'required|string|max:191',
            'date' => 'required|date_format:d-m-Y',
            'time' => 'required|string|max:50',
            'status' => 'required|in:active,inactive',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // verify service user belongs to current user's home
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $service_user = ServiceUser::select('id', 'home_id')->where('id', $service_user_id)->first();
        if (empty($service_user) || $service_user->home_id != $home_id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized or service user not found'], 403);
            }
            return redirect('/')->with('error', UNAUTHORIZE_ERR);
        }

        // convert date
        try {
            $date = Carbon::createFromFormat('d-m-Y', $data['date'])->format('Y-m-d');
        } catch (\Exception $e) {
            $date = null;
        }

        try {
            $task = ServiceUserTask::create([
                'home_id' => $home_id,
                'user_id' => Auth::user()->id,
                'service_user_id' => $service_user_id,
                'task' => $data['task'],
                'date' => $date,
                'time' => $data['time'],
                'status' => $data['status'],
                'comments' => isset($data['comments']) ? $data['comments'] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save ServiceUserTask: ' . $e->getMessage(), ['data' => $data, 'user' => Auth::user()->id]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Failed to save task'], 500);
            }
            return redirect()->back()->with('error', 'Failed to save task')->withInput();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Task saved successfully', 'task' => $task]);
        }

        return redirect()->back()->with('success', 'Task saved successfully');
    }
    /**
     * Delete a ServiceUserTask by ID (AJAX/JSON).
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $task = ServiceUserTask::find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        // Only allow delete if user owns the task and home
        $home_ids = explode(',', $user->home_id);
        if ($task->user_id != $user->id || $task->home_id != $home_ids[0]) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        try {
            $task->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete ServiceUserTask: ' . $e->getMessage(), ['task_id' => $id, 'user' => $user->id]);
            return response()->json(['message' => 'Failed to delete task'], 500);
        }
        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * Update an existing ServiceUserTask's status and comments (AJAX/JSON)
     */
    public function update(Request $request, $id)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive',
            'comments' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find and verify task ownership
        $user = Auth::user();
        $home_ids = explode(',', $user->home_id);
        $home_id = $home_ids[0];

        $task = ServiceUserTask::where('id', $id)
            ->where('home_id', $home_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found or unauthorized'], 404);
        }

        // convert date
        try {
            $date = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
        } catch (\Exception $e) {
            $date = null;
        }

        try {
            // Build update data dynamically — only include fields that exist in request
            $updateData = [];

            if ($request->filled('task')) {
                $updateData['task'] = $request->task;
            }
            if ($date) {
                $updateData['date'] = $date;
            }
            if ($request->filled('time')) {
                $updateData['time'] = $request->time;
            }
            if ($request->filled('status')) {
                $updateData['status'] = $request->status;
            }
            if ($request->has('comments')) {
                $updateData['comments'] = $request->comments;
            }

            // ✅ Update only if there is something to update
            if (!empty($updateData)) {
                $task->update($updateData);
            }

            return response()->json([
                'message' => 'Task updated successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update task: ' . $e->getMessage(), [
                'task_id' => $id,
                'user_id' => $user->id,
                'data' => $request->all()
            ]);
            return response()->json(['message' => 'Failed to update task'], 500);
        }
    }
}
