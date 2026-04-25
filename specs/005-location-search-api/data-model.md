# Data Model: Location-Based Healthcare Search API

**Branch**: `005-location-search-api` | **Date**: 2026-03-31

## New Entities

### Governorate (Module: Location)

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | unsignedBigInteger | PK, auto-increment | |
| name_ar | string(100) | required | Arabic name |
| name_en | string(100) | required | English name |
| created_at | timestamp | auto | |
| updated_at | timestamp | auto | |

**Table name**: `governorates`
**Model**: `Modules\Location\Models\Governorate`
**Extends**: `App\Models\BaseModel`

**Relationships**:
- `cities()` → hasMany GovernorateCity
- `doctors()` → hasMany Doctor (via `doctors.governorate_id`)
- `clinics()` → hasMany Clinics (via `clinic.governorate_id`)
- `nurses()` → hasMany Nurse (via `nurses.governorate_id`)
- `homeHealthcares()` → hasMany HomeHealthcare
- `labs()` → hasMany Lab
- `radiologyCenters()` → hasMany RadiologyCenter

**Accessor**:
- `getNameAttribute()` → returns `name_ar` when `app()->getLocale() === 'ar'`, else `name_en`

---

### GovernorateCity (Module: Location)

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | unsignedBigInteger | PK, auto-increment | |
| governorate_id | unsignedBigInteger | FK → governorates.id, cascadeDelete | |
| name_ar | string(100) | required | Arabic name |
| name_en | string(100) | required | English name |
| created_at | timestamp | auto | |
| updated_at | timestamp | auto | |

