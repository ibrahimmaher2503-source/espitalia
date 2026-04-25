# Feature Specification: Bilingual Doctor Details (Arabic/English)

**Feature Branch**: `007-doctor-bilingual-details`
**Created**: 2026-04-02
**Status**: Draft
**Input**: User description: "I need to edit all doctor entities for DB and UI to can add full doctor details in AR and EN"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin Creates a Doctor with Bilingual Details (Priority: P1)

An admin creates a new doctor profile and enters all text fields in both Arabic and English. The system stores both language versions and displays the correct one based on the current locale.

**Why this priority**: This is the core functionality — without bilingual input, no other stories work. It establishes the data foundation for all downstream display.

**Independent Test**: Can be fully tested by creating a doctor via the admin form with AR/EN fields, then switching locales to verify correct language displays.

**Acceptance Scenarios**:

1. **Given** an admin is on the doctor creation form, **When** they see text fields (first name, last name, experience, description, about/bio, expertise), **Then** each field has two labeled inputs — one for Arabic and one for English.
2. **Given** an admin fills in both Arabic and English values for all fields and submits, **Then** the system saves both language versions and the doctor appears in the list.
3. **Given** an admin fills in only the English values and leaves Arabic blank, **Then** the system saves successfully, and Arabic display falls back to the English value.
4. **Given** an admin fills in only the Arabic values and leaves English blank, **Then** the system saves successfully, and English display falls back to the Arabic value.

---

### User Story 2 - Admin Edits Existing Doctor's Bilingual Fields (Priority: P1)

An admin opens an existing doctor for editing. Pre-existing single-language data appears in both language fields (as fallback), and the admin can now enter proper translations for each language.

**Why this priority**: Equally critical as creation — existing doctors need a migration path to bilingual data without data loss.

**Independent Test**: Can be tested by editing a doctor that was created before this feature and verifying pre-existing data is preserved and editable in both languages.

**Acceptance Scenarios**:

1. **Given** a doctor was created before bilingual support, **When** an admin opens the edit form, **Then** the existing single-language value appears in both the Arabic and English fields as a starting point.
2. **Given** an admin updates the Arabic version of a field, **When** they save, **Then** only the Arabic version changes; the English version remains as-is.
3. **Given** an admin clears one language field and saves, **Then** the system accepts it and the cleared language falls back to the other language's value when displayed.

---

### User Story 3 - Public-Facing Pages Display Doctor Info in Correct Language (Priority: P2)

Visitors browsing doctor listings, doctor detail pages, and search results see doctor information in the language matching the site's current locale (Arabic or English).

**Why this priority**: High value for end-users but depends on P1 stories being complete. Directly impacts the user experience of the public-facing site.

**Independent Test**: Can be tested by browsing the doctor listing page and detail pages while toggling between Arabic and English locale.

**Acceptance Scenarios**:

1. **Given** a doctor has both Arabic and English data stored, **When** a visitor views the doctor listing in Arabic, **Then** the doctor's name, experience, description, expertise, and bio display in Arabic.
2. **Given** the same doctor, **When** a visitor switches to English, **Then** all fields display in English.
3. **Given** a doctor has only English data, **When** a visitor views in Arabic, **Then** the English data displays as fallback (no blank fields shown).

---

### User Story 4 - API Returns Doctor Data in Requested Language (Priority: P2)

API consumers (mobile app, search endpoints) receive doctor data in the language specified by the `Accept-Language` header.

**Why this priority**: Essential for mobile app consumers and the existing location-based search API, but depends on database changes from P1.

**Independent Test**: Can be tested by calling doctor API endpoints with `Accept-Language: ar` and `Accept-Language: en` headers and verifying the response language.

**Acceptance Scenarios**:

1. **Given** a doctor has bilingual data, **When** the API is called with `Accept-Language: ar`, **Then** all text fields return Arabic values.
2. **Given** a doctor has bilingual data, **When** the API is called with `Accept-Language: en` (or no header), **Then** all text fields return English values.
3. **Given** a doctor has only one language populated, **When** the API is called in the missing language, **Then** the available language's value is returned as fallback.

---

### User Story 5 - Doctor List DataTable Displays in Active Locale (Priority: P3)

The admin-side doctor DataTable list displays doctor names and details in the admin's current locale setting.

**Why this priority**: Admin UX improvement, but lower priority since admins can typically read both languages.

**Independent Test**: Can be tested by switching admin locale and refreshing the doctor list page.

**Acceptance Scenarios**:

1. **Given** the admin panel is set to Arabic, **When** the doctor list loads, **Then** doctor names and descriptions appear in Arabic.
2. **Given** the admin panel is set to English, **When** the doctor list loads, **Then** doctor names and descriptions appear in English.
3. **Given** a search/filter is applied on the doctor list, **When** the admin types a name in Arabic, **Then** it matches against the Arabic name fields.

---

### Edge Cases

- What happens when a doctor record is migrated from the old single-language format? The existing value is copied into both language slots to prevent data loss.
- What happens when both language fields are empty for a text field? The system displays an empty string — the field is not required unless marked so in validation.
- How does search work across languages? Name search checks both Arabic and English fields regardless of current locale, so a search for an Arabic name works even in English locale.
- What happens to existing API consumers that send no `Accept-Language` header? They receive English (the default), preserving backward compatibility.
- What about RTL text entered in an English field by mistake? No automatic correction — the system stores whatever the admin enters. Admins are responsible for entering correct language content.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST store doctor text fields (first name, last name, experience, description, expertise/specialization, about/bio) in both Arabic and English.
- **FR-002**: System MUST display doctor text fields in the language matching the current application locale, with fallback to the other language if the requested language's value is empty.
- **FR-003**: The admin doctor creation form MUST provide separate input fields for Arabic and English for each translatable text field.
- **FR-004**: The admin doctor edit form MUST load existing bilingual values into the corresponding language fields.
- **FR-005**: For doctors created before bilingual support, the system MUST migrate existing single-language data so it appears in both language fields (no data loss).
- **FR-006**: The public-facing doctor listing, detail pages, and search results MUST display content in the active locale.
- **FR-007**: All doctor-related API endpoints MUST return text fields in the language specified by the `Accept-Language` header (defaulting to English).
- **FR-008**: Doctor name search (admin DataTable, public search, API) MUST match against both Arabic and English name fields regardless of the current locale.
- **FR-009**: Validation MUST require at least one language to be filled for required fields (first name, last name). Both languages are not independently required.
- **FR-010**: The system MUST maintain backward compatibility — existing API consumers that do not send `Accept-Language` headers MUST continue to receive English responses.

### Key Entities

- **Doctor**: Core entity representing a medical professional. Linked to a User record. Contains professional fields (experience, description, specialization) that need bilingual support. Stored across two tables: `users` (personal info) and `doctors` (professional info).
- **User**: The base user account for a doctor. Contains name fields (first_name, last_name) and address info. These personal fields need bilingual support.
- **User Profile**: Extended profile data for a user. Contains `expert` (specialization label) and `about_self` (biography) fields that need bilingual support.

### Fields Requiring Bilingual Support

| Field | Current Location | Current Type | Description |
|-------|-----------------|--------------|-------------|
| First Name | `users.first_name` | string | Doctor's given name |
| Last Name | `users.last_name` | string | Doctor's family name |
| Experience | `doctors.experience` | string | Years/details of experience |
| Description | `doctors.description` | string | Professional description |
| Expertise | `user_profiles.expert` | string | Specialization label |
| About / Bio | `user_profiles.about_self` | string | Biography paragraph |

## Assumptions

- The existing bilingual pattern used by Governorate/GovernorateCity models (dual `_ar`/`_en` columns with a locale-aware `name` accessor) is the preferred approach for this project. This same pattern will be applied to doctor fields.
- The admin panel supports locale switching (Arabic/English), and the current locale is accessible via `app()->getLocale()`.
- Only Arabic and English are required. No need for a general-purpose multi-language translation system.
- The `Signature` field on the doctor table is an image/binary and does NOT need bilingual support.
- Address fields on the user table (address, city, state, country, pincode) are NOT in scope for this feature — they are structured location data, not free-text fields.
- Social media links (facebook, instagram, twitter, dribbble) on user_profiles do NOT need bilingual support — they are URLs.
- The doctor creation and editing flows are handled through the admin backend (Blade forms), not through the public-facing frontend.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admins can create a doctor with all text fields filled in both Arabic and English, and both versions persist correctly — verified by re-opening the edit form and seeing both values.
- **SC-002**: 100% of existing doctor records retain their data after migration — no text field loses its existing value.
- **SC-003**: Public-facing doctor pages display the correct language within 1 page load (no additional clicks/reloads needed) when the locale is set.
- **SC-004**: API responses return the correct language based on `Accept-Language` header for all doctor-related endpoints, matching the requested locale for all 6 translatable fields.
- **SC-005**: Doctor name search returns correct results when searching in either Arabic or English, regardless of the active locale.
- **SC-006**: The admin form clearly distinguishes Arabic and English inputs (labeled and visually grouped) so admins can fill them without confusion.
