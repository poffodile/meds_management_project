<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "I have read the handover."
 *
 * A handover nobody confirms reading is a handover that was not handed over.
 * This is the record that somebody did — who, and when — which is the only
 * thing that turns a note left on a screen into an actual transfer of
 * responsibility between two shifts.
 *
 * One row per person per handover: it is a personal acknowledgement, not a
 * house-level tick. Two people starting the same shift each confirm for
 * themselves, and the unique key means confirming twice is harmless.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->create('record7_handover_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handover_id')->constrained('record7_handovers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['handover_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('record7')->dropIfExists('record7_handover_reads');
    }
};
