<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix column types to match parent PK types (signed int)
        DB::statement('ALTER TABLE body_map MODIFY home_id INT NULL');
        DB::statement('ALTER TABLE body_map MODIFY created_by INT NULL');
        DB::statement('ALTER TABLE body_map MODIFY updated_by INT NULL');

        // Add foreign key constraints and indexes
        Schema::table('body_map', function (Blueprint $table) {
            // Foreign keys
            $table->foreign('home_id')->references('id')->on('home')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('user')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('user')->onDelete('set null');

            // Indexes
            $table->index(['home_id', 'is_deleted'], 'bm_home_deleted_idx');
            $table->index(['service_user_id', 'is_deleted'], 'bm_su_deleted_idx');
            $table->index(['home_id', 'su_risk_id', 'is_deleted'], 'bm_home_risk_active');
            $table->index(['home_id', 'service_user_id'], 'bm_home_service_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('body_map', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('bm_home_deleted_idx');
            $table->dropIndex('bm_su_deleted_idx');
            $table->dropIndex('bm_home_risk_active');
            $table->dropIndex('bm_home_service_user');

            // Drop foreign keys
            $table->dropForeign(['home_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
    }
};
