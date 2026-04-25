# Data Model: Lab & Radiology Booking API

**Feature Branch**: `008-lab-radiology-booking-api`
**Date**: 2026-04-02

## New Entities

### FacilitySession

Configures operating hours and slot availability for labs and radiology centers. Modeled after `DoctorCallSession`.

**Table**: `facility_sessions`

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | bigint (PK) | auto-increment | |
| facility_type | enum('lab','radiology') | required | Which type of facility |
| facility_id | bigint | required | References labs.id or radiology_centers.id |
| day | string(20) | required | Day of week: monday, tuesday, ... sunday |
| start_time | time | required | Session start (e.g., 08:00) |
| end_time | time | required | Session end (e.g., 17:00) |
| slot_duration | integer | required, default: 30 | Minutes per slot |
| breaks | JSON | nullable | Array of {start_break, end_break} objects |
| is_holiday | boolean | default: false | If true, no slots available for this day |
| status | boolean | default: true | Active/inactive |
| created_by | integer | nullable | Audit (BaseModel) |
| updated_by | integer | nullable | Audit (BaseModel) |
| deleted_by | integer | nullable | Audit (BaseModel) |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

**Indexes**: (facility_type, facility_id, day), status
**Unique**: (facility_type, facility_id, day) — one session per day per facility

**Relationships**:
- Belongs to Lab (when facility_type = 'lab')
- Belongs to RadiologyCenter (when facility_type = 'radiology')

---

### FacilityBooking

A patient's scheduled appointment at a lab or radiology center.

**Table**: `facility_bookings`

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | bigint (PK) | auto-increment | |
| booking_number | string(20) | unique, auto-generated | Format: BK-YYYY-XXXX |
| type | enum('lab','radiology') | required | Booking type |
| lab_id | bigint (FK) | nullable, references labs.id | Set when type = 'lab' |
| radiology_center_id | bigint (FK) | nullable, references radiology_centers.id | Set when type = 'radiology' |
| patient_id | bigint (FK) | required, references users.id | The patient who booked |
| patient_name | string(255) | required | Patient's name at booking time |
| patient_phone | string(50) | required | Patient's phone number |
| scan_type | string(255) | nullable | Only for radiology bookings |
| booking_date | date | required | Appointment date |
| booking_time | time | required | Appointment time slot start |
| status | enum | required, default: 'pending' | pending, confirmed, cancelled, completed, no_show |
| notes | text | nullable | Patient notes |
| admin_notes | text | nullable | Admin notes |
| cancellation_reason | text | nullable | Reason if cancelled |
| cancelled_by | string(50) | nullable | 'patient' or 'admin' |
| created_by | integer | nullable | Audit (BaseModel) |
| updated_by | integer | nullable | Audit (BaseModel) |
| deleted_by | integer | nullable | Audit (BaseModel) |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

**Indexes**: patient_id, (type, lab_id), (type, radiology_center_id), booking_date, status
**Unique constraint**: No two active bookings at same facility+date+time: unique on (type, COALESCE(lab_id, radiology_center_id), booking_date, booking_time) WHERE status NOT IN ('cancelled')

**Relationships**:
- Belongs to User (patient)
- Belongs to Lab (when type = 'lab')
- Belongs to RadiologyCenter (when type = 'radiology')

**Status Transitions**:
```
pending → confirmed (admin confirms)
pending → cancelled (patient or admin cancels)
confirmed → completed (admin marks completed)
confirmed → cancelled (patient or admin cancels)
confirmed → no_show (admin marks no-show)
```

**Auto-numbering**: `BK-{YEAR}-{padded_sequence}` (same pattern as TestOrder's `LAB-YYYY-XXXX`)

---

## Existing Entities (No Changes)

### Lab
Already exists at `Modules/RadiologyLab/Models/Lab.php`. Referenced by FacilityBooking and FacilitySession.

### RadiologyCenter
Already exists at `Modules/RadiologyLab/Models/RadiologyCenter.php`. Referenced by FacilityBooking and FacilitySession.

### User
Already exists at `App/Models/User.php`. Referenced by FacilityBooking (patient_id).

---

## New Permissions

| Permission Name | Assigned To |
|----------------|-------------|
| view_facility_bookings | admin, demo_admin |
| manage_facility_bookings | admin, demo_admin |

---

## Validation Rules

### FacilityBookingRequest (Create)

| Field | Rules |
|-------|-------|
| type | required, in:lab,radiology |
| lab_id | required_if:type,lab, exists:labs,id |
| radiology_center_id | required_if:type,radiology, exists:radiology_centers,id |
| scan_type | required_if:type,radiology, nullable, string, max:255 |
| booking_date | required, date, after_or_equal:today |
| booking_time | required, date_format:H:i |
| patient_name | required, string, max:255 |
| patient_phone | required, string, max:50 |
| notes | nullable, string, max:1000 |

### AdminBookingActionRequest (Confirm/Cancel)

| Field | Rules |
|-------|-------|
| admin_notes | nullable, string, max:1000 |
| cancellation_reason | required_if action is cancel, string, max:500 |
