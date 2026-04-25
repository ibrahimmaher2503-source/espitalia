# Implementation Plan: Specialization Taxonomy Refactor

**Branch**: `022-specialization-taxonomy` | **Date**: 2026-04-21 | **Spec**: `specs/022-specialization-taxonomy/spec.md`
**Input**: Feature specification from `/specs/022-specialization-taxonomy/spec.md`

## Summary

Refactor the taxonomy used across Clinics, Doctors, and Nurses so that "Specialization" becomes a first-class, reusable entity independent from SystemServiceCategory and ClinicsCategory. A new `Specialization` module under `Modules/Specialization` provides admin CRUD with a hierarchical (single-level parent/child) model, bilingual names/descriptions, Phosphor icons, and DataTable management. Doctors and Nurses link to Specializations via many-to-many pivot tables (`doctor_specialization`, `nurse_specialization`), replacing legacy free-text fields. Public frontend pages gain data-driven specialization filters (Select2 + JOIN-based queries). QuickBooking gains an optional first step. REST API endpoints expose specializations with doctor/nurse counts.

## Technical Context

**Language/Version**: PHP 8.x / Laravel 11  
**Primary Dependencies**: nwidart/laravel-modules v10, spatie/laravel-permission, Yajra DataTables, Select2, Vue 3 + Pinia (QuickBooking only)  
**Storage**: MySQL (production), SQLite in-memory (testing)  
**Testing**: PHPUnit + SQLite in-memory  
**Target Platform**: Linux server (production), Windows dev  
**Project Type**: Multi-module web application (SaaS)  
**Performance Goals**: DataTable responsive with 500+ specializations (SC-008), filter results within 2 seconds (SC-003, SC-004)  
**Constraints**: Single-level hierarchy only, no new JS libraries (use existing Select2/Vue patterns), backwards-compatible API responses  
**Scale/Scope**: ~50-200 specializations initially, 33+ existing modules, 6 core roles

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Pre-Research Check

| Principle | Status | Notes |
|-----------|--------|-------|
| **I. Module Encapsulation** | PASS | New `Modules/Specialization` module is self-contained. Cross-module references (Doctor, Nurse) use Eloquent `belongsToMany` relationships, not raw SQL. |
| **II. Convention Adherence** | PASS | Model extends BaseModel + HasSlug. Controller implements full standard method set. Routes use correct prefix/naming/middleware. Translations in `lang/en/specialization.php`. Views extend `backend.layouts.app`. |
| **III. Role-Based Access Control** | PASS | Permission seeder creates `view_specialization`, `add_specialization`, `edit_specialization`, `delete_specialization`. Assigned to admin and demo_admin. Sidebar gated by permission. API uses `auth:sanctum`. |
| **IV. Test Discipline** | PASS | PHPUnit tests required for: model CRUD, pivot management, API endpoints, permission checks, hierarchy constraints. SQLite in-memory. |
| **V. Simplicity and YAGNI** | PASS | Simple pivot tables (no pivot models — association-only). Uses existing Select2/DataTables patterns. No new dependencies. Single-level hierarchy (not recursive tree). |
| **Tech Stack** | PASS | No new technology introduced. All work within Laravel 11, Mix, Blade, jQuery, Select2, Vue 3 (QuickBooking). |

### Post-Design Re-Check

