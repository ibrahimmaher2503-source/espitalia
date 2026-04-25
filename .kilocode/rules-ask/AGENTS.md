# AGENTS.md - Ask Mode

This file provides guidance to agents when working with code in this repository.

## Ask Mode Specific Rules

### Architecture Context
- **Modular Laravel 11** using nwidart/laravel-modules v10 - 33+ feature modules under `Modules/`
- **Forked from** nasirkhan/laravel-starter with Hope UI Pro design system
- **Multi-tenant SaaS** - company-based settings, login, and data isolation

### Module Organization (Counterintuitive)
- Each module is self-contained: Models, Controllers, Routes, Views, Migrations, Assets
- Module status tracked in `modules_statuses.json` (not in database)
- Module routes auto-registered via RouteServiceProvider (no manual registration)
- Module assets auto-bundled to `public/modules/{module_name_lowercase}/` by Laravel Mix

### Key Modules
- **Appointment**: Core booking functionality with encounters, billing, prescriptions
- **Clinic**: Multi-vendor clinic management
- **Service**: Service catalog with ratings
- **Customer**: User and patient management
- **Frontend**: Public-facing pages (not admin)
- **FrontendSetting**: Frontend configuration
- **Wallet**: Payment and wallet system
- **Subscriptions**: Subscription plans and limits
- **MultiVendor**: Vendor management
- **Product**: Inventory management
- **Language**: Database-driven translations (override file translations)

### File Structure Notes
- Admin views extend `backend.layouts.app` (in `resources/views/backend/layouts/`)
- Frontend views extend `frontend::layouts.master` (in `Modules/Frontend/Resources/views/layouts/`)
- Sidebar menus built in `app/Http/Middleware/GenerateMenus.php` (not per-module)
- Global helpers in `app/helpers.php` (autoloaded via composer)

### Translation System (Two Systems)
1. **File translations**: `lang/en/*.php` - sidebar.php, messages.php, menu.php, per-module files
2. **Database translations**: `Modules/Language` - takes PRIORITY over file translations
   - This is counterintuitive - database overrides always win
   - Check database first if translations not updating

### Custom Helpers (Non-Standard)
- `setting('key')` - Get settings from `Modules/Setting` (not `Setting::get()`)
- `slug_format()` - Slug generation (NOT Laravel's `Str::slug()`)
- `mail_footer()` - Email footer with user/company info
- `sendNotification()` - Send notifications via template system

### BaseModel Behavior (Important)
- All module models extend `App\Models\BaseModel`
- Auto-sets `created_by`, `updated_by`, `deleted_by` via boot events
- **Exception**: Skips audit fields for `api.quick_bookings.store` route
- Soft deletes enabled by default
- Spatie MediaLibrary integrated with auto-conversions (thumb: 250x250, thumb300: 300x300)

### API Structure
- API routes: prefix `v1`, middleware `auth:sanctum`, named `api.*`
- API controllers in `Modules/*/Http/Controllers/API/`
- Use Transformers in `Modules/*/Transformers/` for API responses (not Resources)

### Testing Setup
- PHPUnit with SQLite in-memory database
- Test directories: `tests/Unit`, `tests/Feature`, `Modules/**/Tests/Unit`, `Modules/**/Tests/Feature`
- Module tests auto-discovered by phpunit.xml configuration

### Common Confusions
- **Module migrations**: Run `php artisan module:migrate ModuleName` (not `php artisan migrate`)
- **Cache clearing**: After module changes, run `php artisan optimize:clear`
- **Menu generation**: Edit `app/Http/Middleware/GenerateMenus.php` (not module files)
- **Translation priority**: Database > Files (database always wins)
- **Slug helper**: Use `slug_format()` not `Str::slug()`
