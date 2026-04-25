# Tasks: Pharmacy Commissions

**Input**: Design documents from `specs/011-pharmacy-commissions/`
**Prerequisites**: plan.md, spec.md, data-model.md, research.md, quickstart.md

**Tests**: Constitution Principle IV requires PHPUnit tests for new features. Test tasks are included per phase where applicable.

**Organization**: Tasks are grouped by user story (P1-P4) to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies on incomplete tasks)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4)
- Exact file paths included in all descriptions

## Phase 1: Setup (Migrations & Models)

**Purpose**: Database schema and Eloquent models that ALL user stories depend on

- [ ] T001 [P] Create migration `Modules/Pharmacy/database/migrations/2026_04_05_100010_create_pharmacy_commissions_table.php` — fields: id, title (string 255), commission_type (string 20, in: percentage/fixed), commission_value (decimal 8,2), status (boolean default true), audit columns (created_by, updated_by, deleted_by), timestamps, softDeletes. Index: status
- [ ] T002 [P] Create migration `Modules/Pharmacy/database/migrations/2026_04_05_100011_create_pharmacy_commission_earnings_table.php` — fields: id, order_id (FK → pharmacy_orders, unique), pharmacy_id (FK → pharmacies), commission_id (nullable FK → pharmacy_commissions ON DELETE SET NULL), order_subtotal (decimal 10,2), commission_type (string 20), commission_value (decimal 8,2), commission_amount (decimal 10,2), pharmacy_receivable (decimal 10,2), status (string 20, default 'unpaid'), payout_id (nullable FK → pharmacy_payouts ON DELETE SET NULL), audit columns, timestamps, softDeletes. Indexes: order_id (unique), pharmacy_id, status, composite (pharmacy_id, status), payout_id
- [ ] T003 [P] Create migration `Modules/Pharmacy/database/migrations/2026_04_05_100012_create_pharmacy_payouts_table.php` — fields: id, pharmacy_id (FK → pharmacies), total_amount (decimal 10,2), total_commission (decimal 10,2), payment_method (string 50), payment_date (date), notes (text nullable), audit columns, timestamps, softDeletes. Indexes: pharmacy_id, payment_date
- [ ] T004 Create migration `Modules/Pharmacy/database/migrations/2026_04_05_100013_add_commission_id_to_pharmacies_table.php` — add nullable commission_id (bigint unsigned FK → pharmacy_commissions ON DELETE SET NULL) to pharmacies table. Index: commission_id
- [ ] T005 [P] Create PharmacyCommission model in `Modules/Pharmacy/Models/PharmacyCommission.php` — extends BaseModel, table pharmacy_commissions, fillable (title, commission_type, commission_value, status), casts (status → boolean, commission_value → decimal:2), relationships: pharmacies() hasMany(Pharmacy, 'commission_id'), earnings() hasMany(PharmacyCommissionEarning), scope scopeActive (status = true)
- [ ] T006 [P] Create PharmacyCommissionEarning model in `Modules/Pharmacy/Models/PharmacyCommissionEarning.php` — extends BaseModel, table pharmacy_commission_earnings, fillable (order_id, pharmacy_id, commission_id, order_subtotal, commission_type, commission_value, commission_amount, pharmacy_receivable, status, payout_id), casts (order_subtotal/commission_value/commission_amount/pharmacy_receivable → decimal:2), STATUS constants (UNPAID='unpaid', PAID='paid', CANCELLED='cancelled'), relationships: order() belongsTo(PharmacyOrder), pharmacy() belongsTo(Pharmacy), commission() belongsTo(PharmacyCommission), payout() belongsTo(PharmacyPayout), scopes: scopeUnpaid, scopePaid, scopeForPharmacy($id)
- [ ] T007 [P] Create PharmacyPayout model in `Modules/Pharmacy/Models/PharmacyPayout.php` — extends BaseModel, table pharmacy_payouts, fillable (pharmacy_id, total_amount, total_commission, payment_method, payment_date, notes), casts (total_amount/total_commission → decimal:2, payment_date → date), PAYMENT_METHOD constants (CASH='cash', BANK_TRANSFER='bank_transfer'), relationships: pharmacy() belongsTo(Pharmacy), earnings() hasMany(PharmacyCommissionEarning, 'payout_id')
- [ ] T008 Add commission() belongsTo relationship to existing `Modules/Pharmacy/Models/Pharmacy.php` — `return $this->belongsTo(PharmacyCommission::class, 'commission_id')`

