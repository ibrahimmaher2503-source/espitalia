# Implementation Plan: Comprehensive Unit Test Suite for Radiology Lab Module

**Branch**: `002-labs-unit-tests` | **Date**: 2026-03-11 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/002-labs-unit-tests/spec.md`

**Note**: This plan outlines the implementation of comprehensive unit tests for the RadiologyLab module models.

## Summary

Create a complete PHPUnit unit test suite for the RadiologyLab module covering all five model classes (LabTest, TestOrder, TestOrderItem, TestReport, LabTestCategory). The test suite will validate model behavior, relationships, scopes, accessors, status workflows, and edge cases in isolation using in-memory SQLite database to ensure fast, reliable test execution. The tests will serve as living documentation and regression protection for the lab management system's core data layer.

## Technical Context

**Language/Version**: PHP 8.2+ (Laravel 11 requirement)
**Primary Dependencies**: PHPUnit 10.x, Laravel Testing utilities, Mockery, Orchestra Testbench
**Storage**: SQLite in-memory database for test isolation
**Testing**: PHPUnit with Laravel's TestCase base classes
**Target Platform**: Laravel 11 web application (Espitalia SaaS)
**Project Type**: Web service - modular Laravel application with 33+ feature modules
**Performance Goals**: All unit tests execute in < 5 seconds total (fast developer feedback)
**Constraints**: Tests must be isolated (no shared state), no external dependencies, 90%+ code coverage
**Scale/Scope**: 5 model classes, approximately 15-20 test classes, 100+ individual test cases

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Principle I: Module Encapsulation ✅
- **Status**: PASS
- **Compliance**: All tests will reside in `Modules/RadiologyLab/Tests/Unit/`, maintaining module self-containment
- **Analysis**: Tests validate models within the module boundary without creating cross-module dependencies

### Principle II: Convention Adherence ✅
- **Status**: PASS
- **Compliance**: Tests follow Laravel conventions:
  - Extend `Tests\TestCase` base class
  - Use PHPUnit assertions and Laravel testing helpers
  - Follow naming convention: `{ModelName}Test.php`
  - Use factories for test data generation
- **Analysis**: Follows established Laravel testing patterns used throughout the codebase

### Principle III: Role-Based Access Control ⚪
- **Status**: NOT APPLICABLE
- **Analysis**: Unit tests focus on model behavior (data layer), not authorization logic. Access control is tested separately in feature tests.

### Principle IV: Test Discipline ✅
- **Status**: PASS (This feature IS the test discipline)
- **Compliance**:
  - Tests use SQLite in-memory database as per project standard
  - Will be placed in `Modules/RadiologyLab/Tests/Unit/`
  - All tests will pass on default branch before merge
  - Covers both happy paths and critical edge cases
- **Analysis**: This feature directly implements Principle IV for the RadiologyLab module

### Principle V: Simplicity and YAGNI ✅
- **Status**: PASS
- **Compliance**:
  - Tests validate existing model behavior only (no speculative testing)
  - Uses existing Laravel testing infrastructure
  - No new dependencies required
  - Inline test setup where appropriate, shared factories only when genuinely reused
- **Analysis**: Minimal viable test coverage for existing functionality

### Summary
**Gate Result**: ✅ PASS - No constitutional violations. Feature fully compliant with all applicable principles.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
Modules/RadiologyLab/
├── Models/                          # (existing - what we're testing)
│   ├── LabTest.php
│   ├── LabTestCategory.php
│   ├── TestOrder.php
│   ├── TestOrderItem.php
│   └── TestReport.php
├── database/
│   └── factories/                   # (to be created for test data)
│       ├── LabTestFactory.php
│       ├── LabTestCategoryFactory.php
│       ├── TestOrderFactory.php
│       ├── TestOrderItemFactory.php
│       └── TestReportFactory.php
└── Tests/                           # (to be created)
    └── Unit/                        # All unit tests reside here
        ├── LabTestTest.php
        ├── LabTestCategoryTest.php
        ├── TestOrderTest.php
        ├── TestOrderItemTest.php
        └── TestReportTest.php

tests/                               # (existing - core test configuration)
├── TestCase.php                     # Base test case (already exists)
└── CreatesApplication.php           # Bootstrap helper (already exists)

phpunit.xml                          # (existing - project test configuration)
```

**Structure Decision**: Following Laravel modular architecture, all tests reside within the RadiologyLab module under `Tests/Unit/`. This maintains module encapsulation (Principle I) and allows tests to be run module-specifically or as part of the full suite. Factories will be created in `database/factories/` within the module to generate test data efficiently.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

**Status**: N/A - No constitutional violations detected.

---

## Phase 0: Research ✅ COMPLETE

