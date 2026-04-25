# Quickstart: Lab & Radiology Booking API

**Feature Branch**: `008-lab-radiology-booking-api`
**Date**: 2026-04-02

## Prerequisites

1. Application running (`php artisan serve`)
2. Database migrated with RadiologyLab and Location modules
3. Labs and Radiology Centers seeded with data
4. At least one user account for testing

## Implementation Order

### Step 1: Migrations & Models (Foundation)
- Create `facility_sessions` migration and `FacilitySession` model
- Create `facility_bookings` migration and `FacilityBooking` model
- Run migrations

### Step 2: Permissions & Translations
- Add booking permissions to `RadiologyLabPermissionSeeder`
- Add EN/AR translation keys to `lang/en/radiology_lab.php` and `lang/ar/radiology_lab.php`

### Step 3: Services (Business Logic)
- Create `FacilitySlotService` — generates available time slots from sessions, excluding booked slots
- Create `FacilityBookingService` — handles booking creation with double-booking prevention

### Step 4: Form Requests (Validation)
- Create `FacilityBookingRequest` — validates create booking input
- Create `FacilitySlotRequest` — validates slot query input

### Step 5: API Resources (Response Format)
- Create `FacilityBookingResource` — transforms booking for API response
- Create `FacilitySlotResource` — transforms slot data for API response

### Step 6: Controllers (API Endpoints)
- Create `FacilityBookingApiController` — patient endpoints (slots, create, list, show, cancel)
- Create `AdminFacilityBookingApiController` — admin endpoints (list, confirm, cancel, status)

### Step 7: Routes
- Add public slot routes and authenticated booking routes to `Modules/RadiologyLab/Routes/api.php`

### Step 8: Tests
- Feature tests for slot generation, booking CRUD, cancellation, double-booking prevention, admin management

## Verification

```bash
# Run migrations
php artisan migrate

# Seed permissions
php artisan db:seed --class=Modules\\RadiologyLab\\database\\seeders\\RadiologyLabPermissionSeeder

# Clear caches
php artisan optimize:clear

# Test slot endpoint
curl http://localhost:8000/api/v1/facility-bookings/labs/1/slots?date=2026-04-05

# Test booking creation (with auth token)
curl -X POST http://localhost:8000/api/v1/facility-bookings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"type":"lab","lab_id":1,"booking_date":"2026-04-05","booking_time":"09:00","patient_name":"Ahmed","patient_phone":"+20123456789"}'

# Run tests
php artisan test --filter=FacilityBooking
```

## Key Reference Files

| Purpose | File |
|---------|------|
| Reference booking controller | `Modules/Clinic/Http/Controllers/API/CallBookingApiController.php` |
| Reference session model | `Modules/Clinic/Models/DoctorCallSession.php` |
| Reference order API | `Modules/RadiologyLab/Http/Controllers/Backend/API/TestOrdersApiController.php` |
| Existing API routes | `Modules/RadiologyLab/Routes/api.php` |
| Lab model | `Modules/RadiologyLab/Models/Lab.php` |
| RadiologyCenter model | `Modules/RadiologyLab/Models/RadiologyCenter.php` |
| Response helper | `app/Http/Controllers/Controller.php` (sendResponse) |
| Notification helper | `app/helpers.php` (sendNotificationOnBookingUpdate) |
