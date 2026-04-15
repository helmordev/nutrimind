# Phase 3 — Boss Battles, Grade Computation & Badges

> **Milestone:** 35% | **Timeline:** Week 3
> **Goal:** Boss battle submission triggers DepEd grade computation; badges are awarded automatically on qualifying actions.

---

## Prerequisites

- Phase 2 complete (25% milestone passed)
- Level progress submission working
- Unlock logic enforced
- Sample questions seeded

---

### Task 1: Boss Controller — Boss Battle Submission

- [ ] Create `Student\BossController.php` with `submit()` method
- [ ] Create `SubmitBossRequest.php` — validate:
  - `boss_battle_id` (required, exists in boss_battles)
  - `score` (required, numeric, 0-100)
  - `hp_dealt` (required, integer, 0-500)
  - `difficulty_played` (required, in: easy, standard, hard — validated against `DifficultyLevel` enum)
  - `local_id` (required, string, unique in boss_results)
  - `completed_at` (required, ISO 8601 datetime)
- [ ] Verify boss is accessible (all 4 levels in quarter completed)
- [ ] Create/update `BossResult` record (upsert by student_id + boss_battle_id, keeping best score)
- [ ] Deduplicate by `local_id`
- [ ] After successful submission, dispatch:
  - `ComputeGradeJob` — computes the quarter grade
  - `EvaluateDifficultyAdvisoryJob` — evaluates difficulty advisory
- [ ] Register route: `POST /api/v1/student/progress/boss` (student middleware)
- [ ] Return: boss result + computed grade (if available) + any newly earned badges

**Deliverable:** Student can submit boss battle results; grade computation is triggered automatically.

### Task 2: ComputeGrade Action

- [ ] Create `app/Actions/ComputeGrade.php` with `handle()` method
- [ ] Implement DepEd 25/50/25 formula (Section 6.5 of Server Plan):
  - **Written Work (25%)** = average of all level scores for student + subject + quarter
  - **Performance Task (50%)** = `(levels_completed / 4) x 100 x attempt_quality_factor`
    - Attempt quality: 1 attempt = 1.00, 2 attempts = 0.95, 3+ attempts = 0.90
  - **Quarterly Assessment (25%)** = boss battle score for this quarter
  - **Final Grade** = `(Written x 0.25) + (Performance x 0.50) + (Assessment x 0.25)`
- [ ] Upsert `GradeRecord` with all component scores and final grade
- [ ] Set `computed_at` timestamp
- [ ] After computation, check if final grade < 75 and create/update `AtRiskAlert` if needed

**Deliverable:** DepEd-compliant grade computation runs after every boss submission.

### Task 3: ComputeGradeJob

- [ ] Create `ComputeGradeJob.php` — queued job
- [ ] Accepts: `studentId`, `subjectId`, `quarterNumber`
- [ ] Calls `ComputeGrade::handle()`
- [ ] Handle edge cases:
  - Not all 4 levels completed yet (partial written work average)
  - Boss result not yet submitted (quarterly assessment = null, skip final grade)
  - Multiple boss attempts (use best score)
- [ ] Log computation results for debugging

**Deliverable:** Grade computation runs asynchronously via queue.

### Task 4: AwardBadge Action

- [ ] Create `app/Actions/AwardBadge.php` with `handle()` method
- [ ] Implement all 6 badge triggers (Section 6.8 of Server Plan):
  - **First Boss Defeat** — awarded on student's first-ever `BossResult` creation
  - **Three-Star Level** — awarded when `score >= 90` on any level submission
  - **Quarter Complete** — awarded when all 4 levels + boss done in a quarter
  - **Full World Complete** — awarded when all 4 quarters done in one subject world
  - **3-Day Streak** — awarded when student has activity on 3 consecutive calendar days (check `screen_time_logs` or `student_progress`)
  - **Screen Time Compliant** — awarded by daily scheduler when student stays within limits every day for 7 consecutive days
- [ ] Each badge awarded only once per student (idempotent via `student_badges` composite PK)
- [ ] Return list of newly awarded badges for response enrichment
- [ ] Integrate badge checks into:
  - `ProgressController@submitLevel` — check Three-Star, 3-Day Streak
  - `BossController@submit` — check First Boss, Quarter Complete, Full World, 3-Day Streak

