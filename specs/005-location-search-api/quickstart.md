# Quickstart: Location-Based Healthcare Search API

**Branch**: `005-location-search-api` | **Date**: 2026-03-31

## Prerequisites

- PHP 8.2+, Composer, Node.js 18+
- MySQL/MariaDB running
- Project cloned and `.env` configured

## Setup

```bash
# Switch to feature branch
git checkout 005-location-search-api

# Install dependencies
composer install
npm install

# Run migrations (creates governorates, governorate_cities, labs, radiology_centers,
# home_healthcares tables; adds governorate_id/city_id to doctors, clinic, nurses)
php artisan migrate

# Enable new modules
php artisan module:enable Location
php artisan module:enable HomeHealthcare

# Seed Egyptian governorates and cities
php artisan db:seed --class="Modules\Location\database\seeders\GovernorateSeeder"

# Clear caches
php artisan optimize:clear

# Start dev server
php artisan serve
```

## Verify

```bash
# Test governorates endpoint (English)
curl -H "Accept-Language: en" http://localhost:8000/api/governorates

# Test governorates endpoint (Arabic)
curl -H "Accept-Language: ar" http://localhost:8000/api/governorates

# Test cities for Cairo (governorate_id=1)
curl -H "Accept-Language: ar" "http://localhost:8000/api/cities?governorate_id=1"

# Test doctor search by governorate
curl -H "Accept-Language: en" "http://localhost:8000/api/doctors/search?governorate_id=1"

# Test nurse search with availability filter
curl "http://localhost:8000/api/nurses/search?availability=available"
```

## Run Tests

```bash
# All tests
php artisan test

# Location module tests only
php artisan test --filter=GovernorateTest
php artisan test --filter=GovernorateCityTest

# Search endpoint tests
php artisan test --filter=DoctorSearchTest
php artisan test --filter=ClinicSearchTest
php artisan test --filter=NurseSearchTest
```

## Key Files

| Purpose | Path |
|---------|------|
| Governorate Model | `Modules/Location/Models/Governorate.php` |
| GovernorateCity Model | `Modules/Location/Models/GovernorateCity.php` |
| Governorate Seeder | `Modules/Location/database/seeders/GovernorateSeeder.php` |
| Location API Routes | `Modules/Location/Routes/api.php` |
| Doctor Search Service | `Modules/Clinic/Services/DoctorSearchService.php` |
| Clinic Search Service | `Modules/Clinic/Services/ClinicSearchService.php` |
| Nurse Search Service | `Modules/RequestNurse/Services/NurseSearchService.php` |
| Locale Middleware | `app/Http/Middleware/localization.php` |
| API Contracts | `specs/005-location-search-api/contracts/api-endpoints.md` |
