# Data Model: Admin UI for Labs & Radiology Centers

**Feature Branch**: `006-admin-labs-radiology-ui`
**Date**: 2026-04-02

## Existing Entities (No Changes Required)

### Lab

Already exists in `Modules/RadiologyLab/Models/Lab.php` with table `labs`.

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | bigint (PK) | auto-increment | |
| name_ar | string(255) | required | Arabic name |
| name_en | string(255) | required | English name |
| governorate_id | bigint (FK) | nullable, references governorates.id | ON DELETE SET NULL |
| city_id | bigint (FK) | nullable, references governorate_cities.id | ON DELETE SET NULL |
| status | boolean | default: 1 | Active/inactive toggle |
| created_by | integer | nullable | Audit field (BaseModel) |
| updated_by | integer | nullable | Audit field (BaseModel) |
| deleted_by | integer | nullable | Audit field (BaseModel) |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

**Relationships**:
- belongsTo Governorate (via governorate_id)
- belongsTo GovernorateCity (via city_id)

**Accessor**: `name` — returns `name_ar` or `name_en` based on `app()->getLocale()`

---

### RadiologyCenter

Already exists in `Modules/RadiologyLab/Models/RadiologyCenter.php` with table `radiology_centers`.

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | bigint (PK) | auto-increment | |
| name_ar | string(255) | required | Arabic name |
| name_en | string(255) | required | English name |
| scan_type | string(255) | nullable | e.g., MRI, CT, X-Ray |
| governorate_id | bigint (FK) | nullable, references governorates.id | ON DELETE SET NULL |
| city_id | bigint (FK) | nullable, references governorate_cities.id | ON DELETE SET NULL |
| status | boolean | default: 1 | Active/inactive toggle |
| created_by | integer | nullable | Audit field (BaseModel) |
| updated_by | integer | nullable | Audit field (BaseModel) |
| deleted_by | integer | nullable | Audit field (BaseModel) |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

**Relationships**:
- belongsTo Governorate (via governorate_id)
- belongsTo GovernorateCity (via city_id)

**Accessor**: `name` — returns `name_ar` or `name_en` based on `app()->getLocale()`

---

### Governorate (Reference — Location Module)

Exists in `Modules/Location/Models/Governorate.php`.

| Field | Type | Notes |
|-------|------|-------|
| id | bigint (PK) | |
| name_ar | string | Arabic name |
| name_en | string | English name |

**Accessor**: `name` — locale-aware

---

### GovernorateCity (Reference — Location Module)

Exists in `Modules/Location/Models/GovernorateCity.php`.

| Field | Type | Notes |
|-------|------|-------|
| id | bigint (PK) | |
| governorate_id | bigint (FK) | References governorates.id |
| name_ar | string | Arabic name |
| name_en | string | English name |

**Accessor**: `name` — locale-aware

---

## New Entities

### Permissions (via spatie/laravel-permission)

New permission records to be created via seeder:

| Permission Name | Assigned To |
|----------------|-------------|
| view_labs | admin, demo_admin |
| add_labs | admin, demo_admin |
| edit_labs | admin, demo_admin |
| delete_labs | admin, demo_admin |
| view_radiology_centers | admin, demo_admin |
| add_radiology_centers | admin, demo_admin |
| edit_radiology_centers | admin, demo_admin |
| delete_radiology_centers | admin, demo_admin |

---

## Validation Rules

### LabFormRequest

| Field | Rules |
|-------|-------|
| name_ar | required, string, max:255 |
| name_en | required, string, max:255 |
| governorate_id | nullable, integer, exists:governorates,id |
| city_id | nullable, integer, exists:governorate_cities,id |
| status | nullable, boolean |

### RadiologyCenterFormRequest

| Field | Rules |
|-------|-------|
| name_ar | required, string, max:255 |
| name_en | required, string, max:255 |
| scan_type | nullable, string, max:255 |
| governorate_id | nullable, integer, exists:governorates,id |
| city_id | nullable, integer, exists:governorate_cities,id |
| status | nullable, boolean |

---

## No Migrations Required

Both `labs` and `radiology_centers` tables already exist with the correct schema. No data model changes are needed for this feature.
