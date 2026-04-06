<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OnboardingDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OnboardingDetailController extends Controller
{
    public function onboarding_detail_save(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        DB::beginTransaction();
        try {
            $home_id = explode(',', Auth::user()->home_id)[0];
            $rquestData = $request->all();
            $rquestData['home_id'] = $home_id;
            $dataSave = OnboardingDetail::saveOnboardingDetail($rquestData);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Saved Successfully done', 'data' => $dataSave]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving onboarding detail:', [
                'error' => $e->getMessage(),
                'data'  => json_decode('{}')
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'data' => json_decode('{}')]);
        }
    }
    public function onboarding_detail_list(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $list = OnboardingDetail::onboardingDetailList($request->all());
        return response()->json(['success' => true, 'message' => 'onboarding list', 'data' => $list]);
    }
    public function onboarding_detail_delete(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:onboarding_details,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $list = OnboardingDetail::onboardingDetailDelete($request->id);
        return response()->json(['success' => true, 'message' => 'onboarding deleted successfully done', 'data' => $list]);
    }
}
