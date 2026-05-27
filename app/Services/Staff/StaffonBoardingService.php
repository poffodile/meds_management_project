<?php

namespace App\Services\Staff;

use Illuminate\Http\Request;

class StaffonBoardingService
{
    public function formFetch(Request $request)
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function formSave(Request $request)
    {
        return ['success' => true, 'data' => []];
    }
}
