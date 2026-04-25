# Research: Nurse Admin Bilingual

**Branch**: `012-nurse-admin-bilingual` | **Date**: 2026-04-05

## Decision 1: Bilingual Field Pattern

**Decision**: Use the Doctor model's fallback-aware accessor pattern.

**Rationale**: The Doctor model (`Modules/Clinic/Models/Doctor.php`) has the most robust bilingual accessor — if one language is empty, it falls back to the other. This matches FR-012 (at least one language required) and the edge case (fallback when one language missing). Simpler patterns (Lab, Pharmacy) return empty string when the requested locale column is null.

**Pattern**:
```php
public function getSpecializationAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->specialization_ar : $this->specialization_en;
    return $localized ?: ($locale === 'ar' ? $this->specialization_en : $this->specialization_ar) ?: '';
}
```

**Alternatives considered**:
- Simple ternary (Lab/Pharmacy pattern): No fallback, returns empty if one language missing
- Spatie/laravel-translatable package: Would add a new dependency, violating Constitution V (Simplicity/YAGNI)

## Decision 2: Fields to Split

**Decision**: Split 5 text fields across 2 models into `_ar`/`_en` pairs.

| Model | Field | Split To |
|-------|-------|----------|
| Nurse | specialization | specialization_ar, specialization_en |
| Nurse | experience | experience_ar, experience_en |
| Nurse | about | about_ar, about_en |
| Nurse | service_area | service_area_ar, service_area_en |
| NurseRequest | service_description | service_description_ar, service_description_en |

**Rationale**: These are all user-visible text fields. Numeric fields (hourly_rate), enum fields (availability_status), and FK fields (nurse_id, patient_id) do not need bilingual support.

## Decision 3: Governorate/City Cascade Pattern

**Decision**: Reuse the RadiologyLab cascade dropdown pattern (jQuery + Select2 + AJAX to `/api/v1/cities`).

**Rationale**: The exact pattern already exists in `Modules/RadiologyLab/Resources/views/backend/labs/create.blade.php` — Select2 dropdowns with AJAX city loading on governorate change. No new code patterns needed.

**Key finding**: The `NursesController::store()` currently NEVER saves `governorate_id` or `city_id` even though the DB columns exist. The form also has no dropdowns for these fields. Both must be added.

## Decision 4: Data Migration Strategy

**Decision**: Create a single migration that:
1. Adds `_ar` and `_en` columns for each field
2. Copies existing data from the old column to BOTH new columns (preserves data)
3. Drops the old single-language columns

**Rationale**: Existing data is likely in one language (Arabic or English depending on who entered it). Copying to both columns ensures no data loss and provides a starting point for translation.

**Alternative considered**: Keep old columns and add only `_ar`/`_en` — rejected because it creates ambiguity about which column is authoritative.

## Decision 5: API Response Changes

**Decision**: Model accessors handle locale selection transparently. API resources (`NurseResource`, `NurseSearchResource`, `NurseRequestResource`) already read `$this->specialization` etc. — the accessor will return the correct language automatically. Add governorate/city nesting to NurseResource.

**Rationale**: Zero changes needed in resource files for bilingual text fields — the accessor pattern is transparent. Only governorate/city nesting needs explicit addition.
