# Data Model: Reception Portal Enhancement

**Feature**: 021-reception-portal
**Date**: 2026-04-16

## Overview

This feature does not introduce new database tables. Instead, it defines permission relationships and access patterns for receptionists to existing entities.

---

## Entity Relationships

### Receptionist Access Model

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           RECEPTIONIST USER                             │
│                         (users table, role='receptionist')              │
└───────────────────────────────┬─────────────────────────────────────────┘
                                │
                                │ receptionist_id
                                ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         RECEPTIONIST PIVOT                              │
│                       (receptionist table)                              │
│   receptionist_id (FK→users) │ clinic_id (FK→clinics) │ vendor_id      │
└───────────────────────────────┬─────────────────────────────────────────┘
                                │
                                │ clinic_id
                                ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                              CLINIC                                     │
│                         (clinics table)                                 │
└───────────────────────────────┬─────────────────────────────────────────┘
                                │
          ┌─────────────────────┼─────────────────────┐
          │                     │                     │
          ▼                     ▼                     ▼
┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐
│   APPOINTMENTS  │   │ DOCTOR MAPPING  │   │    PATIENTS     │
│ (appointments)  │   │(doctor_clinic_  │   │ (via appts)     │
│ clinic_id = X   │   │ mappings)       │   │                 │
└─────────────────┘   │ clinic_id = X   │   └─────────────────┘
                      └────────┬────────┘
                               │ doctor_id
                               ▼
                      ┌─────────────────┐
                      │    DOCTORS      │
                      │ (doctors table) │
                      └────────┬────────┘
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
          ▼                    ▼                    ▼
┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐
│ DOCTOR VISITS   │   │  TEST ORDERS    │   │ ICU REQUESTS    │
│ (doctor_visit_  │   │ (test_orders)   │   │ (icu_admission_ │
│  requests)      │   │ doctor_id = Y   │   │  requests)      │
│ assigned_       │   └─────────────────┘   │ patient_id = Z  │
│  doctor_id = Y  │                         └─────────────────┘
└─────────────────┘
```

---

## Permission Model

### Existing Permissions (No Changes)

| Permission | Guard | Description |
|------------|-------|-------------|
| `view_doctor_visit` | web | View doctor visit requests |
| `create_doctor_visit` | web | Create doctor visit requests |
| `update_status_doctor_visit` | web | Update request status |
| `assign_doctor_visit` | web | Assign doctor to request |
| `view_test_orders` | web | View test orders |
| `view_icu_admission_requests` | web | View ICU admission requests |

### Permission-Role Assignments (Changes Required)

**Current State**:
```
receptionist → [view_doctor_visit, create_doctor_visit,
                update_status_doctor_visit, assign_doctor_visit]
```

**Target State**:
```
receptionist → [view_doctor_visit, create_doctor_visit,
                update_status_doctor_visit, assign_doctor_visit,
                view_test_orders,              ← NEW
                view_icu_admission_requests]   ← NEW
```

---

## Access Control Rules

### Doctor Visit Requests

| Action | Permission | Scope |
|--------|-----------|-------|
| View list | `view_doctor_visit` | All visible (filtered by role in controller) |
| View detail | `view_doctor_visit` | Same as list |
| Update status | `update_status_doctor_visit` | All visible requests |
| Assign doctor | `assign_doctor_visit` | All visible requests |
| Create | `create_doctor_visit` | Clinic-scoped |
| Delete | `view_doctor_visit` | Soft delete only |

**Receptionist Scope**: Currently not clinic-filtered in controller. May need enhancement if multi-clinic support is required.

### Test Orders (Read-Only)

| Action | Permission | Scope |
|--------|-----------|-------|
| View list | `view_test_orders` | Clinic doctors' orders |
| View detail | `view_test_orders` | Same as list |
| Create | - | **DENIED** |
| Edit | - | **DENIED** |
| Delete | - | **DENIED** |
| Enter results | - | **DENIED** |

**Receptionist Scope Filter**:
```sql
SELECT * FROM test_orders
WHERE doctor_id IN (
    SELECT doctor_id FROM doctor_clinic_mappings
    WHERE clinic_id = :receptionist_clinic_id
)
```

### ICU Admission Requests (Read-Only)

| Action | Permission | Scope |
|--------|-----------|-------|
| View list | `view_icu_admission_requests` | Clinic patients' requests |
| View detail | `view_icu_admission_requests` | Same as list |
| Respond | - | **DENIED** |
| Update status | - | **DENIED** |
| Delete | - | **DENIED** |

**Receptionist Scope Filter**:
```sql
SELECT * FROM icu_admission_requests
WHERE patient_id IN (
    SELECT DISTINCT user_id FROM appointments
    WHERE clinic_id = :receptionist_clinic_id
)
```

---

## State Transitions

### Doctor Visit Request Status

```
┌─────────┐     confirm      ┌───────────┐     complete     ┌───────────┐
│ pending │ ───────────────► │ confirmed │ ────────────────►│ completed │
└────┬────┘                  └─────┬─────┘                  └───────────┘
     │                             │
     │ cancel                      │ cancel
     │                             │
     ▼                             ▼
