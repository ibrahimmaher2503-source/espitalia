# Feature Specification: Nurse Admin Bilingual

**Feature Branch**: `012-nurse-admin-bilingual`
**Created**: 2026-04-05
**Status**: Draft
**Input**: User description: "for Nurse in admin ui and endpoints also in db i need all details in entities be in both lan ar and en and update api docs later"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin Manages Nurses with Bilingual Details (Priority: P1)

An admin creates or edits a nurse profile. All text-based fields that patients or staff see are entered in both Arabic and English: specialization, about (bio), and service area. The admin form shows paired inputs for each language. The system stores both versions and serves the correct one based on the current locale.

**Why this priority**: Bilingual nurse data is foundational — patients browsing nurses in Arabic must see Arabic details, while English-speaking patients see English. Without this, the app delivers a broken experience for one language.

**Independent Test**: Admin creates a nurse with Arabic and English specialization/about/service_area. Switch locale to Arabic — Arabic fields appear. Switch to English — English fields appear. API responses reflect the active locale.

**Acceptance Scenarios**:

1. **Given** the admin is on the nurse create form, **When** they fill in `specialization_ar`, `specialization_en`, `about_ar`, `about_en`, `service_area_ar`, `service_area_en` and submit, **Then** the nurse record is saved with all six text fields.
2. **Given** a nurse exists with both AR and EN data, **When** the app locale is Arabic, **Then** the nurse's `specialization`, `about`, and `service_area` display the Arabic values.
3. **Given** a nurse exists with both AR and EN data, **When** the app locale is English, **Then** the nurse's `specialization`, `about`, and `service_area` display the English values.
4. **Given** the admin edits an existing nurse, **When** the edit form loads, **Then** both Arabic and English fields are pre-populated with their stored values.

---

### User Story 2 - Nurse Request Service Description in Both Languages (Priority: P2)

When an admin or patient creates a nurse request, the service description can be provided in both Arabic and English. Admin views and API responses serve the locale-appropriate version.

**Why this priority**: Service descriptions appear in patient-facing screens and admin dashboards. Bilingual support ensures readability for all users.

**Independent Test**: Create a nurse request with `service_description_ar` and `service_description_en`. View in admin panel — both shown. API response returns locale-appropriate value.

**Acceptance Scenarios**:

1. **Given** the admin is creating a nurse request, **When** they enter both `service_description_ar` and `service_description_en`, **Then** both are persisted.
2. **Given** a nurse request exists with bilingual descriptions, **When** viewed via API with `Accept-Language: ar`, **Then** `service_description` returns the Arabic text.
3. **Given** a nurse request exists, **When** viewed in admin detail page, **Then** both Arabic and English descriptions are visible.

---

### User Story 3 - API Endpoints Return Bilingual Nurse Data (Priority: P2)

All existing API endpoints that return nurse or nurse request data are updated to return locale-aware text fields. The public nurse search, authenticated nurse list, and nurse request endpoints all respect the `Accept-Language` header.

**Why this priority**: Mobile and web frontends consume API data — they need the correct language without client-side mapping.

**Independent Test**: Call `GET /api/nurses/search` with `Accept-Language: ar` — nurse specialization/about return Arabic. Call with `en` — return English.

**Acceptance Scenarios**:

1. **Given** a nurse with bilingual data exists, **When** the public search API is called with `Accept-Language: ar`, **Then** `specialization`, `about`, and `service_area` return Arabic values.
2. **Given** a nurse request with bilingual description exists, **When** the patient's request list API is called with `Accept-Language: en`, **Then** `service_description` returns the English value.

---

### User Story 4 - Governorate & City Location Selection for Nurses (Priority: P2)

The admin nurse create/edit form includes cascading dropdowns for Governorate and City. When the admin selects a governorate, the city dropdown filters to show only cities within that governorate. Both dropdown labels display the locale-appropriate name (Arabic or English). The nurse's assigned location is displayed in the admin index DataTable and in API responses.

**Why this priority**: The `nurses` table already has `governorate_id` and `city_id` columns, but the admin form has no dropdowns to populate them. Without this, nurse location data cannot be set, breaking location-based nurse search and assignment.

**Independent Test**: Admin creates a nurse, selects governorate "Kuwait City" → city dropdown shows its cities. Save. Nurse record has correct governorate_id and city_id. API returns the location data bilingually.

**Acceptance Scenarios**:

1. **Given** the admin opens the nurse create form, **When** the form loads, **Then** a Governorate dropdown is present populated with all active governorates (displaying locale-appropriate name).
2. **Given** the admin selects a governorate, **When** the selection changes, **Then** the City dropdown updates to show only cities belonging to that governorate.
3. **Given** the admin is editing an existing nurse with a governorate and city, **When** the form loads, **Then** both dropdowns are pre-selected with the nurse's current location.
4. **Given** a nurse has a governorate and city assigned, **When** viewed in the admin DataTable, **Then** the location column shows the locale-appropriate governorate and city names.
5. **Given** a nurse has location data, **When** the API returns nurse data, **Then** the response includes nested `governorate: {id, name}` and `city: {id, name}` with locale-aware names.

---

### User Story 5 - Admin UI Shows Both Languages Side by Side (Priority: P3)

The admin nurse index (DataTable) displays both language versions or the current admin locale version. The nurse detail/edit forms show paired language inputs (Arabic field next to English field) consistent with other bilingual entities in the system (e.g., Lab, PharmacyCategory).

**Why this priority**: Admin UX consistency — other modules already use side-by-side bilingual inputs.

