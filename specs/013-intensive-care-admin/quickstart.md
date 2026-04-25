# Quickstart: Intensive Care Admin Platform

**Branch**: `013-intensive-care-admin` | **Date**: 2026-04-05

## Prerequisites

- PHP 8.2+, Composer, Node.js 18+
- MySQL/MariaDB (or SQLite for testing)
- The `003-intensive-care` base module must be scaffolded (hospitals, icu_departments, icu_admission_requests tables exist)
- The Location module must be enabled (governorates, governorate_cities tables exist)

## Setup

```bash
# 1. Switch to feature branch
git checkout 013-intensive-care-admin

# 2. Install dependencies
composer install
npm install

# 3. Run migrations (applies bilingual + commission migrations)
php artisan module:migrate IntensiveCare

# 4. Seed permissions
php artisan db:seed --class="Modules\IntensiveCare\Database\Seeders\IntensiveCareCommissionPermissionSeeder"

# 5. Clear caches
php artisan optimize:clear

# 6. Build assets
npm run dev

# 7. Start dev server
php artisan serve
```

## Verify Installation

```bash
# Check module is enabled
php artisan module:list | grep IntensiveCare

# Check new migrations ran
php artisan migrate:status | grep icu

# Check routes registered
php artisan route:list --path=app/hospitals
php artisan route:list --path=app/icu-commissions
php artisan route:list --path=app/icu-earnings
php artisan route:list --path=v1/hospitals

# Run tests
php artisan test --filter=IntensiveCare
```

## Key Paths

| What | Path |
|------|------|
| Models | `Modules/IntensiveCare/Models/` |
| Admin Controllers | `Modules/IntensiveCare/Http/Controllers/Backend/` |
| API Controllers | `Modules/IntensiveCare/Http/Controllers/Backend/API/` |
| Form Requests | `Modules/IntensiveCare/Http/Requests/` |
| API Resources | `Modules/IntensiveCare/Transformers/` |
| Admin Views | `Modules/IntensiveCare/Resources/views/backend/` |
| Routes (web) | `Modules/IntensiveCare/Routes/web.php` |
| Routes (API) | `Modules/IntensiveCare/Routes/api.php` |
| Migrations | `Modules/IntensiveCare/database/migrations/` |
| Commission Service | `Modules/IntensiveCare/Services/IcuCommissionService.php` |
| Tests | `Modules/IntensiveCare/Tests/Unit/` |
| EN Translations | `lang/en/intensive_care.php` |
| AR Translations | `lang/ar/intensive_care.php` |
| Sidebar | `app/Http/Middleware/GenerateMenus.php` (lines ~686-750) |

## Admin Panel Access

1. Log in as admin: `http://localhost:8000/login`
2. Navigate to sidebar: **Intensive Care** section
3. Available pages:
   - **Admission Requests** — `/app/icu-admission-requests`
   - **Hospitals** — `/app/hospitals`
   - **ICU Departments** — `/app/icu-departments`
   - **ICU Commissions** — `/app/icu-commissions`
   - **ICU Earnings** — `/app/icu-earnings`

## API Testing

```bash
# Get auth token
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' | jq -r '.data.token')

# List hospitals (English)
curl -s http://localhost:8000/api/v1/hospitals \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept-Language: en" | jq

# List hospitals (Arabic)
curl -s http://localhost:8000/api/v1/hospitals \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept-Language: ar" | jq

# Filter by governorate
curl -s "http://localhost:8000/api/v1/hospitals?governorate_id=1" \
  -H "Authorization: Bearer $TOKEN" | jq

# Create admission request
curl -s -X POST http://localhost:8000/api/v1/icu-admission-requests \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "hospital_id": 1,
    "icu_department_id": 1,
    "patient_name": "Test Patient",
    "patient_age": 50,
    "patient_gender": "male",
    "medical_condition_en": "Test condition",
    "case_type": "general",
    "urgency": "standard",
    "contact_name": "Test Contact",
    "contact_phone": "+966500000000",
    "payment_method": "cash"
  }' | jq
```

## Running Tests

```bash
# All IntensiveCare tests
php artisan test --filter=IntensiveCare

# Specific test classes
php artisan test --filter=HospitalModelTest
php artisan test --filter=IcuCommissionServiceTest
php artisan test --filter=IcuAdmissionRequestTest

# With coverage
php artisan test --filter=IntensiveCare --coverage
```