---

## Phase 2: Foundational (Permissions, Translations, Service)

**Purpose**: Shared infrastructure that MUST be complete before user story work begins

**CRITICAL**: No user story work can begin until this phase is complete

- [ ] T009 Create PharmacyCommissionPermissionSeeder in `Modules/Pharmacy/database/seeders/PharmacyCommissionPermissionSeeder.php` — create permissions: view_pharmacy_commission, add_pharmacy_commission, edit_pharmacy_commission, delete_pharmacy_commission, view_pharmacy_earning, edit_pharmacy_earning. Use givePermissionTo (NOT syncPermissions) to assign all to admin role
- [ ] T010 [P] Add English translation keys in `lang/en/pharmacy.php` — keys for: lbl_commission, lbl_commission_template, lbl_commission_type, lbl_commission_value, lbl_percentage, lbl_fixed, lbl_pharmacy_earning, lbl_pharmacy_receivable, lbl_platform_commission, lbl_payout, lbl_payment_method, lbl_payment_date, lbl_cash, lbl_bank_transfer, lbl_unpaid, lbl_paid, lbl_cancelled, lbl_total_earned, lbl_total_unpaid, lbl_total_paid, lbl_process_payout, msg_commission_created, msg_payout_processed
- [ ] T011 [P] Add Arabic translation keys in `lang/ar/pharmacy.php` — Arabic translations for all keys defined in T010
- [ ] T012 [P] Add sidebar translation keys to `lang/en/sidebar.php` and `lang/ar/sidebar.php` — keys: pharmacy_commissions (EN: 'Commissions', AR: 'العمولات'), pharmacy_earnings (EN: 'Pharmacy Earnings', AR: 'أرباح الصيدليات')
- [ ] T013 Implement PharmacyCommissionService in `Modules/Pharmacy/Services/PharmacyCommissionService.php` — methods: calculateCommission(PharmacyOrder $order) — looks up pharmacy's assigned commission, calculates platform commission and pharmacy receivable, creates PharmacyCommissionEarning with status='unpaid', returns earning or null if no commission assigned. Includes duplicate check (order_id unique). reverseCommission(PharmacyOrder $order) — finds earning for order_id, sets status='cancelled' if exists. Both methods handle edge cases: no commission assigned (skip), zero subtotal (create with 0.00 amounts), already existing earning (skip creation)

**Checkpoint**: Models, migrations, permissions, translations, and commission service are ready. Run `php artisan migrate` and `php artisan db:seed --class=PharmacyCommissionPermissionSeeder` to verify.

---

## Phase 3: User Story 1 — Admin Configures Commission Rates (Priority: P1)

**Goal**: Admin can create commission templates (percentage or fixed), edit/deactivate them, and assign one to each pharmacy

**Independent Test**: Admin creates a commission template, assigns it to a pharmacy. Verify template is saved and pharmacy shows the assigned commission.

### Form Requests

- [ ] T014 [P] [US1] Create PharmacyCommissionRequest in `Modules/Pharmacy/Http/Requests/PharmacyCommissionRequest.php` — rules: title (required, string, max 255), commission_type (required, in: percentage,fixed), commission_value (required, numeric, min: 0, max: 100 if percentage), status (nullable, boolean)

### Controller

