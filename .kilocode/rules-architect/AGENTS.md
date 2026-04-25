# AGENTS.md - Architect Mode

This file provides guidance to agents when working with code in this repository.

## Architect Mode Specific Rules

### Modular Architecture Constraints
- **Module isolation**: Each module under `Modules/` is self-contained with its own Models, Controllers, Routes, Views, Migrations, Assets
- **Module coupling**: All module models MUST extend `App\Models\BaseModel` - creates tight coupling to core
- **Route registration**: Auto-registered via each module's RouteServiceProvider - no manual route registration needed
- **Asset bundling**: Laravel Mix auto-discovers module assets from `Modules/*/Resources/assets/` and bundles to `public/modules/{module_name_lowercase}/`

### Core Application Layer (Hidden Dependencies)
- **BaseModel**: Central audit trail via boot events (created_by/updated_by/deleted_by) - all modules depend on this
- **Menu generation**: Centralized in `app/Http/Middleware/GenerateMenus.php` - modules don't define their own menus
- **Translation system**: Two-tier system where database translations (Modules/Language) override file translations
- **Settings system**: Centralized in `Modules/Setting` with `setting('key')` helper - modules access settings this way

### Module Status Management
- Module status tracked in `modules_statuses.json` at project root (not database)
- After enabling/disabling modules: MUST run `php artisan optimize:clear` to clear cached routes/config
- Module migrations: Run per-module with `php artisan module:migrate ModuleName`

### API Architecture
- API routes: prefix `v1`, middleware `auth:sanctum`, named `api.*`
- API controllers in `Modules/*/Http/Controllers/API/`
- Use Transformers in `Modules/*/Transformers/` for API responses (not Laravel Resources)
- Transformers provide consistent API output format across all modules

### Frontend Architecture
- **Admin**: Blade templates extending `backend.layouts.app` + jQuery + DataTables + Select2 + Vue 3 (settings/profile pages)
- **Public**: Blade templates in `Modules/Frontend/Resources/views/` extending `frontend::layouts.master`
- **Vue entry points**: `resources/js/vue/app.js` (global), `resources/js/setting-vue.js`, `resources/js/profile-vue.js`, `resources/js/vue/booking-form.js`
- **State management**: Pinia stores
- **Forms**: spatie/laravel-html form builder in Blade, VeeValidate + Yup in Vue

### Role-Based Access Control
- Spatie/laravel-permission for roles and permissions
- Core roles: `admin`, `demo_admin`, `doctor`, `receptionist`, `vendor`, `user`
- Each role gets different dashboard and sidebar menu
- Menus built by `app/Http/Middleware/GenerateMenus.php` - role-based filtering

### Database Architecture
- Soft deletes enabled by default via BaseModel
- Spatie MediaLibrary integrated with auto-conversions (thumb: 250x250, thumb300: 300x300)
- Activity logging via Spatie activitylog
- Multi-tenant: Company-based data isolation

### Testing Architecture
- PHPUnit with SQLite in-memory database (configured in `phpunit.xml`)
- Test directories: `tests/Unit`, `tests/Feature`, `Modules/**/Tests/Unit`, `Modules/**/Tests/Feature`
- Module tests auto-discovered by phpunit.xml configuration

### Performance Considerations
- Module assets auto-bundled to minimize HTTP requests
- Caching: Spatie permissions cached - clear with `php artisan cache:forget spatie.permission.cache`
- Laravel Telescope disabled by default (in `config/app.php` extra.dont-discover)

### Architectural Gotchas
- **Audit trail exception**: BaseModel boot events skip `created_by`/`updated_by` for `api.quick_bookings.store` route
- **Translation priority**: Database translations always override file translations - can cause confusion
- **Module coupling**: All models extend BaseModel - changes to BaseModel affect all modules
- **Centralized menu**: Modules can't define their own menus - must edit `app/Http/Middleware/GenerateMenus.php`
- **Slug helper**: Use custom `slug_format()` from `app/helpers.php`, not Laravel's `Str::slug()`

### Module Creation Workflow
1. `php artisan module:make ModuleName` - scaffold module
2. Create Model extending `App\Models\BaseModel` + migration
3. Add translation file at `lang/en/module_name.php` + sidebar key in `lang/en/sidebar.php`
4. Create admin controller with standard methods: `index`, `index_data`, `create`, `store`, `edit`, `update`, `destroy`, `bulk_action`, `update_status`
5. Create Blade views: `index.blade.php` (DataTable list), `create.blade.php` (form), `datatable/action_column.blade.php`
6. Register routes in module's `Routes/web.php` and `Routes/api.php`
7. Add API controller + Resource/Transformer under `Http/Controllers/API/` and `Transformers/`
8. Add sidebar entry in `app/Http/Middleware/GenerateMenus.php`
9. (Optional) Frontend pages via `Modules/Frontend/`
10. (Optional) Permission seeder with spatie/laravel-permission
11. `php artisan module:enable ModuleName && php artisan optimize:clear`
