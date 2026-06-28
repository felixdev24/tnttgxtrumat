<?php

namespace Database\Factories;

use App\Models\TnttClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TnttClass>
 */
class TnttClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Lớp '.fake()->unique()->word().' '.fake()->randomDigit(),
            'branch' => fake()->randomElement(['Ấu', 'Thiếu', 'Nghĩa', 'Hiệp', 'Trợ Úy', 'Huynh Trưởng']),
        ];
    }
}