**Deliverable:** Badges are awarded automatically on qualifying gameplay actions.

### Task 5: At-Risk Alert Enhancement

- [ ] Enhance `ComputeGrade` action post-computation check:
  - If `final_grade < 75` -> create `AtRiskAlert` (upsert by student+subject+quarter)
  - If `final_grade >= 75` and an unresolved alert exists -> mark as resolved
- [ ] Create `CheckAtRiskStudents` Artisan command (Section 6.9):
  - Scan all `grade_records` for `final_grade < 75`
  - Also flag students with overdue quarterly assessments (boss not submitted by expected date)
  - Upsert/resolve alerts as needed
- [ ] Register in scheduler: `$schedule->command('nutrimind:check-at-risk')->dailyAt('00:00')`

**Deliverable:** At-risk alerts are created/resolved based on grade thresholds.

### Task 6: MATATAG Competency Service

- [ ] Create `MATATAGCompetencyService.php`
- [ ] For a given student + subject:
  - Return per-level competency breakdown with:
    - `matatag_competency_code`
    - `matatag_competency_desc`
    - Student's score for that level (or null if not attempted)
    - Mastery status: `mastered` (>= 75), `developing` (50-74), `not_started` (no attempt or < 50)
- [ ] Integrate into student detail views (used in Phase 6 dashboards)

**Deliverable:** Per-learner competency breakdown available for teacher dashboards.

### Task 7: Boss Question Pool Seeder

- [ ] Create `BossQuestionSeeder.php`
- [ ] For each boss battle, assign 10-15 questions from the quarter's levels to the boss question pool
- [ ] Ensure questions span all 3 difficulty tiers when available
- [ ] Register in `DatabaseSeeder.php`

**Deliverable:** Boss battles have question pools drawn from their quarter's content.

### Task 8: Update Postman Collection

- [ ] Add `POST /api/v1/student/progress/boss` with sample payload
- [ ] Update response schemas to include badge notifications
- [ ] Add test scenarios:
  - Submit boss with all 4 levels completed (should succeed)
  - Submit boss without completing all levels (should get 403)
  - Verify grade record created after boss submission

**Deliverable:** Postman collection covers boss battle workflow.

---

## Verification Checklist

Before marking Phase 3 as **35% complete**, all of the following must pass:

- [ ] `POST /api/v1/student/progress/boss` accepts boss results when all 4 levels are completed
- [ ] Boss submission is rejected (403) when levels are incomplete
- [ ] `ComputeGradeJob` dispatches after boss submission
- [ ] Grade computation produces correct values:
  - Written Work = average of level scores
  - Performance Task = (completed/4) x 100 x quality factor
  - Quarterly Assessment = boss score
  - Final Grade = (W x 0.25) + (P x 0.50) + (A x 0.25)
- [ ] `GradeRecord` is created/updated with component scores and final grade
- [ ] At-risk alert created when final grade < 75
- [ ] At-risk alert resolved when final grade rises to >= 75
- [ ] Badges awarded correctly:
  - First Boss Defeat on first-ever boss result
  - Three-Star Level when score >= 90
  - Quarter Complete when all 4 levels + boss are done
- [ ] Badges are idempotent (not duplicated on re-submission)
- [ ] `local_id` deduplication works on boss submissions
- [ ] All Phase 1 and Phase 2 functionality still works (no regressions)

---

## Files Created/Modified in Phase 3

```
app/Http/Controllers/Student/BossController.php
app/Http/Requests/Student/SubmitBossRequest.php
app/Actions/ComputeGrade.php
app/Actions/AwardBadge.php
app/Services/MATATAGCompetencyService.php
app/Jobs/ComputeGradeJob.php
app/Jobs/EvaluateDifficultyAdvisoryJob.php
app/Console/Commands/CheckAtRiskStudents.php
database/seeders/BossQuestionSeeder.php
routes/api.php (updated)
routes/console.php (scheduler registration)
docs/postman/NutriMind_API_v1.json (updated)
```

---

*Phase 3 -> Phase 4: Once the 35% milestone passes, proceed to [Phase 4 — Preferences, Language & Sync](./phase-4-preferences-sync.md)*
