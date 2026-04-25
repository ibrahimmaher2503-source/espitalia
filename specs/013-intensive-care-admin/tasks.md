# Tasks: Intensive Care Admin Platform

**Input**: Design documents from `/specs/013-intensive-care-admin/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/api-endpoints.md, quickstart.md

**Tests**: Test tasks are included — the spec inherits test discipline from the constitution (Principle IV) and the existing IntensiveCare module already has unit tests.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Migrations)

**Purpose**: Create all database schema changes. All 7 migrations must run successfully before any model or controller work.

- [x] T001 [P] Create bilingual migration for hospitals table in `Modules/IntensiveCare/database/migrations/2026_04_05_100001_make_hospital_fields_bilingual.php` — add name_ar, name_en, description_ar, description_en, address_ar, address_en columns; add governorate_id and city_id FKs to governorates and governorate_cities; copy existing data to both language columns; drop old name, description, address columns (see data-model.md Migration 1)
- [x] T002 [P] Create bilingual migration for icu_departments table in `Modules/IntensiveCare/database/migrations/2026_04_05_100002_make_icu_department_fields_bilingual.php` — add name_ar, name_en, description_ar, description_en; copy data; drop old name, description (see data-model.md Migration 2)
- [x] T003 [P] Create bilingual migration for icu_admission_requests table in `Modules/IntensiveCare/database/migrations/2026_04_05_100003_make_icu_admission_request_fields_bilingual.php` — add medical_condition_ar/en, diagnosis_ar/en, admin_notes_ar/en, rejection_reason_ar/en; copy data; drop old columns (see data-model.md Migration 3)
- [x] T004 [P] Create icu_commissions table migration in `Modules/IntensiveCare/database/migrations/2026_04_05_100004_create_icu_commissions_table.php` — id, title, commission_type, commission_value, status, audit fields, softDeletes (see data-model.md Migration 4)
- [x] T005 Create icu_payouts table migration in `Modules/IntensiveCare/database/migrations/2026_04_05_100005_create_icu_payouts_table.php` — id, hospital_id FK, total_amount, total_commission, payment_method, payment_date, notes, audit fields, softDeletes (see data-model.md Migration 6). NOTE: Must run before earnings migration due to FK dependency.
- [x] T006 Create icu_commission_earnings table migration in `Modules/IntensiveCare/database/migrations/2026_04_05_100006_create_icu_commission_earnings_table.php` — id, admission_request_id (unique), hospital_id, commission_id, order_subtotal, commission_type/value snapshot, commission_amount, hospital_receivable, status, payout_id FK, audit fields (see data-model.md Migration 5). Depends on T004 and T005 for FKs.
- [x] T007 Create add_commission_id_to_hospitals migration in `Modules/IntensiveCare/database/migrations/2026_04_05_100007_add_commission_id_to_hospitals_table.php` — add commission_id FK to hospitals referencing icu_commissions, onDelete set null (see data-model.md Migration 7). Depends on T004.
- [ ] T008 Run all migrations: `php artisan module:migrate IntensiveCare` and verify with `php artisan migrate:status | grep icu`

**Checkpoint**: All 7 migrations applied. Database schema ready for model and controller work.

---

## Phase 2: Foundational (Models, Translations, Permissions, Sidebar)

**Purpose**: Update existing models with bilingual accessors/relations, create new commission models, update translations, add permissions, and update sidebar. MUST complete before any user story work.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

### Model Updates (Bilingual + Relations)

- [x] T009 [P] Update Hospital model in `Modules/IntensiveCare/Models/Hospital.php` — remove HasTranslations trait; add name_ar, name_en, description_ar, description_en, address_ar, address_en, governorate_id, city_id, commission_id to $fillable; remove old name, description, address from $fillable; add getNameAttribute(), getDescriptionAttribute(), getAddressAttribute() bilingual accessors with locale fallback (same pattern as Nurse model); add governorate(), city(), commission(), commissionEarnings(), payouts() relationships
- [x] T010 [P] Update IcuDepartment model in `Modules/IntensiveCare/Models/IcuDepartment.php` — remove HasTranslations trait; replace name, description in $fillable with name_ar, name_en, description_ar, description_en; add getNameAttribute(), getDescriptionAttribute() bilingual accessors with locale fallback
- [x] T011 [P] Update IcuAdmissionRequest model in `Modules/IntensiveCare/Models/IcuAdmissionRequest.php` — replace medical_condition, diagnosis, admin_notes, rejection_reason in $fillable with _ar/_en variants; add getMedicalConditionAttribute(), getDiagnosisAttribute(), getAdminNotesAttribute(), getRejectionReasonAttribute() bilingual accessors with locale fallback

### New Commission Models

- [x] T012 [P] Create IcuCommission model in `Modules/IntensiveCare/Models/IcuCommission.php` — extend BaseModel; $table = 'icu_commissions'; $fillable = [title, commission_type, commission_value, status]; casts: status→boolean, commission_value→decimal:2; constants TYPE_PERCENTAGE, TYPE_FIXED; relationships: hospitals() hasMany, earnings() hasMany; scope: active() (see data-model.md)
- [x] T013 [P] Create IcuCommissionEarning model in `Modules/IntensiveCare/Models/IcuCommissionEarning.php` — extend BaseModel; $table = 'icu_commission_earnings'; $fillable and $casts per data-model.md; constants STATUS_UNPAID, STATUS_PAID, STATUS_CANCELLED; relationships: admissionRequest(), hospital(), commission(), payout(); scopes: unpaid(), paid(), forHospital($id)
- [x] T014 [P] Create IcuPayout model in `Modules/IntensiveCare/Models/IcuPayout.php` — extend BaseModel; $table = 'icu_payouts'; $fillable and $casts per data-model.md; constants PAYMENT_METHOD_CASH, PAYMENT_METHOD_BANK_TRANSFER; relationships: hospital(), earnings()

### Commission Service

- [x] T015 Create IcuCommissionService in `Modules/IntensiveCare/Services/IcuCommissionService.php` — calculateCommission(IcuAdmissionRequest): check duplicate, get hospital's commission, calculate split (percentage or fixed with cap), create earning record; reverseCommission(IcuAdmissionRequest): find earning, reject if paid, mark cancelled. Mirror PharmacyCommissionService pattern exactly.

### Translations

- [x] T016 [P] Update English translation file `lang/en/intensive_care.php` — add all commission and earning translation keys: lbl_commission, lbl_commission_template, lbl_commission_type, lbl_commission_value, lbl_percentage, lbl_fixed, lbl_hospital_earning, lbl_hospital_receivable, lbl_platform_commission, lbl_payout, lbl_payment_date, lbl_cash, lbl_bank_transfer, lbl_unpaid, lbl_paid, lbl_cancelled, lbl_total_earned, lbl_total_unpaid, lbl_total_paid, lbl_process_payout, msg_commission_created, msg_payout_processed; also add bilingual field labels: lbl_name_ar, lbl_name_en, lbl_description_ar, lbl_description_en, lbl_address_ar, lbl_address_en, lbl_medical_condition_ar, lbl_medical_condition_en, lbl_diagnosis_ar, lbl_diagnosis_en, lbl_admin_notes_ar, lbl_admin_notes_en, lbl_rejection_reason_ar, lbl_rejection_reason_en, lbl_governorate, lbl_select_governorate, lbl_city, lbl_select_city
- [x] T017 [P] Update Arabic translation file `lang/ar/intensive_care.php` — add Arabic translations for all keys added in T016
- [x] T018 [P] Update sidebar translations in `lang/en/sidebar.php` — add keys: icu_commissions => 'ICU Commissions', icu_earnings => 'ICU Earnings'
- [x] T019 [P] Update sidebar translations in `lang/ar/sidebar.php` — add Arabic translations for icu_commissions and icu_earnings

### Permissions & Sidebar

- [x] T020 Create commission permission seeder in `Modules/IntensiveCare/database/seeders/IntensiveCareCommissionPermissionSeeder.php` — create 6 permissions (view_icu_commission, add_icu_commission, edit_icu_commission, delete_icu_commission, view_icu_earning, edit_icu_earning); assign all to admin role; assign view_icu_earning to hospital_coordinator role. Follow PharmacyCommissionPermissionSeeder pattern.
- [x] T021 Update IntensiveCareDatabaseSeeder in `Modules/IntensiveCare/database/seeders/IntensiveCareDatabaseSeeder.php` — add call to IntensiveCareCommissionPermissionSeeder
- [x] T022 Update sidebar in `app/Http/Middleware/GenerateMenus.php` — add two new childMain items under the existing intensive_care parent menu: "ICU Commissions" (icon: ph ph-receipt, route: backend.icu-commissions.index, permission: view_icu_commission, visible to admin/demo_admin only) and "ICU Earnings" (icon: ph ph-chart-line-up, route: backend.icu-earnings.index, permission: view_icu_earning, visible to admin/demo_admin/hospital_coordinator). Add view_icu_commission and view_icu_earning to the $icuPermissions array.
- [ ] T023 Run permission seeder: `php artisan db:seed --class="Modules\IntensiveCare\Database\Seeders\IntensiveCareCommissionPermissionSeeder"` and verify with `php artisan optimize:clear`

**Checkpoint**: Foundation ready — all models have bilingual accessors, commission models exist, translations are in place, permissions seeded, sidebar updated. User story implementation can now begin in parallel.

---

## Phase 3: User Story 1 — Admin Manages Hospitals with Bilingual Details (Priority: P1) 🎯 MVP

**Goal**: Full admin CRUD for hospitals with side-by-side AR/EN bilingual inputs, cascading governorate/city dropdowns, and DataTable with bilingual columns.

**Independent Test**: Admin creates a hospital with Arabic+English names, selects governorate/city, views it in DataTable, edits it, toggles status, and deletes it.

### Tests for User Story 1

- [x] T024 [P] [US1] Update hospital model tests in `Modules/IntensiveCare/Tests/Unit/HospitalTest.php` — add test cases for: bilingual accessor returns correct locale with fallback; governorate() and city() relationships return correct models; commission() relationship; name_ar/name_en in fillable; old name/description/address removed from fillable

### Implementation for User Story 1

- [x] T025 [P] [US1] Update HospitalFormRequest in `Modules/IntensiveCare/Http/Requests/HospitalFormRequest.php` — replace name/description/address rules with bilingual variants (name_ar: nullable|string|max:255, name_en: same); add custom validation: at least one of name_ar/name_en required; add governorate_id: nullable|exists:governorates,id; add city_id: nullable|exists:governorate_cities,id; add commission_id: nullable|exists:icu_commissions,id
- [x] T026 [US1] Update HospitalsController in `Modules/IntensiveCare/Http/Controllers/Backend/HospitalsController.php` — update create()/edit() to load $governorates (all) and $cities (filtered by selected governorate); update store()/update() to save bilingual fields + governorate_id + city_id; update index_data() to add name_ar, name_en, governorate, city columns with eager loading (->with(['governorate', 'city'])); add location computed column; update DataTable column definitions
- [x] T027 [US1] Update hospital create view in `Modules/IntensiveCare/Resources/views/backend/hospitals/create.blade.php` — replace single name/description/address inputs with side-by-side bilingual pairs (AR with dir="rtl", EN with dir="ltr"); add cascading governorate Select2 dropdown and city Select2 dropdown with AJAX refresh on governorate change (fetch cities via /api/v1/cities?governorate_id=X); add commission_id dropdown (Select2, nullable); add JavaScript for governorate→city cascade
- [x] T028 [US1] Update hospital index view in `Modules/IntensiveCare/Resources/views/backend/hospitals/index.blade.php` — update DataTable columns to show Name (AR), Name (EN), Governorate, City, Type, Status, Actions

**Checkpoint**: Hospital admin CRUD is fully bilingual with location selection. Admin can create/edit/delete hospitals in both languages.

---

## Phase 4: User Story 2 — Admin Manages ICU Departments with Bilingual Details (Priority: P1)

**Goal**: Admin CRUD for ICU departments with bilingual name/description, hospital filter, and inline bed update.

**Independent Test**: Admin creates a department under a hospital with Arabic+English name/description, sets specialty and beds, updates bed count inline.

### Tests for User Story 2

- [x] T029 [P] [US2] Update department model tests in `Modules/IntensiveCare/Tests/Unit/IcuDepartmentTest.php` — add test cases for: bilingual accessor returns correct locale with fallback; name_ar/name_en in fillable; old name/description removed from fillable

### Implementation for User Story 2

- [x] T030 [P] [US2] Update IcuDepartmentFormRequest in `Modules/IntensiveCare/Http/Requests/IcuDepartmentFormRequest.php` — replace name/description rules with bilingual variants; add custom validation: at least one of name_ar/name_en required
- [x] T031 [US2] Update IcuDepartmentsController in `Modules/IntensiveCare/Http/Controllers/Backend/IcuDepartmentsController.php` — update create()/store()/update() for bilingual fields; update index_data() to show name_ar, name_en columns; ensure update_beds() endpoint works for inline bed count update
- [x] T032 [US2] Update department create view in `Modules/IntensiveCare/Resources/views/backend/departments/create.blade.php` — replace single name/description inputs with side-by-side bilingual pairs
- [x] T033 [US2] Update department index view in `Modules/IntensiveCare/Resources/views/backend/departments/index.blade.php` — update DataTable columns to show Name (AR), Name (EN), Hospital, Specialty, Available Beds, Daily Price, Status

**Checkpoint**: Department admin CRUD is fully bilingual. Admin can create/edit departments with dual-language content.

---

## Phase 5: User Story 3 — Admin Manages ICU Admission Requests (Priority: P1)

**Goal**: Admin views, filters, and responds to admission requests with bilingual display for medical condition, diagnosis, admin notes, and rejection reason.

**Independent Test**: Admin views request list, opens a detail page, responds with accept/reject/info-request, and sees status update reflected with bilingual content.

### Tests for User Story 3

- [x] T034 [P] [US3] Update admission request model tests in `Modules/IntensiveCare/Tests/Unit/IcuAdmissionRequestTest.php` — add test cases for: bilingual accessors (medical_condition, diagnosis, admin_notes, rejection_reason) with locale fallback; updated fillable array with _ar/_en fields

### Implementation for User Story 3

- [x] T035 [P] [US3] Update IcuAdmissionRequestFormRequest in `Modules/IntensiveCare/Http/Requests/IcuAdmissionRequestFormRequest.php` — replace medical_condition/diagnosis rules with bilingual variants (medical_condition_ar: nullable|string, medical_condition_en: nullable|string, at least one required); replace admin_notes/rejection_reason with bilingual variants
- [x] T036 [US3] Update IcuAdmissionRequestsController in `Modules/IntensiveCare/Http/Controllers/Backend/IcuAdmissionRequestsController.php` — update respond() method to save admin_notes_ar/en and rejection_reason_ar/en; update index_data() for bilingual display; update show() to pass bilingual fields; ensure status transition calls sendNotificationOnBookingUpdate()
- [x] T037 [US3] Update request show view in `Modules/IntensiveCare/Resources/views/backend/requests/show.blade.php` — display medical_condition, diagnosis in both languages; update response form to include bilingual admin_notes and rejection_reason textareas (side-by-side AR/EN)
- [x] T038 [US3] Update request index view in `Modules/IntensiveCare/Resources/views/backend/requests/index.blade.php` — ensure DataTable columns display correctly with bilingual accessor values

**Checkpoint**: Admission request management is fully bilingual. Admins can respond to requests with dual-language notes and reasons.

---

## Phase 6: User Story 4 — Platform Commission on ICU Admissions (Priority: P2)

**Goal**: Admin configures commission templates, assigns them to hospitals, and the system auto-calculates earnings when admissions are accepted.

**Independent Test**: Admin creates a percentage commission template, assigns it to a hospital, creates and accepts an admission request, and verifies the earning record is created with correct split.

### Tests for User Story 4

- [x] T039 [P] [US4] Create IcuCommission model test in `Modules/IntensiveCare/Tests/Unit/IcuCommissionModelTest.php` — test fillable, casts, constants, active() scope, hospitals() and earnings() relationships
- [x] T040 [P] [US4] Create IcuCommissionEarning model test in `Modules/IntensiveCare/Tests/Unit/IcuCommissionEarningModelTest.php` — test fillable, casts, constants, unpaid()/paid()/forHospital() scopes, all relationships
- [x] T041 [P] [US4] Create IcuCommissionService test in `Modules/IntensiveCare/Tests/Unit/IcuCommissionServiceTest.php` — test: percentage commission calculation (15% of 1000 = 150 commission, 850 receivable); fixed commission calculation (50 flat on 500 = 50 commission, 450 receivable); fixed commission capped at subtotal (prevent negative receivable); duplicate prevention (second call returns null); no commission assigned (returns null); zero subtotal handling; reverseCommission marks as cancelled; reverseCommission rejects paid earnings
- [x] T042 [P] [US4] Create commission permission seeder test in `Modules/IntensiveCare/Tests/Unit/IcuCommissionPermissionSeederTest.php` — test all 6 permissions created and assigned to correct roles

### Implementation for User Story 4

- [x] T043 [P] [US4] Create IcuCommissionFactory in `Modules/IntensiveCare/database/factories/IcuCommissionFactory.php` — default state with title, percentage type, value between 5-25; states: percentage(), fixed(), active(), inactive()
- [x] T044 [P] [US4] Create IcuCommissionEarningFactory in `Modules/IntensiveCare/database/factories/IcuCommissionEarningFactory.php` — default unpaid state; states: unpaid(), paid(), cancelled()
- [x] T045 [P] [US4] Create IcuCommissionRequest in `Modules/IntensiveCare/Http/Requests/IcuCommissionRequest.php` — rules: title required|string|max:255, commission_type required|in:percentage,fixed, commission_value required|numeric|min:0 with max:100 validation for percentage type, status nullable|boolean
- [x] T046 [US4] Create IcuCommissionController in `Modules/IntensiveCare/Http/Controllers/Backend/IcuCommissionController.php` — standard admin controller: index() with DataTable, index_data() with pharmacies count, create()/edit() views, store()/update() with IcuCommissionRequest validation, destroy(), bulk_action(), update_status(). Permission middleware: view_icu_commission, add_icu_commission, edit_icu_commission, delete_icu_commission. Mirror PharmacyCommissionController exactly.
- [x] T047 [P] [US4] Create commission index view in `Modules/IntensiveCare/Resources/views/backend/commissions/index.blade.php` — DataTable with columns: Title, Type, Value, Assigned Hospitals count, Status, Actions. Bulk delete, search, status toggle. Mirror pharmacy::backend.commissions.index.
- [x] T048 [P] [US4] Create commission create/edit view in `Modules/IntensiveCare/Resources/views/backend/commissions/create.blade.php` — form with: title input, commission_type select (percentage/fixed), commission_value input with dynamic label, status checkbox. Mirror pharmacy::backend.commissions.create.
- [x] T049 [US4] Wire commission calculation into admission request status change in `Modules/IntensiveCare/Http/Controllers/Backend/IcuAdmissionRequestsController.php` — in the respond()/update_status() method, when status transitions to 'accepted', call IcuCommissionService::calculateCommission($admissionRequest); when status transitions to 'cancelled', call IcuCommissionService::reverseCommission($admissionRequest). Use the department's daily_price as order_subtotal.
- [x] T050 [US4] Add commission routes to `Modules/IntensiveCare/Routes/web.php` — add resource route for icu-commissions with index_data, bulk_action, update_status custom routes inside the auth group. Pattern: Route::group(['prefix' => 'icu-commissions', 'as' => 'icu-commissions.'], ...) + Route::resource('icu-commissions', IcuCommissionController::class)

**Checkpoint**: Commission system is functional. Admin can create templates, assign to hospitals, and earnings auto-generate on admission acceptance.

---

## Phase 7: User Story 5 — REST API for ICU Entities (Priority: P2)

**Goal**: REST API endpoints for hospitals (with filters), departments, admission requests, and commissions — all returning locale-aware bilingual content.

**Independent Test**: API consumer can list hospitals filtered by governorate, view hospital detail with departments, create/cancel admission requests, and receive Arabic or English content based on Accept-Language header.

### Implementation for User Story 5

- [x] T051 [P] [US5] Update HospitalResource in `Modules/IntensiveCare/Transformers/HospitalResource.php` — use bilingual accessors for name, description, address; add governorate (id+name) and city (id+name) nested objects; add logo_url, departments_count, available_beds_total, price_range; conditionally include commission info for admin role
- [x] T052 [P] [US5] Update IcuDepartmentResource in `Modules/IntensiveCare/Transformers/IcuDepartmentResource.php` — use bilingual accessors for name, description; add hospital nested object (id+name)
- [x] T053 [P] [US5] Update IcuAdmissionRequestResource in `Modules/IntensiveCare/Transformers/IcuAdmissionRequestResource.php` — use bilingual accessors for medical_condition, diagnosis, admin_notes, rejection_reason; add hospital and department nested objects
- [x] T054 [P] [US5] Create IcuCommissionResource in `Modules/IntensiveCare/Transformers/IcuCommissionResource.php` — return id, title, commission_type, commission_value, status, hospitals_count, total_earned aggregate
- [x] T055 [P] [US5] Create IcuCommissionEarningResource in `Modules/IntensiveCare/Transformers/IcuCommissionEarningResource.php` — return all fields with nested admission request and hospital references
- [x] T056 [US5] Update HospitalsApiController in `Modules/IntensiveCare/Http/Controllers/Backend/API/HospitalsApiController.php` — add query filters: governorate_id, city_id, specialty (whereHas on departments), has_ventilator, hospital_type, search (LIKE on name_ar and name_en); add eager loading for governorate, city; ensure index returns paginated HospitalResource collection; ensure show returns hospital with nested departments
- [x] T057 [US5] Update IcuAdmissionRequestsApiController in `Modules/IntensiveCare/Http/Controllers/Backend/API/IcuAdmissionRequestsApiController.php` — update store() to accept bilingual fields (medical_condition_ar/en, diagnosis_ar/en); update show() to return bilingual content via resource; ensure cancel() saves bilingual cancellation details
- [x] T058 [P] [US5] Create IcuCommissionsApiController in `Modules/IntensiveCare/Http/Controllers/Backend/API/IcuCommissionsApiController.php` — admin-only index endpoint returning IcuCommissionResource collection with pagination and search filter
- [x] T059 [P] [US5] Create IcuEarningsApiController in `Modules/IntensiveCare/Http/Controllers/Backend/API/IcuEarningsApiController.php` — index endpoint returning aggregated earnings by hospital (total_platform_commission, total_unpaid_receivable, total_paid_receivable); hospital_coordinator sees own hospital only; support hospital_id, start_date, end_date filters
- [x] T060 [US5] Update API routes in `Modules/IntensiveCare/Routes/api.php` — add routes: GET icu-commissions (admin middleware), GET icu-earnings; ensure existing hospital and admission request routes work with updated controllers

**Checkpoint**: All API endpoints return bilingual content based on Accept-Language header. Location filtering works. Commission/earning endpoints available for admin.

---

## Phase 8: User Story 6 — Earnings Dashboard and Payout Management (Priority: P3)

**Goal**: Admin views consolidated earnings dashboard with aggregate metrics, per-hospital drill-down, and can process payouts to mark earnings as paid.

**Independent Test**: Admin opens earnings dashboard, sees total metrics, clicks into a hospital's detail, views individual earnings, processes a payout, and verifies earnings marked as paid.

### Implementation for User Story 6

- [x] T061 [P] [US6] Create IcuPayoutRequest in `Modules/IntensiveCare/Http/Requests/IcuPayoutRequest.php` — rules: payment_method required|in:cash,bank_transfer, notes nullable|string|max:500
- [x] T062 [US6] Create IcuEarningController in `Modules/IntensiveCare/Http/Controllers/Backend/IcuEarningController.php` — index() with dashboard summary (total commission, total unpaid, total paid excluding cancelled); index_data() aggregated by hospital using subquery selects (mirror PharmacyEarningController); show($hospitalId) with per-hospital stats; show_data($hospitalId) with earning records filterable by status and date range; payouts_data($hospitalId) for payout history; payout($hospitalId) using DB::transaction with lockForUpdate to process all unpaid earnings. Permission middleware: view_icu_earning, edit_icu_earning. Hospital coordinator scope: filter by own hospital.
- [x] T063 [P] [US6] Create earnings index view in `Modules/IntensiveCare/Resources/views/backend/earnings/index.blade.php` — 3 summary cards (Platform Commission, Total Unpaid, Total Paid); searchable DataTable of hospitals with columns: Hospital Name, Total Orders, Platform Commission, Total Unpaid, Total Paid, Actions (view). Mirror pharmacy::backend.earnings.index.
- [x] T064 [P] [US6] Create earnings show view in `Modules/IntensiveCare/Resources/views/backend/earnings/show.blade.php` — summary cards for this hospital; earnings DataTable with columns: Request #, Date, Subtotal, Platform Commission, Hospital Receivable, Status, Payout; filterable by status and date range; payout history table; Process Payout button with modal form (payment method select, notes textarea). Mirror pharmacy::backend.earnings.show.
- [x] T065 [US6] Add earnings routes to `Modules/IntensiveCare/Routes/web.php` — add routes inside auth group: GET icu-earnings (index), GET icu-earnings/index_data, GET icu-earnings/{hospitalId} (show), GET icu-earnings/{hospitalId}/show_data, GET icu-earnings/{hospitalId}/payouts_data, POST icu-earnings/{hospitalId}/payout. Named as backend.icu-earnings.*

**Checkpoint**: Earnings dashboard is functional. Admin can view aggregate metrics, drill into hospital details, and process payouts.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Final validation, code quality, and cross-story integration verification.

- [x] T066 Run Laravel Pint code style check: `./vendor/bin/pint --test` and fix any violations across all modified files
- [x] T067 Run full IntensiveCare test suite: `php artisan test --filter=IntensiveCare` — ensure all existing tests still pass alongside new tests
- [x] T068 Run full application test suite: `php artisan test` — ensure no regressions in other modules
- [x] T069 Clear caches and verify routes: `php artisan optimize:clear && php artisan route:list --path=app/hospitals && php artisan route:list --path=app/icu-commissions && php artisan route:list --path=app/icu-earnings && php artisan route:list --path=v1/hospitals && php artisan route:list --path=v1/icu`
- [x] T070 Validate quickstart.md steps: follow `specs/013-intensive-care-admin/quickstart.md` end-to-end to verify setup, admin panel access, and API testing all work

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 completion (migrations must exist) — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Phase 2 — Hospital model, translations, sidebar must be ready
- **US2 (Phase 4)**: Depends on Phase 2 — IcuDepartment model must be ready
- **US3 (Phase 5)**: Depends on Phase 2 — IcuAdmissionRequest model must be ready
- **US4 (Phase 6)**: Depends on Phase 2 — commission models and service must be ready
- **US5 (Phase 7)**: Depends on Phase 2 + at least US1 (for Hospital resource updates); can run in parallel with other stories if needed
- **US6 (Phase 8)**: Depends on Phase 2 + US4 (commission system must work before earnings dashboard)
- **Polish (Phase 9)**: Depends on all desired user stories being complete

### User Story Dependencies

```
Phase 1 (Setup) ──→ Phase 2 (Foundational) ──┬──→ US1 (Hospitals)     ──┐
                                               ├──→ US2 (Departments)   ──┤
                                               ├──→ US3 (Requests)      ──┤──→ Phase 9 (Polish)
                                               ├──→ US4 (Commissions)  ──┤
                                               │    └──→ US6 (Earnings) ──┤
                                               └──→ US5 (API)           ──┘
