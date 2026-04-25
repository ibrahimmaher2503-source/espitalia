# Pharmacy Marketplace Implementation Guide

**Project**: Espitalia Clinic Management System
**Feature**: Pharmacy Marketplace Module
**Branch**: `010-pharmacy-marketplace`
**Implementation Status**: ✅ Complete (All 73 tasks finished)
**Last Updated**: 2026-04-05

---

## Overview

The Pharmacy Marketplace Module provides a complete pharmacy e-commerce system with three distinct interfaces:

1. **Customer API** — Browse and order medications via mobile app
2. **Admin Panel** — Manage product catalog (categories, brands, products, pharmacies)
3. **Pharmacy Panel** — Manage inventory and fulfill orders

---

## Architecture

### Database Schema

```
Pharmacy Marketplace Database Structure
├── pharmacy_categories (hierarchical: main → subcategories)
├── pharmacy_brands
├── pharmacy_product_types
├── pharmacy_products (master catalog, managed by admin)
├── pharmacies (with location data for map display)
├── pharmacy_inventory (per-pharmacy stock and pricing)
├── pharmacy_cart_items (customer shopping carts)
├── pharmacy_orders (customer orders, atomic snapshots)
└── pharmacy_order_items (order items with price snapshots)
```

**Key Features:**
- Hierarchical categories (parent_id supports unlimited levels)
- Per-pharmacy pricing and stock (inventory table)
- Soft deletes on all master data for audit trail
- Immutable order snapshots (order_items don't use soft deletes)
- Audit columns (created_by, updated_by, deleted_by) on all models

---

## API Endpoints Reference

### Customer API (`/api/v1/pharmacy`)

**All endpoints require authentication (`auth:sanctum`)**

#### Browse Categories
```
GET /categories                          → List main categories
GET /categories/{id}/children            → List subcategories
```

#### Browse Products
```
GET /products                            → List products with filters
  ?sub_category_id=2
  &search=aspirin
  &brand_ids[]=1&brand_ids[]=3
  &product_type_ids[]=1
  &per_page=20

GET /products/{id}                       → Get product details
```

#### Get Filter Options
```
GET /filters/brands?sub_category_id=2     → Available brands
GET /filters/product-types?sub_category_id=2 → Available types
```

#### Shopping Cart
```
GET /cart                                → Get user's cart
POST /cart/items                         → Add item
  { "product_id": 1, "quantity": 2 }

PATCH /cart/items/{id}                   → Update quantity
  { "quantity": 3 }

DELETE /cart/items/{id}                  → Remove item
```

#### Find Available Pharmacies
```
GET /cart/available-pharmacies?address_id=5
→ Returns pharmacies that stock ALL cart items
→ Each includes subtotal (from its inventory prices), delivery_fee, total
```

#### Orders
```
POST /orders                             → Create order
  {
    "pharmacy_id": 1,
    "address_id": 5,
    "payment_method": "cash_on_delivery"
  }

GET /orders                              → List user's orders
  ?status=pending
  &per_page=15
  &page=1

GET /orders/{id}                         → Get order details
POST /orders/{id}/cancel                 → Cancel pending order
```

**Order Status Flow:**
`pending` → `confirmed` → `preparing` → `on_the_way` → `delivered` | `cancelled`

---

### Admin Panel (`/app/backend`)

All routes require `auth:sanctum` + admin/demo_admin role + appropriate permission.

#### Categories Management
```
GET /app/pharmacy-categories                    → List (DataTable)
GET /app/pharmacy-categories/index-data         → Server-side JSON
GET /app/pharmacy-categories/create             → Create form
POST /app/pharmacy-categories                   → Store new
GET /app/pharmacy-categories/{id}/edit          → Edit form
PUT /app/pharmacy-categories/{id}               → Update
DELETE /app/pharmacy-categories/{id}            → Delete
POST /app/pharmacy-categories/bulk-action       → Bulk delete
POST /app/pharmacy-categories/{id}/update-status → Toggle is_active
```

**Form Fields:** name_ar, name_en, parent_id (nullable for hierarchy), image, sort_order

#### Brands Management
```
GET /app/pharmacy-brands                    → List
GET /app/pharmacy-brands/index-data         → Server-side JSON
POST /app/pharmacy-brands                   → Create
PUT /app/pharmacy-brands/{id}               → Update
DELETE /app/pharmacy-brands/{id}            → Delete
POST /app/pharmacy-brands/{id}/update-status → Toggle is_active
```

**Form Fields:** name (unique), is_active

#### Product Types Management
```
GET /app/pharmacy-product-types             → List
POST /app/pharmacy-product-types            → Create
PUT /app/pharmacy-product-types/{id}        → Update
DELETE /app/pharmacy-product-types/{id}     → Delete
```

**Form Fields:** name_ar, name_en

#### Products Management
```
GET /app/pharmacy-products                  → List
GET /app/pharmacy-products/index-data       → Server-side JSON
GET /app/pharmacy-products/create           → Create form
POST /app/pharmacy-products                 → Store new
GET /app/pharmacy-products/{id}/edit        → Edit form
PUT /app/pharmacy-products/{id}             → Update
DELETE /app/pharmacy-products/{id}          → Delete
```

**Form Fields:** name_ar, name_en, category_id, sub_category_id, brand_id, product_type_id, description, image, requires_prescription, is_active

#### Pharmacies Management
```
GET /app/pharmacies                     → List
POST /app/pharmacies                    → Create
PUT /app/pharmacies/{id}                → Update
DELETE /app/pharmacies/{id}             → Delete
```

**Form Fields:** name, logo, phone, address, latitude, longitude, delivery_fee, is_active

#### Orders Management (Admin View All)
```
GET /app/pharmacy-orders                    → List all orders
GET /app/pharmacy-orders/index-data         → Server-side JSON
GET /app/pharmacy-orders/{id}               → View order details
POST /app/pharmacy-orders/{id}/update-status → Change order status
```

**Permissions:** view_pharmacy_orders

---

### Pharmacy Panel (`/app/pharmacy-panel`)

All routes require `auth:sanctum` + pharmacy role + appropriate permission.
All data automatically scoped to authenticated user's pharmacy_id.

#### Inventory Management
```
GET /app/pharmacy-panel/inventory           → List inventory
GET /app/pharmacy-panel/inventory/index-data → Server-side JSON
GET /app/pharmacy-panel/inventory/create    → Create form
POST /app/pharmacy-panel/inventory          → Add product to inventory
GET /app/pharmacy-panel/inventory/{id}/edit → Edit form
PUT /app/pharmacy-panel/inventory/{id}      → Update price/stock
DELETE /app/pharmacy-panel/inventory/{id}   → Remove product
POST /app/pharmacy-panel/inventory/{id}/update-status → Toggle is_available
```

**Form Fields:** product_id (from master catalog, excludes already-added), price, stock_qty, is_available

**Important:**
- Only shows products from master catalog not yet added to this pharmacy
- Each product can only be added once per pharmacy
- Price and stock are per-pharmacy and can differ between pharmacies

#### Orders Management
```
GET /app/pharmacy-panel/orders              → List own orders
GET /app/pharmacy-panel/orders/index-data   → Server-side JSON
GET /app/pharmacy-panel/orders/{id}         → View order details
POST /app/pharmacy-panel/orders/{id}/accept → Accept pending order
POST /app/pharmacy-panel/orders/{id}/reject → Reject and restore stock
POST /app/pharmacy-panel/orders/{id}/advance-status → Change status
```

**Order Actions:**
- **Accept** (pending → confirmed): Accept order and prepare for fulfillment
- **Reject** (pending → cancelled): Reject order and restore stock
- **Advance Status** (confirmed/preparing/on_the_way → next): Sequence status updates

---

## Business Logic Rules

### Product Visibility
Products are visible in the API **only if**:
- `is_active = true`
- At least one pharmacy has `stock_qty > 0` AND `is_available = true`

### Cart Rules
- Cannot add more than max stock available at any single pharmacy
- Cart is isolated per user (user_id)
- Cart items are cleared after successful order placement

### Pharmacy Matching
A pharmacy is available **only if**:
- It is active
- It stocks ALL items in the cart
- Each item has sufficient stock for the requested quantity
- Response includes subtotal (calculated from pharmacy's inventory prices) + delivery_fee + total

### Order Processing (Atomic Transaction)
1. Validate cart is non-empty
2. Validate pharmacy stocks all items in sufficient quantity
3. Acquire pessimistic lock on pharmacy_inventory rows (SELECT FOR UPDATE)
4. Create PharmacyOrder record
5. Create PharmacyOrderItem snapshots (product_name + price from inventory at order time)
6. Decrement stock_qty for each item
7. Clear user's cart
8. Commit transaction (atomic guarantee)

### Stock Restoration
When an order is cancelled or rejected:
1. Locate all order_items for that order
2. For each item, increment stock at the pharmacy
3. Set order status to cancelled
4. Uses same pessimistic locking to prevent race conditions

### Status Transitions
- **Customer-initiated**: pending → cancelled (if pending only)
- **Pharmacy-initiated**: pending → confirmed → preparing → on_the_way → delivered
- **Admin-initiated**: Can set any status (bypassing sequence), triggers stock restoration if moving to cancelled

---

## Key Implementation Details

### Services Layer

#### CartService (`Modules/Pharmacy/Services/CartService.php`)
```php
getCart(userId)
addItem(userId, productId, quantity)      // validates stock
updateItem(cartItemId, quantity)          // validates stock
removeItem(cartItemId)
clearCart(userId)
```

#### PharmacyMatchingService (`Modules/Pharmacy/Services/PharmacyMatchingService.php`)
```php
findAvailablePharmacies(userId, addressId)
// Returns pharmacies that stock ALL cart items
// Includes subtotal (from pharmacy prices), delivery_fee, total
```

#### OrderService (`Modules/Pharmacy/Services/OrderService.php`)
```php
placeOrder(userId, pharmacyId, addressId, paymentMethod)
// Atomic transaction with pessimistic locking

cancelOrder(orderId, initiatorId, isAdmin=false)
// Restore stock and set status to cancelled
```

### Models & Relationships

All models extend `BaseModel` for soft deletes and audit columns (except CartItem and OrderItem).

**Key Relationships:**
- PharmacyProduct → PharmacyCategory (category + subcategory)
- PharmacyProduct → PharmacyBrand
- PharmacyProduct → PharmacyProductType
- PharmacyInventory → Pharmacy + PharmacyProduct
- PharmacyOrder → User + Pharmacy + Address + PharmacyOrderItems
- PharmacyOrderItem → PharmacyOrder (immutable snapshots)

### Data Scoping

**OrderApiController (Customer):**
```php
PharmacyOrder::where('user_id', auth()->id())
```

**PharmacyPanelOrderController (Pharmacy Staff):**
```php
PharmacyOrder::where('pharmacy_id', auth()->user()->pharmacy_id)
```

**InventoryController (Pharmacy Staff):**
```php
PharmacyInventory::where('pharmacy_id', auth()->user()->pharmacy_id)
```

### Media Handling

Uses Spatie MediaLibrary with single-file collections:
- Categories: `category_image`
- Products: `product_image`
- Pharmacies: `logo`

---

## Testing Scenarios

### End-to-End Flow (Happy Path)

1. **Customer browses**
   ```
   GET /api/v1/pharmacy/categories → see main categories
   GET /api/v1/pharmacy/categories/1/children → see subcategories
   GET /api/v1/pharmacy/products?sub_category_id=2 → see products
   ```

2. **Customer filters and searches**
   ```
   GET /api/v1/pharmacy/filters/brands?sub_category_id=2
   GET /api/v1/pharmacy/products?sub_category_id=2&brand_ids[]=1&search=aspirin
   ```

3. **Customer shops**
   ```
   POST /api/v1/pharmacy/cart/items { product_id: 1, quantity: 2 }
   GET /api/v1/pharmacy/cart → view cart
   ```

4. **Find pharmacies**
   ```
   GET /api/v1/pharmacy/cart/available-pharmacies?address_id=5
   → sees 1-3 available pharmacies with prices
   ```

5. **Place order**
   ```
   POST /api/v1/pharmacy/orders { pharmacy_id: 1, address_id: 5, payment_method: "cash_on_delivery" }
   → order created with status "pending"
   ```

6. **Pharmacy fulfills**
   - Login as pharmacy staff
   - See order in `/app/pharmacy-panel/orders`
   - Click "Accept" → status changes to "confirmed"
   - Click "Advance Status" to "preparing"
   - Click "Advance Status" to "on_the_way"
   - Click "Advance Status" to "delivered"

7. **Customer tracks**
   ```
   GET /api/v1/pharmacy/orders
   GET /api/v1/pharmacy/orders/1 → see status progression
   ```

### Admin Catalog Management

1. **Admin creates category**
   ```
   GET /app/pharmacy-categories/create
   POST /app/pharmacy-categories { name_ar: "...", name_en: "...", parent_id: null }
   ```

2. **Admin creates product**
   ```
   GET /app/pharmacy-products/create
   POST /app/pharmacy-products {
     name_ar: "...",
     category_id: 1,
     sub_category_id: 2,
     brand_id: 1,
     product_type_id: 1
   }
   ```

3. **Admin creates pharmacy**
   ```
   POST /app/pharmacies {
     name: "...",
     phone: "...",
     address: "...",
     latitude: 24.7136,
     longitude: 46.6753,
     delivery_fee: 25
   }
   ```

### Pharmacy Inventory Setup

1. **Pharmacy staff adds product to inventory**
   ```
   GET /app/pharmacy-panel/inventory/create
   POST /app/pharmacy-panel/inventory {
     product_id: 1,
     price: 15.50,
     stock_qty: 100,
     is_available: true
   }
   ```

2. **Pharmacy staff updates price/stock**
   ```
   PUT /app/pharmacy-panel/inventory/1 { price: 16.00, stock_qty: 95 }
   ```

3. **Product now visible in customer API** (after being ordered once)

---

## Permissions

### Admin Roles
- `view_pharmacy_categories`, `add_pharmacy_categories`, `edit_pharmacy_categories`, `delete_pharmacy_categories`
- `view_pharmacy_brands`, `add_pharmacy_brands`, `edit_pharmacy_brands`, `delete_pharmacy_brands`
- `view_pharmacy_product_types`, `add_pharmacy_product_types`, `edit_pharmacy_product_types`, `delete_pharmacy_product_types`
- `view_pharmacy_products`, `add_pharmacy_products`, `edit_pharmacy_products`, `delete_pharmacy_products`
- `view_pharmacies`, `add_pharmacies`, `edit_pharmacies`, `delete_pharmacies`
- `view_pharmacy_orders`, `edit_pharmacy_orders`

### Pharmacy Role
- `view_pharmacy_inventory`, `add_pharmacy_inventory`, `edit_pharmacy_inventory`, `delete_pharmacy_inventory`
- `view_pharmacy_orders`

---

## Module Files Structure

```
Modules/Pharmacy/
├── Http/
│   ├── Controllers/
│   │   ├── PharmacyCategoryController.php
│   │   ├── PharmacyBrandController.php
│   │   ├── PharmacyProductTypeController.php
│   │   ├── PharmacyProductController.php
│   │   ├── PharmacyController.php
│   │   ├── PharmacyOrderController.php (admin)
│   │   └── API/
│   │       ├── CategoryApiController.php
│   │       ├── ProductApiController.php
│   │       ├── FilterApiController.php
│   │       ├── CartApiController.php
│   │       ├── PharmacyMatchingApiController.php
│   │       └── OrderApiController.php
│   ├── Controllers/PharmacyPanel/
│   │   ├── InventoryController.php
│   │   └── PharmacyPanelOrderController.php
│   └── Requests/
│       ├── PharmacyCategoryRequest.php
│       ├── PharmacyBrandRequest.php
│       ├── PharmacyProductTypeRequest.php
│       ├── PharmacyProductRequest.php
│       ├── PharmacyRequest.php
│       ├── InventoryRequest.php
│       ├── CartItemRequest.php
│       └── PharmacyOrderRequest.php
├── Models/
│   ├── PharmacyCategory.php
│   ├── PharmacyBrand.php
│   ├── PharmacyProductType.php
│   ├── PharmacyProduct.php
│   ├── Pharmacy.php
│   ├── PharmacyInventory.php
│   ├── PharmacyCartItem.php
│   ├── PharmacyOrder.php
│   └── PharmacyOrderItem.php
├── Services/
│   ├── CartService.php
│   ├── PharmacyMatchingService.php
│   └── OrderService.php
├── Transformers/ (JSON Resources)
│   ├── PharmacyCategoryResource.php
│   ├── PharmacyProductResource.php
│   ├── PharmacyProductDetailResource.php
│   ├── PharmacyBrandResource.php
│   ├── PharmacyProductTypeResource.php
│   ├── CartResource.php
│   ├── AvailablePharmacyResource.php
│   ├── PharmacyOrderResource.php
│   └── PharmacyOrderDetailResource.php
├── Resources/views/backend/
│   ├── categories/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── datatable/action_column.blade.php
│   ├── brands/
│   ├── product-types/
│   ├── products/
│   ├── pharmacies/
│   ├── orders/
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   └── datatable/action_column.blade.php
│   └── pharmacy-panel/
│       ├── inventory/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── datatable/action_column.blade.php
│       └── orders/
│           ├── index.blade.php
│           ├── show.blade.php
│           └── datatable/action_column.blade.php
├── Routes/
│   ├── api.php
│   └── web.php
├── database/
│   ├── migrations/
│   │   ├── 2026_04_05_000001_create_pharmacy_categories_table.php
│   │   ├── 2026_04_05_000002_create_pharmacy_brands_table.php
│   │   ├── 2026_04_05_000003_create_pharmacy_product_types_table.php
│   │   ├── 2026_04_05_000004_create_pharmacies_table.php
│   │   ├── 2026_04_05_000005_create_pharmacy_products_table.php
│   │   ├── 2026_04_05_000006_create_pharmacy_inventory_table.php
│   │   ├── 2026_04_05_000007_create_pharmacy_cart_items_table.php
│   │   ├── 2026_04_05_000008_create_pharmacy_orders_table.php
│   │   ├── 2026_04_05_000009_create_pharmacy_order_items_table.php
│   │   └── 2026_04_05_000010_add_pharmacy_id_to_users_table.php
│   └── seeders/
│       ├── PharmacyPermissionSeeder.php
│       └── PharmacyDatabaseSeeder.php
└── Config/
    └── config.php
```

---

## Deployment & Setup

### Initial Setup
```bash
# Run migrations
php artisan module:migrate Pharmacy

# Run seeders (creates sample data)
php artisan module:seed Pharmacy

# Clear caches
php artisan optimize:clear
```

### Sample Data Created
- 2 main categories + 3 subcategories each
- 5 brands
- 4 product types
- 10 products
- 2 pharmacies with inventory for 6 products each

---

## Performance Considerations

### Indexes
- `pharmacy_products(category_id, sub_category_id, brand_id, product_type_id, is_active)`
- `pharmacy_inventory(product_id, pharmacy_id, stock_qty, is_available)`
- `pharmacy_orders(user_id, pharmacy_id, status)`
- `pharmacy_categories(parent_id, sort_order, is_active)`

### Caching Recommendations
- Cache categories (hierarchical data doesn't change often)
- Cache filters (brands/product_types per subcategory)
- Invalidate cache on product/pharmacy changes

### Concurrency Control
- Pessimistic locking (SELECT FOR UPDATE) on pharmacy_inventory during order creation and cancellation
- Prevents race conditions where multiple customers place orders simultaneously for same pharmacy

---

## Future Enhancements

These features are explicitly OUT OF SCOPE for MVP but documented for future:

1. **Insurance Integration** — Validate customer insurance coverage, adjust pricing
2. **Promo Codes** — Apply discount codes to orders
3. **Scheduling** — Allow customers to schedule delivery for specific dates/times
4. **Multi-pharmacy Orders** — Split single order across multiple pharmacies if one doesn't stock everything
5. **Prescription Upload** — For prescription-required medications
6. **Payment Methods** — Expand beyond cash_on_delivery
7. **Ratings & Reviews** — Customer feedback on pharmacies and products
8. **Favorites/Wishlist** — Save frequently purchased items
9. **Recurring Orders** — Auto-order regular medications

---

## Status Codes & Common Errors

### Customer API Errors

**422 - Validation/Business Logic Error:**
```json
{
  "status": false,
  "message": "Product is out of stock or insufficient quantity available"
}
```

**404 - Not Found:**
```json
{
  "status": false,
  "message": "Order not found or does not belong to you"
}
```

**401 - Unauthenticated:**
- Missing or invalid bearer token
- Use `/api/auth/login` to obtain token

### Admin Panel Errors

Uses standard Laravel validation error responses with field-level error messages.

---

## References

- **Customer API Contract**: `specs/010-pharmacy-marketplace/contracts/api-endpoints.md`
- **Task List**: `specs/010-pharmacy-marketplace/tasks.md`
- **Specification**: `specs/010-pharmacy-marketplace/spec.md`
- **Planning Document**: `specs/010-pharmacy-marketplace/plan.md`

---

**Maintained by**: Development Team
**Last Review**: 2026-04-05
**Status**: Production Ready ✅
