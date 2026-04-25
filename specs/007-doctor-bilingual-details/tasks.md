# Tasks: Bilingual Doctor Details (AR/EN)

**Input**: Design documents from `/specs/007-doctor-bilingual-details/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, contracts/

**Tests**: Included — the spec's Constitution Check (Principle IV: Test Discipline) requires feature test coverage.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel modular project**: `Modules/Clinic/`, `app/Models/`, `lang/`, `database/migrations/`
- Admin views: `Modules/Clinic/Resources/views/backend/doctor/`
- API resources: `Modules/Clinic/Transformers/`
- Tests: `Modules/Clinic/Tests/Feature/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No new project setup needed — this is an existing Laravel project. This phase covers translation key additions needed by all stories.

- [x] T001 [P] Add bilingual field labels to English translations in `lang/en/clinic.php` — add keys: `lbl_first_name_en`, `lbl_first_name_ar`, `lbl_last_name_en`, `lbl_last_name_ar`, `lbl_experience_en`, `lbl_experience_ar`, `lbl_description_en`, `lbl_description_ar`, `lbl_expert_en`, `lbl_expert_ar`, `lbl_about_self_en`, `lbl_about_self_ar`, `lbl_english`, `lbl_arabic`
- [x] T002 [P] Add bilingual field labels to Arabic translations in `lang/ar/clinic.php` — same keys as T001 with Arabic values

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Database schema changes and model accessors that ALL user stories depend on. MUST complete before any story work.

**CRITICAL**: No user story work can begin until this phase is complete.

- [x] T003 Create migration `add_bilingual_name_to_users_table` in `Modules/Clinic/database/migrations/2026_04_02_100001_add_bilingual_name_to_users_table.php` — add `first_name_ar`, `first_name_en`, `last_name_ar`, `last_name_en` (nullable string 255) to `users` table. Include data migration: copy existing `first_name` → `first_name_en` AND `first_name_ar`, same for `last_name`. Down method drops the 4 columns.
- [x] T004 Create migration `add_bilingual_fields_to_doctors_table` in `Modules/Clinic/database/migrations/2026_04_02_100002_add_bilingual_fields_to_doctors_table.php` — add `experience_ar`, `experience_en`, `description_ar`, `description_en` (nullable string 255) to `doctors` table. Data migration: copy existing `experience` → both `_en`/`_ar`, copy existing `Description` column → both `description_en`/`description_ar`. Down method drops the 4 columns.
- [x] T005 Create migration `add_bilingual_fields_to_user_profiles_table` in `Modules/Clinic/database/migrations/2026_04_02_100003_add_bilingual_fields_to_user_profiles_table.php` — add `expert_ar`, `expert_en` (nullable string 255), `about_self_ar`, `about_self_en` (nullable text) to `user_profiles` table. Data migration: copy existing `expert` → both `_en`/`_ar`, copy `about_self` → both `_en`/`_ar`. Down method drops the 4 columns.
- [x] T006 Add bilingual accessors to User model in `app/Models/User.php` — implement `getFirstNameAttribute()`, `getLastNameAttribute()`, `getFullNameAttribute()` with locale-aware fallback chain (requested locale → other locale → original column → empty string). Add mutator `setFirstNameEnAttribute()` that also syncs original `first_name` column, same for `last_name_en`. Add `first_name_ar`, `first_name_en`, `last_name_ar`, `last_name_en` to `$fillable`.
- [x] T007 Add bilingual accessors to Doctor model in `Modules/Clinic/Models/Doctor.php` — implement `getExperienceAttribute()`, `getDescriptionAttribute()` with same fallback chain pattern as User. Add `experience_ar`, `experience_en`, `description_ar`, `description_en` to `$fillable`.
- [x] T008 Add bilingual accessors to UserProfile model in `app/Models/UserProfile.php` — implement `getExpertAttribute()`, `getAboutSelfAttribute()` with same fallback chain pattern. Add `expert_ar`, `expert_en`, `about_self_ar`, `about_self_en` to `$fillable`.

