# Phase 6 — Teacher Dashboard + Admin Dashboard

> **Milestone:** 80% | **Timeline:** Weeks 8-9
> **Goal:** Both web dashboards are fully functional with Blade + Livewire 4 + Tailwind (Alpine.js bundled). Teachers can manage their class end-to-end; Super Admin has school-wide oversight and content management. Complex interactive pages use Livewire components for server-driven reactivity; Alpine.js (auto-included with Livewire) handles small UI interactions.

---

## Prerequisites

- Phase 5 complete (65% milestone passed)
- All API endpoints functional: auth, gameplay, progress, sync, difficulty, screen time, alerts
- All services operational: grades, badges, screen time, adaptive difficulty, at-risk
- Rate limiting configured and tested

---

## Week 8 — Teacher Dashboard

### Task 1: Dashboard Layout & Web Auth

- [ ] Set up Blade layout with Tailwind CSS and Livewire 4 (Alpine.js bundled automatically)
  - Base layout: `resources/views/layouts/teacher.blade.php`
  - Sidebar navigation: Class Overview, Students, Difficulty, Screen Time, Grades, Unlock, Alerts
  - Top bar: teacher name, section/grade info, logout button
  - Use self-closing Livewire component tags: `<livewire:component-name />`
  - Use `Route::livewire()` for Livewire page components (v4 recommended routing)
- [ ] Configure web auth routes in `routes/web.php`:
  - `GET /login` — shared login page (teacher + admin)
  - `POST /login` — `WebAuthController@login` (session-based)
  - `POST /logout` — `WebAuthController@logout`
- [ ] Implement `WebAuthController`:
  - Authenticate via `username + password`
  - Check `must_change_password` flag — redirect to password change form if true
  - Route to `/teacher` or `/admin` based on role
- [ ] Create password change flow:
  - `GET /change-password` — form for new password
  - `POST /change-password` — validate, update, clear `must_change_password` flag
- [ ] Apply `EnsureIsTeacher` middleware to all `/teacher/*` routes
- [ ] Apply `EnsureIsSuperAdmin` middleware to all `/admin/*` routes

**Deliverable:** Web login works for both roles with forced password change on first login.

### Task 2: Class Overview Page

- [ ] Create `resources/views/teacher/class/index.blade.php`
- [ ] Student table columns:
  - Name, LRN, Grade, Section
  - Per-subject difficulty badges (Easy = green, Standard = blue, Hard = red)
  - Overall progress (completed levels / total available)
  - At-risk badge (red flag if active unresolved alert)
- [ ] Sortable columns: name, progress, at-risk status
- [ ] Search/filter bar: filter by name or LRN
- [ ] Pending advisory banner: "X students have difficulty suggestions waiting for review" (links to difficulty page)
- [ ] Click on student row → navigates to student detail page

**Deliverable:** Teacher sees all students at a glance with difficulty, progress, and alert indicators.

### Task 3: Student Detail Page

- [ ] Create `resources/views/teacher/students/show.blade.php`
- [ ] Sections:
  - **Profile:** name, LRN, grade, section, last login, account status
  - **Grade Breakdown:** per-quarter table (Written Work 25% | Performance Task 50% | Quarterly Assessment 25% | Final Grade) for each subject
  - **Difficulty History:** per-subject — current setting, set by whom, when last changed
  - **MATATAG Competency Breakdown:** per-level competency code + description, completion status
  - **Level Completion Grid:** all 48 levels in a grid showing star ratings (0-3), organized by subject > quarter > level
  - **Screen Time Chart:** bar chart of last 7 days (total_minutes per day) using Alpine.js (bundled with Livewire) + inline SVG or a lightweight chart library
- [ ] Back button to class overview

**Deliverable:** Teacher can drill into any student's full performance and history.

### Task 4: Student Creation & Management

