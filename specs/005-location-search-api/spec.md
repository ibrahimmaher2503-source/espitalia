# Feature Specification: Location-Based Healthcare Search API

**Feature Branch**: `005-location-search-api`
**Created**: 2026-03-31
**Status**: Draft
**Input**: Healthcare platform (Vizita-like) with governorate/city foundation, multilingual Arabic+English, and location-based search across doctors, clinics, nurses, home healthcare, labs, and radiology centers.

## Clarifications

### Session 2026-03-31

- Q: What is the default sort order for search results across all endpoints? → A: Newest first (most recently created records appear at top).
- Q: Is `governorate_id` required or optional on the cities endpoint? → A: Required — the system returns an error if `governorate_id` is missing (matches cascading dropdown UX pattern).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Browse Governorates and Cities (Priority: P1)

A mobile app user opens the platform and needs to select their location (governorate and city) to find nearby healthcare services. The app displays a list of all Egyptian governorates in the user's preferred language (Arabic or English), and after selecting a governorate, shows the cities within it.

**Why this priority**: Location selection is the foundational interaction — every other search feature depends on governorates and cities being available. Without this, no location-based filtering is possible.

**Independent Test**: Can be fully tested by requesting the governorate list and then requesting cities for a specific governorate. Delivers value by enabling location awareness across the entire platform.

**Acceptance Scenarios**:

1. **Given** the user's device sends `Accept-Language: ar`, **When** they request the list of governorates, **Then** they receive all 27 Egyptian governorates with names displayed in Arabic.
2. **Given** the user's device sends `Accept-Language: en`, **When** they request the list of governorates, **Then** they receive all 27 Egyptian governorates with names displayed in English.
3. **Given** a valid governorate is selected, **When** the user requests cities for that governorate, **Then** they receive only the cities belonging to that specific governorate, in the appropriate language.
4. **Given** an invalid or non-existent governorate ID is provided, **When** the user requests cities, **Then** the system returns an appropriate error message.
5. **Given** no `governorate_id` is provided, **When** the user requests cities, **Then** the system returns a validation error indicating `governorate_id` is required.
6. **Given** no `Accept-Language` header is sent, **When** the user requests governorates, **Then** the system defaults to English.

---

### User Story 2 - Search for Doctors by Location (Priority: P1)

A patient wants to find a doctor in their area. They select a governorate, optionally a city, and can further filter by specialty, name, gender, and price range. Results are paginated and display the doctor's location information.

**Why this priority**: Doctor search is the core feature of any Vizita-like platform — it's the primary reason users download the app.

**Independent Test**: Can be fully tested by searching for doctors with various filter combinations and verifying results are correctly filtered and paginated.

**Acceptance Scenarios**:

1. **Given** doctors exist in Cairo governorate, **When** a user searches with `governorate_id=1`, **Then** only doctors in Cairo are returned, paginated at 15 per page.
2. **Given** a user provides `governorate_id`, `city_id`, and `specialty_id`, **When** they search, **Then** only doctors matching all three filters appear.
3. **Given** a user searches with `name=أحمد` (Arabic partial name), **When** results are returned, **Then** all doctors whose name contains "أحمد" appear.
4. **Given** a user sets `min_price=100` and `max_price=500`, **When** they search, **Then** only doctors within that consultation fee range are returned.
5. **Given** no filters are applied, **When** a user searches, **Then** all doctors are returned sorted by newest first, paginated at 15 per page.
6. **Given** a user searches with filters that match no doctors, **When** results are returned, **Then** the system returns an empty items list with pagination metadata showing zero results.

---

### User Story 3 - Search for Clinics by Location (Priority: P1)

A patient wants to find a clinic near them, filtering by governorate, city, specialty, or clinic name.

**Why this priority**: Clinics are the physical locations where patients receive care — tightly coupled with doctor search as a primary discovery path.

**Independent Test**: Can be fully tested by searching for clinics with location and specialty filters, verifying filtered and paginated results.

**Acceptance Scenarios**:

1. **Given** clinics exist in Alexandria, **When** a user searches with `governorate_id=2`, **Then** only clinics in Alexandria are returned.
2. **Given** a user provides a partial clinic `name`, **When** they search, **Then** clinics with matching names appear regardless of language.
3. **Given** filters that match no clinics, **When** a search is performed, **Then** an empty result set is returned with correct pagination metadata.

