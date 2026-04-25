# API Contracts: Pharmacy Marketplace

**Branch**: `010-pharmacy-marketplace` | **Date**: 2026-04-05
**Base URL**: `/api/v1/pharmacy`
**Auth**: All endpoints require `auth:sanctum` middleware unless noted.

---

## Categories

### GET /categories

List all active top-level categories.

**Auth**: Required
**Response** `200`:
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "Medicines",
      "name_ar": "أدوية",
      "name_en": "Medicines",
      "image": "https://example.com/storage/categories/1/image.jpg",
      "sort_order": 0
    }
  ]
}
```

### GET /categories/{id}/children

List active subcategories under a given parent.

**Auth**: Required
**Path**: `id` — parent category ID
**Response** `200`:
```json
{
  "status": true,
  "data": [
    {
      "id": 5,
      "name": "Pain Relief",
      "name_ar": "مسكنات",
      "name_en": "Pain Relief",
      "image": "https://example.com/storage/categories/5/image.jpg",
      "sort_order": 0
    }
  ]
}
```
**Response** `404`: Category not found

---

## Products

### GET /products

List active products with filtering and pagination. Only products with at least one pharmacy offering (stock > 0, is_available = true) are returned.

**Auth**: Required
**Query Parameters**:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| sub_category_id | integer | No | Filter by subcategory |
| search | string | No | Search by product name (ar/en) |
| brand_ids[] | array of int | No | Filter by brand IDs |
| product_type_ids[] | array of int | No | Filter by product type IDs |
| per_page | integer | No | Default: 15 |
| page | integer | No | Default: 1 |

**Response** `200`:
```json
{
  "status": true,
  "data": [
    {
      "id": 10,
      "name": "Panadol Extra",
      "name_ar": "بانادول إكسترا",
      "name_en": "Panadol Extra",
      "image": "https://example.com/storage/products/10/image.jpg",
      "product_type": { "id": 2, "name": "Tablets" },
      "brand": { "id": 1, "name": "GSK" },
      "price_from": 2.50,
      "is_in_stock": true,
      "requires_prescription": false
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3
  }
}
```

### GET /products/{id}

Get full product details.

**Auth**: Required
**Response** `200`:
```json
{
  "status": true,
  "data": {
    "id": 10,
    "name": "Panadol Extra",
    "name_ar": "بانادول إكسترا",
    "name_en": "Panadol Extra",
    "image": "https://example.com/storage/products/10/image.jpg",
    "description": "Pain relief tablets...",
    "category": { "id": 1, "name": "Medicines" },
    "subcategory": { "id": 5, "name": "Pain Relief" },
    "product_type": { "id": 2, "name": "Tablets" },
    "brand": { "id": 1, "name": "GSK" },
    "price_from": 2.50,
    "is_in_stock": true,
    "requires_prescription": false
  }
}
```
**Response** `404`: Product not found or inactive

---

## Filters

### GET /filters/brands

List brands that have active products in a given subcategory.

**Auth**: Required
**Query**: `sub_category_id` (required, integer)
**Response** `200`:
```json
{
  "status": true,
  "data": [
    { "id": 1, "name": "GSK" },
    { "id": 3, "name": "Pfizer" }
  ]
}
```

### GET /filters/product-types

List product types that have active products in a given subcategory.

**Auth**: Required
**Query**: `sub_category_id` (required, integer)
**Response** `200`:
```json
{
  "status": true,
  "data": [
    { "id": 2, "name": "Tablets", "name_ar": "أقراص", "name_en": "Tablets" },
    { "id": 4, "name": "Syrup", "name_ar": "شراب", "name_en": "Syrup" }
  ]
}
```

---

## Cart

### GET /cart

Get the current user's cart contents.

**Auth**: Required
**Response** `200`:
```json
{
  "status": true,
  "data": {
    "items": [
      {
        "id": 1,
        "product": {
          "id": 10,
          "name": "Panadol Extra",
          "image": "https://...",
          "price_from": 2.50,
          "is_in_stock": true
        },
        "quantity": 3
      }
    ],
    "item_count": 1
  }
}
```

### POST /cart/items

Add a product to the cart. If the product already exists in the cart, its quantity is updated (added).

**Auth**: Required
**Body**:
```json
{
  "product_id": 10,
  "quantity": 2
}
```
**Validation**:
- `product_id`: required, exists, product must be active
- `quantity`: required, integer, min:1
- At least one pharmacy must have `stock_qty >= total requested quantity` for this product

**Response** `200`: Updated cart (same format as GET /cart)
**Response** `422`: Validation error (e.g., insufficient stock at any single pharmacy)

### PATCH /cart/items/{id}

Update quantity of a cart item.

**Auth**: Required
**Path**: `id` — cart item ID
**Body**:
```json
{
  "quantity": 5
}
```
**Validation**:
- `quantity`: required, integer, min:1
- At least one pharmacy must have `stock_qty >= new quantity`

**Response** `200`: Updated cart
**Response** `422`: Validation error

### DELETE /cart/items/{id}

Remove an item from the cart.

**Auth**: Required
**Path**: `id` — cart item ID (must belong to authenticated user)
**Response** `200`:
```json
{
  "status": true,
  "message": "Item removed from cart"
}
```
**Response** `404`: Cart item not found

---

## Pharmacy Matching

### GET /cart/available-pharmacies

Find pharmacies that can fulfill the entire cart.

**Auth**: Required
**Query**: `address_id` (required, integer — for delivery context)

**Validation**:
- Cart must not be empty (returns 422 if empty)
- `address_id` must exist and belong to authenticated user

**Response** `200`:
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "Al-Shifa Pharmacy",
      "logo": "https://...",
      "phone": "+965-12345678",
      "address": "Block 4, Street 10, Hawally",
      "latitude": 29.3375,
      "longitude": 47.9774,
      "subtotal": 12.50,
      "delivery_fee": 2.00,
      "total": 14.50
    }
  ],
  "message": null
}
```
**Response** `200` (no matches):
```json
{
  "status": true,
  "data": [],
  "message": "No pharmacy can fulfill all items in your cart. Please adjust your cart and try again."
}
```
**Response** `422`: Cart is empty or address_id invalid

---

## Orders

### POST /orders

Place an order from the current cart with a selected pharmacy.

**Auth**: Required
**Body**:
```json
{
  "pharmacy_id": 1,
  "address_id": 5,
  "payment_method": "cash_on_delivery"
}
```
**Validation**:
- Cart must not be empty
- `pharmacy_id`: required, exists, pharmacy must be active, pharmacy must stock ALL cart items in sufficient quantity
- `address_id`: required, exists, must belong to authenticated user
- `payment_method`: required, in:cash_on_delivery

**Behavior** (atomic transaction):
1. Lock pharmacy inventory rows for cart products
2. Validate stock sufficiency
3. Create order record with calculated totals
4. Create order item snapshots (price + name at time of order)
5. Decrement stock for each item
6. Clear customer's cart
7. Commit transaction

**Response** `201`:
```json
{
  "status": true,
  "data": {
    "id": 42,
    "status": "pending",
    "pharmacy": { "id": 1, "name": "Al-Shifa Pharmacy" },
    "subtotal": 12.50,
    "delivery_fee": 2.00,
    "total": 14.50,
    "payment_method": "cash_on_delivery",
    "created_at": "2026-04-05T10:30:00Z"
  },
  "message": "Order placed successfully"
}
```
**Response** `422`: Validation error (out of stock, invalid pharmacy, etc.)

### GET /orders

List the current user's orders (paginated, newest first).

**Auth**: Required
**Query**: `per_page` (default: 15), `page` (default: 1), `status` (optional filter)
**Response** `200`:
```json
{
  "status": true,
  "data": [
    {
      "id": 42,
      "pharmacy": { "id": 1, "name": "Al-Shifa Pharmacy" },
      "total": 14.50,
      "status": "pending",
      "item_count": 3,
      "created_at": "2026-04-05T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 8,
    "last_page": 1
  }
}
```

### GET /orders/{id}

Get full order details including items.

**Auth**: Required (must be order owner)
**Response** `200`:
```json
{
  "status": true,
  "data": {
    "id": 42,
    "status": "confirmed",
    "pharmacy": {
      "id": 1,
      "name": "Al-Shifa Pharmacy",
      "phone": "+965-12345678",
      "address": "Block 4, Street 10, Hawally"
    },
    "items": [
      {
        "id": 1,
        "product_id": 10,
        "product_name": "Panadol Extra",
        "quantity": 3,
        "price": 2.50,
        "line_total": 7.50
      }
    ],
    "subtotal": 12.50,
    "delivery_fee": 2.00,
    "total": 14.50,
    "payment_method": "cash_on_delivery",
    "address": {
      "address_line_1": "House 5, Block 3",
      "city": "Hawally"
    },
    "created_at": "2026-04-05T10:30:00Z",
    "updated_at": "2026-04-05T11:00:00Z"
  }
}
```
**Response** `404`: Order not found or not owned by user

### POST /orders/{id}/cancel

Cancel a pending order. Stock is restored.

**Auth**: Required (must be order owner)
**Validation**:
- Order must be in `pending` status

**Behavior**:
1. Validate order is `pending`
2. Restore stock for all order items at the pharmacy
3. Set status to `cancelled`

**Response** `200`:
```json
{
  "status": true,
  "message": "Order cancelled successfully"
}
```
**Response** `422`: Order cannot be cancelled (not in pending status)
**Response** `404`: Order not found

---

## Error Response Format

All error responses follow this format:

```json
{
  "status": false,
  "message": "Human-readable error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

HTTP status codes used:
- `200` — Success
- `201` — Created (order placement)
- `404` — Resource not found
- `422` — Validation error
- `401` — Unauthenticated
- `403` — Unauthorized (wrong role / not owner)
