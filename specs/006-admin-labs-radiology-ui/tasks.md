# Tasks: Admin UI for Labs & Radiology Centers

**Input**: Design documents from `/specs/006-admin-labs-radiology-ui/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Included (Constitution Principle IV requires test coverage for new features).

**Organization**: Tasks are grouped by user story. US3 (Bilingual Data Entry) and US4 (Bilingual UI Translation) are cross-cutting concerns fulfilled within US1 and US2 implementation — bilingual form fields and translations are built into every view and controller. US5 (Bulk Actions) and US6 (Dependent Dropdown) are similarly delivered within the US1/US2 views and controllers.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project initialization needed — module and models already exist. Skip to foundational.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Permissions, translations, validation, sidebar, and routes that ALL user stories depend on.

**CRITICAL**: No user story work can begin until this phase is complete.

- [x] T001 Add 8 new permissions (view/add/edit/delete for labs and radiology_centers) to `Modules/RadiologyLab/database/seeders/RadiologyLabPermissionSeeder.php` — assign all to admin and demo_admin roles
- [x] T002 [P] Add ~30 English translation keys for labs and radiology centers to `lang/en/radiology_lab.php` per contracts/translations.md
- [x] T003 [P] Add ~30 Arabic translation keys for labs and radiology centers to `lang/ar/radiology_lab.php` per contracts/translations.md
- [x] T004 [P] Add sidebar keys ('labs', 'radiology_centers') to `lang/en/sidebar.php`
- [x] T005 [P] Add sidebar keys ('المختبرات', 'مراكز الأشعة') to `lang/ar/sidebar.php`
- [x] T006 [P] Create `Modules/RadiologyLab/Http/Requests/LabFormRequest.php` with rules: name_ar (required|string|max:255), name_en (required|string|max:255), governorate_id (nullable|integer|exists:governorates,id), city_id (nullable|integer|exists:governorate_cities,id), status (nullable|boolean). Include translated validation messages.
- [x] T007 [P] Create `Modules/RadiologyLab/Http/Requests/RadiologyCenterFormRequest.php` with same rules as LabFormRequest plus scan_type (nullable|string|max:255). Include translated validation messages.
- [x] T008 Add Labs and Radiology Centers route groups to `Modules/RadiologyLab/Routes/web.php` — resource routes + custom routes (index_data, bulk_action, action, update_status) per contracts/web-routes.md
- [x] T009 Add "Labs" and "Radiology Centers" child menu items under Diagnostics parent in `app/Http/Middleware/GenerateMenus.php` — icons: ph ph-flask (labs), ph ph-scan (radiology centers), gated by view_labs/view_radiology_centers permissions, admin/demo_admin roles only. Update $diagnosticsPermissions array.

**Checkpoint**: Run seeder (`php artisan db:seed --class=...RadiologyLabPermissionSeeder`), verify permissions in DB, verify `php artisan route:list --path=labs` shows routes, verify translations load.

---

## Phase 3: User Story 1 — Admin Manages Labs (Priority: P1) MVP

**Goal**: Complete CRUD admin page for Labs with bilingual name fields, governorate/city dropdowns, DataTable list, status toggle, bulk actions, and soft delete.

**Independent Test**: Navigate to /app/labs, create a lab with AR+EN names and location, see it in the list, edit it, toggle status, delete and restore it.

**Covers**: US1 (Labs CRUD), US3 (bilingual form fields in labs), US4 (translations used in labs views), US5 (bulk actions in labs), US6 (dependent dropdown in labs form).

### Implementation for User Story 1

- [x] T010 [US1] Create `Modules/RadiologyLab/Http/Controllers/Backend/LabsController.php` following LabTestCategoriesController pattern — constructor sets module_title/module_name/module_icon with translation keys, permission middleware on each method. Methods: index (pass governorates for filter), index_data (Yajra DataTable with Lab::with(['governorate','city']), editColumn name using $data->name accessor, governorate/city name columns with null-safe display, status toggle switch, action_column renderView), create (load governorates, load cities if editing with existing governorate_id, detect edit mode via $request->id), store (LabFormRequest validation, create or update based on $request->id, redirect with translated success message), destroy (soft delete), action (restore/force-delete), update_status (toggle status), bulk_action (change-status/delete/restore/permanently-delete).
- [x] T011 [P] [US1] Create `Modules/RadiologyLab/Resources/views/backend/labs/datatable/action_column.blade.php` — check $lab->trashed() for restore/force-delete vs edit/delete buttons. Permission gates: edit_labs, delete_labs. Edit links to backend.labs.create with id param. Delete uses data-type="ajax" with CSRF token and confirmation message.
- [x] T012 [US1] Create `Modules/RadiologyLab/Resources/views/backend/labs/index.blade.php` — extends backend.layouts.app. Breadcrumb, card layout, section-header with quick-action bulk dropdown (change-status, delete, restore, permanently-delete). Status filter dropdown. Search input. "Add Lab" button gated by @can('add_labs'). DataTable JS config with columns: check, name, governorate, city, status (rawColumn), updated_at (hidden, for ordering), actions (rawColumn). initDatatable() call to backend.labs.index_data URL.
- [x] T013 [US1] Create `Modules/RadiologyLab/Resources/views/backend/labs/create.blade.php` — extends backend.layouts.app. Form POST to backend.labs.store with hidden id field for edit mode. Card layout with back button. Fields: name_ar (text input, dir="rtl", required, value from old('name_ar', $data->name_ar ?? '')), name_en (text input, dir="ltr", required), governorate_id (Select2 dropdown populated from $governorates, with placeholder from translation), city_id (Select2 dropdown populated dynamically). Status checkbox with hidden input default 0. jQuery AJAX on #governorate_id change: fetch /api/v1/cities?governorate_id=X, rebuild #city_id options. On edit: pre-select governorate and pre-load cities then select saved city_id. All labels use __('radiology_lab.lbl_*') translation keys. Validation error display with @error directive.

**Checkpoint**: Labs CRUD fully functional — create/edit/delete/status/bulk all work. Both AR and EN names stored. Dependent city dropdown works. All UI text translated.

---

## Phase 4: User Story 2 — Admin Manages Radiology Centers (Priority: P1)

**Goal**: Complete CRUD admin page for Radiology Centers — identical to Labs plus scan_type field.

**Independent Test**: Navigate to /app/radiology-centers, create a center with AR+EN names, scan type, and location, see it in the list, edit it, toggle status, delete and restore it.

**Covers**: US2 (Radiology Centers CRUD), US3 (bilingual form fields in centers), US4 (translations in centers views), US5 (bulk actions in centers), US6 (dependent dropdown in centers form).

### Implementation for User Story 2

- [x] T014 [US2] Create `Modules/RadiologyLab/Http/Controllers/Backend/RadiologyCentersController.php` — same pattern as LabsController but uses RadiologyCenter model and RadiologyCenterFormRequest. DataTable adds scan_type column. Store method handles scan_type field additionally. Translation keys use radiology_center_* prefixed messages.
- [x] T015 [P] [US2] Create `Modules/RadiologyLab/Resources/views/backend/radiology-centers/datatable/action_column.blade.php` — same pattern as labs action_column but uses $radiologyCenter variable, edit_radiology_centers/delete_radiology_centers permissions, backend.radiology-centers.* routes.
- [x] T016 [US2] Create `Modules/RadiologyLab/Resources/views/backend/radiology-centers/index.blade.php` — same as labs index but with additional scan_type column in DataTable, uses radiology_center_plural title, add_radiology_centers permission gate, backend.radiology-centers.* routes.
- [x] T017 [US2] Create `Modules/RadiologyLab/Resources/views/backend/radiology-centers/create.blade.php` — same as labs create form plus scan_type text input field (nullable, placeholder from translations). Uses RadiologyCenterFormRequest validation. All radiology center specific translations.

**Checkpoint**: Radiology Centers CRUD fully functional — identical to Labs plus scan_type field works correctly. All UI text translated.

---

## Phase 5: Tests

**Purpose**: Feature tests covering CRUD operations, permission enforcement, and bilingual data for both entities.

- [x] T018 [P] [US1] Create `Modules/RadiologyLab/Tests/Feature/LabsCrudTest.php` — test admin can view labs index (200), test admin can create lab with name_ar + name_en (assert DB has both values), test validation rejects missing name_ar (422/redirect with errors), test validation rejects missing name_en, test admin can edit existing lab (assert updated values), test admin can soft-delete lab (assert soft deleted), test admin can toggle lab status via update_status route, test non-admin user gets 403 on labs index, test lab name displays correctly based on locale (set app locale to ar, check name accessor returns name_ar). Use RefreshDatabase, create admin user with permissions via seeder or factory.
- [x] T019 [P] [US2] Create `Modules/RadiologyLab/Tests/Feature/RadiologyCentersCrudTest.php` — same test cases as LabsCrudTest adapted for radiology centers. Additional: test scan_type is stored and returned correctly, test scan_type is nullable (can create center without it).

**Checkpoint**: `php artisan test --filter=LabsCrudTest` and `php artisan test --filter=RadiologyCentersCrudTest` both pass.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final verification and code quality.

- [x] T020 Run `./vendor/bin/pint` to fix code style across all new files
- [x] T021 Run `php artisan optimize:clear` to clear all caches
- [ ] T022 Run `php artisan test` to verify all tests pass (including existing tests)
- [x] T023 Verify routes with `php artisan route:list --path=labs` and `php artisan route:list --path=radiology-centers`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Foundational (Phase 2)**: No dependencies — can start immediately. BLOCKS all user stories.
- **US1 Labs (Phase 3)**: Depends on Phase 2 completion.
- **US2 Radiology Centers (Phase 4)**: Depends on Phase 2 completion. Can run in parallel with US1 (different files), but recommended after US1 since the pattern is identical and US1 serves as the reference.
- **Tests (Phase 5)**: Depends on Phase 3 and Phase 4 completion.
- **Polish (Phase 6)**: Depends on all phases complete.

### User Story Dependencies

- **US1 (Labs — P1)**: Can start after Phase 2. No dependencies on other stories.
- **US2 (Radiology Centers — P1)**: Can start after Phase 2. No strict dependency on US1, but US1's controller/views serve as the template to copy from.
- **US3 (Bilingual Data Entry)**: Fulfilled within US1 (T013) and US2 (T017) — form fields with name_ar/name_en inputs.
- **US4 (Bilingual UI Translation)**: Fulfilled within Phase 2 (T002-T005) and used by all views in US1/US2.
- **US5 (Bulk Actions)**: Fulfilled within US1 (T010, T012) and US2 (T014, T016) — controller bulk_action method + index view quick-action dropdown.
- **US6 (Dependent Dropdown)**: Fulfilled within US1 (T013) and US2 (T017) — jQuery AJAX on governorate change.

### Within Each User Story

- Controller before views (views reference controller routes)
- Action column can be parallel with controller (different file)
- Index view before create view (index is the landing page)

### Parallel Opportunities

**Phase 2** (all [P] tasks can run in parallel):
- T002, T003, T004, T005 (different translation files)
- T006, T007 (different request classes)

**Phase 3+4** (US1 and US2 can run in parallel if desired):
- T011 parallel with T010 (different files)
- T015 parallel with T014 (different files)

**Phase 5** (tests can run in parallel):
- T018, T019 (different test files)

---

## Parallel Example: Phase 2 (Foundational)

```bash
# Launch all translation tasks together:
Task: "Add EN translations to lang/en/radiology_lab.php"           # T002
Task: "Add AR translations to lang/ar/radiology_lab.php"           # T003
Task: "Add EN sidebar keys to lang/en/sidebar.php"                 # T004
Task: "Add AR sidebar keys to lang/ar/sidebar.php"                 # T005

