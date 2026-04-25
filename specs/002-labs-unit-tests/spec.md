# Feature Specification: Comprehensive Unit Test Suite for Radiology Lab Module

**Feature Branch**: `002-labs-unit-tests`
**Created**: 2026-03-10
**Status**: Draft
**Input**: User description: "create unit tests for labs module"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Model Behavior Validation (Priority: P1)

Developers need to verify that all lab-related models (LabTest, TestOrder, TestOrderItem, TestReport, LabTestCategory) behave correctly in isolation, ensuring data integrity, relationship handling, and business logic work as expected without external dependencies.

**Why this priority**: Model correctness is foundational - all other functionality depends on these models working correctly. Without reliable model tests, bugs in data handling can cascade throughout the application.

**Independent Test**: Can be fully tested by creating model instances with various data combinations and verifying attribute casting, relationship loading, scope filtering, and business logic methods return expected results. Delivers confidence in core data layer functionality.

**Acceptance Scenarios**:

1. **Given** a LabTest model with required attributes, **When** the instance is created, **Then** all attributes are correctly cast to their expected types (boolean for status, float for price)
2. **Given** a TestOrder with empty order_number, **When** saved to database, **Then** order number is auto-generated in format LAB-YYYY-XXXX
3. **Given** a TestOrder in 'pending' status, **When** checking allowed transitions, **Then** only 'confirmed' and 'cancelled' are permitted
4. **Given** a TestOrderItem with result data, **When** accessing result attributes, **Then** formatted badges and labels display correctly
5. **Given** models with relationships, **When** loading related data, **Then** all associations (belongsTo, hasMany) resolve correctly

---

### User Story 2 - Order Status Workflow Validation (Priority: P1)

The system must enforce strict status transition rules for test orders to prevent invalid state changes that could compromise lab workflow integrity (e.g., cannot jump from 'pending' directly to 'completed', cannot modify 'delivered' or 'cancelled' orders).

**Why this priority**: Status workflow is critical for lab operations and audit trails. Invalid transitions can lead to data corruption, workflow confusion, and regulatory compliance issues.

**Independent Test**: Can be fully tested by attempting all possible status transitions on TestOrder models and verifying that valid transitions succeed while invalid ones are rejected. Delivers guarantee of workflow integrity.

**Acceptance Scenarios**:

1. **Given** a TestOrder in 'pending' status, **When** attempting to transition to 'completed' (skipping intermediate steps), **Then** transition is rejected
2. **Given** a TestOrder in 'delivered' status, **When** attempting any status change, **Then** transition is rejected (terminal state)
3. **Given** a TestOrder in 'confirmed' status, **When** transitioning to 'sample_collected', **Then** transition succeeds
4. **Given** a TestOrder in any pre-terminal status, **When** transitioning to 'cancelled', **Then** transition succeeds (except from 'delivered')

---

### User Story 3 - Scope and Query Builder Validation (Priority: P2)

Developers need confidence that query scopes (active tests, pending orders, tests by department, orders by status) correctly filter data to prevent incorrect data retrieval in application features.

**Why this priority**: Incorrect query scopes can cause wrong data to appear in views, reports, and business logic, leading to operational errors. While important, this is secondary to core model behavior.

**Independent Test**: Can be fully tested by seeding test data with various states and applying scopes, then verifying the correct subset of records is returned. Delivers confidence in data filtering accuracy.

**Acceptance Scenarios**:

1. **Given** multiple LabTests with different status values, **When** active() scope is applied, **Then** only tests with status=1 are returned
2. **Given** LabTests in both 'laboratory' and 'radiology' departments, **When** laboratory() scope is applied, **Then** only laboratory tests are returned
3. **Given** TestOrders with various statuses, **When** pending() scope is applied, **Then** only orders with status='pending' are returned
4. **Given** TestOrders for different patients, **When** byPatient() scope is applied with patient ID, **Then** only that patient's orders are returned

---

### User Story 4 - Accessor and Mutator Validation (Priority: P3)

The system provides formatted output for display attributes (badges, labels, formatted prices) that must render correctly to ensure consistent UI presentation across the application.

**Why this priority**: While important for UI consistency, formatting issues are less critical than core business logic. These are easier to detect in integration tests if unit tests are missed.

**Independent Test**: Can be fully tested by creating model instances and verifying accessor methods return properly formatted strings/HTML. Delivers confidence in presentation layer data.

**Acceptance Scenarios**:

1. **Given** a LabTest with default_price of 150.5, **When** accessing formatted_price attribute, **Then** returns "150.50"
2. **Given** a TestOrder with status='pending', **When** accessing status_badge attribute, **Then** returns HTML badge with warning styling
3. **Given** a TestOrder with priority='urgent', **When** accessing priority_badge attribute, **Then** returns HTML badge with warning styling
4. **Given** a LabTest with sample_type='blood', **When** accessing sample_type_label attribute, **Then** returns translated label text

---

### User Story 5 - Edge Case and Boundary Validation (Priority: P3)

The system must handle edge cases gracefully including null values, missing relationships, invalid data types, and boundary conditions to prevent runtime errors and data corruption.

**Why this priority**: Edge cases are important for robustness but less frequent in normal operation. Good to have but not blocking core functionality.

**Independent Test**: Can be fully tested by deliberately creating models with problematic data (nulls, invalid relationships, boundary values) and verifying error handling or safe defaults. Delivers confidence in system resilience.

**Acceptance Scenarios**:

