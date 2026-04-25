# Data Model: Intensive Care Admin Platform

**Branch**: `013-intensive-care-admin` | **Date**: 2026-04-05

This document defines the data model changes required for bilingual support, location integration, and the commission/earnings system. The base tables (`hospitals`, `icu_departments`, `icu_admission_requests`) already exist from `003-intensive-care`.

---

## Migration 1: Make Hospital Fields Bilingual + Add Location FKs

**File**: `2026_04_05_100001_make_hospital_fields_bilingual.php`

### New Columns (hospitals table)

| Column | Type | Nullable | Position | Notes |
|--------|------|----------|----------|-------|
| name_ar | string(255) | yes | after slug | Bilingual: Arabic name |
| name_en | string(255) | yes | after name_ar | Bilingual: English name |
| description_ar | text | yes | after name_en | Bilingual: Arabic description |
| description_en | text | yes | after description_ar | Bilingual: English description |
| address_ar | string(255) | yes | after description_en | Bilingual: Arabic address |
| address_en | string(255) | yes | after address_ar | Bilingual: English address |
| governorate_id | unsignedBigInteger | yes | after address_en | FK → governorates.id |
| city_id | unsignedBigInteger | yes | after governorate_id | FK → governorate_cities.id |

### Data Migration

```
UPDATE hospitals SET name_ar = name, name_en = name
UPDATE hospitals SET description_ar = description, description_en = description
UPDATE hospitals SET address_ar = address, address_en = address
```

### Dropped Columns

| Column | Replacement |
|--------|-------------|
| name | name_ar + name_en |
| description | description_ar + description_en |
| address | address_ar + address_en |

### Foreign Keys

| Column | References | OnDelete |
|--------|-----------|----------|
| governorate_id | governorates.id | SET NULL |
| city_id | governorate_cities.id | SET NULL |

### Indexes

- `governorate_id`
- `city_id`

### Down Migration

Reverses: re-creates `name`, `description`, `address` columns; copies `_en` values back; drops bilingual columns and FKs.

---

## Migration 2: Make IcuDepartment Fields Bilingual

**File**: `2026_04_05_100002_make_icu_department_fields_bilingual.php`

### New Columns (icu_departments table)

| Column | Type | Nullable | Position | Notes |
|--------|------|----------|----------|-------|
| name_ar | string(255) | yes | after hospital_id | Bilingual: Arabic name |
| name_en | string(255) | yes | after name_ar | Bilingual: English name |
| description_ar | text | yes | after name_en | Bilingual: Arabic description |
| description_en | text | yes | after description_ar | Bilingual: English description |

### Data Migration

```
UPDATE icu_departments SET name_ar = name, name_en = name
UPDATE icu_departments SET description_ar = description, description_en = description
```

### Dropped Columns

| Column | Replacement |
|--------|-------------|
| name | name_ar + name_en |
| description | description_ar + description_en |

---

## Migration 3: Make IcuAdmissionRequest Fields Bilingual

**File**: `2026_04_05_100003_make_icu_admission_request_fields_bilingual.php`

### New Columns (icu_admission_requests table)

| Column | Type | Nullable | Position | Notes |
|--------|------|----------|----------|-------|
| medical_condition_ar | text | yes | after insurance_number | Bilingual |
| medical_condition_en | text | yes | after medical_condition_ar | Bilingual |
| diagnosis_ar | string(255) | yes | after medical_condition_en | Bilingual |
| diagnosis_en | string(255) | yes | after diagnosis_ar | Bilingual |
| admin_notes_ar | text | yes | after status | Bilingual |
| admin_notes_en | text | yes | after admin_notes_ar | Bilingual |
| rejection_reason_ar | text | yes | after admin_notes_en | Bilingual |
| rejection_reason_en | text | yes | after rejection_reason_ar | Bilingual |

### Data Migration

```
UPDATE icu_admission_requests SET medical_condition_ar = medical_condition, medical_condition_en = medical_condition
UPDATE icu_admission_requests SET diagnosis_ar = diagnosis, diagnosis_en = diagnosis
UPDATE icu_admission_requests SET admin_notes_ar = admin_notes, admin_notes_en = admin_notes
UPDATE icu_admission_requests SET rejection_reason_ar = rejection_reason, rejection_reason_en = rejection_reason
```

