<?php

namespace App\Http\Controllers\Frontend4;

use App\Models\Frontend4User;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\AuthenticationSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContextController extends F4Controller
{
    public function switchService(
        Request $request,
        AccessContext $context,
        AuthenticationSecurityService $security
    ) {
        $data = $request->validate(['service_id' => ['required', 'integer']]);
        $user = Auth::guard('frontend4')->user();
        abort_unless($user instanceof Frontend4User, 403);

        $organisationId = $context->organisationId();
        $serviceId = (int) $data['service_id'];
        if (! $context->validContext($user, $organisationId, $serviceId)) {
            $security->record($request, 'access_scope_denied', false, $user, null, [
                'organisation_id' => $organisationId,
                'service_id' => $context->serviceId(),
                'requested_service_id' => $serviceId,
                'route' => 'frontend4.context.service',
            ]);
            abort(403, 'You do not have access to that service.');
        }

        $oldServiceId = $context->serviceId();
        $context->putSession($user, $organisationId, $serviceId);
        $security->record($request, 'service_switched', true, $user, null, [
            'organisation_id' => $organisationId,
            'from_service_id' => $oldServiceId,
            'to_service_id' => $serviceId,
        ]);

        return redirect()->route('frontend4.today');
    }

    public function switchLocation(
        Request $request,
        AccessContext $context,
        AuthenticationSecurityService $security
    ) {
        $data = $request->validate(['location_id' => ['nullable', 'integer']]);
        $user = Auth::guard('frontend4')->user();
        abort_unless($user instanceof Frontend4User, 403);

        $organisationId = $context->organisationId();
        $serviceId = $context->serviceId();
        $locationId = isset($data['location_id']) ? (int) $data['location_id'] : null;
        if (! $context->validContext($user, $organisationId, $serviceId, $locationId)) {
            $security->record($request, 'access_scope_denied', false, $user, null, [
                'organisation_id' => $organisationId,
                'service_id' => $serviceId,
                'requested_location_id' => $locationId,
                'route' => 'frontend4.context.location',
            ]);
            abort(403, 'You do not have access to that location.');
        }

        $oldLocationId = $context->locationId();
        $context->putSession($user, $organisationId, $serviceId, $locationId);
        $security->record($request, 'location_switched', true, $user, null, [
            'organisation_id' => $organisationId,
            'service_id' => $serviceId,
            'from_location_id' => $oldLocationId,
            'to_location_id' => $locationId,
        ]);

        return redirect()->route('frontend4.today');
    }
}