- [ ] T015 [US1] Implement PharmacyCommissionController in `Modules/Pharmacy/Http/Controllers/PharmacyCommissionController.php` — standard admin methods: index (DataTable list), index_data (server-side JSON for DataTable with search on title, filter by type and status), create, store, edit, update, destroy, bulk_action (delete), update_status (toggle active/inactive). Constructor sets module_title/module_name/module_path and registers permission middleware: can:view_pharmacy_commission (index, index_data), can:add_pharmacy_commission (create, store), can:edit_pharmacy_commission (edit, update, update_status), can:delete_pharmacy_commission (destroy, bulk_action). index_data returns list with pharmacy count (withCount('pharmacies'))

### Blade Views

- [ ] T016 [P] [US1] Create commission Blade views in `Modules/Pharmacy/Resources/views/backend/commissions/` — index.blade.php (extends backend.layouts.app, DataTable with columns: title, type, value, assigned pharmacies count, status toggle, actions), create.blade.php (form with title input, commission_type dropdown, commission_value input, used for both create and edit, AJAX submit)

### Commission Assignment (Pharmacy Edit)

- [ ] T017 [US1] Update existing PharmacyController edit/create views to include a Commission dropdown — in `Modules/Pharmacy/Resources/views/backend/pharmacies/create.blade.php` add a select dropdown for commission_id (populated from PharmacyCommission::active()->get()), update store/update methods in `Modules/Pharmacy/Http/Controllers/PharmacyController.php` to save commission_id, update PharmacyRequest to allow commission_id (nullable, exists:pharmacy_commissions,id)

### Routes and Menu

- [ ] T018 [US1] Register commission web routes in `Modules/Pharmacy/Routes/web.php` — within existing admin group: resource route for pharmacy-commissions + custom routes (index_data, bulk_action, update_status)
- [ ] T019 [US1] Add "Commissions" sidebar menu entry in `app/Http/Middleware/GenerateMenus.php` — under existing Pharmacy Marketplace parent: childMain with icon 'ph ph-percent', title __('sidebar.pharmacy_commissions'), route backend.pharmacy-commissions.index, permission view_pharmacy_commission

**Checkpoint**: Admin can CRUD commission templates and assign one to each pharmacy via the pharmacy edit form.

---

## Phase 4: User Story 2 — Auto-Calculate Commission on Delivery (Priority: P2)

**Goal**: When a pharmacy order status changes to "delivered", the system auto-calculates platform commission and pharmacy receivable

**Independent Test**: Assign a 10% commission to a pharmacy, deliver an order. Verify commission earning created with correct amounts.

- [ ] T020 [US2] Hook PharmacyCommissionService::calculateCommission into PharmacyPanelOrderController::advance_status in `Modules/Pharmacy/Http/Controllers/PharmacyPanel/PharmacyPanelOrderController.php` — after status is updated to 'delivered', call $this->commissionService->calculateCommission($order). Inject PharmacyCommissionService via constructor alongside existing OrderService
- [ ] T021 [US2] Hook PharmacyCommissionService::calculateCommission into PharmacyOrderController::update_status in `Modules/Pharmacy/Http/Controllers/PharmacyOrderController.php` — after status is updated to 'delivered', call $this->commissionService->calculateCommission($order). Inject PharmacyCommissionService via constructor alongside existing OrderService
- [ ] T022 [US2] Hook PharmacyCommissionService::reverseCommission into OrderService::cancelOrder in `Modules/Pharmacy/Services/OrderService.php` — after order status is set to 'cancelled', call PharmacyCommissionService::reverseCommission($order). Inject or instantiate PharmacyCommissionService within cancelOrder method

### Tests

- [ ] T023 [P] [US2] Create PharmacyCommissionServiceTest in `Modules/Pharmacy/Tests/Feature/PharmacyCommissionServiceTest.php` — test cases: (1) percentage commission calculation correct amounts, (2) fixed commission calculation, (3) no commission assigned produces no earning, (4) duplicate order_id does not create second earning, (5) reversal sets status to cancelled, (6) zero subtotal produces zero amounts

