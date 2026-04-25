# Feature Specification: Pharmacy Marketplace

**Feature Branch**: `010-pharmacy-marketplace`
**Created**: 2026-04-05
**Status**: Draft
**Input**: User description: "Build a Pharmacy Marketplace Module where customers can browse pharmacy products, add to cart, select a pharmacy, and place orders. Admin manages the master catalog; Pharmacy manages inventory and order fulfillment."

---

## Clarifications

### Session 2026-04-05

- Q: When a customer adds an item to their cart, what stock limit is enforced before a pharmacy is selected? → A: At least one pharmacy must have `stock_qty >= requested quantity`. The system validates that any single pharmacy can fulfill the quantity, not an aggregate across all pharmacies.
- Q: How are user accounts associated with a pharmacy? → A: One pharmacy can have multiple staff user accounts (1:many). Each staff user is linked to exactly one pharmacy via a `pharmacy_id` foreign key on their user/profile record.
- Q: When a pharmacy rejects a pending order, should stock be restored? → A: Yes — stock is always restored whenever an order is cancelled, regardless of whether the customer or the pharmacy initiated the cancellation.
- Q: Do GPS coordinates affect which pharmacies appear in matching results? → A: No — GPS coordinates are informational only in MVP (e.g., for map display). Pharmacy matching is based solely on stock coverage; no distance filtering or radius-based exclusion is applied.
- Q: What should happen when no pharmacy can fulfill all items in the customer's cart? → A: Return an empty list with a clear message indicating no pharmacy can fulfill the full cart. The customer must adjust (remove or reduce) items to proceed.

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Customer Browses and Orders Medication (Priority: P1)

A customer opens the app and wants to buy a product. They browse main categories, select a subcategory, view product listings filtered by brand or type, add items to their cart, select a pharmacy that stocks all their items, and confirm the order.

**Why this priority**: This is the core end-to-end journey that delivers the primary value of the entire marketplace. Without this flow working, the feature has no value.

**Independent Test**: Can be fully tested by seeding categories, products, a pharmacy with inventory, and a user account — then walking through: browse → add to cart → select pharmacy → confirm order.

**Acceptance Scenarios**:

1. **Given** a customer is logged in, **When** they browse `/pharmacy/categories`, **Then** they see all active top-level categories with Arabic and English names and images.
2. **Given** a customer selects a category, **When** they tap on it, **Then** subcategories are listed.
3. **Given** a customer is on a subcategory page with active products, **When** they view products, **Then** only products with stock > 0 and `is_available = true` at any pharmacy are shown.
4. **Given** a customer adds items to their cart, **When** they request available pharmacies, **Then** only pharmacies that stock ALL items in the cart are returned with pricing breakdown (subtotal, delivery fee, total).
5. **Given** a customer confirms an order with a selected pharmacy and address, **When** the order is submitted, **Then** the order is created, stock is decremented, the cart is cleared, and a pending order appears in their order history.

---

### User Story 2 - Admin Manages Product Catalog (Priority: P2)

An admin logs into the admin panel and can create/manage categories, brands, product types, products, and pharmacies without touching inventory levels.

**Why this priority**: The catalog is the foundation of the marketplace. Without products in the system, no customer flow works. Admin manages the master data.

**Independent Test**: Can be fully tested by logging into the admin panel and creating a category hierarchy, then a product linked to those categories.

**Acceptance Scenarios**:

1. **Given** an admin is on the Categories page, **When** they create a main category with Arabic/English names and an image, **Then** the category appears in the listing and is immediately available in the API.
2. **Given** an admin creates a subcategory by selecting a parent category, **When** they save it, **Then** it appears under the parent in the hierarchy.
3. **Given** an admin creates a product in the master catalog, **When** they assign it to a category, brand, and product type, **Then** the product exists system-wide and any pharmacy can add it to their inventory.
4. **Given** an admin deactivates a product (`is_active = false`), **When** a customer browses, **Then** that product does not appear in any listing.

---

### User Story 3 - Pharmacy Manages Inventory and Fulfills Orders (Priority: P3)

A pharmacy staff member logs into the pharmacy panel, adds products to their inventory with pricing and stock levels, and accepts/fulfills customer orders.

**Why this priority**: Without pharmacies managing inventory, no pharmacy can be matched to a cart and no orders can be fulfilled.

**Independent Test**: Can be fully tested by logging in as a pharmacy user, adding a product with stock, then having a customer order it and verifying the pharmacy sees the order and can update its status.

**Acceptance Scenarios**:

1. **Given** a pharmacy user is on the Inventory page, **When** they add a product from the master catalog with price 50 and stock 10, **Then** the product appears in their inventory and is visible to customers.
2. **Given** a pharmacy updates a product's price or stock, **When** saved, **Then** the new values are reflected immediately in customer-facing pricing.
3. **Given** a pharmacy sets `is_available = false` for a product, **When** a customer browses, **Then** that product from that pharmacy is not offered to customers.
4. **Given** a customer places an order, **When** the pharmacy views their Orders page, **Then** the order appears in `pending` status with customer info and order details.
5. **Given** a pharmacy accepts an order, **When** they advance status through `confirmed → preparing → on_the_way → delivered`, **Then** each status transition is saved and the customer's order history reflects the current status.

---

### User Story 4 - Customer Tracks and Cancels Orders (Priority: P4)

A customer can view their order history, see the current status of each order, and cancel a pending order.

**Why this priority**: Order visibility closes the customer loop. Cancellation prevents stuck orders.

**Independent Test**: Can be tested by placing an order and checking the order list endpoint for status, then calling the cancel endpoint.

**Acceptance Scenarios**:

1. **Given** a customer has placed orders, **When** they view their order list, **Then** all their orders appear with order ID, pharmacy name, total, and current status.
2. **Given** an order is in `pending` status, **When** the customer cancels it, **Then** the order status changes to `cancelled` and stock is restored.
3. **Given** an order is `confirmed` or beyond, **When** the customer attempts to cancel, **Then** the system rejects the cancellation with an appropriate message.

---

### Edge Cases

- What happens when a customer tries to add more quantity than the pharmacy has in stock?
- What happens when another customer purchases the last unit of a product between cart add and order confirmation?
- What happens if a pharmacy becomes unavailable after the customer has already placed their order?
- What happens when a cart contains a product that a pharmacy no longer stocks by the time the order is submitted?
- If the cart is empty when the customer requests available pharmacies, the system rejects the request with an error (FR-019a).
- What happens when two customers simultaneously order the last unit of the same product from the same pharmacy?

---

## Requirements *(mandatory)*

### Functional Requirements

**Category Management**

- **FR-001**: System MUST support a two-level category hierarchy (main categories and subcategories), where each subcategory belongs to exactly one main category.
- **FR-002**: System MUST store bilingual names (`name_ar`, `name_en`) and an image for each category.
- **FR-003**: System MUST allow admins to activate/deactivate categories and control display order via a sort order field.
- **FR-004**: Admin MUST be able to reorder categories within a level.

**Product Catalog**

- **FR-005**: System MUST maintain a master product catalog controlled exclusively by admins.
- **FR-006**: Each product MUST be linked to a main category, a subcategory, an optional brand, and an optional product type.
- **FR-007**: Admin MUST be able to flag a product as requiring a prescription (`requires_prescription`).
- **FR-008**: Products MUST be activatable/deactivatable by admins; inactive products MUST NOT appear in customer-facing APIs.

**Brands and Product Types**

- **FR-009**: Admin MUST be able to create and manage brands (name, active status).
- **FR-010**: Admin MUST be able to create and manage product types with bilingual names.

**Pharmacy Management (Admin)**

- **FR-011**: Admin MUST be able to create and manage pharmacies with: name, logo, phone, address, GPS coordinates (lat/lng), delivery fee, and active status.

**Pharmacy Inventory (Pharmacy Panel)**

- **FR-012**: Each pharmacy MUST be able to add products from the master catalog to their own inventory with a price and stock quantity.
- **FR-013**: Pharmacies MUST be able to update price, stock quantity, and availability (`is_available`) per product.
- **FR-014**: A product is only visible to customers from a specific pharmacy if: `stock_qty > 0` AND `is_available = true` for that pharmacy's inventory record.

**Customer Cart**

- **FR-015**: Authenticated customers MUST be able to add products to their cart with a specified quantity.
- **FR-016**: System MUST prevent adding a quantity greater than available stock at any single pharmacy — at least one pharmacy must have `stock_qty >= requested quantity` for the add-to-cart to succeed. Aggregate stock across multiple pharmacies does NOT satisfy this check.
- **FR-017**: Customers MUST be able to update item quantities and remove items from the cart.
- **FR-018**: Each user has a single active cart at any given time.

**Pharmacy Matching**

- **FR-019**: System MUST return only pharmacies that stock ALL products currently in the customer's cart with `stock_qty > 0` and `is_available = true`. If no pharmacy satisfies this condition, the system MUST return an empty list with a message indicating no pharmacy can fulfill the complete cart, prompting the customer to adjust their items.
- **FR-019a**: System MUST reject the available-pharmacies request with a clear error if the cart is empty.
- **FR-020**: For each matched pharmacy, the system MUST calculate and return: subtotal (sum of pharmacy prices × quantities), delivery fee, and total.

**Order Placement**

