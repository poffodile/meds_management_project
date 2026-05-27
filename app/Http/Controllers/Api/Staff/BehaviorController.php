<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\SuBehavior;
use App\ServiceUser;

class BehaviorController extends Controller
{

    public function addBehavior(Request $request)
    {
        // Get all request data
        $data = $request->all();

        // Validation rules
        $rules = [
            'user_id' => 'required|integer|exists:user,id',
            'service_user_id' => 'required|integer|exists:service_user,id',
            'home_id' => 'required|integer|exists:home,id',
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string|max:500',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get home_id (first value if comma separated)
        $home_ids = $data['home_id'];
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

        // Check if service user belongs to user's home
        $su_home_id = ServiceUser::where('id', $data['service_user_id'])->value('home_id');
        if (empty($su_home_id) || $su_home_id != $home_id) {
            return response()->json(['message' => 'Unauthorized or service user not found'], 403);
        }

        try {
            // --------------------------------------------------------
            // ⭐ 1. Check if rating already exists for TODAY
            // --------------------------------------------------------
            $alreadyExists = SuBehavior::where('service_user_id', $data['service_user_id'])
                ->whereDate('created_at', today())
                ->where('is_deleted', 0)
                ->exists();

            if ($alreadyExists) {
                return response()->json([
                    'status'   => false,
                    'message'  => "Today's rating is already added.",
                ]);
            }

            // --------------------------------------------------------
            // ⭐ 2. Create New Behavior Record
            // --------------------------------------------------------
            SuBehavior::create([
                'user_id'         => $data['user_id'],
                'service_user_id' => $data['service_user_id'],
                'rate'            => $data['rating'],
                'description'     => $data['description'] ?? null,
                'is_deleted'      => 0,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Rating saved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add behavior: ' . $e->getMessage(), [
                'data' => $data,
                'user_id' => $data['user_id'] ?? null,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to save rating',
            ], 500);
        }
    }
}
