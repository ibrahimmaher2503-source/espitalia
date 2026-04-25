# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Espitalia is a Laravel 11 clinic/salon management SaaS application. It uses a modular architecture (nwidart/laravel-modules v10) with 48+ feature modules under `Modules/`. The project is forked from nasirkhan/laravel-starter and uses Hope UI Pro as its admin design system. Module status is tracked in `modules_statuses.json`.

## Key Commands

```bash
# Install
composer install
npm install

# Dev server
php artisan serve

# Frontend assets
npm run dev          # development (Laravel Mix)
npm run production   # production (versioned)

# Database
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed   # full reset

# Module operations
php artisan module:make ModuleName
php artisan module:make-model ModelName ModuleName -m
php artisan module:make-controller ControllerName ModuleName
php artisan module:migrate ModuleName
php artisan module:seed ModuleName
php artisan module:enable ModuleName
php artisan module:disable ModuleName
php artisan module:list

# Testing (uses SQLite in-memory by default)
php artisan test                           # all tests
php artisan test --testsuite=Unit          # unit only
php artisan test --testsuite=Feature       # feature only
php artisan test --filter=TestClassName    # single test
php artisan test Modules/ModuleName/Tests  # single module tests

# Linting
./vendor/bin/pint                          # PHP code style (Laravel preset)

# Cache management
php artisan optimize:clear
composer dump-autoload

# Route debugging
php artisan route:list --path=your-module
```

## Architecture

### Modular Structure (nwidart/laravel-modules)

Each module under `Modules/` is self-contained with its own Models, Controllers, Routes, Views, Migrations, and Assets. Module status is tracked in `modules_statuses.json` at the project root.

Key modules: Appointment, Clinic, Service, Customer, Frontend (public-facing pages), FrontendSetting, Earning, Wallet, Subscriptions, MultiVendor, Product, Logistic, Blog, FAQ, QuickBooking, RequestNurse, IntensiveCare, RadiologyLab, Pharmacy, DoctorVisit, HomeHealthcare, Location.

### Module File Layout

```
Modules/ModuleName/
├── Http/Controllers/          # Admin controllers
├── Http/Controllers/API/      # API controllers
├── Models/
├── Transformers/              # API Resources
├── Routes/web.php             # Admin routes (prefix: app, as: backend.)
├── Routes/api.php             # API routes (auth:sanctum, prefix: v1)
├── Resources/views/backend/   # Admin Blade views
├── Resources/assets/js/app.js # Vue/JS entry (auto-bundled by webpack.mix.js)
├── Resources/assets/sass/     # Module SCSS (auto-bundled)
├── database/migrations/
├── database/seeders/
└── Config/config.php
```

### Core Application Layer (`app/`)

- **BaseModel** (`app/Models/BaseModel.php`): All module models extend this. Includes SoftDeletes, Spatie MediaLibrary, auto-sets `created_by`/`updated_by`/`deleted_by` via boot events.
- **Menu Trait** (`app/Trait/Menu.php`): Provides `staticMenu()`, `mainRoute()`, `parentMenu()`, `childMain()`, `popupMenu()` helpers for sidebar construction.
- **GenerateMenus middleware** (`app/Http/Middleware/GenerateMenus.php`): Builds the admin sidebar. Role-based (admin, doctor, receptionist, vendor, user). Edit this to add new sidebar entries.
- **helpers.php** (`app/helpers.php`): Global helper functions, autoloaded via composer.

### Roles & Permissions

Uses spatie/laravel-permission. Core roles: `admin`, `demo_admin`, `doctor`, `receptionist`, `vendor`, `user`. Each role gets a different dashboard and sidebar menu.

### Routing Conventions

- **Admin web routes**: Prefix `app/`, named `backend.*`, middleware `['auth', 'auth_check']`
- **API routes**: Middleware `auth:sanctum`, prefix `v1`, named `api.*`
- **Frontend routes**: Defined in `Modules/Frontend/Routes/web.php`, public or `['auth', 'user_check']`
- Each module registers its own routes via its RouteServiceProvider

### Frontend Stack

- **Admin**: Blade templates extending `backend.layouts.app` + jQuery + DataTables + Select2 + Vue 3 (settings/profile pages)
- **Public**: Blade templates in `Modules/Frontend/Resources/views/` extending `frontend::layouts.master`
- **Build**: Laravel Mix (webpack.mix.js) — auto-discovers module JS/SCSS from `Modules/*/Resources/assets/`
- **Icons**: Phosphor Icons (`ph ph-*` classes)
- **Vue entry points**: `resources/js/vue/app.js` (global), `resources/js/setting-vue.js`, `resources/js/profile-vue.js`, `resources/js/vue/booking-form.js`
- **State management**: Pinia stores
- **Forms**: spatie/laravel-html form builder in Blade, VeeValidate + Yup in Vue

