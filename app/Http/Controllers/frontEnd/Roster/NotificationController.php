<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Services\Staff\NotificationService;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $eventTypes = DB::table('notification_event_type')->orderBy('id')->get();
        return view('frontEnd.roster.notifications', compact('eventTypes'));
    }

    public function list(Request $request)
    {
        $request->validate([
            'type_id' => 'nullable|integer|min:1|max:999',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
        ]);

        $homeId = (int) explode(',', Auth::user()->home_id)[0];

        try {
            $data = $this->notificationService->list(
                $homeId,
                $request->input('type_id'),
                $request->input('start_date'),
                $request->input('end_date'),
                $request->input('page', 1)
            );
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    public function markRead(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $homeId = (int) explode(',', Auth::user()->home_id)[0];

        try {
            $result = $this->notificationService->markRead($request->input('id'), $homeId);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    public function markAllRead(Request $request)
    {
        $homeId = (int) explode(',', Auth::user()->home_id)[0];

        try {
            $count = $this->notificationService->markAllRead($homeId);
            return response()->json(['success' => true, 'message' => $count . ' notifications marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    public function unreadCount()
    {
        $homeId = (int) explode(',', Auth::user()->home_id)[0];

        try {
            $count = $this->notificationService->unreadCount($homeId);
            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }
}
