# Research: Intensive Care Admin Platform

**Branch**: `013-intensive-care-admin` | **Date**: 2026-04-05

## R1: Bilingual Column Strategy for ICU Entities

**Decision**: Use `_ar`/`_en` dual-column pattern with locale-aware virtual accessors and fallback logic.

**Rationale**: This is the established pattern used by the Nurse module (012-nurse-admin-bilingual) and Location module. The accessor pattern provides:
- Transparent locale resolution via `app()->getLocale()`
- Fallback to opposite language if preferred locale column is empty
- Empty string default if both columns are null
- No additional package dependency (avoids spatie/laravel-translatable complexity)

**Alternatives Considered**:
- **spatie/laravel-translatable JSON columns**: Rejected — the existing Hospital model uses `HasTranslations` trait from the 003-intensive-care spec, but the actual bilingual pattern across the codebase uses explicit `_ar`/`_en` columns. Consistency with Nurse/Location modules takes priority.
- **Single JSON column per field**: Rejected — less queryable, harder to validate, inconsistent with project convention.

**Implementation Detail**: The existing Hospital model from 003-intensive-care uses `HasTranslations` trait. The bilingual migration will:
1. Add `name_ar`, `name_en` columns
2. Copy data from existing `name` column to both
3. Drop the old `name` column
4. Remove `HasTranslations` trait from model, replace with explicit accessors

Same pattern for IcuDepartment's `name` and `description` fields.

**Fields to make bilingual**:

| Entity | Field | Type | Required |
|--------|-------|------|----------|
| Hospital | name | string(255) | At least one |
| Hospital | description | text | No |
| Hospital | address | string(255) | No |
| IcuDepartment | name | string(255) | At least one |
| IcuDepartment | description | text | No |
| IcuAdmissionRequest | medical_condition | text | At least one |
| IcuAdmissionRequest | diagnosis | string(255) | No |
| IcuAdmissionRequest | admin_notes | text | No |
| IcuAdmissionRequest | rejection_reason | text | No |

**Note on IcuAdmissionRequest**: Patient-facing text fields (`medical_condition`, `diagnosis`) benefit from bilingual input since patients/families may prefer Arabic or English. Workflow fields (`admin_notes`, `rejection_reason`) also bilingual since coordinators may work in either language.

## R2: Location Integration for Hospitals

**Decision**: Add nullable `governorate_id` and `city_id` foreign keys to the `hospitals` table via a separate migration.

**Rationale**: Follows the exact pattern from the Nurse module, which added `governorate_id` and `city_id` to reference the Location module's `governorates` and `governorate_cities` tables.

**Alternatives Considered**:
- **Embed city/state directly (existing approach)**: The 003-intensive-care migration has `city` (string) and `state` (string) columns. These remain for free-text entry but the FK references provide structured location data for filtering.
- **Replace city/state columns**: Rejected — migration should be additive; keeping both allows backward compatibility.

**Implementation Detail**:
- Migration adds `governorate_id` and `city_id` after existing location fields
- Foreign keys reference `governorates.id` and `governorate_cities.id` with `onDelete('set null')`
- Model gets `governorate()` and `city()` BelongsTo relationships
- Admin form gets cascading Select2 dropdowns (governorate change → AJAX fetch cities)
- Controller `create()`/`edit()` loads `$governorates` and filtered `$cities`

## R3: Commission/Earnings Pattern for ICU

**Decision**: Replicate the Pharmacy commission three-table pattern (IcuCommission → IcuCommissionEarning ← IcuPayout) within the IntensiveCare module.

**Rationale**: The Pharmacy module's commission system is a proven, production-ready pattern with:
- Template-based commission configuration (percentage or flat)
- Snapshot pattern for historical accuracy in earning records
- Pessimistic locking for concurrent payout safety
- Clean status workflow (unpaid → paid | cancelled)

