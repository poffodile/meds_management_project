<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\Organisation;
use App\Models\Record7\Service;
use App\Services\Record7\AccessPolicy;
use App\Services\Record7\AuditRecorder;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Choosing and switching the house someone is working in.
 *
 * Sections 0.4 and the switching half of 0.9.
 *
 * The list is a convenience. The check is choose(), which refuses any house
 * the person does not currently hold usable access to — so a crafted request
 * cannot select a house that was never offered, and an inactive house such as
 * Willow House is refused even if its id is guessed.
 */
class HouseController extends R7Controller
{
    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly SessionManager $sessions,
        private readonly AuditRecorder $audit
    ) {
    }

    public function index(Request $request)
    {
        $this->useR7Layout($request);

        $user = $this->user();
        abort_unless($user !== null, 403);

        $houses = $this->policy->availableServices($user);

        // 0.4 — nowhere to work means the sign-in cannot complete. End the
        // session rather than leaving someone stranded on a half-signed-in page.
        if ($houses === []) {
            $this->audit->record(
                'no_active_house', AuditRecorder::DENIED, $user,
                $this->sessions->organisationId($request), null,
                'The account has no active house.', 'medium', [], $request,
                $this->sessions->current($request)
            );
            $this->sessions->end($request, 'revoked');

            return redirect()->route('record7.login')->with(
                'error',
                'Your account does not have an active house. Please contact your administrator.'
            );
        }

        $current = $this->sessions->serviceId($request);

        // Exactly one, and none chosen yet: open it rather than asking a
        // question with one answer.
        if (count($houses) === 1 && ! $current) {
            return $this->enter($request, $houses[0], true);
        }

        return Inertia::render('Auth/Houses', [
            'organisationName' => Organisation::find($this->sessions->organisationId($request))?->display_name,
            'name' => $user->displayName(),
            'currentHouseId' => $current,
            'houses' => array_map(fn (Service $house) => [
                'id' => $house->id,
                'name' => $house->name,
                'type' => $house->service_type,
                'town' => $house->town,
                'accessType' => $this->policy->usableAccess($user, $house->id)?->access_type,
            ], $houses),
            'error' => session('error'),
            'chooseUrl' => route('record7.houses.choose'),
            'signOutUrl' => route('record7.signout'),
        ]);
    }

    public function choose(Request $request)
    {
        $data = $request->validate(['house_id' => ['required', 'integer']]);

        $user = $this->user();
        abort_unless($user !== null, 403);

        $access = $this->policy->usableAccess($user, (int) $data['house_id']);
        $house = $access ? Service::find($data['house_id']) : null;

        if (! $access || ! $house || ! $house->isActive()) {
            $this->audit->record(
                'house_selection', AuditRecorder::DENIED, $user,
                $this->sessions->organisationId($request), (int) $data['house_id'],
                'Attempted to enter a house without usable access.', 'high',
                ['requested_house_id' => (int) $data['house_id']], $request,
                $this->sessions->current($request)
            );

            // Back to the list with an inline message, rather than a bare 403
            // page. Being dropped onto an unstyled error screen mid sign-in
            // looks like the product broke, when in fact it worked.
            return back()->with(
                'error',
                'You do not have access to that house. Choose one of the houses listed below, '
                .'or ask your manager to add you.'
            );
        }

        return $this->enter($request, $house, false);
    }

    private function enter(Request $request, Service $house, bool $automatic)
    {
        $previous = $this->sessions->serviceId($request);

        $this->sessions->selectService($request, $house);

        $this->audit->record(
            $previous && $previous !== $house->id ? 'house_switched' : 'house_selected',
            AuditRecorder::SUCCESS,
            $this->user(),
            $this->sessions->organisationId($request),
            $house->id,
            $automatic ? 'Only house available, opened automatically.' : null,
            'none',
            ['automatic' => $automatic, 'previous_house_id' => $previous],
            $request,
            $this->sessions->current($request)
        );

        $intended = $request->session()->pull(SessionManager::INTENDED);

        return is_string($intended) && str_starts_with($intended, url('/record7'))
            ? redirect()->to($intended)
            : redirect()->route('record7.today');
    }
}
