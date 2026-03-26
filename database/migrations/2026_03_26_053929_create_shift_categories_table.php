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
        Schema::create('shift_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id')->nullable();
            $table->string('name');
            $table->string('color', 10)->nullable();
            $table->boolean('status')->default('1')->comment('1=Active,0=Inactive');
            $table->boolean('is_deleted')->default('0')->comment('0=Not Deleted,1=Deleted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_categories');
    }
};