- [ ] Create `resources/views/teacher/students/create.blade.php`
  - Form: full_name, LRN (12-digit), grade (5 or 6), section
  - On submit: calls `CreateStudent` action `handle()` method
  - Success: shows the generated 6-digit PIN in a one-time display + "Print Credential Slip" button
- [ ] PIN reset functionality:
  - Button on student detail page: "Reset PIN"
  - Confirmation modal before executing
  - Shows new PIN once, then it's gone forever
- [ ] Credential slip:
  - `GET /teacher/students/{student}/credential-slip`
  - Generates a printable view (or PDF) with: student name, LRN, PIN, teacher name, school name
  - Styled for printing on paper (clean, minimal, large font)
- [ ] Bulk CSV import:
  - `GET /teacher/students/import` — upload form
  - `POST /teacher/students/import` — validate CSV (columns: full_name, lrn, grade, section)
  - Process each row via `CreateStudent` action `handle()` method
  - Return summary: X created, Y failed (with error details per row)
  - Download link for all generated credential slips
- [ ] Edit student: update name, grade, section (LRN cannot change)
- [ ] Deactivate student: soft disable via `is_active = false`

**Deliverable:** Teacher can create, import, edit, and manage student accounts with PIN lifecycle.

### Task 5: Adaptive Difficulty Manager Page (Livewire)

- [ ] Create `app/Livewire/Teacher/DifficultyManager.php` Livewire component
- [ ] Create `resources/views/livewire/teacher/difficulty-manager.blade.php`
- [ ] Create wrapper `resources/views/teacher/difficulty/index.blade.php` that renders `@livewire('teacher.difficulty-manager')`
- [ ] Layout (per Server Plan Section 8):
  - **Class Default:** dropdown per subject + "Apply to All Students" button
  - **Advisory Notices:** banner showing pending (unreviewed) advisories with Accept/Dismiss buttons:
    - Student name, subject, rolling average, current -> suggested difficulty
  - **Per-Student Table:**
    - Columns: Name | English | Science | Health+PE
    - Each cell is a `<select wire:model.live="...">` dropdown bound to `DifficultyLevel` enum (Easy / Standard / Hard)
- [ ] Livewire actions replace AJAX calls:
  - `updateStudentDifficulty($studentId, $subject, $level)` — saves via `PUT /teacher/difficulty/student/{student}`
  - `applyToAll($subject, $level)` — saves via `PUT /teacher/difficulty/class`
  - `reviewAdvisory($advisoryId, $action)` — accept/dismiss via `POST /teacher/difficulty/advisories/{id}/review`
- [ ] Visual feedback: Livewire `wire:loading` indicators + `$dispatch('notify', ...)` flash on save

**Deliverable:** Teacher can manage difficulty settings interactively with live-updating controls.

### Task 6: Weekly Level Unlock Controls

- [ ] Create `resources/views/teacher/unlock/index.blade.php`
- [ ] One dropdown per subject (English, Science, Health+PE): values 0-4 representing current unlock week
  - Week 0 = no levels unlocked in this subject
  - Week 1-4 = levels up to that week are available
- [ ] `GET /teacher/unlock/status` — returns current `current_unlock_week` per quarter per subject
- [ ] `PUT /teacher/unlock/status` — updates `current_unlock_week` for selected subject/quarter
- [ ] Confirmation modal: "This will unlock Week X levels for [Subject]. Students will see new levels immediately. Continue?"
- [ ] Show which levels will become available (list level titles for the selected week)

**Deliverable:** Teacher controls the pacing of content release per subject.

### Task 7: Screen Time Manager Page (Livewire)

