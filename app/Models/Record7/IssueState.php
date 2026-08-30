<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What people have DONE about an issue — never whether the issue is fixed.
 *
 * SIX SEPARATE THINGS, AND THE LAST ONE IS NOT HERE.
 *
 *   acknowledged   somebody has seen it
 *   owned          somebody is dealing with it
 *   escalated      somebody senior has been told
 *   actionRecorded something was done, and what
 *   closed         administratively finished, with a reason and evidence
 *   ---------------------------------------------------------------------
 *   the underlying condition being resolved is DERIVED from the clinical
 *   record every time it is asked for, and is deliberately absent from this
 *   table.
 *
 * That absence is the point. The earlier version had a single resolved_at and
 * the dashboard hid anything that had one, so pressing a button cleared a
 * time-critical omission off a manager's screen while the dose was still
 * unrecorded. Workflow state can now say whatever it likes; it cannot make a
 * live clinical condition disappear.
 *
 * IDENTITY IS OWNED. issue_type and source_id are explicit columns and the
 * unique key spans organisation and house, so dose 412 in one company can never
 * meet dose 412 in another.
 */
class IssueState extends Record7Model
{
    protected $table = 'record7_issue_states';

    protected $casts = [
        'assigned_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'escalated_at' => 'datetime',
        'action_recorded_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Issues where "I have dealt with it" is not good enough on its own.
     *
     * Closing one of these needs either a reference to the evidence or a link
     * to the corrective clinical record, because these are the ones that get
     * asked about afterwards by somebody who was not there.
     */
    public const SAFETY_CRITICAL = [
        'omitted_dose',
        'time_critical_omission',
        'controlled_drug_discrepancy',
        'stock_discrepancy',
        'stock_out',
        'incomplete_record',
        'prn_follow_up',

        // Somebody was worried enough about a response to write it down.
        // Closing that needs evidence, not a tick.
        'prn_concerning_response',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }

    public function isEscalated(): bool
    {
        return $this->escalated_at !== null;
    }

    public function hasActionRecorded(): bool
    {
        return $this->action_recorded_at !== null;
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    /** Whether this kind of issue needs evidence before it may be closed. */
    public static function needsEvidence(?string $issueType): bool
    {
        return in_array((string) $issueType, self::SAFETY_CRITICAL, true);
    }

    /**
     * The one sentence a manager needs when the paperwork and the reality
     * disagree.
     */
    public function statusWording(bool $conditionActive): string
    {
        if (! $conditionActive) {
            return 'Resolved';
        }

        if ($this->isClosed()) {
            return 'Action recorded — underlying issue remains unresolved';
        }

        if ($this->hasActionRecorded()) {
            return 'Action recorded — underlying issue remains unresolved';
        }

        if ($this->isEscalated()) {
            return 'Escalated';
        }

        if ($this->owner_user_id) {
            return 'Owned';
        }

        return $this->isAcknowledged() ? 'Acknowledged' : 'Open';
    }
}
