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
        Schema::table('leave_type', function (Blueprint $table) {
            $table->integer('max_days')->default(0)->after('leave_category');
            $table->boolean('deleted_at')->nullable()->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_type', function (Blueprint $table) {
            //
        });
    }
};