**Checkpoint**: Run `php artisan migrate`. Verify in tinker: `App\Models\User::first()->first_name_en` returns migrated data. Verify `app()->setLocale('ar'); App\Models\User::first()->first_name` returns Arabic value.

---

## Phase 3: User Story 1 — Admin Creates Doctor with Bilingual Details (Priority: P1) MVP

**Goal**: Admin can create a new doctor with AR/EN fields for all 6 translatable text fields.

**Independent Test**: Create a doctor via admin form with both AR/EN names → verify both saved by re-opening edit form.

### Implementation for User Story 1

- [x] T009 [US1] Update validation rules in `Modules/Clinic/Http/Requests/DoctorRequest.php` — replace `first_name` → `first_name_en` (`required_without:first_name_ar|string|max:255`) and `first_name_ar` (`required_without:first_name_en|string|max:255`). Same for `last_name`. Add nullable rules for `experience_en`, `experience_ar`, `description_en`, `description_ar`, `expert_en`, `expert_ar`, `about_self_en`, `about_self_ar`.
- [x] T010 [US1] Update `store()` method in `Modules/Clinic/Http/Controllers/DoctorController.php` — save bilingual fields: pass `first_name_en`, `first_name_ar`, `last_name_en`, `last_name_ar` to User::create (also set `first_name` = `first_name_en ?: first_name_ar` for backward compat). Save `experience_ar`/`experience_en`, `description_ar`/`description_en` to Doctor::create. Save `expert_ar`/`expert_en`, `about_self_ar`/`about_self_en` to UserProfile updateOrCreate.
- [x] T011 [US1] Update admin doctor create form Blade view(s) in `Modules/Clinic/Resources/views/backend/doctor/` — for each translatable field (first_name, last_name, experience, description, expert, about_self), replace single input with two inputs: one for English (name=`field_en`, label uses `lbl_field_en`) and one for Arabic (name=`field_ar`, label uses `lbl_field_ar`, with `dir="rtl"`). Group each pair visually with a row/card.

**Checkpoint**: Create a doctor with both AR/EN names. Check database — both `_ar` and `_en` columns populated. Original `first_name`/`last_name` columns also populated.

---

## Phase 4: User Story 2 — Admin Edits Existing Doctor's Bilingual Fields (Priority: P1)

**Goal**: Admin can edit existing doctors (including pre-bilingual records), see both language values, and update independently.

**Independent Test**: Open edit form for a doctor created before bilingual support → existing data appears in both fields → update Arabic only → save → re-open → English unchanged, Arabic updated.

### Implementation for User Story 2

- [x] T012 [US2] Update `edit()` method in `Modules/Clinic/Http/Controllers/DoctorController.php` — replace `getTranslations()` calls (lines ~826-828) with direct reads of `_ar`/`_en` columns. Return bilingual fields as `{"ar": value, "en": value}` objects for: first_name, last_name, experience, description, expert, about_self. Ensure pre-bilingual doctors (with null `_ar`/`_en`) fall back to original column values.
- [x] T013 [US2] Update `update()` method in `Modules/Clinic/Http/Controllers/DoctorController.php` — save all bilingual field pairs to User, Doctor, and UserProfile. Sync original columns (`first_name` = `first_name_en ?: first_name_ar`, etc.) for backward compatibility. Only update fields that are present in the request.
- [x] T014 [US2] Update admin doctor edit form Blade view(s) in `Modules/Clinic/Resources/views/backend/doctor/` — populate AR/EN input pairs from the `{"ar": ..., "en": ...}` data returned by edit(). Ensure Arabic inputs have `dir="rtl"`. Pre-fill values correctly for both new and pre-existing doctors.

**Checkpoint**: Edit a pre-existing doctor → both fields show same value → update Arabic name → save → re-open → English unchanged, Arabic updated. Check DB directly.

---

## Phase 5: User Story 3 — Public-Facing Pages Display in Correct Language (Priority: P2)

**Goal**: Public doctor listing/detail pages show doctor info in the visitor's active locale.

**Independent Test**: Browse doctor listing page in English → see English names. Switch to Arabic → see Arabic names. Doctor with only English data → Arabic page shows English as fallback.

### Implementation for User Story 3

