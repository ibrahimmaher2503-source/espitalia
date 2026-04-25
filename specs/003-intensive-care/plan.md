# IntensiveCare Module Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an ICU bed search and emergency admission request system as a new Laravel module, enabling patient families to find available ICU beds and submit admission requests to hospitals.

**Architecture:** New `Modules/IntensiveCare/` module following nwidart/laravel-modules v10 patterns. Three models (Hospital, IcuDepartment, IcuAdmissionRequest) with status state machine. Frontend views in `Modules/Frontend/`. Admin backend with DataTables. API endpoints for mobile app.

**Tech Stack:** Laravel 11, nwidart/laravel-modules v10, Spatie Permission, Spatie MediaLibrary, Spatie Translatable, PHPUnit, jQuery DataTables, Select2, Phosphor Icons, Blade templates.

**Spec:** `specs/003-intensive-care/spec.md`

---

## File Map

### New Files (Modules/IntensiveCare/)

```
Modules/IntensiveCare/
├── module.json
├── composer.json
├── Config/config.php
├── Providers/
│   ├── IntensiveCareServiceProvider.php
│   └── RouteServiceProvider.php
├── Models/
│   ├── Hospital.php
│   ├── IcuDepartment.php
│   └── IcuAdmissionRequest.php
├── Http/
│   ├── Controllers/Backend/
│   │   ├── HospitalsController.php
│   │   ├── IcuDepartmentsController.php
│   │   ├── IcuAdmissionRequestsController.php
│   │   └── API/
│   │       ├── HospitalsApiController.php
│   │       └── IcuAdmissionRequestsApiController.php
│   └── Requests/
│       ├── HospitalFormRequest.php
│       ├── IcuDepartmentFormRequest.php
│       └── IcuAdmissionRequestFormRequest.php
├── Transformers/
│   ├── HospitalResource.php
│   ├── IcuDepartmentResource.php
│   └── IcuAdmissionRequestResource.php
├── Routes/
│   ├── web.php
│   └── api.php
├── Resources/
│   ├── assets/js/app.js
│   ├── assets/sass/app.scss
│   └── views/backend/
│       ├── hospitals/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── datatable/action_column.blade.php
│       ├── departments/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── datatable/action_column.blade.php
│       └── requests/
│           ├── index.blade.php
│           ├── show.blade.php
│           └── datatable/action_column.blade.php
├── database/
│   ├── migrations/
│   │   ├── 2026_03_22_100001_create_hospitals_table.php
│   │   ├── 2026_03_22_100002_create_icu_departments_table.php
│   │   ├── 2026_03_22_100003_create_icu_admission_requests_table.php
│   │   └── 2026_03_22_100004_add_icu_hospitals_to_header_menu_setting.php
│   ├── factories/
│   │   ├── HospitalFactory.php
│   │   ├── IcuDepartmentFactory.php
│   │   └── IcuAdmissionRequestFactory.php
│   └── seeders/
│       ├── IntensiveCareDatabaseSeeder.php
│       └── IntensiveCarePermissionSeeder.php
└── Tests/Unit/
    ├── BaseTest.php
    ├── HospitalTest.php
    ├── IcuDepartmentTest.php
    ├── IcuAdmissionRequestTest.php
    └── PermissionSeederTest.php
```

### New Files (Frontend module additions)

```
Modules/Frontend/
├── Http/Controllers/IcuFrontController.php
└── Resources/views/
    ├── icu_hospitals.blade.php
    ├── icu_hospital_detail.blade.php
    ├── request_icu_admission.blade.php
    ├── my_icu_requests.blade.php
    └── icu_request_detail.blade.php
```

### New Translation File

```
lang/en/intensive_care.php
```

### Modified Files

| File | Change |
|------|--------|
| `modules_statuses.json` | Add `"IntensiveCare": true` |
| `lang/en/sidebar.php` | Add intensive_care sidebar keys |
| `app/Http/Middleware/GenerateMenus.php` | Add INTENSIVE CARE sidebar section |
| `app/Http/Middleware/CheckHeaderMenuSettings.php` | Add `icu-hospital` route map |
| `app/Http/Controllers/Backend/BackendController.php` | Add hospital_coordinator redirect |
| `database/seeders/DatabaseSeeder.php` | Add IntensiveCarePermissionSeeder call |
| `config/constant.php` | Add Hospital/ICU permission modules |
| `Modules/Frontend/Routes/web.php` | Add ICU frontend routes |

---

## Phase 1: Module Scaffold & Database (Tasks 1-5)

### Task 1: Create module scaffold

**Files:**
- Create: `Modules/IntensiveCare/module.json`
- Create: `Modules/IntensiveCare/composer.json`
- Create: `Modules/IntensiveCare/Config/config.php`
- Create: `Modules/IntensiveCare/Providers/IntensiveCareServiceProvider.php`
- Create: `Modules/IntensiveCare/Providers/RouteServiceProvider.php`
- Create: `Modules/IntensiveCare/Routes/web.php` (empty placeholder)
- Create: `Modules/IntensiveCare/Routes/api.php` (empty placeholder)
- Create: `Modules/IntensiveCare/Resources/assets/js/app.js`
- Create: `Modules/IntensiveCare/Resources/assets/sass/app.scss`
- Modify: `modules_statuses.json`

- [ ] **Step 1: Create module.json**

