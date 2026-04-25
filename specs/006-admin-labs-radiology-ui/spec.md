# Feature Specification: Admin UI for Labs & Radiology Centers Management

**Feature Branch**: `006-admin-labs-radiology-ui`
**Created**: 2026-04-02
**Status**: Draft
**Input**: User description: "Add UI for admin to control labs and radiology centers. Make sure all details can be added with both AR and EN and both languages send to API. Also all UI words translated into both languages."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin Manages Labs (Priority: P1)

As an admin, I need to create, view, edit, activate/deactivate, and delete labs so that the system has an accurate directory of available laboratory facilities for patients to search.

**Why this priority**: Labs are a core entity in the RadiologyLab module. Without admin management, lab data can only be manipulated through the database directly, making the feature unusable for non-technical administrators.

**Independent Test**: Can be fully tested by navigating to the Labs admin page, creating a new lab with Arabic and English names plus location, verifying it appears in the list, editing it, toggling its status, and deleting it. Delivers a complete CRUD workflow for labs.

**Acceptance Scenarios**:

1. **Given** an admin is logged in, **When** they navigate to the Labs section in the sidebar, **Then** they see a paginated, searchable DataTable listing all labs with columns for name (current locale), governorate, city, status, and actions.
2. **Given** an admin clicks "Add Lab", **When** they fill in the Arabic name, English name, select a governorate, select a city (filtered by selected governorate), and submit, **Then** a new lab record is created with both language fields stored, and a success message is shown.
3. **Given** an admin clicks "Edit" on an existing lab, **When** they update any field and submit, **Then** the lab is updated and the changes are reflected in the list.
4. **Given** an admin toggles a lab's status, **When** the status is switched off, **Then** the lab no longer appears in public search results but remains visible in the admin list with an "Inactive" badge.
5. **Given** an admin deletes a lab, **When** they confirm deletion, **Then** the lab is soft-deleted and removed from the list (but can be restored via the trashed filter).
6. **Given** the admin switches the interface language to Arabic, **When** they view the labs list and forms, **Then** all UI labels, buttons, messages, and placeholders are displayed in Arabic with RTL layout.

---

### User Story 2 - Admin Manages Radiology Centers (Priority: P1)

As an admin, I need to create, view, edit, activate/deactivate, and delete radiology centers so that patients can find available imaging facilities.

**Why this priority**: Radiology centers are equally critical as labs. They have an additional "scan type" field (e.g., MRI, CT, X-Ray) that distinguishes them and must be managed.

**Independent Test**: Can be fully tested by navigating to the Radiology Centers admin page, creating a center with both-language names and a scan type, verifying it in the list, editing, toggling status, and deleting.

**Acceptance Scenarios**:

1. **Given** an admin is logged in, **When** they navigate to the Radiology Centers section, **Then** they see a DataTable listing all radiology centers with columns for name, scan type, governorate, city, status, and actions.
2. **Given** an admin clicks "Add Radiology Center", **When** they fill in Arabic name, English name, scan type, governorate, city, and submit, **Then** the record is created with all fields stored.
3. **Given** an admin edits a radiology center, **When** they change the scan type or names, **Then** changes are persisted and reflected in both the admin list and public search results.
4. **Given** an admin deletes a radiology center, **When** confirmed, **Then** the center is soft-deleted and can be restored.
5. **Given** the interface is in Arabic, **When** viewing radiology center forms and lists, **Then** all labels (including "Scan Type") are in Arabic.

---

### User Story 3 - Bilingual Data Entry in Forms (Priority: P1)

As an admin, I need every form for labs and radiology centers to have clearly labeled fields for both Arabic and English input so that both language versions of the data are captured in a single form submission.

**Why this priority**: Bilingual data is fundamental to the feature's purpose — without it, the public-facing search in Arabic or English would show incomplete data.

**Independent Test**: Can be tested by opening the create form, verifying both "Name (Arabic)" and "Name (English)" fields are present, submitting the form, and confirming both values are stored in the database and returned correctly by the API.

**Acceptance Scenarios**:

