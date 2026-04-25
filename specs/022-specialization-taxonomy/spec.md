# Feature Specification: Specialization Taxonomy Refactor

**Feature Branch**: `022-specialization-taxonomy`  
**Created**: 2026-04-21  
**Status**: Draft  
**Input**: Refactor the taxonomy used across Clinics, Doctors, and Nurses so that "Specialization" becomes a first-class, reusable entity independent from SystemServiceCategory and ClinicsCategory. Doctors and Nurses link to Specializations via many-to-many pivots, replacing legacy free-text fields. Full admin CRUD, frontend filters, QuickBooking integration, and public API.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin Manages Specializations (Priority: P1)

An admin navigates to the Specializations page under the Masters sidebar section. They can view all specializations in a DataTable with search, pagination, and status indicators. They create a new specialization by filling in Arabic and English names, descriptions, selecting an optional parent specialization for hierarchy, uploading an icon, and setting status. They can edit, soft-delete, toggle status, and perform bulk actions (activate, deactivate, delete) on specializations.

**Why this priority**: This is the foundational entity that all other stories depend on. Without the ability to manage specializations, no downstream linking or filtering is possible.

**Independent Test**: Can be fully tested by logging in as admin, navigating to Specializations, and performing full CRUD operations. Delivers value as a standalone master-data management feature.

**Acceptance Scenarios**:

1. **Given** an admin is logged in, **When** they navigate to the Specializations page, **Then** they see a DataTable listing all specializations with columns: name, parent, status, and action buttons.
2. **Given** an admin clicks "Add Specialization", **When** they fill in name_ar, name_en, description_ar, description_en, select a parent (optional), upload an icon, set status to active, and submit, **Then** the specialization is created and appears in the list.
3. **Given** a specialization exists, **When** the admin edits it and changes the Arabic name, **Then** the updated name appears in the list.
4. **Given** multiple specializations are selected, **When** the admin performs a bulk "Deactivate" action, **Then** all selected specializations have status set to inactive.
5. **Given** a specialization with child specializations, **When** the admin views it, **Then** the hierarchy is visible (parent shown in list, children indented or filterable).
6. **Given** a specialization is soft-deleted, **When** it is removed, **Then** it no longer appears in active lists but remains in the database.

---

### User Story 2 - Admin Assigns Specializations to Doctors (Priority: P2)

On the Doctor edit screen, the admin sees a multi-select field (searchable) for Specializations. They can assign one or more specializations to a doctor. The legacy `expert` field (from user_profiles) is displayed as read-only for reference during the transition period. The assigned specializations appear on the doctor's profile in the admin panel.

**Why this priority**: Doctors are the primary entity consumers use to find care. Structured specialization linking enables accurate filtering and replaces unreliable free-text.

**Independent Test**: Can be tested by editing a doctor, assigning specializations, and verifying they persist on save. Requires Story 1 (specializations exist).

**Acceptance Scenarios**:

1. **Given** an admin opens the Doctor edit form, **When** they see the Specializations field, **Then** it is a searchable multi-select populated with active specializations.
2. **Given** an admin selects "Cardiology" and "Internal Medicine" for a doctor and saves, **When** they reopen the doctor form, **Then** both specializations are pre-selected.
3. **Given** a doctor has the legacy `expert` field set to "Heart Surgeon", **When** the admin views the doctor edit form, **Then** the legacy expert value is shown as read-only reference text below the specialization selector.
4. **Given** an admin removes all specializations from a doctor and saves, **When** the record is updated, **Then** the pivot records are removed.

---

### User Story 3 - Admin Assigns Specializations to Nurses (Priority: P2)

On the Nurse edit screen, the admin sees a multi-select field (searchable) for Specializations, replacing the old free-text specialization fields. The admin can assign one or more specializations to a nurse. During migration, existing free-text values (`specialization_ar`/`specialization_en`) are parsed and matched to specialization records where possible; unmatched values are preserved as-is in the original columns for manual review.

**Why this priority**: Parallel to Story 2 for nurses. Enables structured filtering on the nurse listing page.

**Independent Test**: Can be tested by editing a nurse, assigning specializations, and verifying they persist. Requires Story 1.

**Acceptance Scenarios**:

1. **Given** an admin opens the Nurse edit form, **When** they see the Specializations field, **Then** it is a searchable multi-select populated with active specializations.
2. **Given** an admin selects "Pediatric Nursing" for a nurse and saves, **When** they reopen the nurse form, **Then** the specialization is pre-selected.
3. **Given** existing nurse records have free-text specialization values, **When** the data migration runs, **Then** matching specialization records are created in the pivot table, and the original text columns are retained for fallback.

