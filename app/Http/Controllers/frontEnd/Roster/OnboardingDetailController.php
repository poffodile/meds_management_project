<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OnboardingDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\ServiceUser;

class OnboardingDetailController extends Controller
{
    public function onboarding_detail_save(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        DB::beginTransaction();
        try {
            $home_id = explode(',', Auth::user()->home_id)[0];
            $names = $request->input('name');
            $types = $request->input('type');
            $vats = $request->input('vat');
            $ids = $request->input('detail_ids');
            $frequency = $request->input('frequency');
            $frequency_rate = $request->input('frequency_rate');
            $client_id = $request->input('client_id');

            // Save Billing Type data in service_user table
            $serviceUser = ServiceUser::find($client_id);
            if ($serviceUser) {
                $serviceUser->billing_frequency = $frequency;
                $serviceUser->billing_rate = $frequency_rate;
                $serviceUser->save();
            }

            // Save Funding Type data in onboarding_details table
            if (is_array($names)) {
                // Delete records that are no longer in the list
                $validIds = array_filter($ids);
                OnboardingDetail::where('client_id', $client_id)->whereNotIn('id', $validIds)->delete();

                foreach ($names as $key => $name) {
                    if (!empty($name)) {
                        $data = [
                            'id' => $ids[$key] ?? null,
                            'home_id' => $home_id,
                            'client_id' => $client_id,
                            'name' => $name,
                            'type' => $types[$key] ?? 1,
                            'vat' => $vats[$key] ?? 0,
                        ];
                        OnboardingDetail::saveOnboardingDetail($data);
                    }
                }
            } else {
                $rquestData = $request->all();
                $rquestData['home_id'] = $home_id;
                OnboardingDetail::saveOnboardingDetail($rquestData);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Saved Successfully done']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving onboarding detail:', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function onboarding_detail_list(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $list = OnboardingDetail::onboardingDetailList($request->all());
        $serviceUser = ServiceUser::find($request->client_id);

        return response()->json([
            'success' => true,
            'message' => 'onboarding list',
            'data' => $list,
            'billing' => [
                'frequency' => $serviceUser->billing_frequency ?? '',
                'rate' => $serviceUser->billing_rate ?? ''
            ]
        ]);
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