1. **Given** a LabTest with null sample_type, **When** accessing sample_type_label, **Then** returns safe default "-" instead of error
2. **Given** a TestOrder with deleted patient (soft deleted), **When** loading patient relationship, **Then** returns null without crashing
3. **Given** a TestOrder being created when last order was LAB-2026-9999, **When** auto-generating order number, **Then** correctly increments to LAB-2026-10000
4. **Given** a TestOrderItem with null result_date, **When** accessing result attributes, **Then** handles gracefully without type error

---

### Edge Cases

- What happens when order number generation encounters concurrent requests (race condition)?
- How does the system handle status transitions when business logic changes (adding new statuses)?
- What occurs when a lab test is soft-deleted while referenced by active order items?
- How does the system behave when relationship loading fails (database connection issues)?
- What happens when accessor methods receive unexpected data types or formats?
- How does price calculation handle very large numbers or precision edge cases?
- What occurs when translation keys are missing for badge/label generation?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Unit tests MUST validate all model attribute casting (boolean, float, date, datetime types)
- **FR-002**: Unit tests MUST verify auto-generation of TestOrder order_number in format LAB-YYYY-XXXX with proper sequential numbering
- **FR-003**: Unit tests MUST validate all status transition rules defined in TestOrder::ALLOWED_TRANSITIONS
- **FR-004**: Unit tests MUST test all model relationships (belongsTo, hasMany) resolve to correct related models
- **FR-005**: Unit tests MUST verify all query scopes filter data correctly (active, pending, byPatient, byStatus, etc.)
- **FR-006**: Unit tests MUST validate all accessor methods return correctly formatted output (badges, labels, prices)
- **FR-007**: Unit tests MUST test soft delete behavior across all models
- **FR-008**: Unit tests MUST verify the canTransitionTo() business logic method on TestOrder
- **FR-009**: Unit tests MUST validate constant arrays (SAMPLE_TYPES, DEPARTMENTS, STATUSES, PRIORITIES)
- **FR-010**: Unit tests MUST test edge cases including null values, missing relationships, and boundary conditions
- **FR-011**: Unit tests MUST be isolated (not depend on database state, external services, or other tests)
- **FR-012**: Unit tests MUST use in-memory database or mocking to ensure fast execution
- **FR-013**: Unit tests MUST achieve minimum 90% code coverage for all model classes
- **FR-014**: Unit tests MUST validate fillable attributes and guarded attributes behave as specified
- **FR-015**: Unit tests MUST test the boot() method logic for order number generation including race conditions

### Key Entities

- **LabTest**: Represents individual diagnostic tests with attributes including name, code, sample type, department (laboratory/radiology), price, and status. Relationships to LabTestCategory and TestOrderItem.

- **TestOrder**: Represents an order for one or more lab tests with workflow status (pending → confirmed → sample_collected → processing → completed → delivered), priority levels, payment tracking, and auto-generated order numbers. Relationships to Patient, Doctor, Lab Technician, TestOrderItem, and TestReport.

- **TestOrderItem**: Individual test within an order with pricing, status tracking, result values, reference ranges, and result status (normal/abnormal/critical). Relationships to TestOrder and LabTest.

- **TestReport**: Report document associated with test order containing final results and findings.

- **LabTestCategory**: Organizational categorization for lab tests (e.g., Blood Tests, Imaging, Microbiology).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All model unit tests execute in under 5 seconds total (fast feedback for developers)
- **SC-002**: Test suite achieves 90%+ code coverage for all five model classes (LabTest, TestOrder, TestOrderItem, TestReport, LabTestCategory)
- **SC-003**: Zero test failures when models are modified according to business requirements (tests verify behavior, not implementation)
- **SC-004**: Developers can identify failing business logic within 30 seconds of test failure (clear, descriptive test names and assertions)
- **SC-005**: Test suite catches regressions before code review 95%+ of the time (comprehensive edge case coverage)
- **SC-006**: New developers can understand model behavior by reading test cases (tests serve as living documentation)
- **SC-007**: Test suite runs successfully in CI/CD pipeline without database connection (fully isolated unit tests)
- **SC-008**: All 15 functional requirements have corresponding passing test cases
- **SC-009**: Test maintenance time is under 2 hours per month (tests remain relevant as code evolves)

## Assumptions *(optional)*

- The codebase uses PHPUnit as the testing framework (standard for Laravel projects)
- Models extend a BaseModel class that provides common functionality
- The project uses Laravel's Eloquent ORM for database interactions
- Translation keys used in accessor methods exist in language files
- Soft delete functionality is provided by Laravel's SoftDeletes trait
- The application uses MySQL or compatible database (based on migration files)
- Test data factories exist or can be created for seeding test data
- The project has a continuous integration pipeline where tests will run
- Database transactions or in-memory SQLite will be used for test isolation

## Dependencies *(optional)*

- Existing model classes must remain stable during test development
- Translation files must contain keys referenced in accessor methods
- BaseModel class functionality must be understood for proper test setup
- PHPUnit configuration must be properly set up in the project

## Out of Scope *(optional)*

- Integration tests for API endpoints (covered separately)
- Frontend test coverage (JavaScript/Vue components)
- Performance testing and load testing
- Testing of controllers, services, or business logic outside models
- Security penetration testing
- End-to-end user workflow testing
- Testing of database migrations themselves
- Testing of third-party package functionality
- Browser-based acceptance testing
- Testing of authentication and authorization logic (unless directly in models)
