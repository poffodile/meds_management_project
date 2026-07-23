<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ControlledDrugRegister extends Model
{
    protected $table = 'controlled_drug_register';

    protected $fillable = [
        'home_id',
        'client_id',
        'client_name',
        'mar_sheet_id',
        'medication_name',
        'cd_schedule',
        'action_type',
        'entry_date',
        'entry_time',
        'dose_quantity',
        'unit',
        'balance_before',
        'balance_after',
        'witness_name',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'home_id'            => 'integer',
        'client_id'          => 'integer',
        'mar_sheet_id'       => 'integer',
        'created_by_user_id' => 'integer',
        'entry_date'         => 'date',
        'dose_quantity'      => 'decimal:2',
        'balance_before'     => 'decimal:2',
        'balance_after'      => 'decimal:2',
    ];

    public function createdByUser()
    {
        return $this->belongsTo(\App\User::class, 'created_by_user_id');
    }

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('controlled_drug_register.home_id', $homeId);
    }

    /** Actions that reduce the balance (a dose leaving the cupboard). */
    private const OUTGOING = ['administered', 'disposed', 'returned'];

    /**
     * Append a witnessed movement to the register, with the running balance computed
     * SERVER-SIDE — never taken from the client.
     *
     * A controlled-drug register is an append-only running-balance ledger (Misuse of
     * Drugs Regs 2001 reg 19 for care homes; witnessing at administration/disposal is
     * CQC/NICE good practice — see STANDARDS-REGISTER STD-07). Its whole value is that
     * the balance is a computed chain nobody can fudge, so this is the ONLY way an entry
     * should ever be written. Entries are created, never updated or deleted; a mistake is
     * corrected by a later 'adjustment' entry, not by editing history.
     *
     *   balance_before = the last entry's balance_after for this medicine, OR — for the
     *                    very first entry — the medicine's current recorded stock (the
     *                    opening balance). This bootstraps the ledger from real stock.
     *   balance_after  = before - qty  (administered / disposed / returned)
     *                    before + qty  (received)
     *                    qty           (adjustment — an absolute recount)
     *
     * @param  MARSheet $sheet    the prescription the CD belongs to
     * @param  string   $action   administered|received|disposed|returned|adjustment
     * @param  float    $qty      quantity moved (or the recount total, for adjustment)
     * @param  int      $userId   who recorded it
     * @param  string|null $witness  second signatory (may be null where not required)
     */
    public static function record(MARSheet $sheet, string $action, float $qty, int $userId, ?string $witness, array $extra = []): self
    {
        return DB::transaction(function () use ($sheet, $action, $qty, $userId, $witness, $extra) {
            // Lock the medicine's ledger tail so two concurrent movements can't both read
            // the same "last balance" and branch the chain.
            $last = self::forHome($sheet->home_id)
                ->where('client_id', $sheet->client_id)
                ->where('medication_name', $sheet->medication_name)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $before = $last ? (float) $last->balance_after : (float) ($sheet->stock_level ?? 0);

            $after = match ($action) {
                'received' => $before + $qty,
                'adjustment' => $qty,                    // absolute recount
                default => max(0, $before - $qty),       // administered / disposed / returned
            };

            return self::create([
                'home_id' => $sheet->home_id,
                'client_id' => $sheet->client_id,
                'client_name' => $extra['client_name'] ?? null,
                'mar_sheet_id' => $sheet->id,
                'medication_name' => $sheet->medication_name,
                'cd_schedule' => $sheet->cd_schedule,
                'action_type' => $action,
                'entry_date' => $extra['entry_date'] ?? now()->toDateString(),
                'entry_time' => $extra['entry_time'] ?? now()->format('H:i:s'),
                'dose_quantity' => $action === 'adjustment' ? null : $qty,
                'unit' => $extra['unit'] ?? $sheet->unit,
                'balance_before' => $before,
                'balance_after' => $after,
                // witness_name is NOT NULL. Where a witness genuinely isn't required (e.g.
                // supported living), record that fact rather than a name — the register
                // still shows the movement and that it was unwitnessed.
                'witness_name' => ($witness !== null && trim($witness) !== '') ? $witness : 'Not witnessed',
                'notes' => $extra['notes'] ?? null,
                'created_by_user_id' => $userId,
            ]);
        });
    }
}
