# Phase 7 — QA, Exports & Deployment

> **Milestone:** 100% | **Timeline:** Weeks 10-11
> **Goal:** Production-grade exports, comprehensive testing, deployment to hosting, final polish. The server is ready for Unity integration and capstone panel presentation.

---

## Prerequisites

- Phase 6 complete (80% milestone passed)
- Both dashboards (teacher + admin) fully functional and rendering correctly
- All API endpoints, services, and jobs operational
- No known critical bugs from previous phases

---

## Week 10 — Exports, Testing & Polish

### Task 1: DepEd Grade Sheet Export

- [ ] Create `app/Exports/GradeSheetExport.php` using `Maatwebsite/Laravel-Excel`
- [ ] Accept filters: class (teacher_id), subject, quarter
- [ ] Export format (DepEd-aligned CSV columns):
  - Student Name, LRN, Grade, Section
  - Written Work (25%), Performance Task (50%), Quarterly Assessment (25%)
  - Final Grade, Remarks (Passed >= 75 / Failed < 75)
- [ ] Sort by student name (alphabetical)
- [ ] Wire to teacher dashboard: `GET /teacher/grades/export?subject=&quarter=`
- [ ] Wire to admin dashboard: `GET /admin/reports/export?teacher=&subject=&quarter=`
- [ ] Update `ReportController@export` to use `GradeSheetExport`
- [ ] Test: downloaded CSV opens correctly in Microsoft Excel and Google Sheets

**Deliverable:** Teachers and admin can download DepEd-formatted grade sheets.

### Task 2: Credential Slip Export

- [ ] Create `app/Exports/CredentialSlipExport.php`
  - Generates a printable HTML view (or PDF via `barryvdh/laravel-dompdf` if available)
  - Content per slip: student name, LRN, 6-digit PIN, teacher name, school name, date generated
  - Styled for clean printing: large font, clear layout, one slip per student
- [ ] Single student slip: `GET /teacher/students/{student}/credential-slip`
- [ ] Bulk slips after CSV import: download all generated slips as a single printable page
- [ ] Test: print preview shows correct formatting on A4 paper

**Deliverable:** Teachers can print credential slips for distribution to students.

### Task 3: Finalize Postman Collection

- [ ] Organize all endpoints into folders:
  - Auth (login, logout)
  - Student — Content (worlds, questions, badges, grades, preferences)
  - Student — Progress (level, boss, sync, state)
  - Student — Screen Time (status, heartbeat, history)
  - Teacher — Students (create, import, reset PIN, credential slip)
  - Teacher — Difficulty (index, per-student, class-wide, advisory review)
  - Teacher — Screen Time (class, per-student, override remove)
  - Teacher — Grades & Alerts (grade list, export, at-risk)
  - Teacher — Unlock (status, update)
  - Admin — Dashboard, Teachers, Students, Content, Settings, Reports
- [ ] Add environment variables: `base_url`, `sanctum_token`, `teacher_session`
- [ ] Add pre-request scripts for auth token injection
- [ ] Add test scripts per endpoint: status code checks, response structure validation
- [ ] Add sample request bodies for all POST/PUT endpoints
- [ ] Verify every endpoint returns correct responses against the spec

**Deliverable:** Complete, organized Postman collection ready for Unity team and capstone panel.

### Task 4: Unit Tests — Services

- [ ] `ComputeGradeTest`:
  - Test DepEd formula: WW 25% + PT 50% + QA 25% = correct final grade
  - Test with missing components (no boss result yet -> quarterly_assessment null)
  - Test attempt quality factor: 1 attempt = 1.00, 2 = 0.95, 3+ = 0.90
- [ ] `ScreenTimeServiceTest`:
  - Test three-tier resolution: student > class > global
  - Test heartbeat increments correctly
  - Test `warning_flag` triggers at <= 15 minutes remaining
  - Test `blocked` when time exhausted / levels exhausted / outside window
  - Test weekend vs school day detection
- [ ] `EvaluateDifficultyAdvisoryTest`:
  - Test advisory created when avg >= 90% (suggest harder)
  - Test advisory created when avg < 60% (suggest easier)
  - Test no advisory when fewer than 3 records exist
  - Test advisory not created when already at max/min difficulty
