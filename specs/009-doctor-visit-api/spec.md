# Feature Specification: Doctor Visit Request API

**Feature Branch**: `009-doctor-visit-api`
**Created**: 2026-04-02
**Status**: Draft
**Input**: User description: "create new api endpoints to allow user in mobile app have a doctor visit form and i need to track entities from form into admin dashboard"

## Clarifications

### Session 2026-04-05

- Q: Visit type — home visit only, clinic visit only, or patient-selectable? → A: Home visit only (doctor travels to patient).
- Q: Notification channel for patient status change alerts? → A: Email notification via existing Laravel Mail.
- Q: Reference number format for submitted visit requests? → A: Human-readable sequential, format `VR-{YYYY}-{NNNN}` (e.g., `VR-2026-0042`), year-scoped.
- Q: Which roles can perform status updates on visit requests? → A: Admin, receptionist, and assigned doctor.
- Q: Is cancellation reason required or optional when cancelling a request? → A: Optional (nullable field).

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Submit Doctor Visit Request (Priority: P1)

A patient using the mobile app wants to request a doctor visit. They fill in a structured form with their details, the reason for the visit, preferred doctor (optional), and desired date/time. Upon submission, the request is saved and the patient receives confirmation that their request is being processed.

**Why this priority**: This is the core feature — without the ability to submit a visit request, nothing else in the flow has value. It is the entry point for all downstream tracking.

**Independent Test**: Can be fully tested by submitting a visit request as an authenticated patient and verifying the request appears in the system with a "pending" status.

**Acceptance Scenarios**:

1. **Given** an authenticated patient on the mobile app, **When** they complete and submit the visit request form with valid data, **Then** a new visit request record is created with status "pending" and the patient receives a success response with the request reference number.
2. **Given** an authenticated patient, **When** they submit the form with missing required fields, **Then** the system rejects the submission with clear validation errors indicating which fields are missing.
3. **Given** an authenticated patient, **When** they submit the form with an invalid preferred date (past date), **Then** the system rejects the submission with a validation error on the date field.

---

### User Story 2 - View Own Visit Requests (Priority: P2)

A patient using the mobile app wants to view the history and current status of all their submitted doctor visit requests, so they know whether their request has been confirmed, is pending, or was cancelled.

**Why this priority**: Patients need visibility into their requests to avoid duplicate submissions and to plan accordingly. This is the immediate follow-up need after P1.

**Independent Test**: Can be fully tested by submitting a request (P1 done) then calling the listing endpoint and confirming the submitted request appears with correct status.

**Acceptance Scenarios**:

1. **Given** a patient who has submitted one or more visit requests, **When** they view their requests list, **Then** all their requests are returned ordered by most recent first, with status, date, and reference number.
2. **Given** a patient with no submitted requests, **When** they view their requests list, **Then** an empty list is returned with no error.
3. **Given** a patient, **When** they view a specific request by its reference number, **Then** the full details of that request are returned including current status and any admin notes.

---

### User Story 3 - Admin Views and Manages Visit Requests (Priority: P3)

An admin, receptionist, or assigned doctor uses the admin dashboard to view incoming doctor visit requests, see their details, filter by status or date, and update request statuses (confirm, cancel, complete) to manage the workflow.

**Why this priority**: Admin tracking is the secondary goal of the feature. The API must support it, but the submission flow (P1, P2) must be solid first.

**Independent Test**: Can be fully tested independently by seeding visit request records and verifying the admin listing, filtering, and status-update endpoints work correctly.

**Acceptance Scenarios**:

1. **Given** the admin dashboard, **When** a staff member views the visit requests list, **Then** all requests are displayed with patient name, preferred doctor, requested date, visit type, and current status.
2. **Given** the admin dashboard with requests in various statuses, **When** a staff member filters by status "pending", **Then** only pending requests are returned.
3. **Given** a pending visit request, **When** an admin updates its status to "confirmed", **Then** the request status changes to "confirmed" and the patient receives an email notification of the status change.
4. **Given** a confirmed visit request, **When** an admin marks it as "completed", **Then** the request status updates and a completion timestamp is recorded.
5. **Given** any visit request, **When** an admin cancels it (optionally providing a reason), **Then** the request status becomes "cancelled" and the cancellation reason (if provided) is stored.

---

### Edge Cases

