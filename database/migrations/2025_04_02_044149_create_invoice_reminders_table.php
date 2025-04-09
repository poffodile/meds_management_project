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
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->string('loginUserId');
            $table->string('invoice_id');
            $table->string('user_id');
            $table->date('reminder_date');
            $table->string('reminder_time');
            $table->string('notification')->nullable();
            $table->string('sms')->nullable();
            $table->string('email')->nullable();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->boolean('status')->comment('0 pending, 1 sent')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');
    }
};
