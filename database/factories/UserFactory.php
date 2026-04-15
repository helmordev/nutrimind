<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role' => UserRole::Student,
            'full_name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => 'password',
            'grade' => fake()->randomElement([5, 6]),
            'section' => fake()->randomElement(['A', 'B', 'C']),
            'is_active' => true,
            'must_change_password' => false,
        ];
    }

    public function student(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Student,
            'grade' => fake()->randomElement([5, 6]),
            'section' => fake()->randomElement(['A', 'B', 'C']),
        ]);
    }

    public function teacher(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Teacher,
            'grade' => null,
            'section' => null,
            'teacher_id' => null,
        ]);
    }

    public function superAdmin(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::SuperAdmin,
            'grade' => null,
            'section' => null,
            'teacher_id' => null,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function mustChangePassword(): self
    {
        return $this->state(fn (array $attributes): array => [
            'must_change_password' => true,
        ]);
    }
}
