<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuizAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'answer',
        'is_correct',
        'points_awarded',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_awarded' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
