# Quickstart: Reception Portal Enhancement

**Feature**: 021-reception-portal
**Date**: 2026-04-16

## Prerequisites

- PHP 8.2+
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+ (or MariaDB 10.6+)
- Git

## Setup

### 1. Clone and Checkout Branch

```bash
git fetch origin
git checkout 021-reception-portal
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Configure database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=espitalia
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 5. Run Permission Seeders (For Testing Changes)

After modifying permission seeders, re-run them:

```bash
# RadiologyLab permissions
php artisan module:seed RadiologyLab --class=RadiologyLabPermissionSeeder

# IntensiveCare permissions
php artisan module:seed IntensiveCare --class=IntensiveCarePermissionSeeder

# Or run all seeders fresh (CAUTION: resets data)
php artisan migrate:fresh --seed
```

### 6. Build Assets

```bash
npm run dev
```

### 7. Clear Cache

```bash
php artisan optimize:clear
```

### 8. Start Development Server

```bash
php artisan serve
```

Access at: http://localhost:8000

---

## Testing as Receptionist

### Login Credentials

Default receptionist account (after seeding):
- Email: `receptionist@example.com`
- Password: `password`

Or create a test receptionist:

```php
// Via Tinker
php artisan tinker

$user = \App\Models\User::create([
    'first_name' => 'Test',
    'last_name' => 'Receptionist',
    'email' => 'test.receptionist@example.com',
    'password' => bcrypt('password'),
    'user_type' => 'receptionist',
]);
$user->assignRole('receptionist');

// Assign to a clinic
\Modules\Clinic\Models\Receptionist::create([
    'receptionist_id' => $user->id,
    'clinic_id' => 1, // Use an existing clinic ID
    'vendor_id' => 1, // Use the clinic's vendor ID
]);
```

### Verify Permissions

```php
// Check current permissions
$user = auth()->user();
$user->getAllPermissions()->pluck('name');

// Expected permissions after implementation:
// - view_doctor_visit
// - create_doctor_visit
// - update_status_doctor_visit
// - assign_doctor_visit
// - view_test_orders
// - view_icu_admission_requests
```

---

## Development Workflow

### Files to Modify

| File | Purpose |
|------|---------|
| `app/Http/Middleware/GenerateMenus.php` | Add menu items for receptionist |
| `app/Http/Controllers/Backend/BackendController.php` | Enhance receptionistDashboard() |
| `resources/views/backend/dashboard/receptionist.blade.php` | Dashboard UI |
| `Modules/RadiologyLab/database/seeders/RadiologyLabPermissionSeeder.php` | Add receptionist permission |
| `Modules/RadiologyLab/Http/Controllers/Backend/TestOrdersController.php` | Add receptionist filtering |
| `Modules/IntensiveCare/database/seeders/IntensiveCarePermissionSeeder.php` | Add receptionist permission |
| `Modules/IntensiveCare/Http/Controllers/Backend/IcuAdmissionRequestsController.php` | Add receptionist filtering |

### Code Style

Run Pint before committing:

```bash
./vendor/bin/pint
```

### Testing

Run tests:

```bash
php artisan test
php artisan test --filter=ReceptionistPortalTest
```

Create test file:

```bash
php artisan make:test ReceptionistPortalTest
```

---

## Key Patterns

### Getting Receptionist's Clinic

```php
use Modules\Clinic\Models\Receptionist;

$receptionist = Receptionist::where('receptionist_id', auth()->id())->first();
$clinicId = $receptionist?->clinic_id;
```

### Filtering by Clinic

```php
// For test orders (via doctor relationship)
$query->whereHas('doctor', function ($q) use ($clinicId) {
    $q->whereHas('clinicMappings', function ($q2) use ($clinicId) {
        $q2->where('clinic_id', $clinicId);
    });
});

// For ICU requests (via patient appointments)
$clinicPatientIds = \Modules\Appointment\Models\Appointment::where('clinic_id', $clinicId)
    ->distinct()
    ->pluck('user_id');
$query->whereIn('patient_id', $clinicPatientIds);
```

### Checking Role in Controller

```php
if (auth()->user()->hasRole('receptionist')) {
    // Apply receptionist-specific logic
}
```

### Hiding Actions in Blade

```blade
@unless(auth()->user()->hasRole('receptionist'))
    <button class="btn btn-primary">Edit</button>
@endunless
```

### Adding Menu Item

```php
// In GenerateMenus.php
if (auth()->user()->hasAnyRole(['admin', 'demo_admin', 'receptionist', 'lab_technician', 'doctor'])
    && auth()->user()->can('view_test_orders')) {
    $this->mainRoute($menu, [
        'icon' => 'ph ph-test-tube',
        'title' => __('sidebar.test_orders'),
        'route' => 'backend.test-orders.index',
        'active' => ['app/test-orders'],
        'permission' => ['view_test_orders'],
        'order' => 0,
    ]);
}
```

---

## Troubleshooting

### Menu Not Showing

1. Clear cache: `php artisan optimize:clear`
2. Verify permission is assigned: `$user->can('view_test_orders')`
3. Check role: `$user->hasRole('receptionist')`

### Permission Not Working

1. Re-run seeder: `php artisan db:seed --class=SomePermissionSeeder`
2. Sync roles/permissions:
```php
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

### DataTable Empty

1. Check console for JS errors
2. Verify route returns data: Visit `/app/test-orders-index-data` directly
3. Check controller filtering logic

### 403 Forbidden

1. Verify user has required permission
2. Check middleware on route
3. Verify controller authorization