---

### User Story 4 - Search for Nurses by Location (Priority: P2)

A patient or family member needs to find an available nurse in their area, filtering by location, specialty, gender, and availability.

**Why this priority**: Nurse search is a secondary but important service — it supports home care needs which are growing in demand.

**Independent Test**: Can be fully tested by searching for nurses with location and availability filters, verifying correct results.

**Acceptance Scenarios**:

1. **Given** nurses exist in a governorate, **When** a user searches by that governorate, **Then** only nurses in that governorate are returned.
2. **Given** a user filters by `availability=true`, **When** results are returned, **Then** only currently available nurses appear.
3. **Given** a user filters by `gender=female`, **When** results are returned, **Then** only female nurses appear.

---

### User Story 5 - Search for Home Healthcare by Location (Priority: P2)

A patient needs in-home medical services (physiotherapy, elderly care, post-surgery support, etc.) and wants to find providers in their area by service type.

**Why this priority**: Home healthcare is a distinct service vertical — important for elderly and post-operative patients but secondary to clinic-based care.

**Independent Test**: Can be fully tested by searching with governorate, city, and service type filters.

**Acceptance Scenarios**:

1. **Given** home healthcare providers exist in a city, **When** a user searches by that city, **Then** only providers in that city are returned.
2. **Given** a user filters by `service_type=physiotherapy`, **When** results are returned, **Then** only physiotherapy providers appear.

---

### User Story 6 - Search for Labs by Location (Priority: P2)

A patient needs to find a medical laboratory nearby, optionally searching by test name to find labs that offer a specific test.

**Why this priority**: Lab test booking is a common secondary action after doctor consultation.

**Independent Test**: Can be fully tested by searching labs with location and test name filters.

**Acceptance Scenarios**:

1. **Given** labs exist in a governorate, **When** a user searches by that governorate, **Then** only labs in that governorate are returned.
2. **Given** a user searches with `test_name=CBC`, **When** results are returned, **Then** only labs offering tests matching "CBC" appear.

---

### User Story 7 - Search for Radiology Centers by Location (Priority: P2)

A patient needs imaging/radiology services (X-ray, MRI, CT scan, etc.) and wants to find radiology centers in their area by scan type.

**Why this priority**: Radiology is a specialized service — important but less frequently accessed than doctors or labs.

**Independent Test**: Can be fully tested by searching radiology centers with location and scan type filters.

**Acceptance Scenarios**:

1. **Given** radiology centers exist in a city, **When** a user searches by that city, **Then** only centers in that city are returned.
2. **Given** a user filters by `scan_type=MRI`, **When** results are returned, **Then** only centers offering MRI scans appear.

---

### Edge Cases

- What happens when a user sends an unsupported language in `Accept-Language` (e.g., `fr`)? System defaults to English.
- What happens when `city_id` is provided without `governorate_id`? The search still works — city alone is sufficient for filtering.
- What happens when `city_id` does not belong to the provided `governorate_id`? System returns empty results (no validation error — the mismatch simply yields no matches).
- What happens when pagination parameters exceed available pages? System returns an empty items list with correct pagination metadata (e.g., current_page: 10, total: 5).
- What happens when search text contains special characters or SQL-sensitive characters? System sanitizes input and performs safe partial matching.
- What happens when the governorates or cities database has no seeded data? The API returns empty lists with the standard response format.
- What happens when the cities endpoint is called without `governorate_id`? System returns a validation error — the parameter is required.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a complete list of all 27 Egyptian governorates with bilingual names (Arabic and English).
- **FR-002**: System MUST provide a list of cities belonging to a specific governorate, with bilingual names. The `governorate_id` parameter is required — the system MUST return a validation error if it is omitted.
- **FR-003**: System MUST determine the user's preferred language from the `Accept-Language` request header and return all localizable text (governorate names, city names, messages) in that language.
- **FR-004**: System MUST default to English when no `Accept-Language` header is provided or when an unsupported language is requested.
- **FR-005**: System MUST support searching doctors by governorate, city, specialty, name (partial match), gender, and price range, with results paginated at 15 per page.
- **FR-006**: System MUST support searching clinics by governorate, city, name (partial match), and specialty, with results paginated at 15 per page.
- **FR-007**: System MUST support searching nurses by governorate, city, specialty, gender, and availability, with results paginated at 15 per page.
- **FR-008**: System MUST support searching home healthcare providers by governorate, city, and service type, with results paginated at 15 per page.
- **FR-009**: System MUST support searching labs by governorate, city, and test name (partial match), with results paginated at 15 per page.
- **FR-010**: System MUST support searching radiology centers by governorate, city, and scan type, with results paginated at 15 per page.
- **FR-011**: All search endpoints MUST return results in a unified response format containing status, message, items array, and pagination metadata (current_page, last_page, per_page, total).
- **FR-012**: All search filters MUST be optional — omitting a filter means no restriction on that dimension.
- **FR-012a**: All search endpoints MUST return results sorted by newest first (most recently created) as the default order.
- **FR-013**: Each search result item MUST include nested governorate and city objects with their id and localized name.
- **FR-014**: System MUST validate that provided `governorate_id` and `city_id` values reference existing records before executing the search.
- **FR-015**: System MUST return localized error messages when validation fails (Arabic for `ar`, English for `en`).
- **FR-016**: System MUST seed all 27 Egyptian governorates and their major cities with both Arabic and English names.

