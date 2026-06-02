<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_handovers', function (Blueprint $table) {
            // Detailed audit trail of edits: [{user_id, user_name, at, changes:[...]}]
            $table->json('edit_log')->nullable()->after('acknowledged_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('shift_handovers', function (Blueprint $table) {
            $table->dropColumn('edit_log');
        });
    }
};
