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
        Schema::table('su_education_tasks', function (Blueprint $table) {
            $table->integer('rating')->nullable()->after('submitted_at');
            $table->text('staff_feedback')->nullable()->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('su_education_tasks', function (Blueprint $table) {
            $table->dropColumn(['rating', 'staff_feedback']);
        });
    }
};
