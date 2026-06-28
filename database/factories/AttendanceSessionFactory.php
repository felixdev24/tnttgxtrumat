<?php

namespace Database\Factories;

use App\Models\AttendanceSession;
use App\Models\TnttClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'title' => 'Sinh hoạt CN - '.$date->format('d/m/Y'),
            'session_date' => $date,
            'tntt_class_id' => TnttClass::factory(),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['upcoming', 'in_progress', 'completed']),
            'created_by' => User::factory(),
        ];
    }
}
