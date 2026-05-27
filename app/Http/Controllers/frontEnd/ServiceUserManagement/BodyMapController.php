<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use App\Services\BodyMapService;
use App\ServiceUserRisk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BodyMapController extends ServiceUserManagementController
{
    protected BodyMapService $service;

    public function __construct()
    {
        $this->service = new BodyMapService();
    }

    private function getHomeId(): int
    {
        $homeIds = Auth::user()->home_id;
        $parts = explode(',', $homeIds);
        return (int) $parts[0];
    }

    private function isAdmin(): bool
    {
        return Auth::user()->user_type === 'A';
    }

    /**
     * Show the body map for a service user risk assessment.
     */
    public function index($su_risk_id = null)
    {
        $homeId = $this->getHomeId();

        // Verify risk belongs to this home
        $risk = ServiceUserRisk::where('id', $su_risk_id)
            ->where('home_id', $homeId)
            ->first();

        if (!$risk) {
            return redirect()->back()->with('error', 'Risk assessment not found.');
        }

        $service_user_id = $risk->service_user_id;

        // Show ALL injuries for this service user (not filtered by staff_id)
        $sel_injury_parts = $this->service->listForRisk($homeId, $su_risk_id);

        $isAdmin = $this->isAdmin();

        return view('frontEnd.serviceUserManagement.elements.risk_change.body_map',
            compact('su_risk_id', 'sel_injury_parts', 'service_user_id', 'isAdmin'));
    }

    /**
     * Add an injury point to the body map (AJAX).
     */
    public function addInjury(Request $request)
    {
        $data = $request->validate([
            'service_user_id' => 'required|integer|exists:service_user,id',
            'su_risk_id'      => 'required|integer|exists:su_risk,id',
            'sel_body_map_id' => 'required|string|max:20',
            'injury_type'     => 'nullable|string|in:bruise,wound,rash,burn,swelling,pressure_sore,other',
            'injury_description' => 'nullable|string|max:1000',
            'injury_date'     => 'nullable|date',
            'injury_size'     => 'nullable|string|max:100',
            'injury_colour'   => 'nullable|string|max:50',
        ]);

        $homeId = $this->getHomeId();

        // Verify risk belongs to this home
        $risk = ServiceUserRisk::where('id', $data['su_risk_id'])
            ->where('home_id', $homeId)
            ->first();

        if (!$risk) {
            return response()->json(['success' => false, 'message' => 'Not authorised.'], 403);
        }

        $result = $this->service->addInjury($homeId, $data);

        return response()->json([
            'success'   => true,
            'id'        => $result['injury']->id,
            'duplicate' => $result['duplicate'],
            'message'   => $result['duplicate'] ? 'Injury already recorded for this body part.' : 'Injury point added.',
        ]);
    }

    /**
     * Remove (soft-delete) an injury point from the body map (AJAX).
     */
    public function removeInjury(Request $request)
    {
        $data = $request->validate([
            'injury_id' => 'required|integer',
        ]);

        $homeId = $this->getHomeId();

        // Role check: only admins can remove injuries
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only administrators can remove injuries.'], 403);
        }

        // IDOR check: verify injury belongs to this home before deletion
        $injury = \App\Models\BodyMap::forHome($homeId)->active()->find($data['injury_id']);
        if (!$injury) {
            return response()->json(['success' => false, 'message' => 'Injury not found.'], 404);
        }

        $removed = $this->service->removeInjury($homeId, $data['injury_id']);

        if (!$removed) {
            return response()->json(['success' => false, 'message' => 'Injury not found.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Injury removed.']);
    }

    /**
     * Get injury detail (AJAX).
     */
    public function getInjury(int $id)
    {
        $homeId = $this->getHomeId();
        $injury = $this->service->getInjury($homeId, $id);

        if (!$injury) {
            return response()->json(['success' => false, 'message' => 'Injury not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $injury]);
    }

    /**
     * Update injury details (AJAX).
     */
    public function updateInjury(Request $request)
    {
        $data = $request->validate([
            'id'                 => 'required|integer',
            'injury_type'        => 'nullable|string|in:bruise,wound,rash,burn,swelling,pressure_sore,other',
            'injury_description' => 'nullable|string|max:1000',
            'injury_date'        => 'nullable|date',
            'injury_size'        => 'nullable|string|max:100',
            'injury_colour'      => 'nullable|string|max:50',
        ]);

        $homeId = $this->getHomeId();

        // IDOR check: verify injury belongs to this home before update
        $injury = \App\Models\BodyMap::forHome($homeId)->active()->find($data['id']);
        if (!$injury) {
            return response()->json(['success' => false, 'message' => 'Injury not found.'], 404);
        }

        $updated = $this->service->updateInjury($homeId, $data['id'], $data);

        if (!$updated) {
            return response()->json(['success' => false, 'message' => 'Injury not found.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Injury updated.']);
    }

    /**
     * Get body map history for a service user (AJAX).
     */
    public function history(int $serviceUserId)
    {
        $homeId = $this->getHomeId();
        $history = $this->service->getHistory($homeId, $serviceUserId);

        return response()->json(['success' => true, 'data' => $history]);
    }

    /**
     * List all active injuries for a risk assessment (AJAX/JSON).
     * Used by the body map popup's shown.bs.modal handler to re-paint
     * every path with its persisted injury_type and injury_colour.
     */
    public function listForRisk(int $suRiskId)
    {
        $homeId = $this->getHomeId();

        $risk = ServiceUserRisk::where('id', $suRiskId)
            ->where('home_id', $homeId)
            ->first();

        if (!$risk) {
            return response()->json(['success' => false, 'message' => 'Risk not found.'], 404);
        }

        $injuries = $this->service->listForRisk($homeId, $suRiskId);

        return response()->json(['success' => true, 'data' => $injuries]);
    }

    /**
     * List all active injuries for a service user across every risk (AJAX/JSON).
     * Used by the profile page's read-only aggregated body map view — shows
     * every current injury regardless of which risk assessment recorded it.
     */
    public function listForServiceUser(int $serviceUserId)
    {
        $homeId = $this->getHomeId();

        $su = \App\ServiceUser::where('id', $serviceUserId)
            ->where('home_id', $homeId)
            ->first();

        if (!$su) {
            return response()->json(['success' => false, 'message' => 'Service user not found.'], 404);
        }

        $injuries = $this->service->listForServiceUser($homeId, $serviceUserId);

        return response()->json(['success' => true, 'data' => $injuries]);
    }
}