### Dropped Columns

| Column | Replacement |
|--------|-------------|
| medical_condition | medical_condition_ar + medical_condition_en |
| diagnosis | diagnosis_ar + diagnosis_en |
| admin_notes | admin_notes_ar + admin_notes_en |
| rejection_reason | rejection_reason_ar + rejection_reason_en |

---

## Migration 4: Create ICU Commissions Table

**File**: `2026_04_05_100004_create_icu_commissions_table.php`

### Table: icu_commissions

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigIncrements (PK) | no | auto | |
| title | string(255) | no | | Commission template name |
| commission_type | string(20) | no | | 'percentage' or 'fixed' |
| commission_value | decimal(8,2) | no | | Percentage (0-100) or flat amount |
| status | boolean | no | true | Active/inactive |
| created_by | unsignedBigInteger | yes | | Audit |
| updated_by | unsignedBigInteger | yes | | Audit |
| deleted_by | unsignedBigInteger | yes | | Audit |
| timestamps | | | | created_at, updated_at |
| softDeletes | | | | deleted_at |

### Indexes

- `status`

---

## Migration 5: Create ICU Commission Earnings Table

**File**: `2026_04_05_100005_create_icu_commission_earnings_table.php`

### Table: icu_commission_earnings

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigIncrements (PK) | no | auto | |
| admission_request_id | unsignedBigInteger | no | | FK → icu_admission_requests.id |
| hospital_id | unsignedBigInteger | no | | FK → hospitals.id |
| commission_id | unsignedBigInteger | yes | | FK → icu_commissions.id (snapshot ref) |
| order_subtotal | decimal(10,2) | no | | Admission amount (daily_price) |
| commission_type | string(20) | no | | Snapshot: 'percentage' or 'fixed' |
| commission_value | decimal(8,2) | no | | Snapshot: rate at time of calculation |
| commission_amount | decimal(10,2) | no | | Platform's cut |
| hospital_receivable | decimal(10,2) | no | | Hospital's share |
| status | string(20) | no | 'unpaid' | unpaid / paid / cancelled |
| payout_id | unsignedBigInteger | yes | | FK → icu_payouts.id |
| created_by | unsignedBigInteger | yes | | Audit |
| updated_by | unsignedBigInteger | yes | | Audit |
| deleted_by | unsignedBigInteger | yes | | Audit |
| timestamps | | | | |
| softDeletes | | | | |

### Foreign Keys

| Column | References | OnDelete |
|--------|-----------|----------|
| admission_request_id | icu_admission_requests.id | CASCADE |
| hospital_id | hospitals.id | CASCADE |
| commission_id | icu_commissions.id | SET NULL |
| payout_id | icu_payouts.id | SET NULL |

### Indexes

- `admission_request_id` (UNIQUE — one earning per admission)
- `hospital_id`
- `status`
- `(hospital_id, status)` (composite)
- `payout_id`

---

## Migration 6: Create ICU Payouts Table

**File**: `2026_04_05_100006_create_icu_payouts_table.php`

### Table: icu_payouts

| Column | Type | Nullable | Default | Notes |
|--------|------|----------|---------|-------|
| id | bigIncrements (PK) | no | auto | |
| hospital_id | unsignedBigInteger | no | | FK → hospitals.id |
| total_amount | decimal(10,2) | no | | Sum of hospital_receivable |
| total_commission | decimal(10,2) | no | | Sum of commission_amount |
| payment_method | string(50) | no | | 'cash' or 'bank_transfer' |
| payment_date | date | no | | When payment was made |
| notes | text | yes | | Admin notes |
| created_by | unsignedBigInteger | yes | | Audit |
| updated_by | unsignedBigInteger | yes | | Audit |
| deleted_by | unsignedBigInteger | yes | | Audit |
| timestamps | | | | |
| softDeletes | | | | |

### Foreign Keys

| Column | References | OnDelete |
|--------|-----------|----------|
| hospital_id | hospitals.id | CASCADE |

### Indexes

- `hospital_id`
- `payment_date`

---

## Migration 7: Add commission_id to Hospitals Table

**File**: `2026_04_05_100007_add_commission_id_to_hospitals_table.php`

### New Column (hospitals table)

