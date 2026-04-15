<?php

declare(strict_types=1);

use App\Enums\DifficultyLevel;
use App\Enums\DifficultySetBy;
use App\Enums\Language;
use App\Enums\QuestionType;
use App\Enums\ScreenTimeScope;
use App\Enums\TextSize;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

it('creates all required tables', function (): void {
    $requiredTables = [
        'users',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'personal_access_tokens',
        'student_profiles',
        'student_preferences',
        'subjects',
        'quarters',
        'levels',
        'questions',
        'boss_battles',
        'boss_questions',
        'student_difficulties',
        'difficulty_advisories',
        'student_progress',
        'boss_results',
        'badges',
        'student_badges',
        'grade_records',
        'screen_time_logs',
        'screen_time_settings',
        'at_risk_alerts',
    ];

    foreach ($requiredTables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Table '{$table}' should exist");
    }
});

it('has correct columns on users table', function (): void {
    $expectedColumns = [
        'id', 'role', 'full_name', 'username', 'password',
        'grade', 'section', 'teacher_id', 'is_active',
        'must_change_password', 'created_at', 'updated_at',
    ];

    foreach ($expectedColumns as $column) {
        expect(Schema::hasColumn('users', $column))->toBeTrue("Column '{$column}' should exist on users table");
    }

    $removedColumns = ['name', 'email', 'email_verified_at', 'remember_token'];

    foreach ($removedColumns as $column) {
        expect(Schema::hasColumn('users', $column))->toBeFalse("Column '{$column}' should NOT exist on users table");
    }
});

it('uses uuid primary keys on users table', function (): void {
    $user = User::factory()->superAdmin()->create();

    expect($user->id)
        ->toBeString()
        ->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

it('casts role to UserRole enum', function (): void {
    $user = User::factory()->teacher()->create();

    expect($user->role)->toBe(UserRole::Teacher);
});

it('creates student with factory states', function (): void {
    $teacher = User::factory()->teacher()->create();

    $student = User::factory()->student()->create([
        'teacher_id' => $teacher->id,
    ]);

    expect($student->role)->toBe(UserRole::Student)
        ->and($student->teacher_id)->toBe($teacher->id)
        ->and($student->is_active)->toBeTrue()
        ->and($student->must_change_password)->toBeFalse();
});

it('has correct teacher-student relationship', function (): void {
    $teacher = User::factory()->teacher()->create();
    User::factory()->student()->count(3)->create([
        'teacher_id' => $teacher->id,
    ]);

    expect($teacher->students)->toHaveCount(3);
});

it('has all UserRole enum values', function (): void {
    expect(UserRole::cases())->toHaveCount(3)
        ->and(UserRole::Student->value)->toBe('student')
        ->and(UserRole::Teacher->value)->toBe('teacher')
        ->and(UserRole::SuperAdmin->value)->toBe('super_admin');
});

it('has all DifficultyLevel enum values', function (): void {
    expect(DifficultyLevel::cases())->toHaveCount(3)
        ->and(DifficultyLevel::Easy->value)->toBe('easy')
        ->and(DifficultyLevel::Standard->value)->toBe('standard')
        ->and(DifficultyLevel::Hard->value)->toBe('hard');
});

it('has all QuestionType enum values', function (): void {
    expect(QuestionType::cases())->toHaveCount(5)
        ->and(QuestionType::MultipleChoice->value)->toBe('multiple_choice')
        ->and(QuestionType::TrueOrFalse->value)->toBe('true_or_false')
        ->and(QuestionType::Identification->value)->toBe('identification')
        ->and(QuestionType::Matching->value)->toBe('matching')
        ->and(QuestionType::Sequencing->value)->toBe('sequencing');
});

it('has all TextSize enum values', function (): void {
    expect(TextSize::cases())->toHaveCount(3)
        ->and(TextSize::Small->value)->toBe('small')
        ->and(TextSize::Medium->value)->toBe('medium')
        ->and(TextSize::Large->value)->toBe('large');
});

it('has all Language enum values', function (): void {
    expect(Language::cases())->toHaveCount(2)
        ->and(Language::English->value)->toBe('en')
        ->and(Language::Filipino->value)->toBe('fil');
});

it('has all ScreenTimeScope enum values', function (): void {
    expect(ScreenTimeScope::cases())->toHaveCount(3)
        ->and(ScreenTimeScope::Global->value)->toBe('global')
        ->and(ScreenTimeScope::ClassScope->value)->toBe('class')
        ->and(ScreenTimeScope::Student->value)->toBe('student');
});

it('has all DifficultySetBy enum values', function (): void {
    expect(DifficultySetBy::cases())->toHaveCount(2)
        ->and(DifficultySetBy::System->value)->toBe('system_default')
        ->and(DifficultySetBy::Teacher->value)->toBe('teacher');
});

it('uses uuidMorphs for personal_access_tokens', function (): void {
    expect(Schema::hasColumn('personal_access_tokens', 'tokenable_id'))->toBeTrue()
        ->and(Schema::hasColumn('personal_access_tokens', 'tokenable_type'))->toBeTrue();
});
