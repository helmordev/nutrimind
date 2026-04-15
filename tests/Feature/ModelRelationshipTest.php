<?php

declare(strict_types=1);

use App\Enums\DifficultyLevel;
use App\Enums\DifficultySetBy;
use App\Enums\QuestionType;
use App\Models\AtRiskAlert;
use App\Models\Badge;
use App\Models\BossBattle;
use App\Models\BossResult;
use App\Models\Classroom;
use App\Models\DifficultyAdvisory;
use App\Models\GradeRecord;
use App\Models\Level;
use App\Models\Quarter;
use App\Models\Question;
use App\Models\ScreenTimeLog;
use App\Models\StudentBadge;
use App\Models\StudentDifficulty;
use App\Models\StudentPreference;
use App\Models\StudentProgress;
use App\Models\Subject;
use App\Models\User;

// ====================================================================
// Helper: create a full subject → quarter → level chain for FK needs
// ====================================================================

function createSubjectQuarterLevel(): array
{
    $subject = Subject::query()->create([
        'name' => 'Math',
        'grade' => 5,
        'world_theme' => 'jungle',
        'color_hex' => '#00FF00',
    ]);

    $quarter = Quarter::query()->create([
        'subject_id' => $subject->id,
        'quarter_number' => 1,
        'current_unlock_week' => 1,
        'is_globally_unlocked' => false,
    ]);

    $level = Level::query()->create([
        'quarter_id' => $quarter->id,
        'level_number' => 1,
        'title' => 'Addition Basics',
        'matatag_competency_code' => 'M5-Q1-01',
        'matatag_competency_desc' => 'Add whole numbers',
        'unlock_week' => 1,
    ]);

    return [$subject, $quarter, $level];
}

// ====================================================================
// AtRiskAlert
// ====================================================================

it('casts at risk alert attributes correctly', function (): void {
    $student = User::factory()->student()->create();
    [$subject] = createSubjectQuarterLevel();

    $alert = AtRiskAlert::query()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'quarter_number' => 1,
        'grade_at_flag' => 72.50,
        'is_resolved' => false,
        'resolved_at' => null,
    ]);

    $alert->refresh();

    expect($alert->quarter_number)->toBeInt()
        ->and($alert->is_resolved)->toBeBool();
});

it('has student and subject relationships on at risk alert', function (): void {
    $student = User::factory()->student()->create();
    [$subject] = createSubjectQuarterLevel();

    $alert = AtRiskAlert::query()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'quarter_number' => 1,
        'grade_at_flag' => 72.50,
        'is_resolved' => false,
    ]);

    expect($alert->student)->toBeInstanceOf(User::class)
        ->and($alert->student->id)->toBe($student->id)
        ->and($alert->subject)->toBeInstanceOf(Subject::class)
        ->and($alert->subject->id)->toBe($subject->id);
});

// ====================================================================
// BossResult
// ====================================================================

it('casts boss result attributes correctly', function (): void {
    $student = User::factory()->student()->create();
    [, $quarter] = createSubjectQuarterLevel();

    $battle = BossBattle::query()->create([
        'quarter_id' => $quarter->id,
        'boss_name' => 'Dragon',
        'total_hp' => 500,
    ]);

    $result = BossResult::query()->create([
        'student_id' => $student->id,
        'boss_battle_id' => $battle->id,
        'difficulty_played' => DifficultyLevel::Hard,
        'score' => 95.50,
        'hp_dealt' => 250,
        'completed_at' => now(),
        'local_id' => 'local-br-1',
    ]);

    $result->refresh();

    expect($result->difficulty_played)->toBe(DifficultyLevel::Hard)
        ->and($result->hp_dealt)->toBeInt();
});

