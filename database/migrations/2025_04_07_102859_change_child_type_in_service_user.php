<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("ALTER TABLE service_user MODIFY child_type ENUM('residential', 'accommodation', 'leavers') NULL");
    
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