┌───────────┐              ┌───────────┐
│ cancelled │              │ cancelled │
└───────────┘              └───────────┘
```

**Receptionist Allowed Transitions**: All (same as admin)

### Test Order Status (Receptionist Read-Only)

```
┌─────────┐ ► ┌───────────┐ ► ┌──────────────────┐ ► ┌────────────┐ ► ┌───────────┐ ► ┌───────────┐
│ pending │   │ confirmed │   │ sample_collected │   │ processing │   │ completed │   │ delivered │
└─────────┘   └───────────┘   └──────────────────┘   └────────────┘   └───────────┘   └───────────┘
     │
     ▼
┌───────────┐
│ cancelled │
└───────────┘
```

**Receptionist Allowed Transitions**: None (view only)

### ICU Admission Request Status (Receptionist Read-Only)

```
┌─────────┐     accept      ┌──────────┐
│ pending │ ───────────────►│ accepted │────┐
└────┬────┘                 └──────────┘    │ cancel
     │                                      ▼
     │ reject      ┌──────────┐      ┌───────────┐
     ├────────────►│ rejected │      │ cancelled │
     │             └──────────┘      └───────────┘
     │                                      ▲
     │ request_info ┌────────────────┐      │ cancel
     └─────────────►│ info_requested │──────┘
                    └────────────────┘
```

**Receptionist Allowed Transitions**: None (view only)

---

## Validation Rules

### Doctor Visit Request (Receptionist Actions)

**Status Update**:
- `status`: Required, must be in [confirmed, cancelled, completed]
- `cancellation_reason`: Required if status = cancelled
- `note`: Optional, max 1000 chars

**Doctor Assignment**:
- `doctor_id`: Required, must exist in doctors table
- Doctor must be active and available

### Test Order / ICU Request (Receptionist Actions)

No validation rules - receptionist has read-only access.

---

## Audit Trail

All entities use `created_by`, `updated_by`, `deleted_by` fields from BaseModel.

### Doctor Visit Status History

**Table**: `doctor_visit_status_histories`

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | PK |
| visit_request_id | FK | doctor_visit_requests.id |
| old_status | string (nullable) | Previous status |
| new_status | string | New status |
| changed_by | FK | users.id - who made change |
| note | text (nullable) | Optional note |
| created_at | timestamp | When changed |

**Receptionist Actions**: Logged automatically when updating status.

---

## Dashboard Metrics Queries

### Pending Doctor Visits Count

```sql
SELECT COUNT(*)
FROM doctor_visit_requests
WHERE status = 'pending'
  AND deleted_at IS NULL
  -- Add clinic filter if needed:
  -- AND assigned_doctor_id IN (
  --     SELECT doctor_id FROM doctor_clinic_mappings
  --     WHERE clinic_id = :receptionist_clinic_id
  -- )
```

### Today's Test Orders Count (If Permission Granted)

```sql
SELECT COUNT(*)
FROM test_orders
WHERE DATE(order_date) = CURDATE()
  AND deleted_at IS NULL
  AND doctor_id IN (
      SELECT doctor_id FROM doctor_clinic_mappings
      WHERE clinic_id = :receptionist_clinic_id
  )
```

### Doctor Visits for Calendar

```sql
SELECT id, reference_number, patient_id, assigned_doctor_id,
       preferred_date, status
FROM doctor_visit_requests
WHERE status IN ('pending', 'confirmed')
  AND preferred_date >= :start_date
  AND preferred_date <= :end_date
  AND deleted_at IS NULL
```

---

## Migration Requirements

No database migrations required. All changes are:
1. Permission seeder updates
2. Controller logic modifications
3. View conditional rendering
4. Menu middleware updates
