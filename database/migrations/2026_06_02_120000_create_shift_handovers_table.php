<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->string('location')->nullable();
            $table->date('handover_date');
            $table->time('handover_time');

            $table->unsignedBigInteger('from_carer_user_id')->nullable();
            $table->string('from_carer_name')->nullable();
            $table->unsignedBigInteger('to_carer_user_id')->nullable();
            $table->string('to_carer_name')->nullable();

            $table->text('general_notes')->nullable();
            $table->json('client_updates')->nullable();
            $table->json('medication_concerns')->nullable();
            $table->json('priority_alerts')->nullable();

            $table->enum('status', ['draft', 'submitted', 'acknowledged'])->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('acknowledged_at')->nullable();
            $table->unsignedBigInteger('acknowledged_by_user_id')->nullable();

            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->index('home_id');
            $table->index('handover_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_handovers');
    }
};