**Checkpoint**: Delivering a pharmacy order automatically creates a commission earning. Cancelling a delivered order reverses the earning.

---

## Phase 5: User Story 3 — Admin Views Pharmacy Earnings Dashboard (Priority: P3)

**Goal**: Admin sees financial overview with all pharmacy earnings, filters, and per-pharmacy detail

**Independent Test**: With delivered orders and commission earnings, admin opens earnings page and sees accurate totals, working filters, and per-pharmacy breakdowns.

### Controller

- [ ] T024 [US3] Implement PharmacyEarningController in `Modules/Pharmacy/Http/Controllers/PharmacyEarningController.php` — methods: index (earnings dashboard page), index_data (server-side JSON: list pharmacies with aggregated unpaid/paid totals using subqueries on pharmacy_commission_earnings grouped by pharmacy_id, supports search on pharmacy name, filter by status), show($pharmacyId) (per-pharmacy detail page with individual order earnings), show_data($pharmacyId) (server-side JSON: paginated list of earnings for a pharmacy with order details, filter by status and date range). Constructor registers permission middleware: can:view_pharmacy_earning for all methods. Dashboard summary stats (total_platform_commission, total_unpaid_receivable, total_paid_receivable) computed via aggregate queries

### Blade Views

- [ ] T025 [P] [US3] Create earnings Blade views in `Modules/Pharmacy/Resources/views/backend/earnings/` — index.blade.php (extends backend.layouts.app, summary cards at top: Total Platform Commission, Total Unpaid Receivable, Total Paid; below: DataTable of pharmacies with columns: pharmacy name, total orders, platform commission, unpaid receivable, paid receivable, actions with view link), show.blade.php (per-pharmacy detail: pharmacy name header, summary stats for this pharmacy, DataTable of individual earnings: order ID, order date, order subtotal, commission amount, pharmacy receivable, status badge, payout ID if paid; filter by status dropdown and date range inputs)

### Routes and Menu

- [ ] T026 [US3] Register earning web routes in `Modules/Pharmacy/Routes/web.php` — within existing admin group: GET pharmacy-earnings (index), GET pharmacy-earnings/index-data (index_data), GET pharmacy-earnings/{pharmacyId} (show), GET pharmacy-earnings/{pharmacyId}/data (show_data)
- [ ] T027 [US3] Add "Pharmacy Earnings" sidebar menu entry in `app/Http/Middleware/GenerateMenus.php` — under existing Pharmacy Marketplace parent: childMain with icon 'ph ph-currency-circle-dollar', title __('sidebar.pharmacy_earnings'), route backend.pharmacy-earnings.index, permission view_pharmacy_earning

**Checkpoint**: Admin can view the earnings dashboard, filter by pharmacy/status/date, and drill into per-pharmacy detail.

---

## Phase 6: User Story 4 — Admin Processes Commission Payouts (Priority: P4)

**Goal**: Admin can pay a pharmacy's outstanding receivable, creating a payout record and marking earnings as paid

**Independent Test**: Select a pharmacy with unpaid earnings, process payout. Verify earnings become "paid" and payout record is created.

### Form Request

- [ ] T028 [P] [US4] Create PharmacyPayoutRequest in `Modules/Pharmacy/Http/Requests/PharmacyPayoutRequest.php` — rules: payment_method (required, in: cash,bank_transfer), notes (nullable, string, max 500)

### Payout Logic

- [ ] T029 [US4] Add payout processing methods to PharmacyEarningController in `Modules/Pharmacy/Http/Controllers/PharmacyEarningController.php` — payout($pharmacyId, PharmacyPayoutRequest) method: within DB::transaction, get all unpaid earnings for pharmacy, calculate total_amount (sum pharmacy_receivable) and total_commission (sum commission_amount), create PharmacyPayout record, update all unpaid earnings with payout_id and status='paid'. Return JSON success. Add can:edit_pharmacy_earning middleware to payout method

### Blade Integration

