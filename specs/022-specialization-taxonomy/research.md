# Research: Specialization Taxonomy Refactor

**Branch**: `022-specialization-taxonomy` | **Date**: 2026-04-21

## R1: Where Specialization Data Lives Today

### Decision
Specialization data is currently stored as free-text across two separate locations — not in a normalized entity.

### Findings

**Doctors**: The `user_profiles` table has an `expert` column (varchar) with bilingual variants `expert_ar` and `expert_en` added in migration `2026_04_02_100003_add_bilingual_fields_to_user_profiles_table.php`. This is pure free-text with no consistent format. It is rendered via a `TranslatableInput` Vue component in `Modules/Clinic/Resources/assets/js/component/DoctorOffcanvas.vue`.

**Nurses**: The `nurses` table originally had a single `specialization` column, later migrated to `specialization_ar` and `specialization_en` in `2026_04_05_100001_make_nurse_fields_bilingual.php`. The original column was dropped. The Nurse model (`Modules/RequestNurse/Models/Nurse.php`) has a `getSpecializationAttribute()` accessor returning the locale-appropriate value.

**Public nurse filter**: The frontend nurses page (`Modules/Frontend/Resources/views/available_nurses.blade.php`) uses a **hardcoded** dropdown with 8 specialization options (General Nursing, Pediatric Nursing, etc.). The filter in `NurseRequestFrontController::nurse_index_data()` uses `LIKE` against the (now-dropped) `specialization` column — this is currently broken after the bilingual migration.

**Public doctor filter**: The doctors listing (`Modules/Frontend/Http/Controllers/DoctorController.php`) filters by clinic, service, category (via clinic→specialty relationship), and rating. There is no specialization filter.

### Alternatives Considered
- Extending SystemServiceCategory or ClinicsCategory: Rejected because specializations are a distinct domain concept (medical specialties vs. clinic types vs. service categories). Mixing them would create semantic confusion and coupling.

---

## R2: Existing Module Architecture Patterns

### Decision
Follow the established module pattern with explicit pivot models and BaseModel inheritance.

### Findings

**Model pattern**: All models extend `App\Models\BaseModel` which provides SoftDeletes, HasHashedMediaTrait, and auto-sets `created_by`/`updated_by`/`deleted_by`. Hierarchical models (SystemServiceCategory, ClinicsCategory) use `parent_id` with `mainCategory()`/`subCategories()` relationships.

**Pivot pattern**: The project uses **explicit pivot models** with full audit fields, not simple Laravel `belongsToMany` pivot tables. Examples: `doctor_service_mappings`, `doctor_clinic_mapping`, `clinic_service_mapping` — all have `id`, audit fields, timestamps, and soft deletes.

**Controller pattern**: Standard admin controllers set `$this->module_title` and `$this->module_name` in constructor and implement: `index`, `index_data`, `create`, `store`, `edit`, `update`, `destroy`, `bulk_action`, `update_status`.

**Route pattern**: Admin routes use `prefix 'app/'`, `as 'backend.'`, middleware `['auth', 'auth_check']`. API routes use `auth:sanctum`, `prefix 'v1'`.

**Seeder pattern**: Permission seeders use `Permission::firstOrCreate(['name' => ..., 'guard_name' => 'web'])` then `$role->givePermissionTo()`. Different roles get different permission subsets.

**Menu pattern**: `GenerateMenus` middleware uses the Menu trait. The "System" section contains master-data items (Blog, FAQ, Location, Pages, etc.). Items are gated by permission checks.

---

## R3: Doctor Edit Form Integration Point

### Decision
Add a Select2 multi-select for specializations to the existing Vue-based DoctorOffcanvas component. Display the legacy `expert` field as read-only below it.

### Findings

The doctor edit form is a **Vue 3 component** at `Modules/Clinic/Resources/assets/js/component/DoctorOffcanvas.vue`. It already uses multi-select components for clinics, services, and commissions. The form communicates with `DoctorController::edit()` (returns JSON) and `DoctorController::store()`/`update()` (accepts form data).

The `expert` field is currently an editable `TranslatableInput`. The plan changes it to read-only and adds a new multi-select above it.

The controller's `edit()` method (lines 805-872) returns a JSON structure. The `update()` method (lines 877-1045) handles relationship syncing by deleting and recreating mapping records.

### Rationale
The Vue component already has the multi-select pattern established for clinics and services. Adding specializations follows the same UX pattern. Making `expert` read-only preserves the data for reference without breaking existing workflows.

---

## R4: Nurse Edit Form Integration Point

