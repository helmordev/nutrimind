# Phase 1 — Foundation, Auth & Account Chain

> **Milestone:** 10% | **Timeline:** 2 Days (Sprint)
> **Goal:** Functional demo for capstone technical panel showing the full account creation chain and basic dashboard access.

---

## Demo Scenario

```
SuperAdmin logs into /admin
  -> Creates a Teacher account (username + temp password)
Teacher logs into /teacher
  -> Forced to change password on first login
  -> Creates a Student account (LRN + auto-generated PIN)
  -> Views/prints credential slip
Student authenticates via API
  -> POST /api/v1/auth/login with LRN + PIN -> receives Sanctum token
  -> GET /api/v1/student/worlds -> returns 3 worlds with seeded data
  -> GET /api/v1/student/sync/state -> returns basic state payload
```

---

## Day 1 — Scaffold, Database & Auth

### Task 1: Laravel 13 Scaffolding & Dependencies

- [ ] Install required packages:
  - `laravel/sanctum` (API tokens)
  - `livewire/livewire` (reactive dashboard components)
  - `maatwebsite/excel` (CSV exports — install now, use later)
- [ ] Set up the project directory structure per Section 3 of the Server Plan
- [ ] Create `app/Enums/` directory
- [ ] Create `app/Actions/` directory
- [ ] Verify `php artisan serve` works

**Deliverable:** Clean Laravel 13 project with dependencies installed, including Livewire 4.

### Task 2: MySQL 8 Configuration

- [ ] Configure `.env` with database credentials
- [ ] Set `APP_TIMEZONE=Asia/Manila` in `.env.example`
- [ ] Test database connection with `php artisan migrate:status`
- [ ] Update `.env.example` with placeholder values (no secrets)

**Deliverable:** Laravel connected to MySQL 8; `migrate:status` runs clean.

### Task 3: All Database Migrations (17+ tables)

Run in this order to satisfy foreign key constraints:

- [ ] `users` — role (string, cast via `UserRole` enum), full_name, username, password, grade, section, teacher_id, is_active, must_change_password
- [ ] `student_profiles` — user_id FK, lrn (unique, 12 chars), pin (hashed), pin_generated_at, last_login_at
- [ ] `student_preferences` — user_id FK, language, volumes (master/bgm/sfx), tts_enabled, text_size, colorblind_mode
- [ ] `subjects` — name, grade (tinyint unsigned), world_theme, color_hex, unique(name, grade)
- [ ] `quarters` — subject_id FK, quarter_number, current_unlock_week, is_globally_unlocked
- [ ] `levels` — quarter_id FK, level_number, title, matatag_competency_code/desc, unlock_week
- [ ] `questions` — level_id FK, question_type (string, cast via `QuestionType` enum), content JSON, correct_answer JSON, difficulty, order_index, is_active
- [ ] `boss_battles` — quarter_id FK, boss_name, total_hp
- [ ] `boss_questions` — pivot: boss_battle_id + question_id composite PK
- [ ] `student_difficulties` — student_id FK, subject_id FK, difficulty (string, cast via `DifficultyLevel` enum), set_by (string, cast via `DifficultySetBy` enum), unique composite
- [ ] `difficulty_advisories` — student_id FK, subject_id FK, current/suggested difficulty, reason, rolling_avg, is_reviewed
- [ ] `student_progress` — student_id FK, level_id FK, difficulty_played, score, stars, attempts, time_taken_seconds, local_id (unique), unique composite
- [ ] `boss_results` — student_id FK, boss_battle_id FK, difficulty_played, score, hp_dealt, local_id (unique), unique composite
- [ ] `student_badges` — pivot: student_id + badge_id composite PK, earned_at
- [ ] `badges` — name, description, icon, trigger_type
- [ ] `grade_records` — student_id FK, subject_id FK, quarter_number, written_work, performance_task, quarterly_assessment, final_grade, unique composite
- [ ] `screen_time_logs` — student_id FK, log_date, total_minutes, levels_played, last_active_at, unique composite
- [ ] `screen_time_settings` — scope (string, cast via `ScreenTimeScope` enum), scope_id, limits, play windows
- [ ] `at_risk_alerts` — student_id FK, subject_id FK, quarter_number, grade_at_flag, is_resolved, unique composite
- [ ] Laravel's built-in `jobs` table migration (for queue): `php artisan queue:table`
- [ ] Laravel's built-in `failed_jobs` table
- [ ] Laravel's built-in `personal_access_tokens` table (Sanctum)