| Principle | Status | Notes |
|-----------|--------|-------|
| **I. Module Encapsulation** | PASS | Specialization module owns its model, migrations, routes, views, seeders. Doctor/Nurse models only add a relationship method (1 line each). Frontend controllers add filter logic inline. |
| **II. Convention Adherence** | PASS | All patterns verified against existing modules (FAQ, Offer, Clinic). DataTable, bulk_action, update_status all follow convention. |
| **III. RBAC** | PASS | 4 permissions, 2 roles. demo_admin gets view + add + edit (no delete). |
| **IV. Test Discipline** | PASS | Test plan covers: Specialization CRUD, hierarchy constraint (can't delete parent with children), pivot sync on doctor/nurse, API endpoints, permission guards. |
| **V. YAGNI** | PASS | No pivot models, no deep hierarchy, no specialization-based pricing. Data migration is a separate artisan command (not automatic). Icon stored as CSS class string (not media library — simpler). |

**GATE RESULT**: ALL PASS — no violations requiring justification.

## Project Structure

### Documentation (this feature)

```text
specs/022-specialization-taxonomy/
├── plan.md              # This file
├── research.md          # Phase 0 output — codebase research findings
├── data-model.md        # Phase 1 output — database schema design
├── quickstart.md        # Phase 1 output — development setup guide
├── contracts/
│   ├── admin-web-routes.md    # Admin CRUD route contract
│   └── api-endpoints.md       # REST API endpoint contract
└── tasks.md             # Phase 2 output (created by /speckit.tasks)
```

### Source Code (repository root)

```text
Modules/Specialization/                    # NEW MODULE
├── Http/Controllers/
│   └── SpecializationController.php       # Admin CRUD (index, index_data, create, store, edit, update, destroy, bulk_action, update_status)
├── Http/Controllers/API/
│   └── SpecializationController.php       # API (list, show, doctors, nurses)
├── Models/
│   └── Specialization.php                 # BaseModel + HasSlug, parent/children, doctors(), nurses()
├── Transformers/
│   └── SpecializationResource.php         # API resource with counts
├── Routes/
│   ├── web.php                            # Admin routes (prefix app/, as backend.)
│   └── api.php                            # API routes (auth:sanctum, prefix v1)
├── Resources/views/backend/specializations/
│   ├── index.blade.php                    # DataTable list
│   ├── create.blade.php                   # Create/edit form (bilingual inputs, parent select, icon, status)
│   └── datatable/action_column.blade.php  # Edit/delete buttons
├── database/migrations/
│   ├── *_create_specializations_table.php
│   ├── *_create_doctor_specialization_table.php
│   └── *_create_nurse_specialization_table.php
├── database/seeders/
│   └── SpecializationPermissionSeeder.php
├── Console/Commands/
│   └── MigrateNurseSpecializations.php    # Artisan command for nurse text→pivot migration
├── Config/config.php
└── Tests/
    ├── Unit/
    │   └── SpecializationModelTest.php
    └── Feature/
        ├── SpecializationCrudTest.php
        └── SpecializationApiTest.php

# MODIFIED FILES
Modules/Clinic/Models/Doctor.php                            # +specializations() relationship
Modules/RequestNurse/Models/Nurse.php                       # +specializations() relationship
Modules/Clinic/Resources/assets/js/component/DoctorOffcanvas.vue  # +specialization multi-select, expert read-only
Modules/RequestNurse/Resources/views/backend/nurses/create.blade.php  # +specialization multi-select
Modules/Clinic/Http/Controllers/DoctorController.php        # sync specializations on save/edit
Modules/RequestNurse/Http/Controllers/Backend/NursesController.php  # sync specializations on save/edit
Modules/Frontend/Http/Controllers/DoctorController.php      # +specialization filter
Modules/Frontend/Resources/views/doctors.blade.php          # +specialization Select2 dropdown
Modules/Frontend/Http/Controllers/NurseRequestFrontController.php  # replace LIKE with JOIN filter
Modules/Frontend/Resources/views/available_nurses.blade.php # replace hardcoded dropdown
Modules/QuickBooking/Resources/assets/js/quick-booking/QuickBooking.vue  # +specialization step
Modules/QuickBooking/Resources/assets/js/store/quick-booking.js  # +specialization_id state
Modules/QuickBooking/Resources/assets/js/quick-booking/TabPanel.vue  # +specialization case
Modules/Clinic/Transformers/DoctorResource.php              # +specializations array
app/Http/Middleware/GenerateMenus.php                       # +sidebar entry
lang/en/sidebar.php                                         # +lbl_specializations
lang/en/specialization.php                                  # NEW translation file
modules_statuses.json                                       # +"Specialization": true
```

**Structure Decision**: New self-contained module under `Modules/Specialization/` following the standard nwidart/laravel-modules layout. Cross-module integration via Eloquent relationships added to existing Doctor and Nurse models. No new packages or libraries required.

## Complexity Tracking

No constitution violations — this section is intentionally empty.

## Implementation Phases

### Phase 1: Foundation (Stories 1) — Admin CRUD

1. Scaffold `Modules/Specialization` via artisan
2. Create `specializations` table migration
3. Create `Specialization` model with bilingual accessors, parent/children relationships, HasSlug, scopes
4. Create admin controller with full standard method set
5. Create Blade views: index (DataTable), create/edit (bilingual form with parent select, icon, status)
6. Create permission seeder (4 permissions, admin + demo_admin)
7. Add sidebar entry in GenerateMenus
8. Add translation file `lang/en/specialization.php` + sidebar key
9. Register routes, enable module
10. Write tests: CRUD operations, hierarchy constraint (block delete with active children), permission checks

### Phase 2: Doctor Integration (Story 2)

1. Create `doctor_specialization` pivot migration
2. Add `specializations()` relationship to Doctor model
3. Modify DoctorOffcanvas.vue: add searchable multi-select for specializations, make expert read-only
4. Modify DoctorController: load specializations for edit, sync on update
5. Write tests: pivot sync, edit form data loading

### Phase 3: Nurse Integration (Story 3)

1. Create `nurse_specialization` pivot migration
2. Add `specializations()` relationship to Nurse model
3. Modify nurse create.blade.php: add Select2 multi-select, make legacy fields read-only
4. Modify NursesController: load specializations for edit, sync on update
5. Create `MigrateNurseSpecializations` artisan command
6. Write tests: pivot sync, migration command (matched vs. unmatched)

### Phase 4: Public Frontend Filters (Stories 4, 5)

1. Modify DoctorController (Frontend): pass active specializations to view, add `whereHas` filter
2. Modify doctors.blade.php: add specialization Select2 dropdown
3. Modify NurseRequestFrontController: replace LIKE filter with JOIN-based filter, pass specializations to view
4. Modify available_nurses.blade.php: replace hardcoded dropdown with data-driven Select2
5. Display assigned specializations on doctor/nurse cards
6. Write tests: filter logic for both pages

### Phase 5: QuickBooking Integration (Story 6)

1. Create `SelectSpecialization.vue` component
2. Add specialization step to `setupArray` in QuickBooking.vue (both flows)
3. Add `specialization_id` to Pinia store
4. Add case in TabPanel.vue
5. Filter downstream Clinic/Doctor steps by selected specialization
6. Write tests: step flow with and without specialization selection

### Phase 6: API (Story 7)

1. Create `SpecializationResource` transformer
2. Create API SpecializationController: index (with counts), show, doctors, nurses
3. Register API routes
4. Extend DoctorResource with `specializations` array
5. Extend nurse API response with `specializations` array (keep legacy `specialization` string)
6. Add `specialization_ids[]` filter to existing doctor/nurse list endpoints
7. Write tests: all API endpoints, backwards compatibility

### Phase 7: Polish & Verification

1. Run full test suite
2. Run `./vendor/bin/pint` for code style
3. Run `php artisan optimize:clear`
4. Manual testing of all user stories
5. Verify DataTable performance with bulk data
