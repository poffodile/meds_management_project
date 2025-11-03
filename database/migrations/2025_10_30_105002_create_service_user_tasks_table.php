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
        Schema::create('service_user_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->integer('user_id');
            $table->unsignedBigInteger('service_user_id'); // reference to user/child
            $table->string('task');
            $table->date('date');
            $table->string('time')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_user_tasks');
    }
};
