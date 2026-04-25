# Tasks: Specialization Taxonomy Refactor

**Input**: Design documents from `/specs/022-specialization-taxonomy/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Included per Constitution Principle IV (Test Discipline).

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Module root**: `Modules/Specialization/`
- **Existing modules**: `Modules/Clinic/`, `Modules/RequestNurse/`, `Modules/Frontend/`, `Modules/QuickBooking/`
- **Core**: `app/`, `lang/en/`
- **Tests**: `Modules/Specialization/Tests/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Scaffold module and configure project for specialization feature

- [X] T001 Scaffold Specialization module via `php artisan module:make Specialization`
- [X] T002 Enable module in `modules_statuses.json` (add `"Specialization": true`)
- [X] T003 [P] Create translation file at `lang/en/specialization.php` with keys: `lbl_name_ar`, `lbl_name_en`, `lbl_description_ar`, `lbl_description_en`, `lbl_parent`, `lbl_icon`, `lbl_status`, `lbl_specialization`, `lbl_specializations`, `lbl_select_specialization`, `lbl_no_results`, `lbl_legacy_expert`, `lbl_legacy_specialization`
- [X] T004 [P] Add sidebar translation key `lbl_specializations` to `lang/en/sidebar.php`

**Checkpoint**: Module scaffold exists and is enabled. Translation keys registered.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Database schema and model that ALL user stories depend on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T005 Create `specializations` table migration at `Modules/Specialization/database/migrations/*_create_specializations_table.php` with columns: id, name_ar, name_en, description_ar, description_en, slug (unique), parent_id (FK self, ON DELETE RESTRICT), icon, status (tinyint default 1), created_by, updated_by, deleted_by, timestamps, soft deletes. Add indexes on parent_id, status, deleted_at.
- [X] T006 Create Specialization model at `Modules/Specialization/Models/Specialization.php` extending BaseModel with HasSlug trait, fillable array, bilingual accessors (getNameAttribute, getDescriptionAttribute with locale fallback), relationships: parent() belongsTo self, children() hasMany self, doctors() belongsToMany Doctor, nurses() belongsToMany Nurse. Add scopes: scopeActive, scopeTopLevel (whereNull parent_id).
- [X] T007 Create permission seeder at `Modules/Specialization/database/seeders/SpecializationPermissionSeeder.php` with permissions: view_specialization, add_specialization, edit_specialization, delete_specialization. Assign all 4 to admin role. Assign view, add, edit (not delete) to demo_admin role.
- [X] T008 Run migrations and seeder: `php artisan module:migrate Specialization && php artisan module:seed Specialization`
- [X] T009 Verify setup: `php artisan module:list` shows Specialization enabled, `php artisan optimize:clear`

**Checkpoint**: Foundation ready — specializations table exists, model works, permissions seeded. User story implementation can begin.

---

## Phase 3: User Story 1 — Admin Manages Specializations (Priority: P1) 🎯 MVP

**Goal**: Admin can perform full CRUD on specializations via DataTable with hierarchy, bilingual fields, icons, status, and bulk actions.

**Independent Test**: Log in as admin → navigate to Specializations → create/edit/delete specializations, toggle status, bulk actions. Verify hierarchy (parent/child) and soft delete constraints.

### Tests for User Story 1

- [X] T010 [P] [US1] Create model unit test at `Modules/Specialization/Tests/Unit/SpecializationModelTest.php`: test bilingual accessors (ar/en with fallback), parent/children relationships, scopeActive, scopeTopLevel, HasSlug generates slug from name_en, soft delete works
- [X] T011 [P] [US1] Create CRUD feature test at `Modules/Specialization/Tests/Feature/SpecializationCrudTest.php`: test index returns DataTable view, store creates specialization, edit loads existing, update modifies, destroy soft-deletes, update_status toggles, bulk_action works. Test hierarchy constraint: destroy returns error when specialization has active children. Test permission: unauthenticated user redirected, unauthorized role blocked.

### Implementation for User Story 1

