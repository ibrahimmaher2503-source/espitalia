# Espitalia Laravel - Project Initialization & Complete Module Guide

## Prerequisites

- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL / MariaDB / PostgreSQL
- Redis (optional, for queues)

---

## 1. Project Setup (First Time)

### Step 1: Clone & Install Dependencies

```bash
composer install
npm install
```

### Step 2: Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure:

```dotenv
APP_NAME=Espitalia
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kivicare
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525

STRIPE_KEY=
STRIPE_SECRET=
RAZORPAY_KEY=
RAZORPAY_SECRET=
```

### Step 3: Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### Step 4: Build Frontend Assets

```bash
npm run dev          # Development
npm run production   # Production
```

### Step 5: Storage & Permissions

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache   # Linux/Mac only
```

### Step 6: Start

```bash
php artisan serve
```

---

## 2. Project Architecture

```
kivicare-laravel/
├── app/                    # Core application code
│   ├── Http/
│   │   ├── Controllers/    # Core controllers
│   │   └── Middleware/
│   │       └── GenerateMenus.php   # ← Sidebar menu builder
│   ├── Models/             # Core models (User, Setting, Role...)
│   ├── Trait/
│   │   └── Menu.php        # Menu helper trait
│   ├── Providers/
│   └── helpers.php
├── Modules/                # Feature modules (nwidart/laravel-modules v10)
│   ├── Appointment/
│   ├── Blog/
│   ├── Clinic/
│   ├── Frontend/           # ← User-facing frontend module
│   ├── FrontendSetting/    # ← Frontend section settings
│   └── ... (33+ modules)
├── config/
├── lang/en/                # ← Core language files (sidebar.php, messages.php, etc.)
├── resources/
│   ├── views/
│   │   ├── backend/layouts/app.blade.php    # ← Admin layout
│   │   ├── backend/includes/sidebar.blade.php
│   │   └── components/backend/              # ← Reusable components
│   └── js/                 # Vue.js & JS files
├── routes/
├── modules_statuses.json
└── public/
```

---

## 3. Complete Steps to Add a New Module

Replace `YourModule` with your actual module name (e.g., `Insurance`, `Pharmacy`, `Lab`).

---

### STEP 1: Generate Module

```bash
php artisan module:make YourModule
```

Creates `Modules/YourModule/` with full structure.

---

### STEP 2: Create Model + Migration

```bash
php artisan module:make-model YourModel YourModule -m
```

**Edit the Model** (`Modules/YourModule/Models/YourModel.php`):

```php
<?php

namespace Modules\YourModule\Models;

use App\Models\BaseModel;

class YourModel extends BaseModel
{
    protected $table = 'your_models';