```json
{
    "name": "IntensiveCare",
    "alias": "intensivecare",
    "description": "ICU bed search and emergency admission requests",
    "keywords": [],
    "priority": 0,
    "providers": [
        "Modules\\IntensiveCare\\Providers\\IntensiveCareServiceProvider"
    ],
    "files": []
}
```

- [ ] **Step 2: Create composer.json**

Follow exact pattern from `Modules/RadiologyLab/composer.json`, replacing `RadiologyLab` with `IntensiveCare`.

- [ ] **Step 3: Create Config/config.php**

```php
<?php
return ['name' => 'IntensiveCare'];
```

- [ ] **Step 4: Create ServiceProvider and RouteServiceProvider**

Copy from `Modules/RadiologyLab/Providers/` and replace all `RadiologyLab`/`radiologylab` references with `IntensiveCare`/`intensivecare`.

- [ ] **Step 5: Create empty route files and asset stubs**

`Routes/web.php` and `Routes/api.php`: empty route groups matching RadiologyLab pattern.
`Resources/assets/js/app.js`: empty JS entry.
`Resources/assets/sass/app.scss`: empty SCSS entry.

- [ ] **Step 6: Enable module**

```bash
# Add "IntensiveCare": true to modules_statuses.json
php artisan module:enable IntensiveCare
php artisan optimize:clear
composer dump-autoload
```

- [ ] **Step 7: Commit**

```bash
git add Modules/IntensiveCare/ modules_statuses.json
git commit -m "feat(intensive-care): scaffold module structure"
```

---

### Task 2: Create hospitals migration

**Files:**
- Create: `Modules/IntensiveCare/database/migrations/2026_03_22_100001_create_hospitals_table.php`

- [ ] **Step 1: Write migration**

Follow pattern from `Modules/RadiologyLab/database/migrations/2026_03_10_000001_create_lab_test_categories_table.php`. Schema:

```php
Schema::create('hospitals', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('hospital_type')->default('private');
    $table->string('address');
    $table->string('city');
    $table->string('state')->nullable();
    $table->string('country')->nullable();
    $table->string('postal_code')->nullable();
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->string('phone');
    $table->string('email')->nullable();
    $table->string('website')->nullable();
    $table->string('license_number')->nullable();
    $table->json('accepted_insurance')->nullable();
    $table->decimal('rating', 3, 2)->default(0);
    $table->unsignedBigInteger('vendor_id')->nullable();
    $table->tinyInteger('status')->default(1);
    $table->integer('created_by')->unsigned()->nullable();
    $table->integer('updated_by')->unsigned()->nullable();
    $table->integer('deleted_by')->unsigned()->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index('status');
    $table->index('city');
    $table->index('hospital_type');
    $table->foreign('vendor_id')->references('id')->on('users')->onDelete('set null');
});
```

- [ ] **Step 2: Run migration**

```bash
php artisan module:migrate IntensiveCare
```
Expected: hospitals table created.

- [ ] **Step 3: Commit**

```bash
git add Modules/IntensiveCare/database/migrations/
git commit -m "feat(intensive-care): add hospitals migration"
```

---

### Task 3: Create icu_departments migration

**Files:**
- Create: `Modules/IntensiveCare/database/migrations/2026_03_22_100002_create_icu_departments_table.php`

- [ ] **Step 1: Write migration**

```php
Schema::create('icu_departments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('hospital_id');
    $table->string('name');
    $table->string('specialty_type')->default('general');
    $table->integer('total_beds')->default(0);
    $table->integer('available_beds')->default(0);
    $table->boolean('has_ventilator')->default(false);
    $table->boolean('has_oxygen')->default(true);
    $table->string('equipment_level')->default('basic');
    $table->decimal('daily_price', 8, 2)->default(0);
    $table->text('description')->nullable();
    $table->tinyInteger('status')->default(1);
    $table->integer('created_by')->unsigned()->nullable();
    $table->integer('updated_by')->unsigned()->nullable();
    $table->integer('deleted_by')->unsigned()->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index('status');
    $table->index('specialty_type');
    $table->index('available_beds');
    $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('cascade');
});
```

- [ ] **Step 2: Run migration and verify**

```bash
php artisan module:migrate IntensiveCare
```

- [ ] **Step 3: Commit**

```bash
git add Modules/IntensiveCare/database/migrations/
git commit -m "feat(intensive-care): add icu_departments migration"
```

---

### Task 4: Create icu_admission_requests migration

**Files:**
- Create: `Modules/IntensiveCare/database/migrations/2026_03_22_100003_create_icu_admission_requests_table.php`

- [ ] **Step 1: Write migration**

All columns from spec Section 2.3. FKs: `patient_id` and `hospital_id` use `restrict`, `icu_department_id` uses `set null`, `responded_by` uses `set null`. Index on `status`, `patient_id`, `hospital_id`.

- [ ] **Step 2: Run migration and verify**

```bash
php artisan module:migrate IntensiveCare
```

- [ ] **Step 3: Commit**

```bash
git add Modules/IntensiveCare/database/migrations/
git commit -m "feat(intensive-care): add icu_admission_requests migration"
```

---

### Task 5: Create header menu setting migration

**Files:**
- Create: `Modules/IntensiveCare/database/migrations/2026_03_22_100004_add_icu_hospitals_to_header_menu_setting.php`

- [ ] **Step 1: Write migration**

Follow pattern from `database/migrations/2026_03_11_000001_add_nurse_and_lab_tests_to_header_menu_setting.php`. Add `icu_hospitals` key with default value 1.

- [ ] **Step 2: Run and commit**