- [X] T012 [P] [US1] Create admin controller at `Modules/Specialization/Http/Controllers/SpecializationController.php` with standard method set: index (view with stats), index_data (DataTable JSON with columns: check, name, parent name, status badge, updated_at, action), create (form view), store (validate + create), edit (return data for form), update (validate + update), destroy (block if active children exist, else soft-delete), bulk_action (delete/restore/change-status/permanently-delete), update_status (toggle 0↔1). Set `$this->module_title` and `$this->module_name` in constructor.
- [X] T013 [P] [US1] Create index view at `Modules/Specialization/Resources/views/backend/specializations/index.blade.php` extending `backend.layouts.app` with DataTable listing, search, pagination, status filter tabs (All/Active/Inactive/Trashed), bulk action toolbar, add button gated by `add_specialization` permission
- [X] T014 [P] [US1] Create create/edit form view at `Modules/Specialization/Resources/views/backend/specializations/create.blade.php` extending `backend.layouts.app` with bilingual inputs (name_ar RTL, name_en, description_ar RTL, description_en), parent_id Select2 dropdown (populated from active top-level + children specializations, exclude self on edit), icon text input (Phosphor class), status toggle. Form submits to store (create) or update (edit) route.
- [X] T015 [P] [US1] Create action column partial at `Modules/Specialization/Resources/views/backend/specializations/datatable/action_column.blade.php` with edit and delete buttons gated by `edit_specialization` and `delete_specialization` permissions
- [X] T016 [US1] Register admin routes at `Modules/Specialization/Routes/web.php`: Route group with prefix `app/`, as `backend.`, middleware `['auth', 'auth_check']`. Inside: resource route for specializations, plus named routes for index_data (GET), bulk_action (POST), update_status (POST `update-status/{id}`)
- [X] T017 [US1] Add sidebar entry in `app/Http/Middleware/GenerateMenus.php`: add `mainRoute()` call for Specializations under the System static section, with icon `ph ph-stethoscope`, title `__('sidebar.lbl_specializations')`, route `backend.specializations.index`, permission `view_specialization`. Place it near other master-data items (Blog, FAQ, etc.).
- [ ] T018 [US1] Build frontend assets: `npm run dev` to compile module JS/CSS. Verify DataTable, Select2, and form interactions work.
- [X] T019 [US1] Run tests: `php artisan test Modules/Specialization/Tests` — all must pass

**Checkpoint**: Specialization CRUD fully functional. Admin can manage the taxonomy independently. This is the MVP.

---

## Phase 4: User Story 2 — Admin Assigns Specializations to Doctors (Priority: P2)

**Goal**: Admin can assign multiple specializations to a doctor via a searchable multi-select on the doctor edit form. Legacy `expert` field shown as read-only.

**Independent Test**: Edit a doctor → assign specializations → save → reopen → verify specializations persisted. Verify legacy expert field is displayed read-only.

**Dependencies**: Requires Phase 2 (specializations table exists) and Phase 3 (specializations created).

### Tests for User Story 2

- [X] T020 [P] [US2] Create doctor-specialization test at `Modules/Specialization/Tests/Feature/DoctorSpecializationTest.php`: test Doctor model has specializations() relationship, test sync specializations on doctor update (add, remove, replace), test edit returns assigned specialization IDs, test specializations persisted after save

### Implementation for User Story 2

- [X] T021 [US2] Create `doctor_specialization` pivot migration at `Modules/Specialization/database/migrations/*_create_doctor_specialization_table.php` with composite primary key (specialization_id, doctor_id), foreign keys with CASCADE on delete, timestamps. Run migration.
- [X] T022 [US2] Add `specializations()` belongsToMany relationship to `Modules/Clinic/Models/Doctor.php`: `return $this->belongsToMany(Specialization::class, 'doctor_specialization')->withTimestamps();`
- [X] T023 [US2] Modify `Modules/Clinic/Http/Controllers/DoctorController.php` edit method (~line 805): load doctor's specialization IDs and return in the JSON response as `specialization_ids`. Load all active specializations as `specializations_list` (id, name_ar, name_en) for the dropdown.
- [X] T024 [US2] Modify `Modules/Clinic/Http/Controllers/DoctorController.php` update method (~line 877): after existing relationship sync logic, add `$doctor->specializations()->sync($request->specialization_ids ?? []);`
- [X] T025 [US2] Modify `Modules/Clinic/Resources/assets/js/component/DoctorOffcanvas.vue`: add a Multiselect component for specializations (using existing vue-multiselect pattern from clinics/services) populated from `specializations_list`. Place it in the Professional Details section above the Expert field. Make the Expert TranslatableInput read-only (add `disabled` attribute). Bind to `specialization_ids` array.
- [ ] T026 [US2] Build frontend assets: `npm run dev`. Test the doctor edit form.
- [X] T027 [US2] Run tests: `php artisan test --filter=DoctorSpecialization` — all must pass

