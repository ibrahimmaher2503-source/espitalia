# API Contract: Bilingual Nurse Endpoints

**Branch**: `012-nurse-admin-bilingual` | **Date**: 2026-04-05

All endpoints respect `Accept-Language` header (`ar` → Arabic, `en` or omitted → English).

---

## 1. GET /api/nurses/search (Updated)

**Auth**: None (public)

**Bilingual Behavior Change**: The `specialization` field now returns the locale-appropriate value via model accessor. The `specialty` search parameter now queries both `specialization_ar` and `specialization_en` columns (OR match). Governorate and city names were already locale-aware.

**Response** — NurseSearchResource fields affected:

```json
{
  "status": true,
  "message": "",
  "data": {
    "items": [
      {
        "id": 1,
        "name": "فاطمة الزهراني",
        "specialization": "تمريض العناية المركزة",
        "gender": "female",
        "availability_status": "available",
        "hourly_rate": 200,
        "profile_image": "https://example.com/img/avatar/avatar.webp",
        "governorate": { "id": 1, "name": "القاهرة" },
        "city": { "id": 1, "name": "مدينة نصر" }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 2
    }
  }
}
```

**Search Behavior**: The `specialty` filter now matches against both `specialization_ar` and `specialization_en` using OR conditions at the database level. A search for "تمريض" matches Arabic specialization; a search for "nursing" matches English.

---

## 2. GET /api/v1/nurses (Updated)

**Auth**: Bearer token (auth:sanctum)

**Parameters**:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| availability_status | string | no | Filter: `available`, `busy`, `off_duty` |
| service_area | string | no | Partial match on service area (uses accessor — single-language search) |
| specialization | string | no | Partial match on specialization (uses accessor — single-language search) |
| search | string | no | Full-text search across specialization, service_area, and user name |
| per_page | integer | no | Items per page (default: 15) |

**Response** — NurseResource fields affected by bilingual:

```json
{
  "data": [
    {
      "id": 1,
      "nurse_id": 10,
      "name": "فاطمة الزهراني",
      "first_name": "فاطمة",
      "last_name": "الزهراني",
      "email": "fatima@example.com",
      "mobile": "+201234567890",
      "specialization": "تمريض العناية المركزة",
      "experience": "5 سنوات خبرة في المستشفيات",
      "about": "ممرضة متخصصة في العناية المركزة...",
      "hourly_rate": 200,
      "availability_status": "available",
      "service_area": "القاهرة الكبرى",
      "governorate": { "id": 1, "name": "القاهرة" },
      "city": { "id": 1, "name": "مدينة نصر" },
      "profile_image": null,
      "status": 1,
      "created_at": "2026-04-05T10:00:00.000000Z",
      "updated_at": "2026-04-05T10:00:00.000000Z"
    }
  ],
  "links": { "...": "pagination links" },
  "meta": { "...": "pagination meta" }
}
```

**Behavior Change**: `specialization`, `experience`, `about`, and `service_area` now return locale-aware values via model accessors. `governorate` and `city` are new nested objects with locale-aware names. Response field names are unchanged.

---

## 3. GET /api/v1/nurses/{id} (Updated)

**Auth**: Bearer token (auth:sanctum)

**Response** — same NurseResource as endpoint #2, wrapped in envelope:

```json
{
  "status": true,
  "data": {
    "id": 1,
    "nurse_id": 10,
    "name": "Fatima Al-Zahrani",
    "first_name": "Fatima",
    "last_name": "Al-Zahrani",
    "email": "fatima@example.com",
    "mobile": "+201234567890",
    "specialization": "ICU Nursing",
    "experience": "5 years hospital experience",
    "about": "Specialized ICU nurse...",
    "hourly_rate": 200,
    "availability_status": "available",
    "service_area": "Greater Cairo",
    "governorate": { "id": 1, "name": "Cairo" },
    "city": { "id": 1, "name": "Nasr City" },
    "profile_image": null,
    "status": 1,
    "created_at": "2026-04-05T10:00:00.000000Z",
    "updated_at": "2026-04-05T10:00:00.000000Z"
  }
}
```

