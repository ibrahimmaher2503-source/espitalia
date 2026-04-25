# Feature Specification: Lab & Radiology Booking API

**Feature Branch**: `008-lab-radiology-booking-api`
**Created**: 2026-04-02
**Status**: Draft
**Input**: User description: "Make sure booking for both labs and radiology are sent for API and working well within our project business."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Patient Books a Lab Appointment (Priority: P1)

As a patient, I need to book an appointment at a lab facility through the mobile/web app so that I can visit at a scheduled time for my tests.

**Why this priority**: Lab booking is the core patient-facing workflow. Without it, patients can only browse labs but cannot reserve a time slot, making the search feature incomplete.

**Independent Test**: Can be tested by searching for a lab, selecting it, choosing a date and time, providing contact details, and confirming the booking. The booking should appear in the patient's "My Bookings" list and be retrievable via API.

**Acceptance Scenarios**:

1. **Given** a patient has found a lab via the search API, **When** they request available time slots for a specific date, **Then** the system returns a list of available slots for that lab on that date.
2. **Given** a patient selects an available time slot, **When** they submit the booking with their name, phone number, and optional notes, **Then** the booking is created with status "pending" and a confirmation is returned with the booking ID.
3. **Given** a patient has booked an appointment, **When** they view their bookings list, **Then** the lab booking appears with the lab name, date, time, and status.
4. **Given** a patient has a pending booking, **When** they cancel it before the appointment time, **Then** the booking status changes to "cancelled" and the time slot becomes available again.
5. **Given** a time slot is already booked, **When** another patient tries to book the same slot, **Then** the system rejects the booking with a clear error message.

---

### User Story 2 - Patient Books a Radiology Appointment (Priority: P1)

As a patient, I need to book an appointment at a radiology center for a specific scan type so that I can get my imaging done at a scheduled time.

**Why this priority**: Radiology booking is equally critical as lab booking. Radiology appointments include a scan type selection which distinguishes them from lab bookings.

**Independent Test**: Can be tested by searching for a radiology center, selecting a scan type, choosing a date/time slot, and confirming the booking.

**Acceptance Scenarios**:

1. **Given** a patient has found a radiology center via the search API, **When** they request available time slots for a specific date and scan type, **Then** the system returns available slots for that center on that date.
2. **Given** a patient selects an available time slot, **When** they submit the booking with scan type, name, phone, and optional notes, **Then** the booking is created with status "pending" and includes the scan type.
3. **Given** a patient has radiology bookings, **When** they view their bookings list, **Then** radiology bookings show the center name, scan type, date, time, and status.
4. **Given** a patient cancels a radiology booking, **When** the cancellation is processed, **Then** the slot is released and the booking is marked as "cancelled".

---

### User Story 3 - Admin Manages Booking Requests (Priority: P2)

As an admin, I need to view, confirm, and manage booking requests for labs and radiology centers so that I can coordinate patient appointments.

**Why this priority**: Admin oversight is needed for operational management, but patients can still book and view bookings without admin intervention.

**Independent Test**: Can be tested by logging in as admin, viewing the bookings list, confirming a pending booking, and verifying the patient sees the updated status.

**Acceptance Scenarios**:

1. **Given** an admin is logged in, **When** they view the bookings list, **Then** they see all lab and radiology bookings with patient details, facility name, date/time, and status.
2. **Given** an admin selects a pending booking, **When** they confirm it, **Then** the booking status changes to "confirmed" and the patient can see the updated status.
3. **Given** an admin needs to cancel a booking, **When** they cancel it with a reason, **Then** the booking is marked "cancelled" and the time slot is released.
4. **Given** the admin filters bookings by type (lab/radiology), status, or date range, **When** the filter is applied, **Then** only matching bookings are displayed.

---

### User Story 4 - Booking Status Notifications (Priority: P3)

As a patient, I need to receive status updates when my booking is confirmed or cancelled so that I know the outcome without checking repeatedly.

**Why this priority**: Notifications improve user experience but the booking system functions without them.

**Independent Test**: Can be tested by confirming a booking as admin and verifying a notification is generated for the patient.

**Acceptance Scenarios**:

1. **Given** a booking is confirmed by admin, **When** the status changes, **Then** the patient receives a notification with booking details.
2. **Given** a booking is cancelled (by admin or patient), **When** the status changes, **Then** the relevant parties receive a notification.

---

### Edge Cases

