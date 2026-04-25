# Quickstart: Pharmacy Marketplace

**Branch**: `010-pharmacy-marketplace` | **Date**: 2026-04-05

## Prerequisites

- PHP 8.2+, Composer, Node.js 18+
- MySQL 8.0+ (production) / SQLite (testing)
- Existing Espitalia installation with `composer install` and `npm install` completed

## Setup Steps

### 1. Create the Module Scaffold

```bash
php artisan module:make Pharmacy
```

This creates `Modules/Pharmacy/` with the standard nwidart structure.

### 2. Run Migrations

```bash
php artisan module:migrate Pharmacy
```

Creates all pharmacy tables: `pharmacy_categories`, `pharmacy_brands`, `pharmacy_product_types`, `pharmacy_products`, `pharmacies`, `pharmacy_inventory`, `pharmacy_cart_items`, `pharmacy_orders`, `pharmacy_order_items`, and adds `pharmacy_id` to `users`.

### 3. Seed Permissions

```bash
php artisan module:seed Pharmacy --class=PharmacyPermissionSeeder
```

Creates the `pharmacy` role and assigns permissions:
- Admin: full pharmacy permissions
- Pharmacy role: inventory + own orders only

### 4. Enable the Module

```bash
php artisan module:enable Pharmacy
```

Verify in `modules_statuses.json`: `"Pharmacy": true`

### 5. Build Frontend Assets

```bash
npm run dev
```

webpack.mix.js auto-discovers `Modules/Pharmacy/Resources/assets/js/app.js` and outputs to `public/modules/pharmacy/script.js`.

### 6. Clear Caches

```bash
php artisan optimize:clear
composer dump-autoload
```

### 7. Verify

```bash
# Check routes registered
php artisan route:list --path=pharmacy

# Check module status
php artisan module:list

# Run tests
php artisan test --filter=Pharmacy
```

## Key File Locations

| Purpose | Path |
|---------|------|
| Models | `Modules/Pharmacy/Models/` |
| API Controllers | `Modules/Pharmacy/Http/Controllers/API/` |
| Admin Controllers | `Modules/Pharmacy/Http/Controllers/` |
| Pharmacy Panel Controllers | `Modules/Pharmacy/Http/Controllers/PharmacyPanel/` |
| Form Requests | `Modules/Pharmacy/Http/Requests/` |
| API Resources | `Modules/Pharmacy/Transformers/` |
| Services | `Modules/Pharmacy/Services/` |
| Web Routes | `Modules/Pharmacy/Routes/web.php` |
| API Routes | `Modules/Pharmacy/Routes/api.php` |
| Migrations | `Modules/Pharmacy/database/migrations/` |
| Seeders | `Modules/Pharmacy/database/seeders/` |
| Admin Views | `Modules/Pharmacy/Resources/views/backend/` |
| Tests | `Modules/Pharmacy/Tests/Feature/` and `Tests/Unit/` |
| Translations (EN) | `lang/en/pharmacy.php` |
| Translations (AR) | `lang/ar/pharmacy.php` |
| Sidebar Menu | `app/Http/Middleware/GenerateMenus.php` |

## Testing a Cart-to-Order Flow

```bash
# 1. Seed test data (categories, products, pharmacy, inventory)
php artisan module:seed Pharmacy

# 2. Create a test user and get API token
# POST /api/v1/login → bearer token

# 3. Browse categories
# GET /api/v1/pharmacy/categories

# 4. List products in a subcategory
# GET /api/v1/pharmacy/products?sub_category_id=5

# 5. Add to cart
# POST /api/v1/pharmacy/cart/items { "product_id": 10, "quantity": 2 }

# 6. Find pharmacies
# GET /api/v1/pharmacy/cart/available-pharmacies?address_id=1

# 7. Place order
# POST /api/v1/pharmacy/orders { "pharmacy_id": 1, "address_id": 1, "payment_method": "cash_on_delivery" }
```

## New Role: `pharmacy`

After seeding, pharmacy staff users need:
1. The `pharmacy` role assigned via spatie/laravel-permission
2. `pharmacy_id` set on their user record pointing to their pharmacy

Admin can manage this through the existing user management interface (add pharmacy assignment field).
