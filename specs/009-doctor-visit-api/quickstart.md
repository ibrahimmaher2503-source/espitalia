# Quickstart: Doctor Visit Request API

**Feature**: `009-doctor-visit-api`

---

## Prerequisites

- PHP 8.2+, Composer, Node.js (for asset build)
- MySQL running with the project database configured in `.env`
- Existing Espitalia project dependencies installed (`composer install`, `npm install`)

---

## Setup Steps

### 1. Switch to feature branch

```bash
git checkout 009-doctor-visit-api
```

### 2. Run migrations

```bash
php artisan module:migrate DoctorVisit
```

This creates:
- `doctor_visit_requests` table
- `doctor_visit_status_histories` table

### 3. Run the permission seeder

```bash
php artisan module:seed DoctorVisit
```

This registers permissions (`doctor_visit_request.view`, `doctor_visit_request.create`, `doctor_visit_request.update_status`, `doctor_visit_request.assign_doctor`) and assigns them to the `admin`, `receptionist`, and `doctor` roles.

### 4. Enable the module

```bash
php artisan module:enable DoctorVisit
php artisan optimize:clear
```

### 5. Verify module is active

```bash
php artisan module:list
# DoctorVisit should appear as Enabled
```

### 6. Verify routes are registered

```bash
php artisan route:list --path=doctor-visit
```

Expected routes:
```
POST   api/v1/doctor-visit/requests               api.doctor_visit.store
GET    api/v1/doctor-visit/requests               api.doctor_visit.index
GET    api/v1/doctor-visit/requests/{reference}   api.doctor_visit.show
GET    api/v1/admin/doctor-visit/requests         api.admin.doctor_visit.index
PUT    api/v1/admin/doctor-visit/requests/{ref}/status         api.admin.doctor_visit.update_status
PUT    api/v1/admin/doctor-visit/requests/{ref}/assign-doctor  api.admin.doctor_visit.assign_doctor
POST   api/v1/admin/doctor-visit/requests/{ref}/notes          api.admin.doctor_visit.add_note
GET    app/doctor-visit                           backend.doctor_visit.index
GET    app/doctor-visit/{id}                      backend.doctor_visit.show
```

### 7. Run tests

```bash
php artisan test --filter=DoctorVisit
```

All tests should pass against the SQLite in-memory database.

---

## Quick API Test (curl)

### Submit a visit request (as patient)

```bash
# Get a Sanctum token first
TOKEN="your-sanctum-token"

curl -X POST http://localhost:8000/api/v1/doctor-visit/requests \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "visit_reason": "Persistent fever for 2 days",
    "preferred_date": "2026-04-15",
    "contact_phone": "+966501234567"
  }'
```

Expected response:
```json
{
  "status": true,
  "data": {
    "reference_number": "VR-2026-0001",
    "status": "pending",
    ...
  },
  "message": "doctor_visit.request_submitted_success"
}
```

### Update status (as admin)

```bash
ADMIN_TOKEN="your-admin-sanctum-token"

curl -X PUT http://localhost:8000/api/v1/admin/doctor-visit/requests/VR-2026-0001/status \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{ "status": "confirmed", "note": "Confirmed for April 15 morning." }'
```

---

## Admin Dashboard

Visit: `http://localhost:8000/app/doctor-visit`

The sidebar entry "Doctor Visits" appears for `admin`, `receptionist`, and `doctor` roles under the "Requests" section.

---

## Email Notifications

Status change emails are sent to the patient's registered email address when a request is moved to `confirmed`, `cancelled`, or `completed`. Configure mail settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@espitalia.com
MAIL_FROM_NAME="Espitalia"
```

For local development, use `MAIL_MAILER=log` to write emails to `storage/logs/laravel.log`.
