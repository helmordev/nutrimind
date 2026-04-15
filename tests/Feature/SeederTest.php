<?php

declare(strict_types=1);

use App\Enums\ScreenTimeScope;
use App\Enums\UserRole;
use App\Models\Badge;
use App\Models\BossBattle;
use App\Models\Level;
use App\Models\Quarter;
use App\Models\ScreenTimeSetting;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Database\Seeders\BossSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LevelSeeder;
use Database\Seeders\QuarterSeeder;
use Database\Seeders\ScreenTimeSettingSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| SuperAdmin Seeder
|--------------------------------------------------------------------------
*/

test('super admin seeder creates one admin user', function (): void {
    $this->seed(SuperAdminSeeder::class);

    expect(User::where('role', UserRole::SuperAdmin)->count())->toBe(1);

    $admin = User::where('username', 'registrar')->firstOrFail();

    expect($admin->role)->toBe(UserRole::SuperAdmin)
        ->and($admin->full_name)->toBe('System Administrator')
        ->and($admin->is_active)->toBeTrue()
        ->and($admin->must_change_password)->toBeFalse()
        ->and($admin->grade)->toBeNull()
        ->and($admin->section)->toBeNull()
        ->and($admin->teacher_id)->toBeNull();
});

test('super admin password matches configured value', function (): void {
    config(['app.admin_initial_password' => 'TestPassword123']);

    $this->seed(SuperAdminSeeder::class);

    $admin = User::where('username', 'registrar')->firstOrFail();

    expect(Hash::check('TestPassword123', $admin->password))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Subject Seeder
|--------------------------------------------------------------------------
*/

test('subject seeder creates exactly 3 subjects', function (): void {
    $this->seed(SubjectSeeder::class);

    expect(Subject::count())->toBe(3);

    $subjects = Subject::orderBy('name')->pluck('name')->all();

    expect($subjects)->toBe(['English', 'Health+PE', 'Science']);
});

test('subjects have correct world themes and colors', function (): void {
    $this->seed(SubjectSeeder::class);

    $english = Subject::where('name', 'English')->firstOrFail();
    $science = Subject::where('name', 'Science')->firstOrFail();
    $health = Subject::where('name', 'Health+PE')->firstOrFail();

    expect($english->world_theme)->toBe('Library Dungeon')
        ->and($english->color_hex)->toBe('#4A90D9')
        ->and($science->world_theme)->toBe('Lab Cave')
        ->and($science->color_hex)->toBe('#50C878')
        ->and($health->world_theme)->toBe('Sports Arena')
        ->and($health->color_hex)->toBe('#FF6B6B');
});

/*
|--------------------------------------------------------------------------
| Quarter Seeder
|--------------------------------------------------------------------------
*/

test('quarter seeder creates 12 quarters (4 per subject)', function (): void {
    $this->seed(SubjectSeeder::class);
    $this->seed(QuarterSeeder::class);

    expect(Quarter::count())->toBe(12);

    Subject::all()->each(function (Subject $subject): void {
        $quarterCount = Quarter::where('subject_id', $subject->id)->count();
        expect($quarterCount)->toBe(4, "Subject {$subject->name} should have 4 quarters");
    });
});

test('quarters have sequential quarter numbers per subject', function (): void {
    $this->seed(SubjectSeeder::class);
    $this->seed(QuarterSeeder::class);

    Subject::all()->each(function (Subject $subject): void {
        $numbers = Quarter::where('subject_id', $subject->id)
            ->orderBy('quarter_number')
            ->pluck('quarter_number')
            ->all();

        expect($numbers)->toBe([1, 2, 3, 4]);
    });
});

/*
|--------------------------------------------------------------------------
| Level Seeder
|--------------------------------------------------------------------------
*/

test('level seeder creates 48 levels (4 per quarter)', function (): void {
    $this->seed(SubjectSeeder::class);
    $this->seed(QuarterSeeder::class);
    $this->seed(LevelSeeder::class);

    expect(Level::count())->toBe(48);

    Quarter::all()->each(function (Quarter $quarter): void {
        $levelCount = Level::where('quarter_id', $quarter->id)->count();
        expect($levelCount)->toBe(4, "Quarter {$quarter->id} should have 4 levels");
    });
});

test('levels have sequential level numbers per quarter', function (): void {
    $this->seed(SubjectSeeder::class);
    $this->seed(QuarterSeeder::class);
    $this->seed(LevelSeeder::class);

    Quarter::all()->each(function (Quarter $quarter): void {
        $numbers = Level::where('quarter_id', $quarter->id)
            ->orderBy('level_number')
            ->pluck('level_number')
            ->all();

        expect($numbers)->toBe([1, 2, 3, 4]);
    });
});

test('all levels have non-empty titles', function (): void {
    $this->seed(SubjectSeeder::class);
    $this->seed(QuarterSeeder::class);
    $this->seed(LevelSeeder::class);

    Level::all()->each(function (Level $level): void {
        expect($level->title)->not->toBeEmpty("Level {$level->id} should have a title");
    });
});

/*
|--------------------------------------------------------------------------
| Boss Seeder
|--------------------------------------------------------------------------
*/

test('boss seeder creates 12 bosses (1 per quarter)', function (): void {
    $this->seed(SubjectSeeder::class);
    $this->seed(QuarterSeeder::class);
    $this->seed(BossSeeder::class);

    expect(BossBattle::count())->toBe(12);

    Quarter::all()->each(function (Quarter $quarter): void {
        $bossCount = BossBattle::where('quarter_id', $quarter->id)->count();
        expect($bossCount)->toBe(1, "Quarter {$quarter->id} should have 1 boss");
    });
});

test('bosses have non-empty names', function (): void {
    $this->seed(SubjectSeeder::class);
    $this->seed(QuarterSeeder::class);
    $this->seed(BossSeeder::class);

    BossBattle::all()->each(function (BossBattle $boss): void {
        expect($boss->boss_name)->not->toBeEmpty();
    });
});

/*
|--------------------------------------------------------------------------
| Badge Seeder
|--------------------------------------------------------------------------
*/

test('badge seeder creates exactly 6 badges', function (): void {
    $this->seed(BadgeSeeder::class);

    expect(Badge::count())->toBe(6);

    $triggers = Badge::orderBy('trigger_type')->pluck('trigger_type')->all();

    expect($triggers)->toBe([
        'first_boss_defeat',
        'full_world_complete',
        'quarter_complete',
        'screen_time_compliant',
        'three_day_streak',
        'three_star_level',
    ]);
});

test('badges have names, descriptions, and icons', function (): void {
    $this->seed(BadgeSeeder::class);

    Badge::all()->each(function (Badge $badge): void {
        expect($badge->name)->not->toBeEmpty()
            ->and($badge->description)->not->toBeEmpty()
            ->and($badge->icon)->not->toBeEmpty();
    });
});

/*
|--------------------------------------------------------------------------
| Screen Time Setting Seeder
|--------------------------------------------------------------------------
*/

test('screen time setting seeder creates 1 global default', function (): void {
    $this->seed(ScreenTimeSettingSeeder::class);

    expect(ScreenTimeSetting::count())->toBe(1);

    $setting = ScreenTimeSetting::first();

    expect($setting->scope)->toBe(ScreenTimeScope::Global)
        ->and($setting->scope_id)->toBeNull()
        ->and($setting->school_day_limit_min)->toBe(45)
        ->and($setting->weekend_limit_min)->toBe(60)
        ->and($setting->max_levels_school)->toBe(2)
        ->and($setting->max_levels_weekend)->toBe(3);
});

/*
|--------------------------------------------------------------------------
| Full Database Seeder
|--------------------------------------------------------------------------
*/

test('database seeder creates correct total record counts', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(1)
        ->and(Subject::count())->toBe(3)
        ->and(Quarter::count())->toBe(12)
        ->and(Level::count())->toBe(48)
        ->and(BossBattle::count())->toBe(12)
        ->and(Badge::count())->toBe(6)
        ->and(ScreenTimeSetting::count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Idempotency
|--------------------------------------------------------------------------
*/

test('seeders are idempotent — running twice produces same counts', function (): void {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(1)
        ->and(Subject::count())->toBe(3)
        ->and(Quarter::count())->toBe(12)
        ->and(Level::count())->toBe(48)
        ->and(BossBattle::count())->toBe(12)
        ->and(Badge::count())->toBe(6)
        ->and(ScreenTimeSetting::count())->toBe(1);
});
