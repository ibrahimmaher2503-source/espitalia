# Data Model: RadiologyLab Module Models

**Feature**: 002-labs-unit-tests
**Phase**: 1 (Design & Contracts)
**Date**: 2026-03-11
**Purpose**: Document model structures, relationships, and business rules for comprehensive unit testing

## Overview

The RadiologyLab module consists of 5 Eloquent models that manage lab test definitions, orders, results, and reporting. All models extend `App\Models\BaseModel` which provides soft deletes, media library support, and audit fields.

## Model Inventory

| Model | Table | Primary Purpose | Key Features |
|-------|-------|----------------|--------------|
| `LabTestCategory` | `lab_test_categories` | Organize lab tests | Slug, ordering, active status |
| `LabTest` | `lab_tests` | Define available tests | Pricing, sample types, departments |
| `TestOrder` | `test_orders` | Patient test requests | Status workflow, auto-numbering |
| `TestOrderItem` | `test_order_items` | Individual test in order | Results, reference ranges |
| `TestReport` | `test_reports` | Final report documents | PDF attachments, status tracking |

## Detailed Model Specifications

### 1. LabTestCategory

**Purpose**: Categorize lab tests (e.g., "Blood Tests", "Radiology", "Microbiology")

#### Attributes
```php
[
    'id' => 'integer (PK)',
    'name' => 'string (required)',
    'slug' => 'string (unique, auto-generated)',
    'description' => 'text (nullable)',
    'icon' => 'string (nullable)',
    'display_order' => 'integer (default: 0)',
    'status' => 'boolean (default: 1)',
    'created_by' => 'integer (FK users)',
    'updated_by' => 'integer (FK users)',
    'deleted_by' => 'integer (FK users)',
    'created_at' => 'timestamp',
    'updated_at' => 'timestamp',
    'deleted_at' => 'timestamp (nullable)',
]
```

#### Casts
- `status` → boolean
- `display_order` → integer

#### Relationships
- `labTests()` → `hasMany(LabTest::class)` - Tests in this category

#### Scopes
- `active()` → Where status = 1
- `ordered()` → Order by display_order ASC

#### Business Rules
- Slug auto-generated from name via `HasSlug` trait
- Categories can be soft-deleted
- Active status filters visible categories

#### Test Coverage Requirements
- ✅ Attribute casting (status, display_order)
- ✅ Relationship to lab tests
- ✅ Scopes (active, ordered)
- ✅ Slug auto-generation
- ✅ Soft delete behavior

---

### 2. LabTest

**Purpose**: Define individual diagnostic tests available for ordering

#### Attributes
```php
[
    'id' => 'integer (PK)',
    'category_id' => 'integer (FK lab_test_categories)',
    'name' => 'string (required)',
    'slug' => 'string (unique, auto-generated)',
    'code' => 'string (unique, required)',
    'description' => 'text (nullable)',
    'sample_type' => 'string (enum, required)',
    'department' => 'string (enum, required)',
    'preparation_instructions' => 'text (nullable)',
    'default_price' => 'float (required)',
    'turnaround_time' => 'string (nullable)',
    'status' => 'boolean (default: 1)',
    'created_by' => 'integer (FK users)',
    'updated_by' => 'integer (FK users)',
    'deleted_by' => 'integer (FK users)',
    'created_at' => 'timestamp',
    'updated_at' => 'timestamp',
    'deleted_at' => 'timestamp (nullable)',
]
```

#### Casts
- `status` → boolean
- `default_price` → float

#### Constants
```php
SAMPLE_TYPES = ['blood', 'urine', 'stool', 'tissue', 'imaging', 'swab', 'other']
DEPARTMENTS = ['laboratory', 'radiology']
```

#### Relationships
- `category()` → `belongsTo(LabTestCategory::class)` - Parent category
- `orderItems()` → `hasMany(TestOrderItem::class)` - Times this test has been ordered

#### Scopes
- `active()` → Where status = 1
- `laboratory()` → Where department = 'laboratory'
- `radiology()` → Where department = 'radiology'

#### Accessors
- `formatted_price` → `number_format($this->default_price, 2)`
- `department_badge` → HTML badge with color-coded department label
- `sample_type_label` → Translated human-readable sample type

#### Business Rules
- Code must be unique (e.g., "CBC", "XRAY-CHEST")
- Sample type must be one of `SAMPLE_TYPES`
- Department must be 'laboratory' or 'radiology'
- Price is default; actual price per order can differ (stored in TestOrderItem)
- Slug auto-generated from name