```bash
php artisan module:migrate IntensiveCare
git add Modules/IntensiveCare/database/migrations/
git commit -m "feat(intensive-care): add icu_hospitals header menu setting"
```

---

## Phase 2: Models (Tasks 6-8)

### Task 6: Create Hospital model

**Files:**
- Create: `Modules/IntensiveCare/Models/Hospital.php`

- [ ] **Step 1: Create model**

```php
<?php

namespace Modules\IntensiveCare\Models;

use App\Models\BaseModel;
use App\Models\Traits\HasSlug;
use App\Models\User;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hospital extends BaseModel
{
    use HasFactory, HasSlug, HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $table = 'hospitals';

    protected $fillable = [
        'name', 'slug', 'description', 'hospital_type',
        'address', 'city', 'state', 'country', 'postal_code',
        'latitude', 'longitude', 'phone', 'email', 'website',
        'license_number', 'accepted_insurance', 'rating', 'vendor_id',
        'status', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'accepted_insurance' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'status' => 'boolean',
    ];

    const HOSPITAL_TYPES = ['government', 'private', 'military', 'university'];

    // Relationships
    public function icuDepartments()
    {
        return $this->hasMany(IcuDepartment::class, 'hospital_id');
    }

    public function admissionRequests()
    {
        return $this->hasMany(IcuAdmissionRequest::class, 'hospital_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeHasInsurance($query, $provider)
    {
        return $query->whereJsonContains('accepted_insurance', $provider);
    }

    // Media
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('gallery');
    }

    protected static function newFactory()
    {
        return \Modules\IntensiveCare\Database\Factories\HospitalFactory::new();
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add Modules/IntensiveCare/Models/Hospital.php
git commit -m "feat(intensive-care): add Hospital model"
```

---

### Task 7: Create IcuDepartment model

**Files:**
- Create: `Modules/IntensiveCare/Models/IcuDepartment.php`

- [ ] **Step 1: Create model**

Include: HasFactory, HasTranslations. Table: `icu_departments`. Translatable: name, description. Casts per spec. Constants: SPECIALTY_TYPES, EQUIPMENT_LEVELS. Relationships: belongsTo Hospital, hasMany IcuAdmissionRequest. Scopes: active, available, withVentilator, withOxygen, bySpecialty. Factory method.

- [ ] **Step 2: Commit**

```bash
git add Modules/IntensiveCare/Models/IcuDepartment.php
git commit -m "feat(intensive-care): add IcuDepartment model"
```

---

### Task 8: Create IcuAdmissionRequest model

**Files:**
- Create: `Modules/IntensiveCare/Models/IcuAdmissionRequest.php`

- [ ] **Step 1: Create model**

Follow `Modules/RadiologyLab/Models/TestOrder.php` pattern exactly. Include:
- All STATUS_* constants, STATUSES array, ALLOWED_TRANSITIONS
- CASE_TYPES, URGENCY_LEVELS, PAYMENT_METHODS, GENDER_OPTIONS constants
- Auto-numbering in boot() with `ICU-YYYY-XXXX` format (copy TestOrder::boot logic)
- canTransitionTo() method
- All relationships: patient, hospital, icuDepartment, respondedBy
- Scopes: byPatient, byHospital, byStatus, pending, urgent
- Accessors: status_badge, urgency_badge, payment_badge, case_type_label
- registerMediaCollections() defining 'medical_reports' collection (multiple files), same pattern as Hospital's logo/gallery in Task 6

- [ ] **Step 2: Commit**

```bash
git add Modules/IntensiveCare/Models/IcuAdmissionRequest.php
git commit -m "feat(intensive-care): add IcuAdmissionRequest model with status state machine"
```

---

## Phase 3: Factories & Unit Tests (Tasks 9-14)

### Task 9: Create factories

**Files:**
- Create: `Modules/IntensiveCare/database/factories/HospitalFactory.php`
- Create: `Modules/IntensiveCare/database/factories/IcuDepartmentFactory.php`
- Create: `Modules/IntensiveCare/database/factories/IcuAdmissionRequestFactory.php`

- [ ] **Step 1: Create HospitalFactory**

Follow `Modules/RadiologyLab/database/factories/LabTestCategoryFactory.php` pattern. States: `government()`, `private()`, `active()`, `inactive()`.

- [ ] **Step 2: Create IcuDepartmentFactory**

States: `cardiac()`, `neurology()`, `available()`, `full()`, `withVentilator()`.

- [ ] **Step 3: Create IcuAdmissionRequestFactory**

States: `pending()`, `accepted()`, `rejected()`, `infoRequested()`, `critical()`, `urgent()`.

- [ ] **Step 4: Commit**

```bash
git add Modules/IntensiveCare/database/factories/
git commit -m "feat(intensive-care): add model factories"
```

---

### Task 10: Create BaseTest and HospitalTest

**Files:**
- Create: `Modules/IntensiveCare/Tests/Unit/BaseTest.php`
- Create: `Modules/IntensiveCare/Tests/Unit/HospitalTest.php`

- [ ] **Step 1: Create BaseTest**

Copy from `Modules/RadiologyLab/Tests/Unit/BaseTest.php`. Change:
- Namespace to `Modules\IntensiveCare\Tests\Unit`
- Migration paths: core + `Modules/IntensiveCare/database/migrations`
- Table cleanup: `icu_admission_requests`, `icu_departments`, `hospitals`, plus core tables

- [ ] **Step 2: Write HospitalTest**

