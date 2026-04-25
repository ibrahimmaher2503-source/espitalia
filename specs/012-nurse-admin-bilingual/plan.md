# Implementation Plan: Nurse Admin Bilingual

**Branch**: `012-nurse-admin-bilingual` | **Date**: 2026-04-05 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/012-nurse-admin-bilingual/spec.md`

## Summary

Add bilingual (Arabic/English) support to Nurse and NurseRequest entities. Split 5 single-language text fields into `_ar`/`_en` column pairs with locale-aware accessors (Doctor model pattern with fallback). Add governorate/city cascading dropdowns to the nurse admin form (RadiologyLab pattern). Update admin views, API resources, search service, and validation. No new modules, no new dependencies.

## Technical Context

**Language/Version**: PHP 8.2+ / Laravel 11.x
**Primary Dependencies**: nwidart/laravel-modules 10.x, spatie/laravel-permission, Select2 (existing)
**Storage**: MySQL (2 ALTER TABLE migrations: nurses, nurse_requests)
**Testing**: PHPUnit + SQLite in-memory
**Target Platform**: Admin web panel + REST API
**Project Type**: Web application (modular Laravel SaaS)
**Performance Goals**: No measurable performance impact — accessor pattern is trivial
**Constraints**: Must follow Doctor model bilingual accessor pattern (with fallback). Must follow RadiologyLab cascading dropdown pattern. No new dependencies.
**Scale/Scope**: 2 migrations, 2 model updates, 3 view updates, 3 resource updates, 1 service update, 2 validation updates, ~20 new translation keys per language

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Evidence |
|-----------|--------|----------|
| I. Module Encapsulation | PASS | All changes within `Modules/RequestNurse/` except translation files in `lang/`. Cross-module refs only via Eloquent relationships (Governorate, GovernorateCity from Location module). |
| II. Convention Adherence | PASS | Bilingual accessor pattern matches Doctor model. Admin form pattern matches RadiologyLab. Translation keys follow `request_nurse.lbl_*` convention. |
| III. Role-Based Access Control | PASS | No new permissions needed — existing `view_nurses`, `add_nurses`, `edit_nurses`, `delete_nurses` cover all operations. |
| IV. Test Discipline | PASS | Feature tests for bilingual accessor fallback, form validation, API locale response, and location assignment. |
| V. Simplicity and YAGNI | PASS | Uses existing patterns (Doctor accessors, RadiologyLab cascading dropdown). No new libraries. Minimal schema changes. |

All gates pass. No violations require justification.

## Project Structure

### Documentation (this feature)

```text
specs/012-nurse-admin-bilingual/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0: research decisions
├── data-model.md        # Phase 1: entity documentation
├── quickstart.md        # Phase 1: implementation guide
├── contracts/
│   └── translations.md  # New translation keys
└── checklists/
    └── requirements.md  # Spec quality checklist
```

### Source Code (files to modify)

```text
Modules/RequestNurse/
├── database/migrations/
│   └── 2026_04_05_100001_make_nurse_fields_bilingual.php          # NEW
│   └── 2026_04_05_100002_make_nurse_request_fields_bilingual.php  # NEW
├── Models/
│   ├── Nurse.php                                                   # MODIFY — bilingual accessors + fillable
│   └── NurseRequest.php                                            # MODIFY — bilingual accessor + fillable
├── Http/
│   ├── Controllers/Backend/NursesController.php                    # MODIFY — save governorate/city
│   └── Requests/NurseFormRequest.php                               # MODIFY — bilingual + location validation
│       └── NurseRequestFormRequest.php                             # MODIFY — bilingual service_description
├── Resources/views/backend/
│   ├── nurses/create.blade.php                                     # MODIFY — paired AR/EN inputs + gov/city dropdowns
│   ├── nurses/index.blade.php                                      # MODIFY — location column in DataTable
│   └── requests/create.blade.php                                   # MODIFY — paired service_description inputs
├── Transformers/
│   ├── NurseResource.php                                           # MODIFY — add governorate/city nesting
│   └── NurseSearchResource.php                                     # MODIFY — verify bilingual works via accessor
├── Services/
│   └── NurseSearchService.php                                      # MODIFY — search both _ar/_en columns
└── Tests/Feature/
    └── NurseBilingualTest.php                                      # NEW — bilingual + location tests

