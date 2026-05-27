<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Services\Staff\SafeguardingService;
use App\Models\Staff\SafeguardingType;

class SafeguardingController extends Controller
{
    protected $safeguardingService;

    public function __construct(SafeguardingService $safeguardingService)
    {
        $this->safeguardingService = $safeguardingService;
    }

    private function getHomeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function index()
    {
        $homeId = $this->getHomeId();
        $safeguardingTypes = SafeguardingType::where('home_id', $homeId)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->pluck('type')
            ->toArray();

        return view('frontEnd.roster.safeguarding', compact('safeguardingTypes'));
    }

    public function list(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:reported,under_investigation,safeguarding_plan,closed',
            'risk_level' => 'nullable|in:low,medium,high,critical',
            'search' => 'nullable|string|max:200',
            'page' => 'nullable|integer|min:1',
        ]);

        $homeId = $this->getHomeId();
        $data = $this->safeguardingService->list(
            $homeId,
            $request->input('status'),
            $request->input('risk_level'),
            $request->input('search')
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'client_id' => 'nullable|integer',
            'date_of_concern' => 'required|date',
            'location_of_incident' => 'nullable|string|max:500',
            'details_of_concern' => 'required|string|max:5000',
            'immediate_action_taken' => 'nullable|string|max:5000',
            'safeguarding_type' => 'required|array|min:1',
            'safeguarding_type.*' => 'string|max:100',
            'risk_level' => 'required|in:low,medium,high,critical',
            'ongoing_risk' => 'required|boolean',
            'alleged_perpetrator' => 'nullable|array',
            'witnesses' => 'nullable|array',
            'capacity_to_make_decisions' => 'nullable|boolean',
            'client_wishes' => 'nullable|string|max:5000',
            'police_notified' => 'nullable|boolean',
            'police_reference' => 'nullable|string|max:100',
            'police_notification_date' => 'nullable|date',
            'local_authority_notified' => 'nullable|boolean',
            'local_authority_reference' => 'nullable|string|max:100',
            'local_authority_notification_date' => 'nullable|date',
            'cqc_notified' => 'nullable|boolean',
            'cqc_notification_date' => 'nullable|date',
            'family_notified' => 'nullable|boolean',
            'family_notification_details' => 'nullable|string|max:2000',
            'advocate_involved' => 'nullable|boolean',
            'advocate_details' => 'nullable|string|max:2000',
        ]);

        $homeId = $this->getHomeId();
        $userId = Auth::user()->id;

        try {
            $referral = $this->safeguardingService->store($request->all(), $homeId, $userId);
            return response()->json(['success' => true, 'message' => 'Safeguarding referral created successfully.', 'data' => $referral]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'client_id' => 'nullable|integer',
            'date_of_concern' => 'nullable|date',
            'location_of_incident' => 'nullable|string|max:500',
            'details_of_concern' => 'nullable|string|max:5000',
            'immediate_action_taken' => 'nullable|string|max:5000',
            'safeguarding_type' => 'nullable|array|min:1',
            'safeguarding_type.*' => 'string|max:100',
            'risk_level' => 'nullable|in:low,medium,high,critical',
            'ongoing_risk' => 'nullable|boolean',
            'alleged_perpetrator' => 'nullable|array',
            'witnesses' => 'nullable|array',
            'capacity_to_make_decisions' => 'nullable|boolean',
            'client_wishes' => 'nullable|string|max:5000',
            'police_notified' => 'nullable|boolean',
            'police_reference' => 'nullable|string|max:100',
            'police_notification_date' => 'nullable|date',
            'local_authority_notified' => 'nullable|boolean',
            'local_authority_reference' => 'nullable|string|max:100',
            'local_authority_notification_date' => 'nullable|date',
            'cqc_notified' => 'nullable|boolean',
            'cqc_notification_date' => 'nullable|date',
            'family_notified' => 'nullable|boolean',
            'family_notification_details' => 'nullable|string|max:2000',
            'advocate_involved' => 'nullable|boolean',
            'advocate_details' => 'nullable|string|max:2000',
            'strategy_meeting' => 'nullable|array',
            'safeguarding_plan' => 'nullable|array',
            'outcome' => 'nullable|in:substantiated,partially_substantiated,unsubstantiated,inconclusive',
            'outcome_details' => 'nullable|string|max:5000',
            'lessons_learned' => 'nullable|string|max:5000',
        ]);

        $homeId = $this->getHomeId();

        try {
            $referral = $this->safeguardingService->update($request->input('id'), $request->except('id'), $homeId);
            if (!$referral) {
                return response()->json(['success' => false, 'message' => 'Referral not found.'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Safeguarding referral updated successfully.', 'data' => $referral]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    public function details(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $homeId = $this->getHomeId();
        $referral = $this->safeguardingService->details($request->input('id'), $homeId);

        if (!$referral) {
            return response()->json(['success' => false, 'message' => 'Referral not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $referral]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $userType = Auth::user()->user_type;
        if ($userType !== 'A') {
            return response()->json(['success' => false, 'message' => 'Only administrators can delete referrals.'], 403);
        }

        $homeId = $this->getHomeId();
        $result = $this->safeguardingService->delete($request->input('id'), $homeId);

        if (!$result) {
            return response()->json(['success' => false, 'message' => 'Referral not found.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Safeguarding referral deleted.']);
    }

    public function statusChange(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|in:under_investigation,safeguarding_plan,closed',
        ]);

        $homeId = $this->getHomeId();

        $referral = $this->safeguardingService->statusChange($request->input('id'), $request->input('status'), $homeId);
        if (!$referral) {
            return response()->json(['success' => false, 'message' => 'Invalid status transition or referral not found.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'data' => $referral]);
    }
}
