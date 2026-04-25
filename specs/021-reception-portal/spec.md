# Feature Specification: Reception Portal Enhancement

**Feature Branch**: `021-reception-portal`
**Created**: 2026-04-16
**Status**: Draft
**Input**: User description: "update reception portal based on latest updates"

## Overview

Enhance the receptionist portal to integrate newly added platform modules and provide receptionists with comprehensive tools to manage daily clinic operations. The system has recently added Branch management, Doctor Visit (home visits), Call Booking, Independent Booking, Diagnostics (Lab Tests), and Intensive Care modules. Receptionists should have appropriate access to these features based on their operational needs at the clinic front desk.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Doctor Visit Request Management (Priority: P1)

As a receptionist, I need to view, manage, and track doctor visit (home visit) requests so that I can coordinate home visits for patients who cannot come to the clinic.

**Why this priority**: Doctor visits represent urgent patient needs that require immediate coordination. Receptionists are the first point of contact for scheduling and must be able to manage these requests in real-time.

**Independent Test**: Can be fully tested by creating a doctor visit request from the customer portal, then verifying the receptionist can view, assign doctors, and update status through their dashboard.

**Acceptance Scenarios**:

1. **Given** a receptionist is logged in, **When** they navigate to Doctor Visits menu, **Then** they see a list of all doctor visit requests for their assigned clinic(s) with filters for status, date range, and assigned doctor.

2. **Given** a new doctor visit request is submitted, **When** the receptionist views the requests list, **Then** the new request appears with "pending" status and shows patient name, requested date/time, address, and reason for visit.

3. **Given** a pending doctor visit request, **When** the receptionist assigns it to an available doctor, **Then** the request status updates to "assigned" and the selected doctor is notified.

4. **Given** a doctor visit request, **When** the receptionist updates the status (confirmed, in-progress, completed, cancelled), **Then** the status change is saved with timestamp and the patient receives a notification.

---

### User Story 2 - Enhanced Dashboard with New Module Metrics (Priority: P1)

As a receptionist, I need my dashboard to show consolidated metrics including doctor visits, test orders, and other new services so that I can monitor all clinic activities at a glance.

**Why this priority**: The dashboard is the first screen receptionists see and drives their daily workflow prioritization. Including new module metrics ensures nothing is missed.

**Independent Test**: Can be fully tested by verifying dashboard renders correctly with counts for appointments, doctor visits, test orders (if accessible), and displays today's schedule with all service types.

**Acceptance Scenarios**:

1. **Given** a receptionist is logged in, **When** the dashboard loads, **Then** it displays stat cards for: Total Appointments, Total Patients, Assigned Doctors, and Pending Doctor Visits.

2. **Given** there are doctor visits scheduled for today, **When** viewing the dashboard calendar, **Then** doctor visit events appear alongside regular appointments with a distinct color/icon.

3. **Given** multiple service types exist, **When** viewing the dashboard, **Then** quick action buttons are available for: New Appointment, New Doctor Visit Request, and View Today's Schedule.

---

### User Story 3 - Lab Test Order Visibility (Priority: P2)

As a receptionist, I need to view lab test orders placed by patients through the clinic so that I can assist with scheduling and patient inquiries about their test status.

**Why this priority**: Receptionists frequently receive patient calls about test order status and need read-only visibility to provide accurate information without accessing the full lab technician interface.

**Independent Test**: Can be fully tested by placing a test order and verifying the receptionist can view order details and status but cannot modify results.

**Acceptance Scenarios**:

1. **Given** a receptionist has test order view permission, **When** they access the Test Orders section, **Then** they see orders associated with their clinic(s) with patient name, tests ordered, order date, and current status.

2. **Given** a patient calls about their test order, **When** the receptionist searches by patient name or order ID, **Then** they can view the order details and current processing status.

3. **Given** a test order, **When** the receptionist views details, **Then** they can see scheduled collection date/time but cannot modify test results (read-only access).

---

### User Story 4 - ICU Admission Request Monitoring (Priority: P2)

As a receptionist, I need visibility into ICU admission requests related to our clinic's referrals so that I can follow up with patients and coordinate with hospitals.

**Why this priority**: ICU referrals are critical cases that patients may inquire about. Receptionists need status visibility to provide updates without accessing full hospital coordinator features.

**Independent Test**: Can be fully tested by creating an ICU request associated with a clinic patient and verifying the receptionist can view status and patient details.

**Acceptance Scenarios**:

1. **Given** a receptionist has ICU request view permission, **When** they access ICU Requests, **Then** they see requests from patients registered at their clinic(s).

2. **Given** an ICU admission request, **When** the receptionist views details, **Then** they can see patient info, request status, assigned hospital, and any updates but cannot modify hospital assignments.

---

### User Story 5 - Quick Patient Registration (Priority: P2)

As a receptionist, I need a streamlined patient registration form accessible from the dashboard so that I can quickly register walk-in patients.

**Why this priority**: Walk-in patients need quick registration before being seen. A dedicated quick action reduces friction in the workflow.

**Independent Test**: Can be fully tested by using the quick registration button from the dashboard and verifying a new patient is created with minimal required fields.

**Acceptance Scenarios**:

1. **Given** a receptionist is on the dashboard, **When** they click "New Patient", **Then** a quick registration form appears with required fields: name, phone, email (optional), gender, and date of birth.