| Column | Type | Nullable | Position | Notes |
|--------|------|----------|----------|-------|
| commission_id | unsignedBigInteger | yes | after rating | FK → icu_commissions.id |

### Foreign Key

| Column | References | OnDelete |
|--------|-----------|----------|
| commission_id | icu_commissions.id | SET NULL |

### Index

- `commission_id`

---

## Entity Relationship Diagram

```
Governorate (Location module)
  │ 1
  └──→ * GovernorateCity (Location module)
            │ 0..1                0..1 │
            └────── Hospital ──────────┘
                      │ 1
                      ├──→ * IcuDepartment
                      │        │ 0..1
                      │        └──── IcuAdmissionRequest
                      │                    │ 1
                      │                    └──→ 0..1 IcuCommissionEarning
                      │                                   │ *
                      │                                   └──→ 0..1 IcuPayout
                      │ *                                          │ 1
                      └──→ 1 IcuCommission ←──────────────────────┘
                              (commission template)         (hospital payouts)
```

---

## Model Accessor Pattern

All bilingual fields follow this accessor pattern (from Nurse module):

```
getFieldAttribute():
  1. locale = app()->getLocale()
  2. localized = (locale === 'ar') ? this->field_ar : this->field_en
  3. return localized ?: (fallback to other language) ?: ''
```

### Hospital Accessors

| Virtual Property | AR Column | EN Column |
|-----------------|-----------|-----------|
| name | name_ar | name_en |
| description | description_ar | description_en |
| address | address_ar | address_en |

### IcuDepartment Accessors

| Virtual Property | AR Column | EN Column |
|-----------------|-----------|-----------|
| name | name_ar | name_en |
| description | description_ar | description_en |

### IcuAdmissionRequest Accessors

| Virtual Property | AR Column | EN Column |
|-----------------|-----------|-----------|
| medical_condition | medical_condition_ar | medical_condition_en |
| diagnosis | diagnosis_ar | diagnosis_en |
| admin_notes | admin_notes_ar | admin_notes_en |
| rejection_reason | rejection_reason_ar | rejection_reason_en |

---

## Model: IcuCommission

**Table**: `icu_commissions`
**Extends**: `App\Models\BaseModel`

### Constants

```
TYPE_PERCENTAGE = 'percentage'
TYPE_FIXED = 'fixed'
```

### Fillable

`title`, `commission_type`, `commission_value`, `status`

### Casts

- `status` → boolean
- `commission_value` → decimal:2

### Relationships

- `pharmacies()` → hasMany(Hospital, 'commission_id')  [note: named `hospitals()` in this context]
- `earnings()` → hasMany(IcuCommissionEarning, 'commission_id')

### Scopes

- `active()` → where status = true

---

## Model: IcuCommissionEarning

**Table**: `icu_commission_earnings`
**Extends**: `App\Models\BaseModel`

### Constants

```
STATUS_UNPAID = 'unpaid'
STATUS_PAID = 'paid'
STATUS_CANCELLED = 'cancelled'
```

### Fillable

`admission_request_id`, `hospital_id`, `commission_id`, `order_subtotal`, `commission_type`, `commission_value`, `commission_amount`, `hospital_receivable`, `status`, `payout_id`

### Casts

- `order_subtotal` → decimal:2
- `commission_value` → decimal:2
- `commission_amount` → decimal:2
- `hospital_receivable` → decimal:2

### Relationships

- `admissionRequest()` → belongsTo(IcuAdmissionRequest, 'admission_request_id')
- `hospital()` → belongsTo(Hospital, 'hospital_id')
- `commission()` → belongsTo(IcuCommission, 'commission_id')
- `payout()` → belongsTo(IcuPayout, 'payout_id')

### Scopes

- `unpaid()` → where status = STATUS_UNPAID
- `paid()` → where status = STATUS_PAID
- `forHospital($hospitalId)` → where hospital_id = $hospitalId

---

## Model: IcuPayout

**Table**: `icu_payouts`
**Extends**: `App\Models\BaseModel`

### Constants

```
PAYMENT_METHOD_CASH = 'cash'
PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer'
```

### Fillable

`hospital_id`, `total_amount`, `total_commission`, `payment_method`, `payment_date`, `notes`

### Casts

- `total_amount` → decimal:2
- `total_commission` → decimal:2
- `payment_date` → date

