<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\Staff\SosAlertService;
use App\User;

class SosAlertController extends Controller
{
    protected $sosAlertService;

    public function __construct(SosAlertService $sosAlertService)
    {
        $this->sosAlertService = $sosAlertService;
    }

    public function trigger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|integer|exists:user,id',
            'message'  => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $staffId = (int) $request->input('staff_id');
        $user = User::find($staffId);
        $homeId = (int) explode(',', $user->home_id)[0];

        try {
            $alert = $this->sosAlertService->trigger($staffId, $homeId, $request->input('message'));
            return response()->json([
                'success' => true,
                'message' => 'SOS Alert sent! Managers have been notified.',
                'data'    => $alert
            ]);
        } catch (\Exception $e) {
            Log::error('API SOS Alert Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'home_id' => 'required|integer|exists:home,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $homeId = (int) $request->input('home_id');
        try {
            $alerts = $this->sosAlertService->list($homeId);
            $alertsArray = $alerts->toArray();

            foreach ($alertsArray as $key => $alert) {
                if ($alert['status'] == 1) {
                    $alertsArray[$key]['status'] = 'Active';
                } elseif ($alert['status'] == 2) {
                    $alertsArray[$key]['status'] = 'Acknowledged';
                } elseif ($alert['status'] == 3) {
                    $alertsArray[$key]['status'] = 'Resolved';
                } else {
                    $alertsArray[$key]['status'] = 'Unknown';
                }
            }

            return response()->json([
                'success' => true,
                'data'    => $alertsArray
            ]);
        } catch (\Exception $e) {
            Log::error('API SOS Alert List Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    public function acknowledge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'       => 'required|integer',
            'staff_id' => 'required|integer|exists:user,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $staffId = (int) $request->input('staff_id');
        $user = User::find($staffId);
        if (!in_array($user->user_type, ['M', 'CM', 'A'])) {
            return response()->json(['success' => false, 'message' => 'Only managers can acknowledge alerts.'], 403);
        }

        $homeId = (int) explode(',', $user->home_id)[0];

        try {
            $alert = $this->sosAlertService->acknowledge($request->input('id'), $homeId, $staffId);
            if (!$alert) {
                return response()->json(['success' => false, 'message' => 'Alert not found or already acknowledged.'], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Alert acknowledged.',
                'data'    => $alert
            ]);
        } catch (\Exception $e) {
            Log::error('API SOS Alert Acknowledge Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    public function resolve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'       => 'required|integer',
            'staff_id' => 'required|integer|exists:user,id',
            'notes'    => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $staffId = (int) $request->input('staff_id');
        $user = User::find($staffId);
        if (!in_array($user->user_type, ['M', 'CM', 'A'])) {
            return response()->json(['success' => false, 'message' => 'Only managers can resolve alerts.'], 403);
        }

        $homeId = (int) explode(',', $user->home_id)[0];

        try {
            $alert = $this->sosAlertService->resolve($request->input('id'), $homeId, $staffId, $request->input('notes'));
            if (!$alert) {
                return response()->json(['success' => false, 'message' => 'Alert not found or already resolved.'], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Alert resolved.',
                'data'    => $alert
            ]);
        } catch (\Exception $e) {
            Log::error('API SOS Alert Resolve Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
}
