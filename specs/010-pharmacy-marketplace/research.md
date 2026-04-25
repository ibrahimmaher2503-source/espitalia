# Research: Pharmacy Marketplace

**Branch**: `010-pharmacy-marketplace` | **Date**: 2026-04-05

## Decision Log

### D-001: Table Naming — Prefix All Tables with `pharmacy_`

**Decision**: All new tables use the `pharmacy_` prefix (e.g., `pharmacy_products`, `pharmacy_orders`, `pharmacy_cart_items`).

**Rationale**: The existing Product module already owns `products`, `orders`, `order_items`, and `carts` tables. Using the same names would cause migration conflicts and model ambiguity.

**Alternatives considered**:
- Reuse Product module tables with a `type` discriminator — rejected because the pharmacy product data model is fundamentally different (no variations, no discount scheduling, no warranty, no galleries) and would require heavy Product module modifications.
- Use short prefix `pharm_` — rejected for readability; `pharmacy_` is clearer.

---

### D-002: No Filament — Use Existing Blade + DataTables Admin UI

**Decision**: Both admin and pharmacy panels use the existing Blade + jQuery + DataTables + Select2 stack. No Filament dependency.

**Rationale**: Constitution Principle V (Simplicity/YAGNI) and Technology Stack Constraints explicitly prohibit introducing new UI libraries when equivalent functionality exists. The pharmacy panel is the same admin shell (`backend.layouts.app`) with role-based menu filtering — identical to how `doctor`, `vendor`, and `receptionist` roles work today.

**Alternatives considered**:
- Install Filament for pharmacy panel — rejected because it contradicts the constitution's fixed technology stack and adds a heavy dependency.
- Separate SPA for pharmacy — rejected for the same reason; overkill for CRUD + order management.

---

### D-003: Pharmacy User Linkage via `pharmacy_id` on Users Table

**Decision**: Add a nullable `pharmacy_id` foreign key column to the `users` table. Pharmacy-role users are linked to their pharmacy through this FK.

**Rationale**: Follows the existing pattern where Doctor model links via `doctor_id` on the doctors table pointing to `users.id`. Here we reverse it: `users.pharmacy_id` points to `pharmacies.id`. This is simpler because:
- A pharmacy staff user belongs to exactly one pharmacy (spec clarification).
- Multiple users can share the same `pharmacy_id` (1:many).
- Role-based scoping queries (e.g., `where('pharmacy_id', auth()->user()->pharmacy_id)`) are simple and efficient.

**Alternatives considered**:
- Separate `pharmacy_staff` pivot table — rejected; unnecessary for a 1:many relationship where each user belongs to at most one pharmacy.

---

### D-004: Address Model — Use Existing Polymorphic Addresses Table

**Decision**: Customer delivery addresses use the existing `addresses` table (polymorphic: `addressable_type` + `addressable_id`). The order stores `address_id` as a snapshot reference.

**Rationale**: The `addresses` table already exists with latitude/longitude, address fields, and `is_primary` flag. It uses Laravel's polymorphic morphable pattern. Customers already have address records through other flows.

**Alternatives considered**:
- Inline address fields on the order — rejected because customers reuse addresses across orders.
- Separate `pharmacy_addresses` table — rejected; duplicates existing functionality.

---

### D-005: Pharmacy Role — New `pharmacy` Role via Spatie Permissions

**Decision**: Create a new `pharmacy` role in `spatie/laravel-permission`. Permissions follow the convention: `view_pharmacy_*`, `add_pharmacy_*`, `edit_pharmacy_*`, `delete_pharmacy_*`.

**Rationale**: Follows Constitution Principle III (Role-Based Access Control). Each module creates its own permissions via a seeder. The `pharmacy` role gets inventory and order permissions only; `admin` gets all pharmacy permissions.

**Alternatives considered**:
- Reuse `vendor` role — rejected because pharmacy operations are distinct from vendor product selling; would overload the vendor role with unrelated permissions.

---

### D-006: Cart Implementation — Database-Backed per User

**Decision**: Cart is stored in `pharmacy_cart_items` table (one row per product per user). No separate "cart" entity — the user IS the cart owner.

**Rationale**: The spec defines a single active cart per user (FR-018). Storing cart items directly with `user_id` is sufficient. No cart header record needed since there's no multi-cart or guest-cart requirement.

**Alternatives considered**:
- Session-based cart — rejected because the API is for a mobile app; sessions don't persist across devices.
- Cart header + items tables — rejected; unnecessary complexity for single-cart-per-user.

---

### D-007: Stock Concurrency — Database Row-Level Locking

**Decision**: Use `SELECT ... FOR UPDATE` (pessimistic locking) within a database transaction during order placement to prevent overselling.

**Rationale**: SC-008 requires that concurrent orders for the last stock item don't result in negative stock. Pessimistic locking within the order transaction is the simplest reliable approach. The order placement flow: `BEGIN → lock inventory rows → validate stock → create order + items → decrement stock → clear cart → COMMIT`.

**Alternatives considered**:
- Optimistic locking (version column) — rejected because retry logic adds complexity and the critical section is small enough for pessimistic locking.
- Queue-based serialization — rejected; overkill for MVP scale.

---

### D-008: Order Payment Method — Enum String Field

**Decision**: Store `payment_method` as a string enum on the `pharmacy_orders` table. MVP supports only `cash_on_delivery`. Field is designed to accept future values without migration.

**Rationale**: The spec states payment processing is out of scope for MVP, but the field must exist on the order. A string field with validation is simpler than an enum column (which requires migration to add values).

**Alternatives considered**:
- Database-level ENUM — rejected; adding new payment methods requires migration.
- Separate payment records table — rejected; no payment processing in MVP.

---

### D-009: `price_from` Computation — Query-Time Aggregation

**Decision**: The `price_from` field on product listings is computed at query time via a subquery: `MIN(pharmacy_inventory.price) WHERE stock_qty > 0 AND is_available = true`.

**Rationale**: Ensures price accuracy in real-time. Given that product listing queries are paginated (typically 10-20 items), the subquery overhead per page is negligible. Pharmacy matching (FR-019) already requires joining inventory, so the price subquery adds minimal cost.

**Alternatives considered**:
- Cached `price_from` column on `pharmacy_products` — rejected; requires cache invalidation on every inventory price change, adding complexity.
- Separate pricing endpoint — rejected; forces two API calls for product listing.

---

### D-010: Admin Panel Structure — Shared Admin Shell with Role-Based Menus

**Decision**: The pharmacy panel is NOT a separate application. Pharmacy staff log into the same admin panel (`/app/`) and see pharmacy-specific sidebar items based on their `pharmacy` role and permissions. Admin controllers for pharmacy panel live under `Http/Controllers/PharmacyPanel/`.

**Rationale**: This is exactly how `doctor`, `receptionist`, and `vendor` roles work in the existing system — same admin shell, different menu items. No new route prefixes or layouts needed.

**Alternatives considered**:
- Separate `/pharmacy/` route prefix — rejected; deviates from existing conventions where all admin routes use `/app/`.