it('has student and boss battle relationships on boss result', function (): void {
    $student = User::factory()->student()->create();
    [, $quarter] = createSubjectQuarterLevel();

    $battle = BossBattle::query()->create([
        'quarter_id' => $quarter->id,
        'boss_name' => 'Dragon',
        'total_hp' => 500,
    ]);

    $result = BossResult::query()->create([
        'student_id' => $student->id,
        'boss_battle_id' => $battle->id,
        'difficulty_played' => DifficultyLevel::Easy,
        'score' => 80.00,
        'hp_dealt' => 100,
        'completed_at' => now(),
        'local_id' => 'local-br-2',
    ]);

    expect($result->student)->toBeInstanceOf(User::class)
        ->and($result->student->id)->toBe($student->id)
        ->and($result->bossBattle)->toBeInstanceOf(BossBattle::class)
        ->and($result->bossBattle->id)->toBe($battle->id);
});

// ====================================================================
// DifficultyAdvisory
// ====================================================================

it('casts difficulty advisory attributes correctly', function (): void {
    $student = User::factory()->student()->create();
    [$subject] = createSubjectQuarterLevel();

    $advisory = DifficultyAdvisory::query()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'current_difficulty' => 'easy',
        'suggested_difficulty' => 'standard',
        'reason' => 'High rolling average',
        'rolling_avg' => 88.50,
        'is_reviewed' => false,
    ]);

    $advisory->refresh();

    expect($advisory->is_reviewed)->toBeBool();
});

it('has student and subject relationships on difficulty advisory', function (): void {
    $student = User::factory()->student()->create();
    [$subject] = createSubjectQuarterLevel();

    $advisory = DifficultyAdvisory::query()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'current_difficulty' => 'easy',
        'suggested_difficulty' => 'hard',
        'reason' => 'Performance trend',
        'rolling_avg' => 90.00,
        'is_reviewed' => false,
    ]);

    expect($advisory->student)->toBeInstanceOf(User::class)
        ->and($advisory->student->id)->toBe($student->id)
        ->and($advisory->subject)->toBeInstanceOf(Subject::class)
        ->and($advisory->subject->id)->toBe($subject->id);
});

// ====================================================================
// Question
// ====================================================================

it('casts question attributes correctly', function (): void {
    [,, $level] = createSubjectQuarterLevel();

    $question = Question::query()->create([
        'level_id' => $level->id,
        'question_type' => QuestionType::MultipleChoice,
        'content' => ['text' => 'What is 2+2?', 'choices' => ['3', '4', '5']],
        'correct_answer' => ['4'],
        'difficulty' => 1,
        'order_index' => 1,
        'is_active' => true,
    ]);

    $question->refresh();

    expect($question->question_type)->toBe(QuestionType::MultipleChoice)
        ->and($question->content)->toBeArray()
        ->and($question->correct_answer)->toBeArray()
        ->and($question->is_active)->toBeBool()
        ->and($question->order_index)->toBeInt();
});

it('has level relationship on question', function (): void {
    [,, $level] = createSubjectQuarterLevel();

    $question = Question::query()->create([
        'level_id' => $level->id,
        'question_type' => QuestionType::TrueOrFalse,
        'content' => ['text' => 'Is 5 > 3?'],
        'correct_answer' => ['true'],
        'difficulty' => 1,
        'order_index' => 1,
        'is_active' => true,
    ]);

    expect($question->level)->toBeInstanceOf(Level::class)
        ->and($question->level->id)->toBe($level->id);
});

// ====================================================================
// ScreenTimeLog
// ====================================================================

it('casts screen time log attributes correctly', function (): void {
    $student = User::factory()->student()->create();

    $log = ScreenTimeLog::query()->create([
        'student_id' => $student->id,
        'log_date' => now()->toDateString(),
        'total_minutes' => 45,
        'levels_played' => 3,
        'last_active_at' => now(),
    ]);

    $log->refresh();

    expect($log->total_minutes)->toBeInt()
        ->and($log->levels_played)->toBeInt();
});

