<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEducationTablesV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add title to tasks
        if (Schema::hasTable('su_education_tasks')) {
            Schema::table('su_education_tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('su_education_tasks', 'title')) {
                    $table->string('title')->after('staff_id')->nullable();
                }
            });
        }

        // Add type to notes
        if (Schema::hasTable('su_education_notes')) {
            Schema::table('su_education_notes', function (Blueprint $table) {
                if (!Schema::hasColumn('su_education_notes', 'type')) {
                    $table->string('type')->after('notes')->nullable();
                }
            });
        }

        // Add subject and link to resources
        if (Schema::hasTable('su_education_resources')) {
            Schema::table('su_education_resources', function (Blueprint $table) {
                if (!Schema::hasColumn('su_education_resources', 'subject')) {
                    $table->string('subject')->after('title')->nullable();
                }
                if (!Schema::hasColumn('su_education_resources', 'link')) {
                    $table->string('link')->after('subject')->nullable();
                }
                // Also make file_path nullable since we might only have a link
                $table->string('file_path')->nullable()->change();
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
        Schema::table('su_education_tasks', function (Blueprint $table) {
            $table->dropColumn('title');
        });
        Schema::table('su_education_notes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('su_education_resources', function (Blueprint $table) {
            $table->dropColumn(['subject', 'link']);
        });
    }
}
