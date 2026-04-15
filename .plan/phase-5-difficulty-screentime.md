# Phase 5 — Adaptive Difficulty & Screen Time System

> **Milestone:** 65% | **Timeline:** Weeks 6-7
> **Goal:** Teacher-controlled adaptive difficulty is fully operational with auto-suggest advisories, and the three-tier screen time system enforces play limits with heartbeat tracking.

---

## Prerequisites

- Phase 4 complete (50% milestone passed)
- Preferences, bilingual content, and sync/offline all functional
- `ProcessSync` action processes mixed records reliably; deduplication proven
- All progress submission endpoints working with `difficulty_played` field

---

## Week 6 — Adaptive Difficulty

### Task 1: EvaluateDifficultyAdvisory Action — Core Logic

- [ ] Create `app/Actions/EvaluateDifficultyAdvisory.php`
- [ ] Implement `handle(int $studentId, int $subjectId, int $quarterNumber): void`
  - Query last 3 `StudentProgress` records for the student + subject + quarter (by `completed_at` descending)
  - If fewer than 3 records exist, return early (insufficient data)
  - Compute rolling average of the 3 scores
  - Read current difficulty from `student_difficulties`
  - If avg >= 90% and current != `DifficultyLevel::Hard`: suggest one step up (easy->standard, standard->hard)
  - If avg < 60% and current != `DifficultyLevel::Easy`: suggest one step down (hard->standard, standard->easy)
  - `updateOrCreate` a `DifficultyAdvisory` row with `is_reviewed = false`
- [ ] Implement `getCurrentDifficulty(int $studentId, int $subjectId): string`
  - Returns the student's active difficulty for a subject (defaults to `DifficultyLevel::Standard` if no row exists)

**Deliverable:** Action can evaluate student performance and generate advisory suggestions.

### Task 2: EvaluateDifficultyAdvisoryJob

- [ ] Create `app/Jobs/EvaluateDifficultyAdvisoryJob.php`
- [ ] Accept `studentId`, `subjectId`, `quarterNumber` via constructor
- [ ] In `handle()`: call `EvaluateDifficultyAdvisory::handle()`
- [ ] Dispatch this job from `BossController@submit` after every boss battle submission
- [ ] Also dispatch from `ProgressController@submit` after every level submission
- [ ] Verify job processes correctly via `database` queue driver (`php artisan queue:work`)

**Deliverable:** Advisory evaluation runs asynchronously after every progress submission.

### Task 3: Teacher DifficultyController — Per-Student & Class-Wide

- [ ] Create `app/Http/Controllers/Teacher/DifficultyController.php`
- [ ] `index()` — list all students in teacher's class with their current difficulty per subject + any pending (unreviewed) advisories
- [ ] `updateStudent(SetDifficultyRequest $request, User $student)` — set difficulty for one student per subject:
  - Validate student belongs to this teacher
  - Update `student_difficulties` row: set `difficulty`, `set_by = DifficultySetBy::Teacher`, `updated_at_by_teacher = now()`
  - Return updated difficulty record
- [ ] `updateClass(Request $request)` — set difficulty for ALL students in teacher's class for a given subject:
  - Validate subject_id and difficulty value
  - Batch update all `student_difficulties` rows for students where `teacher_id = auth()->id()`
  - Set `set_by = DifficultySetBy::Teacher`, `updated_at_by_teacher = now()`
  - Return count of students updated
- [ ] `reviewAdvisory(int $advisoryId, Request $request)` — accept or dismiss:
  - If accepted: update `student_difficulties` to the `suggested_difficulty`; set `set_by = DifficultySetBy::Teacher`
  - Mark advisory as `is_reviewed = true`, `reviewed_at = now()`
  - Return updated advisory
- [ ] Register routes (teacher middleware):
  - `GET /teacher/difficulty`
  - `PUT /teacher/difficulty/student/{student}`
  - `PUT /teacher/difficulty/class`
  - `POST /teacher/difficulty/advisories/{id}/review`

**Deliverable:** Teacher can view, set, and manage difficulty for individual students and the whole class.

### Task 4: Difficulty Form Requests

- [ ] Create `app/Http/Requests/Teacher/SetDifficultyRequest.php`:
  - `subject_id` (required, exists:subjects,id)
  - `difficulty` (required, in: easy, standard, hard — validated against `DifficultyLevel` enum)
- [ ] Verify `SetDifficultyRequest` is used in `updateStudent()` and `updateClass()`
- [ ] Validate advisory review request: `action` (required, in: accept, dismiss)

