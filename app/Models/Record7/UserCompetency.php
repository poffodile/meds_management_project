<?php

namespace App\Models\Record7;

class UserCompetency extends Record7Model
{
    protected $table = 'record7_user_competencies';

    protected $casts = ['assessed_at' => 'datetime', 'review_due_at' => 'datetime'];

    public function competencyType()
    {
        return $this->belongsTo(CompetencyType::class, 'competency_type_id');
    }

    /**
     * Does this competency permit the action it gates?
     *
     * 'review_due' still permits — the assessment is overdue, not withdrawn,
     * and blocking a due review would stop safe practice to enforce paperwork.
     * Everything else does not.
     */
    public function permitsPractice(): bool
    {
        return in_array($this->status, ['current', 'review_due', 'not_required'], true);
    }
}
