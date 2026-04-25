# API Endpoint Contracts: Lab & Radiology Booking

**Feature Branch**: `008-lab-radiology-booking-api`
**Date**: 2026-04-02

All routes registered in `Modules/RadiologyLab/Routes/api.php`.

---

## Public Endpoints (No Authentication)

### Get Lab Available Slots

```
GET /api/v1/facility-bookings/labs/{labId}/slots?date=2026-04-05
```

| Param | Type | Required | Validation |
|-------|------|----------|------------|
| labId | int (path) | Yes | exists:labs,id, status=1 |
| date | string (query) | Yes | date format Y-m-d, after_or_equal:today |

**Response (200)**:
```json
{
  "status": true,
  "message": "",
  "data": {
    "facility": { "id": 1, "name": "Al Borg Lab", "type": "lab" },
    "date": "2026-04-05",
    "day": "sunday",
    "slots": [
      { "time": "08:00", "available": true },
      { "time": "08:30", "available": true },
      { "time": "09:00", "available": false },
      { "time": "09:30", "available": true }
    ]
  }
}
```

**Notes**: Returns empty `slots` array if facility has no session for that day or is_holiday=true. `available: false` means slot is already booked.

---

### Get Radiology Center Available Slots

```
GET /api/v1/facility-bookings/radiology-centers/{centerId}/slots?date=2026-04-05
```

| Param | Type | Required | Validation |
|-------|------|----------|------------|
| centerId | int (path) | Yes | exists:radiology_centers,id, status=1 |
| date | string (query) | Yes | date format Y-m-d, after_or_equal:today |

**Response**: Same structure as lab slots, with `"type": "radiology"` in facility object.

---

## Patient Endpoints (auth:sanctum)

### Create Booking

```
POST /api/v1/facility-bookings
Auth: Bearer token (auth:sanctum)
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| type | string | Yes | in:lab,radiology |
| lab_id | int | If type=lab | exists:labs,id |
| radiology_center_id | int | If type=radiology | exists:radiology_centers,id |
| scan_type | string | If type=radiology | max:255 |
| booking_date | string | Yes | date Y-m-d, after_or_equal:today |
| booking_time | string | Yes | time H:i format |
| patient_name | string | Yes | max:255 |
| patient_phone | string | Yes | max:50 |
| notes | string | No | max:1000 |

**Response (201)**:
```json
{
  "status": true,
  "message": "Booking created successfully.",
  "data": {
    "id": 1,
    "booking_number": "BK-2026-0001",
    "type": "lab",
    "facility": { "id": 1, "name": "Al Borg Lab" },
    "scan_type": null,
    "patient_name": "Ahmed Ali",
    "patient_phone": "+201234567890",
    "booking_date": "2026-04-05",
    "booking_time": "09:00",
    "status": "pending",
    "notes": "Fasting required",
    "created_at": "2026-04-02T10:00:00.000000Z"
  }
}
```

**Error (422)** — slot unavailable:
```json
{
  "status": false,
  "message": "This time slot is no longer available."
}
```

---

### List My Bookings

```
GET /api/v1/facility-bookings?type=lab&status=pending&page=1
Auth: Bearer token (auth:sanctum)
```

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| type | string | No | Filter: lab, radiology |
| status | string | No | Filter: pending, confirmed, cancelled, completed, no_show |
| page | int | No | Pagination (default 1, 15 per page) |

**Response (200)**:
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "booking_number": "BK-2026-0001",
      "type": "lab",
      "facility": { "id": 1, "name": "Al Borg Lab" },
      "scan_type": null,
      "booking_date": "2026-04-05",
      "booking_time": "09:00",
      "status": "pending",
      "created_at": "2026-04-02T10:00:00.000000Z"
    }
  ],
  "links": { "..." },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

---

### View Booking Detail

```
GET /api/v1/facility-bookings/{id}
Auth: Bearer token (auth:sanctum)
```

**Response (200)**: Full booking resource (same as create response, plus admin_notes if present).

**Error (403)**: If booking doesn't belong to authenticated user (non-admin).

---

### Cancel Booking

```
POST /api/v1/facility-bookings/{id}/cancel
Auth: Bearer token (auth:sanctum)
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| cancellation_reason | string | No | max:500 |

**Response (200)**:
```json
{
  "status": true,
  "message": "Booking cancelled successfully.",
  "data": { "id": 1, "status": "cancelled" }
}
```

**Error (422)**: If booking status is not pending or confirmed.

---

## Admin Endpoints (auth:sanctum + permission)

### List All Bookings

```
GET /api/v1/admin/facility-bookings?type=lab&status=pending&date_from=2026-04-01&date_to=2026-04-30&facility_id=1&page=1
Auth: Bearer token (auth:sanctum)
Permission: view_facility_bookings
```

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| type | string | No | Filter: lab, radiology |
| status | string | No | Filter by status |
| date_from | string | No | Filter bookings from date |
| date_to | string | No | Filter bookings to date |
| facility_id | int | No | Filter by specific facility |
| page | int | No | Pagination |

**Response**: Paginated list of all bookings with patient details.

---

### Confirm Booking

```
POST /api/v1/admin/facility-bookings/{id}/confirm
Auth: Bearer token (auth:sanctum)
Permission: manage_facility_bookings
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| admin_notes | string | No | max:1000 |

**Response (200)**:
```json
{
  "status": true,
  "message": "Booking confirmed successfully.",
  "data": { "id": 1, "status": "confirmed" }
}
```

---

### Admin Cancel Booking

```
POST /api/v1/admin/facility-bookings/{id}/cancel
Auth: Bearer token (auth:sanctum)
Permission: manage_facility_bookings
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| cancellation_reason | string | Yes | max:500 |
| admin_notes | string | No | max:1000 |

---

### Mark Booking Complete / No-Show

```
POST /api/v1/admin/facility-bookings/{id}/status
Auth: Bearer token (auth:sanctum)
Permission: manage_facility_bookings
```

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| status | string | Yes | in:completed,no_show |
| admin_notes | string | No | max:1000 |
