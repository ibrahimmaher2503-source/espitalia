# Implementation Plan: Doctor Visit Request API

**Branch**: `009-doctor-visit-api` | **Date**: 2026-04-05 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/009-doctor-visit-api/spec.md`

---

## Summary

Build a new `DoctorVisit` Laravel module providing:
1. REST API endpoints for authenticated mobile-app patients to submit home visit requests and track their status.
2. Admin dashboard (Blade) for `admin`, `receptionist`, and `doctor` roles to view, manage, and update visit request statuses.
3. Email notifications (Laravel Mail) to patients on status changes.

Modelled directly on the existing `RequestNurse` module. No new libraries required.

---

## Technical Context

**Language/Version**: PHP 8.2+ / Laravel 11.x
**Primary Dependencies**: nwidart/laravel-modules 10.x, Laravel Sanctum (existing), spatie/laravel-permission (existing), Laravel Mail (existing)
**Storage**: MySQL (existing project database)
**Testing**: PHPUnit + SQLite in-memory
**Target Platform**: Linux server (web service)
**Project Type**: Laravel module — REST API + Blade admin UI
**Performance Goals**: Synchronous API; SC-002 (<5s visibility) satisfied by standard request/response cycle
**Constraints**: No new external integrations; email via existing Mail stack; SQLite-compatible migrations
**Scale/Scope**: Multi-tenant clinic system; same scale as existing modules

---

## Constitution Check

### Principle I — Module Encapsulation ✅
All code lives under `Modules/DoctorVisit/`. The `Doctor` model is referenced via Eloquent relationships (`belongsTo Doctor::class`), never via raw SQL joins across module boundaries. Shared infrastructure (BaseModel, Mail) remains in `app/`.

### Principle II — Convention Adherence ✅
- `DoctorVisitRequest` model extends `App\Models\BaseModel`
- Admin controller implements: `index`, `index_data`, `show`, `update_status`, `bulk_action`
- Admin routes: prefix `app/doctor-visit`, name prefix `backend.doctor_visit.*`, middleware `['auth', 'auth_check']`
- API routes: middleware `auth:sanctum`, prefix `v1`, name prefix `api.doctor_visit.*` / `api.admin.doctor_visit.*`
- Translation keys in `lang/en/doctor_visit.php` following `lbl_*` / `status_*` conventions

### Principle III — Role-Based Access Control ✅
- Patient API (`POST/GET /doctor-visit/requests`): `auth:sanctum` — `user` role
- Admin API: `auth:sanctum` + role check (`admin`, `receptionist`, `doctor`)
- Permission seeder declares: `doctor_visit_request.view`, `.create`, `.update_status`, `.assign_doctor`
- Sidebar entry in `GenerateMenus` conditionally shown for `admin`, `receptionist`, `doctor`
- `demo_admin` receives view-only access (no write permissions)

### Principle IV — Test Discipline ✅
- Feature tests: submit request (happy path + validation errors), list requests, detail by reference, status transitions (valid + invalid), role authorization
- Unit tests: reference number generation, `VALID_TRANSITIONS` guard
- SQLite in-memory; tests in `Modules/DoctorVisit/Tests/`

### Principle V — Simplicity/YAGNI ✅
- Reference number uses the proven `lockForUpdate()` boot-event pattern from `TestOrder.php` — no UUID library
- Email notification is a simple Mailable (`AppointmentConfirmation.php` pattern) — not the complex `CommonNotification` system
- `DoctorVisitStatusHistory` table is new but required by FR-012 (spec-mandated audit trail)
- No new npm packages; admin UI uses existing DataTables + Blade components

**No constitution violations.**

---

## Project Structure

### Documentation (this feature)

```text
specs/009-doctor-visit-api/
├── plan.md              ← this file
├── research.md          ← Phase 0 output
├── data-model.md        ← Phase 1 output
├── quickstart.md        ← Phase 1 output
├── contracts/
│   └── api-endpoints.md ← Phase 1 output
└── tasks.md             ← Phase 2 output (/speckit.tasks — not yet created)
```

### Source Code Layout

```text
Modules/DoctorVisit/
├── Config/
│   └── config.php
├── database/
│   ├── migrations/
│   │   ├── 2026_04_05_000001_create_doctor_visit_requests_table.php
│   │   └── 2026_04_05_000002_create_doctor_visit_status_histories_table.php
│   └── seeders/
│       └── DoctorVisitDatabaseSeeder.php          (permissions seeder)
├── Http/
│   ├── Controllers/
│   │   ├── Backend/
│   │   │   └── DoctorVisitRequestController.php   (admin Blade CRUD)
│   │   └── API/
│   │       ├── DoctorVisitApiController.php        (patient-facing: submit, list, show)
│   │       └── AdminDoctorVisitApiController.php   (staff: list, status, assign, notes)
│   └── Requests/
│       ├── DoctorVisitStoreRequest.php
│       └── DoctorVisitStatusUpdateRequest.php
├── Models/
│   ├── DoctorVisitRequest.php
│   └── DoctorVisitStatusHistory.php
├── Providers/
│   ├── DoctorVisitServiceProvider.php
│   └── RouteServiceProvider.php
├── Resources/views/backend/doctor_visit_request/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── datatable/action_column.blade.php
├── Routes/
│   ├── api.php
│   └── web.php
└── Tests/
    ├── Feature/
    │   └── DoctorVisitApiTest.php
    └── Unit/
        └── DoctorVisitStatusTransitionTest.php

