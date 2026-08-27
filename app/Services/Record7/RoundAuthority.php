<?php

namespace App\Services\Record7;

use App\Models\Record7\CompetencyType;
use App\Models\Record7\Organisation;
use App\Models\Record7\Round;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Http\Request;

/**
 * May this person, right now, work on a medicines round in this house?
 *
 * ASKED ON EVERY REQUEST, NEVER ONCE AT LOGIN.
 * A shift is hours long. In that time a competency can expire, access to a
 * house can be suspended, an account can be locked, a permission can be
 * withdrawn and a round can be closed. Checking any of that only at sign-in
 * means the product is enforcing a snapshot of a situation that has since
 * changed — which is exactly the sort of gap that is invisible until the day it
 * matters.
 *
 * SEVEN THINGS, IN THIS ORDER, EVERY TIME:
 *
 *   1. the account is active
 *   2. the organisation is the one the session belongs to
 *   3. the house is one this person currently holds, usably
 *   4. the access to that house is not suspended or expired
 *   5. medication permission (deny beats allow beats role)
 *   6. medication competency is current
 *   7. the round, if there is one, is still open
 *
 * The first six are asked of AccessPolicy rather than reimplemented, so this
 * can never permit something the rest of the product would refuse.
 *
 * LOSING AUTHORITY DOES NOT DESTROY ANYTHING.
 * If a check fails after the round was opened, the round stays exactly as it
 * is, the participation history stays, and the person is given a blocked state
 * that says what happened and who can continue. Nothing is closed, nothing is
 * deleted, and no medicines action is possible.
 */
class RoundAuthority
{
    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly SessionManager $sessions,
        private readonly AuditRecorder $audit
    ) {
    }

    /**
     * @return array{allowed:bool, reason:?string, code:?string, blocked:bool}
     */
    public function check(User $user, int $serviceId, ?Round $round = null): array
    {
        // 1. The account itself.
        if ($refusal = $user->accessRefusalReason()) {
            return $this->no('account_'.$refusal, 'Your account is not currently available.');
        }

        $service = Service::find($serviceId);

        if (! $service) {
            return $this->no('no_house', 'No house is selected.');
        }

        // 2. The organisation. A house from another company is not merely
        //    unauthorised, it is a different tenant entirely.
        if ((int) $service->organisation_id !== (int) $user->organisation_id) {
            return $this->no('wrong_organisation', 'That house belongs to another organisation.');
        }

        // 3 and 4. Usable access to THIS house, including the date window.
        $access = $this->policy->usableAccess($user, $serviceId);

        if (! $access) {
            return $this->no(
                'no_house_access',
                'You do not currently have access to this house.'
            );
        }

        // 5 and 6. Permission and competency, asked of the policy so this can
        //    never disagree with what the server would actually allow.
        $decision = $this->policy->decide($user, 'administer_medication', $serviceId);

        if ($decision->denied()) {
            return $this->no(
                'not_authorised_to_administer',
                $decision->message ?? 'You are not authorised to give medicines in this house.'
            );
        }

        // 7. The round, if this is about one.
        if ($round !== null) {
            if ((int) $round->service_id !== $serviceId
                || (int) $round->organisation_id !== (int) $service->organisation_id) {
                return $this->no('round_elsewhere', 'That round belongs to another house.');
            }

            if ($round->isClosed()) {
                return $this->no('round_closed', 'That round has been closed by a manager.');
            }
        }

        return ['allowed' => true, 'reason' => null, 'code' => null, 'blocked' => false];
    }

    /**
     * The same check, framed for somebody already inside an open round.
     *
     * The difference is only in what it means: a refusal here is authority LOST
     * mid-shift rather than never held, so it is audited differently and the
     * wording tells the person that somebody else has to carry on.
     */
    public function checkContinuing(User $user, int $serviceId, Round $round, Request $request): array
    {
        $result = $this->check($user, $serviceId, $round);

        if ($result['allowed']) {
            return $result;
        }

        $this->audit->record(
            eventType: 'round_authority_lost',
            result: AuditRecorder::DENIED,
            user: $user,
            serviceId: $serviceId,
            reason: $result['code'],
            riskLevel: 'high',
            metadata: ['round_id' => $round->id, 'slot' => $round->slot],
            request: $request
        );

        return [
            'allowed' => false,
            'blocked' => true,
            'code' => $result['code'],
            'reason' => $result['reason']
                .' The round is still open and nothing has been lost — another authorised '
                .'worker or a manager must continue it.',
        ];
    }

    /** Record a refusal to enter at all. */
    public function refuse(User $user, ?int $serviceId, array $result, Request $request): void
    {
        $this->audit->record(
            eventType: 'round_entry_refused',
            result: AuditRecorder::DENIED,
            user: $user,
            serviceId: $serviceId,
            reason: $result['code'],
            riskLevel: 'medium',
            request: $request
        );
    }

    /**
     * The house context changed while a round was still open.
     *
     * Not an error and not a refusal — people switch houses legitimately. It is
     * recorded because a round left open in one house while its worker walks
     * into another is a thing a manager may need to see.
     */
    public function noteHouseChange(User $user, Round $round, int $newServiceId, Request $request): void
    {
        $this->audit->record(
            eventType: 'round_house_context_changed',
            result: AuditRecorder::WARNING,
            user: $user,
            serviceId: $round->service_id,
            reason: 'Left an open '.$round->slot.' round',
            riskLevel: 'low',
            metadata: [
                'round_id' => $round->id,
                'left_service_id' => $round->service_id,
                'entered_service_id' => $newServiceId,
            ],
            request: $request
        );
    }

    /** When a competency is the thing that will fail next. */
    public function competencyExpiry(User $user, int $serviceId): ?string
    {
        $gate = CompetencyType::where('gates_permission', 'administer_medication')->first();

        if (! $gate) {
            return null;
        }

        return $user->competencies()
            ->where('competency_type_id', $gate->id)
            ->where(fn ($q) => $q->whereNull('service_id')->orWhere('service_id', $serviceId))
            ->first()?->review_due_at?->format('j M Y');
    }

    private function no(string $code, string $reason): array
    {
        return ['allowed' => false, 'reason' => $reason, 'code' => $code, 'blocked' => false];
    }
}
