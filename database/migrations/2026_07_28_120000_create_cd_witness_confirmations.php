<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Witness co-signature confirmations for controlled-drug movements (issue #14, owner
 * decision A2 2026-07-28).
 *
 * Why a SEPARATE table, not columns on controlled_drug_register: the register is a strict
 * append-only ledger — nothing on a register row may ever be updated (that guarantee is the
 * whole point of a CD register). A witness confirmation has a LIFECYCLE — it starts pending
 * and later becomes confirmed or manager-overridden — so it cannot live as a mutable field
 * on the ledger. It lives here instead, referencing the register entry it confirms. One
 * confirmation per register entry that names a witness.
 *
 * Flow (the "Adam & Eve" example): Adam records/administers a CD and names Eve as witness ->
 * a row here is created `pending`, with Eve resolved to a staff user where possible so she
 * can be notified. Eve confirms on her own account -> `confirmed`. A manager may override ->
 * `manager_overridden` (with a reason and the manager's id), and the record shows it was
 * overridden, not witness-confirmed.
 *
 * Force class: the administration witness is CQC/NICE/RPS GOOD PRACTICE, not MDR 2001
 * statute — this is engineering support for that record, pending CSO confirmation (HAZ-05).
 *
 * Schema came from a dump, so this is guarded and re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cd_witness_confirmations')) {
            return;
        }

        Schema::create('cd_witness_confirmations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('home_id')->index();               // tenant scope

            // The controlled_drug_register entry this confirmation is for. No FK constraint
            // (this codebase scopes by denormalised ids, not DB-level FKs), but indexed so a
            // register entry can look up its confirmation cheaply.
            $table->unsignedBigInteger('register_id')->index();

            $table->unsignedInteger('recorded_by_user_id');            // who recorded/administered (Adam)
            $table->unsignedInteger('witness_user_id')->nullable();    // resolved witness account (Eve), if known
            $table->string('witness_name', 255);                       // the name as entered — always kept

            // pending -> confirmed | manager_overridden. A movement that genuinely needs no
            // witness (e.g. supported living) does not get a row here at all.
            $table->string('status', 20)->default('pending');

            $table->unsignedInteger('confirmed_by_user_id')->nullable(); // Eve (confirmed) OR the manager (overridden)
            $table->dateTime('confirmed_at')->nullable();
            $table->string('override_reason', 255)->nullable();          // required when manager-overridden

            $table->timestamps();

            // The core query: "signatures awaiting Eve" = witness_user_id = ? AND status = 'pending'.
            $table->index(['witness_user_id', 'status'], 'cdwc_witness_status_idx');
        });
    }

    public function down(): void
    {
        // Only drop if empty — confirmations are part of the CD audit trail once created.
        if (! Schema::hasTable('cd_witness_confirmations')) {
            return;
        }
        if (DB::table('cd_witness_confirmations')->exists()) {
            throw new \RuntimeException(
                'Refusing to drop cd_witness_confirmations: witness confirmation records exist '
                .'(part of the controlled-drug audit trail).'
            );
        }
        Schema::dropIfExists('cd_witness_confirmations');
    }
};
