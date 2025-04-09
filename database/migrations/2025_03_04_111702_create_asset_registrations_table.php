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
        Schema::create('asset_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('asset_name');
            $table->string('asset_type');
            $table->date('date');
            $table->string('cost_bfwd')->nullable();
            $table->string('cost_disposal')->nullable();
            $table->string('cost_addition')->nullable();
            $table->string('cost_fwd')->nullable();
            $table->string('depreciation_bfwd')->nullable();
            $table->string('depreciation_type')->nullable();
            $table->string('charge')->nullable();
            $table->string('depreciation')->nullable();
            $table->string('depreciation_cfwd')->nullable();
            $table->string('nbv_cfwd')->nullable();
            $table->string('nq')->nullable();
            $table->string('nbv_bfwd')->nullable();
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_registrations');
    }
};
