# Tasks: Pharmacy Marketplace

**Input**: Design documents from `specs/010-pharmacy-marketplace/`
**Prerequisites**: plan.md, spec.md, data-model.md, contracts/api-endpoints.md, research.md

**Tests**: Not explicitly requested in feature specification. Test tasks are excluded. Tests may be added later via `/speckit.implement` or manually.

**Organization**: Tasks are grouped by user story (P1-P4) to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies on incomplete tasks)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4)
- Exact file paths included in all descriptions

## Phase 1: Setup (Module Scaffold)

**Purpose**: Create the Pharmacy module skeleton and configure it within the application

- [X] T001 Scaffold Pharmacy module by running `php artisan module:make Pharmacy` and verify directory structure under `Modules/Pharmacy/`
- [X] T002 Enable the Pharmacy module in `modules_statuses.json` (set `"Pharmacy": true`) and verify with `php artisan module:list`
- [X] T003 [P] Create frontend asset stubs: `Modules/Pharmacy/Resources/assets/js/app.js` (empty Vue entry) and `Modules/Pharmacy/Resources/assets/sass/app.scss` (empty SCSS entry)

---

## Phase 2: Foundational (Migrations, Models, Permissions, Translations)

**Purpose**: Database schema, Eloquent models, permissions, and translations that ALL user stories depend on

**CRITICAL**: No user story work can begin until this phase is complete

### Migrations

