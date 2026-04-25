# API Endpoints Contract: Specializations

**Prefix**: `api/v1` | **Middleware**: `auth:sanctum` | **Name prefix**: `api.`

## Endpoints

### GET /api/v1/specializations

List specializations with pagination, search, and counts.

**Query Parameters**:

| Param | Type | Description |
|-------|------|-------------|
| `search` | string | Filter by name_ar or name_en (LIKE) |
| `status` | 0\|1 | Filter by status |
| `parent_id` | integer | Filter by parent_id (null for top-level) |
| `page` | integer | Pagination page |
| `per_page` | integer | Items per page (default 15) |

**Response** (200):

```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "Cardiology",
      "name_ar": "أمراض القلب",
      "name_en": "Cardiology",
      "description": "...",
      "description_ar": "...",
      "description_en": "...",
      "slug": "cardiology",
      "parent_id": null,
      "parent": null,
      "icon": "ph ph-heart-beat",
      "status": 1,
      "doctors_count": 12,
      "nurses_count": 5,
      "children": [
        {
          "id": 5,
          "name": "Interventional Cardiology",
          "slug": "interventional-cardiology",
          "doctors_count": 3,
          "nurses_count": 1
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 4,
    "per_page": 15,
    "total": 50
  }
}
```

---

### GET /api/v1/specializations/{id}

Get a single specialization with full details.

**Response** (200):

```json
{
  "status": true,
  "data": {
    "id": 1,
    "name": "Cardiology",
    "name_ar": "أمراض القلب",
    "name_en": "Cardiology",
    "description": "...",
    "description_ar": "...",
    "description_en": "...",
    "slug": "cardiology",
    "parent_id": null,
    "parent": null,
    "icon": "ph ph-heart-beat",
    "status": 1,
    "doctors_count": 12,
    "nurses_count": 5,
    "children": []
  }
}
```

**Response** (404):

```json
{
  "status": false,
  "message": "Specialization not found"
}
```

---

### GET /api/v1/specializations/{id}/doctors

List doctors linked to a specialization.

**Query Parameters**:

| Param | Type | Description |
|-------|------|-------------|
| `page` | integer | Pagination page |
| `per_page` | integer | Items per page (default 15) |

**Response** (200):

```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "doctor_id": 5,
      "first_name": "Ahmed",
      "last_name": "Hassan",
      "full_name": "Ahmed Hassan",
      "email": "ahmed@example.com",
      "expert": "Heart Surgeon",
      "specializations": [
        {"id": 1, "name": "Cardiology", "slug": "cardiology"},
        {"id": 5, "name": "Interventional Cardiology", "slug": "interventional-cardiology"}
      ],
      "profile_image": "..."
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 12
  }
}
```

---

### GET /api/v1/specializations/{id}/nurses

List nurses linked to a specialization.

**Query Parameters**:

| Param | Type | Description |
|-------|------|-------------|
| `page` | integer | Pagination page |
| `per_page` | integer | Items per page (default 15) |

**Response** (200):

```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "nurse_id": 10,
      "name": "Fatima Ali",
      "specialization": "ICU Nursing",
      "specializations": [
        {"id": 3, "name": "ICU Nursing", "slug": "icu-nursing"}
      ],
      "experience": "5 years",
      "hourly_rate": 150.00,
      "availability_status": "available",
      "profile_image": "..."
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

Note: The `specialization` string field is the legacy value from `specialization_ar`/`specialization_en` (backwards compatibility per FR-017).

---

## Filter Extensions to Existing Endpoints

### GET /api/v1/doctor-list (existing)

**New Query Parameter**:

| Param | Type | Description |
|-------|------|-------------|
| `specialization_ids[]` | array of int | Filter doctors by specialization (OR logic) |

Applies `whereHas('specializations', fn($q) => $q->whereIn('specializations.id', $ids))`.

### Nurse listing endpoint (existing frontend AJAX)

**New Query Parameter**:

| Param | Type | Description |
|-------|------|-------------|
| `specialization_ids[]` | array of int | Filter nurses by specialization (OR logic) |

Applies `whereHas('specializations', fn($q) => $q->whereIn('specializations.id', $ids))`.

Falls back to LIKE search on `specialization_ar`/`specialization_en` for nurses without pivot records (backwards compatibility during transition, FR-009).
