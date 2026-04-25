# Data Model: Pharmacy Marketplace

**Branch**: `010-pharmacy-marketplace` | **Date**: 2026-04-05

## Entity Relationship Overview

```
PharmacyCategory (parent/child self-ref)
    └── PharmacyProduct ──→ PharmacyBrand
         │                  PharmacyProductType
         └── PharmacyInventory ──→ Pharmacy ──→ User (pharmacy role, 1:many)
                                      │
PharmacyCartItem ──→ User (customer)  │
         │                             │
         └──────── PharmacyProduct     │
                                       │
PharmacyOrder ──→ User (customer)      │
    │             Pharmacy ◄───────────┘
    │             Address (polymorphic, existing)
    └── PharmacyOrderItem ──→ PharmacyProduct
```

---

## Entities

### PharmacyCategory

**Table**: `pharmacy_categories`

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| id | bigint unsigned | PK, auto-increment | |
| parent_id | bigint unsigned, nullable | FK → pharmacy_categories.id, ON DELETE CASCADE | NULL = main category; non-null = subcategory |
| name_ar | string(255) | required | Arabic name |
| name_en | string(255) | required | English name |
| image | via MediaLibrary | optional | Spatie media collection: `category_image` |
| is_active | boolean | default: true | Controls visibility in API |
| sort_order | integer | default: 0 | Display ordering within same level |
| created_by | integer unsigned, nullable | audit | Auto-set by BaseModel |
| updated_by | integer unsigned, nullable | audit | Auto-set by BaseModel |
| deleted_by | integer unsigned, nullable | audit | Auto-set by BaseModel |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp, nullable | soft delete | |

**Relationships**:
- `parent()` → belongsTo(PharmacyCategory, 'parent_id')
- `children()` → hasMany(PharmacyCategory, 'parent_id')
- `products()` → hasMany(PharmacyProduct, 'category_id') — for main categories
- `subcategoryProducts()` → hasMany(PharmacyProduct, 'sub_category_id') — for subcategories

**Indexes**: `parent_id`, `is_active`, composite `(parent_id, sort_order)`

**Validation**:
- `name_ar` and `name_en`: required, string, max:255
- `parent_id`: nullable, exists:pharmacy_categories,id, must not be self-referencing, max depth = 1 (subcategory cannot have children)
- `sort_order`: integer, min:0

---

### PharmacyBrand

**Table**: `pharmacy_brands`

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| id | bigint unsigned | PK | |
| name | string(255) | required, unique | Brand display name |
| is_active | boolean | default: true | |
| created_by | integer unsigned, nullable | audit | |
| updated_by | integer unsigned, nullable | audit | |
| deleted_by | integer unsigned, nullable | audit | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp, nullable | soft delete | |

**Relationships**:
- `products()` → hasMany(PharmacyProduct, 'brand_id')

**Validation**:
- `name`: required, string, max:255, unique (excluding soft-deleted)

---

### PharmacyProductType

**Table**: `pharmacy_product_types`

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| id | bigint unsigned | PK | |
| name_ar | string(255) | required | |
| name_en | string(255) | required | |
| created_by | integer unsigned, nullable | audit | |
| updated_by | integer unsigned, nullable | audit | |
| deleted_by | integer unsigned, nullable | audit | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp, nullable | soft delete | |

**Relationships**:
- `products()` → hasMany(PharmacyProduct, 'product_type_id')

**Validation**:
- `name_ar`, `name_en`: required, string, max:255

---

### PharmacyProduct

**Table**: `pharmacy_products`

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| id | bigint unsigned | PK | |
| name_ar | string(255) | required | Arabic product name |
| name_en | string(255) | required | English product name |
| category_id | bigint unsigned | FK → pharmacy_categories.id | Main category (parent_id IS NULL) |
| sub_category_id | bigint unsigned | FK → pharmacy_categories.id | Subcategory (parent_id IS NOT NULL) |
| brand_id | bigint unsigned, nullable | FK → pharmacy_brands.id | Optional |
| product_type_id | bigint unsigned, nullable | FK → pharmacy_product_types.id | Optional |
| image | via MediaLibrary | optional | Spatie media collection: `product_image` |
| description | text, nullable | | Product description |
| requires_prescription | boolean | default: false | Informational flag for MVP |
| is_active | boolean | default: true | Admin-controlled visibility |
| created_by | integer unsigned, nullable | audit | |
| updated_by | integer unsigned, nullable | audit | |
| deleted_by | integer unsigned, nullable | audit | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp, nullable | soft delete | |

