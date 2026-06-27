<?php

namespace Database\Factories;

use App\Models\AttendanceSession;
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
        $gradeLevels = ['Khai Tâm 1', 'Khai Tâm 2', 'Rước Lễ 1', 'Rước Lễ 2', 'Thêm Sức 1', 'Thêm Sức 2', 'Bao Đồng 1', 'Bao Đồng 2'];
        $date = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'title' => 'Sinh hoạt CN - '.$date->format('d/m/Y'),
            'session_date' => $date,
            'grade_level' => fake()->randomElement($gradeLevels),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['upcoming', 'in_progress', 'completed']),
            'created_by' => User::factory(),
        ];
    }
}
