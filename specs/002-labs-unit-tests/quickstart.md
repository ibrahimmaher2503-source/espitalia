# Quick Start: RadiologyLab Unit Testing

**Feature**: 002-labs-unit-tests
**Audience**: Developers writing or maintaining tests for the RadiologyLab module
**Last Updated**: 2026-03-11

## Overview

This guide helps you quickly run, write, and maintain unit tests for the RadiologyLab module models.

## Running Tests

### Run All Tests
```bash
# From project root
php artisan test
```

### Run Only RadiologyLab Unit Tests
```bash
# Module-specific tests
php artisan test --filter=RadiologyLab

# Or using path
php artisan test Modules/RadiologyLab/Tests/Unit
```

### Run Single Test Class
```bash
# Specific model tests
php artisan test --filter=LabTestTest
php artisan test --filter=TestOrderTest
```

### Run Single Test Method
```bash
# Specific test case
php artisan test --filter=test_it_auto_generates_order_number
```

### Run with Coverage
```bash
# Generate HTML coverage report
php artisan test --coverage-html=coverage

# Open in browser: coverage/index.html

# Check coverage meets minimum (90%)
php artisan test --coverage --min=90
```

### Run with Profiling (Find Slow Tests)
```bash
php artisan test --profile
```

## Writing Your First Test

### 1. Create Test Class
```bash
# Test classes go in: Modules/RadiologyLab/Tests/Unit/
# Naming: {ModelName}Test.php
```

**Example**: `Modules/RadiologyLab/Tests/Unit/LabTestTest.php`
```php
<?php

namespace Modules\RadiologyLab\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\RadiologyLab\Models\LabTest;

class LabTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_lab_test()
    {
        // Arrange
        $data = [
            'name' => 'Complete Blood Count',
            'code' => 'CBC',
            'sample_type' => 'blood',
            'department' => 'laboratory',
            'default_price' => 50.00,
            'status' => 1,
        ];

        // Act
        $labTest = LabTest::factory()->create($data);

        // Assert
        $this->assertInstanceOf(LabTest::class, $labTest);
        $this->assertEquals('CBC', $labTest->code);
        $this->assertDatabaseHas('lab_tests', ['code' => 'CBC']);
    }
}
```

### 2. Use Factories for Test Data

**Creating a Factory**: `Modules/RadiologyLab/database/factories/LabTestFactory.php`
```php
<?php

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

    // State method for laboratory tests
    public function laboratory()
    {
        return $this->state(fn (array $attributes) => [
            'department' => 'laboratory',
        ]);
    }

    // State method for radiology tests
    public function radiology()
    {
        return $this->state(fn (array $attributes) => [
            'department' => 'radiology',
        ]);
    }
}
```

**Register Factory in Model**: `Modules/RadiologyLab/Models/LabTest.php`
```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\RadiologyLab\Database\Factories\LabTestFactory;

class LabTest extends BaseModel
{
    use HasFactory;

    protected static function newFactory()
    {
        return LabTestFactory::new();
    }
}
```

**Using Factories in Tests**:
```php
// Create a lab test (persisted to database)
$labTest = LabTest::factory()->create();

// Create without persisting (in-memory only)
$labTest = LabTest::factory()->make();

// Create with specific attributes
$labTest = LabTest::factory()->create(['code' => 'CBC']);

// Create multiple
$labTests = LabTest::factory()->count(5)->create();

// Use state methods
$labTest = LabTest::factory()->laboratory()->create();

// Create with relationships
$labTest = LabTest::factory()
    ->for(LabTestCategory::factory())
    ->create();
```

## Test Patterns

### Pattern 1: Testing Attribute Casting
```php
public function test_status_is_cast_to_boolean()
{
    $labTest = LabTest::factory()->create(['status' => 1]);

    $this->assertIsBool($labTest->status);
    $this->assertTrue($labTest->status);
}
```

### Pattern 2: Testing Relationships
```php
public function test_it_belongs_to_category()
{
    $category = LabTestCategory::factory()->create();
    $labTest = LabTest::factory()->create(['category_id' => $category->id]);

    // Assert relationship loads correctly
    $this->assertInstanceOf(LabTestCategory::class, $labTest->category);
    $this->assertEquals($category->id, $labTest->category->id);
}
```

