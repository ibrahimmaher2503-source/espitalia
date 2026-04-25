# Tasks: Nurse Admin Bilingual

**Input**: Design documents from `specs/012-nurse-admin-bilingual/`
**Prerequisites**: plan.md, spec.md, data-model.md, research.md, contracts/translations.md

**Tests**: Constitution Principle IV requires PHPUnit tests for new features. Test tasks are included in Phase 6.

**Organization**: US1 (Bilingual Nurse Fields) is the foundation. US4 (Location Dropdowns) is tightly coupled to US1 (same form). US2 (Nurse Request Bilingual) is independent. US3 (API) and US5 (DataTable) depend on Phase 2 foundation.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies on incomplete tasks)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4, US5)
- Exact file paths included in all descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project initialization needed — module already exists. Skip to foundational.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Database migrations, model accessors, translations. BLOCKS all user stories.

- [X] T001 [P] Create migration `Modules/RequestNurse/database/migrations/2026_04_05_100001_make_nurse_fields_bilingual.php` — add 8 new columns (specialization_ar string 255 nullable, specialization_en string 255 nullable, experience_ar string 255 nullable, experience_en string 255 nullable, about_ar text nullable, about_en text nullable, service_area_ar string 255 nullable, service_area_en string 255 nullable). Copy existing data: UPDATE nurses SET specialization_ar = specialization, specialization_en = specialization (same for experience, about, service_area). Then drop old columns (specialization, experience, about, service_area). down() reverses: add old columns, copy _en back, drop _ar/_en pairs.
- [X] T002 [P] Create migration `Modules/RequestNurse/database/migrations/2026_04_05_100002_make_nurse_request_fields_bilingual.php` — add 2 new columns (service_description_ar text nullable, service_description_en text nullable). Copy: UPDATE nurse_requests SET service_description_ar = service_description, service_description_en = service_description. Drop old service_description column. down() reverses.
- [X] T003 Update `Modules/RequestNurse/Models/Nurse.php` — replace $fillable entries: remove specialization, experience, about, service_area; add specialization_ar, specialization_en, experience_ar, experience_en, about_ar, about_en, service_area_ar, service_area_en. Add 4 locale-aware accessors with fallback (Doctor model pattern): getSpecializationAttribute(), getExperienceAttribute(), getAboutAttribute(), getServiceAreaAttribute(). Each checks app()->getLocale(), returns _ar or _en, falls back to other language if empty.
- [X] T004 Update `Modules/RequestNurse/Models/NurseRequest.php` — replace service_description in $fillable with service_description_ar, service_description_en. Add getServiceDescriptionAttribute() accessor with locale-aware fallback pattern.
- [X] T005 [P] Add English translation keys to `lang/en/request_nurse.php` per contracts/translations.md — ~20 keys: lbl_specialization_ar, lbl_specialization_en, lbl_experience_ar, lbl_experience_en, lbl_about_ar, lbl_about_en, lbl_service_area_ar, lbl_service_area_en, lbl_service_description_ar, lbl_service_description_en, lbl_governorate, lbl_city, lbl_select_governorate, lbl_select_city, lbl_location, lbl_no_cities, validation_at_least_one_language.
- [X] T006 [P] Add Arabic translation keys to `lang/ar/request_nurse.php` per contracts/translations.md — matching Arabic translations for all keys in T005.

**Checkpoint**: Run `php artisan migrate`, confirm new columns exist in nurses and nurse_requests tables. Verify Nurse model accessors return correct locale in tinker. Translation keys load correctly.

---

## Phase 3: User Story 1 + 4 — Admin Manages Nurses with Bilingual Details + Location (Priority: P1+P2)

**Goal**: Admin creates/edits nurses with paired AR/EN text inputs and governorate/city cascading dropdowns. All bilingual fields and location data persist correctly.

**Independent Test**: Admin opens nurse create form → sees paired AR/EN inputs for specialization, experience, about, service_area → sees governorate/city dropdowns → fills all fields → saves → verifies all data stored. Edit pre-populates all fields correctly.

### Implementation for User Stories 1 + 4

