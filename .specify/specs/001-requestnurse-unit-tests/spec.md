# Feature Specification: RequestNurse Module Unit Tests

**Feature Branch**: `001-requestnurse-unit-tests`
**Created**: 2026-03-09
**Status**: Draft
**Input**: User description: "create unit tests for requestnurse module"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Model Logic Verification (Priority: P1)

As a developer working on the Espitalia RequestNurse module, I need unit tests that verify all model behaviors (relationships, scopes, accessors, casts, and constants) for both `Nurse` and `NurseRequest` models so that future code changes do not introduce regressions.

**Why this priority**: Models are the foundation of the module. Every controller, API endpoint, and view depends on correct model behavior. Testing models first ensures the data layer is reliable.

**Independent Test**: Can be fully tested by running model unit tests in isolation with an in-memory SQLite database, and delivers confidence that all model relationships resolve correctly, scopes filter as expected, accessors compute correct values, and attribute casts produce the right types.

**Acceptance Scenarios**:

1. **Given** a Nurse model instance, **When** the `user()` relationship is accessed, **Then** it returns the associated User record via `nurse_id`.
2. **Given** a Nurse model instance, **When** the `vendor()` relationship is accessed, **Then** it returns the associated vendor User record via `vendor_id`.
3. **Given** a Nurse model instance, **When** the `nurseRequests()` relationship is accessed, **Then** it returns a collection of associated NurseRequest records.
4. **Given** a Nurse with an associated user, **When** the `full_name` accessor is accessed, **Then** it returns the user's `full_name` attribute.
5. **Given** multiple nurses with varying availability and status, **When** the `available()` scope is applied, **Then** only nurses with `availability_status='available'` AND `status=1` are returned.
6. **Given** multiple nurses with varying status values, **When** the `active()` scope is applied, **Then** only nurses with `status=1` are returned.
7. **Given** a Nurse model, **When** the `hourly_rate` attribute is set and retrieved, **Then** it is cast to a `double` type.
8. **Given** a NurseRequest model instance, **When** the `patient()` relationship is accessed, **Then** it returns the associated User record via `patient_id`.
9. **Given** a NurseRequest model instance, **When** the `nurse()` relationship is accessed, **Then** it returns the associated User record via `nurse_id`.
10. **Given** a NurseRequest with address fields, **When** the `full_address` accessor is accessed, **Then** it returns a concatenated string of all non-null address parts.
11. **Given** a NurseRequest with a status value, **When** the `status_badge` accessor is accessed, **Then** it returns an HTML badge string containing the translated status text.
12. **Given** multiple NurseRequest records with varying statuses, **When** the `pending()` scope is applied, **Then** only records with `status='pending'` are returned.
13. **Given** multiple NurseRequest records, **When** the `byNurse($nurseId)` scope is applied, **Then** only records matching that nurse_id are returned.
14. **Given** multiple NurseRequest records, **When** the `byPatient($patientId)` scope is applied, **Then** only records matching that patient_id are returned.
15. **Given** the NurseRequest model, **When** its status constants are referenced, **Then** the STATUSES array contains exactly: pending, confirmed, in_progress, completed, cancelled.

---

### User Story 2 - Form Request Validation Coverage (Priority: P2)

As a developer, I need unit tests for `NurseFormRequest` and `NurseRequestFormRequest` validation rules so that invalid data is reliably rejected and valid data passes through.

**Why this priority**: Form requests are the gatekeepers of data integrity. Validating these ensures bad data never reaches the database.

**Independent Test**: Can be fully tested by instantiating form request objects with various payloads and asserting validation passes or fails with expected error messages.

**Acceptance Scenarios**:

1. **Given** a NurseFormRequest, **When** `first_name` is missing, **Then** validation fails with a required error.
2. **Given** a NurseFormRequest, **When** `email` is not a valid email format, **Then** validation fails.
3. **Given** a NurseFormRequest, **When** `hourly_rate` is a negative number, **Then** validation fails with a min:0 error.
4. **Given** a NurseFormRequest, **When** `profile_image` exceeds 2048 KB, **Then** validation fails.
5. **Given** a NurseFormRequest, **When** `availability_status` is set to an invalid value, **Then** validation fails.
6. **Given** a NurseFormRequest, **When** all required fields are provided with valid values, **Then** validation passes.
7. **Given** a NurseRequestFormRequest, **When** `patient_id` references a non-existent user, **Then** validation fails.
8. **Given** a NurseRequestFormRequest, **When** `preferred_date` is a date in the past, **Then** validation fails.
9. **Given** a NurseRequestFormRequest, **When** `duration_hours` exceeds 24, **Then** validation fails.
10. **Given** a NurseRequestFormRequest, **When** `preferred_time` is not in H:i format, **Then** validation fails.
11. **Given** a NurseRequestFormRequest, **When** all required fields are valid, **Then** validation passes.