- **FR-021**: Customers MUST specify a pharmacy, delivery address, and payment method when placing an order.
- **FR-022**: At order creation, the system MUST validate that: the cart is non-empty, the selected pharmacy still stocks all items in sufficient quantity.
- **FR-023**: Upon successful order creation, the system MUST: calculate final totals, create an order snapshot (preserving item prices at time of order), decrement stock for each item at the selected pharmacy, and clear the customer's cart.
- **FR-024**: Orders MUST be single-pharmacy only (no splitting across pharmacies).

**Order Lifecycle**

- **FR-025**: An order follows the status flow: `pending → confirmed → preparing → on_the_way → delivered`. Orders may also be `cancelled`.
- **FR-026**: Pharmacy users MUST be able to accept (move to `confirmed`) or reject (`cancelled`) a pending order. When a pharmacy rejects an order, stock for all order items MUST be restored to the pharmacy's inventory.
- **FR-027**: Pharmacy users MUST be able to advance order status through: `confirmed → preparing → on_the_way → delivered`.
- **FR-028**: Admin MUST be able to change order status to any value.
- **FR-029**: Customers MUST be able to cancel their own order only when it is in `pending` status; stock MUST be restored on cancellation.
- **FR-029a**: Stock restoration applies to ALL cancellations regardless of initiator (customer or pharmacy). Any transition to `cancelled` status MUST trigger stock restoration for all order items at the relevant pharmacy.

**API Endpoints**

- **FR-030**: System MUST expose public (authenticated) customer APIs for: categories, products (with filtering by subcategory, search, brand, product type), product details, cart CRUD, pharmacy matching, and orders (create, list, detail, cancel).
- **FR-031**: System MUST expose filter APIs for brands and product types scoped to a subcategory.

**Permissions**

- **FR-032**: Admin MUST have full access to all marketplace resources.
- **FR-033**: Multiple staff users can belong to a single pharmacy (1:many). Each pharmacy user account MUST be linked to exactly one pharmacy, and MUST only access that pharmacy's inventory and orders. Admin assigns pharmacy staff to a pharmacy at account creation.

### Key Entities

- **Category**: Hierarchical (parent/child). Has bilingual name, image, active status, and sort order.
- **Brand**: Has name and active status. Used to classify products.
- **ProductType**: Has bilingual name. Used to classify products.
- **Product**: The master catalog item. Belongs to a category, subcategory, brand, and product type. Has bilingual name, image, description, prescription flag, and active status. Does NOT hold price or stock.
- **Pharmacy**: A physical pharmacy entity. Has name, logo, contact info, GPS coordinates (lat/lng — stored for map display only; not used for filtering in MVP), delivery fee, and active status. Can have multiple associated staff user accounts (1:many relationship with users).
- **PharmacyInventory**: The per-pharmacy stock record for a product. Holds price, stock quantity, and availability flag. Links Pharmacy to Product.
- **CartItem**: Temporary record of a customer's intended purchase. Links a user to a product with a quantity.
- **Order**: A confirmed purchase. Links a customer to a pharmacy with a total, payment method, delivery address, and status.
- **OrderItem**: A snapshot of an ordered product at the time of purchase (product ID, quantity, price). Immutable after creation.

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Customers can complete the full flow (browse → add to cart → select pharmacy → confirm order) in under 5 minutes on a standard connection.
- **SC-002**: Available pharmacy results are returned in under 2 seconds after a customer requests them from a cart.
- **SC-003**: Order placement is atomic: no order is created if stock validation fails, and no stock is decremented without a successful order record.
- **SC-004**: 100% of orders reference only one pharmacy (no partial/split orders accepted by the system).
- **SC-005**: Admin can create and publish a new product category and product in under 3 minutes.
- **SC-006**: Pharmacy staff can update inventory (price + stock) for 10 products in under 5 minutes.
- **SC-007**: Product listings return correct `is_in_stock` status: a product marked as unavailable by all pharmacies does not appear in search results.
- **SC-008**: Concurrent orders for the same last-stock item do not result in negative stock (race condition protection).

---

## Assumptions

- Delivery addresses are stored as user address records already present in the system (referenced by `address_id`). The address model/table is pre-existing.
- Payment method is recorded on the order (e.g., `cash_on_delivery`), but payment processing is out of scope for MVP.
- Prescription-required products are flagged in the catalog for future enforcement; MVP does not block purchase of prescription products.
- "Pharmacy panel" refers to a web-based admin panel for pharmacy staff (role-based access), not a separate application.
- Category images are stored via the existing media library infrastructure (Spatie MediaLibrary).
- The `price_from` field on product listings reflects the lowest price offered by any available pharmacy for that product.
- Stock concurrency is handled at the database level (optimistic locking or row-level locking) to prevent overselling.
- Out-of-scope for MVP: promo codes, insurance integration, scheduled delivery, multi-pharmacy orders, and push notifications.
