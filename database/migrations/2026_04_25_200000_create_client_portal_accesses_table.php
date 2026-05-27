<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_portal_accesses', function (Blueprint $table) {
            $table->id();
            $table->integer('home_id')->index();
            $table->integer('client_id')->index();
            $table->string('client_type', 50)->default('residential');
            $table->string('user_email', 255)->index();
            $table->string('full_name', 255);
            $table->string('relationship', 50);
            $table->string('access_level', 50)->default('view_and_message');
            $table->tinyInteger('can_view_schedule')->default(1);
            $table->tinyInteger('can_view_care_notes')->default(1);
            $table->tinyInteger('can_send_messages')->default(1);
            $table->tinyInteger('can_request_bookings')->default(0);
            $table->string('phone', 50)->nullable();
            $table->tinyInteger('is_primary_contact')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->date('activation_date')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('is_deleted')->default(0);
            $table->integer('created_by');
            $table->timestamps();

            $table->index(['is_active', 'is_deleted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_portal_accesses');
    }
};