2. **Given** a quick registration is completed, **When** the form is submitted, **Then** the patient is created and the receptionist can immediately proceed to book an appointment for them.

---

### User Story 6 - Call Booking Visibility (Priority: P3)

As a receptionist, I need to see video/audio call bookings scheduled with doctors so that I can assist patients with call-related inquiries.

**Why this priority**: Call bookings are a newer feature, and patients may call the clinic for assistance. Read-only visibility helps receptionists support patients.

**Independent Test**: Can be fully tested by booking a call consultation and verifying the receptionist can view the booking in their appointment calendar.

**Acceptance Scenarios**:

1. **Given** call bookings exist for the clinic's doctors, **When** the receptionist views the appointment calendar, **Then** call bookings appear with a distinct "Call" indicator.

2. **Given** a call booking, **When** the receptionist views details, **Then** they can see patient info, scheduled time, call type (video/audio), and status.

---

### Edge Cases

- What happens when a receptionist is assigned to multiple clinics? They should see consolidated data from all assigned clinics with clinic filters available.
- How does the system handle a receptionist viewing data for a clinic they were just unassigned from? Access is immediately revoked upon unassignment.
- What happens when new modules are disabled? Menu items and dashboard widgets for disabled modules should be hidden automatically.
- How does the system handle concurrent updates to doctor visit status? Last-write-wins with optimistic locking; users see a notification if their view was stale.

## Requirements *(mandatory)*

### Functional Requirements

#### Menu & Navigation
- **FR-001**: System MUST add "Doctor Visits" menu item for receptionists with `view_doctor_visit` permission
- **FR-002**: System MUST add "Test Orders" menu item for receptionists with `view_test_orders` permission (read-only access)
- **FR-003**: System MUST add "ICU Requests" menu item for receptionists with `view_icu_admission_requests` permission (read-only access)
- **FR-004**: System MUST show/hide menu items based on module enabled status in `modules_statuses.json`

#### Dashboard Enhancements
- **FR-005**: System MUST display "Pending Doctor Visits" count in the receptionist dashboard stat cards
- **FR-006**: System MUST display "Today's Test Orders" count if receptionist has test order view permission
- **FR-007**: System MUST display doctor visit events in the calendar alongside appointments with a distinct color (orange recommended)
- **FR-008**: System MUST provide quick action buttons for New Appointment, New Doctor Visit, and New Patient

#### Doctor Visit Management
- **FR-009**: System MUST allow receptionist to view doctor visit requests filtered by their assigned clinic(s)
- **FR-010**: System MUST allow receptionist to update doctor visit request status (pending, confirmed, in-progress, completed, cancelled)
- **FR-011**: System MUST allow receptionist to assign available doctors to doctor visit requests
- **FR-012**: System MUST log all status changes with timestamp and user who made the change

#### Read-Only Access Modules
- **FR-013**: System MUST provide read-only access to test orders for receptionists (view status, no result modification)
- **FR-014**: System MUST provide read-only access to ICU admission requests for receptionists (view status, no hospital assignment)
- **FR-015**: System MUST filter test orders and ICU requests by the receptionist's assigned clinic(s)

#### Permissions
- **FR-016**: System MUST create `view_doctor_visit` permission for receptionist role
- **FR-017**: System MUST create `view_test_orders_readonly` permission for receptionist role
- **FR-018**: System MUST create `view_icu_requests_readonly` permission for receptionist role
- **FR-019**: System MUST update receptionist role seeder to include new permissions by default

### Key Entities

- **Receptionist**: User with receptionist role, assigned to one or more clinics via `clinic_receptionists` pivot table. Key attributes: user_id, clinic_id, is_active.
- **DoctorVisitRequest**: A home visit request from a patient. Key attributes: patient_id, doctor_id, clinic_id, requested_datetime, status, address, reason, notes. Relationships: belongs to Patient, Doctor, and Clinic.
- **TestOrder**: A lab/radiology test order. Receptionist has read-only visibility filtered by clinic. Key attributes: patient_id, clinic_id, order_date, status.
- **IcuAdmissionRequest**: An ICU admission request. Receptionist has read-only visibility filtered by referring clinic. Key attributes: patient_id, referring_clinic_id, hospital_id, status.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Receptionists can view and manage doctor visit requests within 3 clicks from login
- **SC-002**: Dashboard loads with all stat cards and calendar within 2 seconds
- **SC-003**: 100% of doctor visit status changes are logged with audit trail
- **SC-004**: Test order and ICU request read-only views enforce no-edit restrictions via both UI and backend authorization
- **SC-005**: Menu items correctly show/hide based on module status and user permissions
- **SC-006**: Quick actions reduce new patient + appointment booking time compared to navigating through menus

## Assumptions

- The receptionist role already exists with base permissions for appointments, encounters, patients, and doctors
- Doctor Visit module is enabled and has basic CRUD operations implemented
- TestOrder and IcuAdmissionRequest models have `clinic_id` or equivalent relationship for filtering
- The `modules_statuses.json` configuration correctly reflects which modules are enabled/disabled
- Calendar integration uses FullCalendar library (already in use for receptionist dashboard)

## Out of Scope

- Creating new doctor visit requests (patients submit these via customer portal)
- Modifying lab test results (lab technician responsibility)
- Hospital assignment for ICU requests (hospital coordinator responsibility)
- Billing or payment processing for any services
- Real-time notifications (would require WebSocket infrastructure)
- Mobile-specific receptionist interface
