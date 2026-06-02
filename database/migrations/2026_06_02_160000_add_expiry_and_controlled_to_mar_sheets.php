<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mar_sheets', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('end_date');
            $table->boolean('is_controlled')->default(false)->after('expiry_date');
            $table->string('cd_schedule', 50)->nullable()->after('is_controlled');
        });
    }

    public function down(): void
    {
        Schema::table('mar_sheets', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'is_controlled', 'cd_schedule']);
        });
    }
};