#### Test Coverage Requirements
- ✅ Attribute casting (status, price)
- ✅ Constant validation (SAMPLE_TYPES, DEPARTMENTS)
- ✅ Relationships (category, orderItems)
- ✅ Scopes (active, laboratory, radiology)
- ✅ Accessors (formatted_price, badges, labels)
- ✅ Edge case: null sample_type should return safe default in accessor

---

### 3. TestOrder

**Purpose**: Represent a patient's request for one or more lab tests with workflow tracking

#### Attributes
```php
[
    'id' => 'integer (PK)',
    'order_number' => 'string (unique, auto-generated)',
    'patient_id' => 'integer (FK users, required)',
    'doctor_id' => 'integer (FK users, nullable)',
    'lab_technician_id' => 'integer (FK users, nullable)',
    'clinical_notes' => 'text (nullable)',
    'priority' => 'string (enum, default: routine)',
    'order_date' => 'date (required)',
    'status' => 'string (enum, default: pending)',
    'payment_status' => 'string (enum, default: unpaid)',
    'total_amount' => 'float (required)',
    'discount_amount' => 'float (default: 0)',
    'final_amount' => 'float (required)',
    'admin_notes' => 'text (nullable)',
    'cancelled_by' => 'integer (FK users, nullable)',
    'cancellation_reason' => 'text (nullable)',
    'created_by' => 'integer (FK users)',
    'updated_by' => 'integer (FK users)',
    'deleted_by' => 'integer (FK users)',
    'created_at' => 'timestamp',
    'updated_at' => 'timestamp',
    'deleted_at' => 'timestamp (nullable)',
]
```

#### Casts
- `order_date` → date
- `total_amount` → float
- `discount_amount` → float
- `final_amount` → float

#### Status Constants
```php
STATUS_PENDING = 'pending'
STATUS_CONFIRMED = 'confirmed'
STATUS_SAMPLE_COLLECTED = 'sample_collected'
STATUS_PROCESSING = 'processing'
STATUS_COMPLETED = 'completed'
STATUS_DELIVERED = 'delivered'
STATUS_CANCELLED = 'cancelled'

STATUSES = [all of the above]
```

#### Priority Constants
```php
PRIORITY_ROUTINE = 'routine'
PRIORITY_URGENT = 'urgent'
PRIORITY_STAT = 'stat'

PRIORITIES = [all of the above]
```

#### Status Workflow (`ALLOWED_TRANSITIONS`)
```php
[
    'pending' => ['confirmed', 'cancelled'],
    'confirmed' => ['sample_collected', 'cancelled'],
    'sample_collected' => ['processing', 'cancelled'],
    'processing' => ['completed'],
    'completed' => ['delivered'],
    'delivered' => [],        // Terminal state
    'cancelled' => [],        // Terminal state
]
```

#### Relationships
- `patient()` → `belongsTo(User::class, 'patient_id')` - Patient who ordered
- `doctor()` → `belongsTo(User::class, 'doctor_id')` - Ordering physician
- `labTechnician()` → `belongsTo(User::class, 'lab_technician_id')` - Assigned tech
- `items()` → `hasMany(TestOrderItem::class)` - Individual tests in this order
- `reports()` → `hasMany(TestReport::class)` - Generated reports

#### Scopes
- `byPatient($patientId)` → Where patient_id = $patientId
- `byTechnician($technicianId)` → Where lab_technician_id = $technicianId
- `byDoctor($doctorId)` → Where doctor_id = $doctorId
- `byStatus($status)` → Where status = $status
- `pending()` → Where status = 'pending'

#### Accessors
- `status_badge` → HTML badge with color-coded status label
- `payment_badge` → HTML badge for payment status (paid/unpaid/refunded)
- `priority_badge` → HTML badge for priority level

#### Business Logic Methods
- `canTransitionTo($newStatus)` → Returns boolean, checks `ALLOWED_TRANSITIONS`

#### Auto-Generation Logic (boot method)
```php
// On creating:
if (empty($order_number)) {
    $year = now()->format('Y');
    $lastOrder = static::withTrashed()
        ->where('order_number', 'like', "LAB-{$year}-%")
        ->orderBy('id', 'desc')
        ->first();

    $nextNumber = $lastOrder ? (int)substr($lastOrder->order_number, -4) + 1 : 1;
    $order_number = "LAB-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}
```

#### Business Rules
- Order number format: `LAB-YYYY-XXXX` (e.g., LAB-2026-0001)
- Order numbers are sequential per year
- Soft-deleted orders are included in sequence calculation (prevent duplicates)
- Manual order_number bypasses auto-generation
- Status transitions must follow `ALLOWED_TRANSITIONS`
- Terminal statuses (delivered, cancelled) cannot transition

