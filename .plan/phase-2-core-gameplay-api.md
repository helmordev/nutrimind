# Phase 2 — Core Gameplay API

> **Milestone:** 25% | **Timeline:** Weeks 1-2
> **Goal:** Unity client can fetch questions, submit level results, and track progress per student.

---

## Prerequisites

- Phase 1 complete (10% milestone passed)
- All migrations and seeders running clean
- Student authentication working end-to-end
- Postman collection current

---

## Week 1 — Content Delivery & Question Serving

### Task 1: Eloquent Models & Relationships

- [ ] Finalize all model relationships:
  - `User` hasOne `StudentProfile`, hasOne `StudentPreference`, hasMany `StudentDifficulty`
  - `Subject` hasMany `Quarter`
  - `Quarter` hasMany `Level`, hasOne `BossBattle`, belongsTo `Subject`
  - `Level` hasMany `Question`, belongsTo `Quarter`
  - `Question` belongsToMany `BossBattle` (via boss_questions pivot)
  - `BossBattle` belongsTo `Quarter`, belongsToMany `Question`
  - `User (student)` hasMany `StudentProgress`, hasMany `BossResult`, belongsToMany `Badge`
- [ ] Add scope methods where useful: `User::scopeStudents()`, `User::scopeTeachers()`, `Question::scopeActive()`
- [ ] Verify all `$fillable` arrays are set correctly on every model

**Deliverable:** All models have proper relationships and mass assignment protection.

### Task 2: World Controller Enhancement

- [ ] Enhance `WorldController@index` to include:
  - Per-quarter: levels with student's completion status (stars, score) if progress exists
  - Per-quarter: boss battle availability (all 4 levels completed?)
  - Per-subject: student's current difficulty setting
  - MATATAG competency code and description per level
- [ ] Create/update API Resources:
  - `WorldResource` — subject with nested quarters
  - `QuarterResource` — quarter with nested levels + boss info
  - `LevelResource` — level with student progress overlay
- [ ] Return `is_accessible` flag per level based on unlock logic (Section 6.6)

**Deliverable:** `/api/v1/student/worlds` returns the full game state map for Unity's World Screen.

### Task 3: Level Controller — Questions Endpoint

- [ ] Create `Student\LevelController.php` with `questions()` method
- [ ] Implement difficulty-filtered question serving (per Section 6.2):
  - Look up student's difficulty setting for the level's subject
  - Map: `DifficultyLevel::Easy` -> D1 only, `DifficultyLevel::Standard` -> D1+D2, `DifficultyLevel::Hard` -> D1+D2+D3
  - Filter `questions` by difficulty tiers, `is_active = true`
  - Randomize order
- [ ] Create `QuestionResource.php`:
  - Return `id`, `question_type`, `content` (bilingual JSON), `difficulty`, `order_index`
  - **Do NOT return `correct_answer` in this resource** (sent separately after submission, or handled client-side)
- [ ] Register route: `GET /api/v1/student/levels/{level}/questions` (student middleware)
- [ ] Validate that the level is accessible to the student (unlock check)

**Deliverable:** Student fetches questions filtered by their difficulty setting.

### Task 4: Sample Question Seeder

- [ ] Create `QuestionSeeder.php` — seed 3-5 sample questions per level for at least Q1 of each subject (12 levels)
- [ ] Include all 3 difficulty tiers (1, 2, 3) across the samples
- [ ] Use bilingual `content` JSON structure (en + fil) per Section 4 of Server Plan
- [ ] Include at least one of each `question_type`: multiple_choice, fill_blank, matching, drag_drop
- [ ] Add to `DatabaseSeeder.php` after existing seeders

**Deliverable:** Database has sample questions for testing the questions endpoint.

---

## Week 2 — Progress Submission & Tracking

### Task 5: Progress Controller — Level Submission

- [ ] Create `Student\ProgressController.php` with `submitLevel()` method
- [ ] Create `SubmitLevelRequest.php` — validate:
  - `level_id` (required, exists in levels)
  - `score` (required, numeric, 0-100)
  - `stars` (required, integer, 0-3)
  - `attempts` (required, integer, min 1)
  - `time_taken_seconds` (required, integer, min 1)
  - `difficulty_played` (required, in: easy, standard, hard — validated against `DifficultyLevel` enum)
  - `local_id` (required, string, unique in student_progress)
  - `completed_at` (required, ISO 8601 datetime)
- [ ] On submission:
  - Create/update `StudentProgress` record (upsert by student_id + level_id, keeping best score)
  - Record `difficulty_played` for analytics
  - Deduplicate by `local_id` — if exists, return success without re-processing
- [ ] Register route: `POST /api/v1/student/progress/level` (student middleware)
- [ ] Return: updated progress record + any newly earned badges (placeholder — badge logic in Phase 3)

**Deliverable:** Student can submit level completion results.

### Task 6: ResolveUnlockStatus Action — Level Accessibility

