# Implementation Plan: Lab & Radiology Booking API

**Branch**: `008-lab-radiology-booking-api` | **Date**: 2026-04-02 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/008-lab-radiology-booking-api/spec.md`

## Summary

Add booking/appointment API endpoints for labs and radiology centers. Patients can view available time slots and create bookings; admins can confirm, cancel, and manage bookings. Uses a single `facility_bookings` table with a type discriminator, and a `facility_sessions` table for configuring facility operating hours. Follows the existing CallBookingApiController pattern. No admin web UI — API only.

## Technical Context

**Language/Version**: PHP 8.2+ / Laravel 11.x
**Primary Dependencies**: nwidart/laravel-modules 10.x, spatie/laravel-permission, Laravel Sanctum
**Storage**: MySQL (2 new tables: `facility_sessions`, `facility_bookings`)
**Testing**: PHPUnit + SQLite in-memory
**Target Platform**: REST API (consumed by mobile/web frontend)
**Project Type**: Web application (modular Laravel SaaS)
**Performance Goals**: Slot calculation < 500ms, booking creation < 1s, admin list with 1000+ records < 3s
**Constraints**: Must follow existing API patterns (sendResponse, JsonResource, auth:sanctum)
**Scale/Scope**: ~12 API endpoints, 2 new models, 2 migrations, 2 services, ~30 translation keys per language

## Constitution Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Module Encapsulation | PASS | All new code within `Modules/RadiologyLab/`. Cross-module refs (Lab, RadiologyCenter, User) via Eloquent relationships only. |
| II. Convention Adherence | PASS | API routes use `auth:sanctum`, prefix `v1`. Models extend BaseModel. Translation keys use `lbl_*` prefix. API resources use JsonResource. |
| III. Role-Based Access Control | PASS | 2 new permissions via seeder. Patient endpoints use auth:sanctum. Admin endpoints gated by permission middleware. |
| IV. Test Discipline | PASS | Feature tests for slot generation, booking CRUD, double-booking prevention, permission checks, cancellation, and admin management. |
| V. Simplicity and YAGNI | PASS | Single table for both booking types (no premature abstraction). No new dependencies. Reuses existing patterns. Notifications in try-catch. |

**Post-Phase 1 Re-check**: All gates PASS.

## Project Structure

### Documentation (this feature)

```text
specs/008-lab-radiology-booking-api/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0: research decisions
├── data-model.md        # Phase 1: entity documentation
├── quickstart.md        # Phase 1: implementation guide
├── contracts/
│   ├── api-endpoints.md # API endpoint contracts
│   └── translations.md  # Translation key contracts
└── checklists/
    └── requirements.md  # Spec quality checklist
```

### Source Code (repository root)

```text
Modules/RadiologyLab/
├── Http/
│   ├── Controllers/API/
│   │   ├── FacilityBookingApiController.php        # NEW — Patient booking API
│   │   └── AdminFacilityBookingApiController.php   # NEW — Admin booking API
│   └── Requests/
│       ├── FacilityBookingRequest.php              # NEW — Create booking validation
│       └── FacilitySlotRequest.php                 # NEW — Slot query validation
├── Models/
│   ├── FacilitySession.php                         # NEW — Facility operating hours
│   └── FacilityBooking.php                         # NEW — Booking model
├── Services/
│   └── FacilitySlotService.php                     # NEW — Slot generation logic
├── Transformers/
│   └── FacilityBookingResource.php                 # NEW — Booking API resource
├── Routes/api.php                                  # MODIFY — Add booking routes
├── database/
│   ├── migrations/
│   │   ├── 2026_04_02_100001_create_facility_sessions_table.php  # NEW
│   │   └── 2026_04_02_100002_create_facility_bookings_table.php  # NEW
│   └── seeders/
│       └── RadiologyLabPermissionSeeder.php        # MODIFY — Add 2 booking permissions
└── Tests/Feature/
    ├── FacilitySlotTest.php                        # NEW — Slot generation tests
    └── FacilityBookingTest.php                     # NEW — Booking CRUD tests

