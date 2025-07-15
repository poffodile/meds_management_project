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
        Schema::create('staff_workers', function (Blueprint $table) {
            $table->id();
            $table->string('home_id')->nullable();
            $table->string('surname');
            $table->string('forename');
            $table->string('address');
            $table->string('postCode');
            $table->date('DOB');
            $table->string('account_num');
            $table->string('sort_code');
            $table->enum('status', ['residential', 'supported_accomodation', 'parental', 'foundations_for_life', 'office_staff', 'leavers' ])->nullable();
            $table->decimal('rate_of_pay', 10, 2);
            $table->enum('level', ['qualified', 'unqualified'])->nullable();
            $table->date('start_date');
            $table->string('job_role');
            $table->string('NIN');
            $table->unsignedTinyInteger('starter_declaration')->nullable();
            $table->date('probation_start_date');
            $table->date('probation_end_date');
            $table->date('probation_renew_date')->nullable();
            $table->boolean('probation_enrollered')->nullable();
            $table->enum('student_loan', ['no_student_loan', 'postgraduate', 'plan_1', 'plan_2', 'plan_4'])->nullable();
            $table->boolean('dbs_clear')->nullable();
            $table->string('dbs_number')->nullable();
            $table->boolean('dbs_service_update')->nullable();
            $table->date('leave_date')->nullable();
            $table->string('email');
            $table->string("mobile")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_workers');
    }
};
