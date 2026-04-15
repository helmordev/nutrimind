# Phase 4 — Preferences, Language Support & Sync/Offline

> **Milestone:** 50% | **Timeline:** Weeks 4-5
> **Goal:** Student preferences persist across devices, bilingual content delivery works, and offline sync handles queued records reliably.

---

## Prerequisites

- Phase 3 complete (35% milestone passed)
- Boss battles, grade computation, and badges functional
- All progress submission endpoints working

---

## Week 4 — Preferences & Language

### Task 1: Preferences Controller

- [ ] Create `Student\PreferencesController.php` with `show()` and `update()` methods
- [ ] Create `UpdatePreferencesRequest.php` — validate:
  - `language` (optional, in: en, fil — validated against `Language` enum values)
  - `master_volume` (optional, integer, 0-100)
  - `bgm_volume` (optional, integer, 0-100)
  - `sfx_volume` (optional, integer, 0-100)
  - `tts_enabled` (optional, boolean)
  - `text_size` (optional, in: normal, large — validated against `TextSize` enum values)
  - `colorblind_mode` (optional, boolean)
- [ ] `show()` — returns current preferences from `student_preferences`
- [ ] `update()` — partial update (only supplied fields change); returns updated preferences
- [ ] Create `PreferenceResource.php`
- [ ] Register routes:
  - `GET /api/v1/student/preferences` (student middleware)
  - `PUT /api/v1/student/preferences` (student middleware)

**Deliverable:** Student can save and retrieve preferences via API.

### Task 2: Preferences in Sync State

- [ ] Update `SyncController@state` to include full preferences in the response payload
- [ ] Ensure preferences are fetched with a single Eloquent `with('preferences')` eager load
- [ ] Verify that preferences in sync/state match what `GET /preferences` returns

**Deliverable:** App open always pulls fresh preferences without a separate request.

### Task 3: Bilingual Content Delivery

- [ ] Verify question `content` JSON supports bilingual structure in API responses
- [ ] `QuestionResource` should pass through the raw bilingual JSON — Unity client selects language
- [ ] Ensure bilingual question seeder samples (from Phase 2) cover all question types
- [ ] Add more bilingual samples if coverage is thin (aim for at least 3 questions per type with both `en` and `fil`)
- [ ] Document the bilingual JSON contract for Unity team reference

**Deliverable:** Questions are served with bilingual content; language selection is client-side.

### Task 4: Preference Reset on Student Account Reset

- [ ] When teacher resets a student's PIN:
  - Preferences are preserved (not reset)
  - Only PIN and `pin_generated_at` change
- [ ] When teacher deletes and recreates a student:
  - Fresh defaults seeded via `CreateStudent` action
- [ ] Verify that `cascadeOnDelete` on `student_preferences` correctly removes orphan rows

**Deliverable:** Preference lifecycle is correct across account operations.

---

## Week 5 — Sync/Offline System

### Task 5: ProcessSync Action — Core Logic

- [ ] Create `app/Actions/ProcessSync.php` with `handle()` method
- [ ] Accept the full sync payload (Section 9 of Server Plan):
  ```
  device_date, session_minutes_today, preferences, records[]
  ```
- [ ] Process each record by type:
  - `type: "level"` -> calls `ProgressController` logic (deduplicate by local_id, upsert by student+level)
  - `type: "boss"` -> calls `BossController` logic (deduplicate by local_id, upsert by student+boss)
- [ ] Process preferences:
  - Merge submitted preferences into `student_preferences` (partial update)
- [ ] Process screen time:
  - `GREATEST(server_total, submitted_total)` — client can never reduce recorded time
  - Upsert `screen_time_logs` for the device_date
- [ ] Return a summary of what was processed:
  - `levels_synced: N`, `bosses_synced: N`, `duplicates_skipped: N`
  - `preferences_updated: true/false`
  - `screen_time_updated: true/false`

**Deliverable:** Sync endpoint processes offline-queued records in one batch.

### Task 6: Sync Controller — Bulk Upload Endpoint

