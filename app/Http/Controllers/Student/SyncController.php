<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Enums\ScreenTimeScope;
use App\Http\Resources\WorldResource;
use App\Models\ScreenTimeSetting;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SyncController
{
    public function state(Request $request): JsonResponse
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
            ->with([
                'studentDifficulties' => fn ($query) => $query
                    ->where('student_id', $student->id),
                'quarters' => fn ($query) => $query
                    ->orderBy('quarter_number')
                    ->with([
                        'levels' => fn ($levelQuery) => $levelQuery->orderBy('level_number'),
                    ]),
            ])
            ->orderBy('name')
            ->get();

        $screenTime = ScreenTimeSetting::query()
            ->where('scope', ScreenTimeScope::Global)
            ->whereNull('scope_id')
            ->first();

        return response()->json([
            'worlds' => WorldResource::collection($worlds)->response()->getData(true)['data'],
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
                ->sortBy(fn ($difficulty) => $difficulty->subject?->name)
                ->values()
                ->map(fn ($difficulty): array => [
                    'subject_id' => $difficulty->subject_id,
                    'subject_name' => $difficulty->subject?->name,
                    'difficulty' => $difficulty->difficulty->value,
                    'set_by' => $difficulty->set_by->value,
                    'updated_at_by_teacher' => $difficulty->updated_at_by_teacher?->toISOString(),
                ])
                ->all(),
            'screen_time' => [
                'scope' => $screenTime?->scope->value ?? ScreenTimeScope::Global->value,
                'school_day_limit_min' => $screenTime?->school_day_limit_min ?? 45,
                'weekend_limit_min' => $screenTime?->weekend_limit_min ?? 60,
                'max_levels_school' => $screenTime?->max_levels_school ?? 2,
                'max_levels_weekend' => $screenTime?->max_levels_weekend ?? 3,
                'play_start_school' => $screenTime?->play_start_school ?? '15:00:00',
                'play_end_school' => $screenTime?->play_end_school ?? '20:00:00',
                'play_start_weekend' => $screenTime?->play_start_weekend ?? '08:00:00',
                'play_end_weekend' => $screenTime?->play_end_weekend ?? '20:00:00',
            ],
            'badges' => $student->studentBadges
                ->sortBy('earned_at')
                ->values()
                ->map(fn ($studentBadge): array => [
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
                    fn ($gradeRecord) => $gradeRecord->subject?->name,
                    fn ($gradeRecord) => $gradeRecord->quarter_number,
                ])
                ->values()
                ->map(fn ($gradeRecord): array => [
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