it('has student relationship on screen time log', function (): void {
    $student = User::factory()->student()->create();

    $log = ScreenTimeLog::query()->create([
        'student_id' => $student->id,
        'log_date' => now()->toDateString(),
        'total_minutes' => 30,
        'levels_played' => 2,
    ]);

    expect($log->student)->toBeInstanceOf(User::class)
        ->and($log->student->id)->toBe($student->id);
});

// ====================================================================
// StudentProgress
// ====================================================================

it('casts student progress attributes correctly', function (): void {
    $student = User::factory()->student()->create();
    [,, $level] = createSubjectQuarterLevel();

    $progress = StudentProgress::query()->create([
        'student_id' => $student->id,
        'level_id' => $level->id,
        'difficulty_played' => DifficultyLevel::Standard,
        'score' => 85.50,
        'stars' => 3,
        'attempts' => 1,
        'time_taken_seconds' => 120,
        'completed_at' => now(),
        'local_id' => 'local-sp-1',
    ]);

    $progress->refresh();

    expect($progress->difficulty_played)->toBe(DifficultyLevel::Standard)
        ->and($progress->stars)->toBeInt()
        ->and($progress->attempts)->toBeInt()
        ->and($progress->time_taken_seconds)->toBeInt();
});

it('has student and level relationships on student progress', function (): void {
    $student = User::factory()->student()->create();
    [,, $level] = createSubjectQuarterLevel();

    $progress = StudentProgress::query()->create([
        'student_id' => $student->id,
        'level_id' => $level->id,
        'difficulty_played' => DifficultyLevel::Easy,
        'score' => 70.00,
        'stars' => 2,
        'attempts' => 2,
        'time_taken_seconds' => 180,
        'completed_at' => now(),
        'local_id' => 'local-sp-2',
    ]);

    expect($progress->student)->toBeInstanceOf(User::class)
        ->and($progress->student->id)->toBe($student->id)
        ->and($progress->level)->toBeInstanceOf(Level::class)
        ->and($progress->level->id)->toBe($level->id);
});

// ====================================================================
// BossBattle — quarter() relationship (75% → 100%)
// ====================================================================

it('has quarter relationship on boss battle', function (): void {
    [, $quarter] = createSubjectQuarterLevel();

    $battle = BossBattle::query()->create([
        'quarter_id' => $quarter->id,
        'boss_name' => 'Phoenix',
        'total_hp' => 600,
    ]);

    expect($battle->quarter)->toBeInstanceOf(Quarter::class)
        ->and($battle->quarter->id)->toBe($quarter->id);
});

// ====================================================================
// Level — quarter() + questions() relationships (66.7% → 100%)
// ====================================================================

it('has quarter relationship on level', function (): void {
    [, $quarter, $level] = createSubjectQuarterLevel();

    expect($level->quarter)->toBeInstanceOf(Quarter::class)
        ->and($level->quarter->id)->toBe($quarter->id);
});

it('has questions relationship on level', function (): void {
    [,, $level] = createSubjectQuarterLevel();

    Question::query()->create([
        'level_id' => $level->id,
        'question_type' => QuestionType::Identification,
        'content' => ['text' => 'Name the shape'],
        'correct_answer' => ['triangle'],
        'difficulty' => 1,
        'order_index' => 1,
        'is_active' => true,
    ]);

    expect($level->questions)->toHaveCount(1)
        ->and($level->questions->first())->toBeInstanceOf(Question::class);
});

// ====================================================================
// Quarter — subject() + bossBattle() relationships (75% → 100%)
// ====================================================================

it('has subject relationship on quarter', function (): void {
    [$subject, $quarter] = createSubjectQuarterLevel();

    expect($quarter->subject)->toBeInstanceOf(Subject::class)
        ->and($quarter->subject->id)->toBe($subject->id);
});

it('has boss battle relationship on quarter', function (): void {
    [, $quarter] = createSubjectQuarterLevel();

    $battle = BossBattle::query()->create([
        'quarter_id' => $quarter->id,
        'boss_name' => 'Kraken',
        'total_hp' => 800,
    ]);

    expect($quarter->bossBattle)->toBeInstanceOf(BossBattle::class)
        ->and($quarter->bossBattle->id)->toBe($battle->id);
});

