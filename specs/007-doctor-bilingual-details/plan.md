# Implementation Plan: Bilingual Doctor Details (AR/EN)

**Branch**: `007-doctor-bilingual-details` | **Date**: 2026-04-02 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/007-doctor-bilingual-details/spec.md`

## Summary

Add bilingual (Arabic/English) support to all doctor text fields across 3 database tables (`users`, `doctors`, `user_profiles`), the admin create/edit forms, API responses, search queries, and public-facing pages. Uses the established dual-column pattern (`field_ar`/`field_en`) with locale-aware Eloquent accessors, matching the Governorate/GovernorateCity implementation.

## Technical Context

**Language/Version**: PHP 8.2 / Laravel 11.x
**Primary Dependencies**: nwidart/laravel-modules 10.x, spatie/laravel-permission, Yajra DataTables, Laravel Mix
**Storage**: MySQL 8.0 — 3 tables modified (`users`, `doctors`, `user_profiles`)
**Testing**: PHPUnit with SQLite in-memory database
**Target Platform**: Web (admin backend + public frontend + REST API)
**Project Type**: Web service (modular Laravel SaaS)
**Performance Goals**: Page loads < 2s, API responses < 1s — no degradation from bilingual fields
**Constraints**: Backward compatible — no breaking changes to existing API response shapes or DB queries
**Scale/Scope**: ~6 fields across 3 tables, ~10 files modified, 1 migration, 1 feature test

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Pre-Research Gate (Pass)

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Module Encapsulation | PASS | Changes span User (core) + Doctor (Clinic module) + UserProfile (core). Cross-module model references use Eloquent relationships. Shared logic (User accessors) stays in `app/`. |
| II. Convention Adherence | PASS | Follows established dual-column bilingual pattern from Location module. Models extend BaseModel. Admin views extend `backend.layouts.app`. Translation keys use `module_name.lbl_*` convention. |
| III. Role-Based Access Control | PASS | No new permissions needed. Only admin role accesses doctor create/edit forms. API endpoints maintain existing auth requirements. |
| IV. Test Discipline | PASS | Feature test planned for bilingual CRUD, accessor fallback, and search. |
| V. Simplicity/YAGNI | PASS | Dual columns over JSON/package/translation-table approaches. Only 2 languages (AR/EN) — no generic i18n system. |

### Post-Design Gate (Pass)

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Module Encapsulation | PASS | Migrations for `users`/`user_profiles` placed in Clinic module (doctor-specific feature). User model accessors in `app/Models/User.php` (shared layer). |
| II. Convention Adherence | PASS | Accessor pattern matches Governorate model exactly. DoctorRequest validation follows existing Laravel patterns. |
| III. Role-Based Access Control | PASS | No changes to auth middleware or permission checks. |
| IV. Test Discipline | PASS | `DoctorBilingualTest` covers: create with both languages, edit/update, accessor fallback, API locale switching, search across languages. |
| V. Simplicity/YAGNI | PASS | No new packages. No new abstractions. Direct column additions with simple accessors. |

## Project Structure

### Documentation (this feature)

```text
specs/007-doctor-bilingual-details/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0: research decisions
├── data-model.md        # Phase 1: schema changes
├── quickstart.md        # Phase 1: setup guide
├── contracts/           # Phase 1: API contracts
│   └── api-doctor-bilingual.md
└── tasks.md             # Phase 2: implementation tasks (via /speckit.tasks)
```

### Source Code (files to modify/create)

```text
# Migrations (new)
Modules/Clinic/database/migrations/
├── 2026_04_02_100001_add_bilingual_name_to_users_table.php
├── 2026_04_02_100002_add_bilingual_fields_to_doctors_table.php
└── 2026_04_02_100003_add_bilingual_fields_to_user_profiles_table.php

# Models (modify)
app/Models/
├── User.php                      # Add bilingual accessors for first_name, last_name
└── UserProfile.php               # Add bilingual accessors for expert, about_self

Modules/Clinic/Models/
└── Doctor.php                    # Add bilingual accessors for experience, description

# Validation (modify)
Modules/Clinic/Http/Requests/
└── DoctorRequest.php             # Update rules for bilingual field pairs

# Controllers (modify)
Modules/Clinic/Http/Controllers/
└── DoctorController.php          # Update store(), update(), edit(), index_data()

