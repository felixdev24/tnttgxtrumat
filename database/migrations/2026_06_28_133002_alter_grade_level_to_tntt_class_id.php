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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('grade_level');
            $table->foreignId('tntt_class_id')->nullable()->constrained('tntt_classes')->nullOnDelete();
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropIndex(['session_date', 'grade_level']);
            $table->dropColumn('grade_level');
            $table->foreignId('tntt_class_id')->nullable()->constrained('tntt_classes')->nullOnDelete();
            $table->index(['session_date', 'tntt_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropIndex(['session_date', 'tntt_class_id']);
            $table->dropForeign(['tntt_class_id']);
            $table->dropColumn('tntt_class_id');
            $table->string('grade_level')->nullable();
            $table->index(['session_date', 'grade_level']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tntt_class_id']);
            $table->dropColumn('tntt_class_id');
            $table->string('grade_level')->nullable();
        });
    }
};
