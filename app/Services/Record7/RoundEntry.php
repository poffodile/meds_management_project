<?php

namespace App\Services\Record7;

use App\Models\Record7\Client;
use App\Models\Record7\Round;
use App\Models\Record7\RoundParticipant;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Starting, joining and resuming a medicines round.
 *
 * CONCURRENCY IS SETTLED BY THE DATABASE, NOT BY TIMING.
 * Two people press Start on the same morning round within the same second. A
 * check-then-insert loses that race: both read "no round yet", both insert, and
 * the house ends up with two rounds and two half-finished sets of records.
 *
 * So the insert is attempted and the UNIQUE constraint on
 * (organisation, service, date, slot) is allowed to refuse it. Whoever loses
 * the race catches the violation and reads the round the winner just made. One
 * round exists because the database will not permit a second, which is a
 * guarantee rather than a probability.
 *
 * JOINING IS NOT BECOMING THE OPENER.
 * started_by_user_id never changes. Everybody who works on the round gets their
 * own participation row, and the second person is visible as themselves.
 *
 * AND RESUMING IS NOT JOINING AGAIN.
 * Somebody coming back to a round they were already on resumes it. The
 * participation row is not duplicated and the audit says "resumed", because
 * "joined" four times over a morning tells a manager nothing.
 */
class RoundEntry
{
    public function __construct(
        private readonly RoundAuthority $authority,
        private readonly AuditRecorder $audit
    ) {
    }

    /**
     * @return array{round:Round, action:string}  action is start|join|resume
     */
    public function enter(User $user, int $serviceId, string $slot, Request $request): array
    {
        $service = Service::findOrFail($serviceId);
        $date = Carbon::today()->toDateString();

        [$round, $created] = $this->findOrCreate($user, $service, $date, $slot);

        // Somebody already on this round is resuming, not joining again.
        $existing = RoundParticipant::where('round_id', $round->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->forceFill(['last_acted_at' => now()])->save();

            $this->record($user, $round, 'round_resumed', $request);

            return ['round' => $round, 'action' => 'resume'];
        }

        RoundParticipant::create([
            'round_id' => $round->id,
            'user_id' => $user->id,
            'organisation_id' => $round->organisation_id,
            'service_id' => $round->service_id,
            'opened_it' => $created,
            'joined_at' => now(),
            'last_acted_at' => now(),
            // Snapshots of what was true at this moment.
            'role_at_join' => $user->primaryRole()?->name,
            'access_type_at_join' => app(AccessPolicy::class)
                ->usableAccess($user, $serviceId)?->access_type,
        ]);

        $this->record($user, $round, $created ? 'round_created' : 'round_joined', $request);

        return ['round' => $round, 'action' => $created ? 'start' : 'join'];
    }

    /**
     * One round, whoever gets there first.
     *
     * The try/catch is the whole mechanism. Reading first and inserting second
     * would be a race; inserting and letting the constraint arbitrate is not.
     *
     * @return array{0:Round, 1:bool}
     */
    private function findOrCreate(User $user, Service $service, string $date, string $slot): array
    {
        $existing = $this->find($service, $date, $slot);

        if ($existing) {
            return [$existing, false];
        }

        try {
            $round = Round::create([
                'organisation_id' => $service->organisation_id,
                'service_id' => $service->id,
                'round_date' => $date,
                'slot' => $slot,
                'started_by_user_id' => $user->id,
                'started_at' => now(),
            ]);

            return [$round, true];
        } catch (UniqueConstraintViolationException) {
            // Lost the race. Somebody else created it between the read above
            // and this insert, which is exactly what the constraint is for.
            // Read theirs and join it.
            return [$this->find($service, $date, $slot), false];
        }
    }

    private function find(Service $service, string $date, string $slot): ?Round
    {
        return Round::where('organisation_id', $service->organisation_id)
            ->where('service_id', $service->id)
            ->whereDate('round_date', $date)
            ->where('slot', $slot)
            ->first();
    }