**Deliverable:** `php artisan migrate` runs successfully; all tables created.

### Task 3b: PHP Backed Enums

Create all enum classes in `app/Enums/` per Section 3a of the Server Plan:

- [ ] `UserRole.php` — backed string enum: `Student`, `Teacher`, `SuperAdmin`
- [ ] `DifficultyLevel.php` — backed string enum: `Easy`, `Standard`, `Hard`
- [ ] `QuestionType.php` — backed string enum: `MultipleChoice`, `TrueOrFalse`, `Identification`, `Matching`, `Sequencing`
- [ ] `TextSize.php` — backed string enum: `Small`, `Medium`, `Large`
- [ ] `Language.php` — backed string enum: `English`, `Filipino`
- [ ] `ScreenTimeScope.php` — backed string enum: `Global`, `ClassScope`, `Student`
- [ ] `DifficultySetBy.php` — backed string enum: `System`, `Teacher`
- [ ] Register enum casts on all relevant Eloquent models (e.g., `User::$casts`, `StudentDifficulty::$casts`)

**Deliverable:** All 7 enums created; models use `$casts` for type-safe enum access.

### Task 4: All Database Seeders

- [ ] `SuperAdminSeeder` — creates registrar account with password from `ADMIN_INITIAL_PASSWORD` env; uses `UserRole::SuperAdmin` enum
- [ ] `SubjectSeeder` — 3 subjects x 2 grades = 6 records (English/Library Dungeon, Science/Lab Cave, Health+PE/Sports Arena) with grade-specific level titles
- [ ] `QuarterSeeder` — 4 quarters per subject (24 total)
- [ ] `LevelSeeder` — 4 levels per quarter (48 total) with titles from the curriculum grid, matatag_competency_code placeholders
- [ ] `BossSeeder` — Word Warden (English Q1-Q4), Contaminus (Science Q1-Q4), Junklord (Health+PE Q1/Q3), Idle Rex (Health+PE Q2/Q4) — 12 boss battles total
- [ ] `BadgeSeeder` — 6 badge types (First Boss Defeat, Three-Star Level, Quarter Complete, Full World Complete, 3-Day Streak, Screen Time Compliant)
- [ ] `ScreenTimeSettingSeeder` — 1 global default row (school: 45min/2levels/15:00-20:00, weekend: 60min/3levels/08:00-20:00)
- [ ] Register all seeders in `DatabaseSeeder.php` in correct order
- [ ] Run `php artisan db:seed` and verify all data created

**Deliverable:** Database populated with all reference data; `php artisan db:seed` is idempotent.

### Task 5: Sanctum Installation & Configuration

- [ ] Publish Sanctum config: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
- [ ] Add Sanctum middleware to API route group
- [ ] Configure token expiration in `config/sanctum.php`
- [ ] Configure `SANCTUM_STATEFUL_DOMAINS` in `.env`

**Deliverable:** Sanctum ready for token-based auth on API routes.

### Task 6: Student Authentication (LRN + PIN -> Sanctum Token)

- [ ] Create `AuthController.php` with `login()` method
- [ ] Create `StudentLoginRequest.php` — validate `lrn` (required, 12 digits) and `pin` (required, 6 digits)
- [ ] Lookup user by LRN via `student_profiles` table
- [ ] Verify PIN with `Hash::check()` against stored hash
- [ ] Check `is_active` flag — reject inactive accounts
- [ ] Issue Sanctum token on success; return token + student basic info
- [ ] Update `last_login_at` on student profile
- [ ] Create `logout()` method — revoke current token
- [ ] Register routes in `routes/api.php`:
  - `POST /api/v1/auth/login` (public)
  - `POST /api/v1/auth/logout` (auth:sanctum)
