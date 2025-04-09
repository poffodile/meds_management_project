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
        Schema::create('credit_note_emails', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->string('loginUserId');
            $table->string('credit_id');
            $table->json('to');
            $table->json('cc')->nullable();
            $table->string('subject');
            $table->string('defaultSelect')->nullable();
            $table->text('body');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_emails');
    }
};