- [X] T007 [US1] Update `Modules/RequestNurse/Http/Requests/NurseFormRequest.php` — replace specialization rule with: specialization_ar (nullable|string|max:255), specialization_en (nullable|string|max:255). Same for experience_ar/en, about_ar/en (max:2000), service_area_ar/en. Add custom withValidator() rule: for each pair, at least one of _ar or _en must be non-empty. Add governorate_id (nullable|exists:governorates,id) and city_id (nullable|exists:governorate_cities,id). Remove old single-field rules.
- [X] T008 [US1] Update `Modules/RequestNurse/Resources/views/backend/nurses/create.blade.php` — in Professional Details card: replace single specialization input with row of 2 cols (specialization_ar input RTL + specialization_en input LTR). Same pattern for experience, service_area. Replace single about textarea with row of 2 textareas (about_ar RTL + about_en LTR). Add Location card (or section within Professional Details): Governorate Select2 dropdown populated from $governorates, City Select2 dropdown. Add jQuery AJAX cascade script: on governorate change, fetch cities from /api/v1/cities?governorate_id={id}, populate city dropdown. On edit: pre-select governorate and load its cities, pre-select city.
- [X] T009 [US4] Update `Modules/RequestNurse/Http/Controllers/Backend/NursesController.php` — in create(): load $governorates = Governorate::all() and pass to view. In edit(): load $governorates and $cities (filtered by nurse's governorate_id) and pass to view. In store(): add governorate_id, city_id to the nurse update data alongside the new bilingual field names (specialization_ar, specialization_en, etc.). In update(): same. Import Modules\Location\Models\Governorate and GovernorateCity.

**Checkpoint**: Admin can create a nurse with bilingual fields and location. Edit form pre-populates all values. Cascade dropdown works.

---

## Phase 4: User Story 2 — Nurse Request Bilingual Service Description (Priority: P2)

**Goal**: Nurse request create form supports bilingual service description inputs.

**Independent Test**: Admin creates a nurse request with both Arabic and English service descriptions → both are saved → admin detail page shows both → API returns locale-appropriate version.

### Implementation for User Story 2

- [X] T010 [US2] Update `Modules/RequestNurse/Http/Requests/NurseRequestFormRequest.php` — replace service_description rule with: service_description_ar (nullable|string|max:2000), service_description_en (nullable|string|max:2000). Add custom validation: at least one must be non-empty.
- [X] T011 [US2] Update `Modules/RequestNurse/Resources/views/backend/requests/create.blade.php` — replace single service_description textarea with row of 2 textareas (service_description_ar RTL + service_description_en LTR). Pre-populate on edit if applicable.
- [X] T012 [US2] Update `Modules/RequestNurse/Http/Controllers/Backend/NurseRequestsController.php` — in store() and any update methods: use new field names service_description_ar, service_description_en instead of old service_description.

**Checkpoint**: Create a nurse request with bilingual service descriptions. Both values persist in database.

---

## Phase 5: User Story 3 — API Endpoints Return Bilingual Nurse Data (Priority: P2)

**Goal**: API resources return locale-aware text fields and nested location data. Search matches both languages.

**Independent Test**: Call GET /api/nurses/search with Accept-Language: ar → Arabic specialization/about returned. Call with en → English returned. Search by Arabic term finds nurse. Search by English term finds same nurse.

### Implementation for User Story 3

- [X] T013 [P] [US3] Update `Modules/RequestNurse/Transformers/NurseResource.php` — add governorate and city nesting: 'governorate' => $this->governorate ? ['id' => $this->governorate->id, 'name' => $this->governorate->name] : null, 'city' => $this->city ? ['id' => $this->city->id, 'name' => $this->city->name] : null. Bilingual text fields (specialization, experience, about, service_area) already work via model accessors — no changes needed for those.
- [X] T014 [P] [US3] Verify `Modules/RequestNurse/Transformers/NurseSearchResource.php` — confirm it uses $this->specialization (accessor-based) not $this->specialization_ar directly. Governorate/city nesting already exists. No changes expected unless field names are hardcoded.
- [X] T015 [US3] Update `Modules/RequestNurse/Services/NurseSearchService.php` — change specialization search filter from: where('specialization', 'LIKE', "%$search%") to: where(function($q) use ($search) { $q->where('specialization_ar', 'LIKE', "%$search%")->orWhere('specialization_en', 'LIKE', "%$search%"); }). Same for any other text field used in search.

**Checkpoint**: API returns correct language per Accept-Language header. Search finds nurses by both Arabic and English terms.

---

## Phase 6: User Story 5 — Admin DataTable + Polish (Priority: P3)

**Goal**: DataTable shows locale-aware specialization and location column. Code style clean. Tests pass.

### Implementation for User Story 5

- [X] T016 [US5] Update `Modules/RequestNurse/Resources/views/backend/nurses/index.blade.php` — in DataTable columns: specialization column should use the model accessor (already returns locale-appropriate value if DataTable uses Eloquent). Add location column showing governorate.name + ' - ' + city.name (locale-aware via accessor). Ensure index_data() in controller eager-loads governorate and city relationships.
- [X] T017 [US5] Update `Modules/RequestNurse/Http/Controllers/Backend/NursesController.php` index_data() — add ->with('governorate', 'city') to the query to eager-load location relationships. Ensure the specialization column in the DataTable response uses the accessor (not the raw _ar/_en columns).

### Tests

- [X] T018 [P] Create `Modules/RequestNurse/Tests/Feature/NurseBilingualTest.php` — test cases: (1) Nurse accessor returns Arabic when locale is ar, (2) Nurse accessor returns English when locale is en, (3) Nurse accessor falls back to other language when one is empty, (4) NurseRequest accessor returns correct locale for service_description, (5) NurseFormRequest validation passes when only one language provided, (6) NurseFormRequest validation fails when both languages empty for a field, (7) Nurse store saves governorate_id and city_id correctly, (8) NurseSearchService finds nurse by Arabic specialization, (9) NurseSearchService finds nurse by English specialization.

### Polish

- [X] T019 Run `./vendor/bin/pint Modules/RequestNurse` to fix code style across all modified files
- [X] T020 Run `php artisan optimize:clear` to clear all caches
- [X] T021 Run `php artisan test --filter=NurseBilingual` to verify all tests pass

**Checkpoint**: DataTable shows bilingual specialization and location. All tests pass. Code style clean.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Foundational (Phase 2)**: No dependencies — start immediately. BLOCKS all user stories.
- **US1+US4 Admin Form (Phase 3)**: Depends on Phase 2 (needs bilingual columns and accessors).
- **US2 Nurse Request (Phase 4)**: Depends on Phase 2 only. Independent of Phase 3.
- **US3 API + Search (Phase 5)**: Depends on Phase 2 (needs bilingual columns). Can run in parallel with Phases 3-4.
- **US5 DataTable + Polish (Phase 6)**: Depends on Phase 3 (needs controller changes for eager loading).

### User Story Dependencies

```
Phase 2 (Foundational)
    ├──→ Phase 3 (US1+US4: Admin Form + Location)
    │         └──→ Phase 6 (US5: DataTable + Polish)
    ├──→ Phase 4 (US2: Nurse Request Bilingual) [independent]
    └──→ Phase 5 (US3: API + Search) [independent]
```

### Within Each Phase

- Validation (T007) before view (T008) — view references validation field names
- View (T008) before controller (T009) — verify templates exist before wiring

### Parallel Opportunities

**Phase 2** (all [P] tasks can run in parallel):
- T001, T002 (different migration files, different tables)
- T005, T006 (different translation files)

**Phase 4 + Phase 5** can run in parallel (independent user stories):
- T010-T012 (nurse request bilingual)
- T013-T015 (API + search bilingual)

**Phase 6**:
- T018 (tests) can start as soon as Phase 2 is done (doesn't depend on Phase 3 views)

---

## Implementation Strategy

### MVP First (US1 + US4)

1. Complete Phase 2: Foundational (migrations, models, translations)
2. Complete Phase 3: US1+US4 (admin form with bilingual inputs and location dropdowns)
3. **STOP and VALIDATE**: Admin can create/edit nurses with bilingual fields and location
4. Deploy/demo if ready — core bilingual nurse management works

### Incremental Delivery

1. Phase 2 → Foundation ready (migrations + models + translations)
2. Add US1+US4 (Admin Form) → Test independently → Deploy (MVP)
3. Add US2 (Nurse Request) + US3 (API) → Test independently → Deploy (full bilingual)
4. Add US5 (DataTable) → Test independently → Deploy (polished admin)
5. Tests + Polish → Verify all pass → Deploy (production-ready)

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- US1 and US4 combined in Phase 3 because they modify the same form and controller
- US2 and US3 are independent of each other and of Phase 3
- Reference model: `Modules/Clinic/Models/Doctor.php` (bilingual accessor with fallback)
- Reference view: `Modules/RadiologyLab/Resources/views/backend/labs/create.blade.php` (cascading dropdown)
- Existing governorate/city API: `GET /api/v1/cities?governorate_id={id}` already works
- Bilingual text fields in API resources require NO changes — accessors handle locale transparently
