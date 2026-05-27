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
            $table->enum('status', [
                'unfilled',     // Shift created but no staff assigned
                'assigned',     // Staff assigned but shift not started
                'in_progress',  // Shift currently running (based on time)
                'completed',    // Shift finished successfully
                'cancelled',    // Shift cancelled by admin
                'no_show'       // Staff assigned but did not attend
            ])
                ->default('unfilled')
                ->after('end_time')
                ->comment('Shift lifecycle status');
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
