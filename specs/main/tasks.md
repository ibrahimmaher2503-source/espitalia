# Tasks: RequestNurse Module Unit Tests

**Input**: Design documents from `/specs/001-requestnurse-unit-tests/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, quickstart.md

**Tests**: This feature IS test creation. All tasks produce test files and supporting factories. Tests are the primary deliverable.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create test directory structure and model factories that all test classes depend on

- [ ] T001 Create test directory structure at `Modules/RequestNurse/Tests/Unit/` and `Modules/RequestNurse/database/factories/`
- [ ] T002 [P] Create NurseFactory at `Modules/RequestNurse/database/factories/NurseFactory.php` — namespace `Modules\RequestNurse\Database\Factories`, define all fillable fields using Faker (nurse_id via User::factory, vendor_id nullable, specialization, experience, about, availability_status from ['available','busy','off_duty'], service_area, hourly_rate as randomFloat(2,10,200), status default 1). Reference: data-model.md Nurse entity
- [ ] T003 [P] Create NurseRequestFactory at `Modules/RequestNurse/database/factories/NurseRequestFactory.php` — namespace `Modules\RequestNurse\Database\Factories`, define all fillable fields using Faker (patient_id via User::factory, nurse_id nullable, service_description, request_date as now(), preferred_date as future date, preferred_time as H:i, duration_hours between 1-8, address fields, contact_number, status default 'pending', payment_status default false, total_amount default 0). Reference: data-model.md NurseRequest entity
- [ ] T004 Add `newFactory()` method to Nurse model at `Modules/RequestNurse/Models/Nurse.php` — return `\Modules\RequestNurse\Database\Factories\NurseFactory::new()`. Reference: research.md R1
- [ ] T005 Add `newFactory()` method to NurseRequest model at `Modules/RequestNurse/Models/NurseRequest.php` — return `\Modules\RequestNurse\Database\Factories\NurseRequestFactory::new()`. Reference: research.md R1

**Checkpoint**: Factories working — `Nurse::factory()->create()` and `NurseRequest::factory()->create()` should succeed in a tinker or test context

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Verify factories work by running a minimal smoke test

**This phase is intentionally minimal** — the factories from Phase 1 are the only shared prerequisite. No other foundational infrastructure is needed since PHPUnit and SQLite in-memory are already configured in `phpunit.xml`.

- [ ] T006 Create a smoke test at `Modules/RequestNurse/Tests/Unit/FactorySmokeTest.php` — extend `Tests\TestCase`, use `RefreshDatabase`, write two test methods: `test_nurse_factory_creates_record()` and `test_nurse_request_factory_creates_record()`. Each should call `::factory()->create()` and assert the record exists in the database. Run with `php artisan test --filter=FactorySmokeTest` to confirm.

**Checkpoint**: `php artisan test --filter=FactorySmokeTest` passes — factories are verified and all user story phases can begin

---

## Phase 3: User Story 1 - Model Logic Verification (Priority: P1)

**Goal**: Unit tests verifying all model behaviors (relationships, scopes, accessors, casts, constants) for Nurse and NurseRequest models

**Independent Test**: Run `php artisan test --filter=NurseModelTest` and `php artisan test --filter=NurseRequestModelTest` — all tests pass

### Implementation for User Story 1

- [ ] T007 [P] [US1] Create NurseModelTest at `Modules/RequestNurse/Tests/Unit/NurseModelTest.php` — extend `Tests\TestCase`, use `RefreshDatabase`. Include these test methods:
  - `test_nurse_belongs_to_user()` — create Nurse with User, assert `$nurse->user` is the correct User instance via `nurse_id` (FR-001)
  - `test_nurse_belongs_to_vendor()` — create Nurse with vendor User, assert `$nurse->vendor` returns correct User via `vendor_id` (FR-001)
  - `test_nurse_vendor_is_nullable()` — create Nurse without vendor_id, assert `$nurse->vendor` is null (FR-001)
  - `test_nurse_has_many_nurse_requests()` — create Nurse + 3 NurseRequests with matching `nurse_id`, assert `$nurse->nurseRequests->count()` is 3 (FR-001)
  - `test_full_name_accessor_returns_user_full_name()` — create Nurse with User, assert `$nurse->full_name` equals `$user->first_name . ' ' . $user->last_name` (FR-004)
  - `test_full_name_accessor_returns_empty_when_no_user()` — create orphan Nurse (user deleted), assert `$nurse->full_name` is empty string (edge case)
  - `test_available_scope_filters_correctly()` — create 3 nurses: available+active, busy+active, available+inactive. Assert `Nurse::available()->count()` is 1 (FR-003)
  - `test_active_scope_filters_correctly()` — create active and inactive nurses, assert `Nurse::active()->count()` matches only active (FR-003)
  - `test_hourly_rate_cast_to_double()` — set hourly_rate to '50', retrieve, assert `is_float($nurse->hourly_rate)` (FR-005)
  - `test_status_cast_to_integer()` — set status to '1', retrieve, assert `is_int($nurse->status)` (FR-005)
  - `test_fillable_fields()` — assert `$nurse->getFillable()` matches expected array (FR-001)

- [ ] T008 [P] [US1] Create NurseRequestModelTest at `Modules/RequestNurse/Tests/Unit/NurseRequestModelTest.php` — extend `Tests\TestCase`, use `RefreshDatabase`. Include these test methods:
  - `test_nurse_request_belongs_to_patient()` — assert `$request->patient` returns User via `patient_id` (FR-002)
  - `test_nurse_request_belongs_to_nurse()` — assert `$request->nurse` returns User via `nurse_id` (FR-002)
  - `test_nurse_request_nurse_is_nullable()` — create request without nurse_id, assert `$request->nurse` is null (FR-002)
  - `test_nurse_request_belongs_to_nurse_profile()` — create Nurse + NurseRequest with matching nurse_id, assert `$request->nurseProfile` returns Nurse instance (FR-002)
  - `test_nurse_profile_returns_null_when_no_profile()` — assign nurse_id to user without Nurse record, assert `$request->nurseProfile` is null (edge case)
  - `test_full_address_accessor_all_fields()` — set all address fields, assert `$request->full_address` equals comma-joined string (FR-004)
  - `test_full_address_accessor_partial_fields()` — set only address_line_1, city, country (others null), assert `$request->full_address` excludes null parts (FR-004)
  - `test_full_address_accessor_all_null()` — set all address fields to null, assert `$request->full_address` is empty string (edge case)
  - `test_status_badge_accessor_pending()` — set status to 'pending', assert `$request->status_badge` contains 'bg-warning' and translation key content (FR-004)
  - `test_status_badge_accessor_all_statuses()` — data provider test for all 5 statuses, assert each returns correct badge class (FR-004)
  - `test_status_badge_accessor_unknown_status()` — set invalid status, assert `$request->status_badge` contains 'bg-secondary' (edge case)
  - `test_pending_scope()` — create pending + non-pending requests, assert `NurseRequest::pending()->count()` is correct (FR-003)
  - `test_confirmed_scope()` — similar for confirmed scope (FR-003)
  - `test_by_nurse_scope()` — create requests for different nurses, assert `NurseRequest::byNurse($id)->count()` filters correctly (FR-003)
  - `test_by_patient_scope()` — similar for byPatient scope (FR-003)
  - `test_by_status_scope()` — test with each status value (FR-003)
  - `test_scope_returns_empty_collection_when_no_matches()` — assert scopes return 0 count when no records match (edge case)
  - `test_status_constants()` — assert `NurseRequest::STATUS_PENDING === 'pending'` etc. for all 5 constants (FR-006)
  - `test_statuses_array_contains_all_statuses()` — assert `NurseRequest::STATUSES` equals `['pending','confirmed','in_progress','completed','cancelled']` (FR-006)
  - `test_preferred_date_cast_to_carbon()` — set preferred_date, assert `$request->preferred_date` is `Carbon` instance (FR-005)
  - `test_payment_status_cast_to_boolean()` — set payment_status to 1, assert `$request->payment_status === true` (FR-005)
  - `test_total_amount_cast_to_double()` — set total_amount to '150', assert `is_float($request->total_amount)` (FR-005)
  - `test_duration_hours_cast_to_double()` — set duration_hours to '3', assert `is_float($request->duration_hours)` (FR-005)

**Checkpoint**: `php artisan test --filter=NurseModelTest` and `php artisan test --filter=NurseRequestModelTest` both pass. All model relationships, scopes, accessors, casts, and constants verified.

---

## Phase 4: User Story 2 - Form Request Validation Coverage (Priority: P2)

**Goal**: Unit tests verifying NurseFormRequest and NurseRequestFormRequest validation rules accept valid data and reject invalid data

**Independent Test**: Run `php artisan test --filter=NurseFormRequestTest` and `php artisan test --filter=NurseRequestFormRequestTest` — all tests pass

### Implementation for User Story 2

- [ ] T009 [P] [US2] Create NurseFormRequestTest at `Modules/RequestNurse/Tests/Unit/NurseFormRequestTest.php` — extend `Tests\TestCase`, use `RefreshDatabase`. Use approach from research.md R2: instantiate `NurseFormRequest`, extract `rules()`, validate with `Validator::make()`. Include:
  - `test_valid_data_passes_validation()` — all required fields present and valid (FR-007)
  - `test_first_name_is_required()` — omit first_name, assert validation fails with 'first_name' error (FR-007)
  - `test_last_name_is_required()` — omit last_name, assert fails (FR-007)
  - `test_email_is_required()` — omit email, assert fails (FR-007)
  - `test_email_must_be_valid_format()` — set email to 'not-an-email', assert fails (FR-007)
  - `test_hourly_rate_is_required()` — omit hourly_rate, assert fails (FR-007)
  - `test_hourly_rate_must_be_non_negative()` — set hourly_rate to -5, assert fails with min:0 (FR-007)
  - `test_hourly_rate_allows_zero()` — set hourly_rate to 0, assert passes (edge case)
  - `test_availability_status_must_be_valid_enum()` — set to 'invalid', assert fails (FR-007)
  - `test_availability_status_accepts_valid_values()` — test 'available', 'busy', 'off_duty' each pass (FR-007)
  - `test_about_max_length_2000()` — set about to 2001 chars, assert fails (FR-007)
  - `test_optional_fields_can_be_null()` — omit mobile, specialization, experience, about, service_area. Assert passes (FR-007)

- [ ] T010 [P] [US2] Create NurseRequestFormRequestTest at `Modules/RequestNurse/Tests/Unit/NurseRequestFormRequestTest.php` — extend `Tests\TestCase`, use `RefreshDatabase`. Include:
  - `test_valid_data_passes_validation()` — all required fields present with valid values (FR-008)
  - `test_patient_id_is_required()` — omit patient_id, assert fails (FR-008)
  - `test_patient_id_must_exist_in_users()` — set patient_id to 99999, assert fails with exists rule (FR-008)
  - `test_service_description_is_required()` — omit, assert fails (FR-008)
  - `test_service_description_max_1000()` — 1001 chars, assert fails (FR-008)
  - `test_preferred_date_is_required()` — omit, assert fails (FR-008)
  - `test_preferred_date_must_be_today_or_future()` — set to yesterday, assert fails (FR-008)
  - `test_preferred_date_accepts_today()` — set to today, assert passes (FR-008)
  - `test_preferred_time_must_be_valid_format()` — set to '25:00', assert fails (FR-008)
  - `test_preferred_time_accepts_valid_format()` — set to '14:30', assert passes (FR-008)
  - `test_duration_hours_is_required()` — omit, assert fails (FR-008)
  - `test_duration_hours_min_1()` — set to 0, assert fails (FR-008)
  - `test_duration_hours_max_24()` — set to 25, assert fails (FR-008)
  - `test_address_line_1_is_required()` — omit, assert fails (FR-008)
  - `test_city_is_required()` — omit, assert fails (FR-008)
  - `test_contact_number_is_required()` — omit, assert fails (FR-008)
  - `test_nurse_id_must_exist_if_provided()` — set nurse_id to 99999, assert fails (FR-008)
  - `test_optional_fields_can_be_null()` — omit preferred_time, address_line_2, state, country, postal_code, nurse_id, patient_notes, admin_notes. Assert passes (FR-008)

**Checkpoint**: `php artisan test --filter=NurseFormRequestTest` and `php artisan test --filter=NurseRequestFormRequestTest` both pass. All validation rules tested with positive and negative cases.

---

## Phase 5: User Story 3 - API Resource Transformation Coverage (Priority: P2)

**Goal**: Unit tests verifying NurseResource and NurseRequestResource produce correct JSON structure and data mappings

**Independent Test**: Run `php artisan test --filter=NurseResourceTest` and `php artisan test --filter=NurseRequestResourceTest` — all tests pass

### Implementation for User Story 3

- [ ] T011 [P] [US3] Create NurseResourceTest at `Modules/RequestNurse/Tests/Unit/NurseResourceTest.php` — extend `Tests\TestCase`, use `RefreshDatabase`. Use approach from research.md R3: create model via factory, load relationships, pass through `NurseResource::toArray(request())`. Include:
  - `test_nurse_resource_contains_all_expected_keys()` — assert output has keys: id, nurse_id, name, first_name, last_name, email, mobile, specialization, experience, about, hourly_rate, availability_status, service_area, profile_image, status, created_at, updated_at (FR-009)
  - `test_nurse_resource_maps_user_data_correctly()` — assert name, first_name, last_name, email, mobile match the associated User record (FR-009)
  - `test_nurse_resource_profile_image_is_null_when_no_media()` — assert profile_image is null for nurse without uploaded image (FR-009)
  - `test_nurse_resource_maps_nurse_fields_correctly()` — assert specialization, experience, about, hourly_rate, availability_status, service_area match model values (FR-009)

- [ ] T012 [P] [US3] Create NurseRequestResourceTest at `Modules/RequestNurse/Tests/Unit/NurseRequestResourceTest.php` — extend `Tests\TestCase`, use `RefreshDatabase`. Include:
  - `test_nurse_request_resource_contains_all_expected_keys()` — assert top-level keys: id, patient, nurse, service_description, request_date, preferred_date, preferred_time, duration_hours, address, contact_number, status, payment_status, total_amount, patient_notes, admin_notes, cancelled_by, cancellation_reason, created_at, updated_at (FR-010)
  - `test_patient_nested_object_structure()` — assert patient key contains: id, name, email, mobile (FR-010)
  - `test_nurse_nested_object_structure()` — assert nurse key contains: id, name, specialization (FR-010)
  - `test_nurse_is_null_when_unassigned()` — create request without nurse, assert nurse key is null (FR-010)
  - `test_address_nested_object_structure()` — assert address key contains: address_line_1, address_line_2, city, state, country, postal_code, latitude, longitude, full_address (FR-010)
  - `test_address_full_address_excludes_null_parts()` — create request with partial address, assert full_address excludes null fields (FR-010)
  - `test_preferred_date_formatted_as_y_m_d()` — assert preferred_date in 'Y-m-d' format (FR-010)
  - `test_payment_status_is_boolean()` — assert payment_status is boolean in output (FR-010)

**Checkpoint**: `php artisan test --filter=NurseResourceTest` and `php artisan test --filter=NurseRequestResourceTest` both pass. All API resource transformation mappings verified.

---

## Phase 6: User Story 4 - Controller Business Logic Coverage (Priority: P3)

**Goal**: Unit tests verifying status transition state machine and total_amount calculation logic

**Independent Test**: Run `php artisan test --filter=StatusTransitionTest` — all tests pass

### Implementation for User Story 4

- [ ] T013 [US4] Create StatusTransitionTest at `Modules/RequestNurse/Tests/Unit/StatusTransitionTest.php` — extend `Tests\TestCase`, use `RefreshDatabase`. Use feature-style test approach from research.md R4 with `actingAs()` for authenticated requests. Include:
  - `test_pending_to_confirmed_is_valid()` — create pending request, POST update_status with status=confirmed, assert response status true and DB updated (FR-011)
  - `test_pending_to_cancelled_is_valid()` — similar for pending→cancelled (FR-011)
  - `test_pending_to_in_progress_is_invalid()` — create pending request, POST update_status with status=in_progress, assert response status false (FR-011)
  - `test_pending_to_completed_is_invalid()` — similar for pending→completed (FR-011)
  - `test_confirmed_to_in_progress_is_valid()` — (FR-011)
  - `test_confirmed_to_cancelled_is_valid()` — (FR-011)
  - `test_in_progress_to_completed_is_valid()` — (FR-011)
  - `test_in_progress_to_cancelled_is_valid()` — (FR-011)
  - `test_completed_is_terminal_state()` — create completed request, attempt any transition, assert all rejected (FR-011)
  - `test_cancelled_is_terminal_state()` — similar for cancelled (FR-011)
  - `test_cancellation_records_cancelled_by_and_reason()` — cancel with reason, assert cancelled_by and cancellation_reason saved (FR-011)
  - `test_same_status_transition_is_invalid()` — pending→pending should be rejected (edge case)
  - `test_assign_nurse_calculates_total_amount()` — create nurse with hourly_rate=50, request with duration_hours=3, call assign_nurse, assert total_amount=150.00 (FR-012)
  - `test_assign_nurse_with_zero_hourly_rate()` — nurse with hourly_rate=0, assert total_amount=0 (edge case from spec)
  - `test_assign_nurse_sets_status_to_confirmed()` — after assignment, assert status is 'confirmed' (FR-012)
  - `test_store_without_nurse_defaults_total_to_zero()` — create request without nurse_id, assert total_amount=0 and status='pending' (FR-012)

**Checkpoint**: `php artisan test --filter=StatusTransitionTest` passes. All valid/invalid status transitions and total_amount calculations verified.

---

## Phase 7: User Story 5 - Permission Seeder Verification (Priority: P3)

**Goal**: Unit tests verifying RequestNursePermissionSeeder creates correct roles, permissions, and assignments

**Independent Test**: Run `php artisan test --filter=PermissionSeederTest` — all tests pass

### Implementation for User Story 5

- [ ] T014 [US5] Create PermissionSeederTest at `Modules/RequestNurse/Tests/Unit/PermissionSeederTest.php` — extend `Tests\TestCase`, use `RefreshDatabase`. Follow approach from research.md R5: create prerequisite admin/demo_admin roles in setUp(), then run seeder. Include:
  - `test_seeder_creates_nurse_role()` — assert Role 'nurse' exists with is_fixed=true after seeding (FR-013)
  - `test_seeder_creates_all_request_nurse_permissions()` — assert all 5 request nurse permissions exist: view_request_nurse, add_request_nurse, edit_request_nurse, delete_request_nurse, request_nurse_assign (FR-013)
  - `test_seeder_creates_all_nurses_management_permissions()` — assert all 4 nurse management permissions exist: view_nurses, add_nurses, edit_nurses, delete_nurses (FR-013)
  - `test_admin_role_has_all_permissions()` — assert admin role has all 9 permissions (FR-013)
  - `test_demo_admin_role_has_all_permissions()` — assert demo_admin role has all 9 permissions (FR-013)
  - `test_nurse_role_has_only_view_permission()` — assert nurse role has exactly ['view_request_nurse'] and no others (FR-013)
  - `test_seeder_is_idempotent()` — run seeder twice, assert no duplicate roles or permissions, counts unchanged (edge case)

**Checkpoint**: `php artisan test --filter=PermissionSeederTest` passes. All role/permission creation and assignments verified.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final verification, cleanup, and documentation

- [ ] T015 Run full test suite with `php artisan test --filter=RequestNurse` and verify all tests pass (SC-001)
- [ ] T016 Verify test isolation — run tests in reverse order or individually to confirm no shared state (SC-007)
- [ ] T017 Remove FactorySmokeTest at `Modules/RequestNurse/Tests/Unit/FactorySmokeTest.php` if its assertions are covered by the user story tests (cleanup)
- [ ] T018 Verify test execution time is under 30 seconds (SC-006)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 (factories must exist before smoke test)
- **User Stories (Phase 3-7)**: All depend on Phase 2 completion (factories verified)
  - US1 (Phase 3), US2 (Phase 4), US3 (Phase 5) can proceed in parallel
  - US4 (Phase 6) can proceed in parallel with others
  - US5 (Phase 7) can proceed in parallel with others
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1 - Models)**: No dependencies on other stories. Needs only factories from Phase 1.
- **User Story 2 (P2 - Form Requests)**: No dependencies on other stories. Uses Validator directly.
- **User Story 3 (P2 - Resources)**: No dependencies on other stories. Uses factories + resource classes.
- **User Story 4 (P3 - Status Transitions)**: No dependencies on other stories. Uses factories + HTTP tests.
- **User Story 5 (P3 - Seeder)**: No dependencies on other stories. Uses seeder + Spatie role/permission API.

### Within Each User Story

- All test classes within a story marked [P] can be written in parallel
- Each test class is self-contained (own setUp, own factory calls)

### Parallel Opportunities

```
Phase 1: T002 ──┐
                 ├── parallel (different files)
