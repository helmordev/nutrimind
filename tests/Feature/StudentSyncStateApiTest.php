<?php

declare(strict_types=1);

use App\Enums\DifficultyLevel;
use App\Enums\DifficultySetBy;
use App\Enums\Language;
use App\Enums\ScreenTimeScope;
use App\Enums\TextSize;
use App\Models\Badge;
use App\Models\GradeRecord;
use App\Models\StudentBadge;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Database\Seeders\LevelSeeder;
use Database\Seeders\QuarterSeeder;
use Database\Seeders\ScreenTimeSettingSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Support\Facades\Auth;

test('authenticated student can fetch a full sync state payload', function (): void {
    $this->seed([
        SubjectSeeder::class,
        QuarterSeeder::class,
        LevelSeeder::class,
        BadgeSeeder::class,
        ScreenTimeSettingSeeder::class,
    ]);

    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Sync State Student',
        'username' => 'syncstudent',
        'lrn' => '123123123123',
        'grade' => 5,
        'section' => 'A',
    ])->assertRedirect(route('teacher.students.create'));

    $student = User::query()
        ->where('username', 'syncstudent')
        ->firstOrFail();

    Auth::logout();

    $english = Subject::query()
        ->where('grade', 5)
        ->where('name', 'English')
        ->firstOrFail();

    $student->studentPreferences()->update([
        'language' => Language::Filipino,
        'master_volume' => 60,
        'bgm_volume' => 50,
        'sfx_volume' => 40,
        'tts_enabled' => false,
        'text_size' => TextSize::Large,
        'colorblind_mode' => true,
    ]);

    $student->studentDifficulties()
        ->where('subject_id', $english->id)
        ->update([
            'difficulty' => DifficultyLevel::Hard,
            'set_by' => DifficultySetBy::Teacher,
            'updated_at_by_teacher' => now(),
        ]);

    $badge = Badge::query()->firstOrFail();

    StudentBadge::query()->create([
        'student_id' => $student->id,
        'badge_id' => $badge->id,
        'earned_at' => now(),
    ]);

    GradeRecord::query()->create([
        'student_id' => $student->id,
        'subject_id' => $english->id,
        'quarter_number' => 1,
        'written_work' => 92,
        'performance_task' => 94,
        'quarterly_assessment' => 91,
        'final_grade' => 92.5,
        'computed_at' => now(),
    ]);

    $token = $student->createToken('student-sync-test')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/student/sync/state');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'worlds',
            'preferences' => [
                'language',
                'master_volume',
                'bgm_volume',
                'sfx_volume',
                'tts_enabled',
                'text_size',
                'colorblind_mode',
            ],
            'difficulties' => [
                [
                    'subject_id',
                    'subject_name',
                    'difficulty',
                    'set_by',
                    'updated_at_by_teacher',
                ],
            ],
            'screen_time' => [
                'scope',
                'school_day_limit_min',
                'weekend_limit_min',
                'max_levels_school',
                'max_levels_weekend',
                'play_start_school',
                'play_end_school',
                'play_start_weekend',
                'play_end_weekend',
            ],
            'badges' => [
                [
                    'badge_id',
                    'name',
                    'description',
                    'icon',
                    'trigger_type',
                    'earned_at',
                ],
            ],
            'grades' => [
                [
                    'subject_id',
                    'subject_name',
                    'quarter_number',
                    'written_work',
                    'performance_task',
                    'quarterly_assessment',
                    'final_grade',
                    'computed_at',
                ],
            ],
        ]);

    $response->assertJsonCount(3, 'worlds');
    $response->assertJsonCount(3, 'difficulties');
    $response->assertJsonPath('preferences.language', Language::Filipino->value);
    $response->assertJsonPath('preferences.text_size', TextSize::Large->value);
    $response->assertJsonPath('preferences.tts_enabled', false);
    $response->assertJsonPath('screen_time.scope', ScreenTimeScope::Global->value);
    $response->assertJsonPath('badges.0.badge_id', $badge->id);
    $response->assertJsonPath('badges.0.name', $badge->name);
    $response->assertJsonPath('grades.0.subject_id', $english->id);
    $response->assertJsonPath('grades.0.subject_name', 'English');
    $response->assertJsonPath('grades.0.quarter_number', 1);
    $response->assertJsonPath('worlds.0.name', 'English');

    expect(collect($response->json('difficulties'))->contains(
        fn (array $difficulty): bool => $difficulty['subject_name'] === 'English'
            && $difficulty['difficulty'] === DifficultyLevel::Hard->value
            && $difficulty['set_by'] === DifficultySetBy::Teacher->value,
    ))->toBeTrue();
});

test('teacher student creation seeds default preferences and difficulties', function (): void {
    $this->seed(SubjectSeeder::class);

    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Defaulted Student',
        'username' => 'defaultstudent',
        'lrn' => '321321321321',
        'grade' => 6,
        'section' => 'B',
    ])->assertRedirect(route('teacher.students.create'));

    $student = User::query()
        ->where('username', 'defaultstudent')
        ->firstOrFail();

    expect($student->studentPreferences)->not->toBeNull();
    expect($student->studentPreferences->language)->toBe(Language::English);
    expect($student->studentPreferences->text_size)->toBe(TextSize::Medium);
    expect($student->studentDifficulties)->toHaveCount(3);
    expect($student->studentDifficulties->every(
        fn ($difficulty): bool => $difficulty->difficulty === DifficultyLevel::Standard
            && $difficulty->set_by === DifficultySetBy::System,
    ))->toBeTrue();
});

test('sync state endpoint requires authentication', function (): void {
    $this->getJson('/api/v1/student/sync/state')
        ->assertUnauthorized();
});

test('non student cannot fetch sync state', function (): void {
    $teacher = User::factory()->teacher()->create();
    $token = $teacher->createToken('teacher-sync-test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/student/sync/state')
        ->assertForbidden();
});
