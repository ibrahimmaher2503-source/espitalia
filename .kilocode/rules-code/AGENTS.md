# AGENTS.md - Code Mode

This file provides guidance to agents when working with code in this repository.

## Code Mode Specific Rules

### Module Development
- All module models MUST extend `App\Models\BaseModel` - never extend `Illuminate\Database\Eloquent\Model` directly
- When creating new modules, always run `php artisan module:enable ModuleName && php artisan optimize:clear` after setup
- Module assets in `Resources/assets/js/app.js` and `Resources/assets/sass/app.scss` are auto-bundled by Laravel Mix to `public/modules/{module_name_lowercase}/`

### Controller Implementation
- Set `$this->module_title` and `$this->module_name` in constructor (required for breadcrumbs/titles)
- DataTables MUST use server-side rendering via `index_data()` method returning paginated JSON
- Bulk actions pattern: POST to `bulk_action` with `ids[]` array and `action_type` string
- Status toggle: POST to `update_status/{id}` with `status` field in request body

### Model Conventions
- Use `slug_format()` helper from `app/helpers.php` for slug generation, NOT Laravel's `Str::slug()`
- Name and slug attributes are auto-trimmed via BaseModel mutators - don't manually trim
- Status badges: use `$model->status_label` accessor for HTML badges (0=Inactive, 1=Active, 2=Pending)
- MediaLibrary conversions: `thumb` (250x250) and `thumb300` (300x300) auto-generated

### Route Registration
- Admin routes in `Routes/web.php`: prefix `app/`, named `backend.*`, middleware `['auth', 'auth_check']`
- API routes in `Routes/api.php`: middleware `auth:sanctum`, prefix `v1`, named `api.*`
- Module routes auto-registered via RouteServiceProvider - no manual registration needed

### Menu System
- Add sidebar entries in `app/Http/Middleware/GenerateMenus.php` (not in module files)
- Use helpers from `app/Trait/Menu.php`: `staticMenu()`, `mainRoute()`, `parentMenu()`, `childMain()`, `popupMenu()`
- Role-based: admin, doctor, receptionist, vendor, user

### Translation Keys
- Use `lbl_` prefix for labels (e.g., `lbl_name`, `lbl_status`)
- Database translations from `Modules/Language` override file translations
- Vue: use `$t('module_name.key')` - auto-loaded from `window.localMessagesUpdate`

### API Responses
- Use Transformers in `Modules/*/Transformers/` for API responses (not Resources)
- Transformers provide consistent API output format across all modules

### Critical Gotchas
- BaseModel boot events skip `created_by`/`updated_by` for `api.quick_bookings.store` route
- Module migrations: run `php artisan module:migrate ModuleName` (not `php artisan migrate`)
- After module enable/disable: always run `php artisan optimize:clear`
- Use `setting('key')` helper for settings, not `Setting::get()`
