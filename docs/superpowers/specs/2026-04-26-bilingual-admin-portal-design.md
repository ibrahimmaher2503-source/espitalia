# Bilingual Admin Portal — Full Consistency Design

## Goal

Ensure every core entity in the admin portal supports bilingual (Arabic/English) input for add and edit operations, using a single consistent pattern: `_ar`/`_en` column suffixes with locale-aware accessors.

## Standard Pattern

### Database Columns

Every user-facing text field gets two columns:

```
{field}_ar  VARCHAR/TEXT  — Arabic content
{field}_en  VARCHAR/TEXT  — English content
```

### Model Accessor (with locale fallback)

```php
public function getNameAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;
    return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
}
```

### Admin Form Layout

Side-by-side columns using Bootstrap grid:

- `col-md-6` Arabic field with `dir="rtl"`
- `col-md-6` English field with `dir="ltr"`
- `@error()` directives per field
- `old()` helper for repopulation
- Labels via `__('module.lbl_field_ar')` / `__('module.lbl_field_en')`

### Validation

Both `_ar` and `_en` fields use `required_without` of the other, so admins can fill one language and return later for the other.

### API Resource

Expose three values per field: the locale-aware accessor, and both raw columns.

```php
'name'    => $this->name,
'name_ar' => $this->name_ar,
'name_en' => $this->name_en,
```

## Scope

### 1. Add Bilingual From Scratch

Models with user-facing text fields that currently have no `_ar`/`_en` support.

| Model | Module | Fields to Add |
|---|---|---|
| Blog | Blog | `title_ar`, `title_en`, `description_ar`, `description_en` |
| Product | Product | `name_ar`, `name_en`, `short_description_ar`, `short_description_en`, `description_ar`, `description_en` |
| FAQ (Faqs) | FAQ | `question_ar`, `question_en`, `answer_ar`, `answer_en` |
| Page | Page | `name_ar`, `name_en`, `description_ar`, `description_en` |
| ClinicsCategory | Clinic | `name_ar`, `name_en`, `description_ar`, `description_en` |

**Work per model:**
- Migration: add `_ar`/`_en` columns, copy existing data into `_en` column. Keep the original column to avoid breaking existing queries — the locale-aware accessor overrides `getAttribute()` so code reading `$model->name` gets the localized value automatically
- Model: add to `$fillable`, add locale-aware accessors
- Admin Blade form: replace single-language inputs with AR/EN side-by-side
- Controller: update validation in `store`/`update`
- API Resource: create or update to expose `_ar`/`_en` + accessor
- Translation file: add `lbl_*_ar` and `lbl_*_en` keys

### 2. Migrate From Spatie Translatable

| Model | Module | Fields |
|---|---|---|
| Service | Service | `name`, `description` |
| Clinics | Clinic | `name`, `description` |

**Work per model:**
- Migration: add `_ar`/`_en` columns, extract data from JSON translatable column into new columns
- Model: remove `HasTranslations` trait, remove `$translatable` array, add `_ar`/`_en` to `$fillable`, add locale-aware accessors
- Admin Blade form: update inputs to AR/EN side-by-side
- Controller: update validation
- API Resource: update to expose `_ar`/`_en` + accessor

### 3. Fix Incomplete Models

| Model | Module | Issue |
|---|---|---|
| Nurse | RequestNurse | Has `_ar`/`_en` columns but missing locale-aware accessors |
| Doctor | Clinic | API Resource doesn't fully expose `_ar`/`_en` fields |

### 4. Consistency Audit — All Existing Bilingual Models

For every model that already has `_ar`/`_en` columns, verify and fix all 7 checkpoints:

1. Migration has `_ar`/`_en` columns
2. Model has fields in `$fillable`
3. Model has locale-aware accessor with fallback
4. Admin Blade form has side-by-side AR/EN inputs
5. API Resource exposes both raw `_ar`/`_en` and computed accessor
6. Controller validates both `_ar`/`_en` fields
7. Translation file has `lbl_*_ar` and `lbl_*_en` keys

**Models to audit:**
- Specialization
- Doctor (experience, description, title)
- Nurse (specialization, experience, about, service_area)
- Hospital (name, description, address)
- IcuDepartment (name, description)
- IcuAdmissionRequest (medical_condition, diagnosis, admin_notes, rejection_reason)
- ClinicsService (name, description)
- Offer (title, description, short_label, terms, banner_text)
- Pharmacy (name, description)
- PharmacyCoupon (title, description)
- PharmacyDeliveryZone (name)
- PharmacyCategory (name)
- PharmacyProduct (name, description)
- Lab (name)
- RadiologyCenter (name)
- HomeHealthcare (name)
- Governorate (name)
- GovernorateCity (name)
- PharmacyNotification (title, body)
- User Profile (first_name, last_name, expert, about_self)

## Out of Scope

- Frontend (public-facing) views — admin portal only
- New CRUD functionality — bilingual field support only
- Business logic changes
- New modules or features

## Risk Areas

- **Spatie Translatable migration**: Requires data extraction from JSON columns. Must handle empty/malformed JSON gracefully.
- **Product model**: Likely has many references to `name` column across queries, views, and exports. Accessor preserves backward compatibility but must verify no raw DB queries break.
- **Existing queries**: Any raw SQL or query builder calls using original column names (e.g., `->where('name', ...)`) will need updating to `->where('name_en', ...)` or similar for the newly migrated models.

## Estimated Migrations

~7 new migration files (one per model group or individual model).
