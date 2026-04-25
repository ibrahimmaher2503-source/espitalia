# Implementation Plan: Admin UI for Labs & Radiology Centers

**Branch**: `006-admin-labs-radiology-ui` | **Date**: 2026-04-02 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/006-admin-labs-radiology-ui/spec.md`

## Summary

Add admin panel CRUD pages for Labs and Radiology Centers within the existing RadiologyLab module. Both entities already have models, database tables, and public search APIs — this feature adds the missing admin management layer with bilingual (AR/EN) form fields, DataTable list views, status/bulk actions, dependent governorate→city dropdowns, and complete UI translations in both languages. No new models or migrations are required.

## Technical Context

**Language/Version**: PHP 8.2+ / Laravel 11.x
**Primary Dependencies**: nwidart/laravel-modules 10.x, spatie/laravel-permission, Yajra DataTables, jQuery, Select2, spatie/laravel-html
**Storage**: MySQL (existing `labs` and `radiology_centers` tables)
**Testing**: PHPUnit + SQLite in-memory
**Target Platform**: Web (admin panel)
**Project Type**: Web application (modular Laravel SaaS)
**Performance Goals**: DataTable pagination with server-side rendering, AJAX city dropdown < 1s
**Constraints**: Must follow existing conventions exactly (Constitution Principle II)
**Scale/Scope**: 2 new admin pages (Labs, Radiology Centers), ~12 new files, ~30 new translation keys per language

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Module Encapsulation | PASS | All new files reside within `Modules/RadiologyLab/`. Cross-module references (Location models) use Eloquent relationships. Sidebar update is in shared `app/Http/Middleware/` as per convention. |
| II. Convention Adherence | PASS | Controllers implement standard method set (index, index_data, create, store, destroy, action, update_status, bulk_action). Routes use `app/` prefix, `backend.*` naming, `['auth', 'auth_check']` middleware. Views extend `backend.layouts.app`. Translation keys use `lbl_*` prefix. |
| III. Role-Based Access Control | PASS | 8 new permissions declared via seeder. Routes gated by permission middleware. Sidebar entries conditionally rendered for admin/demo_admin roles only. |
| IV. Test Discipline | PASS | Feature tests will cover CRUD happy paths and permission enforcement for both entities. |
| V. Simplicity and YAGNI | PASS | No new dependencies. Reuses existing DataTable, Select2, and form patterns. Dependent dropdown uses existing Location API. No speculative abstractions. |

**Post-Phase 1 Re-check**: All gates remain PASS. No new dependencies or patterns introduced.

## Project Structure

### Documentation (this feature)

```text
specs/006-admin-labs-radiology-ui/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0: research decisions
├── data-model.md        # Phase 1: entity documentation
├── quickstart.md        # Phase 1: implementation guide
├── contracts/
│   ├── web-routes.md    # Route contracts
│   └── translations.md  # Translation key contracts
└── checklists/
    └── requirements.md  # Spec quality checklist
```

### Source Code (repository root)

```text
Modules/RadiologyLab/
├── Http/
│   ├── Controllers/Backend/
│   │   ├── LabsController.php                    # NEW — Labs admin CRUD
│   │   └── RadiologyCentersController.php        # NEW — Radiology Centers admin CRUD
│   └── Requests/
│       ├── LabFormRequest.php                     # NEW — Lab validation
│       └── RadiologyCenterFormRequest.php         # NEW — Radiology Center validation
├── Resources/views/backend/
│   ├── labs/
│   │   ├── index.blade.php                       # NEW — Labs DataTable list
│   │   ├── create.blade.php                      # NEW — Labs create/edit form
│   │   └── datatable/
│   │       └── action_column.blade.php           # NEW — Labs row actions
│   └── radiology-centers/
│       ├── index.blade.php                       # NEW — Centers DataTable list
│       ├── create.blade.php                      # NEW — Centers create/edit form
│       └── datatable/
│           └── action_column.blade.php           # NEW — Centers row actions
├── Routes/web.php                                # MODIFY — Add labs & centers routes
├── database/seeders/
│   └── RadiologyLabPermissionSeeder.php          # MODIFY — Add 8 new permissions
└── Tests/Feature/
    ├── LabsCrudTest.php                          # NEW — Labs CRUD tests
    └── RadiologyCentersCrudTest.php              # NEW — Centers CRUD tests

