<?php

namespace App\Http\Middleware\Record7;

use App\Services\Record7\AccessPolicy;
use App\Services\Record7\AuditRecorder;
use App\Services\Record7\SessionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses a request before its controller runs unless the person holds the
 * permission, in the house they are working in.
 *
 * Every refusal is audited with the reason, because "who was turned away from
 * what" is exactly what an access audit is for.
 */
class Authorize
{
    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly SessionManager $sessions,
        private readonly AuditRecorder $audit
    ) {
    }

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $this->sessions->user();
        $serviceId = $this->sessions->serviceId($request);

        if (! $user) {
            return redirect()->route('record7.login');
        }

        $decision = $this->policy->decide($user, $permission, $serviceId);

        if ($decision->allowed) {
            return $next($request);
        }

        $this->audit->record(
            'permission_denied',
            AuditRecorder::DENIED,
            $user,
            $this->sessions->organisationId($request),
            $serviceId,
            $decision->message,
            $decision->riskLevel(),
            ['permission' => $permission, 'decision' => $decision->code, 'route' => $request->route()?->getName()],
            $request,
            $this->sessions->current($request)
        );

        abort(403, $decision->message ?? 'You do not have permission to do that.');
    }
}