Test: status cast boolean, rating cast decimal, accepted_insurance cast array, relationships (hasMany icuDepartments, hasMany admissionRequests, belongsTo vendor), slug auto-generation, soft deletes, active scope, inCity scope, hasInsurance scope, HOSPITAL_TYPES constant.

- [ ] **Step 3: Run tests**

```bash
php artisan test --filter=HospitalTest
```
Expected: All pass.

- [ ] **Step 4: Commit**

```bash
git add Modules/IntensiveCare/Tests/
git commit -m "test(intensive-care): add Hospital model unit tests"
```

---

### Task 11: Create IcuDepartmentTest

**Files:**
- Create: `Modules/IntensiveCare/Tests/Unit/IcuDepartmentTest.php`

- [ ] **Step 1: Write tests**

Test: casts (has_ventilator boolean, has_oxygen boolean, daily_price decimal, total_beds integer, available_beds integer), relationships (belongsTo Hospital), SPECIALTY_TYPES constant, EQUIPMENT_LEVELS constant, scopes (active, available, withVentilator, withOxygen, bySpecialty), soft deletes.

- [ ] **Step 2: Run and verify pass**

```bash
php artisan test --filter=IcuDepartmentTest
```

- [ ] **Step 3: Commit**

```bash
git add Modules/IntensiveCare/Tests/Unit/IcuDepartmentTest.php
git commit -m "test(intensive-care): add IcuDepartment model unit tests"
```

---

### Task 12: Create IcuAdmissionRequestTest

**Files:**
- Create: `Modules/IntensiveCare/Tests/Unit/IcuAdmissionRequestTest.php`

- [ ] **Step 1: Write tests — auto-numbering**

Test: auto-generates ICU-YYYY-XXXX format, sequential numbering, manual bypass, soft-deleted included in sequence, year rollover.

- [ ] **Step 2: Write tests — status transitions**

Test all ALLOWED_TRANSITIONS: pending→accepted, pending→rejected, pending→info_requested, pending→cancelled, info_requested→pending, info_requested→cancelled, accepted→cancelled. Also test invalid: accepted→pending (should fail), rejected→anything, cancelled→anything.

- [ ] **Step 3: Write tests — constants, casts, relationships, scopes, accessors**

Test: STATUSES array, CASE_TYPES, URGENCY_LEVELS, PAYMENT_METHODS, GENDER_OPTIONS. Casts: booleans, datetime. Relationships: patient, hospital, icuDepartment, respondedBy. Scopes: byPatient, byHospital, byStatus, pending, urgent. Accessors: status_badge, urgency_badge, case_type_label.

- [ ] **Step 4: Run all tests**

```bash
php artisan test --filter=IcuAdmissionRequestTest
```

- [ ] **Step 5: Commit**

```bash
git add Modules/IntensiveCare/Tests/Unit/IcuAdmissionRequestTest.php
git commit -m "test(intensive-care): add IcuAdmissionRequest model unit tests"
```

---

### Task 13: Run full test suite

- [ ] **Step 1: Run all unit tests**

```bash
php artisan test --testsuite=Unit
```
Expected: All 243+ existing tests pass + new IntensiveCare tests pass.

- [ ] **Step 2: Fix any failures, then commit**

---

## Phase 4: Permissions, Translations & Integration (Tasks 14-18)

### Task 14: Create permission seeder