- [X] T004 [P] Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000001_create_pharmacy_categories_table.php` — fields: id, parent_id (nullable FK self-ref ON DELETE CASCADE), name_ar, name_en, is_active (default true), sort_order (default 0), audit columns (created_by, updated_by, deleted_by), timestamps, softDeletes. Indexes: parent_id, is_active, composite (parent_id, sort_order)
- [X] T005 [P] Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000002_create_pharmacy_brands_table.php` — fields: id, name (unique), is_active (default true), audit columns, timestamps, softDeletes
- [X] T006 [P] Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000003_create_pharmacy_product_types_table.php` — fields: id, name_ar, name_en, audit columns, timestamps, softDeletes
- [X] T007 [P] Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000004_create_pharmacies_table.php` — fields: id, name, phone, address (text), latitude (double nullable), longitude (double nullable), delivery_fee (decimal 8,2 default 0), is_active (default true), audit columns, timestamps, softDeletes. Index: is_active
- [X] T008 Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000005_create_pharmacy_products_table.php` — fields: id, name_ar, name_en, category_id (FK → pharmacy_categories), sub_category_id (FK → pharmacy_categories), brand_id (nullable FK → pharmacy_brands), product_type_id (nullable FK → pharmacy_product_types), description (text nullable), requires_prescription (boolean default false), is_active (default true), audit columns, timestamps, softDeletes. Indexes: category_id, sub_category_id, brand_id, product_type_id, is_active
- [X] T009 Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000006_create_pharmacy_inventory_table.php` — fields: id, pharmacy_id (FK → pharmacies ON DELETE CASCADE), product_id (FK → pharmacy_products ON DELETE CASCADE), price (decimal 10,2), stock_qty (unsigned int default 0), is_available (boolean default true), audit columns, timestamps, softDeletes. Unique: (pharmacy_id, product_id). Index: composite (product_id, stock_qty, is_available)
- [X] T010 [P] Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000007_create_pharmacy_cart_items_table.php` — fields: id, user_id (FK → users ON DELETE CASCADE), product_id (FK → pharmacy_products ON DELETE CASCADE), quantity (unsigned int), timestamps. NO soft deletes. Unique: (user_id, product_id)
- [X] T011 Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000008_create_pharmacy_orders_table.php` — fields: id, user_id (FK → users), pharmacy_id (FK → pharmacies), address_id (FK → addresses), subtotal (decimal 10,2), delivery_fee (decimal 8,2), total (decimal 10,2), payment_method (string 50 default 'cash_on_delivery'), status (string 20 default 'pending'), audit columns, timestamps, softDeletes. Indexes: user_id, pharmacy_id, status, composite (user_id, status)
- [X] T012 Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000009_create_pharmacy_order_items_table.php` — fields: id, order_id (FK → pharmacy_orders ON DELETE CASCADE), product_id (FK → pharmacy_products), product_name (string 255), quantity (unsigned int), price (decimal 10,2), timestamps. NO soft deletes. Index: order_id, product_id
- [X] T013 Create migration `Modules/Pharmacy/database/migrations/2026_04_05_000010_add_pharmacy_id_to_users_table.php` — add nullable pharmacy_id (bigint unsigned FK → pharmacies ON DELETE SET NULL) to users table

### Models

- [X] T014 [P] Create PharmacyCategory model in `Modules/Pharmacy/Models/PharmacyCategory.php` — extends BaseModel, table pharmacy_categories, fillable fields, casts (is_active → boolean), self-referential relationships (parent, children), products/subcategoryProducts hasMany, bilingual name accessor with locale fallback, media collection `category_image` (singleFile), scope for active categories sorted by sort_order
- [X] T015 [P] Create PharmacyBrand model in `Modules/Pharmacy/Models/PharmacyBrand.php` — extends BaseModel, table pharmacy_brands, fillable, casts (is_active → boolean), products() hasMany
- [X] T016 [P] Create PharmacyProductType model in `Modules/Pharmacy/Models/PharmacyProductType.php` — extends BaseModel, table pharmacy_product_types, fillable, bilingual name accessor, products() hasMany
- [X] T017 [P] Create Pharmacy model in `Modules/Pharmacy/Models/Pharmacy.php` — extends BaseModel, table pharmacies, fillable, casts (is_active → boolean, delivery_fee → decimal), inventory() hasMany, orders() hasMany, staff() hasMany(User, 'pharmacy_id'), media collection `logo` (singleFile)
- [X] T018 [P] Create PharmacyProduct model in `Modules/Pharmacy/Models/PharmacyProduct.php` — extends BaseModel, table pharmacy_products, fillable, casts (is_active → boolean, requires_prescription → boolean), relationships: category, subcategory, brand, productType (all belongsTo), inventories() hasMany, media collection `product_image` (singleFile), bilingual name accessor, scopes: scopeActive, scopeInStock (joins pharmacy_inventory WHERE stock_qty > 0 AND is_available = true)
- [X] T019 [P] Create PharmacyInventory model in `Modules/Pharmacy/Models/PharmacyInventory.php` — extends BaseModel, table pharmacy_inventory, fillable, casts (price → decimal:2, stock_qty → integer, is_available → boolean), relationships: pharmacy() belongsTo, product() belongsTo, scope: scopeAvailable (stock_qty > 0 AND is_available = true)
- [X] T020 [P] Create PharmacyCartItem model in `Modules/Pharmacy/Models/PharmacyCartItem.php` — does NOT extend BaseModel (no soft deletes, no audit columns), table pharmacy_cart_items, fillable (user_id, product_id, quantity), casts (quantity → integer), relationships: user() belongsTo, product() belongsTo
- [X] T021 [P] Create PharmacyOrder model in `Modules/Pharmacy/Models/PharmacyOrder.php` — extends BaseModel, table pharmacy_orders, fillable, casts (subtotal/delivery_fee/total → decimal:2), STATUS constants (PENDING, CONFIRMED, PREPARING, ON_THE_WAY, DELIVERED, CANCELLED), relationships: user, pharmacy, address (all belongsTo), items() hasMany(PharmacyOrderItem), scopes: scopeForUser, scopeByStatus
- [X] T022 [P] Create PharmacyOrderItem model in `Modules/Pharmacy/Models/PharmacyOrderItem.php` — does NOT use SoftDeletes (immutable snapshot), table pharmacy_order_items, fillable, casts (price → decimal:2, quantity → integer), relationships: order() belongsTo, product() belongsTo

### Supporting Infrastructure

- [X] T023 Add `pharmacy()` belongsTo relationship to `app/Models/User.php` — `return $this->belongsTo(\Modules\Pharmacy\Models\Pharmacy::class, 'pharmacy_id')`
- [X] T024 Create PharmacyPermissionSeeder in `Modules/Pharmacy/database/seeders/PharmacyPermissionSeeder.php` — create `pharmacy` role (is_fixed: true), create permissions: view_pharmacy_categories, add_pharmacy_categories, edit_pharmacy_categories, delete_pharmacy_categories (repeat pattern for brands, product_types, products, pharmacies, inventory, orders), assign ALL permissions to admin role, assign only view/add/edit_pharmacy_inventory + view/edit_pharmacy_orders to pharmacy role
- [X] T025 Create PharmacyDatabaseSeeder in `Modules/Pharmacy/database/seeders/PharmacyDatabaseSeeder.php` — call PharmacyPermissionSeeder, seed sample categories (2 main + 3 sub each), 5 brands, 4 product types, 10 products, 2 pharmacies with inventory for 6 products each
- [X] T026 [P] Create English translations in `lang/en/pharmacy.php` — keys for all labels: lbl_category, lbl_brand, lbl_product_type, lbl_product, lbl_pharmacy, lbl_inventory, lbl_order, lbl_cart, lbl_name_ar, lbl_name_en, lbl_price, lbl_stock_qty, lbl_is_available, lbl_delivery_fee, lbl_status, lbl_subtotal, lbl_total, lbl_payment_method, lbl_requires_prescription, plus status labels and messages
- [X] T027 [P] Create Arabic translations in `lang/ar/pharmacy.php` — Arabic translations for all keys defined in lang/en/pharmacy.php
- [X] T028 [P] Add pharmacy sidebar keys to `lang/en/sidebar.php` — keys: pharmacy_marketplace, pharmacy_categories, pharmacy_brands, pharmacy_product_types, pharmacy_products, pharmacies, pharmacy_orders, pharmacy_inventory. Also add matching Arabic keys to `lang/ar/sidebar.php`
- [X] T029 [P] Create API route skeleton in `Modules/Pharmacy/Routes/api.php` — define route group with prefix `v1/pharmacy`, middleware `auth:sanctum`, placeholder comments for each endpoint group (categories, products, filters, cart, matching, orders)
- [X] T030 [P] Create web route skeleton in `Modules/Pharmacy/Routes/web.php` — define route group with prefix `app`, as `backend.`, middleware `['auth', 'auth_check']`, placeholder comments for admin and pharmacy panel route groups

**Checkpoint**: All models, migrations, permissions, and translations are ready. Run `php artisan module:migrate Pharmacy && php artisan module:seed Pharmacy` to verify.

---

## Phase 3: User Story 1 — Customer Browses and Orders Medication (Priority: P1)

**Goal**: Customer can browse categories → view products → filter → add to cart → select pharmacy → place order via API

**Independent Test**: Seed data, authenticate as customer, call category → product → cart → matching → order APIs end-to-end

### API Transformers

- [X] T031 [P] [US1] Create PharmacyCategoryResource in `Modules/Pharmacy/Transformers/PharmacyCategoryResource.php` — return id, name (locale-aware), name_ar, name_en, image (media URL), sort_order per contracts/api-endpoints.md
- [X] T032 [P] [US1] Create PharmacyProductResource in `Modules/Pharmacy/Transformers/PharmacyProductResource.php` — return id, name, name_ar, name_en, image, product_type (nested id+name), brand (nested id+name), price_from (computed), is_in_stock (computed), requires_prescription per contracts
- [X] T033 [P] [US1] Create PharmacyProductDetailResource in `Modules/Pharmacy/Transformers/PharmacyProductDetailResource.php` — extends product resource with description, category (nested), subcategory (nested)
- [X] T034 [P] [US1] Create PharmacyBrandResource and PharmacyProductTypeResource in `Modules/Pharmacy/Transformers/` — simple id+name resources for filter endpoints
- [X] T035 [P] [US1] Create CartResource in `Modules/Pharmacy/Transformers/CartResource.php` — return items array (each with id, product nested with id/name/image/price_from/is_in_stock, quantity), item_count
- [X] T036 [P] [US1] Create AvailablePharmacyResource in `Modules/Pharmacy/Transformers/AvailablePharmacyResource.php` — return id, name, logo, phone, address, latitude, longitude, subtotal, delivery_fee, total
- [X] T037 [P] [US1] Create PharmacyOrderResource and PharmacyOrderDetailResource in `Modules/Pharmacy/Transformers/` — list: id, pharmacy (nested), total, status, item_count, created_at. Detail: adds items array (product_id, product_name, quantity, price, line_total), subtotal, delivery_fee, payment_method, address (nested)

### Services

- [X] T038 [US1] Implement CartService in `Modules/Pharmacy/Services/CartService.php` — methods: getCart(userId), addItem(userId, productId, quantity) with stock validation (at least one pharmacy has stock_qty >= total quantity per FR-016), updateItem(cartItemId, quantity) with same validation, removeItem(cartItemId), clearCart(userId). Stock check: query MAX(stock_qty) from pharmacy_inventory WHERE product_id = ? AND is_available = true
- [X] T039 [US1] Implement PharmacyMatchingService in `Modules/Pharmacy/Services/PharmacyMatchingService.php` — method: findAvailablePharmacies(userId, addressId). Logic: get cart items, for each active pharmacy check if it stocks ALL cart items with stock_qty >= quantity AND is_available = true, for matched pharmacies compute subtotal (SUM of inventory.price * cart.quantity), delivery_fee (from pharmacy), total. Return empty array with message if no matches (FR-019). Reject if cart empty (FR-019a)
- [X] T040 [US1] Implement OrderService in `Modules/Pharmacy/Services/OrderService.php` — method: placeOrder(userId, pharmacyId, addressId, paymentMethod). Atomic transaction with pessimistic locking (SELECT FOR UPDATE on pharmacy_inventory rows): validate cart non-empty, validate pharmacy stocks all items, create PharmacyOrder, create PharmacyOrderItems (snapshot product_name + price from inventory), decrement stock_qty, clear cart, return order. Method: cancelOrder(orderId, initiator) — validate status is pending (or any status for admin), restore stock for all items, set status to cancelled per FR-029a

### Form Requests

- [X] T041 [P] [US1] Create CartItemRequest in `Modules/Pharmacy/Http/Requests/CartItemRequest.php` — rules: product_id (required, exists:pharmacy_products,id), quantity (required, integer, min:1)
- [X] T042 [P] [US1] Create PharmacyOrderRequest in `Modules/Pharmacy/Http/Requests/PharmacyOrderRequest.php` — rules: pharmacy_id (required, exists:pharmacies,id), address_id (required, exists:addresses,id), payment_method (required, in:cash_on_delivery)

### API Controllers

- [X] T043 [P] [US1] Implement CategoryApiController in `Modules/Pharmacy/Http/Controllers/API/CategoryApiController.php` — index(): return active root categories (parent_id IS NULL) sorted by sort_order using PharmacyCategoryResource. children($id): return active subcategories for given parent using PharmacyCategoryResource, 404 if parent not found
- [X] T044 [P] [US1] Implement ProductApiController in `Modules/Pharmacy/Http/Controllers/API/ProductApiController.php` — index(): paginated listing of active products with optional filters (sub_category_id, search on name_ar/name_en, brand_ids[], product_type_ids[]), include price_from and is_in_stock via inventory subqueries, use PharmacyProductResource. show($id): product detail with 404 fallback, use PharmacyProductDetailResource
- [X] T045 [P] [US1] Implement FilterApiController in `Modules/Pharmacy/Http/Controllers/API/FilterApiController.php` — brands(): return active brands that have products in the given sub_category_id. productTypes(): return product types that have products in the given sub_category_id
- [X] T046 [US1] Implement CartApiController in `Modules/Pharmacy/Http/Controllers/API/CartApiController.php` — index(): get cart via CartService. store(CartItemRequest): add item via CartService, return updated cart. update($id, Request): update quantity via CartService, return updated cart. destroy($id): remove item via CartService
- [X] T047 [US1] Implement PharmacyMatchingApiController in `Modules/Pharmacy/Http/Controllers/API/PharmacyMatchingApiController.php` — index(Request): validate address_id, call PharmacyMatchingService::findAvailablePharmacies, return AvailablePharmacyResource collection with message field for empty results
- [X] T048 [US1] Implement OrderApiController create method in `Modules/Pharmacy/Http/Controllers/API/OrderApiController.php` — store(PharmacyOrderRequest): call OrderService::placeOrder, return 201 with PharmacyOrderDetailResource

### Route Registration

- [X] T049 [US1] Register all customer API routes in `Modules/Pharmacy/Routes/api.php` — within auth:sanctum + prefix v1/pharmacy group: GET categories, GET categories/{id}/children, GET products, GET products/{id}, GET filters/brands, GET filters/product-types, GET cart, POST cart/items, PATCH cart/items/{id}, DELETE cart/items/{id}, GET cart/available-pharmacies, POST orders

**Checkpoint**: Customer can browse → filter → add to cart → find pharmacies → place order. Test with seeded data via API calls per quickstart.md.

---

## Phase 4: User Story 2 — Admin Manages Product Catalog (Priority: P2)

**Goal**: Admin can CRUD categories, brands, product types, products, and pharmacies through the admin panel

**Independent Test**: Log in as admin, create a category hierarchy, create a product, verify it appears in the API

### Form Requests

- [X] T050 [P] [US2] Create admin form requests in `Modules/Pharmacy/Http/Requests/` — PharmacyCategoryRequest.php (name_ar, name_en required, parent_id nullable exists, sort_order integer), PharmacyBrandRequest.php (name required unique), PharmacyProductTypeRequest.php (name_ar, name_en required), PharmacyProductRequest.php (name_ar, name_en, category_id, sub_category_id required, brand_id/product_type_id nullable exists, requires_prescription boolean), PharmacyRequest.php (name, phone, address required, delivery_fee numeric, lat/lng nullable numeric)

### Admin Controllers

- [X] T051 [P] [US2] Implement PharmacyCategoryController in `Modules/Pharmacy/Http/Controllers/PharmacyCategoryController.php` — standard admin methods: index (DataTable list), index_data (server-side JSON for DataTable), create, store, edit, update, destroy, bulk_action, update_status. Constructor sets module_title/module_name/module_path. Support parent/child hierarchy in forms (dropdown for parent_id). Image upload via Spatie MediaLibrary
- [X] T052 [P] [US2] Implement PharmacyBrandController in `Modules/Pharmacy/Http/Controllers/PharmacyBrandController.php` — standard admin CRUD methods: index, index_data, create, store, edit, update, destroy, bulk_action, update_status
- [X] T053 [P] [US2] Implement PharmacyProductTypeController in `Modules/Pharmacy/Http/Controllers/PharmacyProductTypeController.php` — standard admin CRUD methods with bilingual name fields
- [X] T054 [P] [US2] Implement PharmacyProductController in `Modules/Pharmacy/Http/Controllers/PharmacyProductController.php` — standard admin CRUD with: category/subcategory dropdowns (dynamic: subcategory filtered by selected category), brand/product_type optional dropdowns, image upload, requires_prescription toggle. index_data returns product list with category/brand names
- [X] T055 [P] [US2] Implement PharmacyController (admin pharmacy management) in `Modules/Pharmacy/Http/Controllers/PharmacyController.php` — standard admin CRUD with: logo upload via MediaLibrary, lat/lng fields, delivery_fee field. index_data returns pharmacy list with staff count

### Admin Blade Views

- [X] T056 [P] [US2] Create admin Blade views for categories, brands, product-types, products, and pharmacies in `Modules/Pharmacy/Resources/views/backend/` — for each entity: index.blade.php (extends backend.layouts.app, DataTable with columns matching entity fields, bulk_action form, status toggle), create.blade.php (extends backend.layouts.app, form with spatie/laravel-html form builder, used for both create/edit), datatable/action_column.blade.php (edit/delete buttons with permission checks)

### Routes and Menu

- [X] T057 [US2] Register admin web routes in `Modules/Pharmacy/Routes/web.php` — within prefix app, as backend., middleware auth+auth_check: resource routes for pharmacy-categories, pharmacy-brands, pharmacy-product-types, pharmacy-products, pharmacies. Plus index_data, bulk_action, update_status custom routes for each
- [X] T058 [US2] Add admin sidebar menu entries in `app/Http/Middleware/GenerateMenus.php` — for admin/demo_admin role: parent menu "Pharmacy Marketplace" (icon: ph ph-prescription), children: Categories, Brands, Product Types, Products, Pharmacies. Each gated by appropriate view_pharmacy_* permission

**Checkpoint**: Admin can log in, see Pharmacy Marketplace in sidebar, CRUD all catalog entities. Products created here appear in customer API (US1).

---

## Phase 5: User Story 3 — Pharmacy Manages Inventory and Fulfills Orders (Priority: P3)

**Goal**: Pharmacy staff can manage their inventory (add products, set price/stock, toggle availability) and process orders (accept/reject, advance status)

**Independent Test**: Log in as pharmacy user, add product to inventory, verify customer sees it. Place an order as customer, log in as pharmacy, accept and advance through status flow.

### Form Requests and Controllers

- [X] T059 [P] [US3] Create InventoryRequest in `Modules/Pharmacy/Http/Requests/InventoryRequest.php` — rules: product_id (required, exists:pharmacy_products,id), price (required, numeric, min:0.01), stock_qty (required, integer, min:0), is_available (boolean)
- [X] T060 [US3] Implement InventoryController in `Modules/Pharmacy/Http/Controllers/PharmacyPanel/InventoryController.php` — standard admin methods scoped to auth user's pharmacy_id: index (DataTable of own inventory), index_data (paginated JSON filtered by pharmacy_id), create (product dropdown from master catalog excluding already-added), store (create inventory record for own pharmacy), edit, update, destroy, update_status (toggle is_available). All queries scoped with `where('pharmacy_id', auth()->user()->pharmacy_id)`
- [X] T061 [US3] Implement PharmacyPanelOrderController in `Modules/Pharmacy/Http/Controllers/PharmacyPanel/PharmacyPanelOrderController.php` — index (DataTable of orders for own pharmacy), index_data (paginated JSON filtered by pharmacy_id), show (order detail with items), accept($id) (validate pending → set confirmed via OrderService), reject($id) (validate pending → set cancelled + restore stock via OrderService), advance_status($id, Request) (confirmed→preparing→on_the_way→delivered, validate transition is sequential)

### Blade Views

- [X] T062 [P] [US3] Create pharmacy panel Blade views in `Modules/Pharmacy/Resources/views/backend/pharmacy-panel/` — inventory/: index.blade.php (DataTable: product name, price, stock_qty, is_available toggle), create.blade.php (product select2 dropdown, price/stock fields), datatable/action_column.blade.php. orders/: index.blade.php (DataTable: order id, customer name, total, status, action buttons), show.blade.php (order detail with items table, status action buttons: accept/reject for pending, advance for confirmed+), datatable/action_column.blade.php

### Routes and Menu

- [X] T063 [US3] Register pharmacy panel web routes in `Modules/Pharmacy/Routes/web.php` — within prefix app, as backend., middleware auth+auth_check: resource routes for pharmacy-panel/inventory and pharmacy-panel/orders with custom routes (accept, reject, advance_status for orders, update_status for inventory)
- [X] T064 [US3] Add pharmacy role sidebar menu entries in `app/Http/Middleware/GenerateMenus.php` — for users with pharmacy role: parent menu "My Pharmacy" (icon: ph ph-storefront), children: Inventory (gated by view_pharmacy_inventory), Orders (gated by view_pharmacy_orders)

**Checkpoint**: Pharmacy user can log in, see "My Pharmacy" sidebar, manage inventory and process orders. Status changes reflect in customer order history (US1/US4).

---

## Phase 6: User Story 4 — Customer Tracks and Cancels Orders (Priority: P4)

**Goal**: Customer can view their order history, see order details with status, and cancel pending orders with stock restoration

**Independent Test**: Place an order (US1), verify it appears in order list. Cancel it while pending, verify stock restored and status = cancelled. Try cancelling a confirmed order, verify rejection.

- [X] T065 [US4] Add order listing and detail methods to OrderApiController in `Modules/Pharmacy/Http/Controllers/API/OrderApiController.php` — index(): paginated list of authenticated user's orders (newest first) with optional status filter, use PharmacyOrderResource. show($id): order detail with items, validate ownership, use PharmacyOrderDetailResource. Return 404 if not found or not owned
- [X] T066 [US4] Add cancel method to OrderApiController in `Modules/Pharmacy/Http/Controllers/API/OrderApiController.php` — cancel($id): validate order belongs to user, validate status is 'pending', call OrderService::cancelOrder to restore stock and set status to cancelled. Return 422 if status is not pending
- [X] T067 [US4] Register order tracking API routes in `Modules/Pharmacy/Routes/api.php` — within existing auth:sanctum group: GET orders (list), GET orders/{id} (detail), POST orders/{id}/cancel

**Checkpoint**: Customer can view all their orders, see status updates from pharmacy (US3), and cancel pending orders. Full end-to-end flow is now complete.

---

## Phase 7: User Story 2 (continued) — Admin Order Management

**Goal**: Admin can view all orders across all pharmacies and change order status

- [X] T068 [US2] Implement PharmacyOrderController (admin) in `Modules/Pharmacy/Http/Controllers/PharmacyOrderController.php` — index (DataTable of ALL orders), index_data (paginated JSON with user/pharmacy names), show (order detail with items), update_status($id, Request) (admin can set any status value per FR-028, trigger stock restoration if moving to cancelled via OrderService)
- [X] T069 [P] [US2] Create admin order Blade views in `Modules/Pharmacy/Resources/views/backend/orders/` — index.blade.php (DataTable: order id, customer, pharmacy, total, status, date), show.blade.php (order detail with items, status change dropdown), datatable/action_column.blade.php
- [X] T070 [US2] Register admin order routes in `Modules/Pharmacy/Routes/web.php` — add resource route for pharmacy-orders (index, show) with update_status custom route within the existing admin route group. Add "Orders" child menu item under admin Pharmacy Marketplace sidebar in `app/Http/Middleware/GenerateMenus.php`

**Checkpoint**: Admin can view and manage all orders from the admin panel, including changing status of any order.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final integration, validation, and cleanup

- [X] T071 Run `php artisan module:migrate Pharmacy && php artisan module:seed Pharmacy` and verify all tables created with correct schema and sample data populates
- [X] T072 Run `./vendor/bin/pint` to fix code style across all Pharmacy module files, then run `php artisan optimize:clear && composer dump-autoload`
- [X] T073 Validate full end-to-end flow per `specs/010-pharmacy-marketplace/quickstart.md`: browse categories → list products → add to cart → find pharmacies → place order → view orders → cancel order. Verify admin catalog CRUD and pharmacy inventory management work

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 — **BLOCKS all user stories**
- **US1 (Phase 3)**: Depends on Phase 2 — core customer API
- **US2 (Phase 4)**: Depends on Phase 2 — admin panel (can run parallel with US1)
- **US3 (Phase 5)**: Depends on Phase 2 — pharmacy panel (can run parallel with US1/US2, but OrderService from US1 is reused)
- **US4 (Phase 6)**: Depends on Phase 3 (OrderApiController + OrderService created in US1)
- **US2 Orders (Phase 7)**: Depends on Phase 3 (OrderService) and Phase 4 (admin routes/menu)
- **Polish (Phase 8)**: Depends on all previous phases

### User Story Dependencies

```
Phase 1 (Setup)
    └──→ Phase 2 (Foundational)
              ├──→ Phase 3 (US1: Customer API) ──→ Phase 6 (US4: Order Tracking)
              │                                  └──→ Phase 7 (US2: Admin Orders)
              ├──→ Phase 4 (US2: Admin Catalog) ──→ Phase 7 (US2: Admin Orders)
              └──→ Phase 5 (US3: Pharmacy Panel)
                                                     All ──→ Phase 8 (Polish)
