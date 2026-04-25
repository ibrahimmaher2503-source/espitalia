# Tasks: Location-Based Healthcare Search API

**Input**: Design documents from `specs/005-location-search-api/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/api-endpoints.md
**Tests**: Included (Constitution Principle IV requires PHPUnit tests for all new features)

**Organization**: Tasks grouped by user story. Each story is independently testable after Phase 2 (Foundational) completes.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2)
- Paths are relative to repository root

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Scaffold new modules and create shared translation files

- [x] T001 Scaffold Location module via `php artisan module:make Location` and verify structure under Modules/Location/
- [x] T002 Scaffold HomeHealthcare module via `php artisan module:make HomeHealthcare` and verify structure under Modules/HomeHealthcare/
- [x] T003 [P] Create English translation file at lang/en/location.php with keys for validation messages (lbl_governorate_required, lbl_invalid_governorate, lbl_invalid_city, etc.)
- [x] T004 [P] Create Arabic translation file at lang/ar/location.php with Arabic translations matching all keys from lang/en/location.php
- [x] T005 Enable both new modules in modules_statuses.json (`"Location": true, "HomeHealthcare": true`)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Locale middleware, Governorate/City data layer — MUST complete before ANY user story

**CRITICAL**: No user story work can begin until this phase is complete

- [x] T006 Modify localization middleware at app/Http/Middleware/localization.php to read `Accept-Language` header first (`ar` → Arabic, anything else → English), fall back to session, then default to `en`
- [x] T007 [P] Create Governorate model at Modules/Location/Models/Governorate.php extending BaseModel with: `$table = 'governorates'`, `$fillable = ['name_ar', 'name_en']`, `getNameAttribute()` accessor returning name_ar or name_en by locale, hasMany relationships to GovernorateCity
- [x] T008 [P] Create GovernorateCity model at Modules/Location/Models/GovernorateCity.php extending BaseModel with: `$table = 'governorate_cities'`, `$fillable = ['governorate_id', 'name_ar', 'name_en']`, `getNameAttribute()` accessor, belongsTo Governorate relationship
- [x] T009 [P] Create migration for governorates table at Modules/Location/database/migrations/ with columns: id (bigIncrements), name_ar (string 100), name_en (string 100), timestamps
- [x] T010 [P] Create migration for governorate_cities table at Modules/Location/database/migrations/ with columns: id (bigIncrements), governorate_id (unsignedBigInteger, FK to governorates with cascade delete, indexed), name_ar (string 100), name_en (string 100), timestamps
- [x] T011 Create GovernorateSeeder at Modules/Location/database/seeders/GovernorateSeeder.php seeding all 27 Egyptian governorates (name_ar + name_en) and 3-10 major cities per governorate
- [x] T012 [P] Create GovernorateResource transformer at Modules/Location/Transformers/GovernorateResource.php returning `{id, name}` where name uses the locale accessor
- [x] T013 [P] Create GovernorateCityResource transformer at Modules/Location/Transformers/GovernorateCityResource.php returning `{id, name}` where name uses the locale accessor
- [x] T014 Run migrations (`php artisan migrate`) and seed governorates (`php artisan db:seed --class=GovernorateSeeder`) to verify data layer works

**Checkpoint**: Foundation ready — governorates/cities exist in DB, locale middleware reads Accept-Language header

---

## Phase 3: User Story 1 — Browse Governorates and Cities (Priority: P1) MVP

**Goal**: Mobile app users can list all 27 Egyptian governorates and drill into cities per governorate, with names in Arabic or English based on Accept-Language header.

**Independent Test**: `curl -H "Accept-Language: ar" /api/governorates` returns 27 governorates in Arabic. `curl /api/cities?governorate_id=1` returns Cairo's cities.

### Implementation for User Story 1

- [x] T015 [US1] Create LocationApiController at Modules/Location/Http/Controllers/API/LocationApiController.php with `governorates()` method (returns all governorates via GovernorateResource) and `cities(Request $request)` method (validates governorate_id is required + exists, returns filtered GovernorateCityResource collection). Use `sendResponse()`/`sendError()` helpers.
- [x] T016 [US1] Register Location API routes at Modules/Location/Routes/api.php: `GET /api/governorates` → `governorates`, `GET /api/cities` → `cities`. No auth middleware. Apply localization middleware via the api group.
- [x] T017 [US1] Write feature test at Modules/Location/Tests/Feature/LocationApiTest.php covering: governorates list returns 27 items, governorates respond in Arabic when Accept-Language: ar, cities require governorate_id (422 if missing), cities filter by governorate_id correctly, invalid governorate_id returns 422, default language is English

**Checkpoint**: US1 fully functional — governorate/city browsing works with bilingual support

---

## Phase 4: User Story 2 — Search for Doctors by Location (Priority: P1)

**Goal**: Patients can search doctors filtered by governorate, city, specialty, name, gender, and price range with paginated results sorted newest first.

**Independent Test**: `curl "/api/doctors/search?governorate_id=1&gender=male"` returns filtered, paginated doctor results with nested governorate/city objects.

### Implementation for User Story 2

- [x] T018 [P] [US2] Create migration at Modules/Clinic/database/migrations/ to add `governorate_id` (unsignedBigInteger, nullable, FK to governorates, indexed) and `city_id` (unsignedBigInteger, nullable, FK to governorate_cities, indexed) to the `doctors` table
- [x] T019 [US2] Update Doctor model at Modules/Clinic/Models/Doctor.php: add `governorate_id`, `city_id` to `$fillable`, add `governorate()` belongsTo Governorate and `city()` belongsTo GovernorateCity relationships
- [x] T020 [P] [US2] Create DoctorSearchRequest form request at Modules/Clinic/Http/Requests/DoctorSearchRequest.php with nullable validation rules for governorate_id (exists:governorates,id), city_id (exists:governorate_cities,id), specialty_id, name (string max:100), gender (in:male,female), min_price (numeric min:0), max_price (numeric min:0)
- [x] T021 [US2] Create DoctorSearchService at Modules/Clinic/Services/DoctorSearchService.php with `search(array $filters)` method: builds Eloquent query on Doctor with eager-loaded governorate/city, applies `when()` clauses for each filter (governorate_id, city_id, name via user join LIKE, gender via user join, specialty_id via doctorService relationship, min/max price via service charges), orders by `created_at desc`, paginates 15/page, returns unified response format
- [x] T022 [P] [US2] Create DoctorSearchResource transformer at Modules/Clinic/Transformers/DoctorSearchResource.php returning: id, name (from user first_name + last_name), specialty, gender, experience, consultation_price, profile_image, governorate {id, name}, city {id, name}
- [x] T023 [US2] Create DoctorSearchApiController at Modules/Clinic/Http/Controllers/API/DoctorSearchApiController.php with `search(DoctorSearchRequest $request)` method delegating to DoctorSearchService, returning paginated response via sendResponse()
- [x] T024 [US2] Register doctor search route in Modules/Clinic/Routes/api.php: `GET /api/doctors/search` → DoctorSearchApiController@search (public, no auth)
- [x] T025 [US2] Write feature test at Modules/Clinic/Tests/Feature/DoctorSearchTest.php covering: unfiltered search returns paginated results sorted newest first, filter by governorate_id works, filter by city_id works, filter by name (partial match) works, filter by gender works, filter by price range works, combined filters work, empty results return valid pagination metadata, invalid governorate_id returns 422

**Checkpoint**: US2 fully functional — doctor search with all filters, pagination, and locale support

---

## Phase 5: User Story 3 — Search for Clinics by Location (Priority: P1)

**Goal**: Patients can search clinics filtered by governorate, city, specialty, and name with paginated results.

**Independent Test**: `curl "/api/clinics/search?governorate_id=2&name=clinic"` returns filtered clinic results.

### Implementation for User Story 3

- [x] T026 [P] [US3] Create migration at Modules/Clinic/database/migrations/ to add `governorate_id` (unsignedBigInteger, nullable, FK to governorates, indexed) and `city_id` (unsignedBigInteger, nullable, FK to governorate_cities, indexed) to the `clinic` table
- [x] T027 [US3] Update Clinics model at Modules/Clinic/Models/Clinics.php: add `governorate_id`, `city_id` to `$fillable`, add `governorate()` belongsTo Governorate and `city()` belongsTo GovernorateCity relationships
- [x] T028 [P] [US3] Create ClinicSearchRequest form request at Modules/Clinic/Http/Requests/ClinicSearchRequest.php with nullable validation for governorate_id, city_id, specialty_id, name (string max:100)
- [x] T029 [US3] Create ClinicSearchService at Modules/Clinic/Services/ClinicSearchService.php with `search(array $filters)`: query Clinics with eager-loaded governorate/city, when() clauses for governorate_id, city_id, name (LIKE on clinic_name), specialty_id (via clinics_services), order by created_at desc, paginate 15/page
- [x] T030 [P] [US3] Create ClinicSearchResource transformer at Modules/Clinic/Transformers/ClinicSearchResource.php returning: id, name, specialty, profile_image, governorate {id, name}, city {id, name}
- [x] T031 [US3] Create ClinicSearchApiController at Modules/Clinic/Http/Controllers/API/ClinicSearchApiController.php with `search(ClinicSearchRequest $request)` method
- [x] T032 [US3] Register clinic search route in Modules/Clinic/Routes/api.php: `GET /api/clinics/search` → ClinicSearchApiController@search (public)
- [x] T033 [US3] Write feature test at Modules/Clinic/Tests/Feature/ClinicSearchTest.php covering: unfiltered returns paginated results, filter by governorate, filter by name (partial match), empty results, invalid IDs return 422

**Checkpoint**: US3 fully functional — clinic search works independently

---

## Phase 6: User Story 4 — Search for Nurses by Location (Priority: P2)

**Goal**: Patients can search nurses filtered by governorate, city, specialty, gender, and availability.

**Independent Test**: `curl "/api/nurses/search?availability=available&governorate_id=1"` returns available nurses in Cairo.

### Implementation for User Story 4

- [x] T034 [P] [US4] Create migration at Modules/RequestNurse/database/migrations/ to add `governorate_id` (unsignedBigInteger, nullable, FK, indexed) and `city_id` (unsignedBigInteger, nullable, FK, indexed) to the `nurses` table
- [x] T035 [US4] Update Nurse model at Modules/RequestNurse/Models/Nurse.php: add `governorate_id`, `city_id` to `$fillable`, add `governorate()` belongsTo Governorate and `city()` belongsTo GovernorateCity relationships
- [x] T036 [P] [US4] Create NurseSearchRequest form request at Modules/RequestNurse/Http/Requests/NurseSearchRequest.php with nullable validation for governorate_id, city_id, specialty (string max:100), gender (in:male,female), availability (in:available,busy,off_duty)
- [x] T037 [US4] Create NurseSearchService at Modules/RequestNurse/Services/NurseSearchService.php with `search(array $filters)`: query Nurse with eager-loaded governorate/city/user, when() for governorate_id, city_id, specialty (LIKE on specialization), gender (via user join), availability (on availability_status), order by created_at desc, paginate 15/page
- [x] T038 [P] [US4] Create NurseSearchResource transformer at Modules/RequestNurse/Transformers/NurseSearchResource.php returning: id, name (from user), specialization, gender, availability_status, hourly_rate, profile_image, governorate {id, name}, city {id, name}
- [x] T039 [US4] Create NurseSearchApiController at Modules/RequestNurse/Http/Controllers/API/NurseSearchApiController.php with `search(NurseSearchRequest $request)` method
- [x] T040 [US4] Register nurse search route in Modules/RequestNurse/Routes/api.php: `GET /api/nurses/search` → NurseSearchApiController@search (public)
- [x] T041 [US4] Write feature test at Modules/RequestNurse/Tests/Feature/NurseSearchTest.php covering: filter by governorate, filter by availability, filter by gender, empty results, combined filters

**Checkpoint**: US4 fully functional — nurse search works independently

---

## Phase 7: User Story 5 — Search for Home Healthcare by Location (Priority: P2)

**Goal**: Patients can search home healthcare providers by governorate, city, and service type.

**Independent Test**: `curl "/api/home-healthcare/search?service_type=physiotherapy"` returns matching providers.

### Implementation for User Story 5

- [x] T042 [P] [US5] Create HomeHealthcare model at Modules/HomeHealthcare/Models/HomeHealthcare.php extending BaseModel with: `$table = 'home_healthcares'`, `$fillable = ['name_ar', 'name_en', 'service_type', 'governorate_id', 'city_id', 'status']`, name accessor by locale, governorate/city belongsTo relationships
- [x] T043 [P] [US5] Create migration at Modules/HomeHealthcare/database/migrations/ for home_healthcares table per data-model.md (id, name_ar, name_en, service_type, governorate_id FK indexed, city_id FK indexed, status, audit fields, timestamps, softDeletes)
- [x] T044 [P] [US5] Create HomeHealthcareSearchRequest form request at Modules/HomeHealthcare/Http/Requests/HomeHealthcareSearchRequest.php with nullable validation for governorate_id, city_id, service_type (string max:100)
- [x] T045 [US5] Create HomeHealthcareSearchService at Modules/HomeHealthcare/Services/HomeHealthcareSearchService.php with `search(array $filters)`: query HomeHealthcare with eager-loaded governorate/city, when() clauses, order by created_at desc, paginate 15/page
- [x] T046 [P] [US5] Create HomeHealthcareResource transformer at Modules/HomeHealthcare/Transformers/HomeHealthcareResource.php returning: id, name, service_type, governorate {id, name}, city {id, name}
- [x] T047 [US5] Create HomeHealthcareSearchApiController at Modules/HomeHealthcare/Http/Controllers/API/HomeHealthcareSearchApiController.php with `search(HomeHealthcareSearchRequest $request)` method
- [x] T048 [US5] Register home healthcare search route in Modules/HomeHealthcare/Routes/api.php: `GET /api/home-healthcare/search` → HomeHealthcareSearchApiController@search (public)
- [x] T049 [US5] Write feature test at Modules/HomeHealthcare/Tests/Feature/HomeHealthcareSearchTest.php covering: filter by governorate, filter by service_type, empty results, pagination

**Checkpoint**: US5 fully functional — home healthcare search works independently

---

## Phase 8: User Story 6 — Search for Labs by Location (Priority: P2)

**Goal**: Patients can search medical labs by governorate, city, and test name.

**Independent Test**: `curl "/api/labs/search?test_name=CBC"` returns labs offering CBC tests.

### Implementation for User Story 6

- [x] T050 [P] [US6] Create Lab model at Modules/RadiologyLab/Models/Lab.php extending BaseModel with: `$table = 'labs'`, `$fillable = ['name_ar', 'name_en', 'governorate_id', 'city_id', 'status']`, name accessor by locale, governorate/city belongsTo relationships, labTests hasMany relationship
- [x] T051 [P] [US6] Create migration at Modules/RadiologyLab/database/migrations/ for labs table per data-model.md (id, name_ar, name_en, governorate_id FK indexed, city_id FK indexed, status, audit fields, timestamps, softDeletes)
- [x] T052 [P] [US6] Create LabSearchRequest form request at Modules/RadiologyLab/Http/Requests/LabSearchRequest.php with nullable validation for governorate_id, city_id, test_name (string max:100)
- [x] T053 [US6] Create LabSearchService at Modules/RadiologyLab/Services/LabSearchService.php with `search(array $filters)`: query Lab with eager-loaded governorate/city, when() for governorate_id, city_id, test_name (LIKE join on lab_tests), order by created_at desc, paginate 15/page
- [x] T054 [P] [US6] Create LabSearchResource transformer at Modules/RadiologyLab/Transformers/LabSearchResource.php returning: id, name, governorate {id, name}, city {id, name}
- [x] T055 [US6] Create LabSearchApiController at Modules/RadiologyLab/Http/Controllers/API/LabSearchApiController.php with `search(LabSearchRequest $request)` method
- [x] T056 [US6] Register lab search route in Modules/RadiologyLab/Routes/api.php: `GET /api/labs/search` → LabSearchApiController@search (public)
- [x] T057 [US6] Write feature test at Modules/RadiologyLab/Tests/Feature/LabSearchTest.php covering: filter by governorate, filter by test_name (partial match), empty results, pagination

**Checkpoint**: US6 fully functional — lab search works independently

---

## Phase 9: User Story 7 — Search for Radiology Centers by Location (Priority: P2)

**Goal**: Patients can search radiology/imaging centers by governorate, city, and scan type.

**Independent Test**: `curl "/api/radiology/search?scan_type=MRI"` returns MRI centers.

### Implementation for User Story 7

- [x] T058 [P] [US7] Create RadiologyCenter model at Modules/RadiologyLab/Models/RadiologyCenter.php extending BaseModel with: `$table = 'radiology_centers'`, `$fillable = ['name_ar', 'name_en', 'scan_type', 'governorate_id', 'city_id', 'status']`, name accessor by locale, governorate/city belongsTo relationships
- [x] T059 [P] [US7] Create migration at Modules/RadiologyLab/database/migrations/ for radiology_centers table per data-model.md (id, name_ar, name_en, scan_type, governorate_id FK indexed, city_id FK indexed, status, audit fields, timestamps, softDeletes)
- [x] T060 [P] [US7] Create RadiologySearchRequest form request at Modules/RadiologyLab/Http/Requests/RadiologySearchRequest.php with nullable validation for governorate_id, city_id, scan_type (string max:100)
- [x] T061 [US7] Create RadiologySearchService at Modules/RadiologyLab/Services/RadiologySearchService.php with `search(array $filters)`: query RadiologyCenter with eager-loaded governorate/city, when() clauses, order by created_at desc, paginate 15/page
- [x] T062 [P] [US7] Create RadiologyCenterResource transformer at Modules/RadiologyLab/Transformers/RadiologyCenterResource.php returning: id, name, scan_type, governorate {id, name}, city {id, name}
- [x] T063 [US7] Create RadiologySearchApiController at Modules/RadiologyLab/Http/Controllers/API/RadiologySearchApiController.php with `search(RadiologySearchRequest $request)` method
- [x] T064 [US7] Register radiology search route in Modules/RadiologyLab/Routes/api.php: `GET /api/radiology/search` → RadiologySearchApiController@search (public)
- [x] T065 [US7] Write feature test at Modules/RadiologyLab/Tests/Feature/RadiologySearchTest.php covering: filter by governorate, filter by scan_type, empty results, pagination

**Checkpoint**: US7 fully functional — radiology center search works independently

---

## Phase 10: Polish & Cross-Cutting Concerns

**Purpose**: Code quality, validation, and final integration checks

- [x] T066 Run `./vendor/bin/pint` to fix code style across all new/modified files
- [x] T067 Run `php artisan optimize:clear` to clear all caches after structural changes
- [x] T068 Run `php artisan test` to verify ALL tests pass (Location, Clinic, RequestNurse, RadiologyLab, HomeHealthcare)
- [x] T069 Validate all 8 API endpoints manually using quickstart.md curl commands (both Arabic and English)
- [x] T070 Verify `modules_statuses.json` has Location and HomeHealthcare enabled, run `php artisan module:list` to confirm

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Phase 2 — MVP milestone
- **US2 (Phase 4)**: Depends on Phase 2 — can run parallel with US3
- **US3 (Phase 5)**: Depends on Phase 2 — can run parallel with US2
- **US4 (Phase 6)**: Depends on Phase 2 — can run parallel with US5, US6, US7
- **US5 (Phase 7)**: Depends on Phase 2 — can run parallel with US4, US6, US7
- **US6 (Phase 8)**: Depends on Phase 2 — can run parallel with US4, US5, US7
- **US7 (Phase 9)**: Depends on Phase 2 — can run parallel with US4, US5, US6
- **Polish (Phase 10)**: Depends on all desired user stories being complete

### User Story Dependencies

- **US1 (P1)**: Foundation only — no story dependencies. **MVP target.**
- **US2 (P1)**: Foundation only — independent of US1 (uses same Governorate/City models)
- **US3 (P1)**: Foundation only — independent of US1, US2
- **US4 (P2)**: Foundation only — independent of all P1 stories
- **US5 (P2)**: Foundation only — independent (new HomeHealthcare module)
- **US6 (P2)**: Foundation only — independent (new Lab model in RadiologyLab)
- **US7 (P2)**: Foundation only — independent (new RadiologyCenter model in RadiologyLab)

### Within Each User Story

1. Migration first (creates columns/tables)
2. Model updates (add relationships, fillable)
3. Form request + Resource transformer (parallel — different files)
4. Search service (depends on model)
5. API controller (depends on service + request)
6. Route registration (depends on controller)
7. Feature test (depends on everything above)

---

## Parallel Opportunities

### Phase 2 (Foundational) — 4 parallel tracks:
```
Track A: T007 (Governorate model) + T009 (governorates migration)
Track B: T008 (GovernorateCity model) + T010 (governorate_cities migration)
Track C: T012 (GovernorateResource) + T013 (GovernorateCityResource)
Track D: T006 (localization middleware)
→ Then T011 (seeder, needs models) → T014 (run migrations)
```

### Phase 4-9 (User Stories) — All independent:
```
After Phase 2 completes, all 6 user stories (US2-US7) can proceed in parallel.
Within each story, models/migrations/requests/resources can be created in parallel.
```

### Example: US2 parallel tasks
```
Parallel: T018 (migration) + T020 (form request) + T022 (resource)
Then: T019 (model update) → T021 (service) → T023 (controller) → T024 (route) → T025 (test)
```

---

## Implementation Strategy

### MVP First (US1 Only)

1. Complete Phase 1: Setup (T001-T005)
2. Complete Phase 2: Foundational (T006-T014)
3. Complete Phase 3: US1 (T015-T017)
4. **STOP and VALIDATE**: Test governorate/city browsing in Arabic + English
5. Deploy/demo if ready — foundation is live

### Incremental Delivery (recommended)

1. Setup + Foundational → Foundation ready
2. US1 → Governorate/City browsing live (MVP)
3. US2 + US3 → Doctor + Clinic search live (core value)
4. US4-US7 → Nurse + HomeHealthcare + Lab + Radiology search (full feature)
5. Polish → Code quality verified, all tests green

### Parallel Team Strategy

With 3 developers after Phase 2:
- Developer A: US2 (Doctor) + US3 (Clinic) — same Clinic module
- Developer B: US4 (Nurse) + US5 (Home Healthcare) — different modules
- Developer C: US6 (Lab) + US7 (Radiology) — same RadiologyLab module

---

## Notes

- [P] tasks = different files, no dependencies between them
- [Story] label maps each task to its user story for traceability
- All search services follow the same pattern: Eloquent query + when() filters + orderBy created_at desc + paginate(15)
- The `governorate_cities` table name avoids conflict with the existing World module's `cities` table (see research.md R-001)
- All models extend `App\Models\BaseModel` per Constitution Principle II
- Commit after each completed phase or logical group