- What happens when a patient submits a request for a date that is fully booked? System accepts the request and marks it pending for admin review (no automatic slot blocking at submission time).
- How does the system handle a patient submitting multiple requests for the same date? System accepts all requests; admin is responsible for managing conflicts.
- What if the preferred doctor is unavailable or inactive? The doctor field is optional; if a preferred doctor is specified but is inactive, the system should warn but still accept the request.
- How does the system handle concurrent status updates by two admin users on the same request? Last-write-wins with a conflict-safe status transition (e.g., cannot confirm an already-cancelled request).
- What if a patient's account is deactivated after submitting a request? The request remains in the system for admin tracking; no data is deleted.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow authenticated mobile app users to submit a doctor visit request form.
- **FR-002**: The visit request form MUST capture: patient contact number, visit reason/chief complaint, preferred visit date, visit type (fixed as **home visit** — doctor travels to patient; no clinic visit option), preferred doctor (optional), and any additional notes.
- **FR-003**: System MUST assign a "pending" status to newly submitted visit requests.
- **FR-004**: System MUST return a unique reference number for each submitted request, formatted as `VR-{YYYY}-{NNNN}` (e.g., `VR-2026-0042`), where `NNNN` is a zero-padded auto-incrementing integer scoped to the calendar year.
- **FR-005**: System MUST allow authenticated patients to retrieve a list of their own visit requests with status and summary details.
- **FR-006**: System MUST allow authenticated patients to retrieve the full details of a specific visit request by reference number.
- **FR-007**: System MUST allow admin users to retrieve a paginated list of all visit requests, with filtering by status, date range, and assigned doctor.
- **FR-008**: System MUST allow users with roles `admin`, `receptionist`, or `doctor` (when assigned to the request) to update the status of a visit request (pending → confirmed, confirmed → completed, any → cancelled).
- **FR-009**: System MUST allow users with roles `admin` or `receptionist` to assign or reassign a doctor to a visit request.
- **FR-010**: System MUST allow users with roles `admin`, `receptionist`, or `doctor` (when assigned to the request) to add internal notes to a visit request.
- **FR-011**: System MUST reject status transitions that are not valid (e.g., completed → confirmed).
- **FR-012**: System MUST record the timestamp and acting user for every status change.
- **FR-013**: System MUST send an email notification to the patient when their request status changes (confirmed, cancelled, or completed), using the existing Laravel Mail stack. No push or SMS integration required.

### Key Entities

- **DoctorVisitRequest**: Represents a patient's request for a doctor visit. Key attributes: reference number (format: `VR-{YYYY}-{NNNN}`, unique, year-scoped sequential), patient (user), preferred doctor (optional), visit type (fixed enum: `home_visit`), visit reason, preferred date, contact phone, additional notes, status (pending/confirmed/cancelled/completed), assigned doctor, cancellation reason (nullable — optional), completion timestamp.
- **DoctorVisitStatusHistory**: Tracks every status change on a visit request. Attributes: visit request reference, old status, new status, changed by (user), changed at (timestamp), note/reason.
- **Patient (User)**: The authenticated mobile app user submitting the request. Linked to the existing user model.
- **Doctor**: The healthcare professional assigned to the visit. Linked to the existing doctor model.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Patients can submit a doctor visit request in under 2 minutes from opening the form to receiving confirmation.
- **SC-002**: 100% of submitted requests appear in the admin dashboard within 5 seconds of submission.
- **SC-003**: Admin staff can locate any specific visit request by patient name, date, or status in under 30 seconds.
- **SC-004**: Status updates made by admin are reflected immediately in the patient's request view without manual refresh.
- **SC-005**: 0% data loss — every submitted visit request is persisted and recoverable even if the mobile app loses connectivity immediately after submission confirmation.
- **SC-006**: All form validation errors are presented to the patient in a single response (not one at a time), reducing resubmission attempts to at most 1 correction cycle.

## Assumptions

- Patients are already authenticated in the mobile app using the existing Sanctum-based authentication system; no new auth mechanism is needed.
- The existing `Doctor` model and user role system are used for doctor assignment; no new doctor entity is required.
- The admin dashboard will surface these requests via a new dedicated section (not embedded in an existing module).
- Preferred date is a date-only field (no time slot reservation at submission); time scheduling is handled by admin confirmation.
- The feature does not include payment processing at the request stage; billing (if any) is handled separately post-confirmation.
- Visit requests are not automatically assigned to a doctor slot; assignment is manual by admin staff.
- The mobile app consumes a REST API (existing pattern in this project); no GraphQL or websocket connections are introduced.
