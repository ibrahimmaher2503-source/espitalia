# Research: RadiologyLab Module Unit Testing

**Feature**: 002-labs-unit-tests
**Phase**: 0 (Research & Discovery)
**Date**: 2026-03-11

## Overview

This document captures research findings and technical decisions for implementing comprehensive unit tests for the RadiologyLab module models.

## 1. Laravel Model Testing Best Practices

### Decision: Use RefreshDatabase Trait
**Rationale**: Laravel's `RefreshDatabase` trait provides automatic database migration and rollback for each test, ensuring test isolation.

**Implementation**:
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class LabTestTest extends TestCase
{
    use RefreshDatabase;

    // Tests automatically get fresh database state
}
```

**Alternatives Considered**:
- `DatabaseTransactions` - Rejected because it doesn't work well with SQLite in-memory databases
- Manual database cleanup - Rejected as error-prone and verbose

### Decision: Use Model Factories for Test Data
**Rationale**: Factories provide consistent, reusable test data generation following the Factory pattern.

**Implementation**:
```php
// database/factories/LabTestFactory.php
namespace Modules\RadiologyLab\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\RadiologyLab\Models\LabTest;

class LabTestFactory extends Factory
{
    protected $model = LabTest::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'code' => strtoupper($this->faker->unique()->bothify('LAB-###')),
            'sample_type' => $this->faker->randomElement(LabTest::SAMPLE_TYPES),
            'department' => $this->faker->randomElement(LabTest::DEPARTMENTS),
            'default_price' => $this->faker->randomFloat(2, 10, 500),
            'status' => 1,
        ];
    }
}
```

**Benefits**:
- Reduces test boilerplate
- Ensures valid test data
- Easy to create variations with state methods
- Supports relationship creation

### Decision: Test Model Behavior, Not Framework Code
**Rationale**: Focus tests on custom business logic, scopes, accessors, and relationships - not Laravel's built-in functionality.

**What to Test**:
- ✅ Custom scopes (e.g., `active()`, `laboratory()`)
- ✅ Accessor/mutator methods (e.g., `getFormattedPriceAttribute()`)
- ✅ Business logic methods (e.g., `canTransitionTo()`)
- ✅ Custom validation rules
- ✅ Relationship definitions
- ✅ Event listeners (e.g., order number generation in `boot()`)

**What NOT to Test**:
- ❌ Laravel's built-in CRUD operations
- ❌ Eloquent's relationship mechanics (already tested by Laravel)
- ❌ Framework-provided casting mechanisms

## 2. PHPUnit Configuration

### Current Setup Analysis

**Existing Configuration** (`phpunit.xml`):
- Already configured for Laravel testing
- Uses SQLite in-memory database (`:memory:`)
- Includes environment variables for testing

**No Changes Required**: The existing PHPUnit configuration is suitable for our unit tests.

### Decision: Use Laravel's TestCase Base Class
**Rationale**: Provides application bootstrapping, database setup, and Laravel testing helpers.

**Implementation**:
```php
namespace Modules\RadiologyLab\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LabTestTest extends TestCase
{
    use RefreshDatabase;

    // Tests here have access to:
    // - $this->app (Laravel application)
    // - Factory helpers
    // - Database assertions
}
```

## 3. Testing Strategy for Each Model

### LabTest Model Testing Strategy

**Key Areas**:
1. **Attribute Casting**: Verify `status` (boolean) and `default_price` (float) cast correctly
2. **Relationships**: Test `category()` belongsTo and `orderItems()` hasMany
3. **Scopes**: Test `active()`, `laboratory()`, `radiology()`
4. **Accessors**: Test `formatted_price`, `department_badge`, `sample_type_label`
5. **Slug Generation**: Test HasSlug trait integration
6. **Soft Deletes**: Verify soft delete behavior

**Edge Cases**:
- Null `sample_type` → `sample_type_label` should return safe default
- Price formatting with various decimal places
- Invalid department values

### TestOrder Model Testing Strategy

**Key Areas**:
1. **Order Number Auto-Generation**: Critical business logic in `boot()` method
   - Format: `LAB-YYYY-XXXX` (zero-padded 4 digits)
   - Sequential numbering per year
   - Handles year rollovers
   - Thread-safe incrementation
2. **Status Workflow**: Test `ALLOWED_TRANSITIONS` and `canTransitionTo()`
3. **Status Constants**: Verify all status constants are defined
4. **Relationships**: Test `patient()`, `doctor()`, `labTechnician()`, `items()`, `reports()`
5. **Scopes**: Test `byPatient()`, `byStatus()`, `pending()`
6. **Accessors**: Test badge generation for status, payment, priority

**Edge Cases**:
- First order of new year (should be LAB-YYYY-0001)
- Last order was LAB-2026-9999 → next should be LAB-2026-10000 (handle leading zeros)
- Concurrent order creation (race conditions in number generation)
- Invalid status transitions
- Deleted patient/doctor relationships

### TestOrderItem Model Testing Strategy

**Key Areas**:
1. **Relationships**: Test `testOrder()`, `labTest()`, `resultEnteredBy()`
2. **Scopes**: Test `pending()`, `completed()`, `withResults()`
3. **Accessors**: Test `result_status_badge`, `has_result`
4. **Result Data Handling**: Test result value, unit, reference range storage

**Edge Cases**:
- Null result values
- Result status without result value
- Deleted lab test reference

### TestReport Model Testing Strategy

**Key Areas**:
1. **Media Handling**: Test Spatie MediaLibrary integration (`registerMediaCollections()`)
2. **Accessors**: Test `file_url` and `status_badge`
3. **Relationships**: Test `testOrder()`, `generatedBy()`
4. **Constants**: Test report type and status constants

**Edge Cases**:
- Report without file attachment
- Null `generated_by` user

### LabTestCategory Model Testing Strategy

**Key Areas**:
1. **Relationships**: Test `labTests()` hasMany
2. **Scopes**: Test `active()`, `ordered()`
3. **Ordering**: Test `display_order` functionality
4. **Slug Generation**: Test HasSlug trait

**Edge Cases**:
- Categories with no associated tests
- Display order sorting with duplicate values

## 4. Test Data Management

### Decision: One Factory Per Model
**Rationale**: Each model gets its own factory for clean separation and easy maintenance.

**Factory Locations**: `Modules/RadiologyLab/database/factories/`

**Factory Features**:
- Use Faker for realistic test data
- Provide state methods for common variations:
  - `LabTestFactory::laboratory()`, `::radiology()`
  - `TestOrderFactory::pending()`, `::confirmed()`, `::completed()`
  - `TestOrderFactory::urgent()`, `::stat()`

**Example State Methods**:
```php
// In TestOrderFactory
public function pending()
{
    return $this->state(fn (array $attributes) => [
        'status' => TestOrder::STATUS_PENDING,
    ]);
}