lang/en/radiology_lab.php                           # MODIFY — Add ~30 EN keys
lang/ar/radiology_lab.php                           # MODIFY — Add ~30 AR keys
```

**Structure Decision**: All code resides within `Modules/RadiologyLab/`. The `Services/` directory is new but justified — slot generation logic is reused by both patient and admin controllers and is complex enough to warrant extraction from the controller.

## Implementation Phases

### Phase 1: Foundation (Migrations, Models, Permissions, Translations)

**Goal**: Database schema, models, permissions, and translations ready.

**Files to create**:
1. `Modules/RadiologyLab/database/migrations/2026_04_02_100001_create_facility_sessions_table.php` — facility_type enum, facility_id, day, start_time, end_time, slot_duration, breaks JSON, is_holiday, status, audit fields, soft deletes. Unique index on (facility_type, facility_id, day).
2. `Modules/RadiologyLab/database/migrations/2026_04_02_100002_create_facility_bookings_table.php` — booking_number unique, type enum, lab_id FK nullable, radiology_center_id FK nullable, patient_id FK, patient_name, patient_phone, scan_type nullable, booking_date, booking_time, status enum, notes, admin_notes, cancellation_reason, cancelled_by, audit fields, soft deletes. Indexes on patient_id, booking_date, status.
3. `Modules/RadiologyLab/Models/FacilitySession.php` — extends BaseModel, fillable fields, casts (is_holiday→boolean, breaks→array, status→boolean, slot_duration→integer), relationships, scopes (active, forFacility, forDay).
4. `Modules/RadiologyLab/Models/FacilityBooking.php` — extends BaseModel, fillable, casts, status constants, ALLOWED_TRANSITIONS, auto-generated booking_number (BK-YYYY-XXXX), relationships (patient, lab, radiologyCenter), scopes (byPatient, byStatus, byType, forDate), canTransitionTo() method, facility() accessor returning lab or radiologyCenter based on type.

**Files to modify**:
5. `Modules/RadiologyLab/database/seeders/RadiologyLabPermissionSeeder.php` — Add `view_facility_bookings` and `manage_facility_bookings`, assign to admin/demo_admin.
6. `lang/en/radiology_lab.php` — Add ~30 booking translation keys per contracts/translations.md.
7. `lang/ar/radiology_lab.php` — Add corresponding Arabic translations.

**Verification**: `php artisan migrate`, confirm tables created. Run seeder, confirm permissions.

---

### Phase 2: Slot Generation Service

**Goal**: Business logic for generating available time slots from sessions.

**Files to create**:
1. `Modules/RadiologyLab/Services/FacilitySlotService.php` — `getAvailableSlots($facilityType, $facilityId, $date)` method. Logic: find session for that day → generate slot intervals from start_time to end_time using slot_duration → exclude breaks → exclude already-booked slots (query facility_bookings where status NOT cancelled) → exclude past times if date is today → return array of `{time, available}` objects.

**Verification**: Unit test slot generation with mock sessions and bookings.

---

### Phase 3: Patient Booking API (US1 + US2)

**Goal**: Complete patient-facing API: slots, create, list, show, cancel.

**Files to create**:
1. `Modules/RadiologyLab/Http/Requests/FacilitySlotRequest.php` — Validates date (required, date, after_or_equal:today).
2. `Modules/RadiologyLab/Http/Requests/FacilityBookingRequest.php` — Validates type, lab_id/radiology_center_id, scan_type, booking_date, booking_time, patient_name, patient_phone, notes. Custom validation: facility must be active, slot must be available.
3. `Modules/RadiologyLab/Transformers/FacilityBookingResource.php` — Returns booking with nested facility data (locale-aware name), patient info, status, dates.
4. `Modules/RadiologyLab/Http/Controllers/API/FacilityBookingApiController.php` — Methods: labSlots($labId), radiologyCenterSlots($centerId), store(FacilityBookingRequest), index(Request), show($id), cancel($id). Uses FacilitySlotService for slots. Uses DB::transaction for booking creation with double-booking check. Sends notification on creation.

**Files to modify**:
5. `Modules/RadiologyLab/Routes/api.php` — Add public slot routes and auth:sanctum booking routes.

**Verification**: Test all patient endpoints with Postman/curl. Verify bilingual responses. Test double-booking rejection.

---

### Phase 4: Admin Booking API (US3)

**Goal**: Admin endpoints for managing bookings.

**Files to create**:
1. `Modules/RadiologyLab/Http/Controllers/API/AdminFacilityBookingApiController.php` — Methods: index(Request) with filters (type, status, date_from, date_to, facility_id), confirm($id), cancel($id), updateStatus($id). Permission middleware on all methods.

**Files to modify**:
2. `Modules/RadiologyLab/Routes/api.php` — Add admin booking routes under auth:sanctum with permission middleware.

**Verification**: Test admin endpoints with different filters. Verify permission enforcement.

---

### Phase 5: Tests

**Goal**: Feature tests covering all critical paths.

**Files to create**:
1. `Modules/RadiologyLab/Tests/Feature/FacilitySlotTest.php` — Test slot generation (happy path, breaks excluded, booked slots excluded, past times excluded for today, no session returns empty, holiday returns empty).
2. `Modules/RadiologyLab/Tests/Feature/FacilityBookingTest.php` — Test booking creation (lab + radiology), validation rejection, double-booking prevention, list own bookings, cancel booking, admin confirm, admin cancel, permission denial for non-admin, inactive facility rejection, past date rejection.

**Verification**: `php artisan test --filter=FacilitySlot` and `php artisan test --filter=FacilityBooking` pass.

---

### Phase 6: Final Verification

**Steps**:
1. `./vendor/bin/pint` — code style
2. `php artisan test` — all tests pass
3. `php artisan optimize:clear` — clear caches
4. End-to-end API test: search lab → get slots → book → view → cancel
5. Verify bilingual responses with `Accept-Language: ar`

## Key Design Decisions

| Decision | Choice | Reference |
|----------|--------|-----------|
| Data model | Single `facility_bookings` table with type column | [research.md#D1](./research.md) |
| Time slots | `facility_sessions` table per DoctorCallSession pattern | [research.md#D2](./research.md) |
| API structure | 3-step: search → slots → book | [research.md#D3](./research.md) |
| Status lifecycle | 5 statuses: pending/confirmed/cancelled/completed/no_show | [research.md#D4](./research.md) |
| Double-booking | DB constraint + app-level check | [research.md#D5](./research.md) |
| Response format | sendResponse + JsonResource | [research.md#D6](./research.md) |
| Notifications | Existing helper in try-catch | [research.md#D7](./research.md) |
| Permissions | 2 admin permissions, patients use auth:sanctum | [research.md#D8](./research.md) |
