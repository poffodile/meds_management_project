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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->string('loginUserId');
            $table->string('supplier_id');
            $table->string('credit_ref')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('telephone_code')->nullable();
            $table->string('telephone')->nullable();
            $table->string('mobile_code')->nullable();
            $table->string('mobile')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('post_code')->nullable();
            $table->string('date')->nullable();
            $table->string('supplier_ref')->nullable();
            $table->string('status')->nullable();
            $table->text('supplier_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