Transformers/
└── DoctorVisitRequestResource.php                 (under Modules/DoctorVisit/)

app/Mail/
└── DoctorVisitStatusChanged.php                   (new Mailable)

lang/en/
└── doctor_visit.php                               (new translation file)

app/Http/Middleware/
└── GenerateMenus.php                              (add sidebar entry — existing file)
```

**Structure Decision**: Single module following `Modules/RequestNurse/` layout. `DoctorVisitStatusHistory` is a second model within the same module (not a separate module) because it is exclusively owned by and accessed through `DoctorVisitRequest`.

---

## Complexity Tracking

No constitution violations — table not required.

---

## Implementation Phases (for /speckit.tasks)

The following phases are informational for task generation. Do not execute — run `/speckit.tasks` next.

### Phase 1 — Module Scaffold & Database
1. Create `DoctorVisit` module via `php artisan module:make`
2. Create `doctor_visit_requests` migration
3. Create `doctor_visit_status_histories` migration
4. Create `DoctorVisitRequest` model (BaseModel, constants, relationships, boot reference-number generation)
5. Create `DoctorVisitStatusHistory` model

### Phase 2 — Patient API
6. `DoctorVisitStoreRequest` validation class
7. `DoctorVisitRequestResource` transformer
8. `DoctorVisitApiController` — `store`, `index`, `show`
9. Register patient API routes in `Routes/api.php`

### Phase 3 — Admin API
10. `DoctorVisitStatusUpdateRequest` validation class
11. `AdminDoctorVisitApiController` — `index`, `updateStatus`, `assignDoctor`, `addNote`
12. Register admin API routes in `Routes/api.php`
13. Role authorization helper (`canManageRequest`)

### Phase 4 — Email Notification
14. `app/Mail/DoctorVisitStatusChanged.php` Mailable
15. Email view template
16. Dispatch mail on status change (in `updateStatus` action)

### Phase 5 — Admin Dashboard (Blade)
17. `DoctorVisitRequestController` (Backend) — `index`, `index_data`, `show`, `update_status`, `bulk_action`
18. Admin web routes in `Routes/web.php`
19. `index.blade.php` — DataTable list view
20. `show.blade.php` — Request detail view
21. `datatable/action_column.blade.php`
22. Sidebar entry in `GenerateMenus.php`

### Phase 6 — Translations & Permissions
23. `lang/en/doctor_visit.php` translation file
24. `DoctorVisitDatabaseSeeder.php` — permission seeder
25. Register seeder in module service provider

### Phase 7 — Tests
26. `DoctorVisitApiTest.php` — Feature tests (submit, list, show, status transitions, role auth)
27. `DoctorVisitStatusTransitionTest.php` — Unit tests (transition guard, reference format)
