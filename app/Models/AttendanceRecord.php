<?php

namespace App\Models;

use Database\Factories\AttendanceRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    /** @use HasFactory<AttendanceRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'attendance_session_id',
        'user_id',
        'status', // present, absent, late, excused
        'checked_in_at',
        'checked_in_by',
        'notes',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