**Relationships**:
- `category()` → belongsTo(PharmacyCategory, 'category_id')
- `subcategory()` → belongsTo(PharmacyCategory, 'sub_category_id')
- `brand()` → belongsTo(PharmacyBrand, 'brand_id')
- `productType()` → belongsTo(PharmacyProductType, 'product_type_id')
- `inventories()` → hasMany(PharmacyInventory, 'product_id')
- `orderItems()` → hasMany(PharmacyOrderItem, 'product_id')

**Computed (query-time)**:
- `price_from` → MIN(pharmacy_inventory.price) WHERE stock_qty > 0 AND is_available = true
- `is_in_stock` → EXISTS(pharmacy_inventory WHERE stock_qty > 0 AND is_available = true)

**Indexes**: `category_id`, `sub_category_id`, `brand_id`, `product_type_id`, `is_active`

**Validation**:
- `name_ar`, `name_en`: required, string, max:255
- `category_id`: required, exists:pharmacy_categories,id (must be a main category)
- `sub_category_id`: required, exists:pharmacy_categories,id (must be a child of category_id)
- `brand_id`: nullable, exists:pharmacy_brands,id
- `product_type_id`: nullable, exists:pharmacy_product_types,id

---

### Pharmacy

**Table**: `pharmacies`

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| id | bigint unsigned | PK | |
| name | string(255) | required | Pharmacy display name |
| logo | via MediaLibrary | optional | Spatie media collection: `logo` |
| phone | string(20) | required | |
| address | text | required | Full text address |
| latitude | double | nullable | GPS lat — display only in MVP |
| longitude | double | nullable | GPS lng — display only in MVP |
| delivery_fee | decimal(8,2) | default: 0.00 | Fixed delivery fee |
| is_active | boolean | default: true | |
| created_by | integer unsigned, nullable | audit | |
| updated_by | integer unsigned, nullable | audit | |
| deleted_by | integer unsigned, nullable | audit | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp, nullable | soft delete | |

**Relationships**:
- `inventory()` → hasMany(PharmacyInventory, 'pharmacy_id')
- `orders()` → hasMany(PharmacyOrder, 'pharmacy_id')
- `staff()` → hasMany(User, 'pharmacy_id') — pharmacy role users

**Indexes**: `is_active`

**Validation**:
- `name`: required, string, max:255
- `phone`: required, string, max:20
- `address`: required, string
- `delivery_fee`: required, numeric, min:0
- `latitude`, `longitude`: nullable, numeric

---

### User (modification to existing table)

**Table**: `users` — ADD COLUMN

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| pharmacy_id | bigint unsigned, nullable | FK → pharmacies.id, ON DELETE SET NULL | Only populated for pharmacy-role users |

**New relationship on User model**:
- `pharmacy()` → belongsTo(Pharmacy, 'pharmacy_id')

---

### PharmacyInventory

**Table**: `pharmacy_inventory`

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| id | bigint unsigned | PK | |
| pharmacy_id | bigint unsigned | FK → pharmacies.id, ON DELETE CASCADE | |
| product_id | bigint unsigned | FK → pharmacy_products.id, ON DELETE CASCADE | |
| price | decimal(10,2) | required | Pharmacy-specific price for this product |
| stock_qty | integer unsigned | default: 0 | Current stock count |
| is_available | boolean | default: true | Pharmacy can toggle availability |
| created_by | integer unsigned, nullable | audit | |
| updated_by | integer unsigned, nullable | audit | |
| deleted_by | integer unsigned, nullable | audit | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp, nullable | soft delete | |

**Relationships**:
- `pharmacy()` → belongsTo(Pharmacy, 'pharmacy_id')
- `product()` → belongsTo(PharmacyProduct, 'product_id')

**Unique constraint**: `(pharmacy_id, product_id)` — one inventory record per product per pharmacy

**Indexes**: `pharmacy_id`, `product_id`, composite `(pharmacy_id, product_id)` UNIQUE, composite `(product_id, stock_qty, is_available)` — for availability queries

**Validation**:
- `pharmacy_id`: required, exists:pharmacies,id
- `product_id`: required, exists:pharmacy_products,id
- `price`: required, numeric, min:0.01
- `stock_qty`: required, integer, min:0

---

### PharmacyCartItem

