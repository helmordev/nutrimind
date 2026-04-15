<?php

declare(strict_types=1);

use App\Enums\DifficultyLevel;
use App\Enums\DifficultySetBy;
use App\Models\StudentDifficulty;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\LevelSeeder;
use Database\Seeders\QuarterSeeder;
use Database\Seeders\SubjectSeeder;

test('authenticated student can fetch grade specific worlds with quarters levels and difficulties', function (): void {
    $this->seed([
        SubjectSeeder::class,
        QuarterSeeder::class,
        LevelSeeder::class,
    ]);

    $student = User::factory()->student()->create([
        'grade' => 5,
        'section' => 'A',
    ]);

    $gradeFiveSubjects = Subject::query()
        ->where('grade', 5)
        ->orderBy('name')
        ->get();

    foreach ($gradeFiveSubjects as $subject) {
        StudentDifficulty::query()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'difficulty' => DifficultyLevel::Standard,
            'set_by' => DifficultySetBy::System,
        ]);
    }

    $english = $gradeFiveSubjects->firstWhere('name', 'English');
    $english?->quarters()->where('quarter_number', 1)->update([
        'current_unlock_week' => 2,
    ]);

    $token = $student->createToken('student-worlds-test')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/student/worlds');

    $response
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'grade',
                    'world_theme',
                    'color_hex',
                    'difficulty',
                    'quarters' => [
                        [
                            'id',
                            'quarter_number',
                            'current_unlock_week',
                            'is_globally_unlocked',
                            'levels' => [
                                [
                                    'id',
                                    'level_number',
                                    'title',
                                    'unlock_week',
                                    'is_unlocked',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    expect(collect($response->json('data'))->every(fn (array $world): bool => $world['grade'] === 5))->toBeTrue();

    $response->assertJsonPath('data.0.name', 'English');
    $response->assertJsonPath('data.0.difficulty', DifficultyLevel::Standard->value);
    $response->assertJsonPath('data.0.quarters.0.current_unlock_week', 2);
    $response->assertJsonPath('data.0.quarters.0.levels.0.level_number', 1);
    $response->assertJsonPath('data.0.quarters.0.levels.0.is_unlocked', true);
    $response->assertJsonPath('data.0.quarters.0.levels.2.level_number', 3);
    $response->assertJsonPath('data.0.quarters.0.levels.2.is_unlocked', false);
});

test('worlds endpoint requires authentication', function (): void {
    $this->getJson('/api/v1/student/worlds')
        ->assertUnauthorized();
});

test('non student cannot fetch worlds', function (): void {
    $teacher = User::factory()->teacher()->create();

    $token = $teacher->createToken('teacher-worlds-test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/student/worlds')
        ->assertForbidden();
});