```

### Within Each User Story

1. Transformers/Resources before controllers (controllers depend on response format)
2. Services before controllers (controllers delegate to services)
3. Form requests before controllers (controllers use requests for validation)
4. Controllers before routes (routes reference controller methods)
5. Routes before menu entries (menu links to named routes)

### Parallel Opportunities

**Within Phase 2** (Foundational):
- T004, T005, T006, T007 (independent table migrations) — all parallel
- T014 through T022 (all models) — all parallel (different files)
- T026, T027, T028 (translations) — all parallel
- T029, T030 (route skeletons) — parallel

**Within Phase 3** (US1):
- T031 through T037 (all transformers) — all parallel
- T041, T042 (form requests) — parallel
- T043, T044, T045 (category, product, filter controllers) — parallel (no cross-dependencies)

**Across Phases** (after Phase 2):
- Phase 3 (US1), Phase 4 (US2), Phase 5 (US3) — can run in parallel with caveat that US3 and US4 reuse OrderService from US1

---

## Parallel Example: Phase 2 Foundational

```
# Parallel batch 1: Independent migrations
Agent 1: T004 (categories migration)
Agent 2: T005 (brands migration)
Agent 3: T006 (product types migration)
Agent 4: T007 (pharmacies migration)

# Sequential: Dependent migrations (after batch 1)
T008 (products) → T009 (inventory) → T011 (orders) → T012 (order items)
T010 (cart items) — parallel with T011
T013 (users pharmacy_id) — parallel with T011

