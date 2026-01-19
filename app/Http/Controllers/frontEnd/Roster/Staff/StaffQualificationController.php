<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class StaffQualificationController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->has('qualifications')) {
            return response()->json(
                ['message' => 'No qualification selected'],
                422
            );
        }

        $user = User::saveQualification($request->qualifications, $request->user_id);

        return response()->json([
            'message' => 'Qualifications saved successfully'
        ]);
    }
}
