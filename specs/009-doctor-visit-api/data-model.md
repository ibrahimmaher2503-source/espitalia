# Data Model: Doctor Visit Request API

**Feature**: `009-doctor-visit-api`
**Date**: 2026-04-05

---

## Entity: DoctorVisitRequest

**Table**: `doctor_visit_requests`
**Model**: `Modules/DoctorVisit/Models/DoctorVisitRequest.php`
**Extends**: `App\Models\BaseModel` (SoftDeletes, MediaLibrary, audit fields `created_by`/`updated_by`/`deleted_by`)

### Fields

| Column | Type | Nullable | Default | Constraints | Notes |
|--------|------|----------|---------|-------------|-------|
| `id` | bigInteger unsigned | No | auto | PK | |
| `reference_number` | string(20) | No | — | UNIQUE | Format: `VR-YYYY-NNNN`. Generated in `boot()`. |
| `patient_id` | bigInteger unsigned | No | — | FK → `users.id` | Authenticated user submitting the request |
| `preferred_doctor_id` | bigInteger unsigned | Yes | null | FK → `doctors.id` | Patient's preferred doctor at submission time |
| `assigned_doctor_id` | bigInteger unsigned | Yes | null | FK → `doctors.id` | Set by admin/receptionist post-submission |
| `visit_type` | string | No | `home_visit` | — | Fixed enum — always `home_visit` (v1) |
| `visit_reason` | text | No | — | — | Chief complaint / reason for visit |
| `preferred_date` | date | No | — | ≥ today at submission | Date-only; no time slot at submission |
| `contact_phone` | string(20) | No | — | — | Contact number for this specific visit |
| `additional_notes` | text | Yes | null | — | Patient free-text notes |
| `status` | string | No | `pending` | ENUM values below | Current lifecycle status |
| `admin_notes` | text | Yes | null | — | Internal notes (admin/receptionist/doctor) |
| `cancellation_reason` | text | Yes | null | — | Optional; stored when status → `cancelled` |
| `completed_at` | timestamp | Yes | null | — | Set when status → `completed` |
| `created_by` | integer unsigned | Yes | null | — | BaseModel auto-sets |
| `updated_by` | integer unsigned | Yes | null | — | BaseModel auto-sets |
| `deleted_by` | integer unsigned | Yes | null | — | BaseModel auto-sets |
| `created_at` | timestamp | Yes | null | — | |
| `updated_at` | timestamp | Yes | null | — | |
| `deleted_at` | timestamp | Yes | null | — | SoftDeletes |

### Indexes

```sql
UNIQUE KEY uk_reference_number (reference_number)
INDEX idx_patient_id (patient_id)
INDEX idx_status (status)
INDEX idx_preferred_date (preferred_date)
INDEX idx_assigned_doctor_id (assigned_doctor_id)
```

### Status Enum Values & Transition Rules

```
Status values: pending | confirmed | cancelled | completed

Valid transitions:
  pending   → confirmed   ✅ Roles: admin, receptionist, assigned-doctor
  pending   → cancelled   ✅ Roles: admin, receptionist, assigned-doctor
  confirmed → completed   ✅ Roles: admin, receptionist, assigned-doctor
  confirmed → cancelled   ✅ Roles: admin, receptionist, assigned-doctor
  completed → *           ❌ Terminal state — no transitions allowed
  cancelled → *           ❌ Terminal state — no transitions allowed
```

### Model Constants

```php
const STATUS_PENDING   = 'pending';
const STATUS_CONFIRMED = 'confirmed';
const STATUS_CANCELLED = 'cancelled';
const STATUS_COMPLETED = 'completed';

const STATUSES = ['pending', 'confirmed', 'cancelled', 'completed'];

const VALID_TRANSITIONS = [
    'pending'   => ['confirmed', 'cancelled'],
    'confirmed' => ['completed', 'cancelled'],
    'completed' => [],
    'cancelled' => [],
];

const VISIT_TYPE_HOME = 'home_visit';
```

### Eloquent Relationships

```php
patient()         → belongsTo(User::class, 'patient_id')
preferredDoctor() → belongsTo(Doctor::class, 'preferred_doctor_id', 'id')
assignedDoctor()  → belongsTo(Doctor::class, 'assigned_doctor_id', 'id')
statusHistories() → hasMany(DoctorVisitStatusHistory::class, 'visit_request_id')
```

---

## Entity: DoctorVisitStatusHistory

**Table**: `doctor_visit_status_histories`
**Model**: `Modules/DoctorVisit/Models/DoctorVisitStatusHistory.php`
**Note**: No `SoftDeletes` — audit log records are immutable.

### Fields

| Column | Type | Nullable | Default | Constraints | Notes |
|--------|------|----------|---------|-------------|-------|
| `id` | bigInteger unsigned | No | auto | PK | |
| `visit_request_id` | bigInteger unsigned | No | — | FK → `doctor_visit_requests.id` | |
| `old_status` | string | No | — | — | Status before transition |
| `new_status` | string | No | — | — | Status after transition |
| `changed_by` | bigInteger unsigned | No | — | FK → `users.id` | Staff member who performed the update |
| `note` | text | Yes | null | — | Optional context/reason for the change |
| `created_at` | timestamp | Yes | null | — | |
| `updated_at` | timestamp | Yes | null | — | |

### Indexes

```sql
INDEX idx_visit_request_id (visit_request_id)
INDEX idx_changed_by (changed_by)
```

### Eloquent Relationships

```php
visitRequest() → belongsTo(DoctorVisitRequest::class, 'visit_request_id')
changedBy()    → belongsTo(User::class, 'changed_by')
```

---

## Entity Cross-Reference Map

| Entity | References | Via | Type |
|--------|-----------|-----|------|
| `DoctorVisitRequest` | `users` | `patient_id` | belongsTo |
| `DoctorVisitRequest` | `doctors` | `preferred_doctor_id` | belongsTo |
| `DoctorVisitRequest` | `doctors` | `assigned_doctor_id` | belongsTo |
| `DoctorVisitStatusHistory` | `doctor_visit_requests` | `visit_request_id` | belongsTo |
| `DoctorVisitStatusHistory` | `users` | `changed_by` | belongsTo |

> `doctors.id` is the PK of the `doctors` table (`Modules/Clinic/Models/Doctor.php`).
> The Doctor model's `doctor_id` field is the FK → `users.id` (the doctor's user account).

---

## Validation Rules (DoctorVisitStoreRequest)

```php
'visit_reason'         => ['required', 'string', 'max:1000'],
'preferred_date'       => ['required', 'date', 'after_or_equal:today'],
'contact_phone'        => ['required', 'string', 'max:20'],
'additional_notes'     => ['nullable', 'string', 'max:2000'],
'preferred_doctor_id'  => ['nullable', 'integer', 'exists:doctors,id'],
```

> `visit_type` is NOT a form input — it is always set to `home_visit` server-side.

## Validation Rules (DoctorVisitStatusUpdateRequest)

```php
'status'               => ['required', 'string', 'in:confirmed,cancelled,completed'],
'cancellation_reason'  => ['nullable', 'string', 'max:1000'],  // only used when status=cancelled
'note'                 => ['nullable', 'string', 'max:1000'],   // appended to history
```