**Alternatives Considered**:
- **Shared commission module**: Rejected — violates Module Encapsulation (Constitution I). Each module should own its domain entities.
- **Commission as a service in app/**: Rejected — the commission logic is tightly coupled to the order/admission lifecycle, making it module-specific.

**Implementation Detail**:
- `icu_commissions` table: id, title, commission_type (percentage/fixed), commission_value, status, audit fields
- `icu_commission_earnings` table: id, admission_request_id (unique), hospital_id, commission_id, order_subtotal, commission_type/value (snapshot), commission_amount, hospital_receivable, status, payout_id, audit fields
- `icu_payouts` table: id, hospital_id, total_amount, total_commission, payment_method, payment_date, notes, audit fields
- `hospitals` table gets `commission_id` FK via migration
- `IcuCommissionService` handles calculation (triggered on admission status → accepted) and reversal (on cancellation)
- The "order subtotal" for ICU is the `daily_price` from the assigned IcuDepartment

**Trigger Point**: Commission is calculated when admission request status transitions to `accepted`. This differs from Pharmacy which triggers on `delivered` status, but the mechanism is identical.

## R4: Admin Controller Pattern

**Decision**: Follow the standard admin controller method set as defined in Constitution II: `index`, `index_data`, `create`, `store`, `edit`, `update`, `destroy`, `bulk_action`, `update_status`.

**Rationale**: This is the established convention for all admin controllers (GovernoratesController, NursesController, PharmacyCommissionController).

**Implementation Notes**:
- `HospitalsController` — full CRUD with DataTable, media upload, bilingual fields, location dropdowns
- `IcuDepartmentsController` — CRUD with hospital filter, inline bed update via `update_beds` endpoint
- `IcuAdmissionRequestsController` — list/show/respond workflow with status transition validation
- `IcuCommissionController` — CRUD for commission templates (mirrors PharmacyCommissionController)
- `IcuEarningController` — earnings dashboard, per-hospital detail, payout processing (mirrors PharmacyEarningController)

## R5: API Endpoint Design

**Decision**: Follow existing API patterns (NursesApiController, FacilityBookingApiController) with Sanctum auth, JSON resources, and role-based filtering.

**Rationale**: Consistent with existing API controllers. The patterns include:
- Eager loading with `->with()` for relationships
- Chained scopes for filtering (`.active()`, `.bySpecialty()`)
- Query parameter filtering with `$request->has()` / `$request->filled()`
- Resource collections with automatic pagination
- Role-based query scoping in index methods

**API Endpoints**:

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | /v1/hospitals | sanctum | List hospitals (filters: governorate_id, city_id, specialty, has_ventilator) |
| GET | /v1/hospitals/{id} | sanctum | Hospital detail with departments and location |
| GET | /v1/icu-departments | sanctum | List departments (filters: hospital_id, specialty, available) |
| GET | /v1/icu-admission-requests | sanctum | User's own requests (role-filtered) |
| POST | /v1/icu-admission-requests | sanctum | Create admission request |
| GET | /v1/icu-admission-requests/{id} | sanctum | Request detail |
| POST | /v1/icu-admission-requests/{id}/cancel | sanctum | Cancel request |
| GET | /v1/icu-commissions | sanctum (admin) | List commission templates |
| GET | /v1/icu-earnings | sanctum (admin) | Earnings summary by hospital |

## R6: Sidebar Integration

**Decision**: Extend the existing Intensive Care sidebar section in GenerateMenus.php to add commission and earnings menu items.

**Rationale**: The sidebar already has an "Intensive Care" section (lines 686-728 in GenerateMenus.php) with Admission Requests, Hospitals, and ICU Departments. Adding commission/earnings items follows the Pharmacy sidebar pattern.

**New Sidebar Items**:
- "ICU Commissions" — visible to admin/demo_admin only (permission: `view_icu_commission`)
- "ICU Earnings" — visible to admin/demo_admin and hospital_coordinator (permission: `view_icu_earning`)

**Permissions to Add (6 new)**:
- `view_icu_commission`, `add_icu_commission`, `edit_icu_commission`, `delete_icu_commission`
- `view_icu_earning`, `edit_icu_earning` (for payout processing)