### Translation System

- Core translations: `lang/en/*.php` (sidebar.php, messages.php, menu.php, and per-module files)
- Blade: `{{ __('module_name.key') }}` or `@lang('module_name.key')`
- PHP: `trans('module_name.key')` or `__('module_name.key')`
- Vue: `{{ $t('module_name.key') }}` — auto-loaded from `window.localMessagesUpdate` via the app layout
- Database overrides via `Modules/Language` take priority over file translations
- Convention: labels prefixed with `lbl_`, e.g., `lbl_name`, `lbl_status`

### Payment Integrations

Stripe, Razorpay, PayPal are integrated for appointment/subscription payments.

### Testing

PHPUnit with SQLite in-memory database (configured in `phpunit.xml`). Test directories: `tests/Unit`, `tests/Feature`, plus `Modules/**/Tests/Unit` and `Modules/**/Tests/Feature`.

Module tests typically extend a `BaseTest` class that handles:
- SQLite compatibility (disabling foreign keys with `PRAGMA foreign_keys = OFF`)
- Selective migration of core + module-specific tables (avoids MySQL-specific SQL in other modules)
- Test isolation via table cleanup

### Bilingual Support Pattern

Models requiring Arabic/English content use the `_ar`/`_en` suffix pattern:
- Store both versions in separate columns: `name_ar`, `name_en`, `description_ar`, `description_en`
- Add computed accessors that return the locale-appropriate value with fallback:

```php
public function getNameAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;
    return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
}
```

- API Resources should expose both raw fields (`name_ar`, `name_en`) and computed accessors (`name`)
- Admin forms should have separate inputs for each language

### Specs Directory

Feature specifications are tracked in `specs/` with numbered directories (e.g., `013-intensive-care-admin`). Each spec folder contains:
- `spec.md` - Feature requirements
- `plan.md` - Implementation design
- `tasks.md` - Actionable task list
- `data-model.md` - Database schema design
- `contracts/` - API contracts
- `checklists/` - Validation checklists

## Adding a New Module

See `INIT.md` for the complete step-by-step guide with code templates. Summary:

1. `php artisan module:make ModuleName` — scaffold
2. Create Model extending `App\Models\BaseModel` + migration
3. Add translation file at `lang/en/module_name.php` + sidebar key in `lang/en/sidebar.php`
4. Create admin controller with `index`, `index_data`, `create`, `store`, `edit`, `update`, `destroy`, `bulk_action`, `update_status` methods
5. Create Blade views: `index.blade.php` (DataTable list), `create.blade.php` (form), `datatable/action_column.blade.php`
6. Register routes in module's `Routes/web.php` and `Routes/api.php`
7. Add API controller + Resource/Transformer under `Http/Controllers/API/` and `Transformers/`
8. Add sidebar entry in `app/Http/Middleware/GenerateMenus.php`
9. (Optional) Frontend pages via `Modules/Frontend/`
10. (Optional) Permission seeder with spatie/laravel-permission
11. `php artisan module:enable ModuleName && php artisan optimize:clear`

## Conventions

- Admin views extend `backend.layouts.app`; frontend views extend `frontend::layouts.master`
- Controller convention: `$this->module_title` and `$this->module_name` set in constructor
- DataTables use server-side rendering via `index_data()` methods returning paginated JSON
- Bulk actions pattern: POST to `bulk_action` with `ids[]` and `action_type`
- Status toggle: POST to `update_status/{id}` with `status` field
- Blade components: `<x-backend.section-header>`, `<x-backend.quick-action>`
- Module assets build to `public/modules/{module_name_lowercase}/script.js` and `style.css`
- Models extend `App\Models\BaseModel` which auto-sets `created_by`/`updated_by`/`deleted_by` via boot events
- Models with slugs use the `HasSlug` trait from `App\Models\Traits\HasSlug`
- Location relationships: `governorate_id` → `Modules\Location\Models\Governorate`, `city_id` → `Modules\Location\Models\GovernorateCity`
- Commission systems follow the pattern: `Commission` (rate config), `CommissionEarning` (per-transaction), `Payout` (disbursement)
