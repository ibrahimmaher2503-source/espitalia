---
description: "Implementation tasks for RadiologyLab module unit tests"
---

# Tasks: Comprehensive Unit Test Suite for Radiology Lab Module

**Input**: Design documents from `/specs/002-labs-unit-tests/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Tests**: This feature IS about creating tests, so all tasks involve test development.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each testing area.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Module root**: `Modules/RadiologyLab/`
- **Test location**: `Modules/RadiologyLab/Tests/Unit/`
- **Factories**: `Modules/RadiologyLab/database/factories/`
- **Models**: `Modules/RadiologyLab/Models/`

## Phase 1: Setup (Test Infrastructure)

**Purpose**: Initialize test directory structure and base configuration

- [x] T001 Create test directory structure at Modules/RadiologyLab/Tests/Unit/
- [x] T002 Verify PHPUnit configuration in phpunit.xml for SQLite in-memory database
- [x] T003 [P] Create base test helper methods in Tests/TestCase.php if needed

---

## Phase 2: Foundational (Test Data Factories)

**Purpose**: Create model factories needed by ALL test suites - MUST complete before any test writing

**⚠️ CRITICAL**: No unit test can be written until factories exist for creating test data

- [x] T004 Create LabTestCategoryFactory in Modules/RadiologyLab/database/factories/LabTestCategoryFactory.php
- [x] T005 [P] Create LabTestFactory with state methods (laboratory, radiology, active) in Modules/RadiologyLab/database/factories/LabTestFactory.php
- [x] T006 [P] Create TestOrderFactory with state methods (pending, confirmed, completed, cancelled, urgent, stat) in Modules/RadiologyLab/database/factories/TestOrderFactory.php
- [x] T007 [P] Create TestOrderItemFactory with state methods (withResults, pending, completed) in Modules/RadiologyLab/database/factories/TestOrderItemFactory.php
- [x] T008 [P] Create TestReportFactory with state methods (draft, final, amended) in Modules/RadiologyLab/database/factories/TestReportFactory.php
- [x] T009 Add HasFactory trait to LabTestCategory model in Modules/RadiologyLab/Models/LabTestCategory.php
- [x] T010 [P] Add HasFactory trait to LabTest model in Modules/RadiologyLab/Models/LabTest.php
- [x] T011 [P] Add HasFactory trait to TestOrder model in Modules/RadiologyLab/Models/TestOrder.php
- [x] T012 [P] Add HasFactory trait to TestOrderItem model in Modules/RadiologyLab/Models/TestOrderItem.php
- [x] T013 [P] Add HasFactory trait to TestReport model in Modules/RadiologyLab/Models/TestReport.php

**Checkpoint**: Factory infrastructure ready - unit test implementation can now begin in parallel

---

## Phase 3: User Story 1 - Model Behavior Validation (Priority: P1) 🎯 MVP

**Goal**: Verify that all lab-related models behave correctly in isolation, ensuring data integrity, relationship handling, and business logic work as expected

**Independent Test**: Create model instances with various data combinations and verify attribute casting, relationship loading, scope filtering, and business logic methods return expected results. Run: `php artisan test Modules/RadiologyLab/Tests/Unit/`

### Core Model Tests for User Story 1

- [x] T014 [P] [US1] Create LabTestCategoryTest in Modules/RadiologyLab/Tests/Unit/LabTestCategoryTest.php
- [x] T015 [US1] Test LabTestCategory attribute casting (status boolean, display_order integer) in LabTestCategoryTest.php
- [x] T016 [US1] Test LabTestCategory relationships (hasMany labTests) in LabTestCategoryTest.php
- [x] T017 [US1] Test LabTestCategory slug auto-generation in LabTestCategoryTest.php
- [x] T018 [US1] Test LabTestCategory soft delete behavior in LabTestCategoryTest.php

- [x] T019 [P] [US1] Create LabTestTest in Modules/RadiologyLab/Tests/Unit/LabTestTest.php
- [x] T020 [US1] Test LabTest attribute casting (status boolean, default_price float) in LabTestTest.php
- [x] T021 [US1] Test LabTest constant validation (SAMPLE_TYPES, DEPARTMENTS) in LabTestTest.php
- [x] T022 [US1] Test LabTest relationships (belongsTo category, hasMany orderItems) in LabTestTest.php
- [x] T023 [US1] Test LabTest slug auto-generation in LabTestTest.php
- [x] T024 [US1] Test LabTest soft delete behavior in LabTestTest.php

- [x] T025 [P] [US1] Create TestOrderTest in Modules/RadiologyLab/Tests/Unit/TestOrderTest.php
- [x] T026 [US1] Test TestOrder attribute casting (order_date date, amounts float) in TestOrderTest.php
- [x] T027 [US1] Test TestOrder status constants defined (all 7 statuses) in TestOrderTest.php
- [x] T028 [US1] Test TestOrder priority constants defined (all 3 priorities) in TestOrderTest.php
- [x] T029 [US1] Test TestOrder relationships (patient, doctor, labTechnician, items, reports) in TestOrderTest.php
- [x] T030 [US1] Test TestOrder soft delete behavior in TestOrderTest.php

- [x] T031 [P] [US1] Create TestOrderItemTest in Modules/RadiologyLab/Tests/Unit/TestOrderItemTest.php
- [x] T032 [US1] Test TestOrderItem attribute casting (result_date datetime, price float) in TestOrderItemTest.php
- [x] T033 [US1] Test TestOrderItem status constants defined in TestOrderItemTest.php
- [x] T034 [US1] Test TestOrderItem relationships (testOrder, labTest, resultEnteredBy) in TestOrderItemTest.php
- [x] T035 [US1] Test TestOrderItem soft delete behavior in TestOrderItemTest.php

- [x] T036 [P] [US1] Create TestReportTest in Modules/RadiologyLab/Tests/Unit/TestReportTest.php
- [x] T037 [US1] Test TestReport attribute casting (report_date date) in TestReportTest.php
- [x] T038 [US1] Test TestReport type and status constants defined in TestReportTest.php
- [x] T039 [US1] Test TestReport relationships (testOrder, generatedBy) in TestReportTest.php
- [x] T040 [US1] Test TestReport media collection registration (registerMediaCollections) in TestReportTest.php
- [x] T041 [US1] Test TestReport soft delete behavior in TestReportTest.php

**Checkpoint**: Basic model functionality validated - all models can be instantiated, casts work, relationships load

---

## Phase 4: User Story 2 - Order Status Workflow Validation (Priority: P1)

**Goal**: Enforce strict status transition rules for test orders to prevent invalid state changes that could compromise lab workflow integrity

**Independent Test**: Attempt all possible status transitions on TestOrder models and verify valid transitions succeed while invalid ones are rejected. Run: `php artisan test --filter=TestOrderTest::test.*transition`

### Order Number Auto-Generation Tests for User Story 2

- [x] T042 [P] [US2] Test TestOrder auto-generates order_number on creation in TestOrderTest.php
- [x] T043 [P] [US2] Test order_number format matches LAB-YYYY-XXXX pattern in TestOrderTest.php
- [x] T044 [US2] Test sequential order numbering (order 2 = order 1 + 1) in TestOrderTest.php
- [x] T045 [US2] Test first order of year generates LAB-YYYY-0001 in TestOrderTest.php
- [x] T046 [US2] Test order number after 9999 generates LAB-YYYY-10000 (5 digits) in TestOrderTest.php
- [x] T047 [US2] Test manual order_number bypasses auto-generation in TestOrderTest.php
- [x] T048 [US2] Test soft-deleted orders included in sequence calculation in TestOrderTest.php

### Status Transition Tests for User Story 2

- [x] T049 [P] [US2] Test canTransitionTo() method exists and returns boolean in TestOrderTest.php
- [x] T050 [US2] Test pending can transition to confirmed in TestOrderTest.php
- [x] T051 [US2] Test pending can transition to cancelled in TestOrderTest.php
- [x] T052 [US2] Test pending cannot transition to completed (skip intermediate) in TestOrderTest.php
- [x] T053 [US2] Test confirmed can transition to sample_collected in TestOrderTest.php
- [x] T054 [US2] Test confirmed can transition to cancelled in TestOrderTest.php
- [x] T055 [US2] Test sample_collected can transition to processing in TestOrderTest.php
- [x] T056 [US2] Test sample_collected can transition to cancelled in TestOrderTest.php
- [x] T057 [US2] Test processing can transition to completed in TestOrderTest.php
- [x] T058 [US2] Test processing cannot transition to cancelled in TestOrderTest.php
- [x] T059 [US2] Test completed can transition to delivered in TestOrderTest.php
- [x] T060 [US2] Test completed cannot transition to cancelled in TestOrderTest.php
- [x] T061 [US2] Test delivered status is terminal (cannot transition to any status) in TestOrderTest.php
- [x] T062 [US2] Test cancelled status is terminal (cannot transition to any status) in TestOrderTest.php

**Checkpoint**: Status workflow validated - order number generation works, all valid transitions succeed, all invalid transitions rejected

---

## Phase 5: User Story 3 - Scope and Query Builder Validation (Priority: P2)

**Goal**: Verify that query scopes correctly filter data to prevent incorrect data retrieval in application features

**Independent Test**: Seed test data with various states, apply scopes, verify correct subset of records returned. Run: `php artisan test --filter=test.*scope`

### LabTestCategory Scope Tests for User Story 3

- [ ] T063 [P] [US3] Test active() scope filters categories with status=1 in LabTestCategoryTest.php
- [ ] T064 [P] [US3] Test ordered() scope sorts by display_order ascending in LabTestCategoryTest.php

### LabTest Scope Tests for User Story 3

- [ ] T065 [P] [US3] Test active() scope filters tests with status=1 in LabTestTest.php
- [ ] T066 [P] [US3] Test laboratory() scope filters department='laboratory' in LabTestTest.php
- [ ] T067 [P] [US3] Test radiology() scope filters department='radiology' in LabTestTest.php

### TestOrder Scope Tests for User Story 3

- [ ] T068 [P] [US3] Test byPatient() scope filters by patient_id in TestOrderTest.php
- [ ] T069 [P] [US3] Test byTechnician() scope filters by lab_technician_id in TestOrderTest.php
- [ ] T070 [P] [US3] Test byDoctor() scope filters by doctor_id in TestOrderTest.php
- [ ] T071 [P] [US3] Test byStatus() scope filters by status in TestOrderTest.php
- [ ] T072 [P] [US3] Test pending() scope filters status='pending' in TestOrderTest.php

### TestOrderItem Scope Tests for User Story 3

- [ ] T073 [P] [US3] Test pending() scope filters status='pending' in TestOrderItemTest.php
- [ ] T074 [P] [US3] Test completed() scope filters status='completed' in TestOrderItemTest.php
- [ ] T075 [P] [US3] Test withResults() scope filters non-null result_value in TestOrderItemTest.php

**Checkpoint**: All query scopes validated - correct data filtering confirmed

---

## Phase 6: User Story 4 - Accessor and Mutator Validation (Priority: P3)

**Goal**: Verify formatted output for display attributes render correctly to ensure consistent UI presentation

**Independent Test**: Create model instances, verify accessor methods return properly formatted strings/HTML. Run: `php artisan test --filter=test.*accessor`

### LabTest Accessor Tests for User Story 4

- [x] T076 [P] [US4] Test formatted_price accessor formats to 2 decimal places in LabTestTest.php
- [x] T077 [P] [US4] Test department_badge accessor returns HTML badge in LabTestTest.php
- [x] T078 [P] [US4] Test sample_type_label accessor returns translated label in LabTestTest.php

### TestOrder Accessor Tests for User Story 4

- [x] T079 [P] [US4] Test status_badge accessor returns HTML badge with correct styling in TestOrderTest.php
- [x] T080 [P] [US4] Test payment_badge accessor returns HTML badge for payment status in TestOrderTest.php
- [x] T081 [P] [US4] Test priority_badge accessor returns HTML badge for priority level in TestOrderTest.php

### TestOrderItem Accessor Tests for User Story 4

- [x] T082 [P] [US4] Test result_status_badge accessor returns HTML badge in TestOrderItemTest.php
- [x] T083 [P] [US4] Test has_result accessor returns boolean based on result_value in TestOrderItemTest.php

### TestReport Accessor Tests for User Story 4

- [x] T084 [P] [US4] Test file_url accessor returns media URL or null in TestReportTest.php
- [x] T085 [P] [US4] Test status_badge accessor returns HTML badge in TestReportTest.php

**Checkpoint**: All accessors validated - formatted output correct for UI display

---

## Phase 7: User Story 5 - Edge Case and Boundary Validation (Priority: P3)

**Goal**: Verify system handles edge cases gracefully including null values, missing relationships, invalid data types, and boundary conditions

**Independent Test**: Create models with problematic data (nulls, invalid relationships, boundary values), verify error handling or safe defaults. Run: `php artisan test --filter=test.*edge`

### LabTest Edge Case Tests for User Story 5

- [ ] T086 [P] [US5] Test null sample_type returns safe default '-' in sample_type_label in LabTestTest.php
- [ ] T087 [P] [US5] Test price formatting with various decimal places in LabTestTest.php
- [ ] T088 [P] [US5] Test invalid department value in department_badge accessor in LabTestTest.php

### TestOrder Edge Case Tests for User Story 5

- [ ] T089 [P] [US5] Test deleted patient relationship returns null gracefully in TestOrderTest.php
- [ ] T090 [P] [US5] Test deleted doctor relationship returns null gracefully in TestOrderTest.php
- [ ] T091 [P] [US5] Test order number generation with year rollover in TestOrderTest.php
- [ ] T092 [P] [US5] Test canTransitionTo() with null status in TestOrderTest.php
- [ ] T093 [P] [US5] Test badge accessors with invalid status values in TestOrderTest.php

### TestOrderItem Edge Case Tests for User Story 5

- [ ] T094 [P] [US5] Test null result_date handled gracefully in TestOrderItemTest.php
- [ ] T095 [P] [US5] Test result_status_badge with null result_status in TestOrderItemTest.php
- [ ] T096 [P] [US5] Test has_result with empty string result_value in TestOrderItemTest.php
- [ ] T097 [P] [US5] Test deleted lab test relationship returns null in TestOrderItemTest.php

### TestReport Edge Case Tests for User Story 5

- [ ] T098 [P] [US5] Test file_url accessor with no media attached in TestReportTest.php
- [ ] T099 [P] [US5] Test null generated_by relationship handled gracefully in TestReportTest.php

### LabTestCategory Edge Case Tests for User Story 5

- [ ] T100 [P] [US5] Test category with no associated lab tests in LabTestCategoryTest.php
- [ ] T101 [P] [US5] Test ordered() scope with duplicate display_order values in LabTestCategoryTest.php

**Checkpoint**: All edge cases validated - system handles problematic data gracefully

---

## Phase 8: Polish & Verification

**Purpose**: Ensure coverage goals met and documentation updated

- [ ] T102 Run test coverage report with php artisan test --coverage-html=coverage
- [ ] T103 Verify 90%+ code coverage for all 5 model classes with php artisan test --coverage --min=90
- [ ] T104 [P] Run full test suite and verify all tests pass with php artisan test
- [ ] T105 [P] Update quickstart.md with any lessons learned during implementation
- [ ] T106 [P] Document any edge cases discovered during testing in data-model.md
- [ ] T107 Run Laravel Pint to ensure code style compliance with ./vendor/bin/pint

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-7)**: All depend on Foundational phase completion
  - User Story 1 (P1) - Core model tests
  - User Story 2 (P1) - Status workflow tests (depends on US1 for TestOrderTest existing)
  - User Story 3 (P2) - Scope tests (can run parallel with US2, depends on US1 for test files)
  - User Story 4 (P3) - Accessor tests (can run parallel with US3, depends on US1 for test files)
  - User Story 5 (P3) - Edge case tests (depends on US1 for test files)
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - Creates test class files
- **User Story 2 (P1)**: Depends on US1 (needs TestOrderTest.php to exist) - Adds workflow tests
- **User Story 3 (P2)**: Depends on US1 (needs test files to exist) - Adds scope tests
- **User Story 4 (P3)**: Depends on US1 (needs test files to exist) - Adds accessor tests
- **User Story 5 (P3)**: Depends on US1 (needs test files to exist) - Adds edge case tests

### Within Each User Story

- Create test class file first (US1)
- Add test methods in logical groups
- Tests within same file but different methods can be written in parallel
- Run tests frequently: `php artisan test --filter=ClassName`

### Parallel Opportunities

- **Phase 1 (Setup)**: All 3 tasks can run in parallel
- **Phase 2 (Factories)**:
  - T004-T008 (factory creation) can all run in parallel
  - T009-T013 (HasFactory trait) can all run in parallel after factories exist
- **Phase 3 (User Story 1)**:
  - Creating test classes (T014, T019, T025, T031, T036) can all run in parallel
  - Tests within each model class can be written in parallel
- **Phase 4-7**: All test methods marked [P] within each phase can run in parallel
- **Phase 8**: T102-T107 can mostly run in parallel

---

## Parallel Example: User Story 1 (Model Behavior)

```bash
# Launch all test class creation in parallel:
Task: "Create LabTestCategoryTest in Modules/RadiologyLab/Tests/Unit/LabTestCategoryTest.php"
Task: "Create LabTestTest in Modules/RadiologyLab/Tests/Unit/LabTestTest.php"
Task: "Create TestOrderTest in Modules/RadiologyLab/Tests/Unit/TestOrderTest.php"
Task: "Create TestOrderItemTest in Modules/RadiologyLab/Tests/Unit/TestOrderItemTest.php"
Task: "Create TestReportTest in Modules/RadiologyLab/Tests/Unit/TestReportTest.php"
```

## Parallel Example: User Story 2 (Status Workflow)

```bash
# Launch all status transition tests in parallel:
Task: "Test pending can transition to confirmed in TestOrderTest.php"
Task: "Test pending can transition to cancelled in TestOrderTest.php"
Task: "Test pending cannot transition to completed (skip intermediate) in TestOrderTest.php"
# ... all other transition tests can run in parallel
```

---

## Implementation Strategy

### MVP First (User Story 1 + 2 Only)

1. Complete Phase 1: Setup (3 tasks)
2. Complete Phase 2: Foundational - Factories (10 tasks)
3. Complete Phase 3: User Story 1 - Core Model Tests (28 tasks)
4. Complete Phase 4: User Story 2 - Status Workflow (21 tasks)
5. **STOP and VALIDATE**: Run `php artisan test` and verify all tests pass
6. Check coverage: `php artisan test --coverage --min=70`
7. **MVP DELIVERED**: Core model behavior and critical workflow validated

### Incremental Delivery

1. **Foundation** (Phase 1-2): Setup + Factories → Test infrastructure ready
2. **MVP** (Phase 3-4): Core models + Status workflow → Critical functionality validated
3. **Increment 1** (Phase 5): Add Scope tests → Query filtering validated
4. **Increment 2** (Phase 6): Add Accessor tests → UI presentation validated
5. **Increment 3** (Phase 7): Add Edge case tests → Robustness validated
6. **Complete** (Phase 8): Verify coverage and polish → 90%+ coverage achieved

### Parallel Team Strategy

With multiple developers:

1. **Together**: Complete Setup + Foundational (Phase 1-2)
2. **Once Foundational is done, split work**:
   - **Developer A**: User Story 1 (LabTestCategory, LabTest)
   - **Developer B**: User Story 1 (TestOrder, TestOrderItem, TestReport)
   - After US1 complete, work on US2-5 in parallel
3. Each developer can work on different test classes simultaneously
4. Merge frequently to avoid conflicts

---

## Coverage Goals

**Target**: 90%+ code coverage for all 5 model classes

### What Must Be Covered

- ✅ All custom scopes (active, laboratory, radiology, pending, etc.)
- ✅ All accessor methods (formatted_price, badges, labels)
- ✅ Business logic methods (canTransitionTo, boot method)
- ✅ Relationship definitions
- ✅ Attribute casting

### What Doesn't Need Coverage

- ❌ Framework-provided CRUD methods
- ❌ Simple getters/setters
- ❌ Boilerplate configuration

### Verification Commands

```bash
# Generate HTML coverage report
php artisan test --coverage-html=coverage