- [ ] Apply rate limiting: 5 attempts/minute on login

**Deliverable:** Student can authenticate via API and receive a token.

### Task 7: Web Session Authentication (Teacher + Admin)

- [ ] Create `WebAuthController.php` with `showLogin()`, `login()`, `logout()`
- [ ] Create `WebLoginRequest.php` — validate username + password
- [ ] Login checks `role` — redirects to `/admin/dashboard` or `/teacher/class`
- [ ] Session-based auth using Laravel's built-in session driver
- [ ] Register routes in `routes/web.php`:
  - `GET /login` — login form
  - `POST /login` — authenticate
  - `POST /logout` — destroy session

**Deliverable:** Teacher and Admin can log in via web browser and get redirected to their dashboards.

### Task 8: Role-Based Middleware (3 Guards)

- [ ] Create `EnsureIsStudent.php` — checks `$request->user()->role === UserRole::Student`; returns 403 JSON if not
- [ ] Create `EnsureIsTeacher.php` — checks role is `UserRole::Teacher`; redirects to login if web, 403 if API
- [ ] Create `EnsureIsSuperAdmin.php` — checks role is `UserRole::SuperAdmin`; redirects to login if web, 403 if API
- [ ] Register all three in `bootstrap/app.php` (Laravel 13 middleware registration)
- [ ] Apply to route groups:
  - `/api/v1/student/*` -> `auth:sanctum` + `EnsureIsStudent`
  - `/teacher/*` -> `auth` + `EnsureIsTeacher`
  - `/admin/*` -> `auth` + `EnsureIsSuperAdmin`

**Deliverable:** Unauthorized access returns proper 403/redirect responses.

---

## Day 2 — Account Chain, Basic API & Dashboards

### Task 9: SuperAdmin Creates Teacher Account

- [ ] Create `Admin\TeacherController.php` with `create()` and `store()` methods
- [ ] Create `CreateTeacherRequest.php` — validate full_name, username (unique), grade, section
- [ ] Generate temporary password (random 8-char alphanumeric)
- [ ] Create user with `role='teacher'`, `must_change_password=true`
- [ ] Return the temporary password to the admin (displayed once on screen)
- [ ] Create basic Blade view: `resources/views/admin/teachers/create.blade.php`

**Deliverable:** SuperAdmin can create teacher accounts with temporary passwords.

### Task 10: Teacher Creates Student Account

- [ ] Create `Actions/CreateStudent.php` with `handle()` method (per Section 6.1 of Server Plan)
- [ ] Create `Teacher\StudentController.php` with `create()` and `store()` methods
- [ ] Create `CreateStudentRequest.php` — validate full_name, lrn (unique, 12 digits), grade, section
- [ ] Auto-generate 6-digit PIN; hash for storage, return plaintext once
- [ ] Seed default preferences (all defaults from migration)
- [ ] Seed default difficulty (`DifficultyLevel::Standard`) for all 3 subjects
- [ ] Create credential slip view: `resources/views/teacher/students/credential-slip.blade.php`
  - Displays: Student Name, LRN, PIN, Teacher Name, Date
  - Print-friendly CSS layout
- [ ] Create basic Blade view: `resources/views/teacher/students/create.blade.php`

**Deliverable:** Teacher can create students and print credential slips with the auto-generated PIN.

### Task 11: Forced Password Change Flow

- [ ] Create `EnsurePasswordChanged.php` middleware
- [ ] If `must_change_password === true`, redirect teacher to `/teacher/change-password`
- [ ] Create `Teacher\PasswordController.php` with `showChangeForm()` and `update()`
- [ ] Validate: current password matches, new password confirmed, min 8 characters
- [ ] On success: set `must_change_password = false`, redirect to `/teacher/class`
- [ ] Apply middleware to all `/teacher/*` routes (except `/teacher/change-password` and `/logout`)