    protected $fillable = [
        'name',
        'description',
        'status',
        'company_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
```

**Edit the Migration** (`Modules/YourModule/database/migrations/xxxx_create_your_models_table.php`):

```php
public function up(): void
{
    Schema::create('your_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->boolean('status')->default(1);
        $table->unsignedBigInteger('company_id')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}
```

Run it:

```bash
php artisan module:migrate YourModule
```

---

### STEP 3: Create Language File

Create `lang/en/your_module.php` in the **core lang directory**:

```php
<?php

return [
    // Page Titles
    'singular_title' => 'Your Module',
    'plural_title' => 'Your Modules',

    // Labels (prefix with lbl_)
    'lbl_name' => 'Name',
    'lbl_description' => 'Description',
    'lbl_status' => 'Status',
    'lbl_action' => 'Action',
    'lbl_id' => 'ID',
    'lbl_update_at' => 'Updated At',
    'lbl_created_at' => 'Created At',
    'lbl_image' => 'Image',

    // Form Labels
    'lbl_select_status' => 'Select Status',
    'lbl_enter_name' => 'Enter Name',
    'lbl_enter_description' => 'Enter Description',

    // Messages
    'save_success' => 'Your Module saved successfully',
    'update_success' => 'Your Module updated successfully',
    'delete_success' => 'Your Module deleted successfully',
    'not_found' => 'Your Module not found',

    // Advanced Filter
    'lbl_advanced_filter' => 'Advanced Filter',
];
```

Also add sidebar entry to `lang/en/sidebar.php`:

```php
// Add this key to the existing sidebar.php file:
'your_module' => 'Your Module',
```

Also add menu section to `lang/en/menu.php` if needed:

```php
// Add to existing menu.php:
'your_module_section' => 'YOUR MODULE',
```

**How translations work in this project:**
- Backend blade: `{{ __('your_module.lbl_name') }}`
- Controllers: `trans('your_module.save_success')` or `__('your_module.save_success')`
- Vue.js frontend: `{{ $t('your_module.lbl_name') }}` (auto-loaded from `window.localMessagesUpdate`)
- All `lang/en/*.php` files are automatically loaded into Vue i18n via `resources/views/backend/layouts/app.blade.php`
- Database overrides via `Modules/Language` take priority over file translations

---

### STEP 4: Create Admin Dashboard Controller

```bash
php artisan module:make-controller YourModuleController YourModule
```

Edit `Modules/YourModule/Http/Controllers/YourModuleController.php`:

```php
<?php

namespace Modules\YourModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\YourModule\Models\YourModel;
use Illuminate\Http\Request;

class YourModuleController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'your_module.plural_title';
        $this->module_name = 'your-module';
    }

    public function index()
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;

        return view('yourmodule::backend.index', compact('module_title', 'module_name'));
    }

    public function index_data(Request $request)
    {
        $query = YourModel::query();

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'data' => $query->orderBy('updated_at', 'desc')
                           ->paginate($request->per_page ?? 15),
        ]);
    }

    public function create()
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $data = null;

        return view('yourmodule::backend.create', compact('module_title', 'module_name', 'data'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $model = YourModel::create($data);

        return redirect()
            ->route('backend.your-module.index')
            ->with('success', __('your_module.save_success'));
    }

    public function edit($id)
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $data = YourModel::findOrFail($id);

        return view('yourmodule::backend.create', compact('module_title', 'module_name', 'data'));
    }

    public function update(Request $request, $id)
    {
        $model = YourModel::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $model->update($data);

        return redirect()
            ->route('backend.your-module.index')
            ->with('success', __('your_module.update_success'));
    }

    public function destroy($id)
    {
        $model = YourModel::findOrFail($id);
        $model->delete();

        return redirect()
            ->route('backend.your-module.index')
            ->with('success', __('your_module.delete_success'));
    }

    public function bulk_action(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action_type;

        switch ($action) {
            case 'delete':
                YourModel::whereIn('id', $ids)->delete();
                break;
            case 'change-status':
                YourModel::whereIn('id', $ids)->update(['status' => $request->status]);
                break;
        }

        return response()->json(['message' => __('messages.bulk_update_success')]);
    }

    public function update_status(Request $request, $id)
    {
        $model = YourModel::findOrFail($id);
        $model->update(['status' => $request->status]);

        return response()->json(['message' => __('your_module.update_success')]);
    }
}
```

---

### STEP 5: Create Admin Dashboard Views

**5a. Index/List Page** (`Modules/YourModule/Resources/views/backend/index.blade.php`):

```blade
@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
    <div class="card-main mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                <x-backend.quick-action url="{{ route('backend.' . $module_name . '.bulk_action') }}">
                    <select name="action_type" class="form-control select2" id="quick-action-type">
                        <option value="">{{ __('messages.no_action') }}</option>
                        <option value="change-status">{{ __('messages.lbl_status') }}</option>
                        <option value="delete">{{ __('messages.delete') }}</option>
                    </select>

                    <div class="select-status d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-control select2">
                            <option value="1">{{ __('messages.active') }}</option>
                            <option value="0">{{ __('messages.inactive') }}</option>
                        </select>
                    </div>
                </x-backend.quick-action>
            </div>

            <x-slot name="toolbar">
                <select name="column_status" id="column_status" class="form-control select2" data-filter="select">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="0">{{ __('messages.inactive') }}</option>
                    <option value="1">{{ __('messages.active') }}</option>
                </select>

                <input type="text" name="search" class="form-control dt-search"
                       placeholder="{{ __('messages.search') }}...">

                <a href="{{ route('backend.' . $module_name . '.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus-circle"></i> {{ __('messages.new') }}
                </a>
            </x-slot>
        </x-backend.section-header>

        <table id="datatable" class="table table-responsive"></table>
    </div>

    @if(session('success'))
        <div class="snackbar" id="snackbar">
            <p>{{ session('success') }}</p>
            <a href="#" onclick="dismissSnackbar(event)">Dismiss</a>
        </div>
    @endif
@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
    <script defer>
        const columns = [
            {
                name: 'check',
                data: 'check',
                title: '<input type="checkbox" id="select-all-table">',
                exportable: false,
                orderable: false,
                searchable: false,
            },
            {
                data: 'updated_at',
                name: 'updated_at',
                title: "{{ __('your_module.lbl_update_at') }}",
                visible: false,
            },
            {
                data: 'name',
                name: 'name',
                title: "{{ __('your_module.lbl_name') }}"
            },
            {
                data: 'description',
                name: 'description',
                title: "{{ __('your_module.lbl_description') }}"
            },
            {
                data: 'status',
                name: 'status',
                title: "{{ __('your_module.lbl_status') }}"
            },
            {
                data: 'action',
                name: 'action',
                title: "{{ __('your_module.lbl_action') }}",
                orderable: false,
                searchable: false,
                className: 'text-end'
            }
        ];

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route("backend." . $module_name . ".index_data") }}',
                finalColumns: columns,
                orderColumn: [[1, "desc"]],
                advanceFilter: () => {
                    return { name: $('#user_name').val() }
                }
            });
        });

        function resetQuickAction() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        }

        $('#quick-action-type').change(() => resetQuickAction());
    </script>
@endpush
```

**5b. Create/Edit Form** (`Modules/YourModule/Resources/views/backend/create.blade.php`):

```blade
@extends('backend.layouts.app')

@section('title')
    {{ isset($data) ? __('messages.edit') : __('messages.create') }} {{ __($module_title) }}
@endsection

@section('content')
    <x-backend.section-header>
        <x-slot name="toolbar">
            <a href="{{ route('backend.' . $module_name . '.index') }}" class="btn btn-primary">
                {{ __('messages.back') }}
            </a>
        </x-slot>
    </x-backend.section-header>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form(isset($data) ? 'PUT' : 'POST',
                            isset($data)
                                ? route('backend.' . $module_name . '.update', $data->id)
                                : route('backend.' . $module_name . '.store'))
                            ->attributes(['enctype' => 'multipart/form-data',
                                          'data-toggle' => 'validator'])
                            ->open() }}

                        <div class="row align-items-center">
                            {{-- Name --}}
                            <div class="form-group col-md-6">
                                {{ html()->label(__('your_module.lbl_name') . ' <span class="text-danger">*</span>', 'name')
                                    ->class('form-label') }}
                                {{ html()->text('name', $data->name ?? old('name'))
                                    ->placeholder(__('your_module.lbl_enter_name'))
                                    ->class('form-control')
                                    ->required() }}
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="form-group col-md-6">
                                {{ html()->label(__('your_module.lbl_status'), 'status')
                                    ->class('form-label mb-0') }}
                                <div class="form-check form-switch">
                                    {{ html()->hidden('status', 0) }}
                                    {{ html()->checkbox('status', old('status', $data->status ?? 1))
                                        ->class('form-check-input')
                                        ->id('status')
                                        ->value(1) }}
                                </div>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="form-group col-md-12">
                                {{ html()->label(__('your_module.lbl_description'), 'description')
                                    ->class('form-label') }}
                                {{ html()->textarea('description', $data->description ?? old('description'))
                                    ->class('form-control')
                                    ->placeholder(__('your_module.lbl_enter_description'))
                                    ->rows(4) }}
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{ html()->submit(trans('messages.save'))->class('btn btn-md btn-primary float-end') }}
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

**5c. Action Column** (`Modules/YourModule/Resources/views/backend/datatable/action_column.blade.php`):

```blade
<div class="text-end d-flex gap-3 align-items-center justify-content-end">
    <a href="{{ route('backend.your-module.edit', $data->id) }}"
       class="btn btn-icon text-primary p-0"
       data-bs-toggle="tooltip" title="{{ __('messages.edit') }}">
        <i class="ph ph-pencil-simple-line"></i>
    </a>
    <a href="{{ route('backend.your-module.destroy', $data->id) }}"
       class="btn text-danger p-0"
       data-type="ajax"
       data-method="DELETE"
       data-token="{{ csrf_token() }}"
       data-confirm="{{ __('messages.are_you_sure?', ['form' => $data->name, 'module' => __('your_module.singular_title')]) }}">
        <i class="ph ph-trash"></i>
    </a>
</div>
```

---

### STEP 6: Create Admin Routes

**Web Routes** (`Modules/YourModule/Routes/web.php`):

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\YourModule\Http\Controllers\YourModuleController;

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {
    Route::group(['prefix' => 'your-module', 'as' => 'your-module.'], function () {
        Route::post('bulk-action', [YourModuleController::class, 'bulk_action'])->name('bulk_action');
        Route::get('index-data', [YourModuleController::class, 'index_data'])->name('index_data');
        Route::post('update-status/{id}', [YourModuleController::class, 'update_status'])->name('update_status');
    });
    Route::resource('your-module', YourModuleController::class);
});
```

---

### STEP 7: Create API Controller + Routes

**API Controller** (`Modules/YourModule/Http/Controllers/API/YourModuleApiController.php`):

```php
<?php

namespace Modules\YourModule\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\YourModule\Models\YourModel;
use Modules\YourModule\Transformers\YourModelResource;
use Illuminate\Http\Request;

class YourModuleApiController extends Controller
{
    public function index(Request $request)
    {
        $query = YourModel::where('status', 1);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $data = $query->orderBy('created_at', 'desc')
                      ->paginate($request->per_page ?? 15);

        return YourModelResource::collection($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $model = YourModel::create($validated);

        return response()->json([
            'message' => __('your_module.save_success'),
            'data' => new YourModelResource($model),
        ], 201);
    }

    public function show($id)
    {
        $model = YourModel::findOrFail($id);
        return new YourModelResource($model);
    }

    public function update(Request $request, $id)
    {
        $model = YourModel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $model->update($validated);

        return response()->json([
            'message' => __('your_module.update_success'),
            'data' => new YourModelResource($model),
        ]);
    }

    public function destroy($id)
    {
        $model = YourModel::findOrFail($id);
        $model->delete();

        return response()->json([
            'message' => __('your_module.delete_success'),
        ]);
    }
}
```

**API Resource/Transformer** (`Modules/YourModule/Transformers/YourModelResource.php`):

```php
<?php

namespace Modules\YourModule\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class YourModelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

**API Routes** (`Modules/YourModule/Routes/api.php`):

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\YourModule\Http\Controllers\API\YourModuleApiController;

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::apiResource('your-module', YourModuleApiController::class);
});
```

**API Endpoints Created:**

| Method | URL | Description |
|---|---|---|
| GET | `/api/v1/your-module` | List all (paginated) |
| POST | `/api/v1/your-module` | Create new |
| GET | `/api/v1/your-module/{id}` | Get single |
| PUT | `/api/v1/your-module/{id}` | Update |
| DELETE | `/api/v1/your-module/{id}` | Delete |

---

### STEP 8: Add to Sidebar Menu

Edit `app/Http/Middleware/GenerateMenus.php`. Add your menu item inside the `handle()` method:

**Option A - Simple menu item (no children):**

```php
$this->mainRoute($menu, [
    'icon' => 'ph ph-clipboard-text',          // Phosphor icon
    'title' => __('sidebar.your_module'),       // From lang/en/sidebar.php
    'route' => 'backend.your-module.index',     // Named route
    'permission' => ['view_your_module'],        // Permission name
    'active' => ['app/your-module'],            // Active URL highlight
    'order' => 0,
]);
```

**Option B - Parent menu with sub-items:**

```php
$yourModule = $this->parentMenu($menu, [
    'icon' => 'ph ph-clipboard-text',
    'title' => __('sidebar.your_module'),
    'route' => 'backend.your-module.index',
    'permission' => ['view_your_module'],
    'nickname' => 'your_module',
    'order' => 0,
]);

$this->childMain($yourModule, [
    'icon' => 'ph ph-list-bullets',
    'title' => __('sidebar.your_module_list'),
    'route' => 'backend.your-module.index',
    'active' => 'app/your-module',
    'permission' => ['view_your_module'],
    'order' => 0,
]);

$this->childMain($yourModule, [
    'icon' => 'ph ph-plus-circle',
    'title' => __('sidebar.your_module_create'),
    'route' => 'backend.your-module.create',
    'active' => 'app/your-module/create',
    'permission' => ['add_your_module'],
    'order' => 0,
]);
```

**Option C - Under a section header:**

```php
// Add section header first
$this->staticMenu($menu, [
    'title' => __('menu.your_module_section'),
    'order' => 0,
]);

// Then add your menu item
$this->mainRoute($menu, [
    'icon' => 'ph ph-clipboard-text',
    'title' => __('sidebar.your_module'),
    'route' => 'backend.your-module.index',
    'active' => ['app/your-module'],
    'order' => 0,
]);
```

**Role-based visibility:**

```php
// Only for admin
if (auth()->user()->hasRole(['admin', 'demo_admin'])) {
    $this->mainRoute($menu, [
        'icon' => 'ph ph-clipboard-text',
        'title' => __('sidebar.your_module'),
        'route' => 'backend.your-module.index',
        'active' => ['app/your-module'],
        'order' => 0,
    ]);
}
```

**Phosphor Icons** (used in this project): Browse at https://phosphoricons.com/
Common ones: `ph-clipboard-text`, `ph-list-bullets`, `ph-plus-circle`, `ph-gear`, `ph-chart-line`, `ph-users`

---

### STEP 9: Add User Frontend Pages (Optional)

If your module needs a user-facing page (like the public website), add routes and controllers to the **Frontend module**.

**9a. Add frontend route** to `Modules/Frontend/Routes/web.php`:

```php
// Public route (no auth needed)
Route::get('/your-module-list', [YourModuleFrontController::class, 'list'])
    ->name('your-module.list');
Route::get('/your-module-details/{id}', [YourModuleFrontController::class, 'details'])
    ->name('your-module.details');
Route::get('/your-module-index-data', [YourModuleFrontController::class, 'index_data'])
    ->name('your-module.index_data');

// OR inside the auth group for logged-in users
Route::group(['middleware' => ['auth', 'user_check']], function () {
    Route::get('/my-your-module', [YourModuleFrontController::class, 'myList'])
        ->name('your-module.my-list');
});
```

**9b. Create frontend controller** (`Modules/Frontend/Http/Controllers/YourModuleFrontController.php`):

```php
<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\YourModule\Models\YourModel;
use Illuminate\Http\Request;

class YourModuleFrontController extends Controller
{
    public function list()
    {
        $pageTitle = __('your_module.plural_title');
        return view('frontend::your_module_list', compact('pageTitle'));
    }

    public function details($id)
    {
        $data = YourModel::where('status', 1)->findOrFail($id);
        $pageTitle = $data->name;
        return view('frontend::your_module_detail', compact('pageTitle', 'data'));
    }

    public function index_data(Request $request)
    {
        $query = YourModel::where('status', 1);

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(12));
    }
}
```

**9c. Create frontend views** (`Modules/Frontend/Resources/views/`):

**List page** (`your_module_list.blade.php`):

```blade
@extends('frontend::layouts.master')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    @include('frontend::components.section.breadcrumb', ['title' => $pageTitle])

    <div class="container my-5">
        <div class="row mb-4">
            <div class="col-md-6">
                <input type="text" class="form-control" id="search-input"
                       placeholder="{{ __('messages.search') }}...">
            </div>
        </div>

        <div class="row" id="data-list">
            {{-- Cards loaded via AJAX --}}
        </div>

        <div class="text-center mt-4" id="load-more-container" style="display:none;">
            <button class="btn btn-primary" id="load-more">{{ __('messages.load_more') }}</button>
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
    let currentPage = 1;

    function loadData(page = 1, reset = false) {
        const search = $('#search-input').val();
        $.get('{{ route("your-module.index_data") }}', { page, search }, function(response) {
            if (reset) $('#data-list').empty();

            response.data.forEach(item => {
                $('#data-list').append(`
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">${item.name}</h5>
                                <p class="card-text">${item.description || ''}</p>
                                <a href="/your-module-details/${item.id}" class="btn btn-sm btn-primary">
                                    {{ __('messages.show') }}
                                </a>
                            </div>
                        </div>
                    </div>
                `);
            });

            if (response.next_page_url) {
                $('#load-more-container').show();
                currentPage = response.current_page;
            } else {
                $('#load-more-container').hide();
            }
        });
    }

    $(document).ready(() => loadData());

    $('#load-more').click(() => loadData(currentPage + 1));

    let searchTimer;
    $('#search-input').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadData(1, true), 300);
    });
</script>
@endpush
```

**Detail page** (`your_module_detail.blade.php`):

```blade
@extends('frontend::layouts.master')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    @include('frontend::components.section.breadcrumb', ['title' => $pageTitle])

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h2>{{ $data->name }}</h2>
                        <p class="text-muted">{{ __('your_module.lbl_created_at') }}: {{ $data->created_at->format('d M Y') }}</p>
                        <hr>
                        <div>{!! $data->description !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

---

### STEP 10: Add Permissions (Optional but recommended)

Create a seeder to add permissions:

```bash
php artisan module:make-seed YourModulePermissionSeeder YourModule
```

Edit `Modules/YourModule/database/seeders/YourModulePermissionSeeder.php`:

```php
<?php

namespace Modules\YourModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class YourModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_your_module',
            'add_your_module',
            'edit_your_module',
            'delete_your_module',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign to admin role
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo($permissions);
    }
}
```

Run it:

```bash
php artisan module:seed YourModule --class=YourModulePermissionSeeder
```

---

### STEP 11: Enable & Clear Caches

```bash
# Ensure module is enabled
php artisan module:enable YourModule

# Clear all caches
php artisan optimize:clear
composer dump-autoload

# Verify routes
php artisan route:list --path=your-module
```

---

## 4. Complete File Checklist for New Module

### Files YOU Create/Edit:

| # | File | Purpose |
|---|---|---|
| 1 | `lang/en/your_module.php` | All translation keys |
| 2 | `lang/en/sidebar.php` | Add sidebar menu text |
| 3 | `Modules/YourModule/Models/YourModel.php` | Eloquent model |
| 4 | `Modules/YourModule/database/migrations/xxx.php` | Database schema |
| 5 | `Modules/YourModule/Http/Controllers/YourModuleController.php` | Admin controller |
| 6 | `Modules/YourModule/Http/Controllers/API/YourModuleApiController.php` | API controller |
| 7 | `Modules/YourModule/Transformers/YourModelResource.php` | API response format |
| 8 | `Modules/YourModule/Routes/web.php` | Admin web routes |
| 9 | `Modules/YourModule/Routes/api.php` | API routes |
| 10 | `Modules/YourModule/Resources/views/backend/index.blade.php` | Admin list page |
| 11 | `Modules/YourModule/Resources/views/backend/create.blade.php` | Admin create/edit form |
| 12 | `Modules/YourModule/Resources/views/backend/datatable/action_column.blade.php` | Table actions |
| 13 | `app/Http/Middleware/GenerateMenus.php` | Add sidebar menu entry |
| 14 | `Modules/Frontend/Routes/web.php` | Frontend routes (if user-facing) |
| 15 | `Modules/Frontend/Http/Controllers/YourModuleFrontController.php` | Frontend controller |
| 16 | `Modules/Frontend/Resources/views/your_module_list.blade.php` | Frontend list page |
| 17 | `Modules/Frontend/Resources/views/your_module_detail.blade.php` | Frontend detail page |

### Files AUTO-GENERATED:

| File | Created By |
|---|---|
| `Modules/YourModule/module.json` | `module:make` |
| `Modules/YourModule/composer.json` | `module:make` |
| `Modules/YourModule/Providers/YourModuleServiceProvider.php` | `module:make` |
| `Modules/YourModule/Providers/RouteServiceProvider.php` | `module:make` |
| `Modules/YourModule/Config/config.php` | `module:make` |
| `modules_statuses.json` | Updated automatically |

---

## 5. Useful Artisan Commands

| Command | Description |
|---|---|
| `php artisan module:list` | List all modules & status |
| `php artisan module:make Name` | Create new module |
| `php artisan module:make-model Name Module -m` | Model + migration |
| `php artisan module:make-controller Name Module` | Controller |
| `php artisan module:make-migration name Module` | Migration only |
| `php artisan module:make-seed Name Module` | Seeder |
| `php artisan module:make-request Name Module` | Form request |
| `php artisan module:make-resource Name Module` | API resource |
| `php artisan module:make-middleware Name Module` | Middleware |
| `php artisan module:make-test Name Module` | Test |
| `php artisan module:migrate Module` | Run migrations |
| `php artisan module:migrate-rollback Module` | Rollback |
| `php artisan module:seed Module` | Run seeders |
| `php artisan module:enable Module` | Enable module |
| `php artisan module:disable Module` | Disable module |
| `php artisan optimize:clear` | Clear all caches |
| `php artisan route:list --path=xxx` | Verify routes |
