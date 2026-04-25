# Data Model: Specialization Taxonomy

**Branch**: `022-specialization-taxonomy` | **Date**: 2026-04-21

## Entity Relationship Diagram

```
┌──────────────────────┐
│   specializations    │
├──────────────────────┤
│ id (PK)              │
│ name_ar              │
│ name_en              │
│ description_ar       │
│ description_en       │
│ slug                 │
│ parent_id (FK→self)  │
│ icon                 │──────┐
│ status               │      │ self-referential
│ created_by           │      │ (1 level max)
│ updated_by           │◄─────┘
│ deleted_by           │
│ created_at           │
│ updated_at           │
│ deleted_at           │
└──────┬───────┬───────┘
       │       │
       │       │  ┌─────────────────────────────┐
       │       └──┤ doctor_specialization (pivot)│
       │          ├─────────────────────────────┤
       │          │ specialization_id (FK)       │
       │          │ doctor_id (FK→doctors.id)    │
       │          │ created_at                   │
       │          │ updated_at                   │
       │          └─────────────────────────────┘
       │
       │          ┌─────────────────────────────┐
       └──────────┤ nurse_specialization (pivot) │
                  ├─────────────────────────────┤
                  │ specialization_id (FK)       │
                  │ nurse_id (FK→nurses.id)      │
                  │ created_at                   │
                  │ updated_at                   │
                  └─────────────────────────────┘
```

## Tables

### 1. `specializations`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | |
| `name_ar` | varchar(255) | NOT NULL | Arabic name |
| `name_en` | varchar(255) | NOT NULL | English name |
| `description_ar` | text | nullable | Arabic description |
| `description_en` | text | nullable | English description |
| `slug` | varchar(255) | UNIQUE, NOT NULL | URL-friendly identifier (generated from name_en) |
| `parent_id` | bigint unsigned | nullable, FK→specializations.id ON DELETE RESTRICT | Parent specialization (1 level max) |
| `icon` | varchar(255) | nullable | Phosphor icon class (e.g., `ph ph-heart-beat`) or media library reference |
| `status` | tinyint | NOT NULL, DEFAULT 1 | 0=inactive, 1=active |
| `created_by` | bigint unsigned | nullable | Auto-set by BaseModel |
| `updated_by` | bigint unsigned | nullable | Auto-set by BaseModel |
| `deleted_by` | bigint unsigned | nullable | Auto-set by BaseModel |
| `created_at` | timestamp | nullable | |
| `updated_at` | timestamp | nullable | |
| `deleted_at` | timestamp | nullable | Soft delete |

**Indexes**:
- PRIMARY on `id`
- UNIQUE on `slug`
- INDEX on `parent_id`
- INDEX on `status`
- INDEX on `deleted_at`

**Migration name**: `create_specializations_table`

### 2. `doctor_specialization` (pivot)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `specialization_id` | bigint unsigned | FK→specializations.id ON DELETE CASCADE | |
| `doctor_id` | bigint unsigned | FK→doctors.id ON DELETE CASCADE | |
| `created_at` | timestamp | nullable | |
| `updated_at` | timestamp | nullable | |

**Indexes**:
- PRIMARY composite on (`specialization_id`, `doctor_id`)
- UNIQUE composite on (`doctor_id`, `specialization_id`) — prevents duplicates from either direction

**Migration name**: `create_doctor_specialization_table`

### 3. `nurse_specialization` (pivot)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `specialization_id` | bigint unsigned | FK→specializations.id ON DELETE CASCADE | |
| `nurse_id` | bigint unsigned | FK→nurses.id ON DELETE CASCADE | |
| `created_at` | timestamp | nullable | |
| `updated_at` | timestamp | nullable | |

**Indexes**:
- PRIMARY composite on (`specialization_id`, `nurse_id`)
- UNIQUE composite on (`nurse_id`, `specialization_id`) — prevents duplicates from either direction

**Migration name**: `create_nurse_specialization_table`

## Model Definitions

### Specialization Model

```php
// Modules/Specialization/Models/Specialization.php
class Specialization extends BaseModel
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name_ar', 'name_en',
        'description_ar', 'description_en',
        'slug', 'parent_id', 'icon', 'status',
    ];

    // Bilingual accessors (standard pattern)
    // getNameAttribute(), getDescriptionAttribute()

    // Relationships
    // parent(): belongsTo(self, 'parent_id')
    // children(): hasMany(self, 'parent_id')
    // doctors(): belongsToMany(Doctor::class, 'doctor_specialization')
    // nurses(): belongsToMany(Nurse::class, 'nurse_specialization')

    // Scopes
    // scopeActive($query): where('status', 1)
    // scopeTopLevel($query): whereNull('parent_id')
}
```

### Doctor Model Changes

```php
// Add to Modules/Clinic/Models/Doctor.php
// New relationship:
// specializations(): belongsToMany(Specialization::class, 'doctor_specialization')
```

### Nurse Model Changes

```php
// Add to Modules/RequestNurse/Models/Nurse.php
// New relationship:
// specializations(): belongsToMany(Specialization::class, 'nurse_specialization')
```

## Validation Rules

### Specialization CRUD

| Field | Create | Update |
|-------|--------|--------|
| `name_ar` | required, string, max:255 | required, string, max:255 |
| `name_en` | required, string, max:255 | required, string, max:255 |
| `description_ar` | nullable, string | nullable, string |
| `description_en` | nullable, string | nullable, string |
| `parent_id` | nullable, exists:specializations,id | nullable, exists:specializations,id |
| `icon` | nullable, string, max:255 | nullable, string, max:255 |
| `status` | required, in:0,1 | required, in:0,1 |

### Doctor/Nurse Specialization Assignment

| Field | Rule |
|-------|------|
| `specialization_ids` | nullable, array |
| `specialization_ids.*` | exists:specializations,id |

## State Transitions

### Specialization Status

```
Active (1) ←→ Inactive (0)
     ↓              ↓
  Soft-deleted   Soft-deleted
```

- Active → Inactive: via `update_status` or bulk action. Specialization is excluded from public filters and new assignments.
- Inactive → Active: via `update_status` or bulk action.
- Any → Soft-deleted: via `destroy` or bulk delete. Only allowed if no active children exist (FR-003).
- Soft-deleted → Restored: via admin restore action.

### Cascade Behavior

| Event | Effect on Pivots | Effect on Children |
|-------|-----------------|-------------------|
| Specialization soft-deleted | Pivot records retained; specialization excluded from filters | BLOCKED if active children exist |
| Specialization hard-deleted | Pivot records CASCADE deleted | BLOCKED (via ON DELETE RESTRICT) |
| Doctor soft-deleted | Pivot records retained | N/A |
| Nurse soft-deleted | Pivot records retained | N/A |

## Data Migration (Nurse Free-Text)

A separate artisan command (not a migration) will:

1. Query all active `Specialization` records into a lookup map (keyed by lowercase `name_ar` and `name_en`)
2. Iterate all `Nurse` records with non-null `specialization_ar` or `specialization_en`
3. For each nurse:
   - Exact case-insensitive match of `specialization_ar` against specialization `name_ar`
   - Exact case-insensitive match of `specialization_en` against specialization `name_en`
   - If match found: create pivot record in `nurse_specialization`
   - If no match: log the nurse ID and text value for manual review
4. Output a summary: X matched, Y unmatched, Z flagged for review

The legacy `specialization_ar`/`specialization_en` columns are **not** dropped — they remain for fallback display during the deprecation window.
