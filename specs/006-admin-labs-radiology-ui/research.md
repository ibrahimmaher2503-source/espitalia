# Research: Admin UI for Labs & Radiology Centers

**Feature Branch**: `006-admin-labs-radiology-ui`
**Date**: 2026-04-02

## Decision Log

### D1: Bilingual Field Strategy — Separate Columns vs. Spatie Translatable

**Decision**: Use the existing **separate column** approach (`name_ar`, `name_en`) already in the `Lab` and `RadiologyCenter` models.

**Rationale**: The models and migrations already store bilingual data in separate columns (`name_ar`, `name_en`) with a locale-aware `getNameAttribute()` accessor. Changing to Spatie Translatable would require a data migration and model rewrite. The existing approach is simpler and already integrated with the search APIs.

**Alternatives considered**:
- *Spatie HasTranslations trait* (used by LabTestCategory): Stores as JSON in a single column, supports arbitrary locales. Rejected because it would require migrating the existing `labs` and `radiology_centers` tables and updating the search services.

**Impact on forms**: Instead of the `@include('backend.components.translatable-input')` component (which expects Spatie Translatable models), forms will use two separate text inputs labeled "Name (Arabic)" and "Name (English)" directly mapped to `name_ar` and `name_en`.

---

### D2: Dependent City Dropdown — AJAX via Existing Location API

**Decision**: Use the existing Location module API endpoint `GET /api/v1/cities?governorate_id=X` to populate the city dropdown dynamically when a governorate is selected.

**Rationale**: The endpoint already exists in `Modules/Location/Http/Controllers/API/LocationApiController.php` and returns cities filtered by `governorate_id`. No new backend work is needed — only a jQuery AJAX call on the governorate `<select>` change event.

**Alternatives considered**:
- *Preload all cities and filter client-side*: Rejected because governorate/city data could grow and is better fetched on demand.
- *Create a new web route*: Rejected because the existing API route is sufficient and avoids duplication.

**API details**:
- `GET /api/v1/governorates` — returns all governorates (no auth required based on route definition)
- `GET /api/v1/cities?governorate_id={id}` — returns cities for a governorate (validates `governorate_id` exists)

---

### D3: Controller Pattern — Single store() for Create & Update

**Decision**: Follow the existing `LabTestCategoriesController` pattern where `create()` handles both create and edit forms (using `$request->id` to detect edit mode), and `store()` handles both create and update logic.

**Rationale**: This is the established convention in the project (Constitution Principle II — Convention Adherence). All existing RadiologyLab controllers use this pattern.

---

### D4: Permission Naming Convention

**Decision**: Use the standard `{action}_{resource_plural}` pattern:
- Labs: `view_labs`, `add_labs`, `edit_labs`, `delete_labs`
- Radiology Centers: `view_radiology_centers`, `add_radiology_centers`, `edit_radiology_centers`, `delete_radiology_centers`

**Rationale**: Follows the exact pattern used by existing permissions (`view_lab_tests`, `add_lab_tests`, etc.). Admin and demo_admin get all permissions. Lab technician role does not need lab/center management access (that's admin-only).

---

### D5: Sidebar Placement

**Decision**: Add "Labs" and "Radiology Centers" as child items under the existing "Diagnostics" parent menu group in `GenerateMenus.php`, visible only to `admin` and `demo_admin` roles.

**Rationale**: Labs and radiology centers are part of the RadiologyLab module and logically belong under Diagnostics. Only admins should manage facility data (lab technicians and doctors don't need this).

---

### D6: Form Validation — Both Languages Required

**Decision**: Both `name_ar` and `name_en` are **required** fields. Governorate, city, and scan_type (for radiology centers) are **optional**.

**Rationale**: The spec (FR-009) explicitly requires both language fields. Location fields are nullable in the database schema, and scan_type may not always apply.

**Validation rules**:
- `name_ar`: required|string|max:255
- `name_en`: required|string|max:255
- `governorate_id`: nullable|exists:governorates,id
- `city_id`: nullable|exists:governorate_cities,id
- `scan_type` (radiology only): nullable|string|max:255
- `status`: nullable|boolean

---

### D7: DataTable Name Display — Locale-Aware

**Decision**: The DataTable `name` column will display `name_ar` when the locale is Arabic and `name_en` when the locale is English, using the existing `getNameAttribute()` accessor on the model.

**Rationale**: The accessor already handles this logic. The `editColumn('name')` in the DataTable will simply call `$data->name` which triggers the accessor.

---

### D8: Translation Key Structure

**Decision**: Extend the existing `lang/en/radiology_lab.php` and `lang/ar/radiology_lab.php` files with new keys following the `lbl_*` prefix convention.

**New keys needed** (approximately 30):
- Entity titles: `lab_singular`, `lab_plural`, `radiology_center_singular`, `radiology_center_plural`
- Field labels: `lbl_name_ar`, `lbl_name_en`, `lbl_scan_type`, `lbl_governorate`, `lbl_city`
- Messages: `lab_save_success`, `lab_update_success`, `lab_delete_success`, etc.
- Sidebar: keys added to `lang/en/sidebar.php` and `lang/ar/sidebar.php`
