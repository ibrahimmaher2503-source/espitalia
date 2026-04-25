# Research: Reception Portal Enhancement

**Feature**: 021-reception-portal
**Date**: 2026-04-16

## Executive Summary

This document consolidates research findings for enhancing the receptionist portal with access to Doctor Visit, Lab Test Orders, and ICU Admission Request modules.

---

## 1. DoctorVisit Module Analysis

### Model: DoctorVisitRequest

**Location**: `Modules/DoctorVisit/Models/DoctorVisitRequest.php`

**Key Fields**:
| Field | Type | Description |
|-------|------|-------------|
| `reference_number` | string | Auto-generated VR-YYYY-NNNN |
| `patient_id` | FK → users | Patient who requested visit |
| `preferred_doctor_id` | FK → doctors (nullable) | Patient's doctor preference |
| `assigned_doctor_id` | FK → doctors (nullable) | Staff-assigned doctor |
| `visit_reason` | text | Chief complaint |
| `preferred_date` | date | Requested visit date |
| `contact_phone` | string | Patient contact |
| `status` | enum | pending, confirmed, cancelled, completed |
| `admin_notes` | text (nullable) | Internal notes |

**Status Workflow**:
```
pending → confirmed → completed
       ↘ cancelled
confirmed → cancelled
```

**Relationships**:
- `patient()` → User
- `preferredDoctor()` → Doctor
- `assignedDoctor()` → Doctor
- `statusHistories()` → DoctorVisitStatusHistory

### Existing Permissions

**Source**: `DoctorVisitDatabaseSeeder.php`

| Permission | Description |
|------------|-------------|
| `view_doctor_visit` | View requests list and details |
| `create_doctor_visit` | Create new requests |
| `update_status_doctor_visit` | Change request status |
| `assign_doctor_visit` | Assign/reassign doctors |

**Current Role Assignments**:
| Role | Permissions |
|------|-------------|
| admin | All 4 permissions |
| receptionist | All 4 permissions ✓ |
| doctor | view, update_status (own assigned) |
| user (patient) | view (own), create |

**Decision**: No changes needed - receptionist already has full access.

### Controller: DoctorVisitRequestController

**Location**: `Modules/DoctorVisit/Http/Controllers/Backend/DoctorVisitRequestController.php`

**Methods**:
- `index()` - List view with stats
- `index_data()` - DataTable server-side
- `show($id)` - Detail view with status history
- `update_status($id)` - Change status with validation
- `bulk_action()` - Bulk delete

**Routes** (prefix: `app/doctor-visit`, name: `backend.doctor_visit.*`):
```
GET    /                    → index
POST   /index-data          → index_data
GET    /{id}                → show
POST   /update-status/{id}  → update_status
POST   /bulk-action         → bulk_action
```

### Integration Action Required

1. **Add menu entry** in `GenerateMenus.php` for receptionist role
2. **No controller changes** needed - already supports role-based filtering

---

## 2. RadiologyLab Module Analysis (TestOrder)

### Model: TestOrder

**Location**: `Modules/RadiologyLab/Models/TestOrder.php`

**Key Fields**:
| Field | Type | Description |
|-------|------|-------------|
| `order_number` | string | Auto-generated LAB-YYYY-NNNN |
| `patient_id` | FK → users | Test patient |
| `doctor_id` | FK → users (nullable) | Requesting doctor |
| `lab_technician_id` | FK → users (nullable) | Assigned technician |
| `priority` | enum | routine, urgent, stat |
| `order_date` | date | When ordered |
| `status` | enum | pending, confirmed, sample_collected, processing, completed, delivered, cancelled |
| `payment_status` | enum | paid, unpaid, refunded |
| `total_amount`, `final_amount` | decimal | Pricing |

**Important**: No direct `clinic_id` field. Clinic context must be derived through:
- `doctor.clinic_id` relationship
- Patient affiliation

**Relationships**:
- `patient()` → User
- `doctor()` → User
- `labTechnician()` → User
- `items()` → TestOrderItem
- `reports()` → TestReport

### Existing Permissions

**Source**: `RadiologyLabPermissionSeeder.php`

| Permission | Description |
|------------|-------------|
| `view_test_orders` | View orders |
| `add_test_orders` | Create orders |
| `edit_test_orders` | Update/assign technician |
| `delete_test_orders` | Delete orders |
| `manage_test_results` | Enter results/reports |

**Current Role Assignments**:
| Role | Permissions |
|------|-------------|
| admin | All permissions |
| lab_technician | view_lab_tests, view_test_orders, manage_test_results |
| doctor | view_lab_tests, view_test_orders, add_test_orders |
| receptionist | **None** ❌ |

### Controller: TestOrdersController

**Location**: `Modules/RadiologyLab/Http/Controllers/Backend/TestOrdersController.php`