**Checkpoint**: Doctors can be linked to specializations. Legacy expert field preserved as read-only.

---

## Phase 5: User Story 3 — Admin Assigns Specializations to Nurses (Priority: P2)

**Goal**: Admin can assign multiple specializations to a nurse via a searchable multi-select. Legacy text fields shown as read-only. Artisan command migrates existing free-text to pivot records.

**Independent Test**: Edit a nurse → assign specializations → save → reopen → verify persisted. Run migration command → verify exact matches created pivot records.

**Dependencies**: Requires Phase 2 (specializations table exists) and Phase 3 (specializations created).

### Tests for User Story 3

- [X] T028 [P] [US3] Create nurse-specialization test at `Modules/Specialization/Tests/Feature/NurseSpecializationTest.php`: test Nurse model has specializations() relationship, test sync on nurse update, test edit returns assigned specialization IDs
- [X] T029 [P] [US3] Create migration command test at `Modules/Specialization/Tests/Feature/MigrateNurseSpecializationsTest.php`: test exact match creates pivot record, test case-insensitive match works, test no-match flags record for review, test already-migrated nurse is not duplicated

### Implementation for User Story 3

- [X] T030 [US3] Create `nurse_specialization` pivot migration at `Modules/Specialization/database/migrations/*_create_nurse_specialization_table.php` with composite primary key (specialization_id, nurse_id), foreign keys with CASCADE on delete, timestamps. Run migration.
- [X] T031 [US3] Add `specializations()` belongsToMany relationship to `Modules/RequestNurse/Models/Nurse.php`: `return $this->belongsToMany(Specialization::class, 'nurse_specialization')->withTimestamps();`
- [X] T032 [US3] Modify `Modules/RequestNurse/Http/Controllers/Backend/NursesController.php` store method: after nurse creation/update, add `$nurse->specializations()->sync($request->specialization_ids ?? []);`. In the create method (when loading edit data), return assigned specialization IDs and active specializations list.
- [X] T033 [US3] Modify `Modules/RequestNurse/Resources/views/backend/nurses/create.blade.php`: add a Select2 multi-select for specializations in the Professional Details section, populated from active specializations passed by controller. Make `specialization_ar` and `specialization_en` text inputs read-only (add `readonly` attribute and muted styling). Pre-select assigned specializations on edit.
- [X] T034 [US3] Create artisan command at `Modules/Specialization/Console/Commands/MigrateNurseSpecializations.php`: register as `specialization:migrate-nurses`. Logic: load all specializations into lookup maps (lowercase name_ar → id, lowercase name_en → id), iterate nurses with non-null specialization text, exact case-insensitive match against lookup, create pivot records for matches via `syncWithoutDetaching`, log unmatched to console. Output summary: X matched, Y unmatched.
- [X] T035 [US3] Register command in `Modules/Specialization/Providers/SpecializationServiceProvider.php` commands array
- [ ] T036 [US3] Build frontend assets: `npm run dev`. Test the nurse edit form.
- [X] T037 [US3] Run tests: `php artisan test --filter=NurseSpecialization` and `php artisan test --filter=MigrateNurseSpecializations` — all must pass

**Checkpoint**: Nurses can be linked to specializations. Migration command converts free-text to structured data.

---

## Phase 6: User Story 4 — Public User Filters Doctors by Specialization (Priority: P3)

**Goal**: Public doctors listing page has a specialization filter (Select2 multi-select) that narrows results via JOIN-based queries. Specialization names shown on doctor cards.

**Independent Test**: Visit /doctors → select a specialization filter → verify only doctors linked to that specialization appear. Combine with city/clinic filters. Verify doctor cards show specialization names.

