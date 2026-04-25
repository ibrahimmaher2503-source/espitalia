# Research: Bilingual Doctor Details (AR/EN)

**Branch**: `007-doctor-bilingual-details` | **Date**: 2026-04-02

## Research Questions & Findings

### RQ-1: Bilingual Storage Strategy — Dual Columns vs JSON

**Decision**: Dual columns (`field_ar` / `field_en`) with locale-aware accessor

**Rationale**:
- The project already establishes this pattern in Governorate and GovernorateCity models
- Dual columns are directly queryable with standard WHERE/LIKE without JSON functions
- MySQL JSON_EXTRACT (used in DoctorController `index_data()`) is slower for search and has inconsistent index support across MySQL versions
- Simpler migration path — `ALTER TABLE ADD COLUMN` is straightforward
- Constitution Principle II (Convention Adherence) mandates following established patterns
- Constitution Principle V (Simplicity/YAGNI) favors the simpler approach

**Alternatives Considered**:
- **JSON columns** (`{"ar": "...", "en": "..."}`): The DoctorController already has JSON_EXTRACT code in `index_data()`, suggesting this was explored. Rejected because: requires MySQL 5.7+ JSON functions, harder to index for search, inconsistent with the Governorate pattern, and the existing JSON_EXTRACT code appears to be premature/aspirational.
- **spatie/laravel-translatable**: A package that stores translations as JSON. Rejected because: adds a new dependency (violates Constitution V), and the project already has a working pattern without it.
- **Separate translations table**: A polymorphic translations table for all translatable fields. Rejected because: over-engineered for a 2-language requirement, adds query overhead, and no existing pattern in the project.

---

### RQ-2: Impact of Modifying the Shared `users` Table

**Decision**: Add `first_name_ar`, `first_name_en`, `last_name_ar`, `last_name_en` columns to the `users` table. Keep existing `first_name` and `last_name` columns as-is for backward compatibility.

**Rationale**:
- The `users` table is shared across all roles (admin, doctor, receptionist, vendor, user)
- Adding nullable bilingual columns does not break existing functionality — non-doctor roles simply won't populate them
- Only the doctor admin form will expose dual-language inputs; other user forms remain unchanged
- The accessor on the User model falls back: `first_name_ar` → `first_name` (or vice versa)
- Moving doctor names to the `doctors` table would break the established pattern where names live in `users`
- The existing `first_name`/`last_name` columns remain as the default (English) source, preserving all existing behavior

**Migration Strategy**:
1. Add `first_name_ar`, `first_name_en`, `last_name_ar`, `last_name_en` as nullable strings
2. Copy existing `first_name` → `first_name_en`, `last_name` → `last_name_en`
3. Copy existing `first_name` → `first_name_ar`, `last_name` → `last_name_ar` (as initial fallback)
4. The original `first_name`/`last_name` columns are kept and continue to be populated (for backward compat with non-doctor code)

---

### RQ-3: Impact on `doctors` and `user_profiles` Tables

**Decision**: Add dual columns for each translatable field. Migrate existing data to `_en` columns.

**Fields to add on `doctors` table**:
- `experience_ar`, `experience_en` (nullable strings)
- `description_ar`, `description_en` (nullable strings)

**Fields to add on `user_profiles` table**:
- `expert_ar`, `expert_en` (nullable strings)
- `about_self_ar`, `about_self_en` (nullable strings/text)

**Migration Strategy**: Copy existing `experience` → `experience_en`, `description` → `description_en`, `expert` → `expert_en`, `about_self` → `about_self_en`. Copy same values to `_ar` columns as initial fallback. Original columns kept for backward compatibility.

---

### RQ-4: Accessor Pattern for Locale-Aware Display

**Decision**: Follow the Governorate accessor pattern exactly.

**Pattern**:
```php
public function getFirstNameAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->first_name_ar : $this->first_name_en;
    // Fallback: if localized is empty, try the other language, then original column
    return $localized ?: ($locale === 'ar' ? $this->first_name_en : $this->first_name_ar) ?: $this->attributes['first_name'] ?? '';
}
```

**Rationale**: The fallback chain ensures no blank fields are displayed. Priority: requested locale → other locale → original column value.

**Note**: This means `$user->first_name` now returns the locale-aware value. Code that needs the raw original column must use `$user->getAttributes()['first_name']` or the explicit `_ar`/`_en` columns.

---

### RQ-5: Admin Form UI Pattern for Bilingual Input

**Decision**: Side-by-side or stacked paired inputs with language labels.

**Pattern**:
- Each translatable field renders as two inputs: one labeled "English" (LTR), one labeled "Arabic" (RTL)
- Arabic inputs get `dir="rtl"` attribute for proper text direction
- Group visually with a fieldset or card per field-pair
- Use existing Blade form builder patterns (spatie/laravel-html)
- Tab-based language switching was considered but rejected — showing both inputs simultaneously is simpler and prevents admins from forgetting to fill one language

---

### RQ-6: Search Behavior Across Languages

**Decision**: Search both language columns regardless of active locale.

**Implementation**: Replace existing single-column LIKE queries with OR conditions across both `_ar` and `_en` columns. Example:
```sql
WHERE (first_name_ar LIKE '%term%' OR first_name_en LIKE '%term%'
   OR last_name_ar LIKE '%term%' OR last_name_en LIKE '%term%')
```

**Applies to**: DoctorSearchService, DoctorController index_data, API DoctorList search.

---

### RQ-7: Backward Compatibility for Existing API Consumers

**Decision**: API responses continue to use field names `first_name`, `last_name`, `experience`, `description`, `expert`, `about_self` — the values returned are locale-aware based on `Accept-Language` header (default: English).

**Rationale**: No breaking changes to response shape. The `localization` middleware already sets `app()->setLocale()` based on the `Accept-Language` header. The accessors automatically return the correct language.

---

### RQ-8: DoctorController edit() — Existing getTranslations() Code

**Finding**: The DoctorController `edit()` method (lines 826-828) already calls `getTranslations()`, which currently doesn't exist. This was likely preparatory code for bilingual support.

**Decision**: Replace these calls with direct access to the `_ar`/`_en` columns. The edit response should return an object like `{"ar": "...", "en": "..."}` for each translatable field so the form can populate both inputs.