```

- **US1, US2, US3**: Fully independent — can run in parallel after Phase 2
- **US4**: Independent of US1-US3, but needed before US6
- **US5**: Can start after Phase 2; benefits from US1 being done first (hospital resource)
- **US6**: Depends on US4 (commission earning records must exist to show on dashboard)

### Within Each User Story

- Tests written and verified to fail BEFORE implementation
- Form requests before controllers (validation defined first)
- Controllers before views (data flow defined first)
- Routes at the end of each phase (wire everything together)

### Parallel Opportunities

- **Phase 1**: T001-T004 can all run in parallel (independent migrations)
- **Phase 2**: T009-T014 (model updates/creates) all in parallel; T016-T019 (translations) all in parallel
- **Phase 3-5 (US1, US2, US3)**: All three stories can run in parallel after Phase 2
- **Within US4**: T039-T045 (tests + factories + request) all in parallel before controllers
- **Within US5**: T051-T055 (all resources) in parallel; T058-T059 (new API controllers) in parallel
- **Within US6**: T061 and T063-T064 in parallel before controller (T062)

---

## Parallel Example: After Phase 2 Completion

```
# Agent A: User Story 1 (Hospital Admin)
Task T025: "Update HospitalFormRequest in Modules/IntensiveCare/Http/Requests/HospitalFormRequest.php"
Task T026: "Update HospitalsController in Modules/IntensiveCare/Http/Controllers/Backend/HospitalsController.php"
Task T027: "Update hospital create view in Modules/IntensiveCare/Resources/views/backend/hospitals/create.blade.php"
Task T028: "Update hospital index view in Modules/IntensiveCare/Resources/views/backend/hospitals/index.blade.php"

