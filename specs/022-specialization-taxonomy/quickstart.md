# Quickstart: Specialization Taxonomy

## Prerequisites

```bash
composer install
npm install
php artisan migrate
php artisan db:seed
php artisan serve
```

## Module Scaffold

```bash
php artisan module:make Specialization
php artisan module:enable Specialization
```

## Key Files to Create/Modify

### New Files (Specialization Module)

| File | Purpose |
|------|---------|
| `Modules/Specialization/Models/Specialization.php` | Model extending BaseModel with HasSlug |
| `Modules/Specialization/Http/Controllers/SpecializationController.php` | Admin CRUD controller |
| `Modules/Specialization/Http/Controllers/API/SpecializationController.php` | API controller |
| `Modules/Specialization/Transformers/SpecializationResource.php` | API resource transformer |
| `Modules/Specialization/Routes/web.php` | Admin routes |
| `Modules/Specialization/Routes/api.php` | API routes |
| `Modules/Specialization/Resources/views/backend/specializations/index.blade.php` | DataTable list view |
| `Modules/Specialization/Resources/views/backend/specializations/create.blade.php` | Create/edit form |
| `Modules/Specialization/Resources/views/backend/specializations/datatable/action_column.blade.php` | Action buttons |
| `Modules/Specialization/database/migrations/*_create_specializations_table.php` | Main table |
| `Modules/Specialization/database/migrations/*_create_doctor_specialization_table.php` | Doctor pivot |
| `Modules/Specialization/database/migrations/*_create_nurse_specialization_table.php` | Nurse pivot |
| `Modules/Specialization/database/seeders/SpecializationPermissionSeeder.php` | Permission seeds |
| `lang/en/specialization.php` | Translation keys |

### Files to Modify

| File | Change |
|------|--------|
| `Modules/Clinic/Models/Doctor.php` | Add `specializations()` belongsToMany relationship |
| `Modules/RequestNurse/Models/Nurse.php` | Add `specializations()` belongsToMany relationship |
| `Modules/Clinic/Resources/assets/js/component/DoctorOffcanvas.vue` | Add specialization multi-select, make expert read-only |
| `Modules/RequestNurse/Resources/views/backend/nurses/create.blade.php` | Add specialization multi-select, make legacy fields read-only |
| `Modules/Clinic/Http/Controllers/DoctorController.php` | Sync specializations on save, load for edit |
| `Modules/RequestNurse/Http/Controllers/Backend/NursesController.php` | Sync specializations on save, load for edit |
| `Modules/Frontend/Http/Controllers/DoctorController.php` | Add specialization filter to `index_data()`, pass to view |
| `Modules/Frontend/Resources/views/doctors.blade.php` | Add specialization Select2 dropdown |
| `Modules/Frontend/Http/Controllers/NurseRequestFrontController.php` | Replace LIKE filter with JOIN-based filter |
| `Modules/Frontend/Resources/views/available_nurses.blade.php` | Replace hardcoded dropdown with data-driven Select2 |
| `Modules/QuickBooking/Resources/assets/js/quick-booking/QuickBooking.vue` | Insert specialization step in setupArray |
| `Modules/QuickBooking/Resources/assets/js/store/quick-booking.js` | Add specialization_id to state |
| `Modules/QuickBooking/Resources/assets/js/quick-booking/TabPanel.vue` | Add case for specialization component |
| `Modules/Clinic/Transformers/DoctorResource.php` | Add `specializations` array to response |
| `app/Http/Middleware/GenerateMenus.php` | Add Specializations sidebar entry under System/Masters |
| `lang/en/sidebar.php` | Add `lbl_specializations` key |
| `modules_statuses.json` | Add `"Specialization": true` |

## Verify Setup

```bash
php artisan module:list                    # Specialization should be enabled
php artisan module:migrate Specialization  # Run migrations
php artisan module:seed Specialization     # Run permission seeder
php artisan route:list --path=specialization  # Verify routes registered
php artisan optimize:clear                 # Clear caches
npm run dev                                # Rebuild frontend assets
```

## Testing

```bash
php artisan test Modules/Specialization/Tests  # Module tests
php artisan test --filter=Specialization        # All specialization tests
```