**Deliverable:** Teachers are forced to change their temporary password before accessing any dashboard feature.

### Task 12: Student API — Worlds Endpoint

- [ ] Create `Student\WorldController.php` with `index()` method
- [ ] Return all 3 subjects as "worlds" with:
  - Subject name, grade, world_theme, color_hex
  - Quarters with `current_unlock_week` and `is_globally_unlocked`
  - Levels with unlock status (basic — just return seeded data for now)
  - Student's difficulty setting per subject
- [ ] Register route: `GET /api/v1/student/worlds` (student middleware)
- [ ] Create `WorldResource.php` and `QuarterResource.php` API Resources

**Deliverable:** Authenticated student can fetch all 3 worlds with seeded curriculum data.

### Task 13: Student API — Sync State Endpoint

- [ ] Create `Student\SyncController.php` with `state()` method
- [ ] Return a combined JSON payload containing:
  - `worlds` — same as worlds endpoint
  - `preferences` — student's preference settings
  - `difficulties` — per-subject difficulty settings
  - `screen_time` — current limits (resolve from global default for now)
  - `badges` — earned badges (empty array initially)
  - `grades` — grade records (empty array initially)
- [ ] Register route: `GET /api/v1/student/sync/state` (student middleware)

**Deliverable:** Authenticated student can pull their full state in one request.

### Task 14: Basic Admin Dashboard (Blade Page)

- [ ] Create layout: `resources/views/layouts/admin.blade.php` (Tailwind CDN for now)
- [ ] Create `Admin\DashboardController.php` with `index()` method
- [ ] Dashboard page shows:
  - Total teachers count
  - Total students count
  - Total active classes
  - Navigation links to: Teacher Management, Students (read-only list)
- [ ] Create `resources/views/admin/dashboard.blade.php`
- [ ] Ensure navigation includes "Create Teacher" link (from Task 9)

**Deliverable:** SuperAdmin sees a functional overview page after login.

### Task 15: Basic Teacher Dashboard (Blade Page)

- [ ] Create layout: `resources/views/layouts/teacher.blade.php` (Tailwind CDN for now)
- [ ] Create `Teacher\ClassController.php` with `index()` method
- [ ] Class page shows:
  - List of students belonging to this teacher
  - Columns: Name, LRN, Grade, Section
  - Navigation links to: Create Student, Change Password
- [ ] Create `resources/views/teacher/class.blade.php`
- [ ] Create `resources/views/teacher/students/index.blade.php` (redirect to class for now)

**Deliverable:** Teacher sees their student list and can navigate to student creation.

### Task 16: Postman Collection

- [ ] Create Postman collection: `NutriMind API v1`
- [ ] Add environment variables: `base_url`, `student_token`, `admin_session`
- [ ] Document all Phase 1 endpoints:
  - `POST /api/v1/auth/login` — with sample LRN + PIN
  - `POST /api/v1/auth/logout` — with Bearer token
  - `GET /api/v1/student/worlds` — with Bearer token
  - `GET /api/v1/student/sync/state` — with Bearer token
- [ ] Include expected response schemas as examples
- [ ] Export collection as JSON to `docs/postman/NutriMind_API_v1.json`

**Deliverable:** Complete Postman collection for all Phase 1 API endpoints.

### Task 17: Smoke Test — End-to-End Verification

