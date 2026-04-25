# Quickstart: RequestNurse Module Unit Tests

**Date**: 2026-03-09
**Branch**: `001-requestnurse-unit-tests`

## Prerequisites

- PHP 8.2+
- Composer dependencies installed (`composer install`)
- No database server required (tests use SQLite in-memory)

## Running Tests

### All RequestNurse tests

```bash
php artisan test --filter=RequestNurse
```

### By test class

```bash
# Model tests
php artisan test --filter=NurseModelTest
php artisan test --filter=NurseRequestModelTest

# Form request validation tests
php artisan test --filter=NurseFormRequestTest
php artisan test --filter=NurseRequestFormRequestTest

# API resource tests
php artisan test --filter=NurseResourceTest
php artisan test --filter=NurseRequestResourceTest

# Business logic tests
php artisan test --filter=StatusTransitionTest

# Permission seeder tests
php artisan test --filter=PermissionSeederTest
```

### Unit suite only (all modules)

```bash
php artisan test --testsuite=Unit
```

## File Locations

| Type | Path |
|------|------|
| Test classes | `Modules/RequestNurse/Tests/Unit/*.php` |
| Model factories | `Modules/RequestNurse/database/factories/*.php` |
| Models under test | `Modules/RequestNurse/Models/*.php` |
| Form requests under test | `Modules/RequestNurse/Http/Requests/*.php` |
| Resources under test | `Modules/RequestNurse/Transformers/*.php` |
| Controller under test | `Modules/RequestNurse/Http/Controllers/Backend/NurseRequestsController.php` |
| Seeder under test | `Modules/RequestNurse/database/seeders/RequestNursePermissionSeeder.php` |

## Test Configuration

Tests use the configuration from `phpunit.xml` at the project root:

- **Database**: SQLite `:memory:` (no setup needed)
- **Cache**: Array driver
- **Mail**: Array driver
- **Queue**: Sync driver
- **Session**: Array driver

## Creating New Tests

New test classes should:

1. Extend `Tests\TestCase` (for tests needing Laravel app) or `PHPUnit\Framework\TestCase` (for pure unit tests)
2. Use `Illuminate\Foundation\Testing\RefreshDatabase` trait when database access is needed
3. Be placed in `Modules/RequestNurse/Tests/Unit/`
4. Follow naming convention: `{Subject}Test.php`
5. Use factories (`Nurse::factory()`, `NurseRequest::factory()`) for test data