**Table**: `pharmacy_cart_items`

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| id | bigint unsigned | PK | |
| user_id | bigint unsigned | FK → users.id, ON DELETE CASCADE | The customer |
| product_id | bigint unsigned | FK → pharmacy_products.id, ON DELETE CASCADE | |
| quantity | integer unsigned | required, min: 1 | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Note**: No `price` column. Price is determined at pharmacy-matching and order-placement time from `pharmacy_inventory`. No soft deletes — cart items are hard-deleted when removed or when cart is cleared at order placement.

**Relationships**:
- `user()` → belongsTo(User, 'user_id')
- `product()` → belongsTo(PharmacyProduct, 'product_id')

**Unique constraint**: `(user_id, product_id)` — one cart item per product per user; adding the same product increases quantity.

**Indexes**: `user_id`, composite `(user_id, product_id)` UNIQUE

**Validation**:
- `product_id`: required, exists:pharmacy_products,id, product must be active
- `quantity`: required, integer, min:1, max: highest stock_qty across any single pharmacy for this product

---

### PharmacyOrder

**Table**: `pharmacy_orders`

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| id | bigint unsigned | PK | |
| user_id | bigint unsigned | FK → users.id | The customer |
| pharmacy_id | bigint unsigned | FK → pharmacies.id | The fulfilling pharmacy |
| address_id | bigint unsigned | FK → addresses.id | Delivery address snapshot reference |
| subtotal | decimal(10,2) | | Sum of (item price * quantity) |
| delivery_fee | decimal(8,2) | | Copied from pharmacy at order time |
| total | decimal(10,2) | | subtotal + delivery_fee |
| payment_method | string(50) | default: 'cash_on_delivery' | Extensible string field |
| status | string(20) | default: 'pending' | pending, confirmed, preparing, on_the_way, delivered, cancelled |
| created_by | integer unsigned, nullable | audit | |
| updated_by | integer unsigned, nullable | audit | |
| deleted_by | integer unsigned, nullable | audit | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp, nullable | soft delete | |

**State transitions**:
```
pending ──→ confirmed ──→ preparing ──→ on_the_way ──→ delivered
  │              │
  ├──→ cancelled (customer or pharmacy rejects)
  │
  └──→ cancelled (triggers stock restoration for all items)
```

Note: Admin can set any status directly (FR-028).

**Relationships**:
- `user()` → belongsTo(User, 'user_id')
- `pharmacy()` → belongsTo(Pharmacy, 'pharmacy_id')
- `address()` → belongsTo(Address, 'address_id')
- `items()` → hasMany(PharmacyOrderItem, 'order_id')

**Indexes**: `user_id`, `pharmacy_id`, `status`, composite `(user_id, status)`

**Validation (create)**:
- `pharmacy_id`: required, exists:pharmacies,id, pharmacy must be active
- `address_id`: required, exists:addresses,id, must belong to authenticated user
- `payment_method`: required, in:cash_on_delivery

---

### PharmacyOrderItem

**Table**: `pharmacy_order_items`

| Field | Type | Constraints | Notes |
|-------|------|------------|-------|
| id | bigint unsigned | PK | |
| order_id | bigint unsigned | FK → pharmacy_orders.id, ON DELETE CASCADE | |
| product_id | bigint unsigned | FK → pharmacy_products.id | Immutable snapshot reference |
| product_name | string(255) | | Snapshot of product name at order time |
| quantity | integer unsigned | | |
| price | decimal(10,2) | | Snapshot of pharmacy_inventory.price at order time |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Note**: Immutable after creation. `product_name` and `price` are snapshots — they do not change if the product or inventory is later modified. No soft deletes.

**Relationships**:
- `order()` → belongsTo(PharmacyOrder, 'order_id')
- `product()` → belongsTo(PharmacyProduct, 'product_id')

**Indexes**: `order_id`, `product_id`

---

## Migration Order

1. `create_pharmacy_categories_table` — no dependencies
2. `create_pharmacy_brands_table` — no dependencies
3. `create_pharmacy_product_types_table` — no dependencies
4. `create_pharmacies_table` — no dependencies
5. `create_pharmacy_products_table` — depends on categories, brands, product_types
6. `create_pharmacy_inventory_table` — depends on pharmacies, pharmacy_products
7. `create_pharmacy_cart_items_table` — depends on users, pharmacy_products
8. `create_pharmacy_orders_table` — depends on users, pharmacies, addresses
9. `create_pharmacy_order_items_table` — depends on pharmacy_orders, pharmacy_products
10. `add_pharmacy_id_to_users_table` — depends on pharmacies
