<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use App\Services\Portal\ClientPortalService;
use App\Models\ClientPortalAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PortalAccessController extends Controller
{
    protected $portalService;

    public function __construct(ClientPortalService $portalService)
    {
        $this->portalService = $portalService;
    }

    private function rejectPortalUsers()
    {
        if (session('portal_access_id')) {
            abort(403, 'Portal users cannot access admin management.');
        }
        if (!in_array(Auth::user()->user_type, ['A', 'M', 'CM'])) {
            abort(403, 'Only administrators and managers can manage portal access.');
        }
    }

    public function list(Request $request)
    {
        $this->rejectPortalUsers();
        $request->validate([
            'client_id' => 'nullable|integer',
        ]);

        $homeId = explode(',', Auth::user()->home_id)[0];
        $clientId = $request->input('client_id');

        $portalUsers = $this->portalService->listPortalUsers((int)$homeId, $clientId ? (int)$clientId : null);

        return response()->json([
            'status' => true,
            'data' => $portalUsers,
        ]);
    }

    public function save(Request $request)
    {
        $this->rejectPortalUsers();
        $request->validate([
            'client_id' => 'required|integer',
            'user_email' => 'required|email|max:255',
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|in:self,parent,child,spouse,sibling,guardian,advocate,social_worker,other',
            'access_level' => 'nullable|in:view_only,view_and_message,full_access',
            'client_type' => 'nullable|in:residential,domiciliary,supported_living,day_centre',
            'phone' => 'nullable|string|max:50',
            'is_primary_contact' => 'nullable|boolean',
            'can_view_schedule' => 'nullable|boolean',
            'can_view_care_notes' => 'nullable|boolean',
            'can_send_messages' => 'nullable|boolean',
            'can_request_bookings' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $homeId = (int)explode(',', Auth::user()->home_id)[0];

        $client = \App\ServiceUser::where('id', $request->client_id)
            ->where('home_id', $homeId)
            ->first();

        if (!$client) {
            return response()->json([
                'status' => false,
                'message' => 'Client not found in your home.',
            ], 404);
        }

        $user = \App\User::where('email', $request->user_email)
            ->where('is_deleted', 0)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No user account found with this email. The family member must have a user account first.',
            ], 422);
        }

        $existing = ClientPortalAccess::where('user_email', $request->user_email)
            ->where('client_id', $request->client_id)
            ->where('is_deleted', 0)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Portal access already exists for this email and client.',
            ], 422);
        }

        $data = $request->only([
            'client_id', 'user_email', 'full_name', 'relationship',
            'access_level', 'client_type', 'phone', 'is_primary_contact',
            'can_view_schedule', 'can_view_care_notes', 'can_send_messages',
            'can_request_bookings', 'notes',
        ]);

        $access = $this->portalService->createPortalAccess($data, $homeId, Auth::user()->id);

        return response()->json([
            'status' => true,
            'message' => 'Portal access granted successfully.',
            'data' => $access,
        ]);
    }

    public function revoke(Request $request)
    {
        $this->rejectPortalUsers();
        $request->validate([
            'id' => 'required|integer',
        ]);

        $homeId = (int)explode(',', Auth::user()->home_id)[0];
        $result = $this->portalService->revokePortalAccess((int)$request->id, $homeId);

        if (!$result) {
            return response()->json([
                'status' => false,
                'message' => 'Portal access record not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Portal access revoked successfully.',
        ]);
    }

    public function delete(Request $request)
    {
        $this->rejectPortalUsers();
        if (Auth::user()->user_type !== 'A') {
            return response()->json([
                'status' => false,
                'message' => 'Only administrators can delete portal access.',
            ], 403);
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $homeId = (int)explode(',', Auth::user()->home_id)[0];
        $result = $this->portalService->deletePortalAccess((int)$request->id, $homeId);

        if (!$result) {
            return response()->json([
                'status' => false,
                'message' => 'Portal access record not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Portal access deleted successfully.',
        ]);
    }
}
