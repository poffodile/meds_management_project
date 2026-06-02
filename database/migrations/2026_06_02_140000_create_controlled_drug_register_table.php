<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlled_drug_register', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->unsignedBigInteger('client_id');
            $table->string('client_name')->nullable();        // snapshot of resident name
            $table->unsignedBigInteger('mar_sheet_id')->nullable(); // link to MAR med if picked from list
            $table->string('medication_name');
            $table->string('cd_schedule')->nullable();        // schedule_2 .. schedule_5
            $table->enum('action_type', ['administered', 'received', 'disposed', 'returned', 'adjustment']);
            $table->date('entry_date');
            $table->time('entry_time');
            $table->decimal('dose_quantity', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->decimal('balance_before', 10, 2)->nullable();
            $table->decimal('balance_after', 10, 2);
            $table->string('witness_name');                   // required for CDs
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            $table->index('home_id');
            $table->index('client_id');
            $table->index('entry_date');
            $table->index(['client_id', 'medication_name']);  // for running-balance lookup
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlled_drug_register');
    }
};