- What happens when a patient tries to book at a deactivated (inactive) lab or radiology center? The system should reject the booking with a clear error.
- What happens when a patient books the last available slot simultaneously with another patient? The system should handle the race condition — first confirmed booking wins, second gets an error.
- What happens when a lab or radiology center has no time slots configured? The available slots API should return an empty list, and the booking form should indicate no availability.
- What happens when a patient tries to book a past date? The system should reject it with a validation error.
- What happens when the patient is not authenticated? The booking endpoint should require authentication and return a 401 error.
- What happens when a booking is already confirmed and the patient tries to cancel? The system should allow cancellation of confirmed bookings (before the appointment time).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide an API endpoint to list available time slots for a specific lab on a given date.
- **FR-002**: System MUST provide an API endpoint to list available time slots for a specific radiology center on a given date, optionally filtered by scan type.
- **FR-003**: System MUST provide an API endpoint for authenticated patients to create a lab booking with: lab ID, date, time slot, patient name, phone number, and optional notes.
- **FR-004**: System MUST provide an API endpoint for authenticated patients to create a radiology booking with: radiology center ID, scan type, date, time slot, patient name, phone number, and optional notes.
- **FR-005**: System MUST provide an API endpoint for authenticated patients to list their own bookings (both lab and radiology), sorted by date descending.
- **FR-006**: System MUST provide an API endpoint for authenticated patients to cancel their own pending or confirmed bookings.
- **FR-007**: System MUST provide an API endpoint for authenticated patients to view a single booking's details.
- **FR-008**: System MUST prevent double-booking of the same time slot at the same facility.
- **FR-009**: System MUST validate that the selected date is not in the past and the selected time slot is available.
- **FR-010**: System MUST reject bookings for inactive (disabled) labs or radiology centers.
- **FR-011**: System MUST support booking statuses: pending, confirmed, cancelled, completed, no_show.
- **FR-012**: System MUST provide admin API endpoints to list all bookings with filters (type, status, date range, facility).
- **FR-013**: System MUST provide admin API endpoints to confirm or cancel bookings.
- **FR-014**: All API responses MUST return bilingual data (lab/center names) based on the request locale.
- **FR-015**: All validation error messages MUST be available in both Arabic and English.
- **FR-016**: Booking data MUST include: patient information, facility information, booking type (lab/radiology), date, time slot, status, scan type (for radiology), and notes.

### Key Entities

- **Lab Booking**: A patient's scheduled appointment at a laboratory. Links to a Lab, a patient (user), a date, a time slot, and has a status lifecycle.
- **Radiology Booking**: A patient's scheduled appointment at a radiology center for a specific scan type. Links to a RadiologyCenter, a patient, a date, a time slot, scan type, and has a status lifecycle.
- **Time Slot**: A configurable time window (e.g., 09:00-09:30) representing an available appointment slot at a facility. Each facility can have its own set of daily time slots.
- **Lab** (existing): The laboratory facility being booked.
- **Radiology Center** (existing): The radiology facility being booked.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Patients can complete a lab or radiology booking in under 2 minutes (search → select → book).
- **SC-002**: 100% of booking API endpoints return correct bilingual responses based on request locale.
- **SC-003**: Zero double-bookings occur — the system prevents any two patients from booking the same slot at the same facility.
- **SC-004**: Patients can view and cancel their own bookings with immediate status updates.
- **SC-005**: Admins can filter and manage all bookings within 3 seconds for datasets of 1000+ bookings.
- **SC-006**: All booking-related API endpoints follow the same response format as existing search APIs (status, message, data structure).
- **SC-007**: The booking system works independently — creating and retrieving bookings does not require any other module's business logic beyond the existing Lab and RadiologyCenter models.

## Assumptions

- The existing `Lab` and `RadiologyCenter` models and their search APIs remain unchanged and serve as the entry point for the booking flow.
- Time slots will use a simple configurable structure (e.g., facility operating hours with a fixed slot duration like 30 minutes) rather than per-technician availability.
- Authentication uses the existing Sanctum-based API authentication already in place for other API endpoints.
- Booking notifications (US4) will use the existing notification infrastructure if available, or be deferred to a follow-up feature.
- The admin management UI for bookings (web views with DataTables) is out of scope for this feature — only API endpoints are included. Admin UI can be added in a follow-up.
- The booking flow is facility-level (book at "Lab X"), not test-specific (not "book Blood Test at Lab X"). Test orders remain a separate workflow.
