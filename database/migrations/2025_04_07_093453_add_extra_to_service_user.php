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
        Schema::table('service_user', function (Blueprint $table) {
            $table->decimal('subs', 8, 2)->after('weekly_rate')->nullable();
            $table->decimal('extra',8, 2)->after('subs')->nullable();
            $table->string('local_authority')->after('extra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            //
        });
    }
};
