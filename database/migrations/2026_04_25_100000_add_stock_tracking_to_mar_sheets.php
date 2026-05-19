<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mar_sheets', function (Blueprint $table) {
            $table->unsignedInteger('quantity_received')->nullable()->after('reorder_level');
            $table->unsignedInteger('quantity_carried_forward')->nullable()->after('quantity_received');
            $table->unsignedInteger('quantity_returned')->nullable()->after('quantity_carried_forward');
        });
    }

    public function down(): void
    {
        Schema::table('mar_sheets', function (Blueprint $table) {
            $table->dropColumn(['quantity_received', 'quantity_carried_forward', 'quantity_returned']);
        });
    }
};
