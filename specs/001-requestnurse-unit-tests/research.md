# Research: RequestNurse Module Unit Tests

**Date**: 2026-03-09
**Branch**: `001-requestnurse-unit-tests`

## R1: Module Factory Registration Pattern

**Decision**: Create factories inside `Modules/RequestNurse/database/factories/` and use the `HasFactory` trait's `newFactory()` override method to resolve them.

**Rationale**: Both `Nurse` and `NurseRequest` models already use the `HasFactory` trait. Laravel's default factory resolution looks in `Database\Factories\` namespace, but module factories need to be in the module's namespace. The standard Laravel modules approach is to override `newFactory()` in the model to return the correct factory class.

**Alternatives considered**:
- Placing factories in root `database/factories/`: Violates Constitution I (Module Encapsulation) — factories belong with their module.
- Using setup methods with raw `create()`: More verbose, harder to reuse across tests, loses faker integration.

**Implementation pattern**:
```php
// In Nurse model - add newFactory() method
protected static function newFactory()
{
    return \Modules\RequestNurse\Database\Factories\NurseFactory::new();
}
```

---

## R2: Form Request Testing Approach

**Decision**: Use Laravel's `Validator` facade to test form request rules directly, instantiating the form request and extracting its rules, then running validation against test data.

**Rationale**: This approach tests validation logic in isolation without HTTP overhead. The project's existing tests don't have form request test examples, but this is the standard Laravel community pattern.

**Alternatives considered**:
- Feature tests via HTTP calls: Tests more than just validation (routing, middleware, controller). Out of scope per spec.
- Manually reimplementing rules in tests: Fragile, doesn't test the actual form request class.

**Implementation pattern**:
```php
// Create a FormRequest instance, extract rules, validate against data
$request = new NurseFormRequest();
$validator = Validator::make($data, $request->rules());
$this->assertTrue($validator->passes());
```

---

## R3: API Resource Testing Approach

**Decision**: Create model instances with relationships loaded, pass through the resource's `toArray()` method, and assert the output structure matches expected keys and values.

**Rationale**: Resources are pure data transformers. Testing them requires real model instances (from factory) with eager-loaded relationships, then asserting the JSON output structure. This approach verifies both key presence and value mapping.

**Alternatives considered**:
- Testing via API endpoints: Feature test territory, out of scope.
- Mocking models: Would miss real relationship loading behavior.

**Implementation pattern**:
```php
$nurse = Nurse::factory()->create();
$nurse->load('user');
$resource = new NurseResource($nurse);
$array = $resource->toArray(request());
$this->assertArrayHasKey('id', $array);
```

---

## R4: Status Transition Testing Approach

**Decision**: Test the controller's `update_status` method indirectly by replicating its transition validation logic in tests, and test the `assign_nurse` method's amount calculation by creating real records and calling the controller method via HTTP or by extracting and testing the logic.

**Rationale**: The status transition logic is embedded in `NurseRequestsController::update_status()`. Since the spec calls for unit tests (not feature tests), we'll test the transition map as a data-driven test: for each current status, verify which new statuses are accepted or rejected. The total_amount calculation (`hourly_rate * duration_hours`) is simple arithmetic embedded in `assign_nurse()` and `store()`.

**Approach**: Use feature-style tests (extending `Tests\TestCase` with `RefreshDatabase`) for status transition and amount calculation tests since they involve authenticated requests, database operations, and controller methods. This matches the project's existing test pattern (see `AppointmentTest.php`).

**Alternatives considered**:
- Extracting logic to a service class first: Violates YAGNI (Constitution V) — adds unnecessary abstraction.
- Pure unit tests with mocks: Would require extensive mocking of Request, Auth, Eloquent; fragile and hard to maintain.

---

## R5: Permission Seeder Testing Approach

**Decision**: Run the seeder against a fresh test database and assert the expected roles, permissions, and role-permission mappings exist using Spatie's API.

**Rationale**: Seeder tests need a real database because they use `firstOrCreate` and Spatie's `givePermissionTo`. SQLite in-memory with `RefreshDatabase` provides isolation. Pre-existing `admin` and `demo_admin` roles must be seeded first since the RequestNursePermissionSeeder depends on them.

**Alternatives considered**:
- Mocking Role/Permission models: Would not test actual database operations.
- Using `DatabaseSeeder` as prerequisite: Too heavy; only need the permission-related tables seeded.

**Implementation pattern**:
```php
// Seed prerequisite roles first
Role::create(['name' => 'admin', 'title' => 'Admin']);
Role::create(['name' => 'demo_admin', 'title' => 'Demo Admin']);

// Run the seeder
$this->seed(RequestNursePermissionSeeder::class);

// Assert
$this->assertTrue(Role::where('name', 'nurse')->exists());
```

---

## R6: BaseModel Boot Event Handling in Tests

**Decision**: Accept that BaseModel's boot events (setting `created_by`, `updated_by`) may set null values during tests when no user is authenticated. Tests that require authenticated context will use `actingAs()`.

**Rationale**: The BaseModel boot events check `request()->route()` before setting audit fields, and tests without HTTP context will have null routes. This is safe because the audit fields are nullable and non-critical to the logic being tested.

**Alternatives considered**:
- Mocking Auth in every test: Adds unnecessary complexity for model tests that don't depend on audit fields.
- Disabling boot events: Would change behavior under test.

---

## R7: SQLite Compatibility for CONCAT and ENUM

**Decision**: Model-level tests avoid raw SQL. Scope tests use Eloquent where clauses which work identically on SQLite. The `availability_status` enum column works as a string in SQLite (no native enum support, treated as TEXT).

**Rationale**: SQLite doesn't support ENUM or CONCAT natively, but Eloquent's query builder abstracts this. The `CONCAT` usage in `index_data()` DataTable filtering is out of scope (DataTable tests are excluded). Scopes use simple `where()` clauses that work on all databases.

**Alternatives considered**:
- Switching to MySQL for tests: Slower, more setup, unnecessary for unit tests.
- Adding DB-specific handling: Over-engineering for tests that don't hit raw SQL.