**Dependencies**: Requires Phase 4 (doctors have specializations assigned).

### Implementation for User Story 4

- [X] T038 [US4] Modify `Modules/Frontend/Http/Controllers/DoctorController.php` `doctorsList()` method: query active specializations and pass to view as `$specializations` (id, name)
- [X] T039 [US4] Modify `Modules/Frontend/Http/Controllers/DoctorController.php` `index_data()` method (~line 29): add filter for `specialization_ids` parameter — `$doctor_list->whereHas('specializations', fn($q) => $q->whereIn('specializations.id', $specialization_ids))`. Eager-load specializations relationship for card display.
- [X] T040 [US4] Modify `Modules/Frontend/Resources/views/doctors.blade.php`: add a Select2 multi-select dropdown for specializations (populated from `$specializations` Blade variable) in the filter section alongside existing clinic/service/rating filters. Wire to advanceFilter AJAX callback as `specialization_ids[]`. Display assigned specialization names as badges on each doctor card.
- [ ] T041 [US4] Build frontend assets: `npm run dev`. Test filter interactions on the public doctors page.

**Checkpoint**: Public doctor filtering by specialization works. Cards show specialization badges.

---

## Phase 7: User Story 5 — Public User Filters Nurses by Specialization (Priority: P3)

**Goal**: Public nurses listing replaces hardcoded specialization dropdown with data-driven Select2 from specializations table. Filter uses JOIN instead of LIKE.

**Independent Test**: Visit /available-nurses → select a specialization → verify only matching nurses appear. Verify nurses with only legacy text still appear via fallback.

**Dependencies**: Requires Phase 5 (nurses have specializations assigned).

### Implementation for User Story 5

- [X] T042 [US5] Modify `Modules/Frontend/Http/Controllers/NurseRequestFrontController.php` `nursesList()` method: query active specializations and pass to view as `$specializations`
- [X] T043 [US5] Modify `Modules/Frontend/Http/Controllers/NurseRequestFrontController.php` `nurse_index_data()` method (~line 22): replace the existing `LIKE` specialization filter with a dual filter: primary — `whereHas('specializations', fn($q) => $q->whereIn('specializations.id', $ids))`; fallback — `orWhere(fn($q) => $q->where('specialization_ar', 'LIKE', ...)->orWhere('specialization_en', 'LIKE', ...))` for nurses not yet migrated. Eager-load specializations for card display.
- [X] T044 [US5] Modify `Modules/Frontend/Resources/views/available_nurses.blade.php`: replace hardcoded specialization `<option>` list with dynamic options from `$specializations` Blade variable. Change filter to send `specialization_ids[]` array instead of single text value. Display specialization badges on nurse cards.
- [ ] T045 [US5] Build frontend assets: `npm run dev`. Test filter on the public nurses page.

**Checkpoint**: Public nurse filtering uses structured data. Backwards compatibility with legacy text preserved.

---

## Phase 8: User Story 6 — QuickBooking with Optional Specialization Step (Priority: P4)

**Goal**: QuickBooking wizard gains an optional first step for specialization selection. If selected, downstream steps filter by specialization. If skipped, existing flow preserved.

**Independent Test**: Open QuickBooking → select a specialization → verify clinics/doctors filtered. Also test skipping → verify original flow unchanged.

**Dependencies**: Requires Phase 4 (doctor-specialization links exist).

### Implementation for User Story 6

- [X] T046 [US6] Add `specialization_id` state property to Pinia store at `Modules/QuickBooking/Resources/assets/js/store/quick-booking.js` with default null. Add reset logic in the reset action.
- [X] T047 [US6] Create `SelectSpecialization.vue` component at `Modules/QuickBooking/Resources/assets/js/quick-booking/SelectSpecialization.vue`: fetch active specializations via API or inline Blade data, render as searchable select with a "Skip" button. On select → store specialization_id in Pinia and advance. On skip → set specialization_id to null and advance.
- [X] T048 [US6] Modify `Modules/QuickBooking/Resources/assets/js/quick-booking/QuickBooking.vue`: insert specialization step as first entry in `setupArray` for both single-vendor and multi-vendor flows. Adjust step numbering.
- [X] T049 [US6] Modify `Modules/QuickBooking/Resources/assets/js/quick-booking/TabPanel.vue`: add case for `'select-specialization'` that renders `SelectSpecialization` component
- [X] T050 [US6] Modify downstream step components (SelectBranch, SelectEmployee) to filter by `specialization_id` from store: when specialization_id is set, pass it as query param to the API/AJAX calls that fetch clinics and doctors
- [ ] T051 [US6] Build frontend assets: `npm run dev`. Test both flows (with and without specialization selection).