lang/en/request_nurse.php                                           # MODIFY — add ~20 keys
lang/ar/request_nurse.php                                           # MODIFY — add ~20 keys
```

**Structure Decision**: All changes are modifications to existing files within `Modules/RequestNurse/` plus translations. Two new migration files and one new test file. No new controllers, services, or modules.

## Implementation Phases

### Phase 1: Foundation (Migrations + Models)

**Goal**: Database schema updated, models have bilingual accessors and updated fillable arrays.

**Files**:
1. Migration: `2026_04_05_100001_make_nurse_fields_bilingual.php` — Add 8 new columns (`specialization_ar/en`, `experience_ar/en`, `about_ar/en`, `service_area_ar/en`), copy data from old columns to both new columns, drop old columns.
2. Migration: `2026_04_05_100002_make_nurse_request_fields_bilingual.php` — Add 2 new columns (`service_description_ar/en`), copy data, drop old column.
3. Model: `Nurse.php` — Update `$fillable` (replace 4 old fields with 8 new `_ar/_en` fields), add 4 locale-aware accessors with fallback pattern (Doctor model style).
4. Model: `NurseRequest.php` — Update `$fillable` (replace `service_description` with `service_description_ar/en`), add 1 locale-aware accessor.

**Verification**: `php artisan migrate`, confirm columns via tinker, test accessor in both locales.

### Phase 2: Admin Form + Controller (US1 + US4)

**Goal**: Admin can create/edit nurses with bilingual fields and location dropdowns.

**Files**:
1. View: `nurses/create.blade.php` — Replace single `specialization/experience/about/service_area` inputs with paired AR/EN inputs (side by side in row). Add Governorate Select2 dropdown + City Select2 dropdown with AJAX cascade (RadiologyLab pattern). Controller must pass `$governorates` collection to view.
2. Controller: `NursesController.php` — In `create()`/`edit()`, load and pass `$governorates` (and `$cities` for edit). In `store()`/`update()`, save `governorate_id`, `city_id`, and new bilingual field names.
3. Validation: `NurseFormRequest.php` — Replace `specialization` rule with `specialization_ar|nullable|string|max:255` and `specialization_en|nullable|string|max:255` + custom rule requiring at least one. Same for other pairs. Add `governorate_id|nullable|exists:governorates,id` and `city_id|nullable|exists:governorate_cities,id`.

**Verification**: Admin form shows paired inputs, governorate dropdown cascades to city, save persists all fields.

### Phase 3: Nurse Request Form (US2)

**Goal**: Nurse request create form supports bilingual service description.

**Files**:
1. View: `requests/create.blade.php` — Replace single `service_description` with paired AR/EN textareas.
2. Validation: `NurseRequestFormRequest.php` — Update rules for `service_description_ar/en`.
3. Controller: `NurseRequestsController.php` — Use new field names in store/update.

**Verification**: Create a nurse request with both AR/EN descriptions.

### Phase 4: API + Search (US3)

**Goal**: API responses return locale-aware data. Search works across both languages.

**Files**:
1. Resource: `NurseResource.php` — Add `governorate` and `city` nesting: `{id, name}`. Bilingual text fields work automatically via model accessors.
2. Resource: `NurseSearchResource.php` — Verify accessor-based fields work (already uses `$this->specialization`). Governorate/city already nested.
3. Service: `NurseSearchService.php` — Update specialization search to query both `specialization_ar` and `specialization_en` (OR condition).

**Verification**: API calls with `Accept-Language: ar` return Arabic, `en` returns English.

### Phase 5: Admin DataTable + Translations (US5)

**Goal**: DataTable shows locale-aware specialization and location. All new labels translated.

**Files**:
1. View: `nurses/index.blade.php` — Update DataTable columns to use accessor-based specialization. Add location column (governorate + city).
2. Translations: `lang/en/request_nurse.php` — Add ~20 new keys per contracts/translations.md.
3. Translations: `lang/ar/request_nurse.php` — Add matching Arabic translations.

**Verification**: Index page shows correct locale text and location data.

### Phase 6: Tests + Polish

**Goal**: Feature tests pass, code style clean.

**Files**:
1. Test: `NurseBilingualTest.php` — Tests: accessor returns correct locale, fallback when one language empty, validation requires at least one language, governorate/city saved correctly, API returns locale-aware data, search matches both languages.
2. Run `./vendor/bin/pint Modules/RequestNurse` for code style.
3. Run `php artisan optimize:clear`.

**Verification**: `php artisan test --filter=NurseBilingual` passes.

## Key Technical Decisions

### Accessor Pattern (from research.md)

Use Doctor model fallback pattern — if requested locale is empty, return other locale:

```php
public function getSpecializationAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->specialization_ar : $this->specialization_en;
    return $localized ?: ($locale === 'ar' ? $this->specialization_en : $this->specialization_ar) ?: '';
}
```

### Cascading Dropdown Pattern (from research.md)

Reuse RadiologyLab pattern — Select2 + jQuery AJAX to `/api/v1/cities?governorate_id={id}`:

```javascript
$('#governorate_id').on('change', function() {
    var governorateId = $(this).val();
    var $citySelect = $('#city_id');
    $citySelect.empty().append('<option>...</option>');
    if (governorateId) {
        $.ajax({
            url: '/api/v1/cities',
            data: { governorate_id: governorateId },
            success: function(response) {
                $.each(response.data, function(i, city) {
                    $citySelect.append($('<option>').val(city.id).text(city.name));
                });
                $citySelect.trigger('change.select2');
            }
        });
    }
});
```

### Data Migration Strategy

Single migration per table. Uses raw SQL for performance:
```sql
UPDATE nurses SET specialization_ar = specialization, specialization_en = specialization;
```
Then drops old column. Reversible via down() method that copies `_en` back to restored column.