1. **Given** an admin opens the "Add Lab" or "Add Radiology Center" form, **When** the form loads, **Then** there are separate, clearly labeled input fields for "Name (Arabic)" and "Name (English)".
2. **Given** an admin fills in only one language field and leaves the other empty, **When** they submit, **Then** validation prevents submission and shows an error indicating both language fields are required.
3. **Given** an admin submits a form with both language names, **When** the record is saved, **Then** the API stores `name_ar` and `name_en` separately, and the public search API returns the correct name based on the request locale.
4. **Given** the admin interface is in Arabic, **When** viewing the form, **Then** the field labels are in Arabic (e.g., "الاسم بالعربية" and "الاسم بالإنجليزية") and the form layout is RTL.

---

### User Story 4 - Bilingual UI Translation (Priority: P2)

As an admin using the system in Arabic, I need all UI elements (sidebar menu items, page titles, button labels, table headers, success/error messages, validation messages) to be fully translated into Arabic so that I can operate the system entirely in my language.

**Why this priority**: While the CRUD functionality works without full translations, usability for Arabic-speaking administrators depends on complete UI localization.

**Independent Test**: Can be tested by switching the application locale to Arabic and navigating through labs and radiology center pages, verifying every visible text string is in Arabic.

**Acceptance Scenarios**:

1. **Given** the system locale is set to Arabic, **When** an admin views the sidebar, **Then** the menu items for Labs and Radiology Centers are displayed in Arabic.
2. **Given** the locale is Arabic, **When** viewing the Labs index page, **Then** page title, table column headers, search placeholder, "Add" button, status badges, and action tooltips are all in Arabic.
3. **Given** the locale is Arabic, **When** an admin submits a form with validation errors, **Then** all error messages are in Arabic.
4. **Given** the locale is Arabic, **When** a CRUD operation succeeds, **Then** the success toast/alert message is in Arabic.
5. **Given** the locale is English, **When** performing the same operations, **Then** all UI text is in English.

---

### User Story 5 - Bulk Actions and Status Management (Priority: P2)

As an admin, I need to perform bulk operations (activate, deactivate, delete, restore) on multiple labs or radiology centers at once, so I can efficiently manage large datasets.

**Why this priority**: Bulk actions improve admin productivity but are not required for basic data management. They follow the existing bulk action pattern used throughout the application.

**Independent Test**: Can be tested by selecting multiple labs in the DataTable, choosing a bulk action, and verifying all selected records are affected.

**Acceptance Scenarios**:

1. **Given** an admin selects multiple labs using checkboxes, **When** they choose "Delete Selected" and confirm, **Then** all selected labs are soft-deleted.
2. **Given** an admin is viewing trashed records, **When** they select records and choose "Restore Selected", **Then** all selected records are restored.
3. **Given** an admin selects records and chooses "Change Status", **When** they toggle to inactive, **Then** all selected records are deactivated.

---

### User Story 6 - Dependent City Dropdown (Priority: P2)

As an admin, when selecting a governorate in the lab or radiology center form, I need the city dropdown to dynamically filter to show only cities belonging to that governorate.

**Why this priority**: Improves data accuracy by preventing mismatched governorate-city combinations, but the form is functional without it (admin can manually pick correct city).

**Independent Test**: Can be tested by selecting a governorate and verifying only its cities appear in the city dropdown; then changing the governorate and confirming the city list updates.

**Acceptance Scenarios**:

1. **Given** an admin opens a create/edit form, **When** they select a governorate, **Then** the city dropdown is populated with only cities belonging to that governorate.
2. **Given** a governorate is already selected (edit mode), **When** the form loads, **Then** the city dropdown shows cities for that governorate and the saved city is pre-selected.
3. **Given** the admin changes the governorate, **When** the city dropdown refreshes, **Then** any previously selected city is cleared.

---

### Edge Cases