Phase 1: T003 ──┘

Phase 1: T004 ──┐
                 ├── parallel (different files)
Phase 1: T005 ──┘

After Phase 2 completes:
  Phase 3: T007 ──┐
  Phase 3: T008 ──┤
  Phase 4: T009 ──┤
  Phase 4: T010 ──┤── ALL can run in parallel
  Phase 5: T011 ──┤
  Phase 5: T012 ──┤
  Phase 6: T013 ──┤
  Phase 7: T014 ──┘
```

---

## Parallel Example: All User Stories

```bash
# After Phase 2 is complete, launch all test file creation in parallel:
Task: "Create NurseModelTest in Modules/RequestNurse/Tests/Unit/NurseModelTest.php"
Task: "Create NurseRequestModelTest in Modules/RequestNurse/Tests/Unit/NurseRequestModelTest.php"
Task: "Create NurseFormRequestTest in Modules/RequestNurse/Tests/Unit/NurseFormRequestTest.php"
Task: "Create NurseRequestFormRequestTest in Modules/RequestNurse/Tests/Unit/NurseRequestFormRequestTest.php"
Task: "Create NurseResourceTest in Modules/RequestNurse/Tests/Unit/NurseResourceTest.php"
Task: "Create NurseRequestResourceTest in Modules/RequestNurse/Tests/Unit/NurseRequestResourceTest.php"
Task: "Create StatusTransitionTest in Modules/RequestNurse/Tests/Unit/StatusTransitionTest.php"
Task: "Create PermissionSeederTest in Modules/RequestNurse/Tests/Unit/PermissionSeederTest.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (factories + model wiring)
2. Complete Phase 2: Smoke test to verify factories
3. Complete Phase 3: NurseModelTest + NurseRequestModelTest
4. **STOP and VALIDATE**: `php artisan test --filter=NurseModelTest` and `--filter=NurseRequestModelTest`
5. Models are the foundation — if these pass, all other stories can proceed safely

### Incremental Delivery

1. Phase 1 + 2 → Factories verified
2. Phase 3 (US1) → Model tests pass → Core data layer confidence
3. Phase 4 (US2) → Validation tests pass → Input integrity confidence
4. Phase 5 (US3) → Resource tests pass → API contract confidence
5. Phase 6 (US4) → Status/amount tests pass → Business logic confidence
6. Phase 7 (US5) → Seeder tests pass → Access control confidence
7. Phase 8 → Full suite green, clean, fast

### Parallel Strategy

All 8 test files (T007-T014) are independent and touch different files. After Phase 2, they can all be written simultaneously by different agents or in parallel tool calls.

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story is independently completable and testable
- All test classes extend `Tests\TestCase` and use `RefreshDatabase` (except pure constant/static tests)
- Factories use `Modules\RequestNurse\Database\Factories` namespace per research.md R1
- Form request tests use `Validator::make()` approach per research.md R2
- Status transition tests use feature-style HTTP tests per research.md R4
- Commit after each phase or logical group