**Table name**: `governorate_cities` (avoids conflict with World module's `cities` table)
**Model**: `Modules\Location\Models\GovernorateCity`
**Extends**: `App\Models\BaseModel`

**Relationships**:
- `governorate()` → belongsTo Governorate

**Accessor**:
- `getNameAttribute()` → returns `name_ar` when `app()->getLocale() === 'ar'`, else `name_en`

**Index**: `governorate_id` (for filtered queries)

---

### HomeHealthcare (Module: HomeHealthcare)

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | unsignedBigInteger | PK, auto-increment | |
| name_ar | string(255) | required | Arabic name |
| name_en | string(255) | required | English name |
| service_type | string(100) | nullable | e.g., physiotherapy, elderly_care |
| governorate_id | unsignedBigInteger | nullable, FK → governorates.id | |
| city_id | unsignedBigInteger | nullable, FK → governorate_cities.id | |
| status | tinyInteger | default: 1 | 1=active, 0=inactive |
| created_by | integer | nullable | BaseModel audit |
| updated_by | integer | nullable | BaseModel audit |
| deleted_by | integer | nullable | BaseModel audit |
| created_at | timestamp | auto | |
| updated_at | timestamp | auto | |
| deleted_at | timestamp | nullable | SoftDeletes |

**Table name**: `home_healthcares`
**Model**: `Modules\HomeHealthcare\Models\HomeHealthcare`
**Extends**: `App\Models\BaseModel`

**Relationships**:
- `governorate()` → belongsTo Governorate
- `city()` → belongsTo GovernorateCity

---

### Lab (Module: RadiologyLab)

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | unsignedBigInteger | PK, auto-increment | |
| name_ar | string(255) | required | Arabic name |
| name_en | string(255) | required | English name |
| governorate_id | unsignedBigInteger | nullable, FK → governorates.id | |
| city_id | unsignedBigInteger | nullable, FK → governorate_cities.id | |
| status | tinyInteger | default: 1 | 1=active, 0=inactive |
| created_by | integer | nullable | BaseModel audit |
| updated_by | integer | nullable | BaseModel audit |
| deleted_by | integer | nullable | BaseModel audit |
| created_at | timestamp | auto | |
| updated_at | timestamp | auto | |
| deleted_at | timestamp | nullable | SoftDeletes |

**Table name**: `labs`
**Model**: `Modules\RadiologyLab\Models\Lab`
**Extends**: `App\Models\BaseModel`

**Relationships**:
- `governorate()` → belongsTo Governorate
- `city()` → belongsTo GovernorateCity
- `labTests()` → hasMany LabTest (via future FK or pivot)

---

### RadiologyCenter (Module: RadiologyLab)

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | unsignedBigInteger | PK, auto-increment | |
| name_ar | string(255) | required | Arabic name |
| name_en | string(255) | required | English name |
| scan_type | string(100) | nullable | e.g., MRI, CT, X-ray |
| governorate_id | unsignedBigInteger | nullable, FK → governorates.id | |
| city_id | unsignedBigInteger | nullable, FK → governorate_cities.id | |
| status | tinyInteger | default: 1 | 1=active, 0=inactive |
| created_by | integer | nullable | BaseModel audit |
| updated_by | integer | nullable | BaseModel audit |
| deleted_by | integer | nullable | BaseModel audit |
| created_at | timestamp | auto | |
| updated_at | timestamp | auto | |
| deleted_at | timestamp | nullable | SoftDeletes |

**Table name**: `radiology_centers`
**Model**: `Modules\RadiologyLab\Models\RadiologyCenter`
**Extends**: `App\Models\BaseModel`

**Relationships**:
- `governorate()` → belongsTo Governorate
- `city()` → belongsTo GovernorateCity

---

## Modified Entities (new columns only)

### Doctor (Modules/Clinic) — add columns

| Field | Type | Constraints |
|-------|------|-------------|
| governorate_id | unsignedBigInteger | nullable, FK → governorates.id |
| city_id | unsignedBigInteger | nullable, FK → governorate_cities.id |

**New relationships**: `governorate()` → belongsTo, `city()` → belongsTo GovernorateCity
**Update $fillable**: add `governorate_id`, `city_id`

### Clinics (Modules/Clinic) — add columns

| Field | Type | Constraints |
|-------|------|-------------|
| governorate_id | unsignedBigInteger | nullable, FK → governorates.id |
| city_id | unsignedBigInteger | nullable, FK → governorate_cities.id |

**New relationships**: `governorate()` → belongsTo, `city()` → belongsTo GovernorateCity
**Update $fillable**: add `governorate_id`, `city_id`

### Nurse (Modules/RequestNurse) — add columns

| Field | Type | Constraints |
|-------|------|-------------|
| governorate_id | unsignedBigInteger | nullable, FK → governorates.id |
| city_id | unsignedBigInteger | nullable, FK → governorate_cities.id |

**New relationships**: `governorate()` → belongsTo, `city()` → belongsTo GovernorateCity
**Update $fillable**: add `governorate_id`, `city_id`

---

## Entity Relationship Diagram (text)

```
Governorate (1) ──── (*) GovernorateCity
     │                        │
     │ (1)                    │ (1)
     ├──── (*) Doctor         ├──── (*) Doctor
     ├──── (*) Clinics        ├──── (*) Clinics
     ├──── (*) Nurse          ├──── (*) Nurse
     ├──── (*) HomeHealthcare ├──── (*) HomeHealthcare
     ├──── (*) Lab            ├──── (*) Lab
     └──── (*) RadiologyCenter└──── (*) RadiologyCenter
```

---

## Indexes

| Table | Index | Type | Purpose |
|-------|-------|------|---------|
| governorate_cities | governorate_id | btree | City lookup by governorate |
| doctors | governorate_id | btree | Doctor search by governorate |
| doctors | city_id | btree | Doctor search by city |
| clinic | governorate_id | btree | Clinic search by governorate |
| clinic | city_id | btree | Clinic search by city |
| nurses | governorate_id | btree | Nurse search by governorate |
| nurses | city_id | btree | Nurse search by city |
| home_healthcares | governorate_id | btree | HomeHealthcare search |
| home_healthcares | city_id | btree | HomeHealthcare search |
| labs | governorate_id | btree | Lab search |
| labs | city_id | btree | Lab search |
| radiology_centers | governorate_id | btree | RadiologyCenter search |
| radiology_centers | city_id | btree | RadiologyCenter search |
