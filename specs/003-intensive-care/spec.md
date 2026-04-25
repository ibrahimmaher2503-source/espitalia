# IntensiveCare Module - MVP Design Spec

**Date:** 2026-03-22
**Branch:** 003-intensive-care
**Status:** Draft
**Scope:** ICU Bed Search + Emergency Admission Request

---

## 1. Overview

The IntensiveCare module transforms the app from a doctor booking platform into a critical care access and coordination platform. The MVP delivers two core capabilities:

1. **ICU Bed Search** — Patients/families search for available ICU beds across hospitals, filtering by specialty, equipment, location, price, and insurance.
2. **Emergency Admission Request** — Submit a structured admission request to a hospital, then track its status through acceptance or rejection.

The primary user is the **patient's family** accessing the public frontend. Hospital staff manage their side through the admin backend via a new `hospital_coordinator` role.

---

## 2. Data Models

### 2.1 Hospital

A new top-level entity, separate from the existing Clinic model. Hospitals have ICU-specific attributes that don't belong on a general clinic record.

```
hospitals
├── id (PK)
├── name (string, translatable)
├── slug (string, unique, auto-generated via HasSlug)
├── description (text, translatable, nullable)
├── hospital_type (string: government, private, military, university)
├── address (string)
├── city (string)
├── state (string, nullable)
├── country (string, nullable)
├── postal_code (string, nullable)
├── latitude (decimal 10,8, nullable)
├── longitude (decimal 11,8, nullable)
├── phone (string)
├── email (string, nullable)
├── website (string, nullable)
├── license_number (string, nullable)
├── accepted_insurance (JSON, nullable) — array of insurance provider names
├── rating (decimal 3,2, default 0)
├── vendor_id (FK → users, nullable) — hospital owner/manager
├── status (tinyint, default 1)
├── created_by, updated_by, deleted_by
├── timestamps, softDeletes
```

**Traits:** HasFactory, HasSlug, HasTranslations, InteractsWithMedia
**Translatable:** name, description
**Media collections:** logo (single), gallery (multiple)
**Relationships:**
- `hasMany(IcuDepartment)`
- `hasMany(IcuAdmissionRequest)`
- `belongsTo(User, 'vendor_id')` — hospital manager

**Table:** `protected $table = 'hospitals';`

**Casts:**
```php
protected $casts = [
    'accepted_insurance' => 'array',
    'latitude' => 'decimal:8',
    'longitude' => 'decimal:8',
    'rating' => 'decimal:2',
    'status' => 'boolean',
];
```

**Scopes:** `active()`, `inCity($city)`, `hasInsurance($provider)`

### 2.2 IcuDepartment

Each hospital has one or more ICU departments with different specialties and equipment levels.

```
icu_departments
├── id (PK)
├── hospital_id (FK → hospitals, cascade on delete)
├── name (string, translatable)
├── specialty_type (string: cardiac, neurology, pediatric, neonatal, burns, chest, surgical, general)
├── total_beds (integer, default 0)
├── available_beds (integer, default 0)
├── has_ventilator (boolean, default false)
├── has_oxygen (boolean, default true)
├── equipment_level (string: basic, advanced, full)
├── daily_price (decimal 8,2, default 0)
├── description (text, translatable, nullable)
├── status (tinyint, default 1)
├── created_by, updated_by, deleted_by
├── timestamps, softDeletes
```

**Traits:** HasFactory, HasTranslations
**Translatable:** name, description
**Relationships:**
- `belongsTo(Hospital)`
- `hasMany(IcuAdmissionRequest)`

**Table:** `protected $table = 'icu_departments';`

**Casts:**
```php
protected $casts = [
    'has_ventilator' => 'boolean',
    'has_oxygen' => 'boolean',
    'daily_price' => 'decimal:2',
    'total_beds' => 'integer',
    'available_beds' => 'integer',
    'status' => 'boolean',
];
```

**Scopes:** `active()`, `available()` (available_beds > 0), `withVentilator()`, `withOxygen()`, `bySpecialty($type)`

**Constants:**
```php
const SPECIALTY_TYPES = ['cardiac', 'neurology', 'pediatric', 'neonatal', 'burns', 'chest', 'surgical', 'general'];
const EQUIPMENT_LEVELS = ['basic', 'advanced', 'full'];
```

