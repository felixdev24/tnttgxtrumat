<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'role',
    'avatar',
    'address',
    'dob',
    'phone',
    'parent_phone',
    'rank',
    'years_of_activity',
    'grade_level',
    'branch',
    'qr_token',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'years_of_activity' => 'integer',
        ];
    }

    /**
     * Get the posts authored by this user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function createdSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'created_by');
    }

    public function scopeDoanSinh($query)
    {
        return $query->where('role', 'giao_ly_sinh');
    }

    public function scopeHuynhTruong($query)
    {
        return $query->where('role', 'huynh_truong');
    }

    public function scopeByGradeLevel($query, $level)
    {
        return $query->where('grade_level', $level);
    }

    public function generateQrToken(): string
    {
        return Str::uuid()->toString();
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->qr_token)) {
                $user->qr_token = $user->generateQrToken();
            }
        });
    }
}
