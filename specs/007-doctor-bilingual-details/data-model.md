# Data Model: Bilingual Doctor Details (AR/EN)

**Branch**: `007-doctor-bilingual-details` | **Date**: 2026-04-02

## Entity Changes

### 1. Users Table (Migration: `add_bilingual_name_to_users_table`)

New columns added alongside existing `first_name`/`last_name`:

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `first_name_ar` | string(255) | Yes | null | Arabic first name |
| `first_name_en` | string(255) | Yes | null | English first name |
| `last_name_ar` | string(255) | Yes | null | Arabic last name |
| `last_name_en` | string(255) | Yes | null | English last name |

**Data Migration**: Existing `first_name` → `first_name_en` AND `first_name_ar`. Same for `last_name`.

**Original columns**: `first_name` and `last_name` are **kept** for backward compatibility. They continue to be populated on save (synced from the English value).

**Accessor Behavior** (`User` model):
- `$user->first_name` → returns locale-aware value (AR if locale=ar, EN otherwise), with fallback chain
- `$user->first_name_ar` → returns raw Arabic value
- `$user->first_name_en` → returns raw English value
- Same pattern for `last_name`

---

### 2. Doctors Table (Migration: `add_bilingual_fields_to_doctors_table`)

New columns added alongside existing `experience`/`description`:

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `experience_ar` | string(255) | Yes | null | Arabic experience text |
| `experience_en` | string(255) | Yes | null | English experience text |
| `description_ar` | string(255) | Yes | null | Arabic description |
| `description_en` | string(255) | Yes | null | English description |

**Data Migration**: Existing `experience` → `experience_en` AND `experience_ar`. Same for `description` (note: column name is `Description` with capital D in original migration — use the actual DB column name).

**Original columns**: `experience` and `Description` are **kept** for backward compatibility.

**Accessor Behavior** (`Doctor` model):
- `$doctor->experience` → returns locale-aware value with fallback
- `$doctor->experience_ar` / `$doctor->experience_en` → raw values
- Same pattern for `description`

---

### 3. User Profiles Table (Migration: `add_bilingual_fields_to_user_profiles_table`)

New columns added alongside existing `expert`/`about_self`:

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `expert_ar` | string(255) | Yes | null | Arabic expertise/specialization label |
| `expert_en` | string(255) | Yes | null | English expertise/specialization label |
| `about_self_ar` | text | Yes | null | Arabic biography |
| `about_self_en` | text | Yes | null | English biography |

**Data Migration**: Existing `expert` → `expert_en` AND `expert_ar`. Same for `about_self`.

**Original columns**: `expert` and `about_self` are **kept** for backward compatibility.

**Accessor Behavior** (`UserProfile` model):
- `$profile->expert` → returns locale-aware value with fallback
- `$profile->about_self` → returns locale-aware value with fallback

---

## Accessor Pattern (All Models)

All bilingual accessors follow the same pattern:

```
getFieldAttribute():
  1. Check locale (app()->getLocale())
  2. If locale=ar → return field_ar (if not empty)
  3. If locale=en → return field_en (if not empty)
  4. Fallback: return the other language's value (if not empty)
  5. Final fallback: return the original column value (if exists)
  6. Return empty string
```

## Mutator Pattern (User Model Only)

When saving, the original `first_name`/`last_name` columns are kept in sync:

```
setFirstNameEnAttribute($value):
  1. Set first_name_en = $value
  2. Set first_name = $value  (sync original column)
```

This ensures backward compatibility for code that reads the original columns directly.

---

## Relationships (Unchanged)

No new relationships are introduced. Existing relationships are unaffected:

- `User` hasOne `Doctor` (via `doctor_id`)
- `User` hasOne `UserProfile` (via `user_id`)
- `Doctor` belongsTo `User` (via `doctor_id`)
- `Doctor` belongsTo `Governorate` (via `governorate_id`)
- `Doctor` belongsTo `GovernorateCity` (via `city_id`)

---

## Validation Rules Changes

### DoctorRequest (Admin Create/Edit)

Current required fields `first_name` and `last_name` change to:

| Field | Rule | Notes |
|-------|------|-------|
| `first_name_en` | `required_without:first_name_ar\|string\|max:255` | At least one language required |
| `first_name_ar` | `required_without:first_name_en\|string\|max:255` | At least one language required |
| `last_name_en` | `required_without:last_name_ar\|string\|max:255` | At least one language required |
| `last_name_ar` | `required_without:last_name_en\|string\|max:255` | At least one language required |
| `experience_en` | `nullable\|string\|max:255` | Optional |
| `experience_ar` | `nullable\|string\|max:255` | Optional |
| `description_en` | `nullable\|string\|max:500` | Optional |
| `description_ar` | `nullable\|string\|max:500` | Optional |
| `expert_en` | `nullable\|string\|max:255` | Optional |
| `expert_ar` | `nullable\|string\|max:255` | Optional |
| `about_self_en` | `nullable\|string\|max:2000` | Optional |
| `about_self_ar` | `nullable\|string\|max:2000` | Optional |

### DoctorSearchRequest (Search API)

The `name` filter remains a single string — the search service queries both `_ar` and `_en` columns.

---

## Index Considerations

No new database indexes required. The bilingual columns are queried via LIKE which doesn't benefit from standard B-tree indexes. The existing data volumes (< 10k doctors) don't require special indexing.
