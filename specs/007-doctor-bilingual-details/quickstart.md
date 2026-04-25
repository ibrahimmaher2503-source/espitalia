# Quickstart: Bilingual Doctor Details (AR/EN)

**Branch**: `007-doctor-bilingual-details` | **Date**: 2026-04-02

## Prerequisites

- PHP 8.2+, Composer, Node.js 18+, MySQL 8.0+
- Project dependencies installed (`composer install && npm install`)
- Database running with existing schema

## Setup

```bash
# 1. Switch to feature branch
git checkout 007-doctor-bilingual-details

# 2. Run migrations (adds bilingual columns + migrates existing data)
php artisan migrate

# 3. Clear caches
php artisan optimize:clear

# 4. Build frontend assets (if admin form views changed)
npm run dev
```

## Verify

```bash
# 1. Check bilingual columns exist
php artisan tinker --execute="echo Schema::hasColumn('users', 'first_name_ar') ? 'OK' : 'MISSING';"

# 2. Check data migration worked (existing doctors have EN values)
php artisan tinker --execute="echo \App\Models\User::whereHas('doctor')->first()?->first_name_en ?? 'NO DATA';"

# 3. Run tests
php artisan test --filter=DoctorBilingual

# 4. Check locale switching works
# Visit admin doctor form → should show AR/EN input pairs
# Switch locale to Arabic → doctor list should show Arabic names
```

## Key Files Changed

| Layer | Files |
|-------|-------|
| **Migrations** | `Modules/Clinic/database/migrations/2026_04_02_*_add_bilingual_*` |
| **Models** | `app/Models/User.php`, `app/Models/UserProfile.php`, `Modules/Clinic/Models/Doctor.php` |
| **Validation** | `Modules/Clinic/Http/Requests/DoctorRequest.php` |
| **Controllers** | `Modules/Clinic/Http/Controllers/DoctorController.php` (store, update, edit, index_data) |
| **API Resources** | `Modules/Clinic/Transformers/DoctorResource.php`, `DoctorSearchResource.php` |
| **Search** | `Modules/Clinic/Services/DoctorSearchService.php` |
| **Views** | `Modules/Clinic/Resources/views/backend/doctor/` (form views) |
| **Translations** | `lang/en/clinic.php`, `lang/ar/clinic.php` |
| **Tests** | `Modules/Clinic/Tests/Feature/DoctorBilingualTest.php` |

## Architecture Notes

- **Pattern**: Dual-column (`field_ar`/`field_en`) with locale-aware Eloquent accessor — same as Governorate/GovernorateCity
- **Fallback Chain**: Requested locale → other locale → original column → empty string
- **Backward Compat**: Original `first_name`, `last_name`, `experience`, `description`, `expert`, `about_self` columns kept and synced
- **Scope**: Only doctor admin forms expose bilingual inputs. Other user roles (admin, vendor, etc.) are unaffected.