---

### User Story 3 - API Resource Transformation Coverage (Priority: P2)

As a developer, I need unit tests for `NurseResource` and `NurseRequestResource` transformers to ensure API responses have the correct structure and data mappings.

**Why this priority**: API resources define the contract with frontend and mobile clients. Incorrect transformations break client integrations.

**Independent Test**: Can be tested by creating model instances and passing them through the resource transformers, then asserting the output JSON structure matches the expected schema.

**Acceptance Scenarios**:

1. **Given** a Nurse model with associated user data, **When** transformed via NurseResource, **Then** the output contains `id`, `nurse_id`, `name`, `first_name`, `last_name`, `email`, `mobile`, `specialization`, `experience`, `about`, `hourly_rate`, `availability_status`, `service_area`, `profile_image`, `status`, `created_at`, `updated_at`.
2. **Given** a Nurse with no profile image, **When** transformed via NurseResource, **Then** `profile_image` is null.
3. **Given** a NurseRequest with an assigned nurse and patient, **When** transformed via NurseRequestResource, **Then** the output contains nested `patient` and `nurse` objects with proper fields, plus the `address` nested object.
4. **Given** a NurseRequest with no assigned nurse, **When** transformed via NurseRequestResource, **Then** the `nurse` field is null.
5. **Given** a NurseRequest with partial address fields (some null), **When** transformed via NurseRequestResource, **Then** the `address.full_address` field correctly excludes null parts.

---

### User Story 4 - Controller Business Logic Coverage (Priority: P3)

As a developer, I need unit tests for the key business logic within the NurseRequestsController, specifically the status transition validation and total amount calculation.

**Why this priority**: The status state machine and pricing calculation are critical business rules that must remain correct. These are the highest-risk logic areas in the controllers.

**Independent Test**: Can be tested by invoking controller methods with mocked dependencies and asserting correct state transitions, calculations, and error responses.

**Acceptance Scenarios**:

1. **Given** a nurse request in `pending` status, **When** a status update to `confirmed` is attempted, **Then** the transition succeeds.
2. **Given** a nurse request in `pending` status, **When** a status update to `in_progress` is attempted, **Then** the transition is rejected as invalid.
3. **Given** a nurse request in `completed` status, **When** any status update is attempted, **Then** the transition is rejected (terminal state).
4. **Given** a nurse request in `cancelled` status, **When** any status update is attempted, **Then** the transition is rejected (terminal state).
5. **Given** a nurse request being cancelled, **When** `cancelled_by` and `cancellation_reason` are provided, **Then** both fields are persisted on the request.
6. **Given** a nurse with an `hourly_rate` of 50.00 and a request with `duration_hours` of 3, **When** the nurse is assigned to the request, **Then** `total_amount` is calculated as 150.00.
7. **Given** a request is being created, **When** no nurse is assigned, **Then** `total_amount` defaults to 0 and status is `pending`.

---

### User Story 5 - Permission Seeder Verification (Priority: P3)

As a developer, I need unit tests for `RequestNursePermissionSeeder` to confirm that the correct roles, permissions, and role-permission assignments are created.

**Why this priority**: Permissions govern access control. If the seeder is incorrect, users may gain unauthorized access or lose legitimate access.

**Independent Test**: Can be tested by running the seeder against a test database and asserting all expected roles, permissions, and assignments exist.

**Acceptance Scenarios**:

1. **Given** the seeder runs, **When** completed, **Then** a `nurse` role exists with `is_fixed=true`.
2. **Given** the seeder runs, **When** completed, **Then** all 9 expected permissions exist: `view_request_nurse`, `add_request_nurse`, `edit_request_nurse`, `delete_request_nurse`, `request_nurse_assign`, `view_nurses`, `add_nurses`, `edit_nurses`, `delete_nurses`.
3. **Given** the seeder runs, **When** completed, **Then** the `admin` role has all 9 permissions assigned.
4. **Given** the seeder runs, **When** completed, **Then** the `nurse` role has only `view_request_nurse` permission assigned.

---

### Edge Cases

