# Service Offers / Promotions System — Complete Feature Specification

## A. MODULE OVERVIEW

### What It Is
A centralized **Service Offers & Promotions** engine that manages promotional pricing across every bookable service in the Espitalia platform. It replaces the existing limited `Promotion/Coupon` module with a comprehensive, rule-based system that supports percentage discounts, fixed discounts, override pricing, bundle deals, buy-X-get-Y, flash sales, coupon codes, auto-apply logic, branch/service/category/customer targeting, scheduling, usage caps, and analytics.

### Why It Exists
The current system has two disconnected discount mechanisms:
1. **Service-level discounts** — ClinicsService and DoctorIndependentService each store `discount`, `discount_value`, `discount_type` directly on the model. These are static, always-on, and have no targeting, scheduling, or usage tracking.
2. **Promotion/Coupon module** — A simple `promotions` + `promotions_coupon` + `user_coupon_redeem` setup with basic percentage/fixed discounts and per-code use limits. No service targeting, no branch scoping, no customer segmentation, no auto-apply, no stacking rules, no analytics.

The new Offers module unifies all promotional pricing under one system with rich targeting rules, audit trail, real-time validation, and analytics — suitable for a multi-vendor, multi-branch, multi-service healthcare SaaS.

### Modules / Entities Affected

| Module | Impact |
|--------|--------|
| Appointment | Booking creation applies offer discounts to `service_amount`, `total_amount`; AppointmentTransaction records `discount_type`, `discount_value`, `discount_amount` plus new `offer_id` reference |
| Clinic | ClinicsService pricing displayed with offer overlays; DoctorServiceMapping charges affected |
| Service | ServicePackage pricing affected by bundle offers |
| Branch | Branch-scoped offers filter by `branch_id` on clinics |
| Customer | Customer booking history queried for first-time / repeat eligibility |
| Promotion (legacy) | Deprecated — migrated to Offers module |
| Commission | Commission calculation uses pre-discount service amount (configurable) |
| Tax | Tax applied on final discounted amount or original amount (configurable per tax) |
| Frontend | Service listing, details, booking form, checkout all display offer badges |
| RequestNurse | NurseRequest `total_amount` affected by applicable offers |
| RadiologyLab | TestOrder `total_amount` / `discount_amount` / `final_amount` affected |
| DoctorVisit | DoctorVisitRequest pricing affected |

### Roles That Manage Offers

| Role | Capabilities |
|------|-------------|
| admin | Full CRUD, analytics, bulk actions, global offers |
| demo_admin | Full CRUD (read-only on delete in demo mode) |
| vendor | CRUD for own clinic/branch offers only; cannot create global or cross-vendor offers |
| receptionist | View offers; apply coupon codes during booking |
| user (patient) | View public offers; apply coupon codes; see discounted prices |

### API / Frontend Flows That Consume Offers

1. **Service Listing API** — returns `active_offer` badge (best applicable auto-apply offer) per service
2. **Service Detail API** — returns all applicable offers with calculated discount preview
3. **Booking/Checkout API** — validates and applies offers, calculates final price
4. **Coupon Validation API** — validates a coupon code against a specific service + branch + customer
5. **Admin Offer CRUD** — full management panel
6. **Vendor Offer CRUD** — scoped to vendor's clinics/branches
7. **Public Offers Feed** — featured/active offers for marketing pages

---

## B. ENTITIES AND SCHEMA

### B.1 — `offers` (Primary Entity)

**Purpose:** Stores each promotional offer with its discount rules, scheduling, targeting scope, and usage limits.

