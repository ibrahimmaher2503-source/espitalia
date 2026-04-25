# API Contract: Bilingual Doctor Endpoints

**Branch**: `007-doctor-bilingual-details` | **Date**: 2026-04-02

All endpoints respect `Accept-Language` header (`ar` → Arabic, `en` or omitted → English).

---

## 1. GET /api/v1/get-doctor-list (Updated)

**Auth**: Bearer token (auth:sanctum)

**Response** — DoctorResource fields affected by bilingual:

```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "doctor_id": 10,
      "first_name": "Ahmed",
      "last_name": "Al-Khalidi",
      "full_name": "Ahmed Al-Khalidi",
      "expert": "Cardiology",
      "about_self": "Experienced cardiologist...",
      "description": "Specialist in cardiac surgery",
      "experience": "15 years",
      "...": "other unchanged fields"
    }
  ]
}
```

**Behavior Change**: `first_name`, `last_name`, `full_name`, `expert`, `about_self`, `description`, `experience` now return locale-aware values. Response field names are unchanged.

---

## 2. GET /api/doctors/search (Updated)

**Auth**: None (public)

**Response** — DoctorSearchResource fields affected:

```json
{
  "status": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Ahmed Al-Khalidi",
        "experience": "15 years",
        "...": "other unchanged fields"
      }
    ],
    "pagination": { "..." : "unchanged" }
  }
}
```

**Behavior Change**: `name` and `experience` now return locale-aware values. The `name` search parameter queries both AR and EN columns.

---

## 3. GET /api/v1/independent-doctors (Updated)

**Auth**: None (public)

Same DoctorResource changes as endpoint #1. All text fields locale-aware.

---

## 4. GET /api/v1/call-doctors (Updated)

**Auth**: None (public)

Same DoctorResource changes as endpoint #1. All text fields locale-aware.

---

## 5. Admin Doctor Edit Response (Updated - Internal)

**Route**: Admin backend edit doctor endpoint

**Response** — Returns bilingual field pairs for form population:

```json
{
  "first_name": { "ar": "أحمد", "en": "Ahmed" },
  "last_name": { "ar": "الخالدي", "en": "Al-Khalidi" },
  "experience": { "ar": "15 سنة", "en": "15 years" },
  "description": { "ar": "متخصص في جراحة القلب", "en": "Specialist in cardiac surgery" },
  "expert": { "ar": "أمراض القلب", "en": "Cardiology" },
  "about_self": { "ar": "طبيب قلب ذو خبرة...", "en": "Experienced cardiologist..." }
}
```

---

## 6. Admin Doctor Store/Update Request (Updated - Internal)

**Route**: Admin backend store/update doctor

**Request Body** (form fields):

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `first_name_en` | string | Required if `first_name_ar` empty | English first name |
| `first_name_ar` | string | Required if `first_name_en` empty | Arabic first name |
| `last_name_en` | string | Required if `last_name_ar` empty | English last name |
| `last_name_ar` | string | Required if `last_name_en` empty | Arabic last name |
| `experience_en` | string | No | English experience |
| `experience_ar` | string | No | Arabic experience |
| `description_en` | string | No | English description |
| `description_ar` | string | No | Arabic description |
| `expert_en` | string | No | English expertise |
| `expert_ar` | string | No | Arabic expertise |
| `about_self_en` | string | No | English bio |
| `about_self_ar` | string | No | Arabic bio |

---

## Backward Compatibility

- **No response field names changed** — all existing field names remain
- **No required request fields removed** — new fields are additions
- **Default behavior unchanged** — without `Accept-Language` header, English is returned
- **Original DB columns kept** — `first_name`, `last_name`, `experience`, `description`, `expert`, `about_self` remain populated