**Checkpoint**: QuickBooking optionally filters by specialization without breaking existing paths.

---

## Phase 9: User Story 7 — Public API for Specializations (Priority: P4)

**Goal**: REST API endpoints for listing specializations (with counts), retrieving doctors/nurses by specialization, and filtering existing endpoints by specialization_ids[].

**Independent Test**: Call `GET /api/v1/specializations` → verify paginated list with counts. Call `GET /api/v1/specializations/{id}/doctors` → verify doctor list. Verify legacy `specialization` string still present in nurse API responses.

**Dependencies**: Requires Phases 4 and 5 (pivots exist).

### Tests for User Story 7

- [X] T052 [P] [US7] Create API feature test at `Modules/Specialization/Tests/Feature/SpecializationApiTest.php`: test GET /api/v1/specializations returns paginated list with doctors_count and nurses_count, test search filter, test GET /api/v1/specializations/{id} returns single, test GET /api/v1/specializations/{id}/doctors returns paginated doctors, test GET /api/v1/specializations/{id}/nurses returns paginated nurses, test 404 for non-existent ID, test auth required (401 without token)

### Implementation for User Story 7

- [X] T053 [US7] Create API resource at `Modules/Specialization/Transformers/SpecializationResource.php`: return id, name, name_ar, name_en, description, description_ar, description_en, slug, parent_id, parent (nested if loaded), icon, status, doctors_count, nurses_count, children (nested collection if loaded)
- [X] T054 [US7] Create API controller at `Modules/Specialization/Http/Controllers/API/SpecializationController.php` with methods: index (paginated list with withCount doctors/nurses, search by name_ar/name_en, filter by status/parent_id), show (single with counts), doctors (paginated doctors for specialization via pivot), nurses (paginated nurses for specialization via pivot)
- [X] T055 [US7] Register API routes at `Modules/Specialization/Routes/api.php`: Route group with middleware `auth:sanctum`, prefix `v1`. Routes: GET `specializations` → index, GET `specializations/{id}` → show, GET `specializations/{id}/doctors` → doctors, GET `specializations/{id}/nurses` → nurses
- [X] T056 [US7] Modify `Modules/Clinic/Transformers/DoctorResource.php`: add `specializations` key returning a collection of `{id, name, slug}` from the doctor's specializations relationship (when loaded)
- [X] T057 [US7] Extend nurse API response in `Modules/Frontend/Http/Controllers/NurseRequestFrontController.php` `nurse_index_data()`: add `specializations` array field (from pivot) alongside existing legacy `specialization` string field for backwards compatibility (FR-017)
- [X] T058 [US7] Add `specialization_ids[]` query parameter filter to `Modules/Clinic/Http/Controllers/API/DoctorController.php` `DoctorList()` method: if `specialization_ids` present, apply `whereHas('specializations', fn($q) => $q->whereIn('specializations.id', $ids))`
- [X] T059 [US7] Run tests: `php artisan test --filter=SpecializationApi` — all must pass

**Checkpoint**: Full API surface available. Backwards-compatible nurse responses preserved.

---

## Phase 10: Polish & Cross-Cutting Concerns

**Purpose**: Code quality, performance verification, and final validation