- [ ] `AwardBadgeTest`:
  - Test each badge trigger: first boss, three-star, quarter complete, world complete, 3-day streak, screen time compliant
- [ ] `ProcessSyncTest`:
  - Test deduplication by `local_id`
  - Test `GREATEST()` for screen time
  - Test mixed level + boss records processed correctly
  - Test preferences merged from sync payload
- [ ] `ResolveUnlockStatusTest`:
  - Test level accessible logic: unlock_week + previous level complete
  - Test boss accessible logic: all 4 levels complete
  - Test cross-subject quarter gate: all 3 bosses required

**Deliverable:** Core business logic has automated test coverage.

### Task 5: Integration Tests — Critical Flows

- [ ] **Full student lifecycle test:**
  - SuperAdmin creates Teacher → Teacher creates Student → Student logs in → Student gets sync state → Student submits level → Student submits boss → Grade computed → Badge awarded
- [ ] **Offline sync test:**
  - Submit 3 level records + 1 boss record via sync endpoint → verify all saved, grade computed, badges checked
  - Re-submit same records → verify deduplication (no duplicates created)
- [ ] **Difficulty flow test:**
  - Student submits 3 levels with avg >= 90% → advisory created → teacher accepts → difficulty updated → next question request returns higher-tier questions
- [ ] **Screen time flow test:**
  - Teacher sets class limit to 10 min → student heartbeats 10 times → student is blocked
  - Teacher sets per-student override to 20 min → student is no longer blocked
  - Teacher removes override → student reverts to class limit and is blocked again
- [ ] **At-risk flow test:**
  - Student has final_grade < 75 → run `nutrimind:check-at-risk` → alert created
  - Student improves to >= 75 → run command again → alert auto-resolved

**Deliverable:** End-to-end critical paths verified with automated tests.

### Task 6: Dashboard Polish

- [ ] Review all Blade views for consistent Tailwind styling
- [ ] Verify responsive layout works on 1024px+ screens (dashboard is desktop-first)
- [ ] Verify Livewire 4 interactive components work: difficulty manager, screen time manager, grade table, teacher manager, question bank editor
- [ ] Verify Alpine.js (bundled with Livewire) small UI interactions work: confirmation dialogs, tooltip toggles
- [ ] Verify CSRF tokens on all forms
- [ ] Verify error handling: form validation errors displayed inline, server errors show user-friendly messages
- [ ] Verify all navigation links work (no dead links)
- [ ] Verify pagination works on student lists (50 per page)
- [ ] Add loading spinners for AJAX operations

**Deliverable:** Dashboards are polished and ready for presentation.

---

## Week 11 — Deployment & Final Checks

### Task 7: Production Environment Setup

- [ ] Create `.env.example` with all variables documented (from Server Plan Section 12)
- [ ] Create production `.env` configuration:
  - `APP_ENV=production`, `APP_DEBUG=false`
  - `APP_TIMEZONE=Asia/Manila`
  - Database credentials (strong password)
  - `QUEUE_CONNECTION=database`
  - `SESSION_DRIVER=database` (or `file`)
  - `LOG_CHANNEL=daily`, `LOG_LEVEL=warning`
- [ ] Run `php artisan key:generate` for production
- [ ] Configure HTTPS (SSL certificate)
- [ ] Set up MySQL 8 database on hosting
- [ ] Configure CORS for Unity app (mobile clients)

**Deliverable:** Production environment configured and secured.

### Task 8: Database Deployment

- [ ] Run all migrations on production database: `php artisan migrate --force`
- [ ] Run seeders in order:
  1. `SuperAdminSeeder` — creates registrar account
  2. `SubjectSeeder` — English, Science, Health+PE
  3. `QuarterSeeder` — 4 quarters per subject (12 total)
  4. `LevelSeeder` — 4 levels per quarter (48 total)
  5. `BossSeeder` — Word Warden, Contaminus, Junklord, Idle Rex mapped to quarters
  6. `BadgeSeeder` — all badge definitions
  7. `ScreenTimeSettingSeeder` — global defaults (school day 45min/2 levels, weekend 60min/3 levels)
