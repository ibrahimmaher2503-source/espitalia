# Implementation Plan: Reception Portal Enhancement

**Branch**: `021-reception-portal` | **Date**: 2026-04-16 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/021-reception-portal/spec.md`

## Summary

Enhance the receptionist portal to integrate newly added platform modules (Doctor Visit, Lab Test Orders, ICU Admission Requests). This involves:
1. Adding full CRUD access for Doctor Visit management
2. Adding read-only access for Test Orders and ICU Admission Requests
3. Enhancing the receptionist dashboard with new metrics and quick actions
4. Creating appropriate permissions and updating menu navigation

## Technical Context

**Language/Version**: PHP 8.2 / Laravel 11.x
**Primary Dependencies**: nwidart/laravel-modules 10.x, spatie/laravel-permission, Laravel Mix
**Storage**: MySQL (SQLite for testing)
**Testing**: PHPUnit + SQLite in-memory
**Target Platform**: Web application (Admin dashboard)
**Project Type**: Multi-tenant SaaS clinic management system
**Performance Goals**: Dashboard loads < 2 seconds, DataTables < 1 second server response
**Constraints**: Must follow existing module conventions, role-based access control
**Scale/Scope**: ~6 user roles, 48+ modules, multi-clinic operations

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| **I. Module Encapsulation** | PASS | No new module needed - enhancing existing modules (DoctorVisit, RadiologyLab, IntensiveCare) and core receptionist views |
| **II. Convention Adherence** | PASS | Will follow established controller patterns, route conventions, permission naming |
| **III. Role-Based Access Control** | PASS | Adding new permissions via seeders, updating GenerateMenus middleware |
| **IV. Test Discipline** | PASS | Unit tests for permission logic, feature tests for receptionist access |
| **V. Simplicity and YAGNI** | PASS | Using existing patterns (DataTables, FullCalendar), no new libraries |

**Pre-Design Gate**: PASSED - No violations detected.

## Project Structure

### Documentation (this feature)

```text
specs/021-reception-portal/
├── plan.md              # This file
├── research.md          # Phase 0 output - module analysis
├── data-model.md        # Phase 1 output - permission & entity relationships
├── quickstart.md        # Phase 1 output - dev setup guide
├── contracts/           # Phase 1 output - N/A (no new APIs)
└── tasks.md             # Phase 2 output
```

### Source Code (repository root)

```text
# Existing structure - modifications only, no new modules

app/Http/Middleware/
└── GenerateMenus.php        # Add receptionist menu items for new modules

app/Http/Controllers/Backend/
└── BackendController.php    # Enhance receptionistDashboard() method

resources/views/backend/dashboard/
└── receptionist.blade.php   # Enhanced dashboard view

Modules/DoctorVisit/
├── Http/Controllers/Backend/
│   └── DoctorVisitRequestController.php  # Already has receptionist support
└── database/seeders/
    └── DoctorVisitDatabaseSeeder.php     # Verify receptionist permissions

Modules/RadiologyLab/
├── Http/Controllers/Backend/
│   └── TestOrdersController.php          # Add receptionist read-only filtering
├── Resources/views/backend/test-orders/
│   └── *.blade.php                       # Hide edit actions for receptionist
└── database/seeders/
    └── RadiologyLabPermissionSeeder.php  # Add receptionist permission

Modules/IntensiveCare/
├── Http/Controllers/Backend/
│   └── IcuAdmissionRequestsController.php # Add receptionist read-only filtering
├── Resources/views/backend/icu-admission-requests/
│   └── *.blade.php                        # Hide edit actions for receptionist
└── database/seeders/
    └── IntensiveCarePermissionSeeder.php  # Add receptionist permission

tests/
├── Feature/
│   └── ReceptionistPortalTest.php         # New feature tests
└── Unit/
    └── ReceptionistPermissionTest.php     # Permission verification tests
```

**Structure Decision**: This is an enhancement to existing modules, not a new module. All changes follow the existing modular architecture with modifications to controllers, views, seeders, and middleware.

## Complexity Tracking

No violations requiring justification - all changes use existing patterns.

## Phase Summary

### Phase 0: Research (Complete)
- Analyzed DoctorVisit module structure and permissions
- Analyzed RadiologyLab TestOrder model and controller
- Analyzed IntensiveCare IcuAdmissionRequest model and controller
- Identified Receptionist model clinic association pattern

### Phase 1: Design & Contracts

**Key Findings from Research:**

1. **DoctorVisit Module**
   - Already has receptionist permissions: `view_doctor_visit`, `create_doctor_visit`, `update_status_doctor_visit`, `assign_doctor_visit`
   - Receptionist role already assigned in DoctorVisitDatabaseSeeder
   - No changes needed to DoctorVisit permissions - just add menu entry

2. **RadiologyLab Module (TestOrder)**
   - Has `view_test_orders` permission but NOT assigned to receptionist
   - TestOrder lacks direct `clinic_id` - must filter via doctor relationship
   - Need: Add receptionist to seeder with read-only access
   - Need: Add clinic-scoped filtering in controller

3. **IntensiveCare Module (IcuAdmissionRequest)**
   - Has `view_icu_admission_requests` permission but NOT assigned to receptionist
   - Uses hospital_id for scoping (hospital_coordinator pattern)
   - Need: Add receptionist to seeder with read-only access
   - Need: Decide scoping strategy (clinic patients who submitted ICU requests)

4. **Receptionist-Clinic Association**
   - Model: `Modules\Clinic\Models\Receptionist`
   - Table: `receptionist` with `receptionist_id`, `clinic_id`, `vendor_id`
   - Receptionist can be assigned to ONE clinic (singular relationship)
   - Used in BackendController::receptionistDashboard() for filtering

**Design Decisions:**

| Decision | Choice | Rationale |
|----------|--------|-----------|
| TestOrder scoping | Filter by doctor's clinic_id | Maintains data isolation per clinic |
| ICU scoping | Filter by patient_id (clinic patients) | Receptionists see ICU requests from their clinic's patients |
| Permission naming | Use existing `view_*` permissions | Consistency, avoid permission proliferation |
| Quick actions | Modal forms using existing patterns | Reuse appointment quick booking UX |

---

## Post-Design Constitution Check

*Re-evaluation after Phase 1 design completion.*

| Principle | Status | Verification |
|-----------|--------|--------------|
| **I. Module Encapsulation** | PASS | ✓ No new module created. ✓ Cross-module references via Eloquent relationships. ✓ Shared logic in existing middleware/controllers. |
| **II. Convention Adherence** | PASS | ✓ Controllers follow standard method set pattern. ✓ Routes use `app/` prefix and `backend.*` naming. ✓ Permissions follow `view_*` convention. |
| **III. Role-Based Access Control** | PASS | ✓ New permissions added via seeders. ✓ GenerateMenus updated with permission checks. ✓ Controllers verify authorization. |
| **IV. Test Discipline** | PASS | ✓ Feature tests planned for receptionist access. ✓ Permission unit tests specified. |
| **V. Simplicity and YAGNI** | PASS | ✓ No new libraries. ✓ Reuses DataTables, FullCalendar. ✓ Minimal permission additions. |

**Post-Design Gate**: PASSED - Design complies with all constitution principles.

---

## Generated Artifacts

- [research.md](research.md) - Full module analysis
- [data-model.md](data-model.md) - Permission and entity relationships
- [quickstart.md](quickstart.md) - Development setup guide
- contracts/ - N/A (no new external APIs, using existing admin routes)

## Next Steps

Run `/speckit.tasks` to generate the actionable task list for implementation.
