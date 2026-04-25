# Feature Specification: Banner Management

**Feature Branch**: `004-banner-management`
**Created**: 2026-03-30
**Status**: Draft
**Input**: User description: "I need to add feature for control banners in my app add screens for admin dashboard and add APIs for app and add endpoint for docs"

## Clarifications

### Session 2026-03-30

- Q: Should this be a new module or extend the existing Slider module? → A: Extend the existing Slider module with the new banner management capabilities (add fields & features).
- Q: Should the banner list API require authentication? → A: No — the banner list API and click tracking endpoint are public (no auth required). Admin CRUD endpoints remain authenticated.
- Q: Should banner content support multiple languages? → A: Yes — banner title supports multiple languages via the existing Language module translation system.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin Creates and Manages Banners (Priority: P1)

As an admin, I want to create, edit, and manage promotional banners from the admin dashboard so that I can control what banners are displayed across the mobile app and website.

**Why this priority**: Banner creation and management is the core capability — without it, no banners can exist in the system. This is the foundation all other stories depend on.

**Independent Test**: Can be fully tested by logging in as admin, navigating to the Banner Management screen, creating a banner with an image, title, and link, then verifying it appears in the banner list. Delivers immediate value by giving admins control over promotional content.

**Acceptance Scenarios**:

1. **Given** I am logged in as an admin, **When** I navigate to the Banner Management section, **Then** I see a list of all banners with their title, image thumbnail, status, display order, start/end dates, and action buttons.
2. **Given** I am on the banner list page, **When** I click "Create Banner", **Then** I see a form to enter banner details including title, image upload, link URL, target type (category, service, doctor, external URL, or none), display order, status (active/inactive), and optional start/end display dates.
3. **Given** I am filling out the create banner form, **When** I upload an image and fill in required fields and click Save, **Then** the banner is created and appears in the banner list.
4. **Given** a banner exists in the list, **When** I click Edit on that banner, **Then** I can modify all banner details and save changes.
5. **Given** a banner exists in the list, **When** I toggle its status, **Then** the banner's active/inactive state is updated immediately.
6. **Given** multiple banners exist, **When** I select several banners and choose a bulk action (activate, deactivate, delete), **Then** the action is applied to all selected banners.

---

### User Story 2 - Mobile/Web App Retrieves Active Banners via API (Priority: P1)

As a mobile or web app, I need to fetch the list of currently active banners via an API endpoint so that I can display them to end users in carousels, hero sections, or promotional areas.

**Why this priority**: The API is equally critical as the admin CRUD — without it, banners created by admins cannot reach end users. This is the delivery mechanism.

**Independent Test**: Can be tested by calling the banner list API endpoint and verifying it returns only active banners within their display date range, ordered by display order, with image URLs and link targets included.

**Acceptance Scenarios**:

1. **Given** active banners exist with valid display dates, **When** the app calls the banner list API, **Then** only active banners within their display date range are returned, sorted by display order.
2. **Given** a banner has expired (end date is in the past), **When** the app calls the banner list API, **Then** the expired banner is not included in the response.
3. **Given** the app calls the banner list API, **Then** the response includes for each banner: id, title (in the requested or default language), image URL (full path), link URL, target type, and display order.
4. **Given** banners exist for different placement locations, **When** the app calls the API with a location filter, **Then** only banners for that location are returned.

---

### User Story 3 - Admin Views Banner Analytics Overview (Priority: P2)

As an admin, I want to see basic analytics for banners (view count, click count) so that I can measure which banners are performing well and make informed decisions about promotional content.

**Why this priority**: Analytics add value to the management experience but are not required for banners to function. This enhances decision-making.

**Independent Test**: Can be tested by viewing a banner's detail/edit page and verifying that view count and click count are displayed. Click tracking can be tested by calling the click-tracking API endpoint and confirming the counter increments.

**Acceptance Scenarios**:

1. **Given** banners are displayed to users, **When** the API returns banners, **Then** a view impression is recorded for each banner returned.
2. **Given** a user taps/clicks a banner, **When** the app calls the banner click API, **Then** the click count for that banner is incremented.
3. **Given** I am editing a banner in the admin dashboard, **When** I view the banner details, **Then** I can see the total view count and click count.

---

### User Story 4 - API Documentation Endpoint (Priority: P2)

As a developer integrating with the banner API, I want accessible API documentation so that I can understand the available endpoints, request/response formats, and authentication requirements.

**Why this priority**: Documentation enables external and internal developers to integrate with the banner API efficiently, reducing support burden and integration errors.

**Independent Test**: Can be tested by navigating to the API documentation endpoint and verifying all banner API endpoints are listed with request parameters, response schemas, and example responses.

**Acceptance Scenarios**:

