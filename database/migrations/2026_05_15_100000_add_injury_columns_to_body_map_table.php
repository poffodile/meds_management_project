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
        Schema::table('body_map', function (Blueprint $table) {
            if (!Schema::hasColumn('body_map', 'home_id')) {
                $table->integer('home_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('body_map', 'injury_type')) {
                $table->string('injury_type', 255)->nullable()->after('sel_body_map_id');
            }
            if (!Schema::hasColumn('body_map', 'injury_description')) {
                $table->text('injury_description')->nullable()->after('injury_type');
            }
            if (!Schema::hasColumn('body_map', 'injury_date')) {
                $table->date('injury_date')->nullable()->after('injury_description');
            }
            if (!Schema::hasColumn('body_map', 'injury_size')) {
                $table->string('injury_size', 255)->nullable()->after('injury_date');
            }
            if (!Schema::hasColumn('body_map', 'injury_colour')) {
                $table->string('injury_colour', 255)->nullable()->after('injury_size');
            }
            if (!Schema::hasColumn('body_map', 'created_by')) {
                $table->integer('created_by')->nullable()->after('is_deleted');
            }
            if (!Schema::hasColumn('body_map', 'updated_by')) {
                $table->integer('updated_by')->nullable()->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('body_map', function (Blueprint $table) {
            $table->dropColumn([
                'home_id',
                'injury_type',
                'injury_description',
                'injury_date',
                'injury_size',
                'injury_colour',
                'created_by',
                'updated_by'
            ]);
        });
    }
};