- [ ] Verify seeded data: `php artisan tinker` → check counts for all tables
- [ ] Set up scheduled task for at-risk check: add `php artisan schedule:run` to cron (every minute)
- [ ] Set up queue worker: `php artisan queue:work --daemon` or supervisor process

**Deliverable:** Production database seeded and background processes running.

### Task 9: Deployment to Hosting

- [ ] **Option A — cPanel Shared Hosting:**
  - Upload codebase via Git or file manager
  - Set document root to `public/` folder
  - Configure `.htaccess` for Laravel
  - Set up cron job: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`
  - Set up queue worker via supervisor or cron-based `queue:work --stop-when-empty`
- [ ] **Option B — VPS:**
  - Install PHP 8.5, Composer, MySQL 8, Nginx/Apache
  - Clone repository, install dependencies: `composer install --no-dev --optimize-autoloader`
  - Configure Nginx virtual host pointing to `public/`
  - Set up Supervisor for queue worker
  - Set up cron for scheduler
  - Configure SSL via Let's Encrypt
- [ ] Run post-deployment commands:
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`
  - `php artisan optimize`
- [ ] Verify deployment:
  - Login page loads at production URL
  - SuperAdmin can log in with seeded credentials
  - API endpoint responds: `POST /api/v1/auth/login` with test credentials

**Deliverable:** Server deployed and accessible at production URL.

### Task 10: Security Hardening

