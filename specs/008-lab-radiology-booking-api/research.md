# Research: Lab & Radiology Booking API

**Feature Branch**: `008-lab-radiology-booking-api`
**Date**: 2026-04-02

## Decision Log

### D1: Booking Data Model — Single Table vs. Separate Tables

**Decision**: Use a single `facility_bookings` table with a `type` column (lab/radiology) and nullable foreign keys for `lab_id` and `radiology_center_id`.

**Rationale**: The two booking types share 90% of the same fields (patient, date, time, status, notes). A single table simplifies querying ("all my bookings"), admin management, and avoids duplicating migration/model/controller logic. The only radiology-specific field (`scan_type`) is nullable. This follows YAGNI (Constitution Principle V).

**Alternatives considered**:
- *Two separate tables* (lab_bookings, radiology_bookings): More normalized but doubles the migration, model, controller, resource, and test count for nearly identical entities.
- *Polymorphic relation* (bookable_type/bookable_id): The project doesn't use polymorphic relations elsewhere; introducing one here would deviate from conventions.

---

### D2: Time Slot Model — FacilitySession

**Decision**: Create a `facility_sessions` table modeled after `DoctorCallSession`, with a `facility_type`/`facility_id` pair to link sessions to either a Lab or RadiologyCenter.

**Rationale**: The DoctorCallSession pattern is proven in the codebase — it stores day-of-week, start/end times, breaks (JSON), holiday flag, and status. Reusing this pattern for facility sessions is consistent and simple. Each facility can have sessions for each day of the week.

**Schema**:
- `facility_type`: enum (lab, radiology)
- `facility_id`: integer (references labs.id or radiology_centers.id)
- `day`: string (monday, tuesday, etc.)
- `start_time`: time
- `end_time`: time
- `slot_duration`: integer (minutes, default 30)
- `breaks`: JSON array of `{start_break, end_break}` objects
- `is_holiday`: boolean (default false)
- `status`: boolean (default true)

**Slot generation**: Generate available slots at runtime by dividing the session time range into `slot_duration` intervals, excluding breaks and already-booked slots.

---

### D3: API Route Structure — Follow Call Booking Pattern

**Decision**: Follow the 3-step call booking API pattern:
1. Search facilities (existing, public)
2. Get available slots for a facility+date (public, no auth needed for browsing)
3. Create booking (authenticated)

**Rationale**: This is the established pattern in `CallBookingApiController`. Patients search → see availability → book. The slot endpoint can be public (read-only), but booking requires `auth:sanctum`.

**Route structure**:
- Public: `GET /api/v1/facility-bookings/labs/{id}/slots?date=YYYY-MM-DD`
- Public: `GET /api/v1/facility-bookings/radiology-centers/{id}/slots?date=YYYY-MM-DD`
- Auth: `POST /api/v1/facility-bookings` (create booking)
- Auth: `GET /api/v1/facility-bookings` (my bookings)
- Auth: `GET /api/v1/facility-bookings/{id}` (booking detail)
- Auth: `POST /api/v1/facility-bookings/{id}/cancel` (cancel booking)
- Auth (admin): `GET /api/v1/admin/facility-bookings` (all bookings with filters)
- Auth (admin): `POST /api/v1/admin/facility-bookings/{id}/confirm` (confirm booking)
- Auth (admin): `POST /api/v1/admin/facility-bookings/{id}/cancel` (admin cancel)

---

### D4: Booking Status Lifecycle

**Decision**: Use a simplified status workflow:
```
pending → confirmed → completed
pending → cancelled
confirmed → cancelled
confirmed → no_show
```

**Rationale**: The TestOrder model has 7 statuses with complex transitions suited for lab test processing. Facility bookings are simpler — the patient shows up (or doesn't) and the appointment either happens or gets cancelled. The 5 statuses (pending, confirmed, cancelled, completed, no_show) cover all cases without over-engineering.

---

### D5: Double-Booking Prevention

**Decision**: Use a database unique constraint on `(facility_type, facility_id, booking_date, booking_time)` where status is not cancelled, plus an application-level check before insert.

**Rationale**: The unique constraint provides a race-condition-safe guarantee at the database level. The application check provides a user-friendly error message. Both are needed — the constraint catches edge cases the app check might miss under concurrent requests.

---

### D6: API Response Format

**Decision**: Follow the existing `sendResponse()` pattern and use Laravel API Resources (JsonResource) with `.additional(['status' => true])`.

**Rationale**: This matches every existing API endpoint in the project (LabSearchApiController, TestOrdersApiController, CallBookingApiController).

---

### D7: Notification Strategy

**Decision**: Use the existing `sendNotificationOnBookingUpdate()` helper wrapped in try-catch. Add two notification types: `new_facility_booking` and `facility_booking_status_changed`.

**Rationale**: The pattern is proven in CallBookingApiController and TestOrdersApiController. Wrapping in try-catch ensures booking operations succeed even if notification delivery fails.

---

### D8: Permission Structure

**Decision**: Add permissions for booking management:
- `view_facility_bookings` (admin: view all bookings)
- `manage_facility_bookings` (admin: confirm/cancel bookings)

Patient booking operations use `auth:sanctum` — any authenticated user can book.

**Rationale**: Follows the existing pattern. Patients don't need explicit permissions — authentication is sufficient. Admin operations need permission gating.