# Parallel batch 2: All models (after all migrations)
Agent 1: T014 (PharmacyCategory)
Agent 2: T015 (PharmacyBrand) + T016 (PharmacyProductType)
Agent 3: T017 (Pharmacy) + T018 (PharmacyProduct)
Agent 4: T019 (PharmacyInventory) + T020 (PharmacyCartItem)
Agent 5: T021 (PharmacyOrder) + T022 (PharmacyOrderItem)
```

## Parallel Example: Phase 3 US1

```
# Parallel batch 1: All transformers + form requests
Agent 1: T031 + T032 + T033 (category + product resources)
Agent 2: T034 + T035 + T036 + T037 (filter, cart, pharmacy, order resources)
Agent 3: T041 + T042 (form requests)

# Sequential: Services (after models from Phase 2)
T038 (CartService) → T039 (PharmacyMatchingService) → T040 (OrderService)

# Parallel batch 2: Independent controllers (after services + transformers)
Agent 1: T043 (CategoryApiController)
Agent 2: T044 (ProductApiController)
Agent 3: T045 (FilterApiController)

# Sequential: Dependent controllers (after services)
T046 (CartApiController) → T047 (MatchingApiController) → T048 (OrderApiController)

# Final: Routes
T049 (register all API routes)
```

---

## Implementation Strategy

### MVP First (US1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL)
3. Complete Phase 3: US1 — Customer browses and orders
4. **STOP and VALIDATE**: Full customer flow works end-to-end
5. This is a functional MVP — customers can order medications

### Incremental Delivery

1. Phase 1 + 2 → Foundation ready
2. + Phase 3 (US1) → Customer API works → **MVP**
3. + Phase 4 (US2) → Admin can manage catalog → **Admin-ready**
4. + Phase 5 (US3) → Pharmacy staff can manage inventory/orders → **Operations-ready**
5. + Phase 6 (US4) → Customer can track/cancel → **Feature-complete**
6. + Phase 7 + 8 → Admin orders + polish → **Production-ready**

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks in same phase
- [US*] label maps task to specific user story for traceability
- All 9 models extend BaseModel EXCEPT PharmacyCartItem and PharmacyOrderItem (no soft deletes)
- All tables prefixed `pharmacy_` per research decision D-001
- No Filament — admin uses Blade + DataTables per research decision D-002
- Pharmacy panel is same admin shell with role-based menus per research decision D-010
- OrderService uses pessimistic locking (SELECT FOR UPDATE) per research decision D-007
- Commit after each task or logical group
- Run `php artisan optimize:clear` after route or config changes