**Files:**
- Create: `Modules/IntensiveCare/database/seeders/IntensiveCarePermissionSeeder.php`
- Create: `Modules/IntensiveCare/database/seeders/IntensiveCareDatabaseSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Create permission seeder**

Follow `Modules/RadiologyLab/database/seeders/RadiologyLabPermissionSeeder.php` pattern exactly.

New role: `hospital_coordinator` (name, title: 'Hospital Coordinator', is_fixed: true).

Permission groups:
- Hospital: `view_hospitals`, `add_hospitals`, `edit_hospitals`, `delete_hospitals`
- ICU Departments: `view_icu_departments`, `add_icu_departments`, `edit_icu_departments`, `delete_icu_departments`
- Admission Requests: `view_icu_admission_requests`, `manage_icu_admission_requests`

Assignments:
- admin/demo_admin: all permissions
- hospital_coordinator: `view_hospitals`, `edit_hospitals`, `view_icu_departments`, `add_icu_departments`, `edit_icu_departments`, `view_icu_admission_requests`, `manage_icu_admission_requests`
- doctor: `view_hospitals`, `view_icu_departments`

- [ ] **Step 2: Create empty IntensiveCareDatabaseSeeder**

- [ ] **Step 3: Add to DatabaseSeeder**

Add line after RadiologyLab seeder (line ~50 in `database/seeders/DatabaseSeeder.php`):
```php
$this->call(\Modules\IntensiveCare\database\seeders\IntensiveCarePermissionSeeder::class);
```

- [ ] **Step 4: Commit**

```bash
git add Modules/IntensiveCare/database/seeders/ database/seeders/DatabaseSeeder.php
git commit -m "feat(intensive-care): add permission seeder with hospital_coordinator role"
```

---

### Task 15: Create PermissionSeederTest

**Files:**
- Create: `Modules/IntensiveCare/Tests/Unit/PermissionSeederTest.php`

- [ ] **Step 1: Write tests**

Follow `Modules/RequestNurse/Tests/Unit/PermissionSeederTest.php` pattern. Test: creates hospital_coordinator role, creates all permissions, admin has all, demo_admin has all, hospital_coordinator has correct subset, doctor has view-only, seeder is idempotent.

- [ ] **Step 2: Run and commit**

```bash
php artisan test --filter=PermissionSeederTest
git add Modules/IntensiveCare/Tests/Unit/PermissionSeederTest.php
git commit -m "test(intensive-care): add permission seeder tests"
```

---

### Task 16: Create translation file

**Files:**
- Create: `lang/en/intensive_care.php`
- Modify: `lang/en/sidebar.php`

- [ ] **Step 1: Create intensive_care.php**

Follow `lang/en/radiology_lab.php` structure. Include keys for:
- Page titles: `singular_title`, `plural_title`, `hospital_catalog`, `hospital_detail`, `admission_request`, `my_icu_requests`, etc.
- Field labels (lbl_ prefix): all model fields
- Status labels: `status_pending`, `status_accepted`, `status_rejected`, `status_info_requested`, `status_cancelled`
- Case types: `case_stroke`, `case_cardiac`, etc.
- Urgency: `urgency_critical`, `urgency_urgent`, `urgency_standard`
- Equipment: `equipment_basic`, `equipment_advanced`, `equipment_full`
- Hospital types: `type_government`, `type_private`, `type_military`, `type_university`
- Messages: success/error messages
- Filter labels

- [ ] **Step 2: Add sidebar keys**

Add after line 114 in `lang/en/sidebar.php`:
```php
'intensive_care' => 'INTENSIVE CARE',
'icu_admission_requests' => 'Admission Requests',
'hospitals' => 'Hospitals',
'icu_departments' => 'ICU Departments',
```

- [ ] **Step 3: Commit**

```bash
git add lang/en/intensive_care.php lang/en/sidebar.php
git commit -m "feat(intensive-care): add translation files"
```

---

### Task 17: Add sidebar menu entries

**Files:**
- Modify: `app/Http/Middleware/GenerateMenus.php` (after line 581, after diagnostics section)

- [ ] **Step 1: Add INTENSIVE CARE sidebar section**

Insert after the diagnostics closing brace (line ~581):

```php
// --- INTENSIVE CARE ---
$icuPermissions = ['view_icu_admission_requests', 'view_hospitals', 'view_icu_departments'];
if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'hospital_coordinator']) && collect($icuPermissions)->contains(fn($permission) => auth()->user()->can($permission))) {
    $this->staticMenu($menu, ['title' => __('sidebar.intensive_care'), 'order' => 0]);
}

