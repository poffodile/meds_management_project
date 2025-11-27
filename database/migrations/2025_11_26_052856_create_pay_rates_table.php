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
        Schema::create('pay_rates', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->unsignedBigInteger('access_level_id');
            $table->decimal('pay_rate', 10, 2);
            $table->tinyInteger('status')->default(1);     // 1 = Active, 0 = Inactive
            $table->tinyInteger('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_rates');
    }
};
