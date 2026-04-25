# Tasks: Lab & Radiology Booking API

**Input**: Design documents from `/specs/008-lab-radiology-booking-api/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Included (Constitution Principle IV requires test coverage).

**Organization**: US1 (Lab Booking) and US2 (Radiology Booking) share the same infrastructure (single table, shared controller, shared service) — they are implemented together in Phase 3. US3 (Admin Management) is a separate phase. US4 (Notifications) is handled inline within booking creation/status changes.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project initialization needed — module already exists. Skip to foundational.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Database schema, models, permissions, and translations. BLOCKS all user stories.

- [X] T001 [P] Create migration `Modules/RadiologyLab/database/migrations/2026_04_02_100001_create_facility_sessions_table.php` — facility_type enum(lab,radiology), facility_id bigint, day string(20), start_time time, end_time time, slot_duration integer default 30, breaks JSON nullable, is_holiday boolean default false, status boolean default true, created_by/updated_by/deleted_by integer nullable, timestamps, soft deletes. Unique index on (facility_type, facility_id, day). Index on status.
- [X] T002 [P] Create migration `Modules/RadiologyLab/database/migrations/2026_04_02_100002_create_facility_bookings_table.php` — booking_number string(20) unique, type enum(lab,radiology), lab_id bigint FK nullable (references labs.id, ON DELETE SET NULL), radiology_center_id bigint FK nullable (references radiology_centers.id, ON DELETE SET NULL), patient_id bigint FK (references users.id, ON DELETE CASCADE), patient_name string(255), patient_phone string(50), scan_type string(255) nullable, booking_date date, booking_time time, status enum(pending,confirmed,cancelled,completed,no_show) default pending, notes text nullable, admin_notes text nullable, cancellation_reason text nullable, cancelled_by string(50) nullable, created_by/updated_by/deleted_by integer nullable, timestamps, soft deletes. Indexes on patient_id, booking_date, status, (type, lab_id), (type, radiology_center_id).
- [X] T003 Create `Modules/RadiologyLab/Models/FacilitySession.php` — extends BaseModel. Fillable: facility_type, facility_id, day, start_time, end_time, slot_duration, breaks, is_holiday, status. Casts: is_holiday→boolean, breaks→array, status→boolean, slot_duration→integer. Scopes: active() (status=1), forFacility($type, $id) (where facility_type+facility_id), forDay($day) (where day). No direct relationship methods needed (facility_id is generic).
- [X] T004 Create `Modules/RadiologyLab/Models/FacilityBooking.php` — extends BaseModel. Status constants: PENDING, CONFIRMED, CANCELLED, COMPLETED, NO_SHOW. ALLOWED_TRANSITIONS map (per data-model.md). Fillable: all fields except id/timestamps/soft-delete. Casts: booking_date→date, status→string. Auto-generate booking_number on creation (BK-YYYY-XXXX format, include soft-deleted in sequence count). Relationships: patient() belongsTo User, lab() belongsTo Lab, radiologyCenter() belongsTo RadiologyCenter. Scopes: byPatient($id), byStatus($status), byType($type), forDate($date), active() (status not cancelled). Accessor: facility() returns lab or radiologyCenter based on type. Method: canTransitionTo($newStatus) validates against ALLOWED_TRANSITIONS.
- [X] T005 [P] Add 2 booking permissions (view_facility_bookings, manage_facility_bookings) to `Modules/RadiologyLab/database/seeders/RadiologyLabPermissionSeeder.php` — assign both to admin and demo_admin roles.
- [X] T006 [P] Add ~30 English booking translation keys to `lang/en/radiology_lab.php` per contracts/translations.md — booking entity labels, status labels, type labels, success messages, error messages, slot labels.
- [X] T007 [P] Add ~30 Arabic booking translation keys to `lang/ar/radiology_lab.php` per contracts/translations.md — matching Arabic translations for all new keys.

**Checkpoint**: Run `php artisan migrate`, confirm both tables created. Run seeder, confirm permissions. Verify translation keys load correctly.

---

## Phase 3: User Story 1 + 2 — Patient Books Lab / Radiology Appointment (Priority: P1) MVP

**Goal**: Complete patient-facing booking API: available time slots for labs and radiology centers, create booking, list bookings, view booking, cancel booking. Covers both US1 (lab) and US2 (radiology) since they share the same controller and service.

**Independent Test**: GET slots for a lab → POST create booking → GET my bookings → GET booking detail → POST cancel booking. Same flow for radiology center with scan_type.

### Implementation for User Stories 1 + 2

- [X] T008 [US1] Create `Modules/RadiologyLab/Services/FacilitySlotService.php` — method getAvailableSlots($facilityType, $facilityId, $date): find FacilitySession for that day (strtolower(Carbon::parse($date)->format('l'))), return empty array if no session or is_holiday=true. Generate slot intervals from start_time to end_time using slot_duration. Exclude break periods (compare each slot start against breaks array). Query facility_bookings for existing bookings at this facility+date where status NOT IN (cancelled) to get booked times. If date is today, exclude past time slots. Return array of {time: 'H:i', available: bool} objects.
- [X] T009 [P] [US1] Create `Modules/RadiologyLab/Http/Requests/FacilitySlotRequest.php` — validates date: required|date_format:Y-m-d|after_or_equal:today. Translated messages.
- [X] T010 [P] [US1] Create `Modules/RadiologyLab/Http/Requests/FacilityBookingRequest.php` — validates type (required|in:lab,radiology), lab_id (required_if:type,lab|exists:labs,id), radiology_center_id (required_if:type,radiology|exists:radiology_centers,id), scan_type (required_if:type,radiology|nullable|string|max:255), booking_date (required|date_format:Y-m-d|after_or_equal:today), booking_time (required|date_format:H:i), patient_name (required|string|max:255), patient_phone (required|string|max:50), notes (nullable|string|max:1000). Custom validation in withValidator(): check facility is active (status=1), check slot is available via FacilitySlotService. Translated messages.
- [X] T011 [P] [US1] Create `Modules/RadiologyLab/Transformers/FacilityBookingResource.php` — returns: id, booking_number, type, facility (nested {id, name} using locale-aware accessor from lab or radiologyCenter), scan_type, patient_name, patient_phone, booking_date (Y-m-d), booking_time (H:i), status, notes, admin_notes (when present), cancellation_reason (when present), cancelled_by (when present), created_at (ISO8601).
- [X] T012 [US1] Create `Modules/RadiologyLab/Http/Controllers/API/FacilityBookingApiController.php` — methods: labSlots($labId, FacilitySlotRequest) validates lab exists and is active, calls FacilitySlotService, returns slot data with facility info. radiologyCenterSlots($centerId, FacilitySlotRequest) same for radiology. store(FacilityBookingRequest) creates booking in DB::transaction, auto-generates booking_number, sets patient_id from auth user, sends notification in try-catch. index(Request) lists authenticated user's bookings with optional type/status filters, paginated 15/page, ordered by booking_date desc. show($id) returns single booking, checks ownership (patient_id = auth user). cancel($id, Request) validates booking belongs to user and status is pending/confirmed, updates status to cancelled with cancellation_reason and cancelled_by='patient'.
- [X] T013 [US1] Add booking API routes to `Modules/RadiologyLab/Routes/api.php` — public routes: GET /v1/facility-bookings/labs/{labId}/slots, GET /v1/facility-bookings/radiology-centers/{centerId}/slots. Auth routes (auth:sanctum group): POST /v1/facility-bookings, GET /v1/facility-bookings, GET /v1/facility-bookings/{id}, POST /v1/facility-bookings/{id}/cancel.

**Checkpoint**: Test lab slots endpoint, radiology slots endpoint, create lab booking, create radiology booking with scan_type, list my bookings, view detail, cancel booking. Verify bilingual responses. Verify double-booking rejection.

---

## Phase 4: User Story 3 — Admin Manages Booking Requests (Priority: P2)

**Goal**: Admin API for listing all bookings with filters, confirming, cancelling, and updating status.

**Independent Test**: Admin lists all bookings → filters by type/status/date → confirms a pending booking → cancels a booking → marks a booking completed/no_show. Non-admin gets 403.

### Implementation for User Story 3

- [X] T014 [US3] Create `Modules/RadiologyLab/Http/Controllers/API/AdminFacilityBookingApiController.php` — constructor applies middleware: auth:sanctum + permission:view_facility_bookings on index, permission:manage_facility_bookings on confirm/cancel/updateStatus. Methods: index(Request) lists all bookings with filters (type, status, date_from, date_to, facility_id), eager loads patient/lab/radiologyCenter, paginated 15/page. confirm($id, Request) validates booking is pending, transitions to confirmed, sets admin_notes, sends notification. cancel($id, Request) validates cancellation_reason required, transitions to cancelled, sets cancelled_by='admin'. updateStatus($id, Request) validates status in:completed,no_show, validates booking is confirmed, transitions status.
- [X] T015 [US3] Add admin booking routes to `Modules/RadiologyLab/Routes/api.php` — under auth:sanctum group: GET /v1/admin/facility-bookings, POST /v1/admin/facility-bookings/{id}/confirm, POST /v1/admin/facility-bookings/{id}/cancel, POST /v1/admin/facility-bookings/{id}/status.

**Checkpoint**: Admin can list/filter/confirm/cancel/update bookings. Non-admin gets 403 on admin endpoints.

---

## Phase 5: Tests

**Purpose**: Feature tests covering slot generation, booking CRUD, admin management, double-booking prevention, and permission enforcement.

- [X] T016 [P] [US1] Create `Modules/RadiologyLab/Tests/Feature/FacilitySlotTest.php` — tests: slot generation returns correct intervals for a session (create session 08:00-12:00 with 30min slots, expect 8 slots), breaks are excluded (add break 10:00-10:30, expect that slot missing), booked slots marked as unavailable (create booking at 09:00, expect available=false), past times excluded when date is today, no session returns empty array, holiday returns empty array, inactive facility returns 422.
- [X] T017 [P] [US2] Create `Modules/RadiologyLab/Tests/Feature/FacilityBookingTest.php` — tests: patient can create lab booking (assert DB record with correct fields), patient can create radiology booking with scan_type, validation rejects missing patient_name, validation rejects missing booking_time, validation rejects past date, double-booking returns error (create booking at 09:00, try same slot again), patient can list own bookings (create 2, assert count), patient cannot see other patient's booking (403), patient can cancel pending booking, patient can cancel confirmed booking, patient cannot cancel completed booking (422), booking_number auto-generated correctly, non-authenticated user gets 401, admin can confirm pending booking, admin can cancel booking with reason, admin can mark completed, non-admin cannot access admin endpoints (403), inactive facility rejected.

**Checkpoint**: `php artisan test --filter=FacilitySlot` and `php artisan test --filter=FacilityBooking` pass.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Code quality and final verification.

- [X] T018 Run `./vendor/bin/pint` to fix code style across all new files
- [X] T019 Run `php artisan optimize:clear` to clear all caches
- [X] T020 Run `php artisan test` to verify all tests pass (including existing tests)
- [X] T021 Verify all API endpoints with `php artisan route:list --path=facility-bookings`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Foundational (Phase 2)**: No dependencies — can start immediately. BLOCKS all user stories.
- **US1+US2 Patient Booking (Phase 3)**: Depends on Phase 2 completion.
- **US3 Admin Management (Phase 4)**: Depends on Phase 3 completion (admin endpoints manage bookings created by patients).
- **Tests (Phase 5)**: Depends on Phase 3 and Phase 4 completion.
- **Polish (Phase 6)**: Depends on all phases complete.

### User Story Dependencies

- **US1+US2 (Lab + Radiology Booking — P1)**: Implemented together. Share FacilityBooking model, FacilitySlotService, and FacilityBookingApiController.
- **US3 (Admin Management — P2)**: Depends on US1+US2 (manages bookings created by patients). Separate controller.
- **US4 (Notifications — P3)**: Delivered inline within US1+US2 store() method and US3 confirm/cancel methods using try-catch notification helper.

### Within Phase 3

- FacilitySlotService (T008) before controller (T012) — controller depends on service
- Form requests (T009, T010) and resource (T011) can be parallel — different files
- Controller (T012) depends on service + requests + resource
- Routes (T013) depends on controller

### Parallel Opportunities

**Phase 2** (all [P] tasks can run in parallel):
- T001, T002 (different migration files)
- T005, T006, T007 (different files: seeder, en translations, ar translations)

**Phase 3** (independent files within story):
- T009, T010, T011 (request classes + resource — different files, no dependencies)

**Phase 5** (tests can run in parallel):
- T016, T017 (different test files)

---

## Parallel Example: Phase 2 (Foundational)

```bash
# Launch both migrations together:
Task: "Create facility_sessions migration"    # T001
Task: "Create facility_bookings migration"    # T002