### Key Entities

- **Governorate**: Represents an Egyptian administrative division. Has a bilingual name (Arabic + English). Contains many cities. Is referenced by doctors, clinics, nurses, home healthcare providers, labs, and radiology centers for location.
- **City**: A subdivision within a governorate. Has a bilingual name. Belongs to one governorate. Is referenced by the same healthcare entities as governorate.
- **Doctor**: A medical professional. Has a location (governorate + city), specialty, gender, and consultation price. Searchable by multiple filters.
- **Clinic**: A medical facility. Has a location (governorate + city), name, and specialty. Searchable by location and attributes.
- **Nurse**: A nursing professional. Has a location, specialty, gender, and availability status. Searchable by location and attributes.
- **Home Healthcare Provider**: Provides in-home medical services. Has a location and service type. Searchable by location and service type.
- **Lab**: A medical laboratory. Has a location and offers specific tests. Searchable by location and test name.
- **Radiology Center**: An imaging/radiology facility. Has a location and offers specific scan types. Searchable by location and scan type.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can retrieve the complete list of 27 governorates in under 1 second from anywhere in Egypt.
- **SC-002**: Users can retrieve cities for any governorate in under 1 second.
- **SC-003**: All search endpoints return paginated results in under 2 seconds for the first page of results.
- **SC-004**: 100% of search results respect all applied filters — no false positives appear in filtered results.
- **SC-005**: All API responses display names in the correct language matching the user's `Accept-Language` header.
- **SC-006**: Users can switch between Arabic and English seamlessly within the same session without data inconsistency.
- **SC-007**: All search endpoints gracefully handle empty result sets, returning a valid response structure with zero items.
- **SC-008**: Platform supports at least 500 concurrent search requests without degradation in response time.

## Assumptions

- The platform targets Egypt exclusively — all 27 governorates and their major cities are pre-seeded and not user-editable.
- Only two languages are supported: Arabic (`ar`) and English (`en`). No other locales are planned for this phase.
- The `Accept-Language` header is parsed simply — the system checks for `ar` (case-insensitive); anything else defaults to `en`.
- Existing doctor, clinic, nurse, home healthcare, lab, and radiology center tables already exist in the database. This feature adds `governorate_id` and `city_id` foreign key columns to each.
- All search endpoints are public (no authentication required) — they serve the app's discovery/browsing experience.
- Pagination defaults to 15 items per page and page 1 when not specified by the client.
- Default sort order for all search results is newest first (by creation date, descending).
- Partial name/text matching uses case-insensitive LIKE queries, which work correctly with both Arabic and English text.
- The "availability" filter for nurses is a boolean field indicating current availability.
- The "service_type" for home healthcare and "scan_type" for radiology are string-based filters matching against predefined values on those entities.
- Price filtering for doctors applies to a consultation fee or similar numeric field already present on the doctor entity.