// ====================================================================
// GradeRecord — student() relationship (90% → 100%)
// ====================================================================

it('has student relationship on grade record', function (): void {
    $student = User::factory()->student()->create();
    [$subject] = createSubjectQuarterLevel();

    $record = GradeRecord::query()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'quarter_number' => 1,
        'written_work' => 85.00,
        'performance_task' => 90.00,
        'quarterly_assessment' => 88.00,
        'final_grade' => 87.67,
    ]);

    expect($record->student)->toBeInstanceOf(User::class)
        ->and($record->student->id)->toBe($student->id);
});

// ====================================================================
// StudentBadge — student() relationship (80% → 100%)
// ====================================================================

it('has student relationship on student badge', function (): void {
    $student = User::factory()->student()->create();

    $badge = Badge::query()->create([
        'name' => 'First Win',
        'description' => 'Complete first level',
        'icon' => 'trophy',
        'trigger_type' => 'level_complete',
    ]);

    $studentBadge = StudentBadge::query()->create([
        'student_id' => $student->id,
        'badge_id' => $badge->id,
        'earned_at' => now(),
    ]);

    expect($studentBadge->student)->toBeInstanceOf(User::class)
        ->and($studentBadge->student->id)->toBe($student->id);
});

// ====================================================================
// StudentDifficulty — student() relationship (85.7% → 100%)
// ====================================================================

it('has student relationship on student difficulty', function (): void {
    $student = User::factory()->student()->create();
    [$subject] = createSubjectQuarterLevel();

    $difficulty = StudentDifficulty::query()->create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'difficulty' => DifficultyLevel::Standard,
        'set_by' => DifficultySetBy::System,
    ]);

    expect($difficulty->student)->toBeInstanceOf(User::class)
        ->and($difficulty->student->id)->toBe($student->id);
});

// ====================================================================
// StudentPreference — student() relationship (90% → 100%)
// ====================================================================

it('has student relationship on student preference', function (): void {
    $student = User::factory()->student()->create();

    $pref = StudentPreference::query()->create([
        'user_id' => $student->id,
        'language' => 'en',
        'master_volume' => 80,
        'bgm_volume' => 60,
        'sfx_volume' => 70,
        'tts_enabled' => true,
        'text_size' => 'medium',
        'colorblind_mode' => false,
    ]);

    expect($pref->student)->toBeInstanceOf(User::class)
        ->and($pref->student->id)->toBe($student->id);
});

// ====================================================================
// User — classroom(), isStudent(), isTeacher(), isSuperAdmin()
// ====================================================================

it('has classroom relationship on user', function (): void {
    $classroom = Classroom::factory()->create();
    $student = User::factory()->student()->create([
        'classroom_id' => $classroom->id,
    ]);

    expect($student->classroom)->toBeInstanceOf(Classroom::class)
        ->and($student->classroom->id)->toBe($classroom->id);
});

it('returns true for isStudent when user is a student', function (): void {
    $user = User::factory()->student()->create();

    expect($user->isStudent())->toBeTrue()
        ->and($user->isTeacher())->toBeFalse()
        ->and($user->isSuperAdmin())->toBeFalse();
});

it('returns true for isTeacher when user is a teacher', function (): void {
    $user = User::factory()->teacher()->create();

    expect($user->isTeacher())->toBeTrue()
        ->and($user->isStudent())->toBeFalse()
        ->and($user->isSuperAdmin())->toBeFalse();
});

it('returns true for isSuperAdmin when user is a super admin', function (): void {
    $user = User::factory()->superAdmin()->create();

    expect($user->isSuperAdmin())->toBeTrue()
        ->and($user->isStudent())->toBeFalse()
        ->and($user->isTeacher())->toBeFalse();
});
