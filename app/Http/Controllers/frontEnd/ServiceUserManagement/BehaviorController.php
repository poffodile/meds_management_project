<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\ServiceUser;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\SuBehavior;

class BehaviorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($service_user_id)
    {

        // Verify service user belongs to current user's home
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];


        $data['service_user_id'] = $service_user_id;
        $data['su_behaviors'] = SuBehavior::join('user', 'user.id', '=', 'su_behavior.user_id')
            ->select('su_behavior.*', 'user.name')
            ->where('service_user_id', $service_user_id)
            ->where('su_behavior.is_deleted', 0)
            ->orderBy('su_behavior.id', 'desc')
            ->where('su_behavior.home_id', $home_id)
            ->get();
        return view('frontEnd.serviceUserManagement.elements.behavior', $data);
    }

    /**
     * Save child behavior rating (AJAX)
     */
    public function saveRating(Request $request, $service_user_id)
    {
        $data = $request->all();

        // Validation rules
        $rules = [
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string|max:500',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify service user belongs to current user's home
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

        $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
        if (empty($su_home_id) || $su_home_id != $home_id) {
            return response()->json(['message' => 'Unauthorized or service user not found'], 403);
        }

        try {
            // --------------------------------------------------------
            // ⭐ 1. Check if rating already exists for TODAY (Add only)
            // --------------------------------------------------------
            if (empty($data['edit_behavior_id'])) {
                $alreadyExists = SuBehavior::where('service_user_id', $service_user_id)
                    ->whereDate('created_at', today())
                    ->where('is_deleted', 0)
                    ->where('home_id', $home_id)
                    ->exists();

                if ($alreadyExists) {
                    return response()->json([
                        'status'   => false,
                        'message'  => "Today's rating is already added. Please edit the existing record if you want to make changes.",
                    ]);
                }
            }

            // --------------------------------------------------------
            // ⭐ 2. If edit_behavior_id → Update, else → Create
            // --------------------------------------------------------
            if (!empty($data['edit_behavior_id'])) {
                $behavior = SuBehavior::find($data['edit_behavior_id']);

                if (!$behavior) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Behavior record not found',
                    ], 404);
                }

                // Update existing record
                $behavior->update([
                    'rate'        => $data['rating'],
                    'description' => $data['description'] ?? null,
                    'is_deleted'  => 0,
                ]);

                $message = 'Rating updated successfully';
            } else {
                // Create new record
                SuBehavior::create([
                    'home_id'         => $home_id,
                    'user_id'         => Auth::id(),
                    'service_user_id' => $service_user_id,
                    'rate'            => $data['rating'],
                    'description'     => $data['description'] ?? null,
                    'is_deleted'      => 0,
                ]);

                $message = 'Rating saved successfully';
            }

            return response()->json([
                'status' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save rating: ' . $e->getMessage(), [
                'data' => $data,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to save rating',
            ], 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