**Behavior Change**: Same bilingual fields as endpoint #2. All text fields locale-aware.

---

## 4. GET /api/v1/nurse-requests (Updated)

**Auth**: Bearer token (auth:sanctum)

**Role-based access**: Nurse sees assigned requests, patient sees own requests, admin sees all.

**Parameters**:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| status | string | no | Filter by status: `pending`, `approved`, `in_progress`, `completed`, `cancelled` |
| search | string | no | Full-text search across service_description, city, patient name |
| per_page | integer | no | Items per page (default: 15) |

**Response** — NurseRequestResource fields affected:

```json
{
  "data": [
    {
      "id": 1,
      "patient": {
        "id": 5,
        "name": "محمد أحمد",
        "email": "patient@example.com",
        "mobile": "+201234567890"
      },
      "nurse": {
        "id": 10,
        "name": "فاطمة الزهراني",
        "specialization": "تمريض العناية المركزة"
      },
      "service_description": "رعاية ما بعد الجراحة في المنزل",
      "request_date": "2026-04-05",
      "preferred_date": "2026-04-10",
      "preferred_time": "09:00",
      "duration_hours": 4,
      "address": {
        "address_line_1": "123 شارع التحرير",
        "address_line_2": null,
        "city": "القاهرة",
        "state": null,
        "country": null,
        "postal_code": null,
        "latitude": null,
        "longitude": null,
        "full_address": null
      },
      "contact_number": "+201234567890",
      "status": "pending",
      "payment_status": false,
      "total_amount": 800,
      "patient_notes": null,
      "admin_notes": null,
      "cancelled_by": null,
      "cancellation_reason": null,
      "created_at": "2026-04-05T10:00:00.000000Z",
      "updated_at": "2026-04-05T10:00:00.000000Z"
    }
  ],
  "links": { "...": "pagination links" },
  "meta": { "...": "pagination meta" }
}
```

**Behavior Change**: `service_description` now returns the locale-appropriate value (Arabic or English) via model accessor. The nested `nurse.specialization` is also locale-aware. Response field names are unchanged.

---

## 5. GET /api/v1/nurse-requests/{id} (Updated)

**Auth**: Bearer token (auth:sanctum). Patient can only see own requests. Nurse can only see assigned requests.

**Response** — same NurseRequestResource as endpoint #4, wrapped in envelope:

```json
{
  "status": true,
  "data": {
    "...": "same fields as endpoint #4"
  }
}
```

---

## 6. POST /api/v1/nurse-requests (Updated)

**Auth**: Bearer token (auth:sanctum)

**Request Body**:

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `service_description` | string | yes | Service description (stored in current locale column) |
| `preferred_date` | date | yes | Must be today or future (Y-m-d) |
| `preferred_time` | time | no | Format: HH:mm |
| `duration_hours` | numeric | yes | Min: 1, Max: 24 |
| `address_line_1` | string | yes | Max: 255 |
| `city` | string | yes | Max: 100 |
| `contact_number` | string | yes | Max: 20 |
| `nurse_id` | integer | no | Preferred nurse (exists:users,id) |

**Note**: The public API `store()` still accepts `service_description` as a single field. The admin form request (`NurseRequestFormRequest`) accepts `service_description_ar` and `service_description_en` separately.

**Response 200**:

```json
{
  "status": true,
  "message": "Nurse request saved successfully",
  "data": {
    "...": "NurseRequestResource (same as endpoint #4)"
  }
}
```

---

## 7. PUT /api/v1/nurse-requests/{id} (Updated)

**Auth**: Bearer token (auth:sanctum). Patient can only update own pending requests.

**Request Body** (all optional — only updates provided fields):

