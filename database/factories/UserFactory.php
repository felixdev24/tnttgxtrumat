<?php

namespace Database\Factories;

use App\Models\TnttClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'qr_token' => Str::uuid()->toString(),
        ];
    }

    /**
     * Indicate that the user is a Doan Sinh.
     */
    public function doanSinh(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'giao_ly_sinh',
            'tntt_class_id' => TnttClass::factory(),
            'branch' => fake()->randomElement(['Ấu', 'Thiếu', 'Nghĩa', 'Hiệp']),
            'phone' => fake()->phoneNumber(),
            'parent_phone' => fake()->phoneNumber(),
            'dob' => fake()->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
