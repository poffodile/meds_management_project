<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE scheduled_shifts MODIFY COLUMN status ENUM('unfilled', 'assigned', 'in_progress', 'completed', 'cancelled', 'no_show', 'approved', 'rejected') DEFAULT 'unfilled' COMMENT 'Shift lifecycle status'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE scheduled_shifts MODIFY COLUMN status ENUM('unfilled', 'assigned', 'in_progress', 'completed', 'cancelled', 'no_show') DEFAULT 'unfilled' COMMENT 'Shift lifecycle status'");
    }
};
