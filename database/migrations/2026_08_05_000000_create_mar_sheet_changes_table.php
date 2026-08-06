<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prescription change-log (Care One OS / frontend4, I18).
 *
 * An append-only record of every change to a prescription (mar_sheet): pause,
 * resume, stop, and later dose/field changes. `mar_sheets` itself has no
 * modified_by / change history, so a prescription edit could otherwise mutate
 * the row without an attributable, reversible record — below the standard the
 * specification and the Definition of Done require.
 *
 * Additive only: this does NOT alter any existing table the other front ends
 * read. This project builds its schema from a dump rather than running
 * migrations, so this file is the version-controlled record of the change; the
 * table was also applied directly to the database when it was introduced.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mar_sheet_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mar_sheet_id')->index();
            $table->integer('home_id')->nullable()->index();
            $table->integer('client_id')->nullable()->index();
            $table->string('change_type', 32);   // paused | resumed | stopped | ...
            $table->string('field', 64)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->integer('changed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mar_sheet_changes');
    }
};
