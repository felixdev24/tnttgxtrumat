<?php

namespace App\Models;

use Database\Factories\AttendanceSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    /** @use HasFactory<AttendanceSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'session_type', // giao_ly, sinh_hoat
        'session_date',
        'tntt_class_id',
        'notes',
        'status', // upcoming, in_progress, completed
        'points_per_attendance',
        'created_by',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function presentMembers(): HasMany
    {
        return $this->records()->where('status', 'present');
    }

    public function absentMembers(): HasMany
    {
        return $this->records()->where('status', 'absent');
    }

    public function tnttClass(): BelongsTo
    {
        return $this->belongsTo(TnttClass::class);
    }

    public function scopeByTnttClass($query, $classId)
    {
        return $query->where('tntt_class_id', $classId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('session_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function attendanceRate(): float
    {
        $total = $this->records()->count();
        if ($total === 0) {
            return 0;
        }

        $present = $this->presentMembers()->count();

        return round(($present / $total) * 100, 2);
    }

    /**
     * Award points to all present members when session is completed.
     * Safe to call multiple times – skips users who already got points for this session.
     */
    public function awardPointsToPresent(): void
    {
        if ($this->points_per_attendance <= 0) {
            return;
        }

        $alreadyAwarded = \App\Models\PointTransaction::where('source_type', self::class)
            ->where('source_id', $this->id)
            ->pluck('user_id');

        $presentUserIds = $this->records()
            ->whereIn('status', ['present', 'late'])
            ->pluck('user_id')
            ->diff($alreadyAwarded);

        foreach ($presentUserIds as $userId) {
            \App\Models\PointTransaction::awardAttendance($userId, $this);
        }
    }
}