    /**
     * The round this person should be taken to, if any.
     *
     * Scoped to the house the session is in, so an open round in the house they
     * just left is invisible here. It has not been closed or lost — it simply
     * is not this house's business.
     */
    public function openRoundFor(int $serviceId, ?Carbon $now = null): ?Round
    {
        $now ??= now();

        return Round::where('service_id', $serviceId)
            ->whereDate('round_date', $now->toDateString())
            // Section 2.6: state comes from the lifecycle chain, not from a
            // projection column. A round is open unless its NEWEST event is a
            // closure — so a reopened round is open again, which `closed_at`
            // alone could never say now that it is no longer cleared.
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('record7_round_lifecycle_events as latest')
                    ->whereColumn('latest.round_id', 'record7_rounds.id')
                    ->where('latest.event', 'closed')
                    ->whereRaw('latest.sequence_no = (
                        SELECT MAX(inner_e.sequence_no) FROM record7_round_lifecycle_events inner_e
                        WHERE inner_e.round_id = record7_rounds.id
                    )');
            })
            ->orderByDesc('started_at')
            ->first();
    }

    /**
     * Rounds this person has open ELSEWHERE.
     *
     * Used to notice, and audit, that somebody has walked into another house
     * leaving a round open behind them.
     */
    public function openRoundsInOtherHouses(User $user, int $serviceId, ?Carbon $now = null)
    {
        $now ??= now();

        return Round::whereIn('id', RoundParticipant::where('user_id', $user->id)->select('round_id'))
            ->where('service_id', '!=', $serviceId)
            ->whereDate('round_date', $now->toDateString())
            // Section 2.6: state comes from the lifecycle chain, not from a
            // projection column. A round is open unless its NEWEST event is a
            // closure — so a reopened round is open again, which `closed_at`
            // alone could never say now that it is no longer cleared.
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('record7_round_lifecycle_events as latest')
                    ->whereColumn('latest.round_id', 'record7_rounds.id')
                    ->where('latest.event', 'closed')
                    ->whereRaw('latest.sequence_no = (
                        SELECT MAX(inner_e.sequence_no) FROM record7_round_lifecycle_events inner_e
                        WHERE inner_e.round_id = record7_rounds.id
                    )');
            })
            ->get();
    }

    /**
     * Which slot is actually open in this house.
     *
     * The earliest slot today that still has an unrecorded dose, which is more
     * honest than reading the clock: a morning round nobody finished is still
     * the morning round at two in the afternoon.
     */
    public function currentSlot(int $serviceId, ?Carbon $now = null): ?string
    {
        $now ??= now();

        return ScheduledDose::with('administration')
            ->where('service_id', $serviceId)
            ->whereBetween('due_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->orderBy('due_at')
            ->get()
            ->first(fn ($dose) => $dose->administration === null)
            ?->slot;
    }

    /** Who is on this round, and who opened it. */
    public function participants(Round $round): array
    {
        return RoundParticipant::with('user')
            ->where('round_id', $round->id)
            ->orderBy('joined_at')
            ->get()
            ->map(fn ($participant) => [
                'name' => $participant->user?->displayName(),
                'fullName' => $participant->user?->full_name,
                'openedIt' => $participant->opened_it,
                'joinedAt' => $participant->joined_at->format('H:i'),
                'lastActedAt' => $participant->last_acted_at?->format('H:i'),
                'roleAtJoin' => $participant->role_at_join,
            ])->all();
    }

    private function record(User $user, Round $round, string $event, Request $request): void
    {
        $this->audit->record(
            eventType: $event,
            result: AuditRecorder::SUCCESS,
            user: $user,
            organisationId: $round->organisation_id,
            serviceId: $round->service_id,
            reason: $round->slot.' round',
            riskLevel: 'low',
            metadata: [
                'round_id' => $round->id,
                'round_date' => $round->round_date->toDateString(),
                'slot' => $round->slot,
            ],
            request: $request
        );
    }
}
