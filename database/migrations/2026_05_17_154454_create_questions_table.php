<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_week_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['multiple_choice', 'fill_blank', 'image_guess'])
                ->default('multiple_choice')
                ->comment('Loại câu hỏi: trắc nghiệm, điền khuyết, đuổi hình');
            $table->text('content')->comment('Nội dung câu hỏi');
            $table->json('options')->nullable()->comment('Các tùy chọn đáp án (cho trắc nghiệm)');
            $table->string('correct_answer')->comment('Đáp án đúng');
            $table->string('image_path')->nullable()->comment('Đường dẫn ảnh (cho đuổi hình bắt chữ)');
            $table->unsignedSmallInteger('points')->default(10)->comment('Điểm số');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy')->comment('Độ khó');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
