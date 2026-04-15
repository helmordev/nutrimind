<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
final class StudentProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'lrn' => fake()->unique()->numerify('############'),
            'pin' => '123456',
            'pin_generated_at' => now(),
            'last_login_at' => null,
        ];
    }
}
