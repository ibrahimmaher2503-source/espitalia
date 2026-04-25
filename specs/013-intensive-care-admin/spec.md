# Feature Specification: Intensive Care Admin Platform

**Feature Branch**: `013-intensive-care-admin`
**Created**: 2026-04-05
**Status**: Draft
**Input**: User description: "Intensive Care in admin ui and endpoints also in db i need all details in entities be in both lang ar and en and update api docs i need also add options for locations cities and gover like existing AND ADD ADMIN PLATFORM COMMISSIONS AND EARNING"

**Depends On**: `003-intensive-care` (base data model: Hospital, IcuDepartment, IcuAdmissionRequest)

---

## Context

The `003-intensive-care` spec defines the core data model (Hospital, IcuDepartment, IcuAdmissionRequest) and frontend patient-facing flows. This spec extends that foundation with:

1. **Full bilingual support** (Arabic/English) for all ICU entity text fields
2. **Admin backend UI** — CRUD for hospitals, departments, and admission request management
3. **REST API endpoints** with documented contracts
4. **Location integration** — Governorate/City selection for hospitals (same pattern as Nurse/Location modules)
5. **Platform commission & earnings system** — the platform takes a configurable cut from ICU admission payments

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Admin Manages Hospitals with Bilingual Details (Priority: P1)

An admin creates and manages hospital records with all text fields in both Arabic and English. The admin selects the hospital's governorate and city from existing location data. The system displays the correct language based on the current locale, with fallback to the other language if the active locale's field is empty.

**Why this priority**: Hospitals are the root entity for the entire ICU workflow. Without bilingual hospital management, no other ICU feature can function in a dual-language environment.

**Independent Test**: Admin can create a hospital with Arabic and English names, select a governorate/city, view it in both locales, edit it, and delete it — all through the admin panel.

**Acceptance Scenarios**:

1. **Given** the admin is on the hospital create form, **When** they fill in `name_ar`, `name_en`, `description_ar`, `description_en`, select a governorate, select a city filtered by that governorate, and submit, **Then** the hospital is saved with all bilingual fields and location references.
2. **Given** a hospital exists with both Arabic and English names, **When** the admin switches the application locale to Arabic, **Then** the hospital name displays in Arabic throughout the admin panel.
3. **Given** a hospital has `name_ar` filled but `name_en` empty, **When** the system displays the hospital in English locale, **Then** it falls back to display the Arabic name.
4. **Given** the admin selects a governorate in the hospital form, **When** the governorate changes, **Then** the city dropdown refreshes to show only cities belonging to that governorate.
5. **Given** multiple hospitals exist, **When** the admin views the hospitals index, **Then** they see a DataTable with columns for name (AR), name (EN), governorate, city, type, status, and actions (edit/delete/toggle status).

---

### User Story 2 — Admin Manages ICU Departments with Bilingual Details (Priority: P1)

An admin creates and manages ICU departments within hospitals. Department names and descriptions are stored in both Arabic and English.

**Why this priority**: ICU departments are required for bed search and admission requests; they must support bilingual content to match the hospital entity.

**Independent Test**: Admin can create an ICU department under a hospital with bilingual name/description, set specialty, beds, equipment, and daily price.

**Acceptance Scenarios**:

1. **Given** the admin is on the ICU department create form, **When** they select a hospital, fill in `name_ar`, `name_en`, `description_ar`, `description_en`, set specialty type, bed counts, equipment flags, and daily price, **Then** the department is saved correctly.
2. **Given** departments exist under a hospital, **When** the admin views the departments index, **Then** they can filter by hospital and see bilingual names, specialty, available beds, and daily price.
3. **Given** a department exists, **When** the admin updates the available beds count via the inline update action, **Then** the available beds are updated without a full page reload.

---

### User Story 3 — Admin Manages ICU Admission Requests (Priority: P1)

An admin or hospital coordinator views, responds to, and manages ICU admission requests. The admin can accept, reject, or request additional information from the patient's family.

**Why this priority**: Admission request management is the core workflow that delivers immediate value — hospitals need to process incoming ICU requests.

**Independent Test**: Admin can view a list of admission requests, open a request's detail page, respond with accept/reject/info-needed, and see the status update reflected.