**Output**: [research.md](./research.md)

**Key Decisions**:
1. Use Laravel's `RefreshDatabase` trait for test isolation
2. Create model factories for all 5 models with state methods
3. Test business logic only (not framework code)
4. Use existing PHPUnit configuration (no changes needed)
5. Target 90%+ code coverage using PHPUnit's built-in coverage
6. Focus tests on: attribute casting, relationships, scopes, accessors, status workflows, order number generation

---

## Phase 1: Design & Contracts ✅ COMPLETE

**Outputs**:
- [data-model.md](./data-model.md) - Complete model specifications with relationships, business rules, and test requirements
- [quickstart.md](./quickstart.md) - Developer guide for running and writing tests
- Agent context updated (CLAUDE.md)

**Key Artifacts**:
1. **Model Inventory**: 5 models documented (LabTestCategory, LabTest, TestOrder, TestOrderItem, TestReport)
2. **Relationships Mapped**: All belongsTo, hasMany relationships documented
3. **Business Rules Identified**:
   - TestOrder auto-generates sequential order numbers (LAB-YYYY-XXXX)
   - Status workflow with 7 states and strict transition rules
   - Terminal states (delivered, cancelled) cannot transition
4. **Test Data Strategy**: Factory pattern with state methods for common variations
5. **Coverage Strategy**: 90%+ target focusing on custom business logic

**No Contracts Created**: This is an internal testing feature with no external interfaces.

---

## Post-Phase 1 Constitution Check ✅ PASS

Re-evaluated after design phase:

### Principle I: Module Encapsulation ✅
- **Status**: PASS
- **Design Validation**: All test files, factories, and test utilities remain within `Modules/RadiologyLab/`. No cross-module test dependencies introduced.

### Principle II: Convention Adherence ✅
- **Status**: PASS
- **Design Validation**:
  - Test structure follows Laravel conventions: `Tests/Unit/{ModelName}Test.php`
  - Factories follow Laravel 11 factory conventions
  - Uses standard PHPUnit assertions and Laravel testing helpers
  - Naming conventions: descriptive test method names with `test_` prefix

### Principle IV: Test Discipline ✅
- **Status**: PASS (Enhanced)
- **Design Validation**:
  - Test suite design ensures SQLite in-memory isolation
  - Comprehensive coverage plan: 5 models → 15-20 test classes → 100+ test cases
  - Both happy paths and critical edge cases covered in design
  - Factories provide consistent test data generation

### Principle V: Simplicity and YAGNI ✅
- **Status**: PASS
- **Design Validation**:
  - Tests only existing model behavior (no speculative tests)
  - No new dependencies required
  - Factory state methods only for genuinely reused variations
  - No over-abstraction in test helpers

**Gate Result**: ✅ PASS - Design maintains full constitutional compliance.

---

## Next Steps

**Command**: `/speckit.tasks` - Generate tasks.md

**What It Will Create**:
- Detailed task breakdown for implementation
- Dependency-ordered tasks (factories first, then tests)
- Assigned to implementation phases
- Ready for execution via `/speckit.implement`

**Implementation Phases (Preview)**:
1. **Phase 0 (Setup)**: Create test directory structure, configure base test class
2. **Phase 1 (Factories)**: Create all 5 model factories with state methods
3. **Phase 2 (Simple Models)**: Test LabTestCategory, LabTest, TestReport
4. **Phase 3 (Complex Models)**: Test TestOrder (order number generation, status workflow)
5. **Phase 4 (Related Models)**: Test TestOrderItem
6. **Phase 5 (Coverage)**: Verify 90%+ coverage, add missing edge case tests
7. **Phase 6 (Documentation)**: Update quickstart.md with lessons learned

---

## Summary

This plan establishes a comprehensive testing strategy for the RadiologyLab module's data layer. The approach prioritizes:

1. **Test Isolation**: RefreshDatabase + SQLite in-memory for fast, reliable tests
2. **Maintainability**: Factory pattern for reusable test data
3. **Focus**: Test business logic and custom behavior, not framework code
4. **Quality**: 90%+ coverage target with emphasis on critical workflows
5. **Constitutional Compliance**: Full adherence to all applicable project principles

**Critical Test Areas Identified**:
- TestOrder auto-number generation (sequential, year rollover, >9999 handling)
- Status workflow transitions (21 valid paths, all invalid paths rejected)
- Relationship integrity (5 models, 10+ relationships)
- Accessor edge cases (null handling, missing translations)

**Estimated Implementation Effort**: 15-20 test classes, 100+ test methods, targeting < 5 second execution time.

**Branch**: `002-labs-unit-tests`
**Ready for**: Task generation (`/speckit.tasks`)