- [ ] Create `app/Livewire/Teacher/ScreenTimeManager.php` Livewire component
- [ ] Create `resources/views/livewire/teacher/screen-time-manager.blade.php`
- [ ] Create wrapper `resources/views/teacher/screentime/index.blade.php` that renders `@livewire('teacher.screen-time-manager')`
- [ ] Layout (per Server Plan Section 8):
  - **Class-Wide Settings:** form with all screen time fields:
    - School day: limit (min), max levels, play start, play end
    - Weekend: limit (min), max levels, play start, play end
    - "Update All Students" button → `wire:click="updateClassSettings"`
  - **Per-Student Overrides Table:**
    - Columns: Name | School Day Limit | Weekend Limit | Today's Usage | Action
    - `*` indicator = inheriting class setting; `~` indicator = student-level override active
    - "Edit" button → Livewire modal with per-student screen time form
    - "Clear" button → `wire:click="clearOverride($studentId)"` (removes student override)
- [ ] Today's usage column: `time_used_today / daily_limit` with a small progress bar
- [ ] Auto-refresh via Livewire polling: `wire:poll.30s` on the component root — updates usage data automatically

**Deliverable:** Teacher can manage screen time for class and individual students.

### Task 8: Grades & At-Risk Alerts (Livewire)

- [ ] Create `app/Livewire/Teacher/GradeTable.php` Livewire component
- [ ] Create `resources/views/livewire/teacher/grade-table.blade.php`
- [ ] Create wrapper `resources/views/teacher/grades/index.blade.php` that renders `@livewire('teacher.grade-table')`
  - Grade table: Students × Subjects × Quarters
  - Filter by subject and quarter via `wire:model.live` on select dropdowns
  - Show Written Work, Performance Task, Quarterly Assessment, Final Grade
  - Color-code: green (>= 85), yellow (75-84), red (< 75)
  - "Export" button → downloads DepEd-formatted CSV (wired in Phase 7)
- [ ] Create `resources/views/teacher/alerts/index.blade.php`
  - List of unresolved at-risk alerts
  - Each row: student name, subject, quarter, grade at flag time, date flagged
  - Click → navigates to student detail page
  - Color-coded severity: red (< 60), orange (60-74)

**Deliverable:** Teacher can view grades and manage at-risk student alerts.

---

## Week 9 — Super Admin Dashboard

### Task 9: Admin Dashboard Layout

- [ ] Create `resources/views/layouts/admin.blade.php`
  - Sidebar navigation: Dashboard, Teachers, Students, Content, Reports, Settings
  - Top bar: "System Administrator", logout button
- [ ] Create `resources/views/admin/dashboard.blade.php`
  - School-wide overview cards:
    - Total students (active)
    - Total teachers (active)
    - Active classes count
    - School-wide at-risk count
    - Overall completion rate (levels completed / levels available across all students)
  - Recent activity feed (last 10 at-risk alerts, last 10 teacher logins)

**Deliverable:** Super admin sees school-wide metrics at a glance.

### Task 10: Teacher Management (Livewire)

- [ ] Create `app/Livewire/Admin/TeacherManager.php` Livewire component
- [ ] Create `resources/views/livewire/admin/teacher-manager.blade.php`
- [ ] Create wrapper `resources/views/admin/teachers/index.blade.php` that renders `@livewire('admin.teacher-manager')`
  - Teacher table: name, username, grade/section, student count, status (active/inactive), last login
- [ ] Create `resources/views/admin/teachers/create.blade.php`
  - Form: full_name, username, grade, section
  - Auto-generate temporary password
  - Set `must_change_password = true`
  - Show credentials once on success
- [ ] Create `resources/views/admin/teachers/edit.blade.php`
  - Edit name, grade, section
  - "Deactivate" toggle — sets `is_active = false`; does NOT delete teacher or students
  - "Reset Password" button — generates new temp password, sets `must_change_password = true`

**Deliverable:** Super admin can create, edit, and deactivate teacher accounts.

### Task 11: School-Wide Student Oversight

- [ ] Create `resources/views/admin/students/index.blade.php`
  - Full student list across all classes
  - Filterable by: grade, section, teacher, at-risk status
  - Columns: name, LRN, grade, section, teacher name, overall progress, at-risk badge
  - Click → read-only student detail view (same data as teacher view, but not editable)
- [ ] Pagination: 50 students per page

**Deliverable:** Super admin can view any student across the school.