- [ ] T060 [P] Run code style fixer: `./vendor/bin/pint` — fix any violations
- [ ] T061 [P] Run full test suite: `php artisan test` — all tests pass
- [ ] T062 Clear caches: `php artisan optimize:clear && composer dump-autoload`
- [ ] T063 Verify DataTable performance: seed 500+ specialization records and test admin DataTable responsiveness (SC-008)
- [ ] T064 Manual end-to-end test: walk through all 7 user stories per acceptance scenarios in spec.md
- [ ] T065 Verify backwards compatibility: call nurse API endpoints and confirm legacy `specialization` string field still present (SC-007)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Phase 2 — no other story dependencies — **MVP target**
- **US2 (Phase 4)**: Depends on Phase 2 + Phase 3 (specializations must exist to assign)
- **US3 (Phase 5)**: Depends on Phase 2 + Phase 3 (specializations must exist to assign)
- **US4 (Phase 6)**: Depends on Phase 4 (doctors have specializations)
- **US5 (Phase 7)**: Depends on Phase 5 (nurses have specializations)
- **US6 (Phase 8)**: Depends on Phase 4 (doctor-specialization links for filtering)
- **US7 (Phase 9)**: Depends on Phases 4 and 5 (both pivot tables populated)
- **Polish (Phase 10)**: Depends on all desired stories being complete

### User Story Dependencies

```
Phase 1 (Setup) → Phase 2 (Foundation) → Phase 3 (US1: Admin CRUD) ──┐
                                                                       ├→ Phase 4 (US2: Doctor assign) ──┬→ Phase 6 (US4: Doctor filter)
                                                                       │                                  ├→ Phase 8 (US6: QuickBooking)
                                                                       │                                  └→ Phase 9 (US7: API) ←──┐
                                                                       └→ Phase 5 (US3: Nurse assign) ───┬→ Phase 7 (US5: Nurse filter)
                                                                                                          └────────────────────────┘
```

### Within Each User Story

- Tests written first (where included) → verify they fail
- Migrations before models
- Models before controllers
- Controllers before views
- Backend before frontend assets build
- Tests pass before moving to next story

### Parallel Opportunities

- **Phase 1**: T003 and T004 can run in parallel (different files)
- **Phase 2**: T005, T006, T007 are sequential (migration → model → seeder)
- **Phase 3**: T010+T011 (tests), T012+T013+T014+T015 (controller + 3 views) can run in parallel
- **Phases 4+5**: US2 and US3 can run in parallel after US1 (different modules: Clinic vs RequestNurse)
- **Phases 6+7**: US4 and US5 can run in parallel (different controllers/views)
- **Phases 8+9**: US6 and US7 can run in parallel (QuickBooking vs API — different modules)

---

## Parallel Example: User Story 1

```
# Launch tests in parallel:
Task: T010 "SpecializationModelTest.php" (unit test)
Task: T011 "SpecializationCrudTest.php" (feature test)

# Launch views in parallel (after controller T012):
Task: T013 "index.blade.php"
Task: T014 "create.blade.php"
Task: T015 "action_column.blade.php"
```

## Parallel Example: User Stories 2 + 3

```
# After US1 complete, launch both in parallel:
Agent A: Phase 4 (US2 — Doctor integration in Modules/Clinic)
Agent B: Phase 5 (US3 — Nurse integration in Modules/RequestNurse)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001–T004)
2. Complete Phase 2: Foundational (T005–T009)
3. Complete Phase 3: User Story 1 (T010–T019)
4. **STOP and VALIDATE**: Test specialization CRUD independently
5. Deploy/demo if ready — admin can manage the specialization taxonomy

### Incremental Delivery

1. Setup + Foundation → scaffold ready
2. **US1** → Admin CRUD (MVP!) → validate
3. **US2 + US3** (parallel) → Doctor + Nurse linking → validate
4. **US4 + US5** (parallel) → Public frontend filters → validate
5. **US6 + US7** (parallel) → QuickBooking + API → validate
6. Polish → full test suite, performance, backwards compatibility

### Parallel Team Strategy

With multiple developers after Foundation:

- **Developer A**: US1 → US4 → US6
- **Developer B**: (waits for US1) → US2 + US3 (parallel) → US5 → US7

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks in same phase
- [Story] label maps task to specific user story for traceability
- Each user story should be independently testable at its checkpoint
- Constitution Principle IV: verify tests fail before implementing, pass after
- Commit after each task or logical group
- Run `php artisan optimize:clear` after structural changes (routes, config, providers)
- Total tasks: 65 (4 setup + 5 foundation + 10 US1 + 8 US2 + 10 US3 + 4 US4 + 4 US5 + 6 US6 + 8 US7 + 6 polish)