### Decision
Add a Select2 multi-select for specializations to the existing Blade-based nurse create/edit form. Keep legacy `specialization_ar`/`specialization_en` fields as read-only during deprecation.

### Findings

The nurse edit form is a **Blade template** at `Modules/RequestNurse/Resources/views/backend/nurses/create.blade.php`. It uses Select2 for governorate/city dropdowns. The `specialization_ar`/`specialization_en` fields are currently plain text inputs.

The controller (`Modules/RequestNurse/Http/Controllers/Backend/NursesController.php`) handles create/update in the `store()` method. Validation includes specialization fields as optional strings.

### Rationale
The Blade form already uses Select2 for dropdowns. Adding a Select2 multi-select for specializations is consistent. The existing text fields become read-only for backwards compatibility during the transition.

---

## R5: QuickBooking Integration Point

### Decision
Insert an optional specialization step as Step 0 in the QuickBooking Vue wizard, before the existing first step.

### Findings

The QuickBooking wizard is at `Modules/QuickBooking/Resources/assets/js/quick-booking/QuickBooking.vue`. It has two flows:

- **Single-vendor**: Service → Clinic → Doctor → DateTime → CustomerDetails → Confirm
- **Multi-vendor**: SystemService → Service → Clinic → Doctor → DateTime → CustomerDetails → Confirm

Steps are defined in a `setupArray` and rendered via `TabPanel.vue` which dynamically loads components based on step type. State is managed in a Pinia store at `Modules/QuickBooking/Resources/assets/js/store/quick-booking.js`.

The specialization step will be a new component (`SelectSpecialization.vue`) added as the first entry in `setupArray` for both flows. It will store the selected specialization_id in the Pinia store. Downstream steps (Clinic, Doctor) will filter by this value if set.

### Rationale
Inserting at the start of the wizard is natural — specialization is the broadest filter. Making it optional (skippable) ensures zero regression for existing booking paths.

---

## R6: Public Frontend Filter Patterns

### Decision
Add specialization filter to both doctor and nurse public listing pages following the existing Select2 dropdown + AJAX filter pattern.

### Findings

**Doctor listing** (`Modules/Frontend/Resources/views/doctors.blade.php`): Uses Select2 dropdowns for clinic, service, and rating filters. Filters are passed via an `advanceFilter` callback to the DataTable AJAX request. The controller's `index_data()` method applies `whereHas()` clauses for each filter.

**Nurse listing** (`Modules/Frontend/Resources/views/available_nurses.blade.php`): Uses Select2 for specialization (hardcoded options) and availability_status. AJAX calls to `nurse.index_data` with filter params. Results rendered as cards with load-more pagination.

Both pages populate filter dropdowns via **Blade server-side rendering** (data passed from controller to view), not API calls. This is confirmed in the spec clarifications.

### Rationale
The specialization filter follows the identical pattern: controller passes active specializations to the view, Select2 renders them, AJAX filter params include `specialization_ids[]`, controller applies `whereHas('specializations')` with `whereIn` on the pivot.

---

## R7: API Resource Patterns

### Decision
Create a `SpecializationResource` transformer following the existing Resource pattern, and extend `DoctorResource` and nurse API responses to include structured specialization data.

### Findings

**DoctorResource** is at `Modules/Clinic/Transformers/DoctorResource.php`. It returns a flat array with profile fields including `expert`. The pattern is standard Laravel API Resources.

**Nurse API**: The nurse frontend controller returns a manually-mapped array (not a Resource class). The response includes `specialization` as a string field from the accessor.

### Rationale
Adding a `SpecializationResource` is consistent. The doctor resource gets a `specializations` array field. The nurse response adds a `specializations` array while keeping the legacy `specialization` string for backwards compatibility.

---

## R8: Pivot Table Design Decision

### Decision
Use simple pivot tables (no explicit pivot model) with just the FK pair and timestamps. No audit fields on pivots.

### Findings

While existing mapping tables (doctor_service_mappings, doctor_clinic_mapping) use full audit fields and soft deletes, those pivots carry additional business data (charges, status). The specialization pivots are pure association tables with no extra data.

### Alternatives Considered
- Full pivot models with audit fields: Rejected because specialization assignments carry no business data beyond the association itself. The overhead of soft deletes and audit fields on a pure join table is unnecessary complexity (Constitution Principle V: YAGNI).
- Using `belongsToMany` with a simple pivot: Chosen. Laravel's built-in many-to-many with `withTimestamps()` is sufficient. This is simpler and idiomatic.

### Rationale
Simpler is better when no business logic lives on the pivot. If future requirements add data to the association (e.g., "primary specialization" flag), the pivot can be promoted to a model then.
