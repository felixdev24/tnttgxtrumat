<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TnttClass extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'branch'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }
}