- [ ] Verify `APP_DEBUG=false` in production
- [ ] Verify rate limiting is active on all configured endpoints
- [ ] Verify CSRF protection on all web POST/PUT/DELETE routes
- [ ] Verify Sanctum token authentication blocks unauthorized API access
- [ ] Verify role middleware blocks cross-role access (teacher can't access admin routes and vice versa)
- [ ] Verify error pages: 404, 403, 500 show user-friendly messages (no stack traces)
- [ ] Verify `.env` is NOT accessible via web (returns 403/404)
- [ ] Verify `storage/` and `database/` are not web-accessible
- [ ] Change SuperAdmin default password after first login
- [ ] Test: 6+ rapid login attempts trigger rate limit (429 response)

**Deliverable:** Production server is hardened against common attack vectors.

### Task 11: End-to-End Smoke Test (Production)

- [ ] **Account chain:**
  1. SuperAdmin logs in → creates Teacher account → verifies temp password shown
  2. Teacher logs in → forced password change → new password works
  3. Teacher creates Student → PIN shown → credential slip printable
  4. Student logs in via API (LRN + PIN) → Sanctum token returned
- [ ] **Gameplay flow:**
  5. Student calls `GET /api/v1/student/sync/state` → receives worlds, difficulties, screen time
  6. Student calls `GET /api/v1/student/worlds` → 3 worlds with unlock status
  7. Student calls `GET /api/v1/student/levels/{id}/questions` → questions filtered by difficulty
  8. Student calls `POST /api/v1/student/progress/level` → progress saved
  9. Student calls `POST /api/v1/student/progress/boss` → grade computed, badges checked
- [ ] **Screen time:**
  10. Student calls `POST /api/v1/student/screentime/heartbeat` → status returned
  11. Teacher updates class screen time → next heartbeat reflects new limits
- [ ] **Difficulty:**
  12. Teacher changes student difficulty → student's next question request returns different tier
- [ ] **Sync:**
  13. Student calls `POST /api/v1/student/progress/sync` with mixed records → all processed
  14. Re-submit same sync payload → duplicates skipped
- [ ] **Dashboards:**
  15. Teacher dashboard shows all student data correctly
  16. Admin dashboard shows school-wide overview
  17. Grade export downloads correct CSV
  18. Credential slip prints cleanly

**Deliverable:** Complete end-to-end verification on production server.

### Task 12: Documentation & Handoff

- [ ] Verify `.env.example` includes all required variables with comments
- [ ] Verify Postman collection is complete and exported as JSON
- [ ] Create brief deployment guide in `docs/deployment.md`:
  - Server requirements (PHP 8.5, MySQL 8, Composer)
  - Step-by-step deployment instructions
  - Seeder run order
  - Cron and queue setup
  - SSL configuration notes
- [ ] Create API quick-reference in `docs/api-reference.md`:
  - All endpoints grouped by role
  - Auth flow for Unity integration
  - Sync payload format
  - Screen time heartbeat contract
- [ ] Record the SuperAdmin credentials securely (not in Git)
- [ ] Verify Git repository is clean: no `.env`, no vendor/, no node_modules/

**Deliverable:** Documentation complete for capstone panel and Unity team handoff.

---

## Verification Checklist

Before marking Phase 7 as **100% complete**, all of the following must pass:

### Exports
- [ ] DepEd grade sheet CSV exports correctly with proper columns and formatting
- [ ] Grade export opens correctly in Microsoft Excel and Google Sheets
- [ ] Credential slip prints cleanly with student name, LRN, PIN, teacher, school
- [ ] Bulk credential slips after CSV import are downloadable

### Testing
- [ ] All action/service unit tests pass (ComputeGrade, ScreenTime, EvaluateDifficultyAdvisory, AwardBadge, ProcessSync, ResolveUnlockStatus)
- [ ] All integration tests pass (student lifecycle, sync, difficulty flow, screen time flow, at-risk)
- [ ] No test failures or skipped critical tests

### Deployment
- [ ] Server accessible at production URL via HTTPS
- [ ] SuperAdmin can log in and access admin dashboard
- [ ] Teacher can log in and access teacher dashboard
- [ ] Student can authenticate via API and receive Sanctum token
- [ ] Queue worker processing jobs (grade computation, difficulty advisory)
- [ ] Scheduler running (at-risk check fires daily at midnight)
- [ ] All database seeders ran successfully (3 subjects, 12 quarters, 48 levels, 4+ bosses, badges, global settings)

### Security
- [ ] `APP_DEBUG=false` confirmed in production
- [ ] Rate limiting active on login, heartbeat, and progress endpoints
- [ ] CSRF protection active on all web forms
- [ ] `.env` file not accessible via browser
- [ ] Error pages show friendly messages without stack traces
- [ ] Default SuperAdmin password has been changed

### End-to-End
- [ ] Full account chain works: SuperAdmin → Teacher → Student → API Login
- [ ] Full gameplay flow works: sync state → questions → submit level → submit boss → grades → badges
- [ ] Screen time enforcement works: heartbeat → warning → blocked
- [ ] Difficulty flow works: teacher sets → student sees filtered questions
- [ ] Offline sync works: batch upload → deduplication → grade computation
- [ ] Both dashboards render correctly with real data
- [ ] Postman collection validated against live server
- [ ] Documentation complete (deployment guide + API reference)

---

## Files Created/Modified in Phase 7

```
# Exports
app/Exports/GradeSheetExport.php
app/Exports/CredentialSlipExport.php

# Documentation
docs/deployment.md
docs/api-reference.md
docs/postman/NutriMind_API_v1.json (finalized)

# Environment
.env.example (finalized with all variables)

# Tests
tests/Unit/ComputeGradeTest.php
tests/Unit/ScreenTimeServiceTest.php
tests/Unit/EvaluateDifficultyAdvisoryTest.php
tests/Unit/AwardBadgeTest.php
tests/Unit/ProcessSyncTest.php
tests/Unit/ResolveUnlockStatusTest.php
tests/Feature/StudentLifecycleTest.php
tests/Feature/OfflineSyncTest.php
tests/Feature/DifficultyFlowTest.php
tests/Feature/ScreenTimeFlowTest.php
tests/Feature/AtRiskFlowTest.php

# Views (polish)
resources/views/errors/404.blade.php
resources/views/errors/403.blade.php
resources/views/errors/500.blade.php
```

---

*Phase 7 complete — Server-side development at 100%. Ready for Unity client integration and capstone panel presentation.*

*University of Eastern Pangasinan | Capstone Project — NutriMind*
*MATATAG Curriculum Aligned | Tayug Central Elementary School | Grade 5 & 6*
