# Implementation Plan: RequestNurse Module Unit Tests

**Branch**: `001-requestnurse-unit-tests` | **Date**: 2026-03-09 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/main/spec.md`

## Summary

Create a comprehensive unit test suite for the RequestNurse module covering models (Nurse, NurseRequest), form requests (NurseFormRequest, NurseRequestFormRequest), API resources (NurseResource, NurseRequestResource), controller business logic (status transitions, total amount calculation), and the permission seeder. Tests will run on SQLite in-memory using PHPUnit with Laravel's testing framework, following established project patterns.

## Technical Context

**Language/Version**: PHP 8.2+ / Laravel 11.x
**Primary Dependencies**: PHPUnit ^10.1, Mockery ^1.4.4, fakerphp/faker ^1.9.1, spatie/laravel-permission, nwidart/laravel-modules v10
**Storage**: SQLite in-memory (`:memory:` via phpunit.xml)
**Testing**: PHPUnit with `Illuminate\Foundation\Testing\TestCase`, `RefreshDatabase` trait
**Target Platform**: Windows development, Linux CI
**Project Type**: Laravel modular web application (SaaS)
**Performance Goals**: Full test suite under 30 seconds
**Constraints**: Tests must be isolated (no shared state), must use module test directory `Modules/RequestNurse/Tests/Unit/`
**Scale/Scope**: ~6 test classes, ~60-80 test methods

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Module Encapsulation | PASS | Tests live inside `Modules/RequestNurse/Tests/Unit/`. Factories reside in the module's `database/factories/` directory. No cross-module coupling. |
| II. Convention Adherence | PASS | Tests follow existing project patterns (PHPUnit, `RefreshDatabase`, `Tests\TestCase` base class, factory-based test data). Module test directories match `phpunit.xml` testsuite configuration. |
| III. Role-Based Access Control | PASS | Seeder tests verify correct permission assignments. No authorization bypass in tests. |
| IV. Test Discipline | PASS | This feature *is* test creation. Tests use SQLite in-memory, reside in `Modules/*/Tests/Unit`, cover happy path and edge cases. |
| V. Simplicity and YAGNI | PASS | No new dependencies required. No abstractions beyond standard factory + test class patterns. Tests cover only what exists in the module. |

All gates pass. No violations to justify.

## Project Structure

### Documentation (this feature)

```text
specs/main/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
└── tasks.md             # Phase 2 output (via /speckit.tasks)
```

### Source Code (repository root)

```text
Modules/RequestNurse/
├── database/
│   └── factories/
│       ├── NurseFactory.php              # NEW - factory for Nurse model
│       └── NurseRequestFactory.php       # NEW - factory for NurseRequest model
├── Tests/
│   └── Unit/
│       ├── NurseModelTest.php            # NEW - Nurse model tests
│       ├── NurseRequestModelTest.php     # NEW - NurseRequest model tests
│       ├── NurseFormRequestTest.php      # NEW - NurseFormRequest validation tests
│       ├── NurseRequestFormRequestTest.php # NEW - NurseRequestFormRequest validation tests
│       ├── NurseResourceTest.php         # NEW - NurseResource transformer tests
│       ├── NurseRequestResourceTest.php  # NEW - NurseRequestResource transformer tests
│       ├── StatusTransitionTest.php      # NEW - Status workflow & amount calc tests
│       └── PermissionSeederTest.php      # NEW - Permission seeder tests
├── Models/
│   ├── Nurse.php                         # EXISTING - under test
│   └── NurseRequest.php                  # EXISTING - under test
├── Http/
│   ├── Requests/
│   │   ├── NurseFormRequest.php          # EXISTING - under test
│   │   └── NurseRequestFormRequest.php   # EXISTING - under test
│   └── Controllers/Backend/
│       └── NurseRequestsController.php   # EXISTING - business logic under test
├── Transformers/
│   ├── NurseResource.php                 # EXISTING - under test
│   └── NurseRequestResource.php         # EXISTING - under test
└── database/seeders/
    └── RequestNursePermissionSeeder.php  # EXISTING - under test
```

**Structure Decision**: All new files are within the RequestNurse module boundary, following Module Encapsulation (Constitution I). Test classes in `Tests/Unit/`, factories in `database/factories/`.

## Complexity Tracking

No violations to justify. All patterns used are standard Laravel testing conventions already present in the codebase.
