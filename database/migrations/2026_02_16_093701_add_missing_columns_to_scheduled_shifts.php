<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scheduled_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('scheduled_shifts', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable();
            }
            if (!Schema::hasColumn('scheduled_shifts', 'carer_id')) {
                $table->unsignedBigInteger('carer_id')->nullable();
            }
            if (!Schema::hasColumn('scheduled_shifts', 'form_id')) {
                $table->unsignedBigInteger('form_id')->nullable();
            }
            if (!Schema::hasColumn('scheduled_shifts', 'frequency')) {
                $table->string('frequency')->nullable();
            }
            if (!Schema::hasColumn('scheduled_shifts', 'week_days')) {
                $table->string('week_days')->nullable();
            }
            if (!Schema::hasColumn('scheduled_shifts', 'end_recurring_date')) {
                $table->date('end_recurring_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_shifts', function (Blueprint $table) {
            //
        });
    }
};