#### Test Coverage Requirements
- ✅ Attribute casting (dates, amounts)
- ✅ All status constants defined
- ✅ All priority constants defined
- ✅ Order number auto-generation (format, sequence, year rollover)
- ✅ Status workflow (`canTransitionTo()` for all valid/invalid combinations)
- ✅ Relationships (patient, doctor, technician, items, reports)
- ✅ Scopes (all filter scopes)
- ✅ Accessors (all badge methods)
- ✅ Edge cases:
  - First order of year → LAB-YYYY-0001
  - After LAB-YYYY-9999 → LAB-YYYY-10000 (no padding issue)
  - Deleted relationships (patient, doctor)
  - Terminal state transitions (should reject all)

---

### 4. TestOrderItem

**Purpose**: Individual test within an order, with result tracking

#### Attributes
```php
[
    'id' => 'integer (PK)',
    'test_order_id' => 'integer (FK test_orders, required)',
    'lab_test_id' => 'integer (FK lab_tests, required)',
    'price' => 'float (required)',
    'status' => 'string (enum, default: pending)',
    'result_value' => 'string (nullable)',
    'result_unit' => 'string (nullable)',
    'reference_range' => 'string (nullable)',
    'result_status' => 'string (enum, nullable)',
    'result_notes' => 'text (nullable)',
    'result_date' => 'datetime (nullable)',
    'result_by' => 'integer (FK users, nullable)',
    'created_by' => 'integer (FK users)',
    'updated_by' => 'integer (FK users)',
    'deleted_by' => 'integer (FK users)',
    'created_at' => 'timestamp',
    'updated_at' => 'timestamp',
    'deleted_at' => 'timestamp (nullable)',
]
```

#### Casts
- `result_date` → datetime
- `price` → float

#### Status Constants
```php
STATUS_PENDING = 'pending'
STATUS_SAMPLE_COLLECTED = 'sample_collected'
STATUS_PROCESSING = 'processing'
STATUS_COMPLETED = 'completed'
```

#### Relationships
- `testOrder()` → `belongsTo(TestOrder::class)` - Parent order
- `labTest()` → `belongsTo(LabTest::class)` - Test definition
- `resultEnteredBy()` → `belongsTo(User::class, 'result_by')` - Technician who entered results

#### Scopes
- `pending()` → Where status = 'pending'
- `completed()` → Where status = 'completed'
- `withResults()` → Where result_value IS NOT NULL

#### Accessors
- `result_status_badge` → HTML badge for result status (normal/abnormal/critical)
- `has_result` → Boolean, true if result_value is not empty

#### Business Rules
- Price may differ from LabTest default_price (discounts, custom pricing)
- Result fields are nullable until results are entered
- Result status can be 'normal', 'abnormal', or 'critical'

#### Test Coverage Requirements
- ✅ Attribute casting (result_date, price)
- ✅ Status constants defined
- ✅ Relationships (testOrder, labTest, resultEnteredBy)
- ✅ Scopes (pending, completed, withResults)
- ✅ Accessors (result_status_badge, has_result)
- ✅ Edge cases:
  - Null result values
  - Result status without result value
  - Deleted lab test reference

---

### 5. TestReport

**Purpose**: Final report document for completed test orders

#### Attributes
```php
[
    'id' => 'integer (PK)',
    'test_order_id' => 'integer (FK test_orders, required)',
    'report_type' => 'string (enum, required)',
    'title' => 'string (required)',
    'notes' => 'text (nullable)',
    'generated_by' => 'integer (FK users, nullable)',
    'report_date' => 'date (required)',
    'status' => 'string (enum, default: draft)',
    'created_by' => 'integer (FK users)',
    'updated_by' => 'integer (FK users)',
    'deleted_by' => 'integer (FK users)',
    'created_at' => 'timestamp',
    'updated_at' => 'timestamp',
    'deleted_at' => 'timestamp (nullable)',
]
```

#### Casts
- `report_date` → date

#### Appends
- `file_url` → Computed attribute from media library

#### Type Constants
```php
TYPE_LAB_REPORT = 'lab_report'
TYPE_RADIOLOGY_REPORT = 'radiology_report'
TYPE_CUSTOM = 'custom'
```

#### Status Constants
```php
STATUS_DRAFT = 'draft'
STATUS_FINAL = 'final'
STATUS_AMENDED = 'amended'
```

#### Relationships
- `testOrder()` → `belongsTo(TestOrder::class)` - Associated order
- `generatedBy()` → `belongsTo(User::class, 'generated_by')` - Creator

#### Media Collections (Spatie MediaLibrary)
- `report_file` → Single file upload (PDF typically)

#### Accessors
- `file_url` → Returns first media URL or null
- `status_badge` → HTML badge for report status