1. **Given** I am a developer, **When** I access the banner API documentation endpoint, **Then** I see a list of all available banner API endpoints with descriptions.
2. **Given** I am viewing the API documentation, **When** I look at any endpoint, **Then** I see the HTTP method, URL path, required/optional parameters, authentication requirements, and example request/response payloads.

---

### Edge Cases

- What happens when an admin uploads an image that exceeds the maximum file size? The system should reject the upload and display a clear error message with the allowed maximum size.
- What happens when a banner's start date is in the future? The banner should be saved but not returned by the public API until the start date arrives.
- What happens when two banners have the same display order? They should be returned in creation date order as a tiebreaker.
- How does the system handle a banner with no image? The system should require at least one image — the form should validate and prevent submission without an image.
- What happens when the banner link URL is invalid or the target entity (e.g., a service or doctor) has been deleted? The API should still return the banner but with a null/empty link, and the admin should see a warning indicator on the banner list.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow admins to create banners with: title (translatable, supports multiple languages), image, link URL, target type (category, service, doctor, external URL, none), display order (numeric), status (active/inactive), and optional start/end display dates.
- **FR-002**: System MUST allow admins to view a paginated, searchable list of all banners with server-side rendering (DataTable pattern).
- **FR-003**: System MUST allow admins to edit any existing banner's details including replacing the image.
- **FR-004**: System MUST allow admins to delete banners (soft delete) individually or via bulk action.
- **FR-005**: System MUST allow admins to toggle banner status (active/inactive) directly from the list view.
- **FR-006**: System MUST allow admins to perform bulk actions: activate, deactivate, and delete on selected banners.
- **FR-007**: System MUST provide a public (no authentication required) API endpoint to retrieve active banners, filtered by display dates, sorted by display order.
- **FR-008**: System MUST support banner placement locations (e.g., "home_top", "home_middle", "category_page") so admins can control where banners appear.
- **FR-009**: System MUST validate that banner images meet size and format requirements (JPEG, PNG, WebP; max 2MB).
- **FR-010**: System MUST track view impressions and click counts for each banner.
- **FR-011**: System MUST provide a public (no authentication required) API endpoint for recording banner clicks from the app.
- **FR-012**: System MUST provide an API documentation endpoint listing all banner-related API endpoints with parameters, authentication requirements, and response schemas.
- **FR-013**: System MUST enforce role-based access control — only users with banner management permissions can create, edit, or delete banners.
- **FR-014**: System MUST return banner images as full URLs (not relative paths) in API responses.
- **FR-015**: System MUST support ordering banners via a display order field, with creation date as a tiebreaker for equal orders.

### Key Entities

- **Banner**: Represents a promotional banner. Key attributes: title (translatable — supports multiple languages via the Language module), status (active/inactive), display order (integer), link URL, target type (category/service/doctor/external/none), target ID (reference to linked entity), placement location, start date (optional), end date (optional), view count, click count. Has one associated image.
- **Banner Placement Location**: A defined area in the app/website where banners can appear (e.g., home top carousel, category page header). Key attributes: location key (unique identifier string), display name, description.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admins can create a fully configured banner (with image, link, and scheduling) in under 2 minutes.
- **SC-002**: The banner list API responds with active banners suitable for immediate display in the app without additional client-side filtering.
- **SC-003**: All banner CRUD operations are accessible and functional from the admin dashboard without requiring direct database access.
- **SC-004**: Banner API documentation is accessible and contains accurate information for all endpoints, enabling a developer to integrate without additional support.
- **SC-005**: Banner display order and date-based scheduling work correctly — expired banners do not appear in the API, and banners display in the intended order.
- **SC-006**: Click and view tracking captures interactions with at least 95% accuracy under normal usage conditions.

## Assumptions

- This feature extends the existing Slider module (`Modules/Slider/`) rather than creating a new module. The existing Slider model, controller, routes, and views will be enhanced with new fields (placement location, start/end dates, view/click counts) and new API endpoints. Existing functionality (homepage banner carousel) will be preserved and enhanced.
- Banner images will be stored using the existing Spatie MediaLibrary integration already present in the BaseModel.
- Authentication for admin operations follows the existing `auth` + `auth_check` middleware pattern.
- Admin CRUD API endpoints use Sanctum authentication. The public banner list and click tracking endpoints do not require authentication.
- The API documentation will be a simple, auto-generated documentation page (not a full Swagger/OpenAPI setup) consistent with the project's current approach.
- Maximum banner image file size is 2MB, supporting JPEG, PNG, and WebP formats.
- Placement locations will be predefined by the admin rather than dynamically created — a reasonable initial set will be seeded (home_top, home_middle, category_page).
- View count tracking occurs when the API returns banners (server-side increment), and click tracking occurs when the app explicitly calls the click API endpoint.