**Current Role Filtering** (in `index_data()`):
```php
if (auth()->user()->hasRole('lab_technician')) {
    $query->where('lab_technician_id', auth()->id());
}
if (auth()->user()->hasRole('doctor')) {
    $query->where('doctor_id', auth()->id());
}
```

**Routes** (prefix: `app/test-orders`, name: `backend.test-orders.*`):
```
GET    /                    → index
GET    /{id}                → show
GET    /create              → create
POST   /                    → store
POST   /{id}/update-status  → update_status
...
```

### Integration Action Required

1. **Update seeder**: Add `view_test_orders` to receptionist role
2. **Add controller filtering**: Filter by clinic via doctor relationship
   ```php
   if (auth()->user()->hasRole('receptionist')) {
       $receptionist = Receptionist::where('receptionist_id', auth()->id())->first();
       if ($receptionist) {
           $query->whereHas('doctor', function ($q) use ($receptionist) {
               // Find doctors associated with receptionist's clinic
               $q->whereHas('clinicMappings', function ($q2) use ($receptionist) {
                   $q2->where('clinic_id', $receptionist->clinic_id);
               });
           });
       }
   }
   ```
3. **Add menu entry** in `GenerateMenus.php`
4. **Hide edit actions** in Blade views for receptionist role

---

## 3. IntensiveCare Module Analysis (IcuAdmissionRequest)

### Model: IcuAdmissionRequest

**Location**: `Modules/IntensiveCare/Models/IcuAdmissionRequest.php`

**Key Fields**:
| Field | Type | Description |
|-------|------|-------------|
| `request_number` | string | Auto-generated ICU-YYYY-NNNN |
| `patient_id` | FK → users | Patient |
| `hospital_id` | FK → hospitals | Target hospital |
| `icu_department_id` | FK → icu_departments (nullable) | Specific ICU |
| `case_type` | enum | stroke, cardiac, post_operative, etc. |
| `urgency` | enum | critical, urgent, standard |
| `status` | enum | pending, accepted, rejected, info_requested, cancelled |
| `medical_condition_ar/en` | text | Bilingual medical details |
| `diagnosis_ar/en` | text | Bilingual diagnosis |
| `admin_notes_ar/en` | text | Bilingual staff notes |

**Relationships**:
- `patient()` → User
- `hospital()` → Hospital
- `icuDepartment()` → IcuDepartment
- `respondedBy()` → User

### Existing Permissions

**Source**: `IntensiveCarePermissionSeeder.php`

| Permission | Description |
|------------|-------------|
| `view_icu_admission_requests` | View requests |
| `edit_icu_admission_requests` | Respond/update status |
| `delete_icu_admission_requests` | Delete requests |

**Current Role Assignments**:
| Role | Permissions |
|------|-------------|
| admin | All permissions |
| hospital_coordinator | view, edit (scoped to their hospital) |
| doctor | view only |
| receptionist | **None** ❌ |

### Controller: IcuAdmissionRequestsController

**Location**: `Modules/IntensiveCare/Http/Controllers/Backend/IcuAdmissionRequestsController.php`

**Current Role Filtering** (hospital_coordinator pattern):
```php
if (auth()->user()->hasRole('hospital_coordinator')) {
    $hospital = Hospital::where('vendor_id', auth()->id())->first();
    if ($hospital) {
        $query->where('hospital_id', $hospital->id);
    }
}
```

### Integration Action Required

1. **Update seeder**: Add `view_icu_admission_requests` to receptionist role
2. **Add controller filtering**: Filter by clinic's patients
   ```php
   if (auth()->user()->hasRole('receptionist')) {
       $receptionist = Receptionist::where('receptionist_id', auth()->id())->first();
       if ($receptionist) {
           // Get patients who have appointments at this clinic
           $clinicPatientIds = Appointment::where('clinic_id', $receptionist->clinic_id)
               ->distinct()
               ->pluck('user_id');
           $query->whereIn('patient_id', $clinicPatientIds);
       }
   }
   ```
3. **Add menu entry** in `GenerateMenus.php`
4. **Hide edit actions** in Blade views for receptionist role

---

## 4. Receptionist Model Analysis

### Model: Receptionist

**Location**: `Modules/Clinic/Models/Receptionist.php`

**Table**: `receptionist`

**Fields**:
| Field | Type | Description |
|-------|------|-------------|
| `receptionist_id` | FK → users | The receptionist user |
| `clinic_id` | FK → clinics | Assigned clinic |
| `vendor_id` | FK → users | Clinic vendor/owner |

**Relationships**:
- `clinics()` → Clinics (belongsTo)
- `users()` → User (belongsTo receptionist_id)
- `vendor()` → User (belongsTo vendor_id)

