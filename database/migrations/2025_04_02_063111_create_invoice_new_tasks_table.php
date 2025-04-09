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
        Schema::create('invoice_new_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->string('invoice_id');
            $table->integer('customer_id');
            $table->integer('user_id');
            $table->string('title');
            $table->integer('task_type_id');
            $table->date('start_date');
            $table->time('start_time');
            $table->date('end_date');
            $table->time('end_time');
            $table->boolean('is_recurring')->comment('0-no 1-yes')->nullable();
            $table->boolean('notify')->nullable();
            $table->date('notify_date')->nullable();
            $table->time('notify_time')->nullable();
            $table->boolean('notification')->nullable();
            $table->boolean('email')->nullable();
            $table->boolean('sms')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('status')->comment('0 pending,1 complete')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_new_tasks');
    }
};
