<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'quiz_week_id',
        'type',
        'content',
        'options',
        'correct_answer',
        'image_path',
        'points',
        'difficulty',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'is_active' => 'boolean',
    ];

    public function quizWeek(): BelongsTo
    {
        return $this->belongsTo(QuizWeek::class);
    }
}