public function urgent()
{
    return $this->state(fn (array $attributes) => [
        'priority' => TestOrder::PRIORITY_URGENT,
    ]);
}
```

## 5. Coverage Goals and Tools

### Decision: Target 90%+ Code Coverage
**Rationale**: Aligns with FR-013 requirement and industry best practices for critical business logic.

**Coverage Exclusions**:
- Boilerplate getters/setters
- Framework-provided methods
- Config files

### Decision: Use PHPUnit's Built-in Coverage
**Rationale**: No additional tools needed - PHPUnit includes Xdebug/PCOV coverage.

**Running Coverage**:
```bash
# Generate HTML coverage report
php artisan test --coverage-html=coverage

# Check coverage percentage
php artisan test --coverage --min=90
```

**CI Integration**: Coverage reports can be generated in CI pipeline and uploaded to services like Codecov if desired (future enhancement).

## 6. Test Organization

### Decision: One Test Class Per Model
**Rationale**: Clear organization, easy to locate tests, manageable file sizes.

**Test Class Structure**:
```php
class LabTestTest extends TestCase
{
    use RefreshDatabase;

    // Group 1: Basic Model Functionality
    public function test_it_can_create_lab_test() { }
    public function test_it_has_required_fillable_attributes() { }

    // Group 2: Attribute Casting
    public function test_status_is_cast_to_boolean() { }
    public function test_price_is_cast_to_float() { }

    // Group 3: Relationships
    public function test_it_belongs_to_category() { }
    public function test_it_has_many_order_items() { }

    // Group 4: Scopes
    public function test_active_scope_filters_active_tests() { }

    // Group 5: Accessors
    public function test_formatted_price_accessor() { }

    // Group 6: Edge Cases
    public function test_handles_null_sample_type_gracefully() { }
}
```

**Naming Convention**: `test_{behavior_being_tested}` - descriptive, readable test names.

## 7. Dependency Analysis

### Required Dependencies
All dependencies are already installed in the project:
- ✅ PHPUnit 10.x (via Laravel 11)
- ✅ Mockery (for mocking if needed)
- ✅ Laravel Testing utilities
- ✅ Orchestra Testbench (if testing service providers)

### No New Dependencies Required
**Decision**: Use existing Laravel testing infrastructure exclusively.

## 8. Database Testing Patterns

### Decision: Use In-Memory SQLite
**Rationale**:
- Fast (no disk I/O)
- Isolated (each test gets fresh database)
- Already configured in project

**Configuration** (already in `phpunit.xml`):
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### Decision: Run Migrations in Tests
**Rationale**: `RefreshDatabase` trait automatically runs migrations, ensuring schema is up-to-date.

### Testing Strategy for Complex Queries
For testing scopes and relationships:
1. Create test data using factories
2. Apply scope/relationship
3. Assert correct data is returned
4. Assert incorrect data is excluded

**Example**:
```php
public function test_active_scope_filters_correctly()
{
    // Arrange: Create active and inactive tests
    $activeTest = LabTest::factory()->create(['status' => 1]);
    $inactiveTest = LabTest::factory()->create(['status' => 0]);

    // Act: Apply active scope
    $results = LabTest::active()->get();

    // Assert: Only active test is returned
    $this->assertTrue($results->contains($activeTest));
    $this->assertFalse($results->contains($inactiveTest));
    $this->assertCount(1, $results);
}
```

## 9. Testing Status Transitions (Critical Logic)

### Strategy for Testing `canTransitionTo()`
**Importance**: Status workflow is critical business logic - incorrect transitions can corrupt data.

**Testing Approach**:
1. Test all valid transitions (from spec: `ALLOWED_TRANSITIONS`)
2. Test all invalid transitions (matrix of all combinations not in `ALLOWED_TRANSITIONS`)
3. Test edge cases (terminal states, null status)

**Implementation Pattern**:
```php
public function test_can_transition_from_pending_to_confirmed()
{
    $order = TestOrder::factory()->create(['status' => TestOrder::STATUS_PENDING]);
    $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_CONFIRMED));
}