# Open coverage/index.html in browser

# Check minimum coverage threshold
php artisan test --coverage --min=90

# Run specific test class
php artisan test --filter=LabTestTest

# Run tests matching pattern
php artisan test --filter=test.*transition
```

---

## Test Execution Commands

```bash
# Run all tests
php artisan test

# Run only RadiologyLab unit tests
php artisan test Modules/RadiologyLab/Tests/Unit

# Run single test class
php artisan test --filter=TestOrderTest

# Run tests with profiling (find slow tests)
php artisan test --profile

# Run tests and show coverage
php artisan test --coverage

# Run specific test method
php artisan test --filter=test_auto_generates_order_number
```

---

## Notes

- [P] tasks = different files or independent methods, no dependencies
- [Story] label maps task to specific user story for traceability
- User Story 1 must complete first (creates test class files)
- User Stories 2-5 add tests to existing class files
- Verify tests fail before implementing (TDD not required for this feature but good practice)
- Commit after completing each user story phase
- Stop at any checkpoint to validate independently
- Use factories extensively - they're created in Phase 2 specifically for this purpose
- Follow AAA pattern: Arrange (setup), Act (execute), Assert (verify)
- Keep tests focused: one behavior per test method
- Use descriptive test names: `test_it_auto_generates_order_number_on_creation`

---

## Task Count Summary

- **Phase 1 (Setup)**: 3 tasks
- **Phase 2 (Foundational)**: 10 tasks
- **Phase 3 (User Story 1 - Core Models)**: 28 tasks
- **Phase 4 (User Story 2 - Status Workflow)**: 21 tasks
- **Phase 5 (User Story 3 - Scopes)**: 13 tasks
- **Phase 6 (User Story 4 - Accessors)**: 10 tasks
- **Phase 7 (User Story 5 - Edge Cases)**: 16 tasks
- **Phase 8 (Polish)**: 6 tasks

**Total**: 107 tasks

**Parallelizable Tasks**: 79 tasks marked [P] (74%)

**MVP Scope** (Phases 1-4): 62 tasks → Core model behavior + critical workflow validated
