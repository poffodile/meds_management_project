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
        Schema::create('credit_note_allocates', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->string('loginUserId');
            $table->string('loginUserName');
            $table->string('po_id');
            $table->string('credit_id');
            $table->string('amount_paid');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_allocates');
    }
};
