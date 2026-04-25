# Quickstart: Admin UI for Labs & Radiology Centers

**Feature Branch**: `006-admin-labs-radiology-ui`
**Date**: 2026-04-02

## Prerequisites

1. The application is running (`php artisan serve`)
2. Database is migrated (`php artisan migrate`)
3. RadiologyLab module is enabled in `modules_statuses.json`
4. Location module is enabled and seeded with governorates/cities
5. An admin user exists and can log in

## Implementation Order

### Step 1: Permissions & Translations (Foundation)
- Add new permissions to `RadiologyLabPermissionSeeder.php`
- Add translation keys to `lang/en/radiology_lab.php` and `lang/ar/radiology_lab.php`
- Add sidebar keys to `lang/en/sidebar.php` and `lang/ar/sidebar.php`
- Run: `php artisan db:seed --class=Modules\\RadiologyLab\\database\\seeders\\RadiologyLabPermissionSeeder`

### Step 2: Form Requests (Validation)
- Create `LabFormRequest.php` in `Modules/RadiologyLab/Http/Requests/`
- Create `RadiologyCenterFormRequest.php` in `Modules/RadiologyLab/Http/Requests/`

### Step 3: Controllers (Business Logic)
- Create `LabsController.php` in `Modules/RadiologyLab/Http/Controllers/Backend/`
- Create `RadiologyCentersController.php` in `Modules/RadiologyLab/Http/Controllers/Backend/`
- Follow `LabTestCategoriesController` as the reference template

### Step 4: Routes (Wiring)
- Add Labs and Radiology Centers route groups to `Modules/RadiologyLab/Routes/web.php`

### Step 5: Views (UI)
- Create `backend/labs/index.blade.php`, `create.blade.php`, `datatable/action_column.blade.php`
- Create `backend/radiology-centers/index.blade.php`, `create.blade.php`, `datatable/action_column.blade.php`
- Forms use two separate text inputs for `name_ar` and `name_en`
- Add jQuery AJAX for dependent city dropdown (call `/api/v1/cities?governorate_id=X`)

### Step 6: Sidebar (Navigation)
- Add "Labs" and "Radiology Centers" child menu items under "Diagnostics" in `GenerateMenus.php`

### Step 7: Tests
- Create feature tests for Labs CRUD in `Modules/RadiologyLab/Tests/Feature/`
- Create feature tests for Radiology Centers CRUD
- Test bilingual data storage and retrieval
- Test permission enforcement

## Verification

```bash
# Run permission seeder
php artisan db:seed --class=Modules\\RadiologyLab\\database\\seeders\\RadiologyLabPermissionSeeder

# Clear caches
php artisan optimize:clear

# Verify routes registered
php artisan route:list --path=labs
php artisan route:list --path=radiology-centers

# Run tests
php artisan test --filter=LabsController
php artisan test --filter=RadiologyCentersController

# Check code style
./vendor/bin/pint
```

## Key Reference Files

| Purpose | File |
|---------|------|
| Reference controller | `Modules/RadiologyLab/Http/Controllers/Backend/LabTestCategoriesController.php` |
| Reference index view | `Modules/RadiologyLab/Resources/views/backend/categories/index.blade.php` |
| Reference form view | `Modules/RadiologyLab/Resources/views/backend/categories/create.blade.php` |
| Reference action column | `Modules/RadiologyLab/Resources/views/backend/categories/datatable/action_column.blade.php` |
| Sidebar middleware | `app/Http/Middleware/GenerateMenus.php` |
| Permission seeder | `Modules/RadiologyLab/database/seeders/RadiologyLabPermissionSeeder.php` |
| Lab model | `Modules/RadiologyLab/Models/Lab.php` |
| RadiologyCenter model | `Modules/RadiologyLab/Models/RadiologyCenter.php` |
| Location API controller | `Modules/Location/Http/Controllers/API/LocationApiController.php` |
