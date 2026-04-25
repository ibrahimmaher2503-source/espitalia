# Research: Location-Based Healthcare Search API

**Branch**: `005-location-search-api` | **Date**: 2026-03-31

## R-001: Table Naming Conflict — `cities`

**Decision**: Use `governorate_cities` for the new Egyptian cities table.

**Rationale**: The World module (`Modules/World`) already owns a `cities` table with schema `{id, name, state_id, status}`. The new feature requires a different schema `{id, governorate_id, name_ar, name_en}`. Creating a second `cities` table would cause migration failures. Renaming to `governorate_cities` avoids conflict while remaining semantically clear.

**Alternatives considered**:
- Reuse existing `cities` table: Rejected — schema is incompatible (single `name` vs bilingual `name_ar`/`name_en`, `state_id` vs `governorate_id`), and modifying it would break the World module.
- Use a database prefix: Rejected — adds complexity, non-standard in Laravel.

## R-002: Module Strategy for Governorate/City

**Decision**: Create a new `Location` module (`Modules/Location`) to house the Governorate and GovernorateCity models, migrations, seeders, controllers, and routes.

**Rationale**: Constitution Principle I (Module Encapsulation) requires every feature to be a self-contained module. The governorate/city entities are foundational location data used across all search endpoints — they deserve their own module rather than being shoehorned into Clinic or World.

**Alternatives considered**:
- Add to World module: Rejected — World handles generic Country/State/City; Egyptian governorates are domain-specific.
- Add to Clinic module: Rejected — governorates are not clinic-specific; they're shared across nurses, labs, radiology, etc.

## R-003: Module Strategy for HomeHealthcare, Lab, RadiologyCenter

**Decision**:
- **HomeHealthcare**: Create new `HomeHealthcare` module — no existing module covers this entity.
- **Lab**: The `RadiologyLab` module already has `LabTest` and `LabTestCategory` models. Add a `Lab` model to the existing `RadiologyLab` module to represent the physical laboratory entity (distinct from individual lab tests).
- **RadiologyCenter**: Add to existing `RadiologyLab` module — it's the natural home for radiology entities.

**Rationale**: Reuse existing modules where conceptually aligned. HomeHealthcare is a completely new domain with no existing module.

**Alternatives considered**:
- Create separate Lab and RadiologyCenter modules: Rejected — too granular; they fit naturally in RadiologyLab.

## R-004: Doctor Location Architecture

**Decision**: Add `governorate_id` and `city_id` directly to the `doctors` table (not the `users` table).

**Rationale**: The existing location system uses `users.city`/`users.state`/`users.country` via the World module. Adding governorate/city to the users table would mix two location systems and affect all user types. Adding to the doctors table keeps the Egyptian governorate system scoped to the search feature. The Doctor model already has direct attributes (`experience`, `vendor_id`) — this is consistent.

**Alternatives considered**:
- Add to users table: Rejected — would affect all user roles, not just doctors.
- Use the existing World module city/state system: Rejected — incompatible with bilingual governorate model.

## R-005: Locale Middleware for API

**Decision**: Modify the existing `localization` middleware (`app/Http/Middleware/localization.php`) to check the `Accept-Language` header first, then fall back to session, then default to `en`.

**Rationale**: The middleware is already registered in the API middleware group in `Kernel.php`. Modifying it is simpler than creating a new middleware and avoids duplicate middleware registrations. The change is backward-compatible — existing session-based behavior is preserved as a fallback.

**Alternatives considered**:
- Create a new `SetApiLocale` middleware: Rejected — unnecessary complexity; the existing middleware just needs a one-line enhancement.

## R-006: API Response Format Consistency

**Decision**: Use the `sendResponse()` / `sendError()` helpers from the base `Controller` class for all new endpoints. Pair with JsonResource transformers for data shaping.

**Rationale**: The base controller already has these helpers (`{status, message, data}` format). Using them ensures consistency with the spec's unified response format. Many existing controllers use them inconsistently, but new code should follow the best available pattern.

**Pattern**:
```
Success: { status: true, message: "", data: { items: [...], pagination: {...} } }
Error:   { status: false, message: "localized error", data: null }
```

## R-007: Search Service Pattern

**Decision**: Create a dedicated search service class per entity (e.g., `DoctorSearchService`, `NurseSearchService`) in each entity's module under `Services/`.

**Rationale**: Keeps search logic testable and separate from controllers. Each service accepts filter parameters, builds the Eloquent query with conditional `when()` clauses, and returns a paginated result. This is the simplest approach that avoids over-engineering (YAGNI — Principle V).

**Alternatives considered**:
- Generic SearchService with strategy pattern: Rejected — over-engineered for 6 straightforward search queries.
- Search logic directly in controllers: Rejected — too much logic in controllers, harder to test.

## R-008: Existing Entity Field Mapping for Search

**Decision**: Map search filters to actual existing database columns:

| Spec Filter | Doctor Column | Source |
|-------------|--------------|--------|
| name | `users.first_name` / `users.last_name` | Via `doctors.doctor_id` → users join |
| specialty_id | `doctor_service_mapping.service_id` | Via relationship |
| gender | `users.gender` | Via user relationship |
| min_price / max_price | `doctor_call_services.charges` or `clinics_services.charges` | Via service mapping |
| governorate_id | `doctors.governorate_id` | New column |
| city_id | `doctors.city_id` | New column |

| Spec Filter | Clinic Column | Source |
|-------------|--------------|--------|
| name | `clinic.clinic_name` | Direct |
| specialty_id | `clinics_services.service_id` | Via relationship |
| governorate_id | `clinic.governorate_id` | New column |
| city_id | `clinic.city_id` | New column |

| Spec Filter | Nurse Column | Source |
|-------------|-------------|--------|
| specialty | `nurses.specialization` | Direct |
| gender | `users.gender` | Via `nurses.nurse_id` → users |
| availability | `nurses.availability_status` | Direct (enum: available/busy/off_duty) |
| governorate_id | `nurses.governorate_id` | New column |
| city_id | `nurses.city_id` | New column |

## R-009: Seeder Data Source

**Decision**: Hardcode all 27 Egyptian governorates with Arabic and English names in a seeder class. Include 3-10 major cities per governorate for the initial seed.

**Rationale**: The data is static and well-known. An external API or CSV adds unnecessary dependency. The seeder can always be expanded later.
