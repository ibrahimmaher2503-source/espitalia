# Quickstart: Nurse Admin Bilingual

**Branch**: `012-nurse-admin-bilingual` | **Date**: 2026-04-05

## Implementation Order

### Step 1: Migration — Split Fields

Create migration to add `_ar`/`_en` columns, copy data, drop old columns for both `nurses` and `nurse_requests` tables.

### Step 2: Model Accessors

Add locale-aware accessors with fallback to Nurse and NurseRequest models. Update `$fillable` arrays to use new column names.

### Step 3: Admin Form — Bilingual Inputs

Replace single text inputs with paired AR/EN inputs in `nurses/create.blade.php`. Add governorate/city Select2 dropdowns with AJAX cascade.

### Step 4: Controller — Save Location

Update `NursesController::store()` to save `governorate_id` and `city_id`. Update to use new bilingual field names.

### Step 5: Validation

Update `NurseFormRequest` to validate `_ar`/`_en` fields (at least one required per pair) and `governorate_id`/`city_id`.

### Step 6: Translations

Add new bilingual field labels and location labels to EN and AR translation files.

### Step 7: API Resources

Update `NurseResource` to include governorate/city nesting. Bilingual text fields work automatically via accessors.

### Step 8: Search Service

Update `NurseSearchService` to search across both `_ar` and `_en` columns.

### Step 9: Admin DataTable

Update nurse index DataTable to show locale-aware specialization and location columns.

## Verification

```bash
# Run migration
php artisan migrate

# Verify columns
php artisan tinker --execute="Schema::getColumnListing('nurses')"

# Test bilingual accessor
php artisan tinker --execute="$n = Modules\RequestNurse\Models\Nurse::first(); app()->setLocale('ar'); echo $n->specialization;"

# Test API
curl -H "Accept-Language: ar" /api/nurses/search
curl -H "Accept-Language: en" /api/nurses/search
```