- [ ] Create/update `Student\SyncController.php` with `sync()` method
- [ ] Create `SyncRequest.php` — validate:
  - `device_date` (required, date)
  - `session_minutes_today` (required, integer, min 0)
  - `preferences` (optional, object with preference fields)
  - `records` (required, array, min 1)
  - `records.*.local_id` (required, string)
  - `records.*.type` (required, in: level, boss)
  - Level-specific fields when type=level
  - Boss-specific fields when type=boss
- [ ] Call `ProcessSync::handle()`
- [ ] Register route: `POST /api/v1/student/progress/sync` (student middleware)
- [ ] Return sync summary + any badges earned during sync processing

**Deliverable:** Single endpoint handles all offline-queued data.

### Task 7: Sync Deduplication Testing

- [ ] Test: submitting same `local_id` twice returns success without creating duplicates
- [ ] Test: submitting records with both new and existing `local_id` values — new records created, duplicates skipped
- [ ] Test: preferences in sync payload override previous server values
- [ ] Test: screen time `GREATEST` logic — submitting lower minutes doesn't reduce server total
- [ ] Test: boss records in sync trigger grade computation and badge checks

**Deliverable:** Sync is idempotent and reliable under re-submission scenarios.

### Task 8: Sync State Endpoint — Final Enhancement

- [ ] Finalize `GET /api/v1/student/sync/state` to return the definitive state payload:
  ```json
  {
    "worlds": [...],
    "progress": [...],
    "boss_results": [...],
    "badges": [...],
    "grades": [...],
    "preferences": {...},
    "difficulties": [...],
    "screen_time": {
      "limits": {...},
      "today": {...}
    },
    "server_timestamp": "2025-07-14T16:00:00+08:00"
  }
  ```
- [ ] Ensure `server_timestamp` uses Manila timezone
- [ ] Optimize with eager loading to minimize queries (target: < 10 queries per call)

**Deliverable:** One API call gives Unity everything it needs to initialize offline cache.

### Task 9: Update Postman Collection

- [ ] Add `GET /api/v1/student/preferences` with sample response
- [ ] Add `PUT /api/v1/student/preferences` with sample partial update
- [ ] Add `POST /api/v1/student/progress/sync` with full sample payload (level + boss records + preferences)
- [ ] Update `GET /api/v1/student/sync/state` with finalized response schema
- [ ] Add test scripts to validate sync deduplication

**Deliverable:** Postman collection covers the full sync workflow.

---

## Verification Checklist

Before marking Phase 4 as **50% complete**, all of the following must pass:

- [ ] `GET /api/v1/student/preferences` returns current saved preferences
- [ ] `PUT /api/v1/student/preferences` updates only supplied fields; others unchanged
- [ ] Preferences survive PIN reset (teacher resets PIN -> student's preferences preserved)
- [ ] Preferences are included in `GET /api/v1/student/sync/state` response
- [ ] Questions return bilingual `content` JSON with `en` and `fil` keys
- [ ] `POST /api/v1/student/progress/sync` processes mixed level+boss records
- [ ] Sync deduplication works: duplicate `local_id` submissions don't create duplicate records
- [ ] Screen time `GREATEST` logic enforced: submitted minutes never reduce server total
- [ ] Preferences from sync payload are merged into `student_preferences`
- [ ] Boss records in sync trigger grade computation and badge checks
- [ ] `GET /api/v1/student/sync/state` returns complete state with < 10 DB queries
- [ ] `server_timestamp` in sync state uses Asia/Manila timezone
- [ ] All previous phase functionality still works (no regressions)

---

## Files Created/Modified in Phase 4

```
app/Http/Controllers/Student/PreferencesController.php
app/Http/Controllers/Student/SyncController.php (enhanced)
app/Http/Requests/Student/UpdatePreferencesRequest.php
app/Http/Requests/Student/SyncRequest.php
app/Http/Resources/PreferenceResource.php
app/Actions/ProcessSync.php
database/seeders/QuestionSeeder.php (enhanced bilingual samples)
routes/api.php (updated)
docs/postman/NutriMind_API_v1.json (updated)
```

---

*Phase 4 -> Phase 5: Once the 50% milestone passes, proceed to [Phase 5 — Adaptive Difficulty & Screen Time](./phase-5-difficulty-screentime.md)*