**Deliverable:** All difficulty-related inputs are validated before processing.

### Task 5: Difficulty in Sync State & Question Filtering

- [ ] Ensure `GET /api/v1/student/sync/state` includes the `difficulties` array:
  ```json
  "difficulties": [
    { "subject_id": 1, "subject_name": "English", "difficulty": "standard" },
    { "subject_id": 2, "subject_name": "Science", "difficulty": "hard" },
    { "subject_id": 3, "subject_name": "Health+PE", "difficulty": "easy" }
  ]
  ```
- [ ] Verify `LevelController@questions` filters questions by difficulty correctly:
  - `easy` -> difficulty tier 1 only
  - `standard` -> difficulty tiers 1 and 2
  - `hard` -> difficulty tiers 1, 2, and 3
- [ ] Verify boss battle question pools also respect the same difficulty filter
- [ ] Test: teacher changes difficulty from `standard` to `hard` -> student's next question request returns tier 3 questions

**Deliverable:** Difficulty settings flow from teacher dashboard through API to Unity question serving.

---

## Week 7 — Screen Time System

### Task 6: ScreenTimeService — Three-Tier Resolution

- [ ] Create `app/Services/ScreenTimeService.php`
- [ ] Implement `getEffectiveSettings(User $student): ScreenTimeSetting`
  - Resolution order: `ScreenTimeScope::Student` (scope_id = student.id) -> `ScreenTimeScope::ClassScope` (scope_id = teacher.id) -> `ScreenTimeScope::Global`
  - Return the first matching `ScreenTimeSetting` row
- [ ] Implement `getStatus(User $student): array`
  - Determine if today is a school day or weekend (Mon-Fri = school, Sat-Sun = weekend)
  - Get effective settings via `getEffectiveSettings()`
  - Get or create today's `screen_time_logs` row
  - Calculate:
    - `in_window`: current Manila time is within `play_start` and `play_end`
    - `remaining_minutes`: `daily_limit - total_minutes` (min 0)
    - `remaining_levels`: `max_levels - levels_played` (min 0)
    - `time_used_today`: `total_minutes` from log
    - `daily_limit`: from effective settings
    - `levels_used_today`: `levels_played` from log
    - `levels_limit`: from effective settings
    - `warning_flag`: remaining_minutes <= 15 AND remaining_minutes > 0
    - `blocked`: remaining_minutes <= 0 OR remaining_levels <= 0 OR NOT in_window
    - `play_window_end`: from effective settings
    - `is_weekend`: boolean
  - Return all values as an array
- [ ] Implement `recordHeartbeat(User $student, int $minutesToAdd = 1): array`
  - Upsert today's `screen_time_logs`: increment `total_minutes`, update `last_active_at`
  - Return fresh status via `getStatus()`

**Deliverable:** Screen time resolution, status computation, and heartbeat recording work correctly.

### Task 7: Student ScreenTimeController

- [ ] Create `app/Http/Controllers/Student/ScreenTimeController.php`
- [ ] `status()` — calls `ScreenTimeService::getStatus()`, returns full heartbeat response JSON:
  ```json
  {
    "in_window": true,
    "remaining_minutes": 28,
    "remaining_levels": 1,
    "time_used_today": 17,
    "daily_limit": 45,
    "levels_used_today": 1,
    "levels_limit": 2,
    "warning_flag": false,
    "blocked": false,
    "play_window_end": "20:00",
    "is_weekend": false
  }
  ```
- [ ] `heartbeat()` — calls `ScreenTimeService::recordHeartbeat()`, returns fresh status
  - Also increment `levels_played` if request includes `level_completed = true`
- [ ] `history()` — returns last 7 days of `screen_time_logs` for the authenticated student
- [ ] Register routes (student middleware):
  - `GET /api/v1/student/screentime/status`
  - `POST /api/v1/student/screentime/heartbeat`
  - `GET /api/v1/student/screentime/history`

**Deliverable:** Unity client can check screen time status, send heartbeats, and view history.

### Task 8: Teacher ScreenTimeController

- [ ] Create `app/Http/Controllers/Teacher/ScreenTimeController.php`
- [ ] `classOverview()` — returns all students in teacher's class with:
  - Today's usage (minutes used, levels played)
  - Their effective settings (student override or class default — indicate which)
  - Whether they are currently blocked
- [ ] `studentDetail(User $student)` — returns individual student's screen time:
  - Last 7 days of `screen_time_logs`
  - Their effective settings + whether it's student-scoped or class-scoped