### Pattern 3: Testing Scopes
```php
public function test_active_scope_filters_active_tests()
{
    // Arrange: Create active and inactive tests
    $activeTest = LabTest::factory()->create(['status' => 1]);
    $inactiveTest = LabTest::factory()->create(['status' => 0]);

    // Act: Apply scope
    $results = LabTest::active()->get();

    // Assert: Correct filtering
    $this->assertTrue($results->contains($activeTest));
    $this->assertFalse($results->contains($inactiveTest));
    $this->assertCount(1, $results);
}
```

### Pattern 4: Testing Accessors
```php
public function test_formatted_price_accessor()
{
    $labTest = LabTest::factory()->create(['default_price' => 150.5]);

    $this->assertEquals('150.50', $labTest->formatted_price);
}
```

### Pattern 5: Testing Business Logic Methods
```php
public function test_can_transition_from_pending_to_confirmed()
{
    $order = TestOrder::factory()->create(['status' => TestOrder::STATUS_PENDING]);

    $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_CONFIRMED));
}

public function test_cannot_skip_intermediate_statuses()
{
    $order = TestOrder::factory()->create(['status' => TestOrder::STATUS_PENDING]);

    $this->assertFalse($order->canTransitionTo(TestOrder::STATUS_COMPLETED));
}
```

### Pattern 6: Testing Edge Cases
```php
public function test_handles_null_sample_type_gracefully()
{
    $labTest = LabTest::factory()->create(['sample_type' => null]);

    // Should return safe default, not throw error
    $this->assertEquals('-', $labTest->sample_type_label);
}
```

### Pattern 7: Testing Complex Auto-Generation Logic
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

    $num1 = (int) substr($order1->order_number, -4);
    $num2 = (int) substr($order2->order_number, -4);

    $this->assertEquals($num1 + 1, $num2);
}
```

## Best Practices

### DO ✅

- **Use `RefreshDatabase`** - Ensures test isolation
  ```php
  use Illuminate\Foundation\Testing\RefreshDatabase;

  class LabTestTest extends TestCase
  {
      use RefreshDatabase;
  }
  ```

- **Use Factories** - DRY, maintainable test data
  ```php
  $labTest = LabTest::factory()->create();
  ```

- **Use Descriptive Test Names** - What behavior is tested?
  ```php
  public function test_it_auto_generates_order_number_on_creation() { }
  ```

- **Follow AAA Pattern** - Arrange, Act, Assert
  ```php
  public function test_example()
  {
      // Arrange: Set up test data
      $labTest = LabTest::factory()->create();

      // Act: Execute the behavior
      $price = $labTest->formatted_price;

      // Assert: Verify the outcome
      $this->assertEquals('100.00', $price);
  }
  ```

- **Test Edge Cases** - Null values, boundary conditions, invalid states
  ```php
  public function test_handles_null_values_gracefully() { }
  ```

- **Use Specific Assertions**
  ```php
  $this->assertInstanceOf(LabTest::class, $model);
  $this->assertCount(3, $collection);
  $this->assertTrue($boolean);
  $this->assertNull($value);
  $this->assertMatchesRegularExpression('/pattern/', $string);
  ```

### DON'T ❌

- **Don't Test Framework Code** - Trust Laravel's tests
  ```php
  // ❌ Bad: Testing Eloquent's create method
  public function test_model_can_be_created() { }

  // ✅ Good: Testing custom business logic
  public function test_auto_generates_unique_code() { }
  ```

- **Don't Share State Between Tests** - Each test should be independent
  ```php
  // ❌ Bad: Using class properties
  private $labTest;

  // ✅ Good: Create fresh data in each test
  public function test_example()
  {
      $labTest = LabTest::factory()->create();
  }
  ```

- **Don't Use Real External Services** - Mock them
  ```php
  // ✅ Good: Mock external API
  Http::fake(['api.example.com/*' => Http::response(['status' => 'ok'])]);
  ```

- **Don't Write Long Tests** - One behavior per test
  ```php
  // ❌ Bad: Testing multiple behaviors
  public function test_lab_test_functionality() { }

  // ✅ Good: Separate tests
  public function test_it_has_active_scope() { }
  public function test_it_formats_price() { }
  ```

## Troubleshooting

### Tests Run Slowly
- **Problem**: Tests take > 5 seconds
- **Solution**:
  - Use `make()` instead of `create()` when persistence isn't needed
  - Check for N+1 queries with `$this->assertQueryCount()`
  - Profile with `php artisan test --profile`

### Database State Persists Between Tests
- **Problem**: Tests fail when run together, pass when run individually
- **Solution**: Ensure `RefreshDatabase` trait is used
  ```php
  use Illuminate\Foundation\Testing\RefreshDatabase;

  class LabTestTest extends TestCase
  {
      use RefreshDatabase; // ← Add this
  }
  ```

### Factory Not Found
- **Problem**: `Unable to locate factory for [Model]`
- **Solution**: Register factory in model
  ```php
  use Illuminate\Database\Eloquent\Factories\HasFactory;

  class LabTest extends BaseModel
  {
      use HasFactory;

      protected static function newFactory()
      {
          return \Modules\RadiologyLab\Database\Factories\LabTestFactory::new();
      }
  }
  ```

### Translation Keys Not Found in Tests
- **Problem**: `__('radiology_lab.status_pending')` returns key, not translation
- **Solution**: This is expected in tests. Test the logic, not the translation:
  ```php
  // ✅ Good: Test that badge is generated
  $this->assertStringContainsString('badge', $order->status_badge);

  // Or mock translations if needed
  Lang::shouldReceive('get')->andReturn('Pending');
  ```

## Assertions Quick Reference

### Common Assertions
```php
// Equality
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual); // Strict comparison (===)