---

### User Story 4 - Public User Filters Doctors by Specialization (Priority: P3)

On the public Doctors listing page, a user sees a Specialization filter (multi-select, searchable) alongside the existing clinic, category, and city filters. Selecting one or more specializations narrows the doctor list to those linked via the pivot table. The results update via the existing AJAX flow without a full page reload.

**Why this priority**: This is the primary consumer-facing value. Users can find doctors by medical specialty rather than navigating clinic hierarchies.

**Independent Test**: Can be tested by visiting the public doctors page, selecting a specialization filter, and verifying the results narrow correctly. Requires Stories 1 and 2.

**Acceptance Scenarios**:

1. **Given** a user is on the Doctors listing page, **When** the page loads, **Then** a Specialization filter dropdown appears with all active specializations.
2. **Given** a user selects "Dermatology" from the Specialization filter, **When** results update, **Then** only doctors linked to Dermatology are shown.
3. **Given** a user selects "Dermatology" AND a specific city, **When** results update, **Then** only doctors matching both filters are shown.
4. **Given** no doctors match the selected specialization, **When** results update, **Then** an appropriate "no results" message is displayed.

---

### User Story 5 - Public User Filters Nurses by Specialization (Priority: P3)

On the public Nurses listing page (/available-nurses), the free-text specialization dropdown is replaced with a data-driven multi-select populated from the Specializations table. Filtering uses a database JOIN instead of LIKE queries, improving accuracy and performance.

**Why this priority**: Parallel to Story 4 for nurses. Replaces unreliable free-text filtering.

**Independent Test**: Can be tested by visiting the nurses page, selecting a specialization, and verifying correct filtering. Requires Stories 1 and 3.

**Acceptance Scenarios**:

1. **Given** a user is on the Available Nurses page, **When** the page loads, **Then** a Specialization filter is a multi-select populated from the specializations table (not free-text).
2. **Given** a user selects "ICU Nursing", **When** results update, **Then** only nurses linked to "ICU Nursing" via the pivot are shown.
3. **Given** a nurse has specializations assigned only via the legacy text field (not yet migrated), **When** the user filters, **Then** the nurse still appears if the legacy text matches (backwards compatibility during transition).

---

### User Story 6 - QuickBooking with Optional Specialization Step (Priority: P4)

In the QuickBooking flow, an optional "Specialization" step is inserted as the first step. The flow becomes: Specialization (optional) -> Clinic -> Service -> Doctor -> Slot. If the user selects a specialization, subsequent steps filter to only show clinics/services/doctors related to that specialization. If skipped, the existing flow works exactly as today.

**Why this priority**: Enhances the booking UX but the current flow is already functional. This is an improvement, not a fix.

**Independent Test**: Can be tested by starting QuickBooking, optionally selecting a specialization, and verifying downstream steps filter correctly. Also test skipping the step to ensure backwards compatibility.

**Acceptance Scenarios**:

1. **Given** a user opens QuickBooking, **When** the form loads, **Then** the first step shows a Specialization selector with an option to skip.
2. **Given** a user selects "Cardiology" in the specialization step, **When** they proceed to the Clinic step, **Then** only clinics with doctors linked to Cardiology are listed.
3. **Given** a user skips the specialization step, **When** they proceed, **Then** the flow behaves identically to the current implementation.

---

### User Story 7 - Public API for Specializations (Priority: P4)

The system exposes REST API endpoints for specializations: a paginated, searchable list with counts of doctors and nurses per specialization; and endpoints to retrieve doctors or nurses for a given specialization. The API preserves backwards compatibility by continuing to return legacy `specialization` string fields where they currently exist.

**Why this priority**: Enables mobile apps and third-party integrations to consume the new taxonomy. Lower priority than admin and frontend features.

**Independent Test**: Can be tested by calling the API endpoints and verifying correct JSON responses, pagination, and filtering. Requires Stories 1-3.

**Acceptance Scenarios**:

1. **Given** an authenticated API client sends `GET /api/v1/specializations`, **When** the request is processed, **Then** a paginated list of specializations is returned with `doctors_count` and `nurses_count` per item.
2. **Given** a client sends `GET /api/v1/specializations?search=cardio`, **When** results are returned, **Then** only specializations matching the search term in name_ar or name_en are included.
3. **Given** a client sends `GET /api/v1/specializations/{id}/doctors`, **When** the specialization exists, **Then** a paginated list of doctors linked to that specialization is returned.
4. **Given** a client sends `GET /api/v1/specializations/{id}/nurses`, **When** the specialization exists, **Then** a paginated list of nurses linked to that specialization is returned.
5. **Given** an existing API consumer relies on the `specialization` string field in nurse responses, **When** they call the nurse API, **Then** the legacy string field is still present alongside the new structured specialization array.

