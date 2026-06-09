<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_weeks', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('week_number')->comment('Tuần thứ (1-5)');
            $table->unsignedTinyInteger('month')->comment('Tháng (1-12)');
            $table->unsignedSmallInteger('year')->comment('Năm');
            $table->string('theme')->nullable()->comment('Chủ đề của tuần');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['week_number', 'month', 'year'], 'quiz_weeks_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_weeks');
    }
};