### Task 12: Question Bank Management (Livewire)

- [ ] Create `app/Livewire/Admin/QuestionBankEditor.php` Livewire component
- [ ] Create `resources/views/livewire/admin/question-bank-editor.blade.php`
- [ ] Create wrapper `resources/views/admin/content/index.blade.php` that renders `@livewire('admin.question-bank-editor')`
  - Browse by: Subject → Quarter → Level (48 levels total)
  - Each level shows: title, MATATAG competency code, question count, active/inactive count
- [ ] Create `resources/views/admin/content/level.blade.php`
  - Question list for the selected level
  - Each question shows: type, difficulty tier (1/2/3), preview of content, active/inactive toggle
- [ ] Create `resources/views/admin/content/question-form.blade.php`
  - Add/edit question form:
    - Question type dropdown: `QuestionType` enum values (multiple_choice, fill_blank, matching, drag_drop)
    - Difficulty tier: 1, 2, 3
    - Content JSON editor (structured fields for bilingual text — `en` and `fil` fields)
    - Correct answer JSON editor
    - Active/inactive toggle
  - Validation: ensure required JSON structure is present
- [ ] Boss question pool management:
  - View which questions are assigned to each boss battle
  - Add/remove questions from boss pools
  - Filter by difficulty tier

**Deliverable:** Super admin can manage the entire question bank with bilingual content.

### Task 13: Global Settings Pages

- [ ] Create `resources/views/admin/settings/screentime.blade.php`
  - Edit the `ScreenTimeScope::Global` screen time settings
  - Same fields as teacher screen time form
  - Note: "These are the defaults inherited by classes that haven't set their own limits"
- [ ] Create `resources/views/admin/settings/difficulty.blade.php`
  - Set the global default difficulty for new students
  - Dropdown: `DifficultyLevel` enum values — Easy / Standard / Hard (default: Standard)
  - Note: "This applies only to newly created student accounts"

**Deliverable:** Super admin can adjust school-wide default settings.

### Task 14: Admin Reports & Export Prep

- [ ] Create `resources/views/admin/reports/index.blade.php`
  - Filter: select classes (multi-select), quarter, subject
  - Preview table showing grade data
  - "Export CSV" button (wired to export logic in Phase 7)
- [ ] Create `app/Http/Controllers/Admin/ReportController.php`
  - `index()` — render report filter page with grade preview
  - `export()` — placeholder for Phase 7 (returns "Export coming in Phase 7" message for now)

**Deliverable:** Report page is ready for Phase 7 export functionality.

---

## Verification Checklist

Before marking Phase 6 as **80% complete**, all of the following must pass:

### Web Auth
- [ ] Teacher can log in with username + password
- [ ] Teacher with `must_change_password = true` is forced to change password before accessing dashboard
- [ ] Super admin can log in and is routed to `/admin`
- [ ] Non-authenticated users are redirected to login
- [ ] Teacher cannot access `/admin/*` routes; admin cannot access `/teacher/*` routes

### Teacher Dashboard
- [ ] Class overview shows all students with difficulty badges, progress, and at-risk flags
- [ ] Student detail page shows grade breakdown, difficulty history, MATATAG competencies, level grid, screen time chart
- [ ] Student creation generates PIN and shows credential slip
- [ ] Bulk CSV import creates multiple students and generates credential slips
- [ ] PIN reset generates new PIN and displays it once
- [ ] Difficulty manager: per-student dropdown saves instantly via Livewire action
- [ ] Difficulty manager: "Apply to All" updates all students in class
- [ ] Advisory accept changes difficulty; dismiss marks advisory as reviewed
- [ ] Level unlock controls update `current_unlock_week` with confirmation
- [ ] Screen time manager: class-wide update saves for all students
- [ ] Screen time manager: per-student edit creates override
- [ ] Screen time manager: "Clear" removes override, student reverts to class setting
- [ ] Grade table shows correct DepEd breakdown with color coding
- [ ] At-risk alerts list shows unresolved alerts with navigation to student detail