### 2.3 IcuAdmissionRequest

The core workflow entity. Follows the same pattern as RequestNurse and RadiologyLab TestOrder.

```
icu_admission_requests
├── id (PK)
├── request_number (string, unique) — auto-generated ICU-YYYY-XXXX
├── patient_id (FK → users, restrict)
├── hospital_id (FK → hospitals, restrict)
├── icu_department_id (FK → icu_departments, nullable, set null)
│
│── # Patient Info
├── patient_name (string)
├── patient_age (integer)
├── patient_gender (string: male, female)
├── national_id (string, nullable)
├── insurance_number (string, nullable)
├── medical_condition (text)
├── diagnosis (string, nullable)
│
│── # Case Details
├── case_type (string: stroke, cardiac, post_operative, ventilator, neonatal, pediatric, burns, general)
├── urgency (string: critical, urgent, standard — default standard)
├── needs_ventilator (boolean, default false)
├── needs_oxygen (boolean, default false)
├── current_location (string, nullable) — where patient is now
├── needs_ambulance (boolean, default false)
│
│── # Contact
├── contact_name (string)
├── contact_phone (string)
├── relationship_to_patient (string, nullable)
├── payment_method (string: insurance, cash — default cash)
├── insurance_provider (string, nullable)
│
│── # Workflow
├── status (string, default pending)
├── admin_notes (text, nullable)
├── rejection_reason (text, nullable)
├── response_date (datetime, nullable)
├── responded_by (FK → users, nullable, set null)
├── cancelled_by (string, nullable)
├── cancellation_reason (text, nullable)
│
├── created_by, updated_by, deleted_by
├── timestamps, softDeletes
```

**Traits:** HasFactory, InteractsWithMedia
**Media collections:** medical_reports (multiple files)
**Relationships:**
- `belongsTo(User, 'patient_id')`
- `belongsTo(Hospital)`
- `belongsTo(IcuDepartment)`
- `belongsTo(User, 'responded_by')`

**Constants:**
```php
const STATUS_PENDING = 'pending';
const STATUS_ACCEPTED = 'accepted';
const STATUS_REJECTED = 'rejected';
const STATUS_INFO_REQUESTED = 'info_requested';
const STATUS_CANCELLED = 'cancelled';

const ALLOWED_TRANSITIONS = [
    'pending'        => ['accepted', 'rejected', 'info_requested', 'cancelled'],
    'info_requested' => ['pending', 'cancelled'],  // patient responds → back to pending
    'accepted'       => ['cancelled'],
    'rejected'       => [],  // terminal
    'cancelled'      => [],  // terminal
];

const CASE_TYPES = ['stroke', 'cardiac', 'post_operative', 'ventilator', 'neonatal', 'pediatric', 'burns', 'general'];
const URGENCY_LEVELS = ['critical', 'urgent', 'standard'];
const PAYMENT_METHODS = ['insurance', 'cash'];
const GENDER_OPTIONS = ['male', 'female'];

const STATUSES = [
    self::STATUS_PENDING,
    self::STATUS_ACCEPTED,
    self::STATUS_REJECTED,
    self::STATUS_INFO_REQUESTED,
    self::STATUS_CANCELLED,
];
```

**Table:** `protected $table = 'icu_admission_requests';`

**Casts:**
```php
protected $casts = [
    'needs_ventilator' => 'boolean',
    'needs_oxygen' => 'boolean',
    'needs_ambulance' => 'boolean',
    'response_date' => 'datetime',
    'patient_age' => 'integer',
];
```