- What happens when a Nurse's associated user is soft-deleted but the Nurse profile still exists?
- How does the `full_address` accessor handle all-null address fields?
- What happens when `nurseProfile()` relationship on NurseRequest is accessed but no Nurse profile record exists for the user?
- How do model scopes behave when the query returns zero results?
- What happens when `hourly_rate` is set to 0 and used in total_amount calculation?
- How does NurseRequest behave when `preferred_time` is null?
- What happens when duplicate status transitions are attempted (e.g., pending to pending)?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Test suite MUST cover all relationships defined on the Nurse model (`user`, `vendor`, `nurseRequests`).
- **FR-002**: Test suite MUST cover all relationships defined on the NurseRequest model (`patient`, `nurse`, `nurseProfile`).
- **FR-003**: Test suite MUST verify all model scopes (`available`, `active`, `pending`, `confirmed`, `byNurse`, `byPatient`, `byStatus`) return correctly filtered results.
- **FR-004**: Test suite MUST verify all model accessors (`full_name` on Nurse, `full_address` and `status_badge` on NurseRequest) return expected values.
- **FR-005**: Test suite MUST verify attribute casts produce the correct PHP types (`hourly_rate` as double, `status` as integer, `preferred_date` as date, `payment_status` as boolean, `total_amount` as double, `latitude`/`longitude` as decimal).
- **FR-006**: Test suite MUST verify NurseRequest status constants match the defined workflow states.
- **FR-007**: Test suite MUST verify NurseFormRequest validation rules accept valid data and reject invalid data for each field.
- **FR-008**: Test suite MUST verify NurseRequestFormRequest validation rules accept valid data and reject invalid data for each field.
- **FR-009**: Test suite MUST verify NurseResource produces the correct JSON structure with all expected keys.
- **FR-010**: Test suite MUST verify NurseRequestResource produces the correct JSON structure including nested `patient`, `nurse`, and `address` objects.
- **FR-011**: Test suite MUST verify the status transition logic allows only valid transitions: pending to confirmed/cancelled, confirmed to in_progress/cancelled, in_progress to completed/cancelled.
- **FR-012**: Test suite MUST verify total_amount calculation as `hourly_rate * duration_hours`.
- **FR-013**: Test suite MUST verify the RequestNursePermissionSeeder creates the correct roles, permissions, and assignments.
- **FR-014**: Test suite MUST use factories or setup methods to create test data rather than relying on production seeders.
- **FR-015**: Test suite MUST run in isolation using an in-memory SQLite test database.

### Key Entities

- **Nurse**: Represents a nurse's professional profile. Links to a User record. Has specialization, experience, hourly rate, availability status, service area. Has many NurseRequests.
- **NurseRequest**: Represents a patient's request for nursing services. Links to a patient User and optionally to a nurse User. Contains service details, scheduling (preferred date/time, duration), location (address with coordinates), payment tracking, and a status workflow (pending > confirmed > in_progress > completed, with cancellation at any non-terminal state).
- **NurseFormRequest**: Validation rules for creating/updating nurse profiles.
- **NurseRequestFormRequest**: Validation rules for creating/updating nurse service requests.
- **NurseResource**: API response transformer for Nurse model.
- **NurseRequestResource**: API response transformer for NurseRequest model with nested patient, nurse, and address data.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All unit tests pass successfully when run with `php artisan test --filter=RequestNurse`.
- **SC-002**: Test suite covers 100% of model relationships, scopes, and accessors for both Nurse and NurseRequest models.
- **SC-003**: Test suite covers all validation rules in both form request classes, with at least one positive and one negative test per rule.
- **SC-004**: Test suite covers all API resource transformation mappings for both NurseResource and NurseRequestResource.
- **SC-005**: Test suite verifies all valid and invalid status transitions in the nurse request workflow.
- **SC-006**: Test suite executes in under 30 seconds on standard development hardware.
- **SC-007**: Tests run independently in any order without side effects between test cases.

## Assumptions

- The project uses PHPUnit with Laravel's testing framework and SQLite in-memory database for testing (as stated in CLAUDE.md).
- Model factories will be created as part of the testing effort since none currently exist for RequestNurse models.
- The `BaseModel` parent class (with SoftDeletes, MediaLibrary, audit fields) is assumed to be tested separately and does not need re-testing within this module's tests.
- Global helper functions (`sendNotificationOnBookingUpdate`, `comman_custom_response`) are assumed to work correctly and will be mocked where needed.
- The Spatie permission package infrastructure (roles, permissions tables) is assumed to be functional for seeder tests.
- Tests will be located under `Modules/RequestNurse/Tests/Unit/` following the project's modular testing convention.

## Scope Boundaries

### In Scope

- Unit tests for Nurse and NurseRequest model logic (relationships, scopes, accessors, casts, constants)
- Unit tests for NurseFormRequest and NurseRequestFormRequest validation rules
- Unit tests for NurseResource and NurseRequestResource API transformers
- Unit tests for status transition business logic
- Unit tests for total_amount calculation logic
- Unit tests for the RequestNursePermissionSeeder
- Creating necessary model factories

### Out of Scope

- Feature/integration tests for HTTP endpoints (controllers handling full request lifecycle)
- End-to-end browser tests
- Testing of Blade views and frontend rendering
- Testing of notification delivery mechanisms
- Testing of DataTables server-side rendering
- Testing of file upload/MediaLibrary integration
- Performance or load testing
- Testing BaseModel inherited behaviors