#### Business Rules
- Each report is associated with one test order
- Reports can have attached PDF files via Spatie MediaLibrary
- Status progresses: draft → final → amended (if corrections needed)

#### Test Coverage Requirements
- ✅ Attribute casting (report_date)
- ✅ Type and status constants defined
- ✅ Relationships (testOrder, generatedBy)
- ✅ Media collection registration (`registerMediaCollections()`)
- ✅ Accessors (file_url, status_badge)
- ✅ Edge cases:
  - Report without file attachment (file_url should be null)
  - Null generated_by user

---

## Relationship Diagram

```
LabTestCategory
    │
    └─── hasMany ───> LabTest
                        │
                        └─── hasMany ───> TestOrderItem
                                              │
                                              ├─── belongsTo ───> TestOrder
                                              │                      │
                                              │                      ├─── belongsTo ───> User (patient)
                                              │                      ├─── belongsTo ───> User (doctor)
                                              │                      ├─── belongsTo ───> User (technician)
                                              │                      └─── hasMany ───> TestReport
                                              │                                             │
                                              │                                             └─── belongsTo ───> User (generated_by)
                                              └─── belongsTo ───> User (result_by)
```

## Validation Rules Summary

### LabTestCategory
- `name`: required, string, max:255
- `status`: boolean

### LabTest
- `name`: required, string, max:255
- `code`: required, unique, max:50
- `sample_type`: required, in:SAMPLE_TYPES
- `department`: required, in:DEPARTMENTS
- `default_price`: required, numeric, min:0

### TestOrder
- `patient_id`: required, exists:users
- `order_date`: required, date
- `priority`: required, in:PRIORITIES
- `status`: required, in:STATUSES
- `total_amount`: required, numeric, min:0

### TestOrderItem
- `test_order_id`: required, exists:test_orders
- `lab_test_id`: required, exists:lab_tests
- `price`: required, numeric, min:0
- `status`: required, in:STATUSES

### TestReport
- `test_order_id`: required, exists:test_orders
- `report_type`: required, in:TYPES
- `title`: required, string
- `report_date`: required, date
- `status`: required, in:STATUSES

## State Machine: TestOrder Status Workflow

```
    ┌─────────┐
    │ pending │ ──────> cancelled
    └────┬────┘
         │
         v
    ┌───────────┐
    │ confirmed │ ──────> cancelled
    └─────┬─────┘
          │
          v
    ┌──────────────────┐
    │ sample_collected │ ──────> cancelled
    └────────┬─────────┘
             │
             v
    ┌────────────┐
    │ processing │
    └──────┬─────┘
           │
           v
    ┌───────────┐
    │ completed │
    └─────┬─────┘
          │
          v
    ┌───────────┐
    │ delivered │ (terminal)
    └───────────┘

    ┌───────────┐
    │ cancelled │ (terminal)
    └───────────┘
```

**Rules**:
- Cannot skip intermediate statuses
- Terminal statuses cannot transition
- Cancellation allowed until processing starts

## Critical Business Logic to Test

1. **Order Number Auto-Generation** (TestOrder)
   - Sequential numbering per year
   - Format validation: LAB-YYYY-XXXX
   - Handles year rollovers
   - Handles numbers > 9999 (5+ digits)
   - Includes soft-deleted orders in sequence

2. **Status Transition Validation** (TestOrder)
   - `canTransitionTo()` enforces workflow rules
   - 21 valid transition combinations (7 statuses)
   - All invalid transitions rejected

3. **Accessor Edge Cases**
   - Null values return safe defaults (not errors)
   - Missing translations return fallback text
   - Deleted relationships return null gracefully

## Test Data Requirements

### Factories Needed
1. `LabTestCategoryFactory` - Generate test categories
2. `LabTestFactory` - Generate lab test definitions
3. `TestOrderFactory` - Generate test orders with states (pending, confirmed, etc.)
4. `TestOrderItemFactory` - Generate order items with results
5. `TestReportFactory` - Generate report documents

### Factory State Methods Needed
- `LabTestFactory::laboratory()`, `::radiology()`, `::active()`, `::inactive()`
- `TestOrderFactory::pending()`, `::confirmed()`, `::completed()`, `::cancelled()`
- `TestOrderFactory::urgent()`, `::stat()`, `::routine()`
- `TestOrderItemFactory::withResults()`, `::pending()`, `::completed()`
- `TestReportFactory::draft()`, `::final()`, `::amended()`

## Next Phase

Phase 2 (Tasks) will break down the implementation into:
1. Create all 5 model factories
2. Create test classes for each model
3. Implement tests for each requirement area
4. Achieve 90%+ coverage
5. Document test execution and results
