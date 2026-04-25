# AGENTS.md - Debug Mode

This file provides guidance to agents when working with code in this repository.

## Debug Mode Specific Rules

### Module Debugging
- Module status tracked in `modules_statuses.json` at project root - check this if module routes/controllers not loading
- After enabling/disabling modules, ALWAYS run `php artisan optimize:clear` to clear cached routes/config
- Module routes auto-registered via RouteServiceProvider - if routes not working, check module's RouteServiceProvider

### Cache Issues
- Run `composer clear-all` custom script to clear all caches (includes `spatie.permission.cache`)
- Individual cache clears: `php artisan cache:clear`, `php artisan route:clear`, `php artisan view:clear`, `php artisan config:clear`
- Laravel Telescope disabled by default - enable in `config/app.php` by removing from `extra.dont-discover`

### Common Issues
- **Routes not found**: Check `modules_statuses.json` and run `php artisan optimize:clear`
- **Translations not updating**: Database translations (Modules/Language) override file translations - check database first
- **Assets not loading**: Module assets build to `public/modules/{module_name_lowercase}/` - check if assets exist there
- **created_by/updated_by not set**: BaseModel boot events skip these for `api.quick_bookings.store` route (lines 57, 65, 73 in BaseModel.php)

### Database Debugging
- Module migrations: run `php artisan module:migrate ModuleName` (not `php artisan migrate`)
- Test database: SQLite in-memory (configured in `phpunit.xml`)
- Check `app/Models/BaseModel.php` for auto-audit trail behavior

### API Debugging
- API routes use `auth:sanctum` middleware - check authentication if getting 401
- API prefix: `v1`, named routes: `api.*`
- Check Transformers in `Modules/*/Transformers/` for API response format issues

### Frontend Debugging
- Vue translations auto-loaded from `window.localMessagesUpdate` via app layout
- Check `resources/js/vue/app.js` for Vue app initialization
- Module Vue components in `Modules/*/Resources/assets/js/components/`

### Permission Debugging
- Spatie permissions cached - clear with `php artisan cache:forget spatie.permission.cache`
- Role-based menus: admin, doctor, receptionist, vendor, user
- Check `app/Http/Middleware/GenerateMenus.php` for menu generation issues

### Log Locations
- Laravel logs: `storage/logs/laravel.log`
- Telescope logs: if enabled, access via `/telescope`
- Activity logs: Spatie activitylog in database (configured in `config/activitylog.php`)

### Testing Debugging
- Run specific test: `php artisan test --filter=TestClassName`
- Module tests auto-discovered from `Modules/**/Tests/Unit` and `Modules/**/Tests/Feature`
- Test database: SQLite in-memory - no persistence between tests
