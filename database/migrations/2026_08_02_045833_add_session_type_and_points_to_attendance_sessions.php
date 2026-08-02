<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            // Session type: giao_ly (catechism by class) | sinh_hoat (activity for all)
            $table->string('session_type')->default('giao_ly')->after('title'); // giao_ly, sinh_hoat
            // Points awarded to present members when session is completed
            $table->unsignedTinyInteger('points_per_attendance')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn(['session_type', 'points_per_attendance']);
        });
    }
};