- [ ] **Fresh start test:** `php artisan migrate:fresh --seed` runs without errors
- [ ] **Admin login:** Log in as `registrar` via web -> dashboard loads
- [ ] **Create teacher:** Admin creates a teacher -> temp password displayed
- [ ] **Teacher login:** Teacher logs in with temp password -> forced to change password
- [ ] **Password change:** Teacher changes password -> redirected to class page
- [ ] **Create student:** Teacher creates a student -> credential slip shows LRN + PIN
- [ ] **Student API login:** `POST /api/v1/auth/login` with LRN + PIN -> token returned
- [ ] **Worlds fetch:** `GET /api/v1/student/worlds` with token -> 3 worlds with levels returned
- [ ] **Sync state:** `GET /api/v1/student/sync/state` with token -> full state payload returned
- [ ] **Role enforcement:** Student token cannot access `/admin` or `/teacher`; teacher session cannot access `/admin`
- [ ] **Rate limiting:** 6th rapid login attempt returns 429 Too Many Requests
- [ ] Document any issues found and resolve before marking Phase 1 complete

**Deliverable:** The full account chain works end-to-end; demo-ready for capstone panel.

---

## Verification Checklist

Before marking Phase 1 as **10% complete**, all of the following must pass:

- [ ] `php artisan migrate:fresh --seed` runs without errors
- [ ] SuperAdmin can log in via web and access `/admin/dashboard`
- [ ] SuperAdmin can create a Teacher account with a temporary password
- [ ] Teacher can log in and is forced to change password before proceeding
- [ ] Teacher can create a Student account and view the credential slip
- [ ] Student can authenticate via `POST /api/v1/auth/login` and receive a token
- [ ] Student can fetch worlds via `GET /api/v1/student/worlds`
- [ ] Student can fetch full state via `GET /api/v1/student/sync/state`
- [ ] Role middleware blocks unauthorized access (403/redirect)
- [ ] Login rate limiting works (429 after 5 rapid attempts)
- [ ] All Postman requests return expected responses
- [ ] No PHP warnings or errors in `storage/logs/laravel.log`

---

## Files Created/Modified in Phase 1

```
app/Actions/CreateStudent.php
app/Enums/UserRole.php
app/Enums/DifficultyLevel.php
app/Enums/QuestionType.php
app/Enums/TextSize.php
app/Enums/Language.php
app/Enums/ScreenTimeScope.php
app/Enums/DifficultySetBy.php
app/Http/Controllers/Auth/AuthController.php
app/Http/Controllers/Auth/WebAuthController.php
app/Http/Controllers/Student/WorldController.php
app/Http/Controllers/Student/SyncController.php
app/Http/Controllers/Teacher/StudentController.php
app/Http/Controllers/Teacher/ClassController.php
app/Http/Controllers/Teacher/PasswordController.php
app/Http/Controllers/Admin/TeacherController.php
app/Http/Controllers/Admin/DashboardController.php
app/Http/Middleware/EnsureIsStudent.php
app/Http/Middleware/EnsureIsTeacher.php
app/Http/Middleware/EnsureIsSuperAdmin.php
app/Http/Middleware/EnsurePasswordChanged.php
app/Http/Requests/Auth/StudentLoginRequest.php
app/Http/Requests/Auth/WebLoginRequest.php
app/Http/Requests/Teacher/CreateStudentRequest.php
app/Http/Requests/Admin/CreateTeacherRequest.php
app/Http/Resources/WorldResource.php
app/Http/Resources/QuarterResource.php
app/Models/*.php (all 18 models)
database/migrations/*.php (17+ migrations)
database/seeders/*.php (7 seeders + DatabaseSeeder)
resources/views/layouts/admin.blade.php
resources/views/layouts/teacher.blade.php
resources/views/admin/dashboard.blade.php
resources/views/admin/teachers/create.blade.php
resources/views/teacher/class.blade.php
resources/views/teacher/students/create.blade.php
resources/views/teacher/students/credential-slip.blade.php
resources/views/teacher/change-password.blade.php
resources/views/auth/login.blade.php
routes/api.php
routes/web.php
docs/postman/NutriMind_API_v1.json
```

---

*Phase 1 -> Phase 2: Once the 10% milestone passes, proceed to [Phase 2 — Core Gameplay API](./phase-2-core-gameplay-api.md)*
