<?php

namespace App\Models;

use Database\Factories\QuizWeekFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizWeek extends Model
{
    /** @use HasFactory<QuizWeekFactory> */
    use HasFactory;

    protected $fillable = [
        'week_number',
        'month',
        'year',
        'theme',
        'created_by',
    ];

    protected $casts = [
        'week_number' => 'integer',
        'month' => 'integer',
        'year' => 'integer',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return string Nhãn hiển thị: "Tuần 1 - Tháng 5/2026"
     */
    public function getLabel(): string
    {
        return "Tuần {$this->week_number} - Tháng {$this->month}/{$this->year}";
    }
}