# Launch both form requests together:
Task: "Create LabFormRequest in Modules/RadiologyLab/Http/Requests/LabFormRequest.php"                     # T006
Task: "Create RadiologyCenterFormRequest in Modules/RadiologyLab/Http/Requests/RadiologyCenterFormRequest.php"  # T007
```

## Parallel Example: Phase 5 (Tests)

```bash
# Launch both test files together:
Task: "Create LabsCrudTest in Modules/RadiologyLab/Tests/Feature/LabsCrudTest.php"                         # T018
Task: "Create RadiologyCentersCrudTest in Modules/RadiologyLab/Tests/Feature/RadiologyCentersCrudTest.php" # T019
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 2: Foundational (permissions, translations, validation, routes, sidebar)
2. Complete Phase 3: User Story 1 — Labs CRUD
3. **STOP and VALIDATE**: Navigate to /app/labs, test full CRUD cycle in both locales
4. Deploy/demo if ready — admins can manage labs

### Incremental Delivery

1. Complete Phase 2 → Foundation ready
2. Add US1 (Labs) → Test independently → Deploy (MVP — labs manageable)
3. Add US2 (Radiology Centers) → Test independently → Deploy (full feature)
4. Add Tests → Verify all pass → Deploy (production-ready)
5. Polish → Code style, cache clear, final verification

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- US3, US4, US5, US6 are cross-cutting and fulfilled within US1/US2 tasks
- Reference controller: `Modules/RadiologyLab/Http/Controllers/Backend/LabTestCategoriesController.php`
- Reference views: `Modules/RadiologyLab/Resources/views/backend/categories/`
- Location API for dropdown: `GET /api/v1/governorates` and `GET /api/v1/cities?governorate_id=X`
- Commit after each task or logical group
- Stop at any checkpoint to validate independently
