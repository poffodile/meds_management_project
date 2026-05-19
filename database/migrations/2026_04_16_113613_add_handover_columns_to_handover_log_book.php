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
        Schema::table('handover_log_book', function (Blueprint $table) {
            $table->tinyInteger('is_deleted')->default(0)->after('notes');
            $table->datetime('acknowledged_at')->nullable()->after('is_deleted');
            $table->unsignedInteger('acknowledged_by')->nullable()->after('acknowledged_at');

            // Indexes for common queries
            $table->index(['home_id', 'is_deleted', 'date'], 'idx_handover_home_active_date');
            $table->index(['log_book_id'], 'idx_handover_log_book_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('handover_log_book', function (Blueprint $table) {
            $table->dropIndex('idx_handover_home_active_date');
            $table->dropIndex('idx_handover_log_book_id');
            $table->dropColumn(['is_deleted', 'acknowledged_at', 'acknowledged_by']);
        });
    }
};
