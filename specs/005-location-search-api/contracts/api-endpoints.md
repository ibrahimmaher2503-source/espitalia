# API Contract: Location-Based Healthcare Search

**Branch**: `005-location-search-api` | **Date**: 2026-03-31 | **Updated**: 2026-04-05
**Base URL**: `/api`
**Auth**: None (public endpoints)
**Locale**: Determined by `Accept-Language` header (`ar` or `en`, default `en`)

---

## Localization

All endpoints respect the `Accept-Language` request header:

| Header | Locale | Effect |
|--------|--------|--------|
| `Accept-Language: ar` | Arabic | Governorate/city names returned in Arabic |
| `Accept-Language: en` | English | Governorate/city names returned in English |
| (missing or other) | English | Defaults to English |

The locale is applied via the `localization` middleware registered in the API middleware group. It uses a word-boundary regex (`\bar\b`) to detect Arabic, preventing false matches on strings like `bar` or `charset`.

**Flutter client usage**: Set the header on every API request:
```dart
headers: {'Accept-Language': currentLocale} // 'ar' or 'en'
```

---

## Unified Response Envelope

### Success Response (lists)

```json
{
  "status": true,
  "message": "Governorates list",
  "data": [ ... ]
}
```

### Success Response (paginated search)

```json
{
  "status": true,
  "message": "",
  "data": {
    "items": [ ... ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 72
    }
  }
}
```

### Validation Error Response (422)

Returned when required parameters are missing or invalid values are provided. Clients should send `Accept: application/json` header to ensure JSON error responses.

```json
{
  "message": "The governorate id field is required.",
  "errors": {
    "governorate_id": ["The governorate id field is required."]
  }
}
```

---

## Endpoints

### 1. GET /api/governorates

**Description**: List all 27 Egyptian governorates. Names are locale-aware.

**Parameters**: None

**Response 200** (Accept-Language: en):
```json
{
  "status": true,
  "message": "Governorates list",
  "data": [
    { "id": 1, "name": "Cairo" },
    { "id": 2, "name": "Alexandria" },
    { "id": 3, "name": "Giza" },
    { "id": 4, "name": "Qalyubia" },
    { "id": 5, "name": "Sharqia" },
    { "id": 6, "name": "Dakahlia" },
    { "id": 7, "name": "Beheira" },
    { "id": 8, "name": "Monufia" },
    { "id": 9, "name": "Gharbia" },
    { "id": 10, "name": "Kafr El Sheikh" },
    { "id": 11, "name": "Fayoum" },
    { "id": 12, "name": "Beni Suef" },
    { "id": 13, "name": "Minya" },
    { "id": 14, "name": "Asyut" },
    { "id": 15, "name": "Sohag" },
    { "id": 16, "name": "Qena" },
    { "id": 17, "name": "Luxor" },
    { "id": 18, "name": "Aswan" },
    { "id": 19, "name": "Red Sea" },
    { "id": 20, "name": "New Valley" },
    { "id": 21, "name": "Matrouh" },
    { "id": 22, "name": "North Sinai" },
    { "id": 23, "name": "South Sinai" },
    { "id": 24, "name": "Port Said" },
    { "id": 25, "name": "Ismailia" },
    { "id": 26, "name": "Suez" },
    { "id": 27, "name": "Damietta" }
  ]
}
```

**Response 200** (Accept-Language: ar):
```json
{
  "status": true,
  "message": "قائمة المحافظات",
  "data": [
    { "id": 1, "name": "القاهرة" },
    { "id": 2, "name": "الإسكندرية" },
    { "id": 3, "name": "الجيزة" }
  ]
}
```

---

### 2. GET /api/cities

**Description**: List cities for a specific governorate. Names are locale-aware.

**Parameters**:

| Param | Type | Required | Validation |
|-------|------|----------|------------|
| governorate_id | integer | **yes** | exists:governorates,id |