# API Resources (modify)
Modules/Clinic/Transformers/
├── DoctorResource.php            # Accessors handle locale automatically
└── DoctorSearchResource.php      # Accessors handle locale automatically

# Search Service (modify)
Modules/Clinic/Services/
└── DoctorSearchService.php       # Search both _ar and _en columns

# Admin Views (modify)
Modules/Clinic/Resources/views/backend/doctor/
└── (form views)                  # Add AR/EN input pairs

# Translations (modify)
lang/en/clinic.php                # Add bilingual field labels
lang/ar/clinic.php                # Add bilingual field labels

# Tests (new)
Modules/Clinic/Tests/Feature/
└── DoctorBilingualTest.php       # Bilingual CRUD + accessor + search tests
```

**Structure Decision**: This feature modifies existing files across the Clinic module and core `app/` layer. No new modules or directories needed. Migrations are placed in the Clinic module since the feature is doctor-specific.

## Complexity Tracking

No constitution violations — this section is empty.

## Implementation Phases

### Phase 1: Database & Models (Foundation)

**Goal**: Add bilingual columns, migrate existing data, implement accessors.

1. **Create 3 migrations** — add `_ar`/`_en` columns to `users`, `doctors`, `user_profiles`. Each migration includes a data copy step (existing values → `_en` and `_ar` columns).
2. **Add accessors to User model** — `getFirstNameAttribute()`, `getLastNameAttribute()`, `getFullNameAttribute()` with locale-aware fallback chain.
3. **Add accessors to Doctor model** — `getExperienceAttribute()`, `getDescriptionAttribute()`.
4. **Add accessors to UserProfile model** — `getExpertAttribute()`, `getAboutSelfAttribute()`.
5. **Update model `$fillable` arrays** to include new columns.

**Verify**: Run migrations, check data copied correctly, verify `$doctor->user->first_name` returns locale-aware value in tinker.

### Phase 2: Admin Forms & Validation (CRUD)

**Goal**: Admin can create/edit doctors with bilingual fields.

1. **Update DoctorRequest** — change `first_name`/`last_name` rules to `first_name_en`/`first_name_ar` with `required_without` cross-validation. Add rules for all bilingual field pairs.
2. **Update DoctorController `store()`** — save bilingual fields to User, Doctor, and UserProfile. Sync original columns from English values.
3. **Update DoctorController `update()`** — same as store for bilingual field handling.
4. **Update DoctorController `edit()`** — replace `getTranslations()` calls with direct `_ar`/`_en` column reads. Return field pairs as `{"ar": "...", "en": "..."}`.
5. **Update admin form Blade views** — replace single inputs with AR/EN input pairs. Arabic inputs get `dir="rtl"`. Labels distinguish languages.
6. **Update translation files** — add `lbl_first_name_en`, `lbl_first_name_ar`, etc. to `lang/en/clinic.php` and `lang/ar/clinic.php`.

**Verify**: Create a doctor with both AR/EN names. Re-open edit form. Both values appear correctly.

### Phase 3: DataTable, Search & API (Display)

**Goal**: All display surfaces show locale-aware doctor data.

1. **Update DoctorController `index_data()`** — replace JSON_EXTRACT search with LIKE queries on `_ar`/`_en` columns. Update column rendering for locale-aware display.
2. **Update DoctorSearchService** — search both `first_name_ar`/`first_name_en` and `last_name_ar`/`last_name_en` in name filter.
3. **Update API DoctorController `DoctorList()`** — search across bilingual columns.
4. **DoctorResource and DoctorSearchResource** — no code changes needed; accessors on models handle locale automatically. Verify response output.
5. **Update frontend Blade views** — if any public pages access doctor fields directly (not via API), ensure they use the accessor (e.g., `$doctor->user->first_name` not raw column).

**Verify**: Search for an Arabic name in English locale — finds the doctor. Switch locale — API returns Arabic values.

### Phase 4: Tests

**Goal**: Feature test coverage for bilingual behavior.

1. **Create `DoctorBilingualTest`** covering:
   - Create doctor with both AR/EN fields → both saved correctly
   - Create doctor with only EN → AR accessor falls back to EN
   - Create doctor with only AR → EN accessor falls back to AR
   - Edit doctor → update one language, other unchanged
   - DataTable search matches both languages
   - API response respects `Accept-Language` header
   - Data migration preserves existing doctor records

**Verify**: `php artisan test --filter=DoctorBilingual` passes.