**Factory method:** All three models must define `newFactory()` returning the factory from `Modules\IntensiveCare\Database\Factories\`.

**Auto-numbering:** Same pattern as TestOrder — `ICU-YYYY-XXXX` with `lockForUpdate()` and `withTrashed()` in boot().

**Method:** `canTransitionTo($newStatus)` — validates against ALLOWED_TRANSITIONS.

**Scopes:** `byPatient($id)`, `byHospital($id)`, `byStatus($status)`, `pending()`, `urgent()`

**Accessors:** `status_badge`, `urgency_badge`, `payment_badge`, `case_type_label`

---

## 3. Roles & Permissions

### 3.1 New Role: hospital_coordinator

Created via `IntensiveCarePermissionSeeder`. This role manages a single hospital's ICU operations.

**Dashboard redirect:** `backend.icu-admission-requests.index` (same pattern as lab_technician → test-orders)

### 3.2 Permissions

| Permission | admin | demo_admin | hospital_coordinator | doctor |
|---|---|---|---|---|
| view_hospitals | x | x | x (own) | x |
| add_hospitals | x | x | | |
| edit_hospitals | x | x | x (own) | |
| delete_hospitals | x | x | | |
| view_icu_departments | x | x | x (own) | x |
| add_icu_departments | x | x | x (own) | |
| edit_icu_departments | x | x | x (own) | |
| delete_icu_departments | x | x | | |
| view_icu_admission_requests | x | x | x (own hospital) | |
| manage_icu_admission_requests | x | x | x (own hospital) | |

"(own)" means filtered to the coordinator's assigned hospital via `vendor_id`.

---

## 4. Routes

### 4.1 Admin Backend (web.php)

```
Prefix: app/, middleware: ['auth', 'auth_check'], as: backend.

Hospitals:
  GET    app/hospitals                          → index
  GET    app/hospitals/hospitals-index-data     → index_data (DataTables)
  GET    app/hospitals/create                   → create
  POST   app/hospitals                          → store
  GET    app/hospitals/{id}                     → show
  GET    app/hospitals/{id}/edit                → edit
  PUT    app/hospitals/{id}                     → update
  DELETE app/hospitals/{id}                     → destroy
  POST   app/hospitals/hospitals-bulk-action    → bulk_action
  POST   app/hospitals/update-status/{id}      → update_status

ICU Departments:
  GET    app/icu-departments                              → index
  GET    app/icu-departments/icu-departments-index-data   → index_data
  POST   app/icu-departments                              → store
  PUT    app/icu-departments/{id}                         → update
  DELETE app/icu-departments/{id}                         → destroy
  POST   app/icu-departments/update-status/{id}           → update_status
  POST   app/icu-departments/update-beds/{id}             → update_beds

ICU Admission Requests:
  GET    app/icu-admission-requests                                      → index
  GET    app/icu-admission-requests/icu-admission-requests-index-data   → index_data
  GET    app/icu-admission-requests/{id}                                → show
  POST   app/icu-admission-requests/update-status/{id}                  → update_status
  POST   app/icu-admission-requests/respond/{id}                        → respond
  POST   app/icu-admission-requests/icu-admission-requests-bulk-action  → bulk_action
```

### 4.2 API (api.php)

```
Prefix: v1/, middleware: auth:sanctum

GET    /hospitals                         → paginated list with filters
GET    /hospitals/{id}                    → detail with departments
GET    /icu-departments                   → filterable list across all hospitals
GET    /icu-admission-requests            → user's own requests (role-filtered)
POST   /icu-admission-requests            → create request
GET    /icu-admission-requests/{id}       → detail
POST   /icu-admission-requests/{id}/cancel → cancel
```

### 4.3 Frontend (Frontend module routes)

```
Public (check.header.menu middleware):
  GET  /icu-hospitals                  → hospital catalog with ICU search
  GET  /icu-hospital-details/{id}      → hospital detail with departments

Public (root level, no middleware — matches existing pattern):
  GET  icu_hospital_index_data         → AJAX pagination/filtering

Authenticated (auth, user_check middleware):
  GET  /request-icu-admission          → admission request form
  POST /request-icu-admission/store    → submit request
  GET  /my-icu-requests                → list user's requests
  GET  /my-icu-requests/{id}           → request detail
  GET  /my-icu-requests/index-data     → AJAX pagination
  POST /cancel-icu-request/{id}        → cancel request
  POST /respond-icu-request/{id}       → patient responds to info request (uploads docs, re-submits)