- [x] T015 [US3] Audit and update public-facing Blade views in `Modules/Frontend/Resources/views/` — verify all doctor-related templates (`booking.blade.php`, `appointments.blade.php`, `appointment_detail.blade.php`, and any doctor listing/detail views) access doctor fields via model accessors (e.g., `$doctor->user->first_name` not raw DB column). The accessors from Phase 2 handle locale automatically — no code changes needed if views already use accessor syntax. Fix any views that bypass accessors.

**Checkpoint**: Browse public doctor pages in English. Switch locale to Arabic. All doctor text fields switch language. Doctor with only EN data shows EN values in Arabic locale (no blanks).

---

## Phase 6: User Story 4 — API Returns Doctor Data in Requested Language (Priority: P2)

**Goal**: API endpoints return doctor data in the language matching the `Accept-Language` header.

**Independent Test**: `curl -H "Accept-Language: ar" /api/doctors/search` → Arabic values. `curl -H "Accept-Language: en" /api/doctors/search` → English values. No header → English.

### Implementation for User Story 4

- [x] T016 [P] [US4] Verify DoctorResource in `Modules/Clinic/Transformers/DoctorResource.php` — confirm `first_name`, `last_name`, `full_name`, `expert`, `about_self`, `description`, `experience` fields use model accessors (e.g., `optional($this->user)->first_name`). Since accessors are locale-aware from Phase 2, no code changes should be needed. Document verification.
- [x] T017 [P] [US4] Verify DoctorSearchResource in `Modules/Clinic/Transformers/DoctorSearchResource.php` — confirm `name` field uses `$this->user->first_name` / `$this->user->last_name` which are now locale-aware. Confirm `experience` uses `$this->experience` accessor. No code changes expected.
- [x] T018 [US4] Update search across bilingual columns in `Modules/Clinic/Services/DoctorSearchService.php` — replace `->where('first_name', 'LIKE', ...)` with OR conditions across both `first_name_ar`/`first_name_en` and `last_name_ar`/`last_name_en` columns for the `name` filter.
- [x] T019 [US4] Update API search in `Modules/Clinic/Http/Controllers/API/DoctorController.php` — in `DoctorList()` method, update the `search` filter to query both `first_name_ar`/`first_name_en` and `last_name_ar`/`last_name_en` columns (replace single-column LIKE with OR across both language columns).

**Checkpoint**: Call `GET /api/doctors/search?name=أحمد` → finds doctor with Arabic name. Call with `Accept-Language: ar` → response fields in Arabic. Call with `Accept-Language: en` → response fields in English. No header → English (backward compat).

---

## Phase 7: User Story 5 — Admin DataTable Displays in Active Locale (Priority: P3)

**Goal**: Admin doctor list DataTable shows locale-aware names and supports bilingual search.

**Independent Test**: Switch admin to Arabic locale → doctor names in Arabic. Search with Arabic text → matches. Switch to English → English names shown.

### Implementation for User Story 5

- [x] T020 [US5] Update `index_data()` in `Modules/Clinic/Http/Controllers/DoctorController.php` — replace JSON_EXTRACT search queries (lines ~422-428) with standard LIKE queries on `first_name_ar`/`first_name_en`/`last_name_ar`/`last_name_en` columns. Update `filterColumn` for the doctor_name column to search both language columns. Update `orderColumn` to sort by the current locale's column. Ensure the rendered name column uses the locale-aware accessor.

**Checkpoint**: Admin doctor list page in English → English names. Switch to Arabic → Arabic names. Search for Arabic name → found. Search for English name → found.

---

## Phase 8: Tests

**Purpose**: Feature test coverage for bilingual doctor behavior.

- [x] T021 Create feature test `Modules/Clinic/Tests/Feature/DoctorBilingualTest.php` covering:
  - Test create doctor with both AR/EN fields → both `_ar` and `_en` columns saved correctly, original columns synced
  - Test create doctor with only EN → AR accessor falls back to EN value
  - Test create doctor with only AR → EN accessor falls back to AR value
  - Test edit doctor → update only Arabic name → English unchanged
  - Test edit pre-bilingual doctor → existing values appear in both `_ar` and `_en`
  - Test DoctorSearchService name filter matches both Arabic and English names
  - Test API response with `Accept-Language: ar` returns Arabic values
  - Test API response with `Accept-Language: en` returns English values
  - Test API response without Accept-Language returns English (backward compat)
  - Test validation: at least one language required for first_name/last_name

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Final cleanup and validation.