// Type checks
$this->assertInstanceOf(LabTest::class, $model);
$this->assertIsInt($value);
$this->assertIsBool($value);
$this->assertIsString($value);
$this->assertIsArray($value);

// Truthiness
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertNull($value);
$this->assertNotNull($value);

// Collections
$this->assertCount(3, $collection);
$this->assertEmpty($collection);
$this->assertNotEmpty($collection);
$this->assertContains('item', $array);

// Database
$this->assertDatabaseHas('lab_tests', ['code' => 'CBC']);
$this->assertDatabaseMissing('lab_tests', ['code' => 'XYZ']);
$this->assertSoftDeleted($model);

// Strings
$this->assertStringContainsString('needle', $haystack);
$this->assertStringStartsWith('prefix', $string);
$this->assertMatchesRegularExpression('/pattern/', $string);

// Exceptions
$this->expectException(\Exception::class);
$this->expectExceptionMessage('Error message');
```

## File Structure Checklist

When adding tests for a new model, ensure:

- [ ] Model has `use HasFactory` trait
- [ ] Model has `newFactory()` method returning correct factory
- [ ] Factory class created in `Modules/RadiologyLab/database/factories/`
- [ ] Factory has `definition()` method with realistic fake data
- [ ] Factory has state methods for common variations
- [ ] Test class created in `Modules/RadiologyLab/Tests/Unit/`
- [ ] Test class uses `RefreshDatabase` trait
- [ ] Tests cover: casting, relationships, scopes, accessors, business logic
- [ ] Edge cases tested
- [ ] All tests pass: `php artisan test`
- [ ] Coverage meets 90%: `php artisan test --coverage --min=90`

## Coverage Goals

### Target: 90%+ Code Coverage

**Check Coverage**:
```bash
php artisan test --coverage --min=90
```

**What Counts Toward Coverage**:
- ✅ Model methods (scopes, accessors, business logic)
- ✅ Boot method logic (order number generation)
- ✅ Relationship methods

**What Doesn't Count**:
- ❌ Framework-provided methods (already tested by Laravel)
- ❌ Simple getters/setters
- ❌ Config files

**Improving Coverage**:
1. Run coverage report: `php artisan test --coverage-html=coverage`
2. Open `coverage/index.html` in browser
3. Identify uncovered lines (red highlights)
4. Write tests for uncovered logic
5. Re-run coverage check

## Next Steps

1. **Create Factories**: Start with factories for all 5 models
2. **Write Tests**: One model at a time (start with simplest: LabTestCategory)
3. **Run Frequently**: `php artisan test` after each test method
4. **Check Coverage**: Aim for 90%+ before moving to next model
5. **Document Findings**: Update this guide if you find better patterns

## Resources

- **Laravel Testing Docs**: https://laravel.com/docs/11.x/testing
- **PHPUnit Docs**: https://phpunit.de/documentation.html
- **Feature Spec**: [spec.md](./spec.md)
- **Data Model**: [data-model.md](./data-model.md)
- **Research Notes**: [research.md](./research.md)
- **Project Constitution**: `.specify/memory/constitution.md`

## Questions?

If you encounter issues not covered here:
1. Check [research.md](./research.md) for detailed technical decisions
2. Check [data-model.md](./data-model.md) for model specifications
3. Review existing tests in other modules for patterns
4. Consult Laravel testing documentation