- [ ] Create `app/Actions/ResolveUnlockStatus.php` with `handle()` method:
  - `isLevelAccessible(User $student, Level $level): bool`
    - Check `quarters.current_unlock_week >= levels.unlock_week`
    - Check previous level in quarter is completed (stars >= 1)
    - First level in quarter requires no previous completion
  - `isBossAccessible(User $student, BossBattle $boss): bool`
    - Check all 4 levels in the quarter are completed
  - `isQuarterAccessible(User $student, Quarter $quarter): bool`
    - Q1 always accessible
    - Q2+ requires boss results for ALL 3 subjects in the previous quarter
- [ ] Integrate into `WorldController` — set `is_accessible` flags
- [ ] Integrate into `LevelController` — reject question requests for inaccessible levels (403)

**Deliverable:** Level unlock logic enforced on both world map display and question fetching.

### Task 7: Student Badges Endpoint

- [ ] Create `Student\BadgeController.php` with `index()` method
- [ ] Return all 6 badge types with earned/unearned status per student:
  - Badge name, description, icon identifier
  - `earned`: boolean
  - `earned_at`: timestamp or null
- [ ] Create `BadgeResource.php`
- [ ] Register route: `GET /api/v1/student/badges` (student middleware)

**Deliverable:** Student can view all badges with their earned status.

### Task 8: Student Grades Endpoint

- [ ] Create `Student\GradeController.php` with `index()` method (read-only for student)
- [ ] Return grade records per subject per quarter:
  - written_work, performance_task, quarterly_assessment, final_grade
  - `computed_at` timestamp
  - Return null for quarters not yet computed
- [ ] Create `GradeResource.php`
- [ ] Register route: `GET /api/v1/student/grades` (student middleware)

**Deliverable:** Student can view their grade summary (empty until boss battles trigger computation in Phase 3).

### Task 9: Update Sync State Endpoint

- [ ] Update `SyncController@state` to include:
  - `worlds` — enhanced world data from Task 2
  - `progress` — all student_progress records
  - `boss_results` — all boss_result records (empty until Phase 3)
  - `badges` — earned badges
  - `grades` — grade records
  - `preferences` — student preferences
  - `difficulties` — per-subject difficulty settings
  - `screen_time` — current limits (from global default)
  - `server_timestamp` — for client cache invalidation
- [ ] Ensure response is a single cohesive JSON payload

**Deliverable:** Sync state endpoint returns the complete game state for offline caching.

### Task 10: Update Postman Collection

- [ ] Add all new endpoints to Postman collection:
  - `GET /api/v1/student/levels/{level}/questions`
  - `POST /api/v1/student/progress/level`
  - `GET /api/v1/student/badges`
  - `GET /api/v1/student/grades`
- [ ] Update `GET /api/v1/student/worlds` with enhanced response schema
- [ ] Update `GET /api/v1/student/sync/state` with full payload schema
- [ ] Add test scripts to verify response structure

**Deliverable:** Postman collection reflects all Phase 2 endpoints.

---

## Verification Checklist

Before marking Phase 2 as **25% complete**, all of the following must pass:

- [ ] `GET /api/v1/student/worlds` returns 3 subjects with quarters, levels, and accessibility flags
- [ ] `GET /api/v1/student/levels/{level}/questions` returns difficulty-filtered questions
- [ ] Questions for `easy` difficulty only include D1; `standard` includes D1+D2; `hard` includes D1+D2+D3
- [ ] `correct_answer` is NOT exposed in the questions response
- [ ] `POST /api/v1/student/progress/level` accepts and stores level results
- [ ] Duplicate `local_id` submissions return success without creating duplicate records
- [ ] Level unlock logic works: inaccessible levels return 403 when requesting questions
- [ ] Boss shows as inaccessible until all 4 levels in the quarter are completed
- [ ] `GET /api/v1/student/badges` returns all 6 badges with earned status
- [ ] `GET /api/v1/student/grades` returns grade structure (empty values are OK at this phase)
- [ ] `GET /api/v1/student/sync/state` returns the complete game state
- [ ] All Phase 1 functionality still works (no regressions)
- [ ] Postman collection updated with all new endpoints

---

## Files Created/Modified in Phase 2

```
app/Http/Controllers/Student/LevelController.php
app/Http/Controllers/Student/ProgressController.php
app/Http/Controllers/Student/BadgeController.php
app/Http/Controllers/Student/GradeController.php
app/Http/Requests/Student/SubmitLevelRequest.php
app/Http/Resources/WorldResource.php (enhanced)
app/Http/Resources/QuarterResource.php (enhanced)
app/Http/Resources/LevelResource.php
app/Http/Resources/QuestionResource.php
app/Http/Resources/BadgeResource.php
app/Http/Resources/GradeResource.php
app/Actions/ResolveUnlockStatus.php
app/Models/*.php (relationship updates)
database/seeders/QuestionSeeder.php
routes/api.php (updated)
docs/postman/NutriMind_API_v1.json (updated)
```

---

*Phase 2 -> Phase 3: Once the 25% milestone passes, proceed to [Phase 3 — Boss Battles, Grades & Badges](./phase-3-boss-grades-badges.md)*