- [ ] `updateClass(UpdateScreenTimeRequest $request)` — upsert `ScreenTimeScope::ClassScope`-scoped setting:
  - `scope = ScreenTimeScope::ClassScope`, `scope_id = auth()->id()`
  - Validate all fields (limits, play windows)
- [ ] `updateStudent(UpdateScreenTimeRequest $request, User $student)` — upsert `ScreenTimeScope::Student`-scoped setting:
  - `scope = ScreenTimeScope::Student`, `scope_id = $student->id`
  - Validate student belongs to this teacher
- [ ] `removeOverride(User $student)` — delete `ScreenTimeScope::Student`-scoped row:
  - Student reverts to class setting automatically
- [ ] Create `app/Http/Requests/Teacher/UpdateScreenTimeRequest.php`:
  - `school_day_limit_min` (required, integer, 10-120)
  - `weekend_limit_min` (required, integer, 10-180)
  - `max_levels_school` (required, integer, 1-10)
  - `max_levels_weekend` (required, integer, 1-10)
  - `play_start_school` (required, time format H:i)
  - `play_end_school` (required, time format H:i, after play_start_school)
  - `play_start_weekend` (required, time format H:i)
  - `play_end_weekend` (required, time format H:i, after play_start_weekend)
- [ ] Register routes (teacher middleware):
  - `GET /teacher/screentime/class`
  - `GET /teacher/screentime/{student}`
  - `PUT /teacher/screentime/class`
  - `PUT /teacher/screentime/student/{student}`
  - `DELETE /teacher/screentime/student/{student}/override`

**Deliverable:** Teacher can view and manage screen time for the entire class and individual students.

### Task 9: Admin Global Settings Controller

- [ ] Create/update `app/Http/Controllers/Admin/SettingsController.php`
- [ ] `screentimeSettings()` — show current global screen time defaults
- [ ] `updateScreentime(Request $request)` — update the `ScreenTimeScope::Global` row in `screen_time_settings`
- [ ] `difficultySettings()` — show current global default difficulty
- [ ] `updateDifficulty(Request $request)` — update default difficulty for all new students
- [ ] Register routes (super_admin middleware):
  - `GET /admin/settings/screentime`
  - `PUT /admin/settings/screentime`
  - `GET /admin/settings/difficulty`
  - `PUT /admin/settings/difficulty`

**Deliverable:** Super admin can adjust global defaults that cascade to all classes.

### Task 10: At-Risk Alert Artisan Command

- [ ] Create `app/Console/Commands/CheckAtRiskStudents.php`
- [ ] Register in scheduler: `$schedule->command('nutrimind:check-at-risk')->dailyAt('00:00')`
- [ ] Logic:
  - Scan all `grade_records` where `final_grade < 75`
  - For each: `updateOrCreate` an `at_risk_alerts` row with `is_resolved = false`
  - For existing alerts where the student's `final_grade` has risen to >= 75: set `is_resolved = true`, `resolved_at = now()`
- [ ] Create `app/Http/Controllers/Teacher/AlertController.php`:
  - `index()` — list unresolved at-risk alerts for this teacher's students
  - `resolve(AtRiskAlert $alert)` — manually mark as resolved
- [ ] Register routes (teacher middleware):
  - `GET /teacher/alerts/at-risk`
  - `POST /teacher/alerts/{alert}/resolve`

**Deliverable:** At-risk students are flagged daily and teachers can view/resolve alerts.

### Task 11: Screen Time in Sync State

- [ ] Update `SyncController@state` to include screen time data:
  ```json
  "screen_time": {
    "limits": {
      "school_day_limit_min": 45,
      "weekend_limit_min": 60,
      "max_levels_school": 2,
      "max_levels_weekend": 3,
      "play_start_school": "15:00",
      "play_end_school": "20:00",
      "play_start_weekend": "08:00",
      "play_end_weekend": "20:00"
    },
    "today": {
      "total_minutes": 23,
      "levels_played": 1,
      "remaining_minutes": 22,
      "remaining_levels": 1,
      "in_window": true,
      "blocked": false,
      "is_weekend": false
    }
  }
  ```
- [ ] Ensure limits are resolved via `ScreenTimeService::getEffectiveSettings()` (three-tier)
- [ ] Ensure today's status is computed via `ScreenTimeService::getStatus()`

**Deliverable:** Unity receives complete screen time state on app open via sync endpoint.

### Task 12: Rate Limiting for Screen Time & Auth