if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'hospital_coordinator'])) {
    $intensiveCare = $this->parentMenu($menu, [
        'icon' => 'ph ph-heartbeat',
        'title' => __('sidebar.intensive_care'),
        'route' => 'backend.icu-admission-requests.index',
        'permission' => ['view_icu_admission_requests'],
        'nickname' => 'intensive_care',
        'order' => 0,
    ]);

    $this->childMain($intensiveCare, [
        'icon' => 'ph ph-clipboard-text',
        'title' => __('sidebar.icu_admission_requests'),
        'route' => 'backend.icu-admission-requests.index',
        'active' => 'app/icu-admission-requests',
        'permission' => ['view_icu_admission_requests'],
        'order' => 0,
    ]);

    if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
        $this->childMain($intensiveCare, [
            'icon' => 'ph ph-buildings',
            'title' => __('sidebar.hospitals'),
            'route' => 'backend.hospitals.index',
            'active' => 'app/hospitals',
            'permission' => ['view_hospitals'],
            'order' => 0,
        ]);
    }

    $this->childMain($intensiveCare, [
        'icon' => 'ph ph-bed',
        'title' => __('sidebar.icu_departments'),
        'route' => 'backend.icu-departments.index',
        'active' => 'app/icu-departments',
        'permission' => ['view_icu_departments'],
        'order' => 0,
    ]);
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Middleware/GenerateMenus.php
git commit -m "feat(intensive-care): add sidebar menu entries"
```

---

### Task 18: Add dashboard redirect and middleware updates

**Files:**
- Modify: `app/Http/Controllers/Backend/BackendController.php` (~line 45)
- Modify: `app/Http/Middleware/CheckHeaderMenuSettings.php` (~line 30)
- Modify: `config/constant.php`

- [ ] **Step 1: Add hospital_coordinator redirect**

In BackendController::index(), after lab_technician redirect:
```php
if (auth()->user()->hasRole('hospital_coordinator')) {
    return redirect()->route('backend.icu-admission-requests.index');
}
```

- [ ] **Step 2: Add route to CheckHeaderMenuSettings**

Add to `$routeSettingsMap`:
```php
'icu-hospital' => 'icu_hospitals',
```

- [ ] **Step 3: Add modules to config/constant.php**

Add to MODULES array (after Test Orders):
```php
[
    'module_name' => 'Hospitals',
    'is_custom_permission' => 0,
],
[
    'module_name' => 'Icu Departments',
    'is_custom_permission' => 0,
],
[
    'module_name' => 'Icu Admission Requests',
    'is_custom_permission' => 0,
    'more_permission' => ['manage'],
],
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Backend/BackendController.php app/Http/Middleware/CheckHeaderMenuSettings.php config/constant.php
git commit -m "feat(intensive-care): add dashboard redirect, header menu check, permission modules"
```

---

## Phase 5: Admin Backend (Tasks 19-24)

### Task 19: Create admin routes

**Files:**
- Modify: `Modules/IntensiveCare/Routes/web.php`

- [ ] **Step 1: Write admin routes**

Follow `Modules/RadiologyLab/Routes/web.php` exactly. Three route groups: hospitals (CRUD + bulk + status), icu-departments (CRUD + bulk + status + update-beds), icu-admission-requests (index + show + respond + status + bulk).

- [ ] **Step 2: Commit**

---

### Task 20: Create HospitalsController

**Files:**
- Create: `Modules/IntensiveCare/Http/Controllers/Backend/HospitalsController.php`
- Create: `Modules/IntensiveCare/Http/Requests/HospitalFormRequest.php`
- Create: `Modules/IntensiveCare/Resources/views/backend/hospitals/index.blade.php`
- Create: `Modules/IntensiveCare/Resources/views/backend/hospitals/create.blade.php`
- Create: `Modules/IntensiveCare/Resources/views/backend/hospitals/datatable/action_column.blade.php`

- [ ] **Step 1: Create FormRequest**

Validate: name (required, translatable array), hospital_type (required, in HOSPITAL_TYPES), address/city/phone (required), email (nullable, email), latitude/longitude (nullable, numeric), accepted_insurance (nullable, array), status (nullable, boolean).

- [ ] **Step 2: Create controller**

Standard CRUD methods following `Modules/RadiologyLab/Http/Controllers/Backend/LabTestCategoriesController.php` pattern: index, index_data (DataTables), create, store, edit, update, destroy, bulk_action, update_status, action (restore/force-delete).

Coordinator filter: if role is hospital_coordinator, filter to `where('vendor_id', auth()->id())`.

- [ ] **Step 3: Create views**

Index: DataTable with columns (name, type, city, departments count, status, actions). Include translatable fields in form using `@include('backend.components.translatable-input')`.
Create: form with all hospital fields, media upload for logo.

- [ ] **Step 4: Commit**

```bash
git add Modules/IntensiveCare/Http/ Modules/IntensiveCare/Resources/
git commit -m "feat(intensive-care): add hospitals admin CRUD"
```

---

### Task 21: Create IcuDepartmentsController

**Files:**
- Create: `Modules/IntensiveCare/Http/Controllers/Backend/IcuDepartmentsController.php`
- Create: `Modules/IntensiveCare/Http/Requests/IcuDepartmentFormRequest.php`
- Create: `Modules/IntensiveCare/Resources/views/backend/departments/index.blade.php`
- Create: `Modules/IntensiveCare/Resources/views/backend/departments/create.blade.php`
- Create: `Modules/IntensiveCare/Resources/views/backend/departments/datatable/action_column.blade.php`

- [ ] **Step 1: Create FormRequest, controller, views**

Filter by hospital. Coordinator sees own hospital only. Include `update_beds` action for quick bed count update. DataTable columns: name, hospital, specialty, beds (available/total), ventilator, oxygen, equipment, price, status.

- [ ] **Step 2: Commit**

```bash
git add Modules/IntensiveCare/Http/ Modules/IntensiveCare/Resources/
git commit -m "feat(intensive-care): add ICU departments admin CRUD"
```

---

### Task 22: Create IcuAdmissionRequestsController

**Files:**
- Create: `Modules/IntensiveCare/Http/Controllers/Backend/IcuAdmissionRequestsController.php`
- Create: `Modules/IntensiveCare/Resources/views/backend/requests/index.blade.php`
- Create: `Modules/IntensiveCare/Resources/views/backend/requests/show.blade.php`
- Create: `Modules/IntensiveCare/Resources/views/backend/requests/datatable/action_column.blade.php`

- [ ] **Step 1: Create controller**

Methods: index (with stats: total/pending/accepted/rejected counts), index_data (DataTables), show (detail page), update_status (with canTransitionTo validation), respond (accept/reject/request info with notes), bulk_action.

Coordinator filter: `where('hospital_id', coordinator's hospital)`.

Use `sendNotificationOnBookingUpdate()` with `function_exists()` guard on status changes.

- [ ] **Step 2: Create views**

Index: stats cards + DataTable (request_number, patient_name, hospital, case_type, urgency, status, date, actions).
Show: full request detail + response form (accept/reject/info_requested buttons + admin_notes textarea + rejection_reason).

- [ ] **Step 3: Commit**

```bash
git add Modules/IntensiveCare/Http/ Modules/IntensiveCare/Resources/
git commit -m "feat(intensive-care): add admission requests admin management"
```

---

### Task 23: Verify admin backend works

- [ ] **Step 1: Clear caches and test routes**

```bash
php artisan optimize:clear
php artisan route:list --path=app/hospitals 2>/dev/null || echo "Check manually"
```

- [ ] **Step 2: Run unit tests to ensure no regressions**

```bash
php artisan test --testsuite=Unit
```

- [ ] **Step 3: Commit any fixes**

---

## Phase 6: Frontend Views (Tasks 24-29)

### Task 24: Create IcuFrontController

**Files:**
- Create: `Modules/Frontend/Http/Controllers/IcuFrontController.php`
- Create: `Modules/IntensiveCare/Http/Requests/IcuAdmissionRequestFormRequest.php`

- [ ] **Step 0: Create IcuAdmissionRequestFormRequest**

Validate all admission request fields per spec section 2.3. Key conditional rules:
- `insurance_number`: required when `payment_method` is `insurance` → use `required_if:payment_method,insurance`
- `icu_department_id`: must belong to selected `hospital_id` → use `exists:icu_departments,id` + custom rule checking hospital_id
- `items` array: `case_type` required and in CASE_TYPES, `urgency` in URGENCY_LEVELS, `patient_gender` in GENDER_OPTIONS
- File validation: `medical_reports.*` → nullable, file, max:10240, mimes:pdf,jpg,png,doc,docx

- [ ] **Step 1: Create controller**

Follow `Modules/Frontend/Http/Controllers/LabTestFrontController.php` pattern exactly. Methods:

- `hospitalCatalog()` — load view with filter data
- `hospital_index_data(Request)` — AJAX JSON with filters (city, specialty, ventilator, oxygen, equipment_level, insurance, price_min, price_max, search)
- `hospitalDetail($id)` — hospital + departments detail
- `requestForm(Request)` — admission form (optionally pre-select hospital via ?hospital_id)
- `store(Request)` — create admission request with DB::transaction, validate, send notification
- `myRequests()` — list view
- `myRequests_index_data(Request)` — AJAX JSON with status filter
- `requestDetail($id)` — detail (auth check: patient_id = auth()->id())
- `respond(Request, $id)` — patient responds to info_requested (transitions back to pending)
- `cancel(Request, $id)` — cancel using canTransitionTo()

- [ ] **Step 2: Commit**

```bash
git add Modules/Frontend/Http/Controllers/IcuFrontController.php
git commit -m "feat(intensive-care): add frontend controller"
```

---

### Task 25: Add frontend routes

**Files:**
- Modify: `Modules/Frontend/Routes/web.php`

- [ ] **Step 1: Add public routes**

Inside `check.header.menu` middleware group (after lab test routes, ~line 76):
```php
// ICU hospital public routes
Route::get('/icu-hospitals', [IcuFrontController::class, 'hospitalCatalog'])->name('icu-hospitals');
Route::get('/icu-hospital-details/{id}', [IcuFrontController::class, 'hospitalDetail'])->name('icu-hospital-details');
```

- [ ] **Step 2: Add authenticated routes**

Inside `['auth', 'user_check']` group (after lab test auth routes, ~line 144):
```php
// ICU admission auth routes
Route::get('/request-icu-admission', [IcuFrontController::class, 'requestForm'])->name('request-icu-admission');
Route::post('/request-icu-admission/store', [IcuFrontController::class, 'store'])->name('request-icu-admission.store');
Route::get('/my-icu-requests', [IcuFrontController::class, 'myRequests'])->name('my-icu-requests');
Route::get('/my-icu-requests/{id}', [IcuFrontController::class, 'requestDetail'])->name('my-icu-requests.detail');
Route::get('/my-icu-requests/index-data', [IcuFrontController::class, 'myRequests_index_data'])->name('my-icu-requests.index_data');
Route::post('/cancel-icu-request/{id}', [IcuFrontController::class, 'cancel'])->name('cancel-icu-request');
Route::post('/respond-icu-request/{id}', [IcuFrontController::class, 'respond'])->name('respond-icu-request');
```

- [ ] **Step 3: Add AJAX route at root level**

After line ~153 (with other index_data routes):
```php
Route::get('icu_hospital_index_data', [IcuFrontController::class, 'hospital_index_data'])->name('icu-hospital.index_data');
```

- [ ] **Step 4: Add use statement**

Add at top of file: `use Modules\Frontend\Http\Controllers\IcuFrontController;`

- [ ] **Step 5: Commit**

```bash
git add Modules/Frontend/Routes/web.php
git commit -m "feat(intensive-care): add frontend routes"
```

---

### Task 26: Create hospital catalog view

**Files:**
- Create: `Modules/Frontend/Resources/views/icu_hospitals.blade.php`

- [ ] **Step 1: Create view**

Follow `Modules/Frontend/Resources/views/lab_test_catalog.blade.php` pattern exactly. Extends `frontend::layouts.master`. Include:
- Breadcrumb
- Filter section: city (Select2), specialty_type (Select2 from IcuDepartment::SPECIALTY_TYPES), ventilator checkbox, oxygen checkbox, equipment_level (Select2), insurance (text input), price range (min/max), search input
- Shimmer loader placeholders
- AJAX-loaded card grid: hospital name, city, specialties badges, available beds count, price range, rating stars
- Card click → hospital detail page

- [ ] **Step 2: Commit**

```bash
git add Modules/Frontend/Resources/views/icu_hospitals.blade.php
git commit -m "feat(intensive-care): add hospital catalog frontend view"
```

---

### Task 27: Create hospital detail view

**Files:**
- Create: `Modules/Frontend/Resources/views/icu_hospital_detail.blade.php`

- [ ] **Step 1: Create view**

Follow `Modules/Frontend/Resources/views/lab_test_detail.blade.php` two-column pattern.
- Sidebar: logo, name, hospital_type badge, rating, address, phone/email, accepted insurance pills, CTA "Request Admission" button
- Main: description, ICU departments table (name, specialty, beds, equipment, ventilator/oxygen icons, daily price)

- [ ] **Step 2: Commit**

---

### Task 28: Create admission request form view

**Files:**
- Create: `Modules/Frontend/Resources/views/request_icu_admission.blade.php`

- [ ] **Step 1: Create view**

Follow `Modules/Frontend/Resources/views/order_test_form.blade.php` pattern. Multi-section form:
- Section 1: Hospital select (pre-filled if coming from detail) + department select (dynamically loaded based on hospital)
- Section 2: Patient info fields
- Section 3: Case details (case_type select, urgency select, checkboxes for ventilator/oxygen/ambulance)
- Section 4: Medical condition textarea + diagnosis + file upload (multiple)
- Section 5: Contact info + payment method (toggles insurance fields)
- Sticky sidebar: selected hospital info + estimated price

- [ ] **Step 2: Commit**

---

### Task 29: Create request tracking views

**Files:**
- Create: `Modules/Frontend/Resources/views/my_icu_requests.blade.php`
- Create: `Modules/Frontend/Resources/views/icu_request_detail.blade.php`

- [ ] **Step 1: Create my_icu_requests.blade.php**

Follow `Modules/Frontend/Resources/views/my_test_orders.blade.php` exactly. Status filter tabs (all/pending/accepted/rejected/info_requested/cancelled), AJAX-loaded cards, load more.

- [ ] **Step 2: Create icu_request_detail.blade.php**

Follow `Modules/Frontend/Resources/views/test_order_detail.blade.php` pattern. Sidebar: request number, status/urgency/payment badges, hospital name, dates, action buttons (cancel, respond to info request). Main: patient info, case details, medical reports section, admin response, cancellation alert. Include cancel modal and respond-to-info modal.

- [ ] **Step 3: Commit**

```bash
git add Modules/Frontend/Resources/views/
git commit -m "feat(intensive-care): add frontend request tracking views"
```

---

## Phase 7: API & Final Integration (Tasks 30-33)

### Task 30: Create API controllers and routes

**Files:**
- Create: `Modules/IntensiveCare/Http/Controllers/Backend/API/HospitalsApiController.php`
- Create: `Modules/IntensiveCare/Http/Controllers/Backend/API/IcuAdmissionRequestsApiController.php`
- Create: `Modules/IntensiveCare/Transformers/HospitalResource.php`
- Create: `Modules/IntensiveCare/Transformers/IcuDepartmentResource.php`
- Create: `Modules/IntensiveCare/Transformers/IcuAdmissionRequestResource.php`
- Modify: `Modules/IntensiveCare/Routes/api.php`

- [ ] **Step 1: Create API Resources (Transformers)**

Follow `Modules/RadiologyLab/Transformers/` pattern. HospitalResource includes nested departments. IcuAdmissionRequestResource includes nested hospital and department.

- [ ] **Step 2: Create HospitalsApiController**

Methods: index (paginated with filters), show (detail with departments).

- [ ] **Step 3: Create IcuAdmissionRequestsApiController**

Methods: index (role-filtered), store (create with validation), show, cancel. Follow `Modules/RadiologyLab/Http/Controllers/Backend/API/TestOrdersApiController.php`.

- [ ] **Step 4: Write API routes**

```php
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/hospitals', [HospitalsApiController::class, 'index']);
    Route::get('/hospitals/{id}', [HospitalsApiController::class, 'show']);
    Route::get('/icu-departments', [HospitalsApiController::class, 'departments']);

    Route::get('/icu-admission-requests', [IcuAdmissionRequestsApiController::class, 'index']);
    Route::post('/icu-admission-requests', [IcuAdmissionRequestsApiController::class, 'store']);
    Route::get('/icu-admission-requests/{id}', [IcuAdmissionRequestsApiController::class, 'show']);
    Route::post('/icu-admission-requests/{id}/cancel', [IcuAdmissionRequestsApiController::class, 'cancel']);
});
```

- [ ] **Step 5: Commit**

```bash
git add Modules/IntensiveCare/Http/Controllers/Backend/API/ Modules/IntensiveCare/Transformers/ Modules/IntensiveCare/Routes/api.php
git commit -m "feat(intensive-care): add API controllers and routes"
```

---

### Task 31: Add header navigation link

**Files:**
- Modify: `Modules/Frontend/Resources/views/layouts/header.blade.php`

- [ ] **Step 1: Add ICU Hospitals link**

Find existing navigation links (Available Nurses, Lab Tests, Book a Doctor) and add:
```blade
@if(isset($headerSettings['icu_hospitals']) && $headerSettings['icu_hospitals'])
    <li class="nav-item">
        <a class="nav-link" href="{{ route('icu-hospitals') }}">
            {{ __('intensive_care.hospital_catalog') }}
        </a>
    </li>
@endif
```

- [ ] **Step 2: Commit**

```bash
git add Modules/Frontend/Resources/views/layouts/header.blade.php
git commit -m "feat(intensive-care): add ICU Hospitals to frontend navigation"
```

---

### Task 32: Final test run and cleanup

- [ ] **Step 1: Clear all caches**

```bash
php artisan optimize:clear
composer dump-autoload
```

- [ ] **Step 2: Run full unit test suite**

```bash
php artisan test --testsuite=Unit
```
Expected: All tests pass (existing + new IntensiveCare tests).

- [ ] **Step 3: Run linter**

```bash
./vendor/bin/pint
```

- [ ] **Step 4: Commit lint fixes**

```bash
git add -A
git commit -m "style(intensive-care): apply code style fixes"
```

---

### Task 33: Final commit and summary

- [ ] **Step 1: Verify git status is clean**

```bash
git status
git log --oneline -15
```

- [ ] **Step 2: Tag completion**

All phases complete. Module is functional with:
- 3 database tables + migrations
- 3 models with full business logic
- Hospital coordinator role + 10 permissions
- Admin CRUD for hospitals, departments, admission requests
- Frontend: hospital catalog, detail, admission form, request tracking
- API endpoints for mobile app
- Unit tests for all models
- Translations, sidebar menu, notifications