# Agent B: User Story 2 (Department Admin)  [in parallel with Agent A]
Task T030: "Update IcuDepartmentFormRequest in Modules/IntensiveCare/Http/Requests/IcuDepartmentFormRequest.php"
Task T031: "Update IcuDepartmentsController in Modules/IntensiveCare/Http/Controllers/Backend/IcuDepartmentsController.php"
Task T032: "Update department create view in Modules/IntensiveCare/Resources/views/backend/departments/create.blade.php"
Task T033: "Update department index view in Modules/IntensiveCare/Resources/views/backend/departments/index.blade.php"

# Agent C: User Story 3 (Admission Requests) [in parallel with Agents A & B]
Task T035: "Update IcuAdmissionRequestFormRequest"
Task T036: "Update IcuAdmissionRequestsController"
Task T037: "Update request show view"
Task T038: "Update request index view"
```

---

## Parallel Example: User Story 4 Tests + Factories

```
# All in parallel (different files, no dependencies):
Task T039: "Create IcuCommission model test in Modules/IntensiveCare/Tests/Unit/IcuCommissionModelTest.php"
Task T040: "Create IcuCommissionEarning model test in Modules/IntensiveCare/Tests/Unit/IcuCommissionEarningModelTest.php"
Task T041: "Create IcuCommissionService test in Modules/IntensiveCare/Tests/Unit/IcuCommissionServiceTest.php"
Task T042: "Create commission permission seeder test in Modules/IntensiveCare/Tests/Unit/IcuCommissionPermissionSeederTest.php"
Task T043: "Create IcuCommissionFactory in Modules/IntensiveCare/database/factories/IcuCommissionFactory.php"
Task T044: "Create IcuCommissionEarningFactory in Modules/IntensiveCare/database/factories/IcuCommissionEarningFactory.php"
Task T045: "Create IcuCommissionRequest in Modules/IntensiveCare/Http/Requests/IcuCommissionRequest.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (all migrations)
2. Complete Phase 2: Foundational (models, translations, permissions, sidebar)
3. Complete Phase 3: User Story 1 (Hospital admin with bilingual + location)
4. **STOP and VALIDATE**: Test hospital CRUD independently — create, edit, view in both locales, location cascade
5. Deploy/demo if ready — bilingual hospitals are the foundation for everything else

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add US1 (Hospitals) → Test → Deploy (MVP!)
3. Add US2 (Departments) + US3 (Requests) → Test → Deploy (Core admin complete)
4. Add US4 (Commissions) → Test → Deploy (Revenue system)
5. Add US5 (API) → Test → Deploy (Mobile ready)
6. Add US6 (Earnings) → Test → Deploy (Full platform)
7. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple agents/developers after Phase 2:

1. **Agent A**: US1 (Hospitals) → then US5 (API, hospitals part)
2. **Agent B**: US2 (Departments) + US3 (Requests)
3. **Agent C**: US4 (Commissions) → then US6 (Earnings)

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story is independently completable and testable
- All bilingual patterns follow Nurse module (012) accessor convention
- All commission patterns follow Pharmacy module (011) service convention
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
