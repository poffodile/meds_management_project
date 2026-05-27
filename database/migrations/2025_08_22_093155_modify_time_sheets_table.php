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
        Schema::table('time_sheets', function (Blueprint $table) {
            $table->dropColumn([
                'sleep', 
                'wake_night', 
                'disturbance', 
                'annual_leave', 
                'on_call', 
            ]);
            $table->unsignedBigInteger('category_id')->after('user_id');
            $table->time('time')->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_sheets', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'time']);
            $table->time('sleep')->nullable();
            $table->time('wake_night')->nullable();
            $table->integer('disturbance')->nullable();
            $table->boolean('annual_leave')->default(false);
            $table->boolean('on_call')->default(false);
        });
    }
};