- [ ] T030 [US4] Add payout button and form to `Modules/Pharmacy/Resources/views/backend/earnings/show.blade.php` — at top of per-pharmacy detail page, show "Process Payout" button (only visible when unpaid amount > 0), clicking opens a modal/section with: payment method dropdown (Cash, Bank Transfer), optional notes textarea, confirm button. AJAX submit to payout route. After success, reload page to reflect updated statuses. Below earnings table, add "Payout History" section: DataTable of pharmacy_payouts for this pharmacy showing payout ID, date, total payout amount, total commission, payment method, notes

### Routes

- [ ] T031 [US4] Register payout route in `Modules/Pharmacy/Routes/web.php` — within existing admin group: POST pharmacy-earnings/{pharmacyId}/payout (named backend.pharmacy-earnings.payout)

**Checkpoint**: Admin can process payouts, earnings transition to paid, payout records are created and visible in history.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final integration, validation, and cleanup

- [ ] T032 Run Pharmacy module migrations and commission permission seeder — `php artisan migrate && php artisan db:seed --class=Modules\\Pharmacy\\Database\\Seeders\\PharmacyCommissionPermissionSeeder`
- [ ] T033 Run `./vendor/bin/pint Modules/Pharmacy` to fix code style across all new files
- [ ] T034 Run `php artisan optimize:clear && composer dump-autoload` to clear caches
- [ ] T035 Validate end-to-end flow per `specs/011-pharmacy-commissions/quickstart.md`: create commission → assign to pharmacy → deliver order → verify earning → view dashboard → process payout → verify paid status

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 — **BLOCKS all user stories**
- **US1 (Phase 3)**: Depends on Phase 2 — commission template CRUD
- **US2 (Phase 4)**: Depends on Phase 2 + Phase 3 (needs commission templates to exist and be assigned to pharmacies)
- **US3 (Phase 5)**: Depends on Phase 2 + Phase 4 (needs commission earnings data to display)
- **US4 (Phase 6)**: Depends on Phase 5 (adds payout to the earning detail page)
- **Polish (Phase 7)**: Depends on all previous phases

### User Story Dependencies

```
Phase 1 (Setup)
    └──→ Phase 2 (Foundational)
              └──→ Phase 3 (US1: Commission CRUD)
                        └──→ Phase 4 (US2: Auto-Calculation)
                                  └──→ Phase 5 (US3: Earnings Dashboard)
                                            └──→ Phase 6 (US4: Payouts)
                                                      └──→ Phase 7 (Polish)
```

Note: Unlike the Pharmacy Marketplace feature, these user stories are **sequential** — each depends on the previous. US2 needs commission templates from US1, US3 needs earnings from US2, US4 adds payout to US3's views.

### Within Each User Story

1. Form requests before controllers (controllers use requests for validation)
2. Controllers before views (views reference controller routes)
3. Views before routes (verify templates exist before registering)
4. Routes before menu entries (menu links to named routes)

### Parallel Opportunities

**Within Phase 1** (Setup):
- T001, T002, T003 (independent table migrations) — all parallel
- T005, T006, T007 (all models) — all parallel after migrations

**Within Phase 2** (Foundational):
- T010, T011, T012 (translations) — all parallel

**Within Phase 3** (US1):
- T014, T016 (form request and blade views) — parallel

**Within Phase 4** (US2):
- T023 (test) — parallel with other phase 4 tasks

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (migrations + models)
2. Complete Phase 2: Foundational (permissions, translations, service)
3. Complete Phase 3: US1 (commission CRUD + assignment)
4. **STOP and VALIDATE**: Admin can create and assign commission templates

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add US1 (Commission CRUD) → Admin configures commissions (MVP)
3. Add US2 (Auto-Calculation) → Orders generate earnings automatically
4. Add US3 (Earnings Dashboard) → Admin views financial data
5. Add US4 (Payouts) → Admin processes payments to pharmacies
6. Polish → Code style, caches, end-to-end validation
