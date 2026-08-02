<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type', // attendance, activity, quiz
        'points',
        'source_id',
        'source_type',
        'description',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Award points to a user from an attendance session.
     */
    public static function awardAttendance(int $userId, AttendanceSession $session): self
    {
        $type = $session->session_type === 'sinh_hoat' ? 'activity' : 'attendance';

        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'points' => $session->points_per_attendance,
            'source_id' => $session->id,
            'source_type' => AttendanceSession::class,
            'description' => 'Điểm danh: '.$session->title,
        ]);
    }

    /**
     * Award quiz points to a user.
     */
    public static function awardQuiz(int $userId, int $points, int $sourceId, string $description = ''): self
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'quiz',
            'points' => $points,
            'source_id' => $sourceId,
            'source_type' => 'quiz_week',
            'description' => $description ?: 'Điểm đố vui Kinh Thánh',
        ]);
    }

    /** Scope by type */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
