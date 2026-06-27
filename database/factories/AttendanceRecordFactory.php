<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_session_id' => AttendanceSession::factory(),
            'user_id' => User::factory(),
            'status' => fake()->randomElement(['present', 'absent', 'late', 'excused']),
            'checked_in_at' => fake()->optional(0.8)->dateTimeThisMonth(),
            'checked_in_by' => User::factory(),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
