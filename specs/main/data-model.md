# Data Model: RequestNurse Module

**Date**: 2026-03-09
**Branch**: `001-requestnurse-unit-tests`

## Entities

### Nurse

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | bigint | PK, auto-increment | — |
| nurse_id | bigint | FK→users.id, NOT NULL, CASCADE | The user who is a nurse |
| vendor_id | bigint | FK→users.id, NULLABLE | Clinic admin who manages this nurse |
| specialization | string(255) | NULLABLE | e.g., "Pediatric", "Geriatric" |
| experience | string(255) | NULLABLE | e.g., "5 years" |
| about | text | NULLABLE | Bio / description |
| availability_status | enum | DEFAULT 'available' | Values: available, busy, off_duty |
| service_area | string(255) | NULLABLE | Geographic area served |
| hourly_rate | double | DEFAULT 0 | Cast: double |
| status | tinyint | DEFAULT 1 | Cast: integer. 1=active, 0=inactive |
| created_by | int | NULLABLE | Auto-set by BaseModel boot |
| updated_by | int | NULLABLE | Auto-set by BaseModel boot |
| deleted_by | int | NULLABLE | Auto-set by BaseModel boot |
| created_at | timestamp | — | — |
| updated_at | timestamp | — | — |
| deleted_at | timestamp | NULLABLE | SoftDeletes |

**Relationships**:
- `user()` → belongsTo User via `nurse_id`
- `vendor()` → belongsTo User via `vendor_id`
- `nurseRequests()` → hasMany NurseRequest via `nurse_id` (local) → `nurse_id` (foreign)

**Scopes**:
- `available()` → `availability_status = 'available' AND status = 1`
- `active()` → `status = 1`

**Accessors**:
- `full_name` → delegates to `$this->user->full_name`

---

### NurseRequest

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | bigint | PK, auto-increment | — |
| patient_id | bigint | FK→users.id, NOT NULL, CASCADE | The patient requesting service |
| nurse_id | bigint | FK→users.id, NULLABLE | Assigned nurse (null = unassigned) |
| service_description | text | NOT NULL | What service is needed |
| request_date | datetime | DEFAULT CURRENT_TIMESTAMP | When the request was submitted |
| preferred_date | date | NOT NULL | Cast: date |
| preferred_time | time | NULLABLE | — |
| duration_hours | double | DEFAULT 1 | Cast: double. Min 1, Max 24 |
| address_line_1 | string(255) | NOT NULL | — |
| address_line_2 | string(255) | NULLABLE | — |
| city | string(255) | NOT NULL | — |
| state | string(255) | NULLABLE | — |
| country | string(255) | NULLABLE | — |
| postal_code | string(255) | NULLABLE | — |
| latitude | decimal(10,8) | NULLABLE | Cast: decimal:8 |
| longitude | decimal(11,8) | NULLABLE | Cast: decimal:8 |
| contact_number | string(255) | NOT NULL | — |
| status | string(255) | DEFAULT 'pending' | See State Machine below |
| payment_status | tinyint | DEFAULT 0 | Cast: boolean. 0=unpaid, 1=paid |
| total_amount | double | DEFAULT 0 | Cast: double. = hourly_rate × duration_hours |
| patient_notes | text | NULLABLE | — |
| admin_notes | text | NULLABLE | — |
| cancelled_by | string(255) | NULLABLE | Values: patient, admin, nurse |
| cancellation_reason | text | NULLABLE | — |
| created_by | int | NULLABLE | Auto-set by BaseModel boot |
| updated_by | int | NULLABLE | Auto-set by BaseModel boot |
| deleted_by | int | NULLABLE | Auto-set by BaseModel boot |
| created_at | timestamp | — | — |
| updated_at | timestamp | — | — |
| deleted_at | timestamp | NULLABLE | SoftDeletes |

**Relationships**:
- `patient()` → belongsTo User via `patient_id`
- `nurse()` → belongsTo User via `nurse_id`
- `nurseProfile()` → belongsTo Nurse via `nurse_id` (local) → `nurse_id` (foreign on nurses table)

