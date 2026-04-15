<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Enums\ScreenTimeScope;
use App\Http\Resources\WorldResource;
use App\Models\GradeRecord;
use App\Models\ScreenTimeSetting;
use App\Models\StudentBadge;
use App\Models\StudentDifficulty;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SyncController
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $student */
        $student = $request->user();

        $student->load([
            'studentPreferences',
            'studentDifficulties.subject',
            'studentBadges.badge',
            'gradeRecords.subject',
        ]);

        $worlds = Subject::query()
            ->where('grade', $student->grade)
            ->with([ // @phpstan-ignore argument.type
                'studentDifficulties' => fn (HasMany $query): HasMany => $query
                    ->where('student_id', $student->id),
                'quarters' => fn (HasMany $query): HasMany => $query
                    ->orderBy('quarter_number')
                    ->with([ // @phpstan-ignore argument.type
                        'levels' => fn (HasMany $levelQuery): HasMany => $levelQuery->orderBy('level_number'),
                    ]),
            ])
            ->orderBy('name')
            ->get();

        $screenTime = ScreenTimeSetting::query()
            ->where('scope', ScreenTimeScope::Global)
            ->whereNull('scope_id')
            ->first();

        /** @var array{data: array<int, mixed>} $worldsData */
        $worldsData = WorldResource::collection($worlds)->response()->getData(true);

        return response()->json([
            'worlds' => $worldsData['data'],
            'preferences' => [
                'language' => $student->studentPreferences?->language->value,
                'master_volume' => $student->studentPreferences?->master_volume,
                'bgm_volume' => $student->studentPreferences?->bgm_volume,
                'sfx_volume' => $student->studentPreferences?->sfx_volume,
                'tts_enabled' => $student->studentPreferences?->tts_enabled,
                'text_size' => $student->studentPreferences?->text_size->value,
                'colorblind_mode' => $student->studentPreferences?->colorblind_mode,
            ],
            'difficulties' => $student->studentDifficulties
                ->sortBy(fn (StudentDifficulty $difficulty): ?string => $difficulty->subject?->name)
                ->values()
                ->map(fn (StudentDifficulty $difficulty): array => [
                    'subject_id' => $difficulty->subject_id,
                    'subject_name' => $difficulty->subject?->name,
                    'difficulty' => $difficulty->difficulty->value,
                    'set_by' => $difficulty->set_by->value,
                    'updated_at_by_teacher' => $difficulty->updated_at_by_teacher?->toISOString(),
                ])
                ->all(),
            'screen_time' => [
                'scope' => $screenTime?->scope->value ?? ScreenTimeScope::Global->value,
                'school_day_limit_min' => $screenTime?->school_day_limit_min ?? 45, // @phpstan-ignore nullsafe.neverNull
                'weekend_limit_min' => $screenTime?->weekend_limit_min ?? 60, // @phpstan-ignore nullsafe.neverNull
                'max_levels_school' => $screenTime?->max_levels_school ?? 2, // @phpstan-ignore nullsafe.neverNull
                'max_levels_weekend' => $screenTime?->max_levels_weekend ?? 3, // @phpstan-ignore nullsafe.neverNull
                'play_start_school' => $screenTime?->play_start_school ?? '15:00:00', // @phpstan-ignore nullsafe.neverNull
                'play_end_school' => $screenTime?->play_end_school ?? '20:00:00', // @phpstan-ignore nullsafe.neverNull
                'play_start_weekend' => $screenTime?->play_start_weekend ?? '08:00:00', // @phpstan-ignore nullsafe.neverNull
                'play_end_weekend' => $screenTime?->play_end_weekend ?? '20:00:00', // @phpstan-ignore nullsafe.neverNull
            ],
            'badges' => $student->studentBadges
                ->sortBy('earned_at')
                ->values()
                ->map(fn (StudentBadge $studentBadge): array => [
                    'badge_id' => $studentBadge->badge_id,
                    'name' => $studentBadge->badge?->name,
                    'description' => $studentBadge->badge?->description,
                    'icon' => $studentBadge->badge?->icon,
                    'trigger_type' => $studentBadge->badge?->trigger_type,
                    'earned_at' => $studentBadge->earned_at->toISOString(),
                ])
                ->all(),
            'grades' => $student->gradeRecords
                ->sortBy([
                    fn (GradeRecord $gradeRecord): ?string => $gradeRecord->subject?->name,
                    fn (GradeRecord $gradeRecord): int => $gradeRecord->quarter_number,
                ])
                ->values()
                ->map(fn (GradeRecord $gradeRecord): array => [
                    'subject_id' => $gradeRecord->subject_id,
                    'subject_name' => $gradeRecord->subject?->name,
                    'quarter_number' => $gradeRecord->quarter_number,
                    'written_work' => $gradeRecord->written_work,
                    'performance_task' => $gradeRecord->performance_task,
                    'quarterly_assessment' => $gradeRecord->quarterly_assessment,
                    'final_grade' => $gradeRecord->final_grade,
                    'computed_at' => $gradeRecord->computed_at?->toISOString(),
                ])
                ->all(),
        ]);
    }
}