```sql
CREATE TABLE offers (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Identity
    title_ar                    VARCHAR(255) NOT NULL,
    title_en                    VARCHAR(255) NOT NULL,
    description_ar              TEXT NULL,
    description_en              TEXT NULL,
    short_label_ar              VARCHAR(100) NULL,  -- "20% OFF", "خصم ٢٠٪"
    short_label_en              VARCHAR(100) NULL,
    terms_ar                    TEXT NULL,
    terms_en                    TEXT NULL,
    banner_text_ar              VARCHAR(255) NULL,
    banner_text_en              VARCHAR(255) NULL,
    slug                        VARCHAR(255) NULL UNIQUE,
    internal_name               VARCHAR(255) NULL,  -- admin-facing label

    -- Discount Configuration
    discount_type               VARCHAR(30) NOT NULL,
        -- 'percentage', 'fixed_amount', 'override_price',
        -- 'bundle_price', 'buy_x_get_y', 'free_addon'
    discount_value              DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_discount_amount         DECIMAL(12,2) NULL,       -- cap for percentage discounts
    minimum_order_amount        DECIMAL(12,2) DEFAULT 0,  -- min service charge to qualify
    override_price              DECIMAL(12,2) NULL,       -- for 'override_price' type
    buy_quantity                INT UNSIGNED NULL,         -- X in buy-X-get-Y
    get_quantity                INT UNSIGNED NULL,         -- Y in buy-X-get-Y
    get_service_id              BIGINT UNSIGNED NULL,      -- free/discounted service in buy_x_get_y
    get_discount_percentage     DECIMAL(5,2) NULL,         -- discount on the "get" item (100 = free)

    -- Coupon
    code                        VARCHAR(50) NULL UNIQUE,   -- NULL = not coupon-based
    requires_coupon             TINYINT(1) DEFAULT 0,
    auto_apply                  TINYINT(1) DEFAULT 0,      -- auto-applied without code

    -- Targeting Scope
    target_scope                VARCHAR(30) DEFAULT 'global',
        -- 'global', 'service', 'category', 'branch', 'package', 'vendor'
    applies_to_all_services     TINYINT(1) DEFAULT 1,
    applies_to_all_branches     TINYINT(1) DEFAULT 1,
    applies_to_all_categories   TINYINT(1) DEFAULT 1,
    vendor_id                   BIGINT UNSIGNED NULL,      -- NULL = platform-wide

    -- Customer Eligibility
    customer_scope              VARCHAR(30) DEFAULT 'all',
        -- 'all', 'first_time', 'returning', 'segment'
    first_booking_only          TINYINT(1) DEFAULT 0,
    min_completed_bookings      INT UNSIGNED DEFAULT 0,    -- for loyalty offers
    max_completed_bookings      INT UNSIGNED NULL,         -- NULL = no upper cap

    -- Scheduling
    starts_at                   TIMESTAMP NULL,
    ends_at                     TIMESTAMP NULL,
    is_flash                    TINYINT(1) DEFAULT 0,      -- flash sale flag

    -- Usage Limits
    usage_limit_total           INT UNSIGNED NULL,          -- NULL = unlimited
    usage_limit_per_user        INT UNSIGNED DEFAULT 1,
    used_count                  INT UNSIGNED DEFAULT 0,

    -- Priority & Stacking
    priority                    INT DEFAULT 0,              -- higher = more priority
    stackable                   TINYINT(1) DEFAULT 0,
    exclusive_group             VARCHAR(50) NULL,           -- offers in same group are mutually exclusive

    -- Display
    is_active                   TINYINT(1) DEFAULT 1,
    is_featured                 TINYINT(1) DEFAULT 0,
    status                      VARCHAR(20) DEFAULT 'draft',
        -- 'draft', 'scheduled', 'active', 'paused', 'expired', 'exhausted'

    -- Booking Channel
    allowed_channels            JSON NULL,  -- ['web', 'mobile', 'reception'] or NULL = all
    allowed_payment_methods     JSON NULL,  -- ['cash', 'online', 'advance'] or NULL = all

    -- Commission
    commission_on_original      TINYINT(1) DEFAULT 1,  -- calculate commission on pre-discount amount

    -- Audit
    created_by                  BIGINT UNSIGNED NULL,
    updated_by                  BIGINT UNSIGNED NULL,
    deleted_by                  BIGINT UNSIGNED NULL,
    created_at                  TIMESTAMP NULL,
    updated_at                  TIMESTAMP NULL,
    deleted_at                  TIMESTAMP NULL,

    INDEX idx_offers_status (status),
    INDEX idx_offers_active (is_active),
    INDEX idx_offers_code (code),
    INDEX idx_offers_dates (starts_at, ends_at),
    INDEX idx_offers_vendor (vendor_id),
    INDEX idx_offers_priority (priority DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### B.2 — `offer_services` (Pivot)

**Purpose:** Links an offer to specific `clinics_services` rows.

```sql
CREATE TABLE offer_services (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id            BIGINT UNSIGNED NOT NULL,
    service_id          BIGINT UNSIGNED NOT NULL,  -- FK -> clinics_services.id
    created_at          TIMESTAMP NULL,

    UNIQUE KEY uq_offer_service (offer_id, service_id),
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B.3 — `offer_branches` (Pivot)

**Purpose:** Links an offer to specific branches.

```sql
CREATE TABLE offer_branches (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id            BIGINT UNSIGNED NOT NULL,
    branch_id           BIGINT UNSIGNED NOT NULL,  -- FK -> branches.id
    created_at          TIMESTAMP NULL,

    UNIQUE KEY uq_offer_branch (offer_id, branch_id),
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B.4 — `offer_categories` (Pivot)

**Purpose:** Links an offer to service categories.

```sql
CREATE TABLE offer_categories (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id            BIGINT UNSIGNED NOT NULL,
    category_id         BIGINT UNSIGNED NOT NULL,  -- FK -> clinics_categories.id
    created_at          TIMESTAMP NULL,

    UNIQUE KEY uq_offer_category (offer_id, category_id),
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B.5 — `offer_customer_segments` (Pivot)

**Purpose:** Links an offer to customer segments / user groups.

```sql
CREATE TABLE offer_customer_segments (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id            BIGINT UNSIGNED NOT NULL,
    segment_type        VARCHAR(30) NOT NULL,
        -- 'role', 'tag', 'subscription_plan', 'custom'
    segment_value       VARCHAR(100) NOT NULL,
        -- role name, tag string, plan_id, etc.
    created_at          TIMESTAMP NULL,

    UNIQUE KEY uq_offer_segment (offer_id, segment_type, segment_value),
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B.6 — `offer_schedules` (Time-Window Rules)

**Purpose:** Defines recurring time windows when an offer is valid (e.g., "weekdays 9-12", "Fridays only").

```sql
CREATE TABLE offer_schedules (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id            BIGINT UNSIGNED NOT NULL,
    day_of_week         TINYINT UNSIGNED NULL,  -- 0=Sun, 6=Sat; NULL = every day
    start_time          TIME NULL,              -- NULL = all day
    end_time            TIME NULL,
    created_at          TIMESTAMP NULL,

    INDEX idx_schedule_offer (offer_id),
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B.7 — `offer_usages` (Redemption Tracking)

**Purpose:** Records each time an offer is applied to a booking. Replaces `user_coupon_redeem`.

```sql
CREATE TABLE offer_usages (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id            BIGINT UNSIGNED NOT NULL,
    user_id             BIGINT UNSIGNED NOT NULL,
    booking_type        VARCHAR(50) NOT NULL,
        -- 'appointment', 'nurse_request', 'test_order', 'doctor_visit'
    booking_id          BIGINT UNSIGNED NOT NULL,
    service_id          BIGINT UNSIGNED NULL,
    branch_id           BIGINT UNSIGNED NULL,
    original_amount     DECIMAL(12,2) NOT NULL,
    discount_amount     DECIMAL(12,2) NOT NULL,
    final_amount        DECIMAL(12,2) NOT NULL,
    coupon_code         VARCHAR(50) NULL,
    status              VARCHAR(20) DEFAULT 'applied',
        -- 'applied', 'completed', 'reversed', 'cancelled'
    reversed_at         TIMESTAMP NULL,
    reversal_reason     VARCHAR(255) NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    INDEX idx_usage_offer (offer_id),
    INDEX idx_usage_user (user_id),
    INDEX idx_usage_booking (booking_type, booking_id),
    INDEX idx_usage_status (status),
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B.8 — `offer_audit_logs`

**Purpose:** Tracks admin actions on offers for compliance.

```sql
CREATE TABLE offer_audit_logs (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id            BIGINT UNSIGNED NOT NULL,
    user_id             BIGINT UNSIGNED NULL,
    action              VARCHAR(50) NOT NULL,
        -- 'created', 'updated', 'activated', 'paused', 'expired',
        -- 'deleted', 'status_changed', 'usage_reversed'
    old_values          JSON NULL,
    new_values          JSON NULL,
    ip_address          VARCHAR(45) NULL,
    user_agent          VARCHAR(500) NULL,
    created_at          TIMESTAMP NULL,

    INDEX idx_audit_offer (offer_id),
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### B.9 — Booking Table Modifications

Add to `appointments`:
```sql
ALTER TABLE appointments ADD COLUMN offer_id BIGINT UNSIGNED NULL AFTER cancellation_charge_amount;
ALTER TABLE appointments ADD COLUMN offer_discount DECIMAL(12,2) DEFAULT 0 AFTER offer_id;
ALTER TABLE appointments ADD COLUMN offer_code VARCHAR(50) NULL AFTER offer_discount;
```

Add to `nurse_requests`:
```sql
ALTER TABLE nurse_requests ADD COLUMN offer_id BIGINT UNSIGNED NULL;
ALTER TABLE nurse_requests ADD COLUMN offer_discount DECIMAL(12,2) DEFAULT 0;
ALTER TABLE nurse_requests ADD COLUMN offer_code VARCHAR(50) NULL;
```

Add to `test_orders`:
```sql
ALTER TABLE test_orders ADD COLUMN offer_id BIGINT UNSIGNED NULL;
ALTER TABLE test_orders ADD COLUMN offer_discount DECIMAL(12,2) DEFAULT 0;
ALTER TABLE test_orders ADD COLUMN offer_code VARCHAR(50) NULL;
```

Add to `doctor_visit_requests`:
```sql
ALTER TABLE doctor_visit_requests ADD COLUMN offer_id BIGINT UNSIGNED NULL;
ALTER TABLE doctor_visit_requests ADD COLUMN offer_discount DECIMAL(12,2) DEFAULT 0;
ALTER TABLE doctor_visit_requests ADD COLUMN offer_code VARCHAR(50) NULL;
```

---

## C. OFFER TYPES

### C.1 — Percentage Discount
- **discount_type:** `percentage`
- **Calculation:** `discount = service_charge × (discount_value / 100)`, capped by `max_discount_amount`
- **Validation:** `discount_value` must be 1–100; `max_discount_amount` optional
- **Edge cases:** If multiple services in a package, applies per-service; round to 2 decimal places (round half up)

### C.2 — Fixed Amount Discount
- **discount_type:** `fixed_amount`
- **Calculation:** `discount = min(discount_value, service_charge)` — never reduces below zero
- **Validation:** `discount_value > 0`
- **Edge cases:** If `discount_value > service_charge`, discount = service_charge (service becomes free, no negative)

### C.3 — Override Price (Promotional Price)
- **discount_type:** `override_price`
- **Calculation:** `discount = service_charge - override_price`; final price = `override_price`
- **Validation:** `override_price >= 0`; must target specific services (not global)
- **Edge cases:** If `override_price > service_charge`, no discount applied (offer is informational only). Cannot override if `service_charge` is 0.

### C.4 — Bundle Price
- **discount_type:** `bundle_price`
- **Calculation:** The entire service package / bundle is sold at `discount_value` as the total price
- **Validation:** Must target a `ServicePackage` (target_scope = 'package'); `discount_value` = bundle price
- **Edge cases:** Individual service prices within the bundle are prorated proportionally for commission calculation

### C.5 — Buy X Get Y
- **discount_type:** `buy_x_get_y`
- **Calculation:** Book `buy_quantity` services, get `get_quantity` services at `get_discount_percentage`% off. If `get_discount_percentage = 100`, the "get" service is free.
- **Validation:** `buy_quantity >= 1`, `get_quantity >= 1`, `get_discount_percentage` between 0–100. If `get_service_id` is set, the free/discounted service is that specific service; otherwise it's the cheapest of the booked services.
- **Edge cases:** Only applicable when booking multiple services in a single appointment or encounter. The "get" discount applies to the lowest-priced qualifying service.

### C.6 — Free Add-on Service
- **discount_type:** `free_addon`
- **Calculation:** When a qualifying service is booked, `get_service_id` is added at zero cost
- **Validation:** `get_service_id` required; the add-on service must exist and be active
- **Edge cases:** The add-on must be shown on the invoice at full price with 100% discount line

### C.7 — Compatibility Matrix

| Type | Stackable with % | Stackable with Fixed | Stackable with Override | Auto-Apply | Coupon |
|------|:-:|:-:|:-:|:-:|:-:|
| percentage | Yes (if stackable flag) | Yes | No | Yes | Yes |
| fixed_amount | Yes | No | No | Yes | Yes |
| override_price | No | No | No | Yes | Yes |
| bundle_price | No | No | No | Yes | No |
| buy_x_get_y | No | No | No | Yes | Yes |
| free_addon | Yes | Yes | No | Yes | Yes |

---

## D. APPLICABILITY RULES

### D.1 — Service Targeting
- `applies_to_all_services = true` → offer valid for any service
- `applies_to_all_services = false` → only services in `offer_services` pivot

### D.2 — Branch Targeting
- `applies_to_all_branches = true` → offer valid at any branch/clinic
- `applies_to_all_branches = false` → only branches in `offer_branches` pivot
- Branch is resolved from the clinic's `branch_id` during booking

### D.3 — Category Targeting
- `applies_to_all_categories = true` → any category
- `applies_to_all_categories = false` → only categories in `offer_categories`
- Matched against `ClinicsService.category_id`

### D.4 — Vendor Scoping
- `vendor_id IS NULL` → platform-wide offer (admin only)
- `vendor_id IS NOT NULL` → vendor-specific (vendor or admin can create)

### D.5 — Customer Eligibility
- `customer_scope = 'all'` → everyone
- `customer_scope = 'first_time'` → user has zero completed+paid bookings across the system
- `customer_scope = 'returning'` → user has `>= min_completed_bookings` completed bookings
- `customer_scope = 'segment'` → user matches at least one row in `offer_customer_segments`
- `first_booking_only = true` → stricter: no prior booking **for this specific service** (not system-wide)

### D.6 — Time Schedule
- `starts_at` / `ends_at` → absolute date range
- `offer_schedules` → recurring day/time windows within the active period
- Both must pass: the current time must be within `starts_at..ends_at` AND match at least one schedule rule (if any exist; if none exist, all times within the date range qualify)

### D.7 — Channel & Payment
- `allowed_channels` → `['web', 'mobile', 'reception']` or NULL (all)
- `allowed_payment_methods` → `['cash', 'online']` or NULL (all)
- Validated at booking creation time

### D.8 — Minimum Amount
- `minimum_order_amount` → the service charge (pre-discount) must meet this threshold

---

## E. VALIDATION ENGINE

### OfferEligibilityService

```
class OfferEligibilityService
{
    /**
     * Returns all valid offers for a given booking context.
     *
     * @param int      $userId
     * @param int      $serviceId        ClinicsService ID
     * @param int|null $branchId
     * @param int|null $clinicId
     * @param float    $serviceCharge    Pre-discount price
     * @param string   $channel          'web'|'mobile'|'reception'
     * @param string|null $paymentMethod
     * @param string|null $couponCode
     * @return Collection<Offer>
     */
    public function getEligibleOffers(...): Collection

    /**
     * Validates a single offer against booking context.
     * Returns ['valid' => bool, 'reason' => string|null]
     */
    public function validateOffer(Offer $offer, array $context): array

    /**
     * Validates a coupon code and returns the offer if valid.
     */
    public function validateCoupon(string $code, array $context): ?Offer
}
```

### Validation Pipeline (sequential — short-circuits on first failure)

1. **Status Check** — `is_active = true` AND `status = 'active'`
2. **Date Range** — `starts_at <= now() <= ends_at` (NULLs treated as unbounded)
3. **Schedule Check** — If `offer_schedules` exist, current day/time must match at least one
4. **Vendor Check** — If `vendor_id` is set, the clinic's vendor must match
5. **Branch Check** — If `applies_to_all_branches = false`, booking's branch must be in `offer_branches`
6. **Service Check** — If `applies_to_all_services = false`, service must be in `offer_services`
7. **Category Check** — If `applies_to_all_categories = false`, service's category must be in `offer_categories`
8. **Customer Scope** — Check `customer_scope` against user's booking history
9. **First Booking Only** — If `first_booking_only = true`, verify no prior completed booking for this service by this user
10. **Minimum Amount** — `service_charge >= minimum_order_amount`
11. **Coupon Check** — If `requires_coupon = true`, a matching `code` must be provided
12. **Channel Check** — If `allowed_channels` is set, booking channel must match
13. **Payment Method** — If `allowed_payment_methods` is set, payment method must match
14. **Usage Limit (Total)** — `used_count < usage_limit_total` (if set)
15. **Usage Limit (Per-User)** — count of `offer_usages` for this user + offer < `usage_limit_per_user`
16. **Segment Check** — If `customer_scope = 'segment'`, user must match at least one segment rule

---

## F. PRICE CALCULATION LOGIC

### OfferPricingService

```
class OfferPricingService
{
    /**
     * Calculate the best applicable discount for a booking line item.
     *
     * @return array{
     *   original_price: float,
     *   discount_amount: float,
     *   discounted_price: float,
     *   applied_offer_id: int|null,
     *   applied_offer_code: string|null,
     *   applied_offer_title: string,
     *   discount_breakdown: array
     * }
     */
    public function calculateDiscount(
        float $serviceCharge,
        Collection $eligibleOffers,
        array $context
    ): array
}
```

### Calculation Flow

1. **Filter eligible offers** — from `OfferEligibilityService::getEligibleOffers()`
2. **Separate by stackability:**
   - Non-stackable offers → select best one by: priority DESC, then discount_amount DESC
   - Stackable offers → accumulate all discounts
3. **Calculate each offer's discount:**
   - `percentage`: `round(serviceCharge × value / 100, 2)`, cap at `max_discount_amount`
   - `fixed_amount`: `min(value, serviceCharge)`
   - `override_price`: `max(0, serviceCharge - override_price)`
   - `bundle_price`: `max(0, packageTotal - bundlePrice)` (prorated across services)
   - `buy_x_get_y`: discount on the cheapest qualifying service(s)
   - `free_addon`: 0 on the original service; the add-on service shows full price minus 100% discount
4. **Apply stacking rules:**
   - Best non-stackable offer discount = D1
   - Sum of stackable offer discounts = D2
   - If D1 > D2 → use non-stackable; else sum stackable
   - Exception: `exclusive_group` — only one offer per group
5. **Final calculation:**
   - `discount_amount = min(totalDiscount, serviceCharge)` — never go below zero
   - `discounted_price = serviceCharge - discount_amount`
   - Tax computed on `discounted_price` (unless `is_inclusive_tax` applies)
   - Commission computed on `serviceCharge` if `commission_on_original = true`, else on `discounted_price`
6. **Rounding:** Round to 2 decimal places using PHP_ROUND_HALF_UP at each step

---

## G. SERVICE BOOKING / ORDER FLOW IMPACT

### G.1 — Service Listing Page
- Each service card shows a badge if an auto-apply offer exists: "20% OFF" / short_label
- Original price shown with strikethrough; discounted price shown
- Featured offers appear in a carousel/banner section

### G.2 — Service Details Page
- All applicable offers listed in an "Available Offers" section
- Each offer shows: title, short_label, terms, validity period
- "Apply" button for coupon-based offers (opens code input)
- Auto-apply offers shown as already applied

### G.3 — Booking Form
- Coupon code input field
- "Apply Coupon" button with real-time validation
- Price breakdown shows: service charge, offer discount, tax, total
- If coupon invalid: inline error message with reason

### G.4 — Checkout / Payment
- Final price includes all applied discounts
- `AppointmentTransaction` records: `discount_type`, `discount_value`, `discount_amount`
- `Appointment` records: `offer_id`, `offer_discount`, `offer_code`
- `offer_usages` row created
- `offers.used_count` incremented atomically

### G.5 — Invoice / Receipt
- Line item shows: original price, discount line (with offer title), final price
- Coupon code displayed if used

### G.6 — Cancellation
- If booking is cancelled:
  - `offer_usages.status` → 'cancelled'
  - `offers.used_count` decremented (if `cancel_releases_usage` business rule is enabled)
  - Refund calculated on the actually paid amount (post-discount), not original

### G.7 — Rescheduling
- If rescheduled to a date outside offer validity → offer discount is removed; patient notified of price change
- If still within validity → offer remains applied

---

## H. ADMIN PANEL PAGES

### H.1 — Offers List Page (`/app/offers`)

| Column | Description |
|--------|-------------|
| Checkbox | Bulk select |
| Title | `title_en` with `title_ar` subtitle |
| Code | Coupon code or "Auto-apply" badge |
| Type | Discount type badge |
| Value | e.g., "20%" or "50 EGP" |
| Scope | "Global" / "3 services" / "2 branches" |
| Valid Period | starts_at — ends_at |
| Usage | `used_count / usage_limit_total` |
| Status | Draft / Active / Paused / Expired / Exhausted |
| Actions | View, Edit, Duplicate, Toggle Status, Delete |

**Filters:** status, discount_type, vendor, branch, date range, is_featured, has_coupon
**Bulk actions:** activate, pause, delete

### H.2 — Create/Edit Offer Page (`/app/offers/create`, `/app/offers/{id}/edit`)

**Sections:**

1. **Basic Info** — internal_name, slug (auto-generated)
2. **Bilingual Content** — title_ar/en, description_ar/en, short_label_ar/en, terms_ar/en, banner_text_ar/en (side-by-side AR/EN inputs)
3. **Discount Settings** — discount_type (dropdown), discount_value, max_discount_amount, override_price, buy/get quantities, get_service_id, get_discount_percentage
4. **Applicability Scope** — target_scope, applies_to_all_services/branches/categories toggles, service picker (searchable multi-select), branch picker, category picker
5. **Vendor Scope** — vendor_id (admin only; auto-set for vendor role)
6. **Schedule** — starts_at, ends_at, is_flash toggle, recurring schedule builder (day/time grid)
7. **Customer Eligibility** — customer_scope dropdown, first_booking_only, min/max completed bookings, segment rules
8. **Coupon Settings** — requires_coupon toggle, code input (with "Generate Random" button), auto_apply toggle
9. **Usage Limits** — usage_limit_total, usage_limit_per_user
10. **Priority & Stacking** — priority (0–100 slider), stackable toggle, exclusive_group
11. **Channel & Payment** — allowed_channels checkboxes, allowed_payment_methods checkboxes
12. **Display Settings** — is_featured, banner image upload
13. **Commission** — commission_on_original toggle
14. **Activation** — is_active toggle, status selector

### H.3 — Offer Details Page (`/app/offers/{id}`)
- Full offer info rendered read-only
- Linked services, branches, categories listed
- Usage statistics: total uses, unique users, total discount given
- Recent redemption log (last 20)
- Quick actions: Edit, Duplicate, Toggle Status

### H.4 — Offer Analytics Page (`/app/offers/{id}/analytics`)
- Redemptions over time (line chart)
- Revenue impact (original vs discounted totals)
- Top services using this offer
- Top branches
- Customer breakdown (new vs returning)

### H.5 — Offer Redemption Logs (`/app/offers/usage-logs`)

| Column | Description |
|--------|-------------|
| Date | created_at |
| Offer | title_en + code |
| Customer | user name + email |
| Booking | booking_type + booking_id link |
| Service | service name |
| Branch | branch name |
| Original | original_amount |
| Discount | discount_amount |
| Final | final_amount |
| Status | applied / completed / reversed / cancelled |

**Filters:** offer, date range, status, branch, customer

---

## I. API ENDPOINTS

### I.1 — Admin Offer Management

| Method | URI | Auth | Permission | Description |
|--------|-----|------|------------|-------------|
| GET | `/api/v1/offers` | sanctum | view_offers | List offers (paginated, filterable) |
| GET | `/api/v1/offers/{id}` | sanctum | view_offers | Show offer detail |
| POST | `/api/v1/offers` | sanctum | add_offers | Create offer |
| PUT | `/api/v1/offers/{id}` | sanctum | edit_offers | Update offer |
| DELETE | `/api/v1/offers/{id}` | sanctum | delete_offers | Soft-delete offer |
| POST | `/api/v1/offers/{id}/update-status` | sanctum | edit_offers | Toggle active/paused |
| POST | `/api/v1/offers/bulk-action` | sanctum | delete_offers | Bulk activate/pause/delete |
| GET | `/api/v1/offers/{id}/usage-logs` | sanctum | view_offer_analytics | Redemption logs for one offer |
| GET | `/api/v1/offers/usage-logs` | sanctum | view_offer_analytics | All redemption logs |
| POST | `/api/v1/offers/{id}/duplicate` | sanctum | add_offers | Clone an offer |

### I.2 — Admin Web Routes (Backend Panel)

| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/app/offers` | backend.offers.index | OfferController@index |
| GET | `/app/offers/index-data` | backend.offers.index_data | OfferController@index_data |
| GET | `/app/offers/create` | backend.offers.create | OfferController@create |
| POST | `/app/offers` | backend.offers.store | OfferController@store |
| GET | `/app/offers/{id}/edit` | backend.offers.edit | OfferController@edit |
| PUT | `/app/offers/{id}` | backend.offers.update | OfferController@update |
| DELETE | `/app/offers/{id}` | backend.offers.destroy | OfferController@destroy |
| POST | `/app/offers/{id}/update-status` | backend.offers.update_status | OfferController@update_status |
| POST | `/app/offers/bulk-action` | backend.offers.bulk_action | OfferController@bulk_action |
| GET | `/app/offers/{id}` | backend.offers.show | OfferController@show |
| GET | `/app/offer-analytics` | backend.offer-analytics.index | OfferAnalyticsController@index |
| GET | `/app/offer-analytics/data` | backend.offer-analytics.data | OfferAnalyticsController@data |
| GET | `/app/offer-usage-logs` | backend.offer-usage-logs.index | OfferUsageLogController@index |
| GET | `/app/offer-usage-logs/index-data` | backend.offer-usage-logs.index_data | OfferUsageLogController@index_data |

### I.3 — Public / Customer-Facing APIs

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| GET | `/api/v1/offers/public` | none | List active, featured, public offers (paginated) |
| GET | `/api/v1/offers/public/{slug}` | none | Single offer by slug |
| GET | `/api/v1/services/{id}/offers` | optional | Eligible offers for a specific service |
| POST | `/api/v1/offers/validate-coupon` | sanctum | Validate a coupon code against booking context |
| POST | `/api/v1/offers/calculate-discount` | sanctum | Calculate discount for booking context (preview) |
| POST | `/api/v1/offers/apply` | sanctum | Apply an offer (or coupon) to a booking-in-progress |
| POST | `/api/v1/offers/remove` | sanctum | Remove a previously applied offer |

---

## J. REQUEST VALIDATION

### J.1 — OfferFormRequest (Create / Update)

```php
public function rules(): array
{
    $id = $this->route('id');

    return [
        // Bilingual content
        'title_en'                  => 'required|string|max:255',
        'title_ar'                  => 'required|string|max:255',
        'description_en'            => 'nullable|string|max:5000',
        'description_ar'            => 'nullable|string|max:5000',
        'short_label_en'            => 'nullable|string|max:100',
        'short_label_ar'            => 'nullable|string|max:100',
        'terms_en'                  => 'nullable|string|max:5000',
        'terms_ar'                  => 'nullable|string|max:5000',
        'banner_text_en'            => 'nullable|string|max:255',
        'banner_text_ar'            => 'nullable|string|max:255',
        'internal_name'             => 'nullable|string|max:255',

        // Discount
        'discount_type'             => 'required|in:percentage,fixed_amount,override_price,bundle_price,buy_x_get_y,free_addon',
        'discount_value'            => [
            'required', 'numeric', 'min:0',
            function ($attr, $value, $fail) {
                if ($this->discount_type === 'percentage' && $value > 100) {
                    $fail('Percentage discount cannot exceed 100.');
                }
            },
        ],
        'max_discount_amount'       => 'nullable|numeric|min:0',
        'minimum_order_amount'      => 'nullable|numeric|min:0',
        'override_price'            => 'required_if:discount_type,override_price|nullable|numeric|min:0',
        'buy_quantity'              => 'required_if:discount_type,buy_x_get_y|nullable|integer|min:1',
        'get_quantity'              => 'required_if:discount_type,buy_x_get_y|nullable|integer|min:1',
        'get_service_id'            => 'nullable|integer|exists:clinics_services,id',
        'get_discount_percentage'   => 'nullable|numeric|min:0|max:100',

        // Coupon
        'code'                      => [
            'nullable', 'string', 'max:50', 'alpha_dash',
            Rule::unique('offers', 'code')->ignore($id)->whereNull('deleted_at'),
        ],
        'requires_coupon'           => 'nullable|boolean',
        'auto_apply'                => 'nullable|boolean',

        // Targeting
        'target_scope'              => 'required|in:global,service,category,branch,package,vendor',
        'applies_to_all_services'   => 'nullable|boolean',
        'applies_to_all_branches'   => 'nullable|boolean',
        'applies_to_all_categories' => 'nullable|boolean',
        'vendor_id'                 => 'nullable|exists:users,id',
        'service_ids'               => 'required_if:applies_to_all_services,false|nullable|array',
        'service_ids.*'             => 'integer|exists:clinics_services,id',
        'branch_ids'                => 'required_if:applies_to_all_branches,false|nullable|array',
        'branch_ids.*'              => 'integer|exists:branches,id',
        'category_ids'              => 'required_if:applies_to_all_categories,false|nullable|array',
        'category_ids.*'            => 'integer|exists:clinics_categories,id',

        // Customer
        'customer_scope'            => 'nullable|in:all,first_time,returning,segment',
        'first_booking_only'        => 'nullable|boolean',
        'min_completed_bookings'    => 'nullable|integer|min:0',
        'max_completed_bookings'    => 'nullable|integer|min:0|gte:min_completed_bookings',
        'customer_segments'         => 'nullable|array',
        'customer_segments.*.segment_type'  => 'required_with:customer_segments|in:role,tag,subscription_plan,custom',
        'customer_segments.*.segment_value' => 'required_with:customer_segments|string|max:100',

        // Schedule
        'starts_at'                 => 'nullable|date',
        'ends_at'                   => 'nullable|date|after_or_equal:starts_at',
        'is_flash'                  => 'nullable|boolean',
        'schedules'                 => 'nullable|array',
        'schedules.*.day_of_week'   => 'nullable|integer|min:0|max:6',
        'schedules.*.start_time'    => 'nullable|date_format:H:i',
        'schedules.*.end_time'      => 'nullable|date_format:H:i|after:schedules.*.start_time',

        // Usage
        'usage_limit_total'         => 'nullable|integer|min:1',
        'usage_limit_per_user'      => 'nullable|integer|min:1',

        // Priority
        'priority'                  => 'nullable|integer|min:0|max:100',
        'stackable'                 => 'nullable|boolean',
        'exclusive_group'           => 'nullable|string|max:50',

        // Channel
        'allowed_channels'          => 'nullable|array',
        'allowed_channels.*'        => 'in:web,mobile,reception',
        'allowed_payment_methods'   => 'nullable|array',
        'allowed_payment_methods.*' => 'in:cash,online,advance',

        // Display
        'is_active'                 => 'nullable|boolean',
        'is_featured'               => 'nullable|boolean',
        'commission_on_original'    => 'nullable|boolean',
        'banner_image'              => 'nullable|image|max:5120',
    ];
}
```

### J.2 — CouponValidateRequest

```php
'code'           => 'required|string|max:50',
'service_id'     => 'required|integer|exists:clinics_services,id',
'branch_id'      => 'nullable|integer|exists:branches,id',
'clinic_id'      => 'nullable|integer|exists:clinic,id',
'service_charge' => 'required|numeric|min:0',
'channel'        => 'nullable|in:web,mobile,reception',
'payment_method' => 'nullable|in:cash,online,advance',
```

### J.3 — CalculateDiscountRequest

```php
'service_id'     => 'required|integer|exists:clinics_services,id',
'branch_id'      => 'nullable|integer|exists:branches,id',
'clinic_id'      => 'nullable|integer|exists:clinic,id',
'service_charge' => 'required|numeric|min:0',
'coupon_code'    => 'nullable|string|max:50',
'channel'        => 'nullable|in:web,mobile,reception',
'payment_method' => 'nullable|in:cash,online,advance',
'offer_id'       => 'nullable|integer|exists:offers,id',
```

---

## K. RESPONSE FORMAT

### K.1 — Offer List Item
```json
{
  "id": 42,
  "title_en": "20% Off Dental Checkup",
  "title_ar": "خصم ٢٠٪ على فحص الأسنان",
  "title": "20% Off Dental Checkup",
  "short_label_en": "20% OFF",
  "short_label_ar": "خصم ٢٠٪",
  "short_label": "20% OFF",
  "slug": "20-off-dental-checkup",
  "discount_type": "percentage",
  "discount_value": 20.00,
  "max_discount_amount": 500.00,
  "code": "DENTAL20",
  "requires_coupon": true,
  "auto_apply": false,
  "target_scope": "service",
  "starts_at": "2026-04-01T00:00:00Z",
  "ends_at": "2026-06-30T23:59:59Z",
  "usage": {"used": 145, "limit": 500},
  "status": "active",
  "is_active": true,
  "is_featured": true,
  "created_at": "2026-03-15T10:00:00Z"
}
```

### K.2 — Coupon Validation Response (Success)
```json
{
  "status": true,
  "data": {
    "offer_id": 42,
    "code": "DENTAL20",
    "title": "20% Off Dental Checkup",
    "discount_type": "percentage",
    "discount_value": 20.00,
    "original_price": 500.00,
    "discount_amount": 100.00,
    "final_price": 400.00,
    "terms": "Valid for dental checkup services only. Max discount 500 EGP."
  },
  "message": "Coupon applied successfully"
}
```

### K.3 — Coupon Validation Response (Failure)
```json
{
  "status": false,
  "message": "This coupon has expired",
  "data": {
    "code": "DENTAL20",
    "reason": "expired",
    "expired_at": "2026-03-31T23:59:59Z"
  }
}
```

### K.4 — Calculate Discount Response
```json
{
  "status": true,
  "data": {
    "original_price": 500.00,
    "applied_offers": [
      {
        "offer_id": 42,
        "title": "20% Off Dental Checkup",
        "code": "DENTAL20",
        "discount_type": "percentage",
        "discount_amount": 100.00
      }
    ],
    "total_discount": 100.00,
    "discounted_price": 400.00,
    "tax_amount": 56.00,
    "final_payable": 456.00
  }
}
```

### K.5 — Public Offer (for service listing)
```json
{
  "id": 42,
  "slug": "20-off-dental-checkup",
  "title": "20% Off Dental Checkup",
  "short_label": "20% OFF",
  "description": "Get 20% off on any dental checkup...",
  "discount_type": "percentage",
  "discount_value": 20.00,
  "banner_text": "Limited Time Offer!",
  "starts_at": "2026-04-01T00:00:00Z",
  "ends_at": "2026-06-30T23:59:59Z",
  "is_flash": false,
  "terms": "Valid for dental checkup services only...",
  "banner_image": "https://storage.../offer-banner.jpg",
  "services_count": 5,
  "branches_count": 3
}
```

---

## L. BUSINESS RULES

1. **Inactive offers cannot be applied** — `is_active = false` or `status != 'active'` → rejected
2. **Expired offers cannot be applied** — `ends_at < now()` → rejected; status auto-transitions to 'expired' via scheduled command
3. **Offer cannot reduce service below zero** — `discount_amount = min(calculated_discount, service_charge)`
4. **Override price replaces service price** — the entire service charge is set to `override_price`; if `override_price > service_charge`, offer has no effect
5. **Only one non-stackable offer per booking line** — when multiple non-stackable offers match, select the one with highest `priority`; ties broken by highest `discount_amount`
6. **Exclusive group enforcement** — offers sharing the same `exclusive_group` string are mutually exclusive; only the best one applies
7. **Cancelled bookings release usage** — when an appointment is cancelled, `offer_usages.status` → 'cancelled' and `offers.used_count` is decremented
8. **Refunded bookings can reverse usage** — configurable; default: yes for full refund, no for partial refund
9. **Branch-specific offers only match booking's branch** — resolved via `clinic.branch_id`
10. **Coupon-based offers require explicit validation** — `requires_coupon = true` offers are invisible in auto-apply flow
11. **Auto-apply offers are applied automatically** — `auto_apply = true` AND `requires_coupon = false` → applied without user action; the best auto-apply offer is chosen
12. **First-time customer check** — queries `appointments` + `nurse_requests` + `test_orders` + `doctor_visit_requests` for completed+paid bookings by user
13. **Commission calculated on original amount by default** — `commission_on_original = true` means vendor/doctor commission is based on pre-discount `service_charge`, not discounted price. Configurable per offer.
14. **Vendor can only manage their own offers** — `vendor_id` auto-set from `auth()->user()->id` for vendor role; vendors cannot create global offers
15. **Draft offers cannot be applied** — only `status = 'active'` offers are eligible
16. **Exhausted offers auto-transition** — when `used_count >= usage_limit_total`, status → 'exhausted'
17. **Rescheduling re-validates** — if a booking is rescheduled, the offer eligibility is re-checked; if no longer valid, discount is removed and customer is notified

---

## M. PERMISSIONS

### Permission Names
```
view_offers
add_offers
edit_offers
delete_offers
change_status_offers
bulk_action_offers
view_offer_analytics
manage_offer_coupons
```

### Role Matrix

| Permission | admin | demo_admin | vendor | receptionist | doctor | user |
|-----------|:-----:|:----------:|:------:|:------------:|:------:|:----:|
| view_offers | ✓ | ✓ | ✓ (own) | ✓ | - | - |
| add_offers | ✓ | ✓ | ✓ (own) | - | - | - |
| edit_offers | ✓ | ✓ | ✓ (own) | - | - | - |
| delete_offers | ✓ | - | ✓ (own) | - | - | - |
| change_status_offers | ✓ | ✓ | ✓ (own) | - | - | - |
| bulk_action_offers | ✓ | - | - | - | - | - |
| view_offer_analytics | ✓ | ✓ | ✓ (own) | - | - | - |
| manage_offer_coupons | ✓ | ✓ | ✓ (own) | ✓ | - | - |

"(own)" = vendor_id must match the authenticated user's vendor ID.

---

## N. REPORTS & ANALYTICS

### N.1 — OfferReportService Methods

| Method | Returns |
|--------|---------|
| `getDashboardStats()` | active_count, total_redemptions, total_discount_given, avg_discount_per_use |
| `getRedemptionTimeline($offerId, $period)` | Daily/weekly/monthly redemption counts + discount totals |
| `getTopPerformingOffers($limit)` | Offers ranked by redemption count or discount volume |
| `getBranchPerformance($offerId)` | Per-branch breakdown of usage and revenue impact |
| `getServicePerformance($offerId)` | Per-service breakdown |
| `getCustomerBreakdown($offerId)` | New vs returning customer usage split |
| `getRevenueImpact($dateRange)` | Original revenue vs discounted revenue, percentage impact |
| `getCouponUsageReport()` | Per-code usage, unique users, total discount |
| `getAbuseIndicators()` | Users with unusually high redemption counts, rapid sequential uses, multi-account patterns |
| `getExpiringSoonOffers($days)` | Offers ending within N days |
| `getExhaustedOffers()` | Offers that hit usage cap |

---

## O. NOTIFICATIONS

| Event | Recipients | Channel |
|-------|-----------|---------|
| Offer created | Admin (if vendor-created) | DB + optional email |
| Offer activated | Branch managers (if branch-scoped) | DB |
| Offer about to expire (7 days, 1 day) | Offer creator + admin | DB + email |
| Offer exhausted (usage cap hit) | Offer creator + admin | DB |
| Offer expired | Offer creator | DB |
| Coupon usage cap 80% reached | Offer creator + admin | DB |
| Suspicious usage pattern detected | Admin | DB + email |
| Offer removed from booking (reschedule) | Customer | DB + push |

---

## P. IMPLEMENTATION NOTES

### P.1 — Module & File Structure

**Module name:** `Offer`

```
Modules/Offer/
├── Config/config.php
├── Console/
│   ├── ExpireOffersCommand.php          -- pharmacy:expire-offers daily
│   ├── ExhaustOffersCommand.php         -- check usage caps hourly
│   └── NotifyExpiringOffersCommand.php  -- notify 7d/1d before expiry
├── Http/
│   ├── Controllers/
│   │   ├── OfferController.php              -- Admin CRUD
│   │   ├── OfferAnalyticsController.php     -- Analytics page
│   │   ├── OfferUsageLogController.php      -- Usage logs page
│   │   └── API/
│   │       ├── OfferApiController.php       -- Admin API CRUD
│   │       └── PublicOfferApiController.php  -- Public + customer APIs
│   └── Requests/
│       ├── OfferFormRequest.php
│       ├── CouponValidateRequest.php
│       └── CalculateDiscountRequest.php
├── Models/
│   ├── Offer.php
│   ├── OfferService.php
│   ├── OfferBranch.php
│   ├── OfferCategory.php
│   ├── OfferCustomerSegment.php
│   ├── OfferSchedule.php
│   ├── OfferUsage.php
│   └── OfferAuditLog.php
├── Services/
│   ├── OfferEligibilityService.php    -- validation engine
│   ├── OfferPricingService.php        -- price calculator
│   ├── OfferAuditService.php          -- audit logging
│   └── OfferReportService.php         -- analytics
├── Transformers/
│   ├── OfferResource.php
│   ├── OfferDetailResource.php
│   ├── PublicOfferResource.php
│   ├── OfferUsageResource.php
│   └── DiscountCalculationResource.php
├── Providers/
│   ├── OfferServiceProvider.php
│   └── RouteServiceProvider.php
├── Routes/
│   ├── web.php
│   └── api.php
├── Resources/views/backend/
│   ├── offers/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── show.blade.php
│   ├── analytics/
│   │   └── index.blade.php
│   └── usage-logs/
│       └── index.blade.php
├── database/
│   ├── migrations/
│   │   ├── 2026_04_11_300001_create_offers_table.php
│   │   ├── 2026_04_11_300002_create_offer_services_table.php
│   │   ├── 2026_04_11_300003_create_offer_branches_table.php
│   │   ├── 2026_04_11_300004_create_offer_categories_table.php
│   │   ├── 2026_04_11_300005_create_offer_customer_segments_table.php
│   │   ├── 2026_04_11_300006_create_offer_schedules_table.php
│   │   ├── 2026_04_11_300007_create_offer_usages_table.php
│   │   ├── 2026_04_11_300008_create_offer_audit_logs_table.php
│   │   ├── 2026_04_11_300009_add_offer_fields_to_appointments.php
│   │   ├── 2026_04_11_300010_add_offer_fields_to_nurse_requests.php
│   │   ├── 2026_04_11_300011_add_offer_fields_to_test_orders.php
│   │   └── 2026_04_11_300012_add_offer_fields_to_doctor_visit_requests.php
│   └── seeders/
│       └── OfferPermissionSeeder.php
└── Tests/
    └── Unit/
        ├── OfferEligibilityTest.php
        ├── OfferPricingTest.php
        ├── OfferCouponValidationTest.php
        └── OfferUsageTrackingTest.php
```

### P.2 — Model Names & Relationships Summary

| Model | Table | Extends | Key Relationships |
|-------|-------|---------|-------------------|
| Offer | offers | BaseModel | services(), branches(), categories(), customerSegments(), schedules(), usages(), auditLogs(), vendor(), creator() |
| OfferService | offer_services | Model | offer(), service() |
| OfferBranch | offer_branches | Model | offer(), branch() |
| OfferCategory | offer_categories | Model | offer(), category() |
| OfferCustomerSegment | offer_customer_segments | Model | offer() |
| OfferSchedule | offer_schedules | Model | offer() |
| OfferUsage | offer_usages | Model | offer(), user(), service() |
| OfferAuditLog | offer_audit_logs | Model | offer(), user() |

### P.3 — Service Classes

| Class | Purpose |
|-------|---------|
| `OfferEligibilityService` | Validates offer eligibility against full booking context (16-step pipeline) |
| `OfferPricingService` | Calculates optimal discount from eligible offers, handles stacking/priority |
| `OfferAuditService` | Logs admin actions (create, update, status change, delete, usage reversal) |
| `OfferReportService` | Generates analytics: timeline, top offers, branch/service performance, abuse indicators |

### P.4 — Integration Points

**Appointment creation** (`Modules/Appointment`):
- After service selection and before payment, call `OfferEligibilityService::getEligibleOffers()` for auto-apply
- If coupon provided, call `OfferEligibilityService::validateCoupon()`
- Call `OfferPricingService::calculateDiscount()` to get final amount
- Set `appointment.offer_id`, `appointment.offer_discount`, `appointment.offer_code`
- Set `appointment_transaction.discount_type/value/amount` from offer
- Create `offer_usages` row
- Increment `offers.used_count` atomically (`DB::raw('used_count + 1')`)

**NurseRequest, TestOrder, DoctorVisitRequest** — same pattern, adapted for each model's pricing fields.

**Cancellation/Refund:**
- On cancel: update `offer_usages.status` → 'cancelled', decrement `offers.used_count`
- On refund: update `offer_usages.status` → 'reversed' only if full refund

### P.5 — Scheduled Commands

| Command | Schedule | Description |
|---------|----------|-------------|
| `offer:expire` | Daily midnight | Set `status='expired'` where `ends_at < now()` and `status='active'` |
| `offer:check-exhausted` | Hourly | Set `status='exhausted'` where `used_count >= usage_limit_total` |
| `offer:notify-expiring` | Daily 9 AM | Send notifications for offers expiring within 7 days and 1 day |
| `offer:activate-scheduled` | Every 15 min | Set `status='active'` where `status='scheduled'` and `starts_at <= now()` |

### P.6 — Test Cases

| Test | What It Covers |
|------|---------------|
| `OfferEligibilityTest` | Each of 16 validation steps; edge cases for each targeting rule |
| `OfferPricingTest` | All 6 discount types; stacking logic; priority resolution; max cap; zero-floor |
| `OfferCouponValidationTest` | Valid code, invalid code, expired, exhausted, per-user limit, wrong service |
| `OfferUsageTrackingTest` | Usage increment on booking, decrement on cancel, no decrement on partial refund |
| `OfferApiTest` | CRUD endpoints, authorization, vendor scoping, bulk actions |
| `OfferScheduleTest` | Day-of-week matching, time window matching, combined date+schedule |

### P.7 — Sidebar Entries

**Admin section (add under SYSTEM or as standalone):**
```
OFFERS
  ├── Offers (ph ph-tag)
  ├── Offer Analytics (ph ph-chart-line-up)
  └── Offer Logs (ph ph-clipboard-text)
```

### P.8 — Translation Keys (`lang/en/offer.php`)

Key labels: `lbl_offer`, `lbl_offers`, `lbl_coupon_code`, `lbl_discount_type`, `lbl_discount_value`, `lbl_override_price`, `lbl_target_scope`, `lbl_auto_apply`, `lbl_stackable`, `lbl_priority`, `lbl_usage_limit`, `lbl_schedule`, `lbl_customer_scope`, `lbl_first_booking_only`, `lbl_flash_sale`, `lbl_exclusive_group`, `lbl_commission_on_original`, `lbl_banner_text`, `lbl_terms`, plus all status labels, discount type labels, customer scope labels, validation messages, and success messages.

---

## Q. MIGRATION FROM LEGACY PROMOTION MODULE

The existing `Promotion`, `Coupon`, and `UserCouponRedeem` models should be deprecated but not deleted immediately:

1. Create a one-time migration command `offer:migrate-legacy` that:
   - Reads each `promotions_coupon` row
   - Creates an `offers` row with `discount_type = coupon.discount_type`, `discount_value`, `code = coupon.coupon_code`, `starts_at = coupon.start_date_time`, `ends_at = coupon.end_date_time`, `requires_coupon = true`, `usage_limit_total = coupon.use_limit`, `target_scope = 'global'`
   - Migrates `user_coupon_redeem` rows into `offer_usages`
2. Mark `Modules/Promotion` as disabled in `modules_statuses.json` after migration
3. Remove promotion routes from `GenerateMenus.php`
4. Keep database tables for historical data
