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
        Schema::table('leads', function (Blueprint $table) {
            $table->enum('converted_to', ['quote', 'job', 'customer'])->nullable()->after('prefer_time');
            $table->boolean('notity')->after('converted_to')->nullable();
            $table->integer('notifiy_user_id')->after('notify')->nullable();
            $table->boolean('notifocation')->after('notifiy_user_id')->nullable();
            $table->boolean('sms')->after('notifocation')->nullable();
            $table->boolean('email')->after('sms')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
};