**Acceptance Scenarios**:

1. **Given** admission requests exist, **When** the admin views the requests index, **Then** they see a DataTable with request number, patient name, hospital, department, urgency, status, and date.
2. **Given** a pending admission request, **When** the admin opens the detail view and clicks "Accept", **Then** the status changes to "accepted" and the patient receives a notification.
3. **Given** a pending admission request, **When** the admin clicks "Reject" and enters a rejection reason, **Then** the status changes to "rejected" with the reason recorded.
4. **Given** a pending admission request, **When** the admin clicks "Request Info" and enters notes, **Then** the status changes to "info_requested" and the patient is notified to provide additional information.
5. **Given** a hospital coordinator is logged in, **When** they view admission requests, **Then** they only see requests for their assigned hospital.

---

### User Story 4 — Platform Commission on ICU Admissions (Priority: P2)

The platform administrator configures commission templates that define the platform's percentage or flat-rate cut from ICU admission payments. When an admission is accepted and completed, the system calculates the commission earning, splitting the total into platform commission and hospital receivable.

**Why this priority**: Revenue sharing is essential for platform sustainability but can be added after the core CRUD and workflow are functional.

**Independent Test**: Admin can create a commission template, assign it to a hospital, and when an admission is completed the system generates an earning record with the correct split.

**Acceptance Scenarios**:

1. **Given** the admin is on the ICU commission management page, **When** they create a new commission template with title, type (percentage or flat), and value, **Then** the template is saved and appears in the list.
2. **Given** a commission template exists, **When** the admin assigns it to a hospital, **Then** the hospital's commission reference is updated.
3. **Given** a hospital has a percentage commission of 15% and an admission with total amount 1000, **When** the admission status changes to "accepted", **Then** the system creates an earning record: commission_amount = 150, hospital_receivable = 850.
4. **Given** a hospital has a flat commission of 50 and an admission total of 500, **When** the admission is completed, **Then** commission_amount = 50, hospital_receivable = 450.
5. **Given** multiple earning records are unpaid for a hospital, **When** the admin creates a payout, **Then** the selected earnings are marked as "paid" and linked to the payout record.

---

### User Story 5 — REST API for ICU Entities (Priority: P2)

Mobile and third-party clients consume ICU data via REST API endpoints. The API returns bilingual fields using locale-aware accessors and supports filtering, pagination, and location-based queries.

**Why this priority**: The API enables mobile app integration and external system access but depends on the admin UI being functional first.

**Independent Test**: API consumers can list hospitals with filters (governorate, city, specialty), view hospital details with departments, list/create/cancel admission requests, and receive locale-aware responses.

**Acceptance Scenarios**:

1. **Given** an authenticated API user, **When** they GET `/api/v1/hospitals?governorate_id=5&specialty=cardiac`, **Then** they receive a paginated list of hospitals in that governorate with cardiac ICU departments, with names in the request's locale.
2. **Given** an authenticated API user, **When** they GET `/api/v1/hospitals/{id}`, **Then** they receive hospital details including nested departments, location data (governorate/city), and commission info (if admin).
3. **Given** an authenticated API user, **When** they POST `/api/v1/icu-admission-requests` with valid patient data, **Then** an admission request is created and the auto-generated request number is returned.
4. **Given** an authenticated API user, **When** they GET `/api/v1/icu-admission-requests` with locale header `Accept-Language: ar`, **Then** all text fields are returned in Arabic.
5. **Given** an admin API user, **When** they GET `/api/v1/icu-commissions`, **Then** they receive the list of commission templates with associated earning summaries.

---

### User Story 6 — Earnings Dashboard and Payout Management (Priority: P3)

The admin views a consolidated earnings dashboard showing total commissions collected, pending payouts, and completed payouts across all hospitals. Hospital coordinators see their own hospital's earnings.

**Why this priority**: Reporting and payout management build on top of the commission system and can be delivered after the commission calculation logic is stable.

**Independent Test**: Admin can view the earnings dashboard with aggregate metrics, filter by hospital/date range, and process payouts.

**Acceptance Scenarios**:

1. **Given** earning records exist across multiple hospitals, **When** the admin opens the earnings dashboard, **Then** they see total commission earned, total pending payouts, and total paid out.
2. **Given** unpaid earnings exist for a hospital, **When** the admin initiates a payout with payment method and date, **Then** a payout record is created and linked earnings are marked as paid.
3. **Given** a hospital coordinator is logged in, **When** they view the earnings page, **Then** they only see earnings and payouts for their assigned hospital.

---

### Edge Cases

- What happens when a hospital has no commission template assigned and an admission is accepted? The system should skip earning generation and log a warning; the admin is notified to assign a commission.
- What happens when the commission value results in a negative hospital receivable (flat fee exceeds admission total)? The system should reject the earning calculation and alert the admin that the commission exceeds the admission amount.
- What happens when both Arabic and English fields are empty for a required bilingual field? Validation should require at least one language to be filled.
- What happens when a governorate is deleted but hospitals still reference it? The governorate foreign key should be nullable; orphaned references display "—" in the UI.
- What happens when a payout is created but some selected earnings have already been paid? The system should exclude already-paid earnings and only process unpaid ones.
- What happens when two admins simultaneously respond to the same admission request? The system should use optimistic locking (check current status before transition) to prevent conflicting state changes.

---

## Requirements *(mandatory)*

### Functional Requirements

**Bilingual Entity Management**

- **FR-001**: System MUST store all text fields for Hospital, IcuDepartment, and IcuAdmissionRequest in both Arabic (`_ar`) and English (`_en`) columns.
- **FR-002**: System MUST provide locale-aware accessors that return the field in the current application locale, with fallback to the other language if the active locale's value is empty.
- **FR-003**: Admin forms MUST display side-by-side Arabic (RTL) and English (LTR) inputs for all bilingual fields.
- **FR-004**: Validation MUST require at least one language to be filled for required bilingual fields (e.g., hospital name).

**Hospital Admin CRUD**

- **FR-005**: System MUST provide a full CRUD interface for hospitals with DataTable listing, create/edit form, status toggle, and bulk delete.
- **FR-006**: Hospital create/edit form MUST include bilingual fields (name, description, address), hospital type selector, phone, email, website, license number, accepted insurance (multi-select), and media upload (logo, gallery).
- **FR-007**: Hospital form MUST include cascading governorate and city dropdowns that filter cities by the selected governorate.
- **FR-008**: Hospital index DataTable MUST display name (AR/EN), governorate, city, type, status, and action columns.

**ICU Department Admin CRUD**

- **FR-009**: System MUST provide CRUD for ICU departments with hospital filter, bilingual name/description, specialty type, bed counts, equipment flags, and daily price.
- **FR-010**: System MUST support inline bed count update (available beds) without full page reload.

**Admission Request Management**

- **FR-011**: System MUST provide a list view of admission requests with filtering by status, hospital, urgency, and date range.
- **FR-012**: System MUST provide a detail view for each admission request showing all patient, case, contact, and workflow information.
- **FR-013**: System MUST allow admins to respond to pending requests with: accept, reject (with reason), or request additional information (with notes).
- **FR-014**: System MUST enforce status transition rules as defined in the IcuAdmissionRequest model (pending → accepted/rejected/info_requested/cancelled).
- **FR-015**: Hospital coordinators MUST only see and manage requests for their assigned hospital.

**Location Integration**

- **FR-016**: Hospital entity MUST reference `governorate_id` (FK to governorates table) and `city_id` (FK to governorate_cities table), both nullable.
- **FR-017**: API endpoints MUST support filtering hospitals by `governorate_id` and `city_id`.

**Commission & Earnings**

- **FR-018**: System MUST provide CRUD for ICU commission templates with fields: title, commission type (percentage or flat), commission value, and status.
- **FR-019**: System MUST allow assigning a commission template to each hospital via a `commission_id` foreign key on the hospitals table.
- **FR-020**: System MUST automatically calculate and record an earning when an ICU admission status changes to "accepted": splitting the admission total into `commission_amount` (platform's cut) and `hospital_receivable` (hospital's share).
- **FR-021**: Earning records MUST snapshot the commission type and value at the time of calculation to preserve historical accuracy.
- **FR-022**: System MUST provide payout management: admin selects unpaid earnings for a hospital, enters payment method and date, and the system creates a payout record linking those earnings.
- **FR-023**: System MUST provide an earnings dashboard showing aggregate metrics (total earned, pending payouts, completed payouts) filterable by hospital and date range.

