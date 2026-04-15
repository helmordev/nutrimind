<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
final class ClassroomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grade = fake()->randomElement([5, 6]);
        $section = fake()->randomElement(['A', 'B', 'C', 'D']);

        return [
            'teacher_id' => User::factory()->teacher(),
            'name' => sprintf('Grade %s - Section %s', $grade, $section),
            'grade' => $grade,
            'section' => $section,
            'room_code' => Classroom::generateRoomCode(),
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function forGrade(int $grade): self
    {
        return $this->state(fn (array $attributes): array => [
            'grade' => $grade,
        ]);
    }
}