public function test_cannot_transition_from_pending_to_completed()
{
    $order = TestOrder::factory()->create(['status' => TestOrder::STATUS_PENDING]);
    $this->assertFalse($order->canTransitionTo(TestOrder::STATUS_COMPLETED));
}

public function test_delivered_status_is_terminal()
{
    $order = TestOrder::factory()->create(['status' => TestOrder::STATUS_DELIVERED]);

    foreach (TestOrder::STATUSES as $status) {
        if ($status === TestOrder::STATUS_DELIVERED) continue;
        $this->assertFalse($order->canTransitionTo($status));
    }
}
```

## 10. Testing Order Number Generation (Critical Logic)

### Strategy for Testing Auto-Generation in `boot()`
**Importance**: Order numbers must be unique, sequential, and follow format `LAB-YYYY-XXXX`.

**Key Tests**:
1. First order generates LAB-2026-0001
2. Second order generates LAB-2026-0002
3. Order after LAB-2026-9999 generates LAB-2026-10000 (no leading zeros issue)
4. New year resets numbering (LAB-2027-0001)
5. Soft-deleted orders don't break sequence
6. Manual order_number is respected (doesn't auto-generate)

**Race Condition Handling**:
- Document that Laravel's database transactions provide basic protection
- For production, consider database-level constraints or locks if high concurrency expected
- Note in test comments that full race condition testing requires integration tests

**Implementation Pattern**:
```php
public function test_auto_generates_order_number_on_creation()
{
    $order = TestOrder::factory()->create(['order_number' => null]);

    $this->assertNotNull($order->order_number);
    $this->assertMatchesRegularExpression('/^LAB-\d{4}-\d{4}$/', $order->order_number);
}

public function test_sequential_order_numbering()
{
    $order1 = TestOrder::factory()->create();
    $order2 = TestOrder::factory()->create();

    // Extract sequence numbers
    $num1 = (int) substr($order1->order_number, -4);
    $num2 = (int) substr($order2->order_number, -4);

    $this->assertEquals($num1 + 1, $num2);
}

public function test_handles_large_order_numbers()
{
    // Create order with number 9999
    TestOrder::factory()->create([
        'order_number' => 'LAB-2026-9999'
    ]);

    // Next order should be 10000 (5 digits, not 4)
    $nextOrder = TestOrder::factory()->create();
    $this->assertEquals('LAB-2026-10000', $nextOrder->order_number);
}
```

## 11. Test Execution Performance

### Target: < 5 Seconds Total Execution
**Strategy**:
- Use in-memory SQLite (fast)
- Minimize database queries (use factories efficiently)
- Avoid external dependencies (no HTTP calls, no file I/O)
- Keep tests focused (unit tests, not integration tests)

**Measurement**:
```bash
# Run tests with timing
php artisan test --profile

# Check slow tests
php artisan test --profile | grep -A 5 "slowest"
```

### Optimization Tips
- Use `create()` only when database persistence is needed for the test
- Use `make()` for in-memory models when persistence isn't required
- Mock external services (e.g., Spatie Media Library if testing file uploads)

## Summary of Key Decisions

| Area | Decision | Rationale |
|------|----------|-----------|
| **Test Trait** | `RefreshDatabase` | Best for SQLite in-memory, ensures isolation |
| **Test Data** | Model Factories | Reusable, consistent, follows Laravel conventions |
| **Test Focus** | Business logic only | Don't test framework code |
| **Base Class** | `Tests\TestCase` | Laravel testing utilities and app bootstrapping |
| **Database** | SQLite in-memory | Fast, isolated, already configured |
| **Coverage Goal** | 90%+ | Per spec requirement FR-013 |
| **Organization** | One test class per model | Clear structure, easy navigation |
| **Dependencies** | Use existing only | No new packages needed |
| **Test Naming** | `test_{behavior}` | Descriptive, readable |
| **Critical Tests** | Status transitions, order numbers | These are core business logic |

## Next Steps

Phase 1 will create:
1. **data-model.md**: Document model structure, relationships, and validation rules
2. **quickstart.md**: Developer guide for running and writing tests
3. **Model Factories**: Create factory classes for test data generation
4. *No contracts needed*: Internal testing feature, no external interfaces

## References

- Laravel Testing Documentation: https://laravel.com/docs/11.x/testing
- PHPUnit Documentation: https://phpunit.de/documentation.html
- Laravel Factories: https://laravel.com/docs/11.x/eloquent-factories
- Project Constitution: `.specify/memory/constitution.md`
