<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Services\Staff\SosAlertService;

class SosAlertController extends Controller
{
    protected $sosAlertService;

    public function __construct(SosAlertService $sosAlertService)
    {
        $this->sosAlertService = $sosAlertService;
    }

    public function index()
    {
        return view('frontEnd.roster.dashboard');
    }

    public function trigger(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:2000',
        ]);

        $homeId = (int) explode(',', Auth::user()->home_id)[0];
        $staffId = Auth::user()->id;

        try {
            $alert = $this->sosAlertService->trigger($staffId, $homeId, $request->input('message'));
            return response()->json(['success' => true, 'message' => 'SOS Alert sent! Managers have been notified.', 'data' => $alert]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SOS Alert Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 200); // Return 200 so jQuery success handler catches it
        }
    }

    public function list(Request $request)
    {
        $homeId = (int) explode(',', Auth::user()->home_id)[0];
        $alerts = $this->sosAlertService->list($homeId);
        return response()->json(['success' => true, 'data' => $alerts]);
    }

    public function acknowledge(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $homeId = (int) explode(',', Auth::user()->home_id)[0];
        $userType = Auth::user()->user_type;

        if (!in_array($userType, ['M', 'A'])) {
            return response()->json(['success' => false, 'message' => 'Only managers and admins can acknowledge alerts.'], 403);
        }

        $alert = $this->sosAlertService->acknowledge($request->input('id'), $homeId, Auth::user()->id);
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Alert not found or already acknowledged.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Alert acknowledged.', 'data' => $alert]);
    }

    public function resolve(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'notes' => 'nullable|string|max:2000',
        ]);

        $homeId = (int) explode(',', Auth::user()->home_id)[0];
        $userType = Auth::user()->user_type;

        if (!in_array($userType, ['M', 'A'])) {
            return response()->json(['success' => false, 'message' => 'Only managers and admins can resolve alerts.'], 403);
        }

        $alert = $this->sosAlertService->resolve($request->input('id'), $homeId, Auth::user()->id, $request->input('notes'));
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Alert not found or already resolved.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Alert resolved.', 'data' => $alert]);
    }
}
