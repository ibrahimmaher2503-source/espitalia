# API Contracts: Doctor Visit Request API

**Feature**: `009-doctor-visit-api`
**Base URL**: `/api/v1`
**Auth**: All endpoints require `Authorization: Bearer {sanctum-token}` header.

---

## Patient-Facing Endpoints

### POST /doctor-visit/requests

Submit a new doctor visit request.

**Auth**: `auth:sanctum` (role: `user`)
**Route name**: `api.doctor_visit.store`

**Request Body** (JSON):
```json
{
  "visit_reason": "Severe back pain for 3 days",
  "preferred_date": "2026-04-10",
  "contact_phone": "+966501234567",
  "preferred_doctor_id": 5,
  "additional_notes": "Please bring pain medication samples."
}
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `visit_reason` | string | Yes | max:1000 |
| `preferred_date` | date (YYYY-MM-DD) | Yes | after_or_equal:today |
| `contact_phone` | string | Yes | max:20 |
| `preferred_doctor_id` | integer | No | exists:doctors,id |
| `additional_notes` | string | No | max:2000 |

**Response 201** (Created):
```json
{
  "status": true,
  "data": {
    "id": 42,
    "reference_number": "VR-2026-0042",
    "visit_type": "home_visit",
    "visit_reason": "Severe back pain for 3 days",
    "preferred_date": "2026-04-10",
    "contact_phone": "+966501234567",
    "additional_notes": "Please bring pain medication samples.",
    "status": "pending",
    "preferred_doctor": { "id": 5, "name": "Dr. Ahmed Al-Rashid" },
    "assigned_doctor": null,
    "created_at": "2026-04-05T10:30:00+00:00"
  },
  "message": "doctor_visit.request_submitted_success"
}
```

**Response 422** (Validation error):
```json
{
  "status": false,
  "message": "The preferred date must be a date after or equal to today."
}
```

---

### GET /doctor-visit/requests

List the authenticated patient's own visit requests (most recent first).

**Auth**: `auth:sanctum` (role: `user`)
**Route name**: `api.doctor_visit.index`

**Query Parameters**: None required. Pagination: `?page=1&per_page=15`

**Response 200**:
```json
{
  "status": true,
  "data": [
    {
      "id": 42,
      "reference_number": "VR-2026-0042",
      "visit_reason": "Severe back pain for 3 days",
      "preferred_date": "2026-04-10",
      "status": "pending",
      "assigned_doctor": null,
      "created_at": "2026-04-05T10:30:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

---

### GET /doctor-visit/requests/{reference}

Get full details of a specific visit request by reference number.

**Auth**: `auth:sanctum` (role: `user`)
**Route name**: `api.doctor_visit.show`
**URL param**: `reference` — e.g., `VR-2026-0042`

**Authorization**: Returns 403 if the request belongs to a different patient.

**Response 200**:
```json
{
  "status": true,
  "data": {
    "id": 42,
    "reference_number": "VR-2026-0042",
    "visit_type": "home_visit",
    "visit_reason": "Severe back pain for 3 days",
    "preferred_date": "2026-04-10",
    "contact_phone": "+966501234567",
    "additional_notes": "Please bring pain medication samples.",
    "status": "confirmed",
    "admin_notes": "Doctor will arrive between 10am-12pm.",
    "preferred_doctor": { "id": 5, "name": "Dr. Ahmed Al-Rashid" },
    "assigned_doctor": { "id": 5, "name": "Dr. Ahmed Al-Rashid" },
    "cancellation_reason": null,
    "completed_at": null,
    "created_at": "2026-04-05T10:30:00+00:00",
    "updated_at": "2026-04-05T11:00:00+00:00"
  },
  "message": ""
}
```

**Response 404**:
```json
{ "status": false, "message": "doctor_visit.request_not_found" }
```

---

## Admin / Staff Endpoints

> **Auth**: `auth:sanctum` with role check (`admin` or `receptionist`; `doctor` for status-update only when assigned).

### GET /admin/doctor-visit/requests

List all visit requests (paginated). Admin, receptionist, doctor roles.

**Route name**: `api.admin.doctor_visit.index`

**Query Parameters**:
| Param | Type | Description |
|-------|------|-------------|
| `status` | string | Filter by status: `pending`, `confirmed`, `cancelled`, `completed` |
| `date_from` | date | Filter preferred_date ≥ date_from |
| `date_to` | date | Filter preferred_date ≤ date_to |
| `assigned_doctor_id` | integer | Filter by assigned doctor |
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 15) |

**Response 200**:
```json
{
  "status": true,
  "data": [
    {
      "id": 42,
      "reference_number": "VR-2026-0042",
      "patient": { "id": 10, "name": "Sara Al-Hassan", "email": "sara@example.com" },
      "preferred_doctor": { "id": 5, "name": "Dr. Ahmed Al-Rashid" },
      "assigned_doctor": null,
      "visit_reason": "Severe back pain for 3 days",
      "preferred_date": "2026-04-10",
      "status": "pending",
      "created_at": "2026-04-05T10:30:00+00:00"
    }
  ],
  "meta": { "current_page": 1, "per_page": 15, "total": 38, "last_page": 3 }
}
```

---

### PUT /admin/doctor-visit/requests/{reference}/status

Update the status of a visit request.

**Route name**: `api.admin.doctor_visit.update_status`
**Auth**: `admin` or `receptionist` (any request); `doctor` (only if `assigned_doctor_id` matches)

**Request Body**:
```json
{
  "status": "confirmed",
  "cancellation_reason": null,
  "note": "Confirmed for morning slot."
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `status` | string | Yes | Must be in: `confirmed`, `cancelled`, `completed` |
| `cancellation_reason` | string | No | Stored when `status = cancelled` |
| `note` | string | No | Appended to status history |

**Response 200**:
```json
{
  "status": true,
  "data": { /* full DoctorVisitRequestResource */ },
  "message": "doctor_visit.status_updated_success"
}
```

**Response 422** (invalid transition, e.g. completed → confirmed):
```json
{
  "status": false,
  "message": "doctor_visit.invalid_status_transition"
}
```

**Response 403** (doctor not assigned to this request):
```json
{
  "status": false,
  "message": "doctor_visit.unauthorized_status_update"
}
```

---

### PUT /admin/doctor-visit/requests/{reference}/assign-doctor

Assign or reassign a doctor to a visit request.

**Route name**: `api.admin.doctor_visit.assign_doctor`
**Auth**: `admin` or `receptionist` only

**Request Body**:
```json
{ "doctor_id": 7 }
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `doctor_id` | integer | Yes | `exists:doctors,id` |

**Response 200**:
```json
{
  "status": true,
  "data": { /* full DoctorVisitRequestResource */ },
  "message": "doctor_visit.doctor_assigned_success"
}
```

---

### POST /admin/doctor-visit/requests/{reference}/notes

Add or update internal admin notes on a visit request.

**Route name**: `api.admin.doctor_visit.add_note`
**Auth**: `admin`, `receptionist`, or assigned `doctor`

**Request Body**:
```json
{ "admin_notes": "Patient prefers afternoon appointments. Will coordinate with assigned doctor." }
```

**Response 200**:
```json
{
  "status": true,
  "data": { /* full DoctorVisitRequestResource */ },
  "message": "doctor_visit.notes_updated_success"
}
```

---

## Error Codes Reference

| HTTP | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created (new request submitted) |
| 401 | Unauthenticated |
| 403 | Forbidden (wrong patient or insufficient role) |
| 404 | Resource not found |
| 422 | Validation error or invalid status transition |
| 500 | Server error |
