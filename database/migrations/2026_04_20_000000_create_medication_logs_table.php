<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('medication_logs')) {
            Schema::create('medication_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('home_id');
                $table->integer('user_id');
                $table->integer('client_id');
                $table->string('medication_name');
                $table->string('dosage')->nullable();
                $table->string('frequesncy')->nullable(); // custom typo 'frequesncy' matches system-wide spelling
                $table->dateTime('administrator_date')->nullable();
                $table->string('witnessed_by')->nullable();
                $table->text('notes')->nullable();
                $table->text('side_effect')->nullable();
                $table->integer('status')->default(1);
                $table->tinyInteger('is_deleted')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medication_logs');
    }
}