```

---

## 5. Frontend Views (Patient/Family)

### 5.1 ICU Hospital Catalog (`icu_hospitals.blade.php`)

Extends `frontend::layouts.master`. AJAX-powered catalog page, same pattern as lab_test_catalog.

**Filters sidebar:**
- City/area (Select2)
- Specialty type (Select2: cardiac, neurology, etc.)
- Ventilator required (checkbox)
- Oxygen required (checkbox)
- Equipment level (Select2: basic/advanced/full)
- Insurance provider (Select2)
- Price range (min/max inputs)
- Sort by: price, rating, availability (distance sorting deferred — requires geolocation API, out of MVP scope)

**Results:** Card grid showing hospital name, available beds count, specialties, price range, rating, distance badge.

### 5.2 Hospital Detail (`icu_hospital_detail.blade.php`)

Two-column layout (same as nurse_detail, lab_test_detail):
- **Sidebar:** Hospital logo, name, rating, address, phone, accepted insurance list, CTA button "Request Admission"
- **Main content:** Description, ICU departments table (name, specialty, beds available/total, equipment level, ventilator/oxygen, daily price), location map (if lat/lng available)

### 5.3 Admission Request Form (`request_icu_admission.blade.php`)

Multi-section form, extends `frontend::layouts.master`:
- **Section 1:** Select hospital (pre-filled if coming from detail page) + department
- **Section 2:** Patient info (name, age, gender, national ID, insurance)
- **Section 3:** Case details (case type select, urgency, ventilator/oxygen checkboxes, current location, ambulance needed)
- **Section 4:** Medical condition textarea + diagnosis + file uploads
- **Section 5:** Contact info (name, phone, relationship, payment method)
- **Sidebar:** Selected hospital card + estimated daily price

### 5.4 My ICU Requests (`my_icu_requests.blade.php`)

Same pattern as my_test_orders: status filter tabs, AJAX-loaded cards, load more button.

### 5.5 Request Detail (`icu_request_detail.blade.php`)

Two-column layout:
- **Sidebar:** Request number, status badge, urgency badge, hospital name, department, dates, cancel button (if pending/info_requested)
- **Main content:** Patient info section, case details section, medical reports section, admin response section (if responded), cancellation section (if cancelled)

---

## 6. Admin Backend Views

### 6.1 Hospitals Management

Standard CRUD with DataTables (same pattern as Clinics module):
- Index page with DataTable, bulk actions, status toggle
- Create/edit form with translatable fields, media upload for logo/gallery
- Coordinator-only: edit own hospital only

### 6.2 ICU Departments Management

DataTable filtered by hospital. For coordinators, auto-filtered to their hospital.
- Inline bed count update (`update_beds` action)
- Create/edit via offcanvas or modal (lightweight fields)

### 6.3 Admission Requests Management

Index with stats dashboard (total, pending, accepted, rejected) + DataTable.
- **Show page:** Full request detail with response form (accept/reject/request info + notes)
- Coordinators see only their hospital's requests
- Notification sent on every status change

---

## 7. Sidebar Menu

Added to `GenerateMenus.php`:

```
INTENSIVE CARE (static header)
└── Intensive Care (parent)
    ├── Admission Requests   — visible to: admin, demo_admin, hospital_coordinator
    ├── Hospitals             — visible to: admin, demo_admin
    └── ICU Departments       — visible to: admin, demo_admin, hospital_coordinator
