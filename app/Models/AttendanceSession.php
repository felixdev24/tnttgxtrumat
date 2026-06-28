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
        'session_date',
        'tntt_class_id',
        'notes',
        'status', // upcoming, in_progress, completed
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
}