- [x] T022 [P] Run `./vendor/bin/pint` on all modified files to ensure code style compliance
- [x] T023 [P] Run `php artisan optimize:clear` and verify no cache issues
- [x] T024 Run full test suite `php artisan test` to verify no regressions across existing tests
- [x] T025 Run quickstart.md validation — follow the verify steps in `specs/007-doctor-bilingual-details/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: No dependency on Phase 1 (translations and migrations are independent). BLOCKS all user stories.
- **User Story 1 (Phase 3)**: Depends on Phase 2 completion
- **User Story 2 (Phase 4)**: Depends on Phase 2 completion. Can run in parallel with US1 (different controller methods, same form views — coordinate if parallel)
- **User Story 3 (Phase 5)**: Depends on Phase 2 completion. Independent of US1/US2.
- **User Story 4 (Phase 6)**: Depends on Phase 2 completion. Independent of US1/US2/US3.
- **User Story 5 (Phase 7)**: Depends on Phase 2 completion. Independent of other stories.
- **Tests (Phase 8)**: Depends on Phases 3-7 (needs implementation to test against)
- **Polish (Phase 9)**: Depends on all prior phases

### User Story Dependencies

- **US1 (P1)**: Phase 2 → US1. No cross-story dependencies.
- **US2 (P1)**: Phase 2 → US2. Shares form views with US1 — if sequential, do US1 first.
- **US3 (P2)**: Phase 2 → US3. Fully independent (different files).
- **US4 (P2)**: Phase 2 → US4. Fully independent (different files).
- **US5 (P3)**: Phase 2 → US5. Fully independent (different file/method).

### Within Each User Story

- Validation before controllers
- Controllers before views
- Core logic before integration points

### Parallel Opportunities

- **Phase 1**: T001 and T002 can run in parallel (different lang files)
- **Phase 2**: T003, T004, T005 are sequential (migrations run in order), but T006, T007, T008 can run in parallel (different model files)
- **After Phase 2**: US3, US4, US5 can all run in parallel (different files entirely). US1 and US2 share form views — best done sequentially.
- **Phase 6**: T016, T017 can run in parallel (different resource files), T018 and T019 can run in parallel (different files)

---

## Parallel Example: After Foundation (Phase 2)

```text
# These can all run in parallel (different files, no shared state):

Agent A (US3): T015 — Audit/update public Blade views
Agent B (US4): T016, T017, T018, T019 — API resources + search service
Agent C (US5): T020 — DataTable index_data update

# US1 + US2 should be sequential (shared form views):
Agent D: T009 → T010 → T011 → T012 → T013 → T014
```

---

## Implementation Strategy

### MVP First (User Stories 1 + 2 Only)

1. Complete Phase 1: Translations (T001-T002)
2. Complete Phase 2: Migrations + Accessors (T003-T008)
3. Complete Phase 3: US1 — Create doctor with bilingual fields (T009-T011)
4. Complete Phase 4: US2 — Edit doctor with bilingual fields (T012-T014)
5. **STOP and VALIDATE**: Admin can create and edit doctors with AR/EN fields. Data persists correctly.

### Incremental Delivery

1. MVP (US1 + US2) → Admin CRUD works bilingually
2. Add US3 → Public pages locale-aware
3. Add US4 → API locale-aware
4. Add US5 → Admin DataTable locale-aware
5. Add Tests + Polish → Full coverage and cleanup

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- US1 and US2 share form Blade views — best done sequentially by same agent
- US3, US4, US5 are fully independent and can be parallelized
- The accessor pattern from Phase 2 does the heavy lifting — most display surfaces (API resources, Blade views) need minimal or no changes since they already use model properties
- The `localization` middleware already sets `app()->setLocale()` from `Accept-Language` header — no middleware changes needed