- [ ] Apply rate limiting to `POST /api/v1/auth/login` — 5 per minute per IP
- [ ] Apply rate limiting to `POST /api/v1/student/screentime/heartbeat` — 12 per minute per user
- [ ] Apply rate limiting to `POST /api/v1/student/progress/*` — 30 per minute per user
- [ ] Apply rate limiting to web login routes — 5 per minute per IP
- [ ] Configure in `app/Providers/AppServiceProvider.php` or `RouteServiceProvider` using `RateLimiter::for()`

**Deliverable:** All endpoints respect rate limits defined in the security spec.

### Task 13: Update Postman Collection

- [ ] Add `GET /api/v1/student/screentime/status` with sample response
- [ ] Add `POST /api/v1/student/screentime/heartbeat` with sample request/response
- [ ] Add `GET /api/v1/student/screentime/history` with sample response
- [ ] Add all teacher difficulty endpoints with sample payloads
- [ ] Add all teacher screen time endpoints with sample payloads
- [ ] Add admin settings endpoints
- [ ] Add at-risk alert endpoints
- [ ] Update `GET /api/v1/student/sync/state` with screen time + difficulty fields
- [ ] Add test scripts to validate three-tier screen time resolution

**Deliverable:** Postman collection covers the complete difficulty and screen time workflows.

---

## Verification Checklist

Before marking Phase 5 as **65% complete**, all of the following must pass:

- [ ] `EvaluateDifficultyAdvisory::handle()` generates advisories when rolling avg >= 90% or < 60%
- [ ] `EvaluateDifficultyAdvisoryJob` dispatches and processes after level and boss submissions
- [ ] Teacher can set difficulty per student per subject via `PUT /teacher/difficulty/student/{student}`
- [ ] Teacher can set difficulty for all students in class via `PUT /teacher/difficulty/class`
- [ ] Teacher can accept an advisory — student's difficulty changes to the suggested value
- [ ] Teacher can dismiss an advisory — no difficulty change; advisory marked reviewed
- [ ] Questions filter correctly: easy=tier1, standard=tier1+2, hard=tier1+2+3
- [ ] Boss question pools also respect difficulty filtering
- [ ] `ScreenTimeService::getEffectiveSettings()` resolves student > class > global correctly
- [ ] Heartbeat increments `total_minutes` and returns full status JSON
- [ ] `warning_flag` is true when remaining_minutes <= 15 (and > 0)
- [ ] `blocked` is true when time or levels exhausted or outside play window
- [ ] Teacher can set class-wide screen time limits
- [ ] Teacher can set per-student override that supersedes class setting
- [ ] Teacher can remove per-student override — student reverts to class setting
- [ ] Super admin can update global screen time and difficulty defaults
- [ ] `nutrimind:check-at-risk` command flags students with final_grade < 75
- [ ] At-risk alerts auto-resolve when grade rises to >= 75
- [ ] Rate limiting enforced on login (5/min/IP), heartbeat (12/min/user), progress (30/min/user)
- [ ] Sync state includes complete `difficulties` array and `screen_time` object
- [ ] All previous phase functionality still works (no regressions)

---

## Files Created/Modified in Phase 5

```
app/Actions/EvaluateDifficultyAdvisory.php
app/Services/ScreenTimeService.php
app/Jobs/EvaluateDifficultyAdvisoryJob.php
app/Http/Controllers/Student/ScreenTimeController.php
app/Http/Controllers/Teacher/DifficultyController.php
app/Http/Controllers/Teacher/ScreenTimeController.php
app/Http/Controllers/Teacher/AlertController.php
app/Http/Controllers/Admin/SettingsController.php
app/Http/Requests/Teacher/SetDifficultyRequest.php
app/Http/Requests/Teacher/UpdateScreenTimeRequest.php
app/Console/Commands/CheckAtRiskStudents.php
app/Http/Controllers/Student/SyncController.php (enhanced — screen time + difficulty in state)
app/Http/Controllers/Student/ProgressController.php (enhanced — dispatches difficulty job)
app/Http/Controllers/Student/BossController.php (enhanced — dispatches difficulty job)
app/Providers/AppServiceProvider.php (rate limiting)
routes/api.php (updated)
routes/web.php (updated)
docs/postman/NutriMind_API_v1.json (updated)
```

---

*Phase 5 -> Phase 6: Once the 65% milestone passes, proceed to [Phase 6 — Teacher Dashboard + Admin Dashboard](./phase-6-dashboards.md)*
