<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a correction request actually asks for.
 *
 * Without this the manager typed the replacement outcome themselves at the
 * moment of approving, which is not approving a request — it is writing a new
 * clinical judgement and calling it somebody else's. The person who was there
 * says what they believe happened; the manager approves or declines THAT.
 *
 * Nullable because only correction requests carry one.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->table('record7_review_items', function (Blueprint $table) {
            $table->string('requested_outcome', 40)->nullable()->after('detail');
        });
    }

    public function down(): void
    {
        Schema::connection('record7')->table('record7_review_items', function (Blueprint $table) {
            $table->dropColumn('requested_outcome');
        });
    }
};