### Admin Dashboard
- [ ] Dashboard shows school-wide statistics (students, teachers, classes, at-risk count)
- [ ] Teacher creation generates temp password with forced change
- [ ] Teacher deactivation sets `is_active = false` without deleting data
- [ ] Student oversight shows all students filterable by grade, section, teacher
- [ ] Question bank: browse by subject → quarter → level with question counts
- [ ] Question add/edit form validates bilingual JSON structure
- [ ] Boss question pool management: add/remove questions from boss battles
- [ ] Global screen time settings update the `ScreenTimeScope::Global` row
- [ ] Global difficulty default setting is configurable
- [ ] Report page renders with filter controls (export placeholder in place)

### General
- [ ] All Blade views use Tailwind CSS for consistent styling
- [ ] Livewire 4 handles interactive components (difficulty manager, screen time, grade table, teacher manager, question bank)
- [ ] Alpine.js (bundled with Livewire) used for small UI interactions (modals, confirmation dialogs)
- [ ] CSRF tokens present on all POST/PUT/DELETE forms (Livewire handles this automatically)
- [ ] No regressions in any API endpoints from previous phases

---

## Files Created/Modified in Phase 6

```
# Web Auth
app/Http/Controllers/Auth/WebAuthController.php
resources/views/auth/login.blade.php
resources/views/auth/change-password.blade.php

# Layouts
resources/views/layouts/teacher.blade.php
resources/views/layouts/admin.blade.php

# Livewire Components — Teacher
app/Livewire/Teacher/DifficultyManager.php
app/Livewire/Teacher/ScreenTimeManager.php
app/Livewire/Teacher/GradeTable.php
resources/views/livewire/teacher/difficulty-manager.blade.php
resources/views/livewire/teacher/screen-time-manager.blade.php
resources/views/livewire/teacher/grade-table.blade.php

# Livewire Components — Admin
app/Livewire/Admin/TeacherManager.php
app/Livewire/Admin/QuestionBankEditor.php
app/Livewire/Admin/SchoolOverview.php
resources/views/livewire/admin/teacher-manager.blade.php
resources/views/livewire/admin/question-bank-editor.blade.php
resources/views/livewire/admin/school-overview.blade.php

# Teacher Dashboard Views (Blade wrappers + static pages)
resources/views/teacher/class/index.blade.php
resources/views/teacher/students/show.blade.php
resources/views/teacher/students/create.blade.php
resources/views/teacher/students/import.blade.php
resources/views/teacher/difficulty/index.blade.php       (wrapper → DifficultyManager)
resources/views/teacher/unlock/index.blade.php
resources/views/teacher/screentime/index.blade.php       (wrapper → ScreenTimeManager)
resources/views/teacher/grades/index.blade.php            (wrapper → GradeTable)
resources/views/teacher/alerts/index.blade.php

# Admin Dashboard Views (Blade wrappers + static pages)
resources/views/admin/dashboard.blade.php
resources/views/admin/teachers/index.blade.php            (wrapper → TeacherManager)
resources/views/admin/teachers/create.blade.php
resources/views/admin/teachers/edit.blade.php
resources/views/admin/students/index.blade.php
resources/views/admin/content/index.blade.php             (wrapper → QuestionBankEditor)
resources/views/admin/content/level.blade.php
resources/views/admin/content/question-form.blade.php
resources/views/admin/settings/screentime.blade.php
resources/views/admin/settings/difficulty.blade.php
resources/views/admin/reports/index.blade.php

# Controllers
app/Http/Controllers/Admin/ReportController.php
app/Http/Controllers/Teacher/UnlockController.php (web routes)

# Routes
routes/web.php (updated — all dashboard routes)
```

---

*Phase 6 -> Phase 7: Once the 80% milestone passes, proceed to [Phase 7 — QA, Exports & Deployment](./phase-7-qa-deployment.md)*
