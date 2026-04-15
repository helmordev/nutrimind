# NutriMind — Server Development Execution Plan

> 7-Phase Milestone Roadmap | Laravel 13 + MySQL 8
> Total Timeline: 2-Day Sprint + 11 Weeks

---

## Overview

This execution plan breaks the NutriMind server-side development into **7 progressive phases**, each representing a cumulative milestone checkpoint. Phase 1 is a critical 2-day sprint for capstone panel demonstration; Phases 2-7 span 11 weeks of iterative development.

**Reference:** See [`context/NutriMind_Server_Plan.md`](../context/NutriMind_Server_Plan.md) for the complete technical specification.

---

## Milestone Timeline

| Phase | Milestone | Timeline | Focus | Document |
|-------|-----------|----------|-------|----------|
| Phase 1 | **10%** | 2 Days | Foundation, Auth, Account Chain, Basic Dashboards | [phase-1-foundation-auth.md](./phase-1-foundation-auth.md) |
| Phase 2 | **25%** | Weeks 1-2 | Core Gameplay API (Worlds, Levels, Questions, Progress) | [phase-2-core-gameplay-api.md](./phase-2-core-gameplay-api.md) |
| Phase 3 | **35%** | Week 3 | Boss Battles, Grade Computation, Badges, Level Unlock | [phase-3-boss-grades-badges.md](./phase-3-boss-grades-badges.md) |
| Phase 4 | **50%** | Weeks 4-5 | Preferences, Language Support, Sync/Offline | [phase-4-preferences-sync.md](./phase-4-preferences-sync.md) |
| Phase 5 | **65%** | Weeks 6-7 | Adaptive Difficulty, Screen Time System | [phase-5-difficulty-screentime.md](./phase-5-difficulty-screentime.md) |
| Phase 6 | **80%** | Weeks 8-9 | Teacher Dashboard + Admin Dashboard (Full Polish) | [phase-6-dashboards.md](./phase-6-dashboards.md) |
| Phase 7 | **100%** | Weeks 10-11 | QA, Exports, Deployment, Final Polish | [phase-7-qa-deployment.md](./phase-7-qa-deployment.md) |

---

## Visual Timeline

```
Day 1-2          Wk1  Wk2  Wk3  Wk4  Wk5  Wk6  Wk7  Wk8  Wk9  Wk10 Wk11
  |               |    |    |    |    |    |    |    |    |    |    |
  [=P1=]          [==P2===] [P3=] [==P4===] [==P5===] [==P6===] [==P7===]
  10%             25%       35%   50%       65%       80%       100%
```

---

## Phase Dependencies

```
Phase 1 (Foundation)
  |
  +-- Phase 2 (Gameplay API) -- depends on: migrations, auth, seeders
  |     |
  |     +-- Phase 3 (Boss/Grades/Badges) -- depends on: progress submission, levels
  |           |
  |           +-- Phase 4 (Preferences/Sync) -- depends on: all progress endpoints
  |                 |
  |                 +-- Phase 5 (Difficulty/Screen Time) -- depends on: sync, preferences
  |                       |
  |                       +-- Phase 6 (Dashboards) -- depends on: all API + services
  |                             |
  |                             +-- Phase 7 (QA/Deploy) -- depends on: everything
```

---

## Entry Criteria Per Phase

| Phase | What Must Be True Before Starting |
|-------|-----------------------------------|
| Phase 1 | Development environment ready (PHP 8.5, Composer, MySQL 8, Node.js) |
| Phase 2 | Phase 1 smoke test passes; all 17+ migrations run; auth works end-to-end |
| Phase 3 | Phase 2 endpoints return correct data; level progress submission works |
| Phase 4 | Phase 3 grade computation verified against DepEd formula manually |
| Phase 5 | Phase 4 sync endpoint handles offline queue; preferences persist |
| Phase 6 | All Phase 5 API endpoints are functional and tested via Postman |
| Phase 7 | All dashboard views render correctly; all services have unit tests |

---

## Exit Criteria (Definition of Done)

Each phase is considered complete when:
1. All numbered tasks within the phase are marked done
2. The verification checklist at the bottom of each phase document passes
3. Changes are committed to version control with descriptive messages
4. The Postman collection is updated to reflect any new or modified endpoints
5. No regressions in previously completed functionality

---

## Quick Links

- [Server Plan (Full Spec)](../context/NutriMind_Server_Plan.md)
- [Unity Plan (Full Spec)](../context/NutriMind_Unity_Plan.md)

---

*University of Eastern Pangasinan | Capstone Project — NutriMind*
