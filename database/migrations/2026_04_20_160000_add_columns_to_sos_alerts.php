<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sos_alerts', function (Blueprint $table) {
            $table->integer('home_id')->after('id');
            $table->text('message')->nullable()->after('location');
            $table->tinyInteger('status')->default(1)->after('message');
            $table->integer('acknowledged_by')->nullable()->after('status');
            $table->dateTime('acknowledged_at')->nullable()->after('acknowledged_by');
            $table->integer('resolved_by')->nullable()->after('acknowledged_at');
            $table->dateTime('resolved_at')->nullable()->after('resolved_by');
            $table->tinyInteger('is_deleted')->default(0)->after('resolved_at');

            $table->index('home_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('sos_alerts', function (Blueprint $table) {
            $table->dropIndex(['home_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['home_id', 'message', 'status', 'acknowledged_by', 'acknowledged_at', 'resolved_by', 'resolved_at', 'is_deleted']);
        });
    }
};