```

---

## 8. Notifications

Using existing `sendNotificationOnBookingUpdate()` pattern:

| Event | Recipient | Channel |
|---|---|---|
| new_icu_admission | Hospital coordinator + admin | DB, email, push |
| icu_request_accepted | Patient | DB, email, push |
| icu_request_rejected | Patient | DB, email, push |
| icu_request_info_needed | Patient | DB, email, push |
| icu_request_cancelled | Hospital coordinator | DB, email, push |

---

## 9. Header Menu Integration

New migration to add `icu_hospitals` key to `frontend_settings` table's `heder-menu-setting` entry (same pattern as nurse/lab_tests migration).

Also update `app/Http/Middleware/CheckHeaderMenuSettings.php` to add `'icu-hospital' => 'icu_hospitals'` to the `$routeSettingsMap` array.

---

## 10. Translations

File: `lang/en/intensive_care.php`

Covers: page titles, field labels (lbl_ prefix), status labels, case type labels, urgency labels, equipment levels, success/error messages, filter labels. Approximately 150+ keys.

Also add sidebar keys to `lang/en/sidebar.php`: `intensive_care`, `icu_admission_requests`, `hospitals`, `icu_departments`.

---

## 11. Testing Strategy

### Unit Tests (Modules/IntensiveCare/Tests/Unit/)

- **HospitalTest** — attribute casting, relationships, scopes, slug generation, soft deletes
- **IcuDepartmentTest** — relationships, scopes (available, withVentilator, bySpecialty), constants, bed count logic
- **IcuAdmissionRequestTest** — auto request_number generation, status transitions (canTransitionTo), relationships, scopes, accessors (badges), constants validation
- **PermissionSeederTest** — role creation, permission assignment

### Factories

- HospitalFactory with states: `government()`, `private()`, `active()`, `inactive()`
- IcuDepartmentFactory with states: `cardiac()`, `neurology()`, `available()`, `full()`, `withVentilator()`
- IcuAdmissionRequestFactory with states: `pending()`, `accepted()`, `rejected()`, `critical()`, `urgent()`

---

## 12. Module File Structure

```
Modules/IntensiveCare/
├── module.json
├── composer.json
├── Config/config.php
├── Providers/
│   ├── IntensiveCareServiceProvider.php
│   └── RouteServiceProvider.php
├── Http/
│   ├── Controllers/Backend/
│   │   ├── HospitalsController.php
│   │   ├── IcuDepartmentsController.php
│   │   ├── IcuAdmissionRequestsController.php
│   │   └── API/
│   │       ├── HospitalsApiController.php
│   │       └── IcuAdmissionRequestsApiController.php
│   └── Requests/
│       ├── HospitalFormRequest.php
│       ├── IcuDepartmentFormRequest.php
│       └── IcuAdmissionRequestFormRequest.php
├── Models/
│   ├── Hospital.php
│   ├── IcuDepartment.php
│   └── IcuAdmissionRequest.php
├── Transformers/
│   ├── HospitalResource.php
│   ├── IcuDepartmentResource.php
│   └── IcuAdmissionRequestResource.php
├── Routes/
│   ├── web.php
│   └── api.php
├── Resources/
│   ├── assets/js/app.js
│   ├── assets/sass/app.scss
│   └── views/backend/
│       ├── hospitals/ (index, create, datatable/action_column)
│       ├── departments/ (index, create, datatable/action_column)
│       └── requests/ (index, show, datatable/action_column)
├── database/
│   ├── migrations/ (3 table creates + 1 header menu setting)
│   ├── factories/ (3 factories)
│   └── seeders/
│       ├── IntensiveCareDatabaseSeeder.php
│       └── IntensiveCarePermissionSeeder.php
└── Tests/Unit/ (4 test classes)

Frontend additions (Modules/Frontend/):
├── Http/Controllers/IcuFrontController.php
├── Resources/views/
│   ├── icu_hospitals.blade.php
│   ├── icu_hospital_detail.blade.php
│   ├── request_icu_admission.blade.php
│   ├── my_icu_requests.blade.php
│   └── icu_request_detail.blade.php
└── Routes/web.php (new routes added)
```

---

## 13. Implementation Notes

- All models extend `App\Models\BaseModel` (provides SoftDeletes, audit fields automatically — do NOT redeclare `use SoftDeletes`)
- All models must define explicit `$table` property
- All models must define `newFactory()` returning factory from `Modules\IntensiveCare\Database\Factories\`
- `IntensiveCarePermissionSeeder` must be added to `database/seeders/DatabaseSeeder.php`
- `modules_statuses.json` updated via `php artisan module:enable IntensiveCare`
- Webpack auto-discovers module assets from `modules_statuses.json` — no manual webpack.mix.js changes needed
- `BackendController::index()` needs `lab_technician`-style redirect for `hospital_coordinator` role
- `insurance_number` validation: required when `payment_method` is `insurance`
- `icu_department_id` validation: must belong to the selected `hospital_id`

---

## 14. Out of Scope (Future Phases)

These are explicitly NOT part of this MVP:

- **Patient monitoring / vitals tracking** — Phase 2
- **Ambulance coordination** — Phase 2
- **Real-time bed availability updates** (websockets) — Phase 2
- **Multi-hospital simultaneous requests** — Phase 2
- **Specialist/consultant coordination** — Phase 2
- **Payment integration for ICU** — Phase 2
- **Hospital-to-hospital patient transfers** — Phase 3
- **Mobile push notification for bed availability alerts** — Phase 3
