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
        Schema::create('client_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Created by
            $table->unsignedBigInteger('alert_type_id');
            $table->string('severity'); // Low, Medium, High, Critical
            $table->string('alert_title');
            $table->text('description');
            $table->text('action_required')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('requires_staff_acknowledgment')->default(0);
            $table->integer('staff_acknowledgment_count')->default(0);
            $table->dateTime('resolve_date')->nullable();
            $table->tinyInteger('status')->default(1); // 1: Active, 2: Resolved, 3: Archived
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_alerts');
    }
};