app/Http/Middleware/GenerateMenus.php             # MODIFY — Add sidebar entries

lang/en/radiology_lab.php                         # MODIFY — Add ~30 EN keys
lang/ar/radiology_lab.php                         # MODIFY — Add ~30 AR keys
lang/en/sidebar.php                               # MODIFY — Add 2 sidebar keys
lang/ar/sidebar.php                               # MODIFY — Add 2 sidebar keys
```

**Structure Decision**: All new code resides within the existing `Modules/RadiologyLab/` module following the established module file layout. No new modules, services, or abstractions are created.

## Implementation Phases

### Phase 1: Foundation (Permissions, Translations, Validation)

**Goal**: Establish the permission, translation, and validation layer before building UI.

**Files to modify**:
1. `Modules/RadiologyLab/database/seeders/RadiologyLabPermissionSeeder.php` — Add 8 new permissions (view/add/edit/delete for labs and radiology_centers), assign to admin and demo_admin roles.
2. `lang/en/radiology_lab.php` — Add ~30 new keys for lab/radiology center labels, messages, form placeholders.
3. `lang/ar/radiology_lab.php` — Add corresponding Arabic translations.
4. `lang/en/sidebar.php` — Add `'labs'` and `'radiology_centers'` keys.
5. `lang/ar/sidebar.php` — Add Arabic sidebar translations.

**Files to create**:
6. `Modules/RadiologyLab/Http/Requests/LabFormRequest.php` — Validate name_ar (required, string, max:255), name_en (required, string, max:255), governorate_id (nullable, exists), city_id (nullable, exists), status (nullable, boolean).
7. `Modules/RadiologyLab/Http/Requests/RadiologyCenterFormRequest.php` — Same as LabFormRequest plus scan_type (nullable, string, max:255).

**Verification**: Run seeder, confirm permissions exist in DB, confirm translation keys return correct values.

---

### Phase 2: Labs Admin CRUD

**Goal**: Complete admin management page for Labs.

**Files to create**:
1. `Modules/RadiologyLab/Http/Controllers/Backend/LabsController.php` — Full CRUD controller following LabTestCategoriesController pattern. Methods: index, index_data, create (handles both create/edit via $request->id), store, destroy, action, update_status, bulk_action. Uses Lab model with eager-loaded governorate/city relationships. DataTable name column uses `$data->name` accessor for locale display. Permission middleware on each method.

2. `Modules/RadiologyLab/Resources/views/backend/labs/index.blade.php` — DataTable list view. Columns: checkbox, name (locale-aware), governorate name, city name, status toggle, actions. Filter dropdowns for status. "Add Lab" button gated by `add_labs` permission. Quick-action dropdown for bulk operations.

3. `Modules/RadiologyLab/Resources/views/backend/labs/create.blade.php` — Create/edit form. Fields: name_ar (text, required, dir="rtl"), name_en (text, required, dir="ltr"), governorate (Select2 dropdown loaded with all governorates), city (Select2 dropdown, populated via AJAX on governorate change), status (checkbox). jQuery AJAX: on governorate change, fetch `/api/v1/cities?governorate_id=X` and repopulate city dropdown.

4. `Modules/RadiologyLab/Resources/views/backend/labs/datatable/action_column.blade.php` — Edit/delete/restore buttons following categories action_column pattern.

**Files to modify**:
5. `Modules/RadiologyLab/Routes/web.php` — Add Labs route group with resource routes + custom routes (index_data, bulk_action, action, update_status).

**Verification**: Navigate to /app/labs, create a lab with both names, verify list displays correct locale name, test edit/delete/status toggle.

---

### Phase 3: Radiology Centers Admin CRUD

**Goal**: Complete admin management page for Radiology Centers. Nearly identical to Phase 2 with the addition of scan_type field.

**Files to create**:
1. `Modules/RadiologyLab/Http/Controllers/Backend/RadiologyCentersController.php` — Same pattern as LabsController but for RadiologyCenter model. DataTable includes scan_type column.

2. `Modules/RadiologyLab/Resources/views/backend/radiology-centers/index.blade.php` — Same as labs index but with scan_type column in DataTable.

3. `Modules/RadiologyLab/Resources/views/backend/radiology-centers/create.blade.php` — Same as labs create form plus scan_type text input field.

4. `Modules/RadiologyLab/Resources/views/backend/radiology-centers/datatable/action_column.blade.php` — Same pattern as labs.

**Files to modify**:
5. `Modules/RadiologyLab/Routes/web.php` — Add Radiology Centers route group.

**Verification**: Navigate to /app/radiology-centers, create a center with scan type, verify list shows all columns correctly.

---

### Phase 4: Sidebar Integration

**Goal**: Make Labs and Radiology Centers accessible from the admin sidebar.

**Files to modify**:
1. `app/Http/Middleware/GenerateMenus.php` — Add two new `childMain()` entries under the existing "Diagnostics" parent menu:
   - "Labs" (icon: `ph ph-flask`, route: `backend.labs.index`, permission: `view_labs`, active: `app/labs`)
   - "Radiology Centers" (icon: `ph ph-scan`, route: `backend.radiology-centers.index`, permission: `view_radiology_centers`, active: `app/radiology-centers`)
   - Visible only to admin/demo_admin roles.
   - Update the `$diagnosticsPermissions` array to include `view_labs` and `view_radiology_centers`.

**Verification**: Log in as admin, verify sidebar shows Labs and Radiology Centers under Diagnostics. Log in as non-admin, verify they are hidden.

---

### Phase 5: Tests

**Goal**: Feature tests covering CRUD operations and permission enforcement.

**Files to create**:
1. `Modules/RadiologyLab/Tests/Feature/LabsCrudTest.php`:
   - Test admin can view labs index page
   - Test admin can create lab with bilingual names
   - Test validation rejects missing name_ar or name_en
   - Test admin can edit existing lab
   - Test admin can delete (soft delete) lab
   - Test admin can toggle lab status
   - Test non-admin cannot access labs management
   - Test lab data appears correctly in both locales

2. `Modules/RadiologyLab/Tests/Feature/RadiologyCentersCrudTest.php`:
   - Same test cases as LabsCrudTest
   - Additional test for scan_type field storage and display

**Verification**: `php artisan test --filter=LabsCrudTest` and `php artisan test --filter=RadiologyCentersCrudTest` pass.

---

### Phase 6: Final Verification

**Goal**: End-to-end verification and code quality.

**Steps**:
1. Run `./vendor/bin/pint` — Code style check
2. Run `php artisan test` — All tests pass
3. Run `php artisan optimize:clear` — Clear all caches
4. Run `php artisan route:list --path=labs` — Verify routes registered
5. Manual smoke test in both EN and AR locales:
   - Create/edit/delete a lab
   - Create/edit/delete a radiology center
   - Verify dependent city dropdown
   - Verify bulk actions
   - Verify all UI strings are translated
6. Verify public search APIs still return correct data for newly created records

## Key Design Decisions

| Decision | Choice | Reference |
|----------|--------|-----------|
| Bilingual field strategy | Separate columns (name_ar, name_en) | [research.md#D1](./research.md) |
| City dropdown | AJAX via existing Location API | [research.md#D2](./research.md) |
| Controller pattern | Single store() for create & update | [research.md#D3](./research.md) |
| Permission naming | {action}_{resource_plural} | [research.md#D4](./research.md) |
| Sidebar placement | Under Diagnostics parent menu | [research.md#D5](./research.md) |
| Form validation | Both AR/EN names required | [research.md#D6](./research.md) |
| DataTable name display | Locale-aware via model accessor | [research.md#D7](./research.md) |
| Translation keys | Extend existing radiology_lab.php | [research.md#D8](./research.md) |