### Relationships

- `hospital()` → belongsTo(Hospital, 'hospital_id')
- `earnings()` → hasMany(IcuCommissionEarning, 'payout_id')

---

## Updated Hospital Model (additions)

### New Fillable Fields

Add to existing fillable: `name_ar`, `name_en`, `description_ar`, `description_en`, `address_ar`, `address_en`, `governorate_id`, `city_id`, `commission_id`

Remove from fillable: `name`, `description`, `address`

Remove trait: `HasTranslations` (replaced by explicit accessors)

### New Relationships

- `governorate()` → belongsTo(Governorate)
- `city()` → belongsTo(GovernorateCity, 'city_id')
- `commission()` → belongsTo(IcuCommission, 'commission_id')
- `commissionEarnings()` → hasMany(IcuCommissionEarning, 'hospital_id')
- `payouts()` → hasMany(IcuPayout, 'hospital_id')

### New Accessors

- `getNameAttribute()` — bilingual with fallback
- `getDescriptionAttribute()` — bilingual with fallback
- `getAddressAttribute()` — bilingual with fallback

---

## Updated IcuDepartment Model (additions)

### New Fillable Fields

Add: `name_ar`, `name_en`, `description_ar`, `description_en`
Remove: `name`, `description`
Remove trait: `HasTranslations`

### New Accessors

- `getNameAttribute()` — bilingual with fallback
- `getDescriptionAttribute()` — bilingual with fallback

---

## Updated IcuAdmissionRequest Model (additions)

### New Fillable Fields

Add: `medical_condition_ar`, `medical_condition_en`, `diagnosis_ar`, `diagnosis_en`, `admin_notes_ar`, `admin_notes_en`, `rejection_reason_ar`, `rejection_reason_en`
Remove: `medical_condition`, `diagnosis`, `admin_notes`, `rejection_reason`

### New Accessors

- `getMedicalConditionAttribute()` — bilingual with fallback
- `getDiagnosisAttribute()` — bilingual with fallback
- `getAdminNotesAttribute()` — bilingual with fallback
- `getRejectionReasonAttribute()` — bilingual with fallback

---

## Validation Rules Summary

### Hospital Form

| Field | Rule |
|-------|------|
| name_ar | nullable\|string\|max:255 (at least one of name_ar/name_en required) |
| name_en | nullable\|string\|max:255 |
| description_ar | nullable\|string\|max:5000 |
| description_en | nullable\|string\|max:5000 |
| address_ar | nullable\|string\|max:255 |
| address_en | nullable\|string\|max:255 |
| governorate_id | nullable\|exists:governorates,id |
| city_id | nullable\|exists:governorate_cities,id |
| hospital_type | required\|in:government,private,military,university |
| phone | required\|string\|max:20 |
| commission_id | nullable\|exists:icu_commissions,id |

### IcuDepartment Form

| Field | Rule |
|-------|------|
| hospital_id | required\|exists:hospitals,id |
| name_ar | nullable\|string\|max:255 (at least one required) |
| name_en | nullable\|string\|max:255 |
| description_ar | nullable\|string\|max:5000 |
| description_en | nullable\|string\|max:5000 |
| specialty_type | required\|in:cardiac,neurology,pediatric,neonatal,burns,chest,surgical,general |
| total_beds | required\|integer\|min:0 |
| available_beds | required\|integer\|min:0\|lte:total_beds |
| daily_price | required\|numeric\|min:0 |

### IcuCommission Form

| Field | Rule |
|-------|------|
| title | required\|string\|max:255 |
| commission_type | required\|in:percentage,fixed |
| commission_value | required\|numeric\|min:0 (max:100 if percentage) |
| status | nullable\|boolean |

### IcuPayout Form

| Field | Rule |
|-------|------|
| payment_method | required\|in:cash,bank_transfer |
| notes | nullable\|string\|max:500 |

---

## Permissions (6 new, added to existing 10)

| Permission | admin | demo_admin | hospital_coordinator |
|-----------|-------|-----------|---------------------|
| view_icu_commission | x | x | |
| add_icu_commission | x | x | |
| edit_icu_commission | x | x | |
| delete_icu_commission | x | x | |
| view_icu_earning | x | x | x (own hospital) |
| edit_icu_earning | x | x | |