**REST API**

- **FR-024**: System MUST provide RESTful API endpoints for: listing/showing hospitals, listing ICU departments, listing/creating/showing/cancelling admission requests.
- **FR-025**: API responses MUST use locale-aware accessors, returning text in the language specified by the `Accept-Language` header.
- **FR-026**: API responses MUST include nested location data (governorate name, city name) in hospital resources.
- **FR-027**: Admin-only API endpoints MUST be provided for commission templates and earning summaries.

**Sidebar & Navigation**

- **FR-028**: Admin sidebar MUST include an "Intensive Care" section with menu items: Admission Requests, Hospitals, ICU Departments, ICU Commissions, and Earnings.
- **FR-029**: Sidebar items MUST be visible based on role and permissions (admin sees all; hospital coordinators see requests, departments, and their earnings).

### Key Entities

- **Hospital**: Represents a healthcare facility with ICU capabilities. Key attributes: bilingual name/description/address, hospital type, location (governorate/city reference), contact info, accepted insurance list, rating, media (logo/gallery), commission template reference, vendor/coordinator assignment.
- **IcuDepartment**: An ICU ward within a hospital. Key attributes: bilingual name/description, specialty type, total and available bed counts, equipment flags (ventilator, oxygen), equipment level, daily price.
- **IcuAdmissionRequest**: A patient's request for ICU admission at a specific hospital. Key attributes: auto-generated request number, patient and case details, urgency level, status workflow (pending → accepted/rejected/info_requested/cancelled), contact info, admin response notes.
- **IcuCommission**: A reusable commission template. Key attributes: title, commission type (percentage/flat), commission value, status. One commission template can be assigned to many hospitals.
- **IcuCommissionEarning**: A record of commission earned on a specific admission. Key attributes: admission request reference, hospital reference, commission snapshot (type/value at time of calculation), order subtotal, commission amount (platform cut), hospital receivable, payment status (unpaid/paid), payout reference.
- **IcuPayout**: A batch payment record for a hospital. Key attributes: hospital reference, total amount, total commission deducted, payment method, payment date, status. Links to multiple earning records.

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admins can create a bilingual hospital record with location selection in under 3 minutes.
- **SC-002**: All ICU entity text fields display correctly in both Arabic and English when the user switches locale, with zero missing translations in the admin interface.
- **SC-003**: Hospital coordinators see only their hospital's data across all admin pages — zero cross-hospital data leakage.
- **SC-004**: Admission request response (accept/reject/info request) is completed in under 30 seconds from the request detail page.
- **SC-005**: Commission earnings are automatically calculated with 100% accuracy on every admission status change to "accepted", with correct percentage/flat splits.
- **SC-006**: The earnings dashboard loads aggregate metrics across all hospitals within 2 seconds for up to 10,000 earning records.
- **SC-007**: All API endpoints return correct paginated responses with locale-aware content and location data, achieving 100% parity with the admin UI data.
- **SC-008**: Payout processing correctly marks all selected earnings as paid and prevents double-payment of already-paid earnings.

---

## Assumptions

- The `003-intensive-care` base data model (Hospital, IcuDepartment, IcuAdmissionRequest) is implemented or will be implemented as part of this work. This spec extends those entities with bilingual columns, location FKs, and commission FKs.
- The Location module (Governorate, GovernorateCity) is already implemented and available for reference.
- The bilingual pattern follows the established convention: `field_ar`/`field_en` columns with a `getFieldAttribute()` accessor providing locale-aware fallback (same as Nurse module in `012-nurse-admin-bilingual`).
- The commission/earnings pattern follows the Pharmacy commission model from `011-pharmacy-commissions`.
- The `hospital_coordinator` role defined in `003-intensive-care` is the role used for hospital-scoped access control.
- ICU admission payment amounts come from the `daily_price` on the IcuDepartment (the total is calculated based on the department's rate at the time of acceptance).
- API authentication uses Sanctum tokens, consistent with the rest of the application.
