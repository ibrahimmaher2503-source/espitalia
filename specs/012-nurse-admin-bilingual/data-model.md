# Data Model: Nurse Admin Bilingual

**Branch**: `012-nurse-admin-bilingual` | **Date**: 2026-04-05

## Modified Entities

### Nurse (existing table: `nurses`)

**Columns to remove** (replaced by bilingual pairs):

| Old Column | Type |
|------------|------|
| specialization | string(255) |
| experience | string(255) |
| about | text |
| service_area | string(255) |

**Columns to add**:

| New Column | Type | Constraints | Description |
|------------|------|-------------|-------------|
| specialization_ar | string(255) | nullable | Arabic specialization |
| specialization_en | string(255) | nullable | English specialization |
| experience_ar | string(255) | nullable | Arabic experience description |
| experience_en | string(255) | nullable | English experience description |
| about_ar | text | nullable | Arabic bio/about |
| about_en | text | nullable | English bio/about |
| service_area_ar | string(255) | nullable | Arabic service area |
| service_area_en | string(255) | nullable | English service area |

**Data migration**: Copy `specialization` → both `specialization_ar` and `specialization_en`. Same for `experience`, `about`, `service_area`.

**Validation**: At least one of `_ar`/`_en` must be non-empty for each pair.

**Accessors** (with fallback):
- `getSpecializationAttribute()` → returns locale-appropriate value, falls back to other language
- `getExperienceAttribute()` → same pattern
- `getAboutAttribute()` → same pattern
- `getServiceAreaAttribute()` → same pattern

**Existing columns unchanged**: `governorate_id`, `city_id` (already exist, need to be wired to form/controller/validation)

---

### NurseRequest (existing table: `nurse_requests`)

**Column to remove**:

| Old Column | Type |
|------------|------|
| service_description | text |

**Columns to add**:

| New Column | Type | Constraints | Description |
|------------|------|-------------|-------------|
| service_description_ar | text | nullable | Arabic service description |
| service_description_en | text | nullable | English service description |

**Data migration**: Copy `service_description` → both `service_description_ar` and `service_description_en`.

**Accessor**:
- `getServiceDescriptionAttribute()` → locale-aware with fallback

---

## Existing Entities (No Schema Changes)

### Governorate (table: `governorates`)
Already bilingual: `name_ar`, `name_en` with `getNameAttribute()` accessor. Used by Nurse via `governorate_id` FK.

### GovernorateCity (table: `governorate_cities`)
Already bilingual: `name_ar`, `name_en` with `getNameAttribute()` accessor. Used by Nurse via `city_id` FK.

---

## Relationships (Existing, Need Wiring)

- Nurse `belongsTo` Governorate (via `governorate_id`) — relationship exists in model, missing from form/controller
- Nurse `belongsTo` GovernorateCity (via `city_id`) — relationship exists in model, missing from form/controller
- Nurse `belongsTo` User (via `nurse_id`) — unchanged
- NurseRequest `belongsTo` User (via `patient_id`) — unchanged
- NurseRequest `belongsTo` Nurse (via `nurse_id`) — unchanged