**Response 200** (Accept-Language: en, governorate_id=1):
```json
{
  "status": true,
  "message": "Cities list",
  "data": [
    { "id": 1, "name": "Nasr City" },
    { "id": 2, "name": "Maadi" },
    { "id": 3, "name": "Heliopolis" },
    { "id": 4, "name": "Fifth Settlement" },
    { "id": 5, "name": "Mokattam" },
    { "id": 6, "name": "Shubra" },
    { "id": 7, "name": "Ain Shams" }
  ]
}
```

**Response 200** (Accept-Language: ar, governorate_id=1):
```json
{
  "status": true,
  "message": "قائمة المدن",
  "data": [
    { "id": 1, "name": "مدينة نصر" },
    { "id": 2, "name": "المعادي" },
    { "id": 3, "name": "مصر الجديدة" },
    { "id": 4, "name": "التجمع الخامس" },
    { "id": 5, "name": "المقطم" },
    { "id": 6, "name": "شبرا" },
    { "id": 7, "name": "عين شمس" }
  ]
}
```

**Response 422** (missing governorate_id):
```json
{
  "message": "The governorate id field is required.",
  "errors": {
    "governorate_id": ["The governorate id field is required."]
  }
}
```

**Note**: Clients must send `Accept: application/json` header to receive JSON error responses. Without it, Laravel may redirect to the home page on validation failure.

---

### 3. GET /api/doctors/search

**Description**: Search doctors with optional filters. Returns active doctors only, sorted by newest first. Governorate and city names are locale-aware.

**Parameters**:

| Param | Type | Required | Validation |
|-------|------|----------|------------|
| governorate_id | integer | no | exists:governorates,id |
| city_id | integer | no | exists:governorate_cities,id |
| specialty_id | integer | no | integer |
| name | string | no | max:100 (partial match on first/last name) |
| gender | string | no | in:male,female |
| min_price | numeric | no | min:0 (combined with max_price on same service) |
| max_price | numeric | no | min:0 (combined with min_price on same service) |
| page | integer | no | default:1 |

**Response 200** (Accept-Language: ar, governorate_id=1):
```json
{
  "status": true,
  "message": "",
  "data": {
    "items": [
      {
        "id": 1,
        "name": "أحمد الخالدي",
        "gender": "male",
        "experience": "15 سنة",
        "specialty": "طب القلب",
        "consultation_price": 300,
        "profile_image": "https://example.com/storage/147/photo.png",
        "governorate": { "id": 1, "name": "القاهرة" },
        "city": { "id": 1, "name": "مدينة نصر" }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 3
    }
  }
}
```

**Notes**:
- `specialty` and `consultation_price` come from the doctor's service mapping. They may be `null` if no services are assigned.
- `name` is built from the user's `first_name` + `last_name`.
- Price filter combines `min_price` and `max_price` on the same service row (not independently).

---

### 4. GET /api/clinics/search

**Description**: Search clinics with optional filters. Returns active clinics only, sorted by newest first.

**Parameters**:

| Param | Type | Required | Validation |
|-------|------|----------|------------|
| governorate_id | integer | no | exists:governorates,id |
| city_id | integer | no | exists:governorate_cities,id |
| specialty_id | integer | no | integer |
| name | string | no | max:100 (partial match on clinic name) |
| page | integer | no | default:1 |

**Response 200** (Accept-Language: ar, governorate_id=1):
```json
{
  "status": true,
  "message": "",
  "data": {
    "items": [
      {
        "id": 2,
        "name": "مركز الوئام الطبي",
        "profile_image": "https://example.com/storage/26/photo.png",
        "governorate": { "id": 1, "name": "القاهرة" },
        "city": { "id": 1, "name": "مدينة نصر" }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 3
    }
  }
}
```

---

### 5. GET /api/nurses/search

**Description**: Search nurses with optional filters. Returns active nurses only, sorted by newest first. Text fields (`specialization`) are locale-aware via model accessors. The `specialty` filter searches both Arabic and English specialization columns.

**Parameters**:

| Param | Type | Required | Validation |
|-------|------|----------|------------|
| governorate_id | integer | no | exists:governorates,id |
| city_id | integer | no | exists:governorate_cities,id |
| specialty | string | no | max:100 (partial match on `specialization_ar` OR `specialization_en`) |
| gender | string | no | in:male,female |
| availability | string | no | in:available,busy,off_duty |
| page | integer | no | default:1 |

**Response 200** (Accept-Language: ar, governorate_id=1):
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

**Bilingual Note** (updated 2026-04-05): `specialization` is now locale-aware — returns Arabic when `Accept-Language: ar`, English otherwise. The `specialty` search parameter queries both `specialization_ar` and `specialization_en` columns with OR matching, so searches work regardless of which language the user types in.

---

### 6. GET /api/home-healthcare/search

**Description**: Search home healthcare providers by location and service type. Returns active providers only, sorted by newest first.

**Parameters**:

| Param | Type | Required | Validation |
|-------|------|----------|------------|
| governorate_id | integer | no | exists:governorates,id |
| city_id | integer | no | exists:governorate_cities,id |
| service_type | string | no | max:100 (exact match) |
| page | integer | no | default:1 |

**Response 200**:
```json
{
  "status": true,
  "message": "",
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Home Care Services",
        "service_type": "physiotherapy",
        "governorate": { "id": 1, "name": "Cairo" },
        "city": { "id": 1, "name": "Nasr City" }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

**Notes**: `name` is locale-aware (uses `name_ar`/`name_en` accessor).

---

### 7. GET /api/labs/search

**Description**: Search medical labs by location and test name. Returns active labs only, sorted by newest first.

**Parameters**:

| Param | Type | Required | Validation |
|-------|------|----------|------------|
| governorate_id | integer | no | exists:governorates,id |
| city_id | integer | no | exists:governorate_cities,id |
| test_name | string | no | max:100 (partial match on lab name) |
| page | integer | no | default:1 |

**Response 200**:
```json
{
  "status": true,
  "message": "",
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Al Borg Lab",
        "governorate": { "id": 1, "name": "Cairo" },
        "city": { "id": 1, "name": "Nasr City" }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

**Notes**: `name` is locale-aware. `test_name` filter searches against the lab's name fields.

---

### 8. GET /api/radiology/search

**Description**: Search radiology/imaging centers by location and scan type. Returns active centers only, sorted by newest first.

**Parameters**:

| Param | Type | Required | Validation |
|-------|------|----------|------------|
| governorate_id | integer | no | exists:governorates,id |
| city_id | integer | no | exists:governorate_cities,id |
| scan_type | string | no | max:100 (exact match) |
| page | integer | no | default:1 |

**Response 200**:
```json
{
  "status": true,
  "message": "",
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Diagnostic Radiology Center",
        "scan_type": "MRI",
        "governorate": { "id": 2, "name": "Alexandria" },
        "city": { "id": 8, "name": "Smouha" }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

**Notes**: `name` is locale-aware. `scan_type` is an exact string match (e.g., `MRI`, `CT`, `X-ray`).

---

## Flutter Client Integration Guide

### Required Headers

```dart
Map<String, String> get headers => {
  'Accept': 'application/json',          // Required for proper error responses
  'Accept-Language': currentLocale,       // 'ar' or 'en'
};
```

### Cascading Location Selection

```
1. GET /api/governorates              → populate governorate dropdown
2. GET /api/cities?governorate_id=X   → populate city dropdown (after governorate selected)
3. GET /api/doctors/search?governorate_id=X&city_id=Y  → search with location
```

### Pagination

All search endpoints return 15 items per page. Use the `page` query parameter:
```
GET /api/doctors/search?governorate_id=1&page=2
```

Check `pagination.current_page < pagination.last_page` to determine if more pages exist.

### Empty Results

When no results match the filters, the response returns an empty `items` array with valid pagination:
```json
{
  "status": true,
  "message": "",
  "data": {
    "items": [],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 0
    }
  }
}
```
