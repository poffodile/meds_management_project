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
        if (!Schema::hasColumn('home', 'is_home_area')) {
            Schema::table('home', function (Blueprint $table) {
                $table->boolean('is_home_area')->default(0)->after('home_area');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home', function (Blueprint $table) {
            $table->dropColumn('is_home_area');
        });
    }
};