| Field | Type | Description |
|-------|------|-------------|
| `service_description` | string | Updated description |
| `preferred_date` | date | Updated preferred date |
| `preferred_time` | time | Updated preferred time |
| `duration_hours` | numeric | Updated duration |
| `address_line_1` | string | Updated address |
| `address_line_2` | string | Updated address line 2 |
| `city` | string | Updated city |
| `state` | string | Updated state |
| `country` | string | Updated country |
| `postal_code` | string | Updated postal code |
| `contact_number` | string | Updated contact number |
| `patient_notes` | string | Updated patient notes |

**Response 200**:

```json
{
  "status": true,
  "message": "Nurse request updated successfully",
  "data": {
    "...": "NurseRequestResource (same as endpoint #4)"
  }
}
```

---

## 8. POST /api/v1/nurse-requests/{id}/cancel (Unchanged)

**Auth**: Bearer token (auth:sanctum)

**Request Body**:

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `cancellation_reason` | string | yes | Max: 500 |

**Response 200**:

```json
{
  "status": true,
  "message": "Nurse request cancelled successfully"
}
```

**Response 400** (already completed/cancelled):

```json
{
  "status": false,
  "message": "Cannot cancel a completed or already cancelled request"
}
```

---

## Admin Form Requests (Internal — Web Routes)

### Admin Nurse Store/Update

**Route**: `POST/PUT /app/nurses`

**Request Body** (form fields):

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `first_name` | string | yes | User first name |
| `last_name` | string | yes | User last name |
| `email` | email | yes | Unique per user |
| `mobile` | string | no | Max: 20 |
| `password` | string | Create: yes, Update: no | Min: 6 |
| `specialization_ar` | string | no | Arabic specialization (max: 255) |
| `specialization_en` | string | no | English specialization (max: 255) |
| `experience_ar` | string | no | Arabic experience (max: 255) |
| `experience_en` | string | no | English experience (max: 255) |
| `about_ar` | string | no | Arabic bio (max: 2000) |
| `about_en` | string | no | English bio (max: 2000) |
| `service_area_ar` | string | no | Arabic service area (max: 255) |
| `service_area_en` | string | no | English service area (max: 255) |
| `hourly_rate` | numeric | yes | Min: 0 |
| `availability_status` | string | no | `available`, `busy`, `off_duty` |
| `governorate_id` | integer | no | exists:governorates,id |
| `city_id` | integer | no | exists:governorate_cities,id |
| `profile_image` | file | no | jpeg,png,jpg,gif (max: 2MB) |

### Admin Nurse Request Store/Update

**Route**: `POST/PUT /app/nurse-requests`

**Request Body** (form fields):

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `patient_id` | integer | yes | exists:users,id |
| `service_description_ar` | string | no | Arabic description (max: 2000) |
| `service_description_en` | string | no | English description (max: 2000) |
| `preferred_date` | date | yes | After or equal to today |
| `preferred_time` | time | no | Format: HH:mm |
| `duration_hours` | numeric | yes | Min: 1, Max: 24 |
| `address_line_1` | string | yes | Max: 255 |
| `address_line_2` | string | no | Max: 255 |
| `city` | string | yes | Max: 100 |
| `state` | string | no | Max: 100 |
| `country` | string | no | Max: 100 |
| `postal_code` | string | no | Max: 20 |
| `contact_number` | string | yes | Max: 20 |
| `nurse_id` | integer | no | exists:users,id |
| `patient_notes` | string | no | Max: 1000 |
| `admin_notes` | string | no | Max: 1000 |

---

## Backward Compatibility

- **No response field names changed** — all existing field names remain (`specialization`, `experience`, `about`, `service_area`, `service_description`)
- **No required request fields removed** — bilingual `_ar`/`_en` fields are additions for admin forms
- **Default behavior unchanged** — without `Accept-Language` header, English values are returned
- **Fallback logic** — if the requested locale's value is empty, the other language's value is returned instead of an empty string
- **New response fields** — `governorate` and `city` nested objects added to NurseResource (nullable — `null` if no location assigned)
- **Public API store** — still accepts `service_description` as a single field; admin form accepts `_ar`/`_en` split