**Usage Pattern** (from BackendController):
```php
$receptionist = Receptionist::CheckMultivendor()
    ->where('receptionist_id', $user->id)
    ->pluck('clinic_id');
```

**Key Insight**: A receptionist can be assigned to ONE clinic. All data filtering should use this clinic association.

---

## 5. Dashboard Enhancement Analysis

### Current Dashboard: receptionistDashboard()

**Location**: `app/Http/Controllers/Backend/BackendController.php`

**Current Metrics**:
- `total_appointments` - Clinic appointments count
- `total_patient` - Unique patients count
- `total_earning` - Total revenue
- `total_assign_doctor` - Doctors assigned to clinic

**Current View**: `resources/views/backend/dashboard/receptionist.blade.php`
- Uses FullCalendar for appointment calendar
- Shows 3 stat cards (appointments, patients, doctors)
- Links to appointments list

### Enhancement Required

1. **Add stat card**: Pending Doctor Visits count
2. **Add stat card**: Today's Test Orders (if permission granted)
3. **Add calendar events**: Doctor visits with distinct color
4. **Add quick actions**: New Patient, New Appointment, View Schedule

---

## 6. Menu Navigation Analysis

### GenerateMenus Middleware

**Location**: `app/Http/Middleware/GenerateMenus.php`

**Current Receptionist Menu Items** (lines 42-81):
- Dashboard (receptionist-dashboard)
- Appointment
- Encounter

**Receptionist Access Sections**:
- Clinic Center (with permission check)
- Doctor menu (sessions, services)
- Doctor Visits (if hasAnyRole includes receptionist)

**Current Doctor Visit Menu** (lines 600-619):
```php
if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'receptionist', 'doctor'])
    && auth()->user()->can('view_doctor_visit')) {
    // Doctor Visits parent menu
}
```

**Decision**: Doctor Visit menu already includes receptionist - just verify permission is assigned.

---

## 7. Alternatives Considered

### Permission Strategy Options

| Option | Pros | Cons | Decision |
|--------|------|------|----------|
| A. Reuse existing `view_*` permissions | Simple, consistent | Less granular control | **Selected** |
| B. Create new `view_*_readonly` permissions | Explicit read-only | Permission proliferation | Rejected |
| C. Create combined `receptionist_*` permissions | Role-specific | Breaks existing patterns | Rejected |

**Rationale**: Option A maintains consistency with existing permission model. Read-only enforcement is handled via controller/view logic, not permission names.

### Scoping Strategy for TestOrders

| Option | Pros | Cons | Decision |
|--------|------|------|----------|
| A. Filter by doctor.clinic_id | Direct clinic association | Requires doctor relationship | **Selected** |
| B. Filter by patient affiliation | Patient-centric | Patients may visit multiple clinics | Rejected |
| C. Show all orders | Simple | Data isolation violation | Rejected |

### Scoping Strategy for IcuAdmissionRequests

| Option | Pros | Cons | Decision |
|--------|------|------|----------|
| A. Filter by clinic patients | Shows relevant requests | Requires appointment lookup | **Selected** |
| B. Filter by hospital_id | Matches coordinator pattern | Clinics don't have hospital_id | Rejected |
| C. Show all requests | Simple | Data isolation violation | Rejected |

---

## 8. Summary of Changes Required

### Permission Seeder Updates

| Module | Permission | Role | Action |
|--------|-----------|------|--------|
| DoctorVisit | view_doctor_visit | receptionist | Already assigned ✓ |
| RadiologyLab | view_test_orders | receptionist | **Add** |
| IntensiveCare | view_icu_admission_requests | receptionist | **Add** |

### Controller Updates

| Controller | Method | Change |
|------------|--------|--------|
| TestOrdersController | index_data() | Add receptionist clinic filtering |
| TestOrdersController | show() | Add receptionist authorization check |
| IcuAdmissionRequestsController | index_data() | Add receptionist patient filtering |
| IcuAdmissionRequestsController | show() | Add receptionist authorization check |
| BackendController | receptionistDashboard() | Add new metrics and quick actions |

### View Updates

| View | Change |
|------|--------|
| test-orders/index.blade.php | Hide create/edit buttons for receptionist |
| test-orders/show.blade.php | Hide status/technician actions for receptionist |
| icu-admission-requests/index.blade.php | Hide respond/edit buttons for receptionist |
| icu-admission-requests/show.blade.php | Hide response form for receptionist |
| dashboard/receptionist.blade.php | Add stat cards, quick actions, calendar events |

### Menu Updates

| File | Change |
|------|--------|
| GenerateMenus.php | Add Test Orders menu for receptionist |
| GenerateMenus.php | Add ICU Requests menu for receptionist |
| GenerateMenus.php | Verify Doctor Visit menu includes receptionist |
