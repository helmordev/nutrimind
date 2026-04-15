# NutriMind — Server-Side Development Plan (Laravel)

> REST API Backend + Admin & Teacher Web Dashboard
> Tayug Central Elementary School | Grade 5 & 6 | MATATAG Curriculum Aligned
> University of Eastern Pangasinan Capstone Project

---

## Table of Contents

1. [Technology Stack](#1-technology-stack)
2. [Role Hierarchy & Account Creation Flow](#2-role-hierarchy--account-creation-flow)
3. [Laravel Project Structure](#3-laravel-project-structure)
4. [Database Schema & Migrations](#4-database-schema--migrations)
5. [API Endpoints](#5-api-endpoints)
6. [Core Business Logic](#6-core-business-logic)
7. [Web Dashboard — Super Admin Panel](#7-web-dashboard--super-admin-panel)
8. [Web Dashboard — Teacher Panel](#8-web-dashboard--teacher-panel)
9. [Offline Sync Design](#9-offline-sync-design)
10. [Database Seeders](#10-database-seeders)
11. [Security Considerations](#11-security-considerations)
12. [Environment Variables](#12-environment-variables)

> **Development Phases & Execution Plan:** See [`.plan/README.md`](../.plan/README.md) for the full 7-phase milestone roadmap.

---

## 1. Technology Stack

| Layer | Choice | Reason |
|---|---|---|
| Framework | Laravel 13 | Fast development, rich tooling, widely known by PH capstone panels |
| Language | PHP 8.5 | Native Laravel 13 environment; backed enums, fibers, property hooks |
| Database | MySQL 8 | Available on most PH shared hosting and cPanel setups |
| Auth | Laravel Sanctum | Simple opaque API tokens for Unity; session auth for web dashboards |
| Scheduler | Laravel Scheduler | Built-in cron — at-risk alert job is one `schedule->daily()` call |
| Queue | Laravel Queue (database driver) | Async grade computation; no Redis required |
| Export | Maatwebsite/Laravel-Excel | DepEd-formatted CSV export in one Exportable class |
| UI Layer | Livewire 4 + Blade + Tailwind | Reactive dashboards; Alpine.js bundled with Livewire for small UI. Livewire 4 features: single-file components (SFC), `Route::livewire()` routing, self-closing tags, `data-loading` attribute, non-blocking `wire:poll` |
| Patterns | Action Classes + Enums | Single-responsibility Actions; PHP 8.5 backed enums for type safety |
| Deployment | Shared hosting (cPanel) or basic VPS | School-friendly; runs on any PHP 8.5+ host |

### Why Not WebSocket?

All Unity <-> Server interactions are simple request/response (submit score, check unlock, heartbeat ping). The teacher and admin dashboards refresh via polling — sufficient for 60-120 students at one school. If live push is ever needed for teacher dashboards, Laravel's Server-Sent Events via `response()->stream()` is sufficient without a full WebSocket server.

---

## 2. Role Hierarchy & Account Creation Flow

### Role Structure

```
super_admin  (School Registrar / System Administrator)
    +-- teacher  (Subject teachers, class advisers)
            +-- student  (Grade 5 & 6 learners)
```

### Account Creation Chain

```
+-------------------------------------------------------------+
|  SUPER ADMIN (Registrar)                                     |
|  - Created via Artisan seeder at deployment                  |
|  - Creates and manages all Teacher accounts                  |
|  - Full school-wide oversight and settings                   |
+-----------------------------+-------------------------------+
                              | creates
                              v
+-------------------------------------------------------------+
|  TEACHER                                                     |
|  - Receives: username + temporary password from super admin  |
|  - Forced to change password on first login                  |
|  - Creates and manages Student accounts for their class      |
|  - Controls: level unlocks, difficulty, screen time limits   |
+-----------------------------+-------------------------------+
                              | creates
                              v
+-------------------------------------------------------------+
|  STUDENT                                                     |
|  - Receives: Student ID (LRN) + auto-generated 6-digit PIN  |
|  - Teacher prints/distributes a credential slip per student  |
|  - Logs into Unity app with Student ID + PIN                 |
|  - PIN reset by teacher; preferences saved to account        |
+-------------------------------------------------------------+
```

### Authentication by Role

| Role | Login Method | Interface |
|---|---|---|
| `super_admin` | Username + password | Web dashboard (`/admin`) |
| `teacher` | Username + password | Web dashboard (`/teacher`) |
| `student` | Student ID (LRN) + 6-digit PIN | Unity Android app |

---

## 3. Laravel Project Structure

```
app/
|-- Http/
|   |-- Controllers/
|   |   |-- Auth/
|   |   |   |-- AuthController.php              -- Student: LRN + PIN -> Sanctum token
|   |   |   +-- WebAuthController.php           -- Teacher/Admin: username + password (session)
|   |   |-- Student/
|   |   |   |-- WorldController.php
|   |   |   |-- LevelController.php
|   |   |   |-- ProgressController.php
|   |   |   |-- BossController.php
|   |   |   |-- ScreenTimeController.php
|   |   |   |-- BadgeController.php
|   |   |   |-- PreferencesController.php       -- Language, sound, text size, TTS
|   |   |   +-- SyncController.php
|   |   |-- Teacher/
|   |   |   |-- StudentController.php           -- Create/edit/delete students, PIN management
|   |   |   |-- ClassController.php
|   |   |   |-- UnlockController.php
|   |   |   |-- DifficultyController.php        -- Adaptive difficulty per student / class
|   |   |   |-- GradeController.php
|   |   |   |-- ScreenTimeController.php        -- Batch + per-student screen time controls
|   |   |   +-- AlertController.php
|   |   +-- Admin/
|   |       |-- TeacherController.php
|   |       |-- StudentController.php
|   |       |-- ClassController.php
|   |       |-- ReportController.php
|   |       |-- ContentController.php           -- Question bank management
|   |       +-- SettingsController.php          -- Global screen time + difficulty defaults
|   |
|   |-- Middleware/
|   |   |-- EnsureIsStudent.php
|   |   |-- EnsureIsTeacher.php
|   |   +-- EnsureIsSuperAdmin.php
|   |
|   +-- Requests/                               -- Form Request validation classes
|       |-- Auth/
|       |   |-- StudentLoginRequest.php
|       |   +-- WebLoginRequest.php
|       |-- Student/
|       |   |-- SubmitLevelRequest.php
|       |   |-- SubmitBossRequest.php
|       |   |-- SyncRequest.php
|       |   +-- UpdatePreferencesRequest.php
|       |-- Teacher/
|       |   |-- CreateStudentRequest.php
|       |   |-- ImportStudentsRequest.php
|       |   |-- SetDifficultyRequest.php
|       |   +-- UpdateScreenTimeRequest.php
|       +-- Admin/
|           |-- CreateTeacherRequest.php
|           +-- UpdateSettingsRequest.php
|
|-- Actions/                                    -- Single-responsibility action classes
|   |-- CreateStudent.php                       -- Student account + PIN + default preferences
|   |-- ResetStudentPin.php                     -- Regenerate PIN, return credential slip
|   |-- ComputeGrade.php                        -- DepEd grade computation (WW/PT/QA/Final)
|   |-- AwardBadge.php                          -- Check + award badge eligibility
|   |-- ProcessSync.php                         -- Bulk offline sync: dedup, upsert, screen time
|   |-- EvaluateDifficultyAdvisory.php          -- Rolling average → auto-suggest for teacher
|   +-- ResolveUnlockStatus.php                 -- Level/boss/quarter unlock resolution
|
|-- Enums/                                      -- PHP 8.5 backed enums for type safety
|   |-- UserRole.php                            -- student, teacher, super_admin
|   |-- DifficultyLevel.php                     -- easy, standard, hard
|   |-- QuestionType.php                        -- multiple_choice, fill_blank, matching, drag_drop
|   |-- TextSize.php                            -- normal, large
|   |-- Language.php                            -- en, fil
|   |-- ScreenTimeScope.php                     -- global, class, student
|   +-- DifficultySetBy.php                     -- teacher, system_default
|
|-- Models/
|   |-- User.php
|   |-- StudentProfile.php
|   |-- StudentPreference.php                   -- Language, sound, text size, TTS toggle
|   |-- StudentDifficulty.php                   -- Per-subject difficulty setting per student
|   |-- Subject.php
|   |-- Quarter.php
|   |-- Level.php
|   |-- Question.php
|   |-- BossBattle.php
|   |-- Badge.php
|   |-- StudentProgress.php
|   |-- BossResult.php
|   |-- StudentBadge.php
|   |-- GradeRecord.php
|   |-- ScreenTimeLog.php
|   |-- ScreenTimeSetting.php
|   |-- DifficultyAdvisory.php                  -- Auto-suggest records for teacher review
|   +-- AtRiskAlert.php
|
|-- Services/                                   -- Utility / resolution services (stateful logic)
|   |-- ScreenTimeService.php                   -- Three-tier resolution: student > class > global
|   +-- MATATAGCompetencyService.php            -- Competency code mapping and breakdown
|
|-- Livewire/                                   -- Livewire 4 components for reactive dashboards
|   |-- Teacher/
|   |   |-- DifficultyManager.php               -- Difficulty dropdowns + advisory review
|   |   |-- ScreenTimeManager.php               -- Class/student screen time controls
|   |   |-- GradeTable.php                      -- Filterable grade table with export
|   |   +-- StudentDetailPanel.php              -- Per-student detail with live data
|   +-- Admin/
|       |-- TeacherManager.php                  -- Teacher CRUD + overview
|       |-- QuestionBankEditor.php              -- Question add/edit/deactivate
|       +-- SchoolOverview.php                  -- School-wide stats dashboard
|
|-- Jobs/
|   |-- ComputeGradeJob.php                     -- Dispatches ComputeGrade action
|   +-- EvaluateDifficultyAdvisoryJob.php       -- Dispatches EvaluateDifficultyAdvisory action
|
|-- Console/Commands/
|   +-- CheckAtRiskStudents.php
|
+-- Exports/
    |-- GradeSheetExport.php
    +-- CredentialSlipExport.php

database/
+-- seeders/
    |-- SuperAdminSeeder.php
    |-- SubjectSeeder.php
    |-- QuarterSeeder.php
    |-- LevelSeeder.php
    |-- BossSeeder.php
    |-- BadgeSeeder.php
    +-- ScreenTimeSettingSeeder.php
```

### Enum Definitions (PHP 8.5 Backed Enums)

All enums are stored as `string` columns in MySQL (not DB-level `ENUM`) and cast via Laravel's native enum casting on each model. This keeps migrations portable and lets PHP enforce valid values at the application layer.

```php
enum UserRole: string {
    case Student    = 'student';
    case Teacher    = 'teacher';
    case SuperAdmin = 'super_admin';
}

enum DifficultyLevel: string {
    case Easy     = 'easy';
    case Standard = 'standard';
    case Hard     = 'hard';
}

enum QuestionType: string {
    case MultipleChoice = 'multiple_choice';
    case FillBlank      = 'fill_blank';
    case Matching       = 'matching';
    case DragDrop       = 'drag_drop';
}

enum TextSize: string {
    case Normal = 'normal';
    case Large  = 'large';
}

enum Language: string {
    case English  = 'en';
    case Filipino = 'fil';
}

enum ScreenTimeScope: string {
    case Global  = 'global';
    case ClassScope = 'class';      // 'Class' is reserved in PHP
    case Student = 'student';
}

enum DifficultySetBy: string {
    case Teacher       = 'teacher';
    case SystemDefault = 'system_default';
}
```

---

## 4. Database Schema & Migrations

### 4.1 Users & Auth

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('role');                                    // Cast via UserRole enum
    $table->string('full_name');
    $table->string('username')->unique();
    $table->string('password');
    $table->unsignedTinyInteger('grade')->nullable();     // 5 or 6
    $table->string('section')->nullable();
    $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
    $table->boolean('is_active')->default(true);
    $table->boolean('must_change_password')->default(false);
    $table->timestamps();
});
// Model cast: 'role' => UserRole::class

// student_profiles — LRN + PIN + login tracking
Schema::create('student_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('lrn', 12)->unique();
    $table->string('pin', 6);                    // hashed 6-digit numeric PIN
    $table->timestamp('pin_generated_at')->nullable();
    $table->timestamp('last_login_at')->nullable();
    $table->timestamps();
});
```

### 4.2 Student Preferences

```php
// student_preferences — synced to device; survives reinstall
// Language preference applies to UI shell only (not question content)
Schema::create('student_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('language')->default('en');                // Cast via Language enum
    $table->unsignedTinyInteger('master_volume')->default(80);  // 0-100
    $table->unsignedTinyInteger('bgm_volume')->default(70);
    $table->unsignedTinyInteger('sfx_volume')->default(90);
    $table->boolean('tts_enabled')->default(true);           // read-aloud on/off
    $table->string('text_size')->default('normal');           // Cast via TextSize enum
    $table->boolean('colorblind_mode')->default(false);
    $table->timestamps();
});
```

### 4.3 Adaptive Difficulty

```php
// student_difficulties — per student per subject
// Teacher sets this manually; auto-suggest populates difficulty_advisory
Schema::create('student_difficulties', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->string('difficulty')->default('standard');       // Cast via DifficultyLevel enum
    $table->string('set_by')->default('teacher');            // Cast via DifficultySetBy enum
    $table->timestamp('updated_at_by_teacher')->nullable();
    $table->timestamps();
    $table->unique(['student_id', 'subject_id']);
});

// difficulty_advisories — auto-suggested changes for teacher review (not auto-applied)
Schema::create('difficulty_advisories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->string('current_difficulty');                    // Cast via DifficultyLevel enum
    $table->string('suggested_difficulty');                  // Cast via DifficultyLevel enum
    $table->string('reason');           // e.g. '3 consecutive levels above 90% — consider Hard'
    $table->decimal('rolling_avg', 5, 2);
    $table->boolean('is_reviewed')->default(false);
    $table->timestamp('reviewed_at')->nullable();
    $table->timestamps();
});
```

### How Difficulty Affects Question Serving

```
Difficulty setting maps to question difficulty tiers:
  easy     -> serves only difficulty 1 questions
  standard -> serves difficulty 1 and 2 questions (default)
  hard     -> serves all difficulty levels (1, 2, 3)

Boss battle pools also respect difficulty:
  easy     -> draws from difficulty 1 pool only
  standard -> draws from difficulty 1-2 pool
  hard     -> draws from full pool including difficulty 3
```

### 4.4 Curriculum & Content

```php
Schema::create('subjects', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('world_theme');
    $table->string('color_hex');
    $table->timestamps();
});

Schema::create('quarters', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->unsignedTinyInteger('quarter_number');
    $table->unsignedTinyInteger('current_unlock_week')->default(0);
    $table->boolean('is_globally_unlocked')->default(false);
    $table->timestamps();
});

Schema::create('levels', function (Blueprint $table) {
    $table->id();
    $table->foreignId('quarter_id')->constrained()->cascadeOnDelete();
    $table->unsignedTinyInteger('level_number');
    $table->string('title');
    $table->string('matatag_competency_code')->nullable();
    $table->string('matatag_competency_desc')->nullable();
    $table->unsignedTinyInteger('unlock_week');
    $table->timestamps();
});

Schema::create('questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('level_id')->constrained()->cascadeOnDelete();
    $table->string('question_type');                         // Cast via QuestionType enum
    $table->json('content');             // supports both 'en' and 'fil' instruction text
    $table->json('correct_answer');
    $table->unsignedTinyInteger('difficulty')->default(1);   // 1 = easy, 2 = medium, 3 = hard
    $table->unsignedSmallInteger('order_index')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Question Content JSON Structure (Bilingual)

```json
{
  "instruction": {
    "en": "Choose the correct answer.",
    "fil": "Piliin ang tamang sagot."
  },
  "question_text": {
    "en": "Which word is a synonym for 'happy'?",
    "fil": "Alin ang salitang kasingkahulugan ng 'masaya'?"
  },
  "options": [
    { "id": "a", "en": "Sad", "fil": "Malungkot" },
    { "id": "b", "en": "Joyful", "fil": "Masaya" },
    { "id": "c", "en": "Angry", "fil": "Galit" },
    { "id": "d", "en": "Tired", "fil": "Pagod" }
  ]
}
```

> **Important:** Bilingual support applies to question instructions and UI labels. Subject-specific content (e.g., English vocabulary questions) keeps its primary language intact — the Filipino translation is for instructions only, not the English terms being tested.

### 4.5 Boss Battles

```php
Schema::create('boss_battles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('quarter_id')->constrained()->cascadeOnDelete();
    $table->string('boss_name');
    $table->unsignedSmallInteger('total_hp')->default(500);
    $table->timestamps();
});

Schema::create('boss_questions', function (Blueprint $table) {
    $table->foreignId('boss_battle_id')->constrained()->cascadeOnDelete();
    $table->foreignId('question_id')->constrained()->cascadeOnDelete();
    $table->primary(['boss_battle_id', 'question_id']);
});
```

### 4.6 Student Progress

```php
Schema::create('student_progress', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('level_id')->constrained()->cascadeOnDelete();
    $table->string('difficulty_played')->default('standard');  // Cast via DifficultyLevel enum
    $table->decimal('score', 5, 2);
    $table->unsignedTinyInteger('stars');
    $table->unsignedSmallInteger('attempts')->default(1);
    $table->unsignedSmallInteger('time_taken_seconds');
    $table->timestamp('completed_at');
    $table->string('local_id')->unique();
    $table->timestamps();
    $table->unique(['student_id', 'level_id']);
});

Schema::create('boss_results', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('boss_battle_id')->constrained()->cascadeOnDelete();
    $table->string('difficulty_played')->default('standard');  // Cast via DifficultyLevel enum
    $table->decimal('score', 5, 2);
    $table->unsignedSmallInteger('hp_dealt');
    $table->timestamp('completed_at');
    $table->string('local_id')->unique();
    $table->timestamps();
    $table->unique(['student_id', 'boss_battle_id']);
});

Schema::create('student_badges', function (Blueprint $table) {
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
    $table->timestamp('earned_at');
    $table->primary(['student_id', 'badge_id']);
});
```

### 4.7 Grades

```php
Schema::create('grade_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->unsignedTinyInteger('quarter_number');
    $table->decimal('written_work', 5, 2)->nullable();          // 25%
    $table->decimal('performance_task', 5, 2)->nullable();      // 50%
    $table->decimal('quarterly_assessment', 5, 2)->nullable();  // 25%
    $table->decimal('final_grade', 5, 2)->nullable();
    $table->timestamp('computed_at')->nullable();
    $table->timestamps();
    $table->unique(['student_id', 'subject_id', 'quarter_number']);
});
```

### 4.8 Screen Time

```php
Schema::create('screen_time_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->date('log_date');
    $table->unsignedSmallInteger('total_minutes')->default(0);
    $table->unsignedTinyInteger('levels_played')->default(0);
    $table->timestamp('last_active_at')->nullable();
    $table->timestamps();
    $table->unique(['student_id', 'log_date']);
});

// screen_time_settings — three-tier scope: student > class > global
// Default values per NutriMind document:
//   School Day: 3:00PM-8:00PM | 45min | 2 levels
//   Weekend:    8:00AM-8:00PM | 60min | 3 levels
Schema::create('screen_time_settings', function (Blueprint $table) {
    $table->id();
    $table->string('scope')->default('global');               // Cast via ScreenTimeScope enum
    $table->unsignedBigInteger('scope_id')->nullable(); // teacher_id for class, user_id for student
    $table->unsignedTinyInteger('school_day_limit_min')->default(45);
    $table->unsignedTinyInteger('weekend_limit_min')->default(60);
    $table->unsignedTinyInteger('max_levels_school')->default(2);
    $table->unsignedTinyInteger('max_levels_weekend')->default(3);
    $table->time('play_start_school')->default('15:00:00');
    $table->time('play_end_school')->default('20:00:00');
    $table->time('play_start_weekend')->default('08:00:00');
    $table->time('play_end_weekend')->default('20:00:00');
    $table->timestamps();
});

Schema::create('at_risk_alerts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->unsignedTinyInteger('quarter_number');
    $table->decimal('grade_at_flag', 5, 2);
    $table->boolean('is_resolved')->default(false);
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();
    $table->unique(['student_id', 'subject_id', 'quarter_number']);
});
```

---

## 5. API Endpoints

Routes: `routes/api.php` (Unity, `/api/v1` prefix, Sanctum token auth) and `routes/web.php` (dashboards, session auth).

### 5.1 Auth — Unity

| Method | Route | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/auth/login` | Public | Student LRN + PIN -> Sanctum token |
| POST | `/api/v1/auth/logout` | `auth:sanctum` | Revoke token |

### 5.2 Student — World, Content & Preferences

| Method | Route | Auth | Description |
|---|---|---|---|
| GET | `/api/v1/student/worlds` | student | All 3 worlds with unlock status, difficulty setting, MATATAG info |
| GET | `/api/v1/student/levels/{level}/questions` | student | Questions filtered by student's current difficulty setting |
| GET | `/api/v1/student/badges` | student | All badges with earned/unearned status |
| GET | `/api/v1/student/grades` | student | Grade summary per subject per quarter |
| GET | `/api/v1/student/preferences` | student | Fetch saved preferences (language, volume, text size, TTS, colorblind) |
| PUT | `/api/v1/student/preferences` | student | Save updated preferences to server |

### 5.3 Student — Progress Submission

| Method | Route | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/student/progress/level` | student | Submit level result (includes `difficulty_played`) |
| POST | `/api/v1/student/progress/boss` | student | Submit boss result; dispatches `ComputeGradeJob` + `EvaluateDifficultyAdvisoryJob` |
| POST | `/api/v1/student/progress/sync` | student | Bulk upload offline-queued records |
| GET | `/api/v1/student/sync/state` | student | Full state pull — unlocks, difficulty, preferences, screen time, badges, grades |

### 5.4 Student — Screen Time

| Method | Route | Auth | Description |
|---|---|---|---|
| GET | `/api/v1/student/screentime/status` | student | Remaining time, remaining levels, play window, full timer display values |
| POST | `/api/v1/student/screentime/heartbeat` | student | Increments log, returns full status including visible countdown values |
| GET | `/api/v1/student/screentime/history` | student | Last 7 days of usage |

### 5.5 Teacher Dashboard

| Method | Route | Auth | Description |
|---|---|---|---|
| GET | `/teacher/class` | teacher | All students with progress, difficulty badges, at-risk flags |
| GET | `/teacher/students/{student}` | teacher | Full breakdown including difficulty history and advisory notices |
| GET | `/teacher/students/{student}/matatag` | teacher | MATATAG competency breakdown per learner |
| GET/POST | `/teacher/students/create` | teacher | Create student account |
| POST | `/teacher/students/{student}/reset-pin` | teacher | Regenerate student PIN |
| GET | `/teacher/students/{student}/credential-slip` | teacher | Printable credential PDF |
| POST | `/teacher/students/import` | teacher | Bulk CSV import |
| GET/PUT | `/teacher/unlock/status` | teacher | Get/set current unlock week per subject |
| GET | `/teacher/grades` | teacher | Full grade table |
| GET | `/teacher/grades/export` | teacher | DepEd-formatted CSV export |
| GET | `/teacher/alerts/at-risk` | teacher | Unresolved at-risk alerts |
| GET | `/teacher/difficulty` | teacher | All students' current difficulty settings + pending advisories |
| PUT | `/teacher/difficulty/student/{student}` | teacher | Set difficulty for one student per subject |
| PUT | `/teacher/difficulty/class` | teacher | Set difficulty for ALL students in class at once |
| POST | `/teacher/difficulty/advisories/{id}/review` | teacher | Accept or dismiss an auto-suggest advisory |
| GET | `/teacher/screentime/class` | teacher | Class-wide screen time summary |
| GET | `/teacher/screentime/{student}` | teacher | Individual student screen time |
| PUT | `/teacher/screentime/class` | teacher | Update screen time limits for ALL students at once |
| PUT | `/teacher/screentime/student/{student}` | teacher | Update screen time limit for ONE student (overrides class setting) |
| DELETE | `/teacher/screentime/student/{student}/override` | teacher | Remove per-student override — student reverts to class setting |

### 5.6 Super Admin Dashboard

| Method | Route | Auth | Description |
|---|---|---|---|
| GET | `/admin/dashboard` | super_admin | School-wide overview |
| GET/POST | `/admin/teachers/create` | super_admin | Create teacher account |
| GET/PUT | `/admin/teachers/{teacher}` | super_admin | Edit or deactivate teacher |
| GET | `/admin/students` | super_admin | School-wide student list |
| GET | `/admin/reports/export` | super_admin | Cross-class grade export |
| GET/POST | `/admin/content/levels/{level}/questions` | super_admin | Question bank management |
| GET/PUT | `/admin/settings/screentime` | super_admin | Global screen time defaults |
| GET/PUT | `/admin/settings/difficulty` | super_admin | Global default difficulty |

---

## 6. Core Business Logic

### 6.1 Student Account & PIN (`CreateStudent` Action)

```php
public function handle(array $data, User $teacher): array
{
    $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $user = User::create([
        'role'       => UserRole::Student,
        'full_name'  => $data['full_name'],
        'username'   => $data['lrn'],
        'password'   => Hash::make($pin),
        'grade'      => $data['grade'],
        'section'    => $data['section'],
        'teacher_id' => $teacher->id,
    ]);

    $user->studentProfile()->create([
        'lrn'              => $data['lrn'],
        'pin'              => Hash::make($pin),
        'pin_generated_at' => now(),
    ]);

    // Seed default preferences for the new student
    $user->preferences()->create([]);  // all defaults from migration

    // Seed default difficulty (standard) per subject
    Subject::all()->each(fn($subject) =>
        $user->difficulties()->create([
            'subject_id' => $subject->id,
        'difficulty' => DifficultyLevel::Standard,
        ])
    );

    // Seed default screen time (inherits class setting — no student row needed unless overriding)

    return ['user' => $user, 'plain_pin' => $pin]; // PIN visible in plaintext only once
}
```

### 6.2 Adaptive Difficulty (`EvaluateDifficultyAdvisory` Action)

**Teacher-manual control** (primary): Teacher sets difficulty per student per subject from the dashboard. This is the authoritative value that Unity reads.

**Auto-suggest** (advisory only): Runs as a queued job after every boss battle submission. Analyses the student's rolling average for that subject over the last quarter and generates a `difficulty_advisory` record if thresholds are crossed. Teacher must explicitly accept or dismiss each advisory — **nothing changes automatically**.

```php
// EvaluateDifficultyAdvisoryJob.php
public function handle(EvaluateDifficultyAdvisory $action): void
{
    $action->handle($this->studentId, $this->subjectId, $this->quarterNumber);
}

// EvaluateDifficultyAdvisory.php (Action)
public function handle(int $studentId, int $subjectId, int $quarterNumber): void
{
    $recentScores = StudentProgress::where('student_id', $studentId)
        ->whereHas('level.quarter', fn($q) =>
            $q->where('subject_id', $subjectId)->where('quarter_number', $quarterNumber))
        ->latest('completed_at')
        ->take(3)
        ->pluck('score');

    if ($recentScores->count() < 3) return; // not enough data yet

    $avg     = $recentScores->average();
    $current = StudentDifficulty::where(['student_id' => $studentId, 'subject_id' => $subjectId])
                 ->value('difficulty');

    $suggestion = null;
    $reason     = null;

    if ($avg >= 90 && $current !== DifficultyLevel::Hard) {
        $suggestion = match($current) {
            DifficultyLevel::Easy => DifficultyLevel::Standard,
            DifficultyLevel::Standard => DifficultyLevel::Hard,
            default => null,
        };
        $reason = "Last 3 levels averaged {$avg}% — student may be ready for harder questions.";
    } elseif ($avg < 60 && $current !== DifficultyLevel::Easy) {
        $suggestion = match($current) {
            DifficultyLevel::Hard => DifficultyLevel::Standard,
            DifficultyLevel::Standard => DifficultyLevel::Easy,
            default => null,
        };
        $reason = "Last 3 levels averaged {$avg}% — student may need easier questions.";
    }

    if ($suggestion) {
        DifficultyAdvisory::updateOrCreate(
            ['student_id' => $studentId, 'subject_id' => $subjectId, 'is_reviewed' => false],
            ['current_difficulty' => $current, 'suggested_difficulty' => $suggestion,
             'reason' => $reason, 'rolling_avg' => $avg]
        );
    }
}
```

**Question Serving by Difficulty:**

```php
// LevelController — questions filtered by student's active difficulty
public function questions(Level $level, Request $request): JsonResponse
{
    $student    = $request->user();
    $difficulty = StudentDifficulty::where([
        'student_id' => $student->id,
        'subject_id' => $level->quarter->subject_id,
    ])->value('difficulty') ?? DifficultyLevel::Standard;

    $difficultyMap = [
        DifficultyLevel::Easy->value     => [1],
        DifficultyLevel::Standard->value => [1, 2],
        DifficultyLevel::Hard->value     => [1, 2, 3],
    ];

    $questions = $level->questions()
        ->whereIn('difficulty', $difficultyMap[$difficulty->value ?? $difficulty])
        ->where('is_active', true)
        ->inRandomOrder()
        ->get();

    return response()->json([
        'questions'  => QuestionResource::collection($questions),
        'difficulty' => $difficulty,
    ]);
}
```

### 6.3 Screen Time Controls (`ScreenTimeService`)

**Three-tier resolution: student > class > global**

```
Teacher sets class-wide limit (PUT /teacher/screentime/class)
  -> Upserts a 'class' scoped row in screen_time_settings (scope_id = teacher_id)
  -> Affects all students in the class whose individual override is absent

Teacher sets per-student limit (PUT /teacher/screentime/student/{id})
  -> Upserts a 'student' scoped row in screen_time_settings (scope_id = student_id)
  -> Overrides the class setting for just this student

Teacher removes per-student override (DELETE /teacher/screentime/student/{id}/override)
  -> Deletes the 'student' scoped row
  -> Student automatically reverts to the class setting

Resolution on heartbeat:
  1. Check for ScreenTimeScope::Student, scope_id=student.id -> use if found
  2. Check for ScreenTimeScope::ClassScope, scope_id=teacher.id -> use if found
  3. Fall back to ScreenTimeScope::Global
```

**Heartbeat response includes full visible timer data for Unity:**

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

Unity uses `remaining_minutes`, `time_used_today`, and `daily_limit` to render the visible countdown timer in the app.

### 6.4 Student Preferences (`PreferencesController`)

```php
// GET /api/v1/student/preferences — returns current saved preferences
// PUT /api/v1/student/preferences — updates one or more fields

// Included in GET /api/v1/student/sync/state so preferences are always
// fresh on app open without a separate request.

// Preference fields:
//   language        -> 'en' | 'fil'  (UI shell language only)
//   master_volume   -> 0-100
//   bgm_volume      -> 0-100
//   sfx_volume      -> 0-100
//   tts_enabled     -> true | false
//   text_size       -> 'normal' | 'large'
//   colorblind_mode -> true | false
```

### 6.5 DepEd Grade Computation

```
Written Work (25%)   = average of all level scores for this student, subject, quarter
Performance Task (50%) = (levels_completed / 4) x 100 x attempt_quality_factor
                         attempt quality: 1 attempt=1.00, 2=0.95, 3+=0.90
Quarterly Assessment (25%) = boss battle score for this quarter

Final Grade = (Written x 0.25) + (Performance x 0.50) + (Assessment x 0.25)
```

Dispatched as `ComputeGradeJob` after every boss submission. At-risk check fires after every computation.

### 6.6 Level Unlock Logic

```
Level ACCESSIBLE if:
  quarters.current_unlock_week >= levels.unlock_week   [teacher-paced]
  AND previous level in quarter is completed by student

Boss ACCESSIBLE if:
  All 4 levels in the quarter are completed (stars >= 1)

Next QUARTER unlocks if:
  Student has BossResult for ALL 3 subject world bosses in current quarter
  (English + Science + Health+PE — cross-subject gate per document)
```

### 6.7 Boss Mapping (Per Document)

| Boss | Subject | Active Quarters |
|---|---|---|
| Word Warden | English | Q1, Q2, Q3, Q4 |
| Contaminus | Science | Q1, Q2, Q3, Q4 |
| Junklord | Health+PE | Q1, Q3 |
| Idle Rex | Health+PE | Q2, Q4 |

### 6.8 Badge Award Logic

| Badge | Trigger | Condition |
|---|---|---|
| First Boss Defeat | Boss submitted | Student's first ever BossResult |
| Three-Star Level | Level submitted | score >= 90 |
| Quarter Complete | Boss submitted | All 4 levels + boss done in a quarter |
| Full World Complete | Boss submitted | All 4 quarters done in one subject world |
| 3-Day Streak | Heartbeat/level | Active on 3 consecutive calendar days |
| Screen Time Compliant | Daily scheduler | Within limits every day for 7 days |

### 6.9 At-Risk Alert System

```php
$schedule->command('nutrimind:check-at-risk')->dailyAt('00:00');
// Scans grade_records for final_grade < 75 or overdue quarterly_assessment
// Upserts at_risk_alerts; resolves alerts where grade has risen to >= 75
```

---

## 7. Web Dashboard — Super Admin Panel

Blade + Livewire 4 + Tailwind served at `/admin`. Session-based auth. Livewire handles reactive components (teacher manager, question bank editor, school overview); Alpine.js (bundled with Livewire) used for small UI interactions like dropdowns and modals.

**School-Wide Dashboard** — total students, teachers, active classes, school-wide at-risk count, overall completion rates

**Teacher Management** — create (with temp password + `must_change_password`), edit, deactivate

**School-Wide Student Oversight** — filterable by grade, section, teacher, at-risk status

**Cross-Class Grade Export** — select class / quarter / subject -> download DepEd-formatted CSV

**Question Bank Management** — browse all 48 levels, add/edit/deactivate questions, manage boss question pools, edit bilingual (`en`/`fil`) content for question instructions

**Global Default Settings**
- Screen time defaults (the baseline all class settings inherit from)
- Default difficulty level (standard for all new students)

---

## 8. Web Dashboard — Teacher Panel

Blade + Livewire 4 + Tailwind served at `/teacher`. Session-based auth. Livewire powers the interactive components (difficulty manager, screen time manager, grade table, student detail); Alpine.js (bundled) for lightweight UI toggles.

### Class Overview

- Student table: name, LRN, grade, section, per-subject difficulty badge, progress rings, at-risk badge
- Pending difficulty advisory notices shown as a banner (e.g., "3 students have difficulty suggestions waiting for review")

### Student Creation & Management

- Add student, auto-generate PIN, credential slip, bulk CSV import, edit, reset PIN

### Student Detail

- Per-quarter grade breakdown (Written / Performance / Quarterly Assessment / Final)
- Difficulty history per subject — when it was set and by whom
- MATATAG competency-level breakdown
- Level completion grid with star ratings
- Screen time bar chart last 7 days

### Adaptive Difficulty Manager

This is the teacher's primary tool for adjusting student learning:

```
+---------------------------------------------------------------+
|  DIFFICULTY CONTROLS                                           |
|                                                                |
|  Class Default: [Standard v]  [Apply to All Students]          |
|  ------------------------------------------------------------ |
|  ! 3 advisory notices pending review                           |
|     Juan dela Cruz - English - avg 93% - Suggest: Hard  [Y][N]|
|     Maria Santos  - Science - avg 55% - Suggest: Easy   [Y][N]|
|                                                                |
|  Per-Student Overrides:                                        |
|  Name            | English    | Science    | Health+PE         |
|  Juan dela Cruz  | Standard v | Hard v     | Standard v        |
|  Maria Santos    | Easy v     | Standard v | Standard v        |
+---------------------------------------------------------------+
```

- **"Apply to All Students"** — sets a class-level default difficulty for the selected subject; per-student overrides still take precedence
- **Per-student dropdowns** — sets a student-level override that supersedes the class default
- **Advisory notices** — the system suggests changes; teacher accepts (Y) or dismisses (N); nothing applies automatically

### Weekly Level Unlock Controls

- One dropdown per subject (English, Science, Health+PE): set current week (1-4)
- Confirmation modal before applying

### Screen Time Manager

```
+---------------------------------------------------------------+
|  SCREEN TIME CONTROLS                                          |
|                                                                |
|  Class-Wide Settings (applies to all unless overridden)        |
|  School Day: [45] min / [2] levels  Weekend: [60] min / [3]   |
|  Play Window School: [15:00] - [20:00]                         |
|  Play Window Weekend: [08:00] - [20:00]                        |
|  [Update All Students]                                         |
|  ------------------------------------------------------------ |
|  Per-Student Overrides:                                        |
|  Name           | School Day | Weekend | Today's Usage| Action |
|  Juan dela Cruz | 45 min *   | 60 min *| 23/45 min    | [Edit] |
|  Maria Santos   | 30 min ~   | 45 min ~| 18/30 min    | [Edit][Clear] |
+---------------------------------------------------------------+
* = inheriting class setting   ~ = student-level override active
```

- **"Update All Students"** -> writes a `class`-scoped setting for this teacher's class; affects all students without a personal override
- **"Edit" per student** -> opens a modal to set a `student`-scoped override
- **"Clear"** -> deletes the `student`-scoped row; student reverts to class setting
- Today's usage column updates via Livewire `wire:poll.30s` (auto-refresh every 30 seconds)

### Grade Export

- Quarter + Subject filter -> DepEd CSV download

### At-Risk Alerts Panel

- Color-coded list with click-through to student detail

---

## 9. Offline Sync Design

```json
POST /api/v1/student/progress/sync
{
  "device_date": "2025-07-14",
  "session_minutes_today": 38,
  "preferences": {
    "language": "fil",
    "master_volume": 80,
    "bgm_volume": 70,
    "sfx_volume": 90,
    "tts_enabled": true,
    "text_size": "normal",
    "colorblind_mode": false
  },
  "records": [
    {
      "local_id": "client-uuid-1",
      "type": "level",
      "level_id": 12,
      "difficulty_played": "standard",
      "score": 85.0,
      "stars": 2,
      "attempts": 1,
      "time_taken_seconds": 145,
      "completed_at": "2025-07-14T15:32:00+08:00"
    },
    {
      "local_id": "client-uuid-2",
      "type": "boss",
      "boss_battle_id": 3,
      "difficulty_played": "standard",
      "score": 90.0,
      "hp_dealt": 450,
      "completed_at": "2025-07-14T15:55:00+08:00"
    }
  ]
}
```

**Sync Rules:**
- Preferences included in sync payload so they are saved even if `PUT /student/preferences` was called offline
- `local_id` deduplication prevents duplicate records on re-submit
- Screen time: `GREATEST(server_total, submitted_total)` — client can never reduce recorded time

---

## 10. Database Seeders

### English — Library Dungeon (Boss: Word Warden — Q1-Q4)

| Quarter | L1 | L2 | L3 | L4 |
|---|---|---|---|---|
| Q1 | Phonics & Letters | Vocabulary Building | Reading Comprehension | Grammar Basics |
| Q2 | Figurative Language | Story Elements | Main Idea & Details | Parts of Speech |
| Q3 | Writing Sentences | Spelling Patterns | Oral Presentation | Inferencing Skills |
| Q4 | Poetry & Creative Text | Research Skills | Debate & Opinion | Cumulative Review |

### Science — Lab Cave (Boss: Contaminus — Q1-Q4)

| Quarter | L1 | L2 | L3 | L4 |
|---|---|---|---|---|
| Q1 | Intro to Science | Human Body Systems | States of Matter | Ecosystems |
| Q2 | Properties of Matter | Living vs Non-living | Food Chains | Weather & Climate |
| Q3 | Force & Motion | Energy Forms | Simple Machines | Science Experiments |
| Q4 | Earth & Space | Environmental Care | Tech in Science | Cumulative Review |

### Health+PE — Sports Arena (Bosses: Junklord Q1/Q3, Idle Rex Q2/Q4)

| Quarter | L1 | L2 | L3 | L4 | Boss |
|---|---|---|---|---|---|
| Q1 | Personal Hygiene | Nutrition & Food Groups | Disease Prevention | Basic Movement Skills | Junklord |
| Q2 | First Aid Basics | Mental Health | Sports & Games | Fitness Exercises | Idle Rex |
| Q3 | Healthy Habits | Substance Awareness | Team Sports | PE Safety Rules | Junklord |
| Q4 | Community Health | Lifestyle Diseases | Indigenous Games | Cumulative PE Review | Idle Rex |

### Super Admin Seeder

```php
// SuperAdminSeeder.php
User::create([
    'role'      => UserRole::SuperAdmin,
    'full_name' => 'System Administrator',
    'username'  => 'registrar',
    'password'  => Hash::make(env('ADMIN_INITIAL_PASSWORD')),
]);
```

---

## 11. Security Considerations

### 11.1 Authentication & Authorization

| Concern | Implementation |
|---|---|
| API Authentication | Sanctum opaque tokens for Unity client; session-based for web dashboards |
| PIN Storage | Student PINs hashed via `Hash::make()` — never stored in plaintext |
| Forced Password Change | Teachers flagged with `must_change_password=true` on creation; middleware blocks all routes until password is changed |
| Role Middleware | Three dedicated middleware classes enforce role-based access on every route group |
| Token Expiration | Sanctum tokens configured with expiration; stale tokens rejected automatically |

### 11.2 Input Validation

| Concern | Implementation |
|---|---|
| Form Requests | All endpoints use dedicated `FormRequest` classes with explicit validation rules |
| LRN Format | Validated as exactly 12 numeric characters |
| PIN Format | Validated as exactly 6 numeric digits |
| JSON Payloads | Sync payloads validated against strict schema (type, required fields, value ranges) |
| File Uploads | CSV imports validated for MIME type, size limit, and column structure |

### 11.3 Rate Limiting

| Endpoint | Limit | Reason |
|---|---|---|
| `POST /api/v1/auth/login` | 5 attempts per minute per IP | Prevents PIN brute-force attacks |
| `POST /api/v1/student/progress/*` | 30 requests per minute per user | Prevents replay/spam submissions |
| `POST /api/v1/student/screentime/heartbeat` | 12 requests per minute per user | Heartbeat expected every ~5 seconds |
| Web login routes | 5 attempts per minute per IP | Standard brute-force protection |

### 11.4 Data Protection

| Concern | Implementation |
|---|---|
| SQL Injection | Mitigated by Eloquent ORM; no raw SQL without parameter binding |
| XSS | Blade's `{{ }}` auto-escapes output; `{!! !!}` never used with user input |
| CSRF | Laravel's built-in CSRF token verification on all web POST/PUT/DELETE routes |
| CORS | Configured to allow only the Unity app's origin (or `*` for mobile clients) |
| Mass Assignment | All models use explicit `$fillable` arrays |
| Sensitive Data | Student PINs visible in plaintext only once at creation; credential slips are one-time downloads |

### 11.5 Operational Security

| Concern | Implementation |
|---|---|
| Error Handling | Production mode hides stack traces; errors logged to `storage/logs` |
| Debug Mode | `APP_DEBUG=false` enforced in production |
| HTTPS | Required in production; enforced via `APP_URL` and middleware |
| Database Backups | Scheduled daily via cron or hosting panel; backup storage outside web root |
| Audit Trail | `created_at` / `updated_at` on all tables; difficulty changes track `set_by` and `updated_at_by_teacher` |

---

## 12. Environment Variables

```env
# ─── Application ─────────────────────────────────────────────
APP_NAME=NutriMind
APP_ENV=production               # local | staging | production
APP_KEY=                         # Generated via: php artisan key:generate
APP_DEBUG=false                  # MUST be false in production
APP_URL=https://nutrimind.example.com
APP_TIMEZONE=Asia/Manila

# ─── Database ────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nutrimind
DB_USERNAME=nutrimind_user
DB_PASSWORD=                     # Strong password, never commit to git

# ─── Authentication ──────────────────────────────────────────
SANCTUM_STATEFUL_DOMAINS=nutrimind.example.com
ADMIN_INITIAL_PASSWORD=          # Used by SuperAdminSeeder; change after first login

# ─── Queue ───────────────────────────────────────────────────
QUEUE_CONNECTION=database        # No Redis required; uses jobs table
QUEUE_FAILED_TABLE=failed_jobs

# ─── Session ─────────────────────────────────────────────────
SESSION_DRIVER=database          # Or 'file' for simpler hosting
SESSION_LIFETIME=120             # Minutes; web dashboard session timeout
SESSION_DOMAIN=nutrimind.example.com

# ─── Rate Limiting ───────────────────────────────────────────
LOGIN_RATE_LIMIT=5               # Max login attempts per minute per IP
API_RATE_LIMIT=60                # Max API requests per minute per user

# ─── Logging ─────────────────────────────────────────────────
LOG_CHANNEL=daily                # Rotates log files daily
LOG_LEVEL=warning                # debug | info | warning | error

# ─── Mail (optional — for future email features) ─────────────
MAIL_MAILER=log                  # Use 'smtp' when email is needed
```

> **Note:** The `.env` file must never be committed to version control. A `.env.example` file with placeholder values should be maintained in the repository for reference.

---

*University of Eastern Pangasinan | Capstone Project — NutriMind*
*MATATAG Curriculum Aligned | Tayug Central Elementary School | Grade 5 & 6*
