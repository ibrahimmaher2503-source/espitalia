# API Contracts: Intensive Care Admin Platform

**Branch**: `013-intensive-care-admin` | **Date**: 2026-04-05
**Base URL**: `/api/v1`
**Auth**: All endpoints require `Authorization: Bearer {sanctum_token}`
**Locale**: Set `Accept-Language: ar` or `Accept-Language: en` to control bilingual field output

---

## Hospitals

### GET /hospitals

List hospitals with filters and pagination.

**Query Parameters**:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| page | int | no | Page number (default: 1) |
| per_page | int | no | Items per page (default: 15) |
| governorate_id | int | no | Filter by governorate |
| city_id | int | no | Filter by city |
| specialty | string | no | Filter hospitals that have a department with this specialty type |
| has_ventilator | boolean | no | Filter hospitals with ventilator-equipped departments |
| hospital_type | string | no | Filter by hospital type (government/private/military/university) |
| search | string | no | Search by hospital name (both AR/EN) |
| sort_by | string | no | Sort field: price, rating, availability (default: created_at) |

**Response 200**:

```json
{
  "status": true,
  "message": "Hospital list",
  "data": [
    {
      "id": 1,
      "name": "King Fahad Hospital",
      "description": "A leading government hospital...",
      "address": "123 Main Street, Riyadh",
      "slug": "king-fahad-hospital",
      "hospital_type": "government",
      "phone": "+966123456789",
      "email": "info@kfh.gov",
      "website": "https://kfh.gov",
      "license_number": "MOH-12345",
      "accepted_insurance": ["Bupa", "Medgulf", "Tawuniya"],
      "rating": "4.50",
      "status": true,
      "governorate": {
        "id": 1,
        "name": "Riyadh"
      },
      "city": {
        "id": 5,
        "name": "Riyadh City"
      },
      "logo_url": "https://example.com/storage/hospitals/1/logo.png",
      "departments_count": 3,
      "available_beds_total": 12,
      "price_range": {
        "min": "500.00",
        "max": "2500.00"
      },
      "created_at": "2026-04-01T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

---

### GET /hospitals/{id}

Hospital detail with nested departments and location.

**Response 200**:

```json
{
  "status": true,
  "message": "Hospital detail",
  "data": {
    "id": 1,
    "name": "King Fahad Hospital",
    "description": "A leading government hospital...",
    "address": "123 Main Street, Riyadh",
    "slug": "king-fahad-hospital",
    "hospital_type": "government",
    "phone": "+966123456789",
    "email": "info@kfh.gov",
    "website": "https://kfh.gov",
    "license_number": "MOH-12345",
    "accepted_insurance": ["Bupa", "Medgulf", "Tawuniya"],
    "rating": "4.50",
    "status": true,
    "governorate": {
      "id": 1,
      "name": "Riyadh"
    },
    "city": {
      "id": 5,
      "name": "Riyadh City"
    },
    "logo_url": "https://example.com/storage/hospitals/1/logo.png",
    "gallery": [
      "https://example.com/storage/hospitals/1/gallery/1.jpg"
    ],
    "departments": [
      {
        "id": 1,
        "name": "Cardiac ICU",
        "description": "Specialized cardiac care unit",
        "specialty_type": "cardiac",
        "total_beds": 10,
        "available_beds": 3,
        "has_ventilator": true,
        "has_oxygen": true,
        "equipment_level": "full",
        "daily_price": "1500.00",
        "status": true
      }
    ],
    "created_at": "2026-04-01T10:00:00.000000Z"
  }
}
```

---

## ICU Departments

### GET /icu-departments

List ICU departments with filters.

**Query Parameters**:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| page | int | no | Page number |
| per_page | int | no | Items per page |
| hospital_id | int | no | Filter by hospital |
| specialty | string | no | Filter by specialty type |
| has_ventilator | boolean | no | Ventilator-equipped only |
| available | boolean | no | Only departments with available beds |

**Response 200**:

```json
{
  "status": true,
  "message": "ICU department list",
  "data": [
    {
      "id": 1,
      "hospital": {
        "id": 1,
        "name": "King Fahad Hospital"
      },
      "name": "Cardiac ICU",
      "description": "Specialized cardiac care unit",
      "specialty_type": "cardiac",
      "total_beds": 10,
      "available_beds": 3,
      "has_ventilator": true,
      "has_oxygen": true,
      "equipment_level": "full",
      "daily_price": "1500.00",
      "status": true
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 8
  }
}
```

---

## ICU Admission Requests

### GET /icu-admission-requests

List admission requests (role-filtered: patients see own, coordinators see hospital's, admins see all).

**Query Parameters**:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| page | int | no | Page number |
| per_page | int | no | Items per page |
| status | string | no | Filter by status |
| hospital_id | int | no | Filter by hospital (admin/coordinator) |
| urgency | string | no | Filter by urgency level |

**Response 200**:

```json
{
  "status": true,
  "message": "Admission request list",
  "data": [
    {
      "id": 1,
      "request_number": "ICU-2026-0001",
      "patient_name": "Ahmed Ali",
      "patient_age": 65,
      "patient_gender": "male",
      "hospital": {
        "id": 1,
        "name": "King Fahad Hospital"
      },
      "department": {
        "id": 1,
        "name": "Cardiac ICU",
        "specialty_type": "cardiac"
      },
      "medical_condition": "Acute myocardial infarction requiring monitoring",
      "diagnosis": "ST-elevation MI",
      "case_type": "cardiac",
      "urgency": "critical",
      "needs_ventilator": true,
      "needs_oxygen": true,
      "needs_ambulance": false,
      "contact_name": "Mohammed Ali",
      "contact_phone": "+966501234567",
      "relationship_to_patient": "Son",
      "payment_method": "insurance",
      "insurance_provider": "Bupa",
      "status": "pending",
      "status_badge": "<span class=\"badge bg-warning\">Pending</span>",
      "urgency_badge": "<span class=\"badge bg-danger\">Critical</span>",
      "admin_notes": null,
      "rejection_reason": null,
      "response_date": null,
      "created_at": "2026-04-05T14:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 5
  }
}
```

---

### POST /icu-admission-requests

Create a new admission request.

**Request Body**:

```json
{
  "hospital_id": 1,
  "icu_department_id": 1,
  "patient_name": "Ahmed Ali",
  "patient_age": 65,
  "patient_gender": "male",
  "national_id": "1234567890",
  "insurance_number": "INS-12345",
  "medical_condition_ar": "احتشاء عضلة القلب الحاد",
  "medical_condition_en": "Acute myocardial infarction",
  "diagnosis_ar": "احتشاء عضلة القلب مع ارتفاع ST",
  "diagnosis_en": "ST-elevation MI",
  "case_type": "cardiac",
  "urgency": "critical",
  "needs_ventilator": true,
  "needs_oxygen": true,
  "current_location": "Emergency Room, General Hospital",
  "needs_ambulance": false,
  "contact_name": "Mohammed Ali",
  "contact_phone": "+966501234567",
  "relationship_to_patient": "Son",
  "payment_method": "insurance",
  "insurance_provider": "Bupa"
}
```

**Response 201**:

```json
{
  "status": true,
  "message": "Admission request created successfully",
  "data": {
    "id": 6,
    "request_number": "ICU-2026-0006",
    "status": "pending",
    "created_at": "2026-04-05T15:00:00.000000Z"
  }
}
```

**Validation Errors 422**:

```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "hospital_id": ["The hospital id field is required."],
    "patient_name": ["The patient name field is required."]
  }
}
```

---

### GET /icu-admission-requests/{id}

Get admission request detail.

**Response 200**: Same structure as list item but with full details including `medical_reports` media URLs.

---

### POST /icu-admission-requests/{id}/cancel

Cancel an admission request (patient only, must be in pending or info_requested status).

**Request Body**:

```json
{
  "cancellation_reason": "Found a closer hospital"
}
```

**Response 200**:

```json
{
  "status": true,
  "message": "Admission request cancelled successfully",
  "data": {
    "id": 6,
    "status": "cancelled"
  }
}
```

**Response 403** (not owner or invalid status):

```json
{
  "status": false,
  "message": "You are not authorized to cancel this request"
}
```

---

## ICU Commissions (Admin Only)

### GET /icu-commissions

List commission templates (admin/demo_admin only).

**Response 200**:

```json
{
  "status": true,
  "message": "Commission template list",
  "data": [
    {
      "id": 1,
      "title": "Standard 10%",
      "commission_type": "percentage",
      "commission_value": "10.00",
      "status": true,
      "hospitals_count": 5,
      "total_earned": "15000.00"
    }
  ]
}
```

---

## ICU Earnings (Admin / Hospital Coordinator)

### GET /icu-earnings

Earnings summary aggregated by hospital. Hospital coordinators see only their hospital.

**Query Parameters**:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| hospital_id | int | no | Filter by hospital (admin only) |
| start_date | date | no | Filter from date |
| end_date | date | no | Filter to date |

**Response 200**:

```json
{
  "status": true,
  "message": "Earnings summary",
  "data": {
    "totals": {
      "total_platform_commission": "25000.00",
      "total_unpaid_receivable": "18000.00",
      "total_paid_receivable": "7000.00"
    },
    "hospitals": [
      {
        "id": 1,
        "name": "King Fahad Hospital",
        "total_orders": 15,
        "total_platform_commission": "12000.00",
        "total_unpaid_receivable": "8000.00",
        "total_paid_receivable": "4000.00"
      }
    ]
  }
}
```

---

## Common Error Responses

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
  "status": false,
  "message": "You do not have permission to perform this action"
}
```

### 404 Not Found

```json
{
  "status": false,
  "message": "Resource not found"
}
```

### 422 Validation Error

```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```