# Launch permissions + translations together:
Task: "Add booking permissions to seeder"     # T005
Task: "Add EN booking translations"           # T006
Task: "Add AR booking translations"           # T007
```

## Parallel Example: Phase 3 (Patient Booking)

```bash
# Launch validation + resource together:
Task: "Create FacilitySlotRequest"            # T009
Task: "Create FacilityBookingRequest"         # T010
Task: "Create FacilityBookingResource"        # T011
```

---

## Implementation Strategy

### MVP First (US1 + US2)

1. Complete Phase 2: Foundational (migrations, models, permissions, translations)
2. Complete Phase 3: Patient Booking API (slots + CRUD for both lab and radiology)
3. **STOP and VALIDATE**: Test full booking flow via curl/Postman
4. Deploy/demo if ready — patients can book lab and radiology appointments

### Incremental Delivery

1. Complete Phase 2 → Foundation ready
2. Add US1+US2 (Patient Booking) → Test independently → Deploy (MVP)
3. Add US3 (Admin Management) → Test independently → Deploy (full feature)
4. Add Tests → Verify all pass → Deploy (production-ready)
5. Polish → Code style, cache clear, final verification

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- US1 and US2 are implemented together (shared model, service, controller)
- US4 (Notifications) is delivered inline within booking creation and admin actions
- Reference controller: `Modules/Clinic/Http/Controllers/API/CallBookingApiController.php`
- Reference session model: `Modules/Clinic/Models/DoctorCallSession.php`
- Reference order API: `Modules/RadiologyLab/Http/Controllers/Backend/API/TestOrdersApiController.php`
- Existing API routes: `Modules/RadiologyLab/Routes/api.php`
- Double-booking prevention: app-level check in FacilityBookingRequest + DB transaction in controller