- What happens when a lab or radiology center is deleted while it appears in active search results? The soft-deleted record should no longer appear in public search, but existing references (if any) remain intact.
- What happens when the Location module is disabled or no governorates/cities exist? The governorate and city dropdowns should be empty but the form should still be submittable (location fields are optional in the database schema).
- What happens when an admin enters very long names in Arabic or English? Validation should enforce a maximum character length (255 characters, matching the database column type).
- What happens when two labs are created with the same name? The system should allow it — multiple labs can share a name (different locations distinguish them).
- What happens when the governorate or city referenced by a lab is deleted? The lab record should retain the reference but display "N/A" or the last known value.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a Labs management page accessible from the admin sidebar, listing all labs in a paginated, searchable, sortable DataTable.
- **FR-002**: System MUST provide a Radiology Centers management page accessible from the admin sidebar, listing all radiology centers in a paginated, searchable, sortable DataTable.
- **FR-003**: System MUST allow admins to create a new lab by providing: Arabic name (required), English name (required), governorate (optional), and city (optional).
- **FR-004**: System MUST allow admins to create a new radiology center by providing: Arabic name (required), English name (required), scan type (optional), governorate (optional), and city (optional).
- **FR-005**: System MUST allow admins to edit existing labs and radiology centers, pre-populating the form with current values.
- **FR-006**: System MUST allow admins to toggle the active/inactive status of individual labs and radiology centers.
- **FR-007**: System MUST allow admins to soft-delete labs and radiology centers, with the ability to view trashed records and restore them.
- **FR-008**: System MUST support bulk actions (delete, restore, change status) on multiple selected records.
- **FR-009**: System MUST validate that both Arabic and English name fields are provided and do not exceed 255 characters.
- **FR-010**: System MUST dynamically filter the city dropdown based on the selected governorate in create/edit forms.
- **FR-011**: System MUST display all UI text (sidebar items, page titles, table headers, buttons, messages, validation errors) in the user's selected language (Arabic or English).
- **FR-012**: System MUST provide complete Arabic translations for all new UI strings introduced by this feature.
- **FR-013**: System MUST provide complete English translations for all new UI strings introduced by this feature.
- **FR-014**: System MUST restrict access to lab and radiology center management pages to users with the appropriate admin permissions.
- **FR-015**: System MUST send both `name_ar` and `name_en` fields to the backend on form submission, storing them as separate database columns.
- **FR-016**: The admin list view MUST display the name in the current locale (Arabic name when locale is AR, English name when locale is EN).
- **FR-017**: System MUST display the radiology center's scan type as a column in the list view and as a field in the create/edit form.

### Key Entities

- **Lab**: A laboratory facility with bilingual names (Arabic, English), optional location (governorate, city), and active/inactive status. Used in public search for patients finding nearby labs.
- **Radiology Center**: An imaging facility with bilingual names, scan type (e.g., MRI, CT, X-Ray), optional location, and status. Used in public search for patients finding radiology services.
- **Governorate**: A top-level geographic region (already exists in Location module). Used as a filter for labs and radiology centers.
- **Governorate City**: A city within a governorate (already exists in Location module). Dependent on governorate selection.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admins can create a new lab or radiology center with bilingual data in under 1 minute.
- **SC-002**: All admin pages for labs and radiology centers display 100% of visible text in the selected language (no untranslated strings).
- **SC-003**: Bulk actions on 10+ selected records complete successfully within 3 seconds.
- **SC-004**: City dropdown updates within 1 second of selecting a governorate.
- **SC-005**: Data entered via admin forms is correctly returned by the existing public search APIs (`/api/labs/search`, `/api/radiology/search`) in the appropriate language.
- **SC-006**: 100% of CRUD operations (create, read, update, delete, restore) function correctly for both labs and radiology centers.
- **SC-007**: The feature follows existing application patterns (DataTable lists, form layout, permission checks, sidebar structure) with zero deviation from established conventions.

## Assumptions

- The existing `Lab` and `RadiologyCenter` models, database tables, and migrations are already in place and do not need modification.
- The Location module (governorates and cities) is enabled and seeded with data.
- The existing permission system (spatie/laravel-permission) will be extended with new permissions for lab and radiology center management.
- The existing admin layout (`backend.layouts.app`), DataTable patterns, and form conventions from other modules (e.g., LabTestCategories, LabTests) will be followed as templates.
- The sidebar menu will be updated in `GenerateMenus.php` to include new entries under the RadiologyLab section.
- Translation files `lang/en/radiology_lab.php` and `lang/ar/radiology_lab.php` already exist and will be extended with new keys.