---

### Edge Cases

- What happens when a specialization assigned to doctors/nurses is soft-deleted? The assignment persists in the pivot but the specialization is excluded from public filters and new assignments. Existing doctor/nurse profiles show the specialization name with a visual indicator that it is archived.
- What happens when a parent specialization is deleted but children remain? Children are promoted to top-level (parent_id set to null) or the delete is blocked if children exist. **Assumption**: Delete is blocked if active children exist; admin must reassign or delete children first.
- What happens when data migration encounters a nurse with a specialization text that partially matches multiple specialization records? The migration creates no automatic link and flags the record for manual review.
- How does the system handle a specialization with zero doctors/nurses in the API? It is returned with `doctors_count: 0` and `nurses_count: 0`; it is still visible in filters unless status is inactive.
- What happens if a user selects a specialization in QuickBooking but no clinics match? A message indicates no clinics are available for the selected specialization, with an option to go back and choose differently or skip the filter.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a Specialization entity with bilingual name (ar/en), bilingual description (ar/en), icon, status (active/inactive), slug, and optional parent_id for hierarchical classification.
- **FR-002**: System MUST support full CRUD operations for specializations: create, read (list with DataTable), update, and soft-delete, including bulk actions (activate, deactivate, delete) and status toggle.
- **FR-003**: System MUST enforce hierarchical integrity: a specialization cannot be deleted if it has active child specializations.
- **FR-004**: System MUST link Doctors to Specializations via a many-to-many relationship. A doctor can have zero or more specializations.
- **FR-005**: System MUST link Nurses to Specializations via a many-to-many relationship. A nurse can have zero or more specializations.
- **FR-006**: System MUST display a searchable multi-select for specializations on the Doctor edit screen in the admin panel.
- **FR-007**: System MUST display a searchable multi-select for specializations on the Nurse edit screen in the admin panel.
- **FR-008**: System MUST retain the legacy `expert` field on user_profiles as read-only during a deprecation window, displaying it on the doctor edit form for reference.
- **FR-009**: System MUST retain the legacy `specialization_ar`/`specialization_en` columns on the nurses table during the deprecation window, used as fallback display values.
- **FR-010**: System MUST provide a data migration (run separately after admins have manually created specializations) that parses existing nurse `specialization_ar`/`specialization_en` values and creates pivot records for exact matches against specialization names; unmatched values are flagged for manual review.
- **FR-011**: System MUST add a Specialization filter (multi-select, searchable) to the public Doctors listing page, composable with existing clinic, category, and city filters. When multiple specializations are selected, results use OR logic (doctors matching any of the selected specializations are shown).
- **FR-012**: System MUST replace the free-text specialization filter on the public Nurses listing page with a data-driven multi-select populated from the Specializations table. Filtering MUST use a JOIN instead of LIKE. When multiple specializations are selected, results use OR logic.
- **FR-013**: System MUST insert an optional Specialization step as the first step in the QuickBooking flow. If selected, it filters downstream Clinic, Service, and Doctor steps. If skipped, existing behavior is preserved.
- **FR-014**: System MUST expose `GET /api/v1/specializations` (authenticated, `auth:sanctum`) returning a paginated, searchable list with `doctors_count` and `nurses_count` per specialization. Public frontend pages MUST populate specialization dropdowns via Blade server-side rendering (controller injects data into the view), not via API calls.
- **FR-015**: System MUST expose `GET /api/v1/specializations/{id}/doctors` returning a paginated list of doctors for a given specialization.
- **FR-016**: System MUST expose `GET /api/v1/specializations/{id}/nurses` returning a paginated list of nurses for a given specialization.
- **FR-017**: System MUST continue returning the legacy `specialization` string field in nurse API responses during the deprecation window, alongside the new structured specialization data.
- **FR-018**: System MUST add a "Specializations" sidebar entry under the Masters section, permission-guarded for admin and demo_admin roles.
- **FR-019**: System MUST create permission seeds for specializations: `view_specialization`, `add_specialization`, `edit_specialization`, `delete_specialization`, assigned to admin and demo_admin roles.
- **FR-020**: System MUST add the Specialization filter as a query parameter to existing Doctor listing and Nurse listing API endpoints (e.g., `?specialization_ids[]=1&specialization_ids[]=2`). Multiple IDs use OR logic.
- **FR-021**: Specialization entity MUST be independent from SystemServiceCategory and ClinicsCategory. It is a separate domain concept with its own table and management.
- **FR-022**: System MUST display assigned specialization names on doctor and nurse profile cards in public listing pages, providing immediate context without requiring navigation to a detail page.

