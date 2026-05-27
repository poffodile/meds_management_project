<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEducationModuleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Education Profiles
        Schema::create('su_education_profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('service_user_id');
            $table->string('school_name');
            $table->string('grade');
            $table->text('subjects')->nullable();
            $table->string('academic_year');
            $table->integer('home_id');
            $table->integer('created_by'); // manager id
            $table->tinyInteger('status')->default(1); // 1 = active, 0 = inactive
            $table->timestamps();
        });

        // 2. Staff Assignments
        Schema::create('su_education_staff_assignments', function (Blueprint $table) {
            $table->id();
            $table->integer('service_user_id');
            $table->integer('staff_id'); // user id
            $table->integer('assigned_by'); // manager id
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // 3. Education Tasks (Homework)
        Schema::create('su_education_tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('service_user_id');
            $table->integer('education_profile_id');
            $table->integer('staff_id'); // created by staff
            $table->string('subject');
            $table->text('description');
            $table->date('due_date');
            $table->string('attachment')->nullable();
            $table->string('status')->default('pending'); // pending, completed
            $table->string('submission_file')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        // 4. Attendance
        Schema::create('su_education_attendance', function (Blueprint $table) {
            $table->id();
            $table->integer('service_user_id');
            $table->integer('education_profile_id');
            $table->integer('staff_id');
            $table->date('date');
            $table->string('status'); // present, absent, late
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Education Notes
        Schema::create('su_education_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('service_user_id');
            $table->integer('education_profile_id');
            $table->integer('staff_id');
            $table->text('notes');
            $table->boolean('is_alert')->default(false);
            $table->timestamps();
        });

        // 6. Education Resources
        Schema::create('su_education_resources', function (Blueprint $table) {
            $table->id();
            $table->integer('service_user_id');
            $table->integer('education_profile_id');
            $table->integer('staff_id');
            $table->string('title');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('su_education_resources');
        Schema::dropIfExists('su_education_notes');
        Schema::dropIfExists('su_education_attendance');
        Schema::dropIfExists('su_education_tasks');
        Schema::dropIfExists('su_education_staff_assignments');
        Schema::dropIfExists('su_education_profiles');
    }
}