**Independent Test**: Open nurse index in admin — specialization column shows locale-appropriate value. Open edit form — both AR and EN fields visible and editable.

**Acceptance Scenarios**:

1. **Given** the admin views the nurses DataTable, **When** the admin locale is Arabic, **Then** the specialization column shows Arabic values.
2. **Given** the admin opens the nurse edit form, **When** the form loads, **Then** paired AR/EN inputs are displayed for specialization, about, and service area.

---

### Edge Cases

- What happens when only one language is provided (e.g., Arabic but not English)? The system stores what is given; the empty language field falls back to the other language's value.
- What happens to existing nurse records that have single-language data? A data migration copies existing single-language values to both `_ar` and `_en` columns so no data is lost.
- What if the specialization is selected from a predefined list? The predefined specialization options are translated via the translation files (already done). Free-text specialization overrides are stored bilingually.
- How does the nurse search handle bilingual data? Search queries match against both `_ar` and `_en` columns.
- What happens when a governorate has no cities? The city dropdown shows empty with a "No cities available" message.
- What if the admin changes the governorate after a city was selected? The city dropdown resets and the previous city selection is cleared.
- What if a governorate or city is deleted after being assigned to a nurse? The foreign key allows NULL (SET NULL), so the nurse remains but shows no location.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST store nurse text fields (`specialization`, `about`, `service_area`) in both Arabic and English as separate database columns (`_ar` and `_en` suffixes).
- **FR-002**: System MUST store nurse request `service_description` in both Arabic and English as separate database columns.
- **FR-003**: System MUST provide locale-aware accessors on the Nurse model that return the appropriate language based on the application locale (`app()->getLocale()`).
- **FR-004**: System MUST provide locale-aware accessors on the NurseRequest model for `service_description`.
- **FR-005**: The admin nurse create/edit form MUST display paired inputs for each bilingual field (Arabic input + English input side by side).
- **FR-006**: The admin nurse request create form MUST display paired inputs for `service_description` in both languages.
- **FR-007**: The admin nurse index DataTable MUST display the locale-appropriate value for specialization.
- **FR-008**: All API endpoints returning nurse data MUST return locale-aware text fields based on the request's `Accept-Language` header.
- **FR-009**: All API endpoints returning nurse request data MUST return locale-aware `service_description`.
- **FR-010**: The nurse search endpoint MUST search across both `_ar` and `_en` columns for text matching.
- **FR-011**: Existing single-language data in the database MUST be migrated to both language columns to preserve backward compatibility.
- **FR-012**: The admin form validation MUST require at least one language for each bilingual field (both are not mandatory, but at least one must be non-empty).
- **FR-013**: The admin nurse create/edit form MUST include a Governorate dropdown populated from the existing governorates table, displaying locale-aware names.
- **FR-014**: The admin nurse create/edit form MUST include a City dropdown that dynamically filters based on the selected governorate (cascading dropdown).
- **FR-015**: All API endpoints returning nurse data MUST include nested governorate and city objects with locale-aware names (`{id, name}`).
- **FR-016**: The admin nurse index DataTable MUST display the nurse's governorate and city in a location column.

### Key Entities

- **Nurse**: Profile of a healthcare nurse. Key bilingual attributes: specialization (AR/EN), about/bio (AR/EN), service area description (AR/EN). Related to a User record, a Governorate, and a City.
- **NurseRequest**: A patient's request for a home nurse visit. Key bilingual attribute: service description (AR/EN). Related to a Patient (User) and optionally an assigned Nurse.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All nurse text fields are available in both Arabic and English across admin UI and API responses within 1 release cycle.
- **SC-002**: Existing nurse records retain their data after migration — zero data loss during the bilingual column transition.
- **SC-003**: Admin can create a nurse with bilingual details in under 3 minutes (same time as current single-language flow, accounting for additional fields).
- **SC-004**: API responses return the correct language in 100% of cases when `Accept-Language` header is set.
- **SC-005**: Nurse search returns relevant results regardless of which language the user searches in.
- **SC-006**: Admin UI bilingual inputs follow the same visual pattern used in other bilingual modules (Lab, PharmacyCategory) for consistency.

## Assumptions

- The bilingual pattern follows the established convention in this codebase: `field_ar`/`field_en` columns with a `getFieldAttribute()` accessor that checks `app()->getLocale()` (same as `Lab::getNameAttribute()`).
- Predefined specialization options are already translated via `lang/en/request_nurse.php` and `lang/ar/request_nurse.php` translation files. This feature adds bilingual support for the stored database value (which may be a free-text override or a stored translated label).
- The admin UI language for form labels is controlled by the app locale; the bilingual inputs always show both languages regardless of admin locale.
- API documentation updated in `contracts/api-endpoints.md` (completed 2026-04-05).
- No new permissions are needed — existing `view_nurses`, `add_nurses`, `edit_nurses`, `delete_nurses` permissions cover the bilingual admin operations.

## Scope Boundaries

**In scope**:
- Database schema changes (new `_ar`/`_en` columns for Nurse and NurseRequest)
- Data migration for existing records
- Model accessor updates for bilingual support
- Admin Blade view updates (create/edit forms, index DataTable)
- Governorate/City cascading dropdowns in nurse admin form
- Location column in nurse admin DataTable
- API transformer/resource updates for locale-aware responses (including governorate/city nesting)
- Search service update for bilingual matching
- Form request validation updates

**Out of scope**:
- New admin pages or features beyond bilingual support
- Patient-facing frontend changes (handled by API consumers)
- ~~API documentation updates~~ (completed)
- New nurse management features (CRUD already exists)
- Notification content translation (handled separately)