**Scopes**:
- `pending()` → `status = 'pending'`
- `confirmed()` → `status = 'confirmed'`
- `byNurse($nurseId)` → `nurse_id = $nurseId`
- `byPatient($patientId)` → `patient_id = $patientId`
- `byStatus($status)` → `status = $status`

**Accessors**:
- `full_address` → `implode(', ', array_filter([address_line_1, address_line_2, city, state, postal_code, country]))`
- `status_badge` → HTML `<span class="badge ...">` with translated status text

**Constants**:
- `STATUS_PENDING = 'pending'`
- `STATUS_CONFIRMED = 'confirmed'`
- `STATUS_IN_PROGRESS = 'in_progress'`
- `STATUS_COMPLETED = 'completed'`
- `STATUS_CANCELLED = 'cancelled'`
- `STATUSES = [pending, confirmed, in_progress, completed, cancelled]`

## State Machine: NurseRequest Status

```
                ┌──────────┐
                │ pending  │
                └────┬─────┘
                     │
            ┌────────┴────────┐
            ▼                 ▼
      ┌───────────┐    ┌───────────┐
      │ confirmed │    │ cancelled │
      └─────┬─────┘    └───────────┘
            │                 ▲
      ┌─────┴─────┐          │
      ▼           ▼          │
┌─────────────┐  ┌───────────┘
│ in_progress │──┘
└──────┬──────┘
       │
       ▼
┌───────────┐
│ completed │
└───────────┘
```

**Allowed Transitions**:

| From | To (allowed) |
|------|-------------|
| pending | confirmed, cancelled |
| confirmed | in_progress, cancelled |
| in_progress | completed, cancelled |
| completed | *(terminal — no transitions)* |
| cancelled | *(terminal — no transitions)* |

## Validation Rules

### NurseFormRequest

| Field | Rules | Create vs Update |
|-------|-------|------------------|
| first_name | required, string, max:255 | Same |
| last_name | required, string, max:255 | Same |
| email | required, email, unique:users (except self) | unique check excludes `$this->user_id` on update |
| mobile | nullable, string, max:20 | Same |
| specialization | nullable, string, max:255 | Same |
| experience | nullable, string, max:255 | Same |
| about | nullable, string, max:2000 | Same |
| hourly_rate | required, numeric, min:0 | Same |
| service_area | nullable, string, max:255 | Same |
| availability_status | nullable, in:available,busy,off_duty | Same |
| profile_image | nullable, image, mimes:jpeg,png,jpg,gif, max:2048 | Same |
| password | required, min:6 (create) / nullable, min:6 (update) | Conditional: `POST && !$this->id` |

### NurseRequestFormRequest

| Field | Rules |
|-------|-------|
| patient_id | required, exists:users,id |
| service_description | required, string, max:1000 |
| preferred_date | required, date, after_or_equal:today |
| preferred_time | nullable, date_format:H:i |
| duration_hours | required, numeric, min:1, max:24 |
| address_line_1 | required, string, max:255 |
| address_line_2 | nullable, string, max:255 |
| city | required, string, max:100 |
| state | nullable, string, max:100 |
| country | nullable, string, max:100 |
| postal_code | nullable, string, max:20 |
| contact_number | required, string, max:20 |
| nurse_id | nullable, exists:users,id |
| patient_notes | nullable, string, max:1000 |
| admin_notes | nullable, string, max:1000 |

## Permissions Matrix

| Permission | admin | demo_admin | nurse |
|-----------|-------|------------|-------|
| view_request_nurse | YES | YES | YES |
| add_request_nurse | YES | YES | — |
| edit_request_nurse | YES | YES | — |
| delete_request_nurse | YES | YES | — |
| request_nurse_assign | YES | YES | — |
| view_nurses | YES | YES | — |
| add_nurses | YES | YES | — |
| edit_nurses | YES | YES | — |
| delete_nurses | YES | YES | — |