### Key Entities

- **Specialization**: A reusable medical/service specialty classification. Has bilingual names and descriptions, hierarchical structure (parent/child), icon, slug, and status. Independent from clinic categories and service categories.
- **Doctor-Specialization (pivot)**: Links a doctor to one or more specializations. Contains doctor_id and specialization_id.
- **Nurse-Specialization (pivot)**: Links a nurse to one or more specializations. Contains nurse_id and specialization_id.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admins can create, edit, and manage specializations through the admin panel in under 2 minutes per operation (create, edit, or bulk action).
- **SC-002**: 100% of existing nurse free-text specialization values that exactly match a specialization name are automatically migrated to structured pivot records.
- **SC-003**: Users can filter doctors by specialization on the public listing page and see results update within 2 seconds.
- **SC-004**: Users can filter nurses by specialization on the public listing page using data-driven selects instead of free-text, with results updating within 2 seconds.
- **SC-005**: The QuickBooking flow supports an optional specialization pre-filter step without breaking any existing booking paths (zero regression in booking completion rate).
- **SC-006**: API consumers can retrieve specializations with doctor/nurse counts, and filter doctors/nurses by specialization, with responses returned within standard API performance expectations.
- **SC-007**: All existing API consumers that rely on the legacy `specialization` string field in nurse responses continue to function without modification during the deprecation window.
- **SC-008**: The Specializations admin page supports at least 500 specialization records with responsive DataTable performance (search, sort, paginate without noticeable delay).

## Clarifications

### Session 2026-04-21

- Q: How are initial specializations created before the nurse data migration runs? → A: Admins manually create specializations first; the data migration is a separate step run afterward.
- Q: Should the specializations list API endpoint be public or require authentication? → A: All API endpoints require `auth:sanctum`; public frontend pages populate specialization dropdowns via Blade server-side rendering (controller passes data to view).
- Q: When multiple specializations are selected in a filter, should results use AND or OR logic? → A: OR (union) — show results matching any of the selected specializations.
- Q: Should assigned specializations be displayed on doctor/nurse cards in public listings? → A: Yes, display specialization names on the listing cards (not only on detail pages).

## Assumptions

- The Specialization entity is a medical/service specialty (e.g., Cardiology, Pediatrics, ICU Nursing) and is distinct from clinic categories (e.g., Dental Center, Multi-Specialty Hospital) and system service categories.
- The hierarchy (parent_id) supports one level of nesting (parent -> children). Deeper nesting is not required initially.
- The data migration for nurse specializations is a two-step process: (1) admins manually create specialization records, (2) migration is run separately to match nurse free-text values using exact string matching (case-insensitive) against specialization `name_ar` and `name_en`. Fuzzy matching is out of scope; unmatched records are flagged.
- The `expert` field on user_profiles (doctors) is not automatically migrated into specialization pivots because it is truly free-text with no consistent format. It is displayed as read-only for manual reference.
- Permission seeds follow the existing spatie/laravel-permission pattern used by other modules.
- The Specialization icon field stores a reference (CSS class or file path) consistent with how icons are handled elsewhere in the system (Phosphor Icons or media library).
- The Postman collection update adds new endpoints without modifying existing endpoint definitions.
- Multi-vendor filtering: specializations are global (not vendor-specific). All vendors share the same specialization taxonomy.

## Scope Boundaries

### In Scope
- New `specializations` table and Specialization model
- Pivot tables `doctor_specialization` and `nurse_specialization`
- Admin CRUD for specializations with DataTable
- Multi-select on Doctor and Nurse edit screens
- Public frontend filters for doctors and nurses by specialization
- QuickBooking optional specialization step
- REST API endpoints for specializations
- Permission seeder
- Data migration for nurse free-text values
- Postman collection update
- Backwards-compatible API responses

### Out of Scope
- Migrating doctor `expert` free-text into structured specialization pivots (manual process)
- Removing or altering the SystemServiceCategory or ClinicsCategory models
- Deep hierarchy (more than 2 levels) for specializations
- Specialization-based pricing or commission rules
- Mobile app UI changes (API only)
- Removing the legacy `specialization_ar`/`specialization_en` columns from the nurses table (future deprecation)
- Removing the legacy `expert` field from user_profiles (future deprecation)
