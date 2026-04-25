# Bilingual Admin Portal — Full Consistency Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ensure every core entity in the admin portal supports bilingual (Arabic/English) input using the `_ar`/`_en` column pattern consistently.

**Architecture:** Add `_ar`/`_en` database columns for all user-facing text fields, locale-aware accessors with fallback on models, side-by-side AR/EN form inputs in admin Blade views, and both raw + computed fields in API resources. Migrate Service and Clinics from Spatie Translatable to the same pattern.

**Tech Stack:** Laravel 11, nwidart/laravel-modules, Spatie MediaLibrary, Blade + jQuery, Vue 3 (offcanvas forms), PHPUnit

---

## File Structure

### New Files
- `Modules/Blog/database/migrations/2026_04_26_000001_add_bilingual_fields_to_blogs_table.php`
- `Modules/Blog/Transformers/BlogResource.php`
- `Modules/FAQ/database/migrations/2026_04_26_000002_add_bilingual_fields_to_faqs_table.php`
- `Modules/FAQ/Transformers/FaqResource.php`
- `Modules/Product/database/migrations/2026_04_26_000003_add_bilingual_fields_to_products_table.php`
- `Modules/Page/database/migrations/2026_04_26_000004_add_bilingual_fields_to_pages_table.php`
- `Modules/Clinic/database/migrations/2026_04_26_000005_add_bilingual_fields_to_clinics_categories_table.php`
- `Modules/Service/database/migrations/2026_04_26_000006_migrate_services_from_translatable_to_bilingual.php`
- `Modules/Clinic/database/migrations/2026_04_26_000007_migrate_clinics_from_translatable_to_bilingual.php`
- `Modules/Clinic/database/migrations/2026_04_26_000008_add_title_columns_to_doctors_table.php`

### Modified Files
- `Modules/Blog/Models/Blog.php`
- `Modules/Blog/Http/Requests/BlogRequest.php`
- `Modules/Blog/Http/Controllers/BlogController.php`
- `Modules/Blog/Resources/views/blog/create.blade.php`
- `Modules/FAQ/Models/Faqs.php`
- `Modules/FAQ/Http/Requests/FAQRequest.php`
- `Modules/FAQ/Http/Controllers/FAQController.php`
- `Modules/FAQ/Resources/views/backend/faq/create.blade.php`
- `Modules/FAQ/Resources/views/backend/faq/edit.blade.php`
- `Modules/Product/Models/Product.php`
- `Modules/Product/Http/Requests/ProductRequest.php`
- `Modules/Product/Http/Controllers/Backend/ProductsController.php`
- `Modules/Product/Transformers/ProductResource.php`
- `Modules/Product/Transformers/ProductDetailResource.php`
- `Modules/Page/Models/Page.php`
- `Modules/Page/Http/Controllers/Backend/PagesController.php`
- `Modules/Page/Resources/views/backend/pages/form.blade.php`
- `Modules/Page/Transformers/PageResource.php`
- `Modules/Clinic/Models/ClinicsCategory.php`
- `Modules/Clinic/Http/Requests/ClinicsCategoryRequest.php`
- `Modules/Clinic/Http/Controllers/ClinicsCategoryController.php`
- `Modules/Clinic/Transformers/CategoryResource.php`
- `Modules/Service/Models/Service.php`
- `Modules/Service/Http/Requests/ServiceRequest.php`
- `Modules/Service/Http/Controllers/Backend/ServicesController.php`
- `Modules/Service/Transformers/ServiceResource.php`
- `Modules/Clinic/Models/Clinics.php`
- `Modules/Clinic/Http/Requests/ClinicRequest.php`
- `Modules/Clinic/Http/Controllers/ClinicesController.php`
- `Modules/Clinic/Transformers/ClinicDetailsResource.php`
- `Modules/RequestNurse/Transformers/NurseResource.php`
- `Modules/Clinic/Models/Doctor.php`
- `Modules/Clinic/Transformers/DoctorResource.php`
- `lang/en/blog.php` (create)
- `lang/en/faq.php` (create)
- `lang/en/product.php` (modify)
- `lang/en/page.php` (create)
- `lang/en/category.php` (modify)
- `lang/en/messages.php` (modify)

---

### Task 1: Blog — Migration & Model

**Files:**
- Create: `Modules/Blog/database/migrations/2026_04_26_000001_add_bilingual_fields_to_blogs_table.php`
- Modify: `Modules/Blog/Models/Blog.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('title');
            $table->string('title_en')->nullable()->after('title_ar');
            $table->longText('description_ar')->nullable()->after('description');
            $table->longText('description_en')->nullable()->after('description_ar');
        });

        DB::statement('UPDATE blogs SET title_en = title WHERE title IS NOT NULL');
        DB::statement('UPDATE blogs SET description_en = description WHERE description IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_en', 'description_ar', 'description_en']);
        });
    }
};
```

- [ ] **Step 2: Update Blog model**

In `Modules/Blog/Models/Blog.php`, update the fillable array and add accessors:

```php
protected $fillable = [
    'title', 'title_ar', 'title_en',
    'description', 'description_ar', 'description_en',
    'total_views', 'author_id', 'status', 'is_featured', 'tags',
];

public function getTitleAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->title_ar : $this->title_en;
    return $localized ?: ($locale === 'ar' ? $this->title_en : $this->title_ar) ?: '';
}

public function getDescriptionAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;
    return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
}
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: Migration runs successfully, `title_ar`, `title_en`, `description_ar`, `description_en` columns added.

- [ ] **Step 4: Commit**

```bash
git add Modules/Blog/database/migrations/2026_04_26_000001_add_bilingual_fields_to_blogs_table.php Modules/Blog/Models/Blog.php
git commit -m "feat(blog): add bilingual _ar/_en columns and locale accessors"
```

---

### Task 2: Blog — Validation, Controller & Form

**Files:**
- Modify: `Modules/Blog/Http/Requests/BlogRequest.php`
- Modify: `Modules/Blog/Http/Controllers/BlogController.php`
- Modify: `Modules/Blog/Resources/views/blog/create.blade.php`

- [ ] **Step 1: Update BlogRequest validation**

Replace the rules method in `Modules/Blog/Http/Requests/BlogRequest.php`:

```php
public function rules()
{
    $id = request()->id;
    return [
        'title_ar' => 'nullable|string|max:255|required_without:title_en',
        'title_en' => 'nullable|string|max:255|required_without:title_ar',
        'description_ar' => 'nullable|string',
        'description_en' => 'nullable|string',
    ];
}
```

- [ ] **Step 2: Update BlogController store method**

In `Modules/Blog/Http/Controllers/BlogController.php`, update the store method. Replace the data preparation section (lines 156-168):

```php
public function store(BlogRequest $request)
{
    $data = $request->except('blog_attachment');
    if ($request->has('description_en')) {
        $data['description_en'] = strip_tags($request->description_en);
    }
    if ($request->has('description_ar')) {
        $data['description_ar'] = strip_tags($request->description_ar);
    }
    $data['title'] = $request->title_en ?? $request->title_ar;
    $data['description'] = $request->description_en ?? $request->description_ar;
    $data['tags'] = isset($request->tags) ? json_encode($request->tags) : null;
    $data['author_id'] = !empty($request->author_id) ? $request->author_id : auth()->user()->id;
    $data['is_featured'] = $request->has('is_featured') ? 1 : 0;

    $blog = Blog::updateOrCreate(['id' => $data['id'] ?? null], $data);

    // ... rest of media handling stays the same ...
```

- [ ] **Step 3: Update create.blade.php form**

Replace the title field (line 26-31) with side-by-side AR/EN:

```blade
<!-- Title (Arabic) -->
<div class="form-group col-md-4">
    <div class="form-group">
        {{ html()->label(__('blog.lbl_title_ar') . ' <span class="text-danger">*</span>', 'title_ar')->class('form-label') }}
        {{ html()->text('title_ar', $blogdata->title_ar ?? old('title_ar'))
            ->placeholder(__('blog.lbl_title_ar'))
            ->class('form-control')
            ->attribute('dir', 'rtl') }}
        @error('title_ar')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<!-- Title (English) -->
<div class="form-group col-md-4">
    <div class="form-group">
        {{ html()->label(__('blog.lbl_title_en') . ' <span class="text-danger">*</span>', 'title_en')->class('form-label') }}
        {{ html()->text('title_en', $blogdata->title_en ?? old('title_en'))
            ->placeholder(__('blog.lbl_title_en'))
            ->class('form-control') }}
        @error('title_en')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>
```

Replace the description field (lines 112-117) with side-by-side AR/EN:

```blade
<!-- Description (Arabic) -->
<div class="col-md-6">
    <div class="form-group">
        {{ html()->label(__('blog.lbl_description_ar'), 'description_ar')->class('form-control-label') }}
        {{ html()->textarea('description_ar', $blogdata->description_ar ?? old('description_ar'))
            ->class('form-control tinymce-template')
            ->attribute('dir', 'rtl')
            ->placeholder(__('blog.lbl_description_ar')) }}
        @error('description_ar')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<!-- Description (English) -->
<div class="col-md-6">
    <div class="form-group">
        {{ html()->label(__('blog.lbl_description_en'), 'description_en')->class('form-control-label') }}
        {{ html()->textarea('description_en', $blogdata->description_en ?? old('description_en'))
            ->class('form-control tinymce-template')
            ->placeholder(__('blog.lbl_description_en')) }}
        @error('description_en')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>
```

- [ ] **Step 4: Create translation file**

Create `lang/en/blog.php`:

```php
<?php

return [
    'lbl_title_ar' => 'Title (Arabic)',
    'lbl_title_en' => 'Title (English)',
    'lbl_description_ar' => 'Description (Arabic)',
    'lbl_description_en' => 'Description (English)',
];
```

- [ ] **Step 5: Commit**

```bash
git add Modules/Blog/Http/Requests/BlogRequest.php Modules/Blog/Http/Controllers/BlogController.php Modules/Blog/Resources/views/blog/create.blade.php lang/en/blog.php
git commit -m "feat(blog): add bilingual form inputs and validation"
```

---

### Task 3: Blog — API Resource

**Files:**
- Create: `Modules/Blog/Transformers/BlogResource.php`

- [ ] **Step 1: Create BlogResource**

```php
<?php

namespace Modules\Blog\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->full_name,
            ]),
            'tags' => $this->tags ? json_decode($this->tags) : [],
            'total_views' => $this->total_views,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'image' => $this->getFirstMediaUrl('blog_attachment') ?: null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add Modules/Blog/Transformers/BlogResource.php
git commit -m "feat(blog): add BlogResource with bilingual fields"
```

---

### Task 4: FAQ — Migration, Model & Accessors

**Files:**
- Create: `Modules/FAQ/database/migrations/2026_04_26_000002_add_bilingual_fields_to_faqs_table.php`
- Modify: `Modules/FAQ/Models/Faqs.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->string('question_ar')->nullable()->after('question');
            $table->string('question_en')->nullable()->after('question_ar');
            $table->longText('answer_ar')->nullable()->after('answer');
            $table->longText('answer_en')->nullable()->after('answer_ar');
        });

        DB::statement('UPDATE faqs SET question_en = question WHERE question IS NOT NULL');
        DB::statement('UPDATE faqs SET answer_en = answer WHERE answer IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['question_ar', 'question_en', 'answer_ar', 'answer_en']);
        });
    }
};
```

- [ ] **Step 2: Update Faqs model**

In `Modules/FAQ/Models/Faqs.php`:

```php
protected $fillable = ['question', 'question_ar', 'question_en', 'answer', 'answer_ar', 'answer_en', 'status'];

public function getQuestionAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->question_ar : $this->question_en;
    return $localized ?: ($locale === 'ar' ? $this->question_en : $this->question_ar) ?: '';
}

public function getAnswerAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->answer_ar : $this->answer_en;
    return $localized ?: ($locale === 'ar' ? $this->answer_en : $this->answer_ar) ?: '';
}
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`

- [ ] **Step 4: Commit**

```bash
git add Modules/FAQ/database/migrations/2026_04_26_000002_add_bilingual_fields_to_faqs_table.php Modules/FAQ/Models/Faqs.php
git commit -m "feat(faq): add bilingual _ar/_en columns and locale accessors"
```

---

### Task 5: FAQ — Validation, Controller & Forms

**Files:**
- Modify: `Modules/FAQ/Http/Requests/FAQRequest.php`
- Modify: `Modules/FAQ/Http/Controllers/FAQController.php`
- Modify: `Modules/FAQ/Resources/views/backend/faq/create.blade.php`
- Modify: `Modules/FAQ/Resources/views/backend/faq/edit.blade.php`

- [ ] **Step 1: Update FAQRequest validation**

```php
public function rules()
{
    return [
        'question_ar' => 'nullable|string|max:255|required_without:question_en',
        'question_en' => 'nullable|string|max:255|required_without:question_ar',
        'answer_ar' => 'nullable|string|required_without:answer_en',
        'answer_en' => 'nullable|string|required_without:answer_ar',
    ];
}
```

- [ ] **Step 2: Update FAQController store method**

In `Modules/FAQ/Http/Controllers/FAQController.php`, update store (line 147):

```php
public function store(FAQRequest $request)
{
    $data = $request->all();
    if (isset($data['answer_en'])) {
        $data['answer_en'] = strip_tags($data['answer_en']);
    }
    if (isset($data['answer_ar'])) {
        $data['answer_ar'] = strip_tags($data['answer_ar']);
    }
    $data['question'] = $request->question_en ?? $request->question_ar;
    $data['answer'] = $request->answer_en ?? $request->answer_ar;
    $faq = faqs::create($data);

    $message = __('messages.create_form', ['form' => __('faq')]);
    return redirect()->route('backend.faqs.index')->with('success', $message);
}
```

Update the update method (line 179) similarly:

```php
public function update(FAQRequest $request, faqs $faq)
{
    $requestData = $request->all();
    if (isset($requestData['answer_en'])) {
        $requestData['answer_en'] = strip_tags($requestData['answer_en']);
    }
    if (isset($requestData['answer_ar'])) {
        $requestData['answer_ar'] = strip_tags($requestData['answer_ar']);
    }
    $requestData['question'] = $request->question_en ?? $request->question_ar;
    $requestData['answer'] = $request->answer_en ?? $request->answer_ar;
    $faq->update($requestData);

    $message = __('messages.update_form', ['form' => __('FAQ')]);
    return redirect()->route('backend.faqs.index')->with('success', $message);
}
```

- [ ] **Step 3: Update create.blade.php**

Replace the question field (lines 27-41) with bilingual:

```blade
<!-- Question (Arabic) -->
<div class="col-md-6">
    <div class="form-group">
        {{ html()->label(__('faq.lbl_question_ar') . ' <span class="text-danger">*</span>', 'question_ar')->class('form-label') }}
        {{ html()->text('question_ar')
                ->attribute('value', old('question_ar'))
                ->placeholder(__('faq.lbl_question_ar'))
                ->class('form-control')
                ->attribute('dir', 'rtl') }}
        @error('question_ar')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<!-- Question (English) -->
<div class="col-md-6">
    <div class="form-group">
        {{ html()->label(__('faq.lbl_question_en') . ' <span class="text-danger">*</span>', 'question_en')->class('form-label') }}
        {{ html()->text('question_en')
                ->attribute('value', old('question_en'))
                ->placeholder(__('faq.lbl_question_en'))
                ->class('form-control') }}
        @error('question_en')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>
```

Replace the answer field (lines 43-57) with bilingual:

```blade
<!-- Answer (Arabic) -->
<div class="col-md-6">
    <div class="form-group">
        {{ html()->label(__('faq.lbl_answer_ar') . ' <span class="text-danger">*</span>', 'answer_ar')->class('form-label') }}
        {{ html()->textarea('answer_ar')
                ->attribute('value', old('answer_ar'))
                ->placeholder(__('faq.lbl_answer_ar'))
                ->class('form-control tinymce-template')
                ->attribute('dir', 'rtl') }}
        @error('answer_ar')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<!-- Answer (English) -->
<div class="col-md-6">
    <div class="form-group">
        {{ html()->label(__('faq.lbl_answer_en') . ' <span class="text-danger">*</span>', 'answer_en')->class('form-label') }}
        {{ html()->textarea('answer_en')
                ->attribute('value', old('answer_en'))
                ->placeholder(__('faq.lbl_answer_en'))
                ->class('form-control tinymce-template') }}
        @error('answer_en')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>
```

- [ ] **Step 4: Update edit.blade.php**

Same pattern as create.blade.php but with `$data->question_ar`, `$data->question_en`, `$data->answer_ar`, `$data->answer_en` as values:

```blade
<!-- Question (Arabic) -->
<div class="col-md-6">
    <div class="form-group">
        {{ html()->label(__('faq.lbl_question_ar') . ' <span class="text-danger">*</span>', 'question_ar')->class('form-label') }}
        {{ html()->text('question_ar')
                ->attribute('value', old('question_ar', $data->question_ar))
                ->placeholder(__('faq.lbl_question_ar'))
                ->class('form-control')
                ->attribute('dir', 'rtl') }}
        @error('question_ar')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<!-- Question (English) -->
<div class="col-md-6">
    <div class="form-group">
        {{ html()->label(__('faq.lbl_question_en') . ' <span class="text-danger">*</span>', 'question_en')->class('form-label') }}
        {{ html()->text('question_en')
                ->attribute('value', old('question_en', $data->question_en))
                ->placeholder(__('faq.lbl_question_en'))
                ->class('form-control') }}
        @error('question_en')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<!-- Answer (Arabic) -->
<div class="col-md-6">
    <div class="form-group">
        <label for="answer_ar" class="form-label">{{ __('faq.lbl_answer_ar') }} <span class="text-danger">*</span></label>
        <textarea name="answer_ar" id="answer_ar" class="form-control tinymce-template" dir="rtl" placeholder="{{ __('faq.lbl_answer_ar') }}">{{ old('answer_ar', $data->answer_ar) }}</textarea>
        @error('answer_ar')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<!-- Answer (English) -->
<div class="col-md-6">
    <div class="form-group">
        <label for="answer_en" class="form-label">{{ __('faq.lbl_answer_en') }} <span class="text-danger">*</span></label>
        <textarea name="answer_en" id="answer_en" class="form-control tinymce-template" placeholder="{{ __('faq.lbl_answer_en') }}">{{ old('answer_en', $data->answer_en) }}</textarea>
        @error('answer_en')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>
```

- [ ] **Step 5: Create translation file**

Create `lang/en/faq.php`:

```php
<?php

return [
    'lbl_question_ar' => 'Question (Arabic)',
    'lbl_question_en' => 'Question (English)',
    'lbl_answer_ar' => 'Answer (Arabic)',
    'lbl_answer_en' => 'Answer (English)',
];
```

- [ ] **Step 6: Commit**

```bash
git add Modules/FAQ/Http/Requests/FAQRequest.php Modules/FAQ/Http/Controllers/FAQController.php Modules/FAQ/Resources/views/backend/faq/create.blade.php Modules/FAQ/Resources/views/backend/faq/edit.blade.php lang/en/faq.php
git commit -m "feat(faq): add bilingual form inputs and validation"
```

---

### Task 6: FAQ — API Resource

**Files:**
- Create: `Modules/FAQ/Transformers/FaqResource.php`

- [ ] **Step 1: Create FaqResource**

```php
<?php

namespace Modules\FAQ\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'question_ar' => $this->question_ar,
            'question_en' => $this->question_en,
            'answer' => $this->answer,
            'answer_ar' => $this->answer_ar,
            'answer_en' => $this->answer_en,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add Modules/FAQ/Transformers/FaqResource.php
git commit -m "feat(faq): add FaqResource with bilingual fields"
```

---

### Task 7: Product — Migration & Model

**Files:**
- Create: `Modules/Product/database/migrations/2026_04_26_000003_add_bilingual_fields_to_products_table.php`
- Modify: `Modules/Product/Models/Product.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->longText('short_description_ar')->nullable()->after('short_description');
            $table->longText('short_description_en')->nullable()->after('short_description_ar');
            $table->longText('description_ar')->nullable()->after('description');
            $table->longText('description_en')->nullable()->after('description_ar');
        });

        DB::statement('UPDATE products SET name_en = name WHERE name IS NOT NULL');
        DB::statement('UPDATE products SET short_description_en = short_description WHERE short_description IS NOT NULL');
        DB::statement('UPDATE products SET description_en = description WHERE description IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'short_description_ar', 'short_description_en', 'description_ar', 'description_en']);
        });
    }
};
```

- [ ] **Step 2: Update Product model**

Add to the fillable array in `Modules/Product/Models/Product.php` (after existing `name`, `short_description`, `description`):

```php
protected $fillable = [
    'name', 'name_ar', 'name_en',
    'slug',
    'short_description', 'short_description_ar', 'short_description_en',
    'description', 'description_ar', 'description_en',
    'brand_id',
    'unit_id',
    'product_tags',
    'min_price',
    'max_price',
    'discount_value',
    'discount_type',
    'discount_start_date',
    'discount_end_date',
    'sell_target',
    'stock_qty',
    'status',
    'is_featured',
    'min_purchase_qty',
    'max_purchase_qty',
    'has_variation',
    'has_warranty',
    'total_sale_count',
    'standard_delivery_hours',
    'express_delivery_hours',
    'size_guide',
    'reward_points',
];

public function getNameAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;
    return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
}

public function getShortDescriptionAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->short_description_ar : $this->short_description_en;
    return $localized ?: ($locale === 'ar' ? $this->short_description_en : $this->short_description_ar) ?: '';
}

public function getDescriptionAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;
    return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
}
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`

- [ ] **Step 4: Commit**

```bash
git add Modules/Product/database/migrations/2026_04_26_000003_add_bilingual_fields_to_products_table.php Modules/Product/Models/Product.php
git commit -m "feat(product): add bilingual _ar/_en columns and locale accessors"
```

---

### Task 8: Product — Validation, Controller & API Resources

**Files:**
- Modify: `Modules/Product/Http/Requests/ProductRequest.php`
- Modify: `Modules/Product/Http/Controllers/Backend/ProductsController.php`
- Modify: `Modules/Product/Transformers/ProductResource.php`
- Modify: `Modules/Product/Transformers/ProductDetailResource.php`

- [ ] **Step 1: Update ProductRequest validation**

Add bilingual rules to the rules array in `Modules/Product/Http/Requests/ProductRequest.php`:

```php
public function rules()
{
    return [
        'name_ar' => 'nullable|string|max:255|required_without:name_en',
        'name_en' => 'nullable|string|max:255|required_without:name_ar',
        'short_description_ar' => 'nullable|string',
        'short_description_en' => 'nullable|string',
        'description_ar' => 'nullable|string',
        'description_en' => 'nullable|string',
        'category_ids' => 'required',
    ];
}
```

- [ ] **Step 2: Update ProductsController store method**

In the store method of `Modules/Product/Http/Controllers/Backend/ProductsController.php`, after getting request data, add:

```php
$data['name'] = $request->name_en ?? $request->name_ar;
$data['short_description'] = $request->short_description_en ?? $request->short_description_ar;
$data['description'] = $request->description_en ?? $request->description_ar;
```

Do the same in the update method.

- [ ] **Step 3: Update ProductResource**

In `Modules/Product/Transformers/ProductResource.php`, add bilingual fields after existing fields:

```php
'name' => $this->name,
'name_ar' => $this->name_ar,
'name_en' => $this->name_en,
// ... existing fields ...
'short_description' => $this->short_description,
'short_description_ar' => $this->short_description_ar,
'short_description_en' => $this->short_description_en,
'description' => $this->description,
'description_ar' => $this->description_ar,
'description_en' => $this->description_en,
```

- [ ] **Step 4: Update ProductDetailResource the same way**

Add `_ar`/`_en` fields to `Modules/Product/Transformers/ProductDetailResource.php` matching the same pattern.

- [ ] **Step 5: Add translation keys**

Add to `lang/en/product.php`:

```php
'lbl_name_ar' => 'Name (Arabic)',
'lbl_name_en' => 'Name (English)',
'lbl_short_description_ar' => 'Short Description (Arabic)',
'lbl_short_description_en' => 'Short Description (English)',
'lbl_description_ar' => 'Description (Arabic)',
'lbl_description_en' => 'Description (English)',
```

- [ ] **Step 6: Commit**

```bash
git add Modules/Product/Http/Requests/ProductRequest.php Modules/Product/Http/Controllers/Backend/ProductsController.php Modules/Product/Transformers/ProductResource.php Modules/Product/Transformers/ProductDetailResource.php lang/en/product.php
git commit -m "feat(product): add bilingual validation, controller logic, and API resources"
```

---

### Task 9: Page — Migration, Model, Controller & Form

**Files:**
- Create: `Modules/Page/database/migrations/2026_04_26_000004_add_bilingual_fields_to_pages_table.php`
- Modify: `Modules/Page/Models/Page.php`
- Modify: `Modules/Page/Http/Controllers/Backend/PagesController.php`
- Modify: `Modules/Page/Resources/views/backend/pages/form.blade.php`
- Modify: `Modules/Page/Transformers/PageResource.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->longText('description_ar')->nullable()->after('description');
            $table->longText('description_en')->nullable()->after('description_ar');
        });

        DB::statement('UPDATE pages SET name_en = name WHERE name IS NOT NULL');
        DB::statement('UPDATE pages SET description_en = description WHERE description IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en']);
        });
    }
};
```

- [ ] **Step 2: Update Page model**

In `Modules/Page/Models/Page.php`:

```php
protected $fillable = ['slug', 'name', 'name_ar', 'name_en', 'description', 'description_ar', 'description_en', 'sequence', 'status'];

public function getNameAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;
    return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
}

public function getDescriptionAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;
    return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
}
```

- [ ] **Step 3: Update PagesController store and update**

In `Modules/Page/Http/Controllers/Backend/PagesController.php`:

Update store (line 185):
```php
public function store(Request $request)
{
    $request->validate([
        'name_ar' => 'nullable|string|max:255|required_without:name_en',
        'name_en' => 'nullable|string|max:255|required_without:name_ar',
        'description_ar' => 'nullable|string',
        'description_en' => 'nullable|string',
    ]);

    $data = $request->all();
    $data['name'] = $request->name_en ?? $request->name_ar;
    $data['description'] = $request->description_en ?? $request->description_ar;

    Page::create($data);

    $message = __('messages.create_form', ['form' => __($this->module_title)]);

    return response()->json(['message' => $message, 'status' => true], 200);
}
```

Update update (line 230):
```php
public function update(Request $request, $id)
{
    $request->validate([
        'name_ar' => 'nullable|string|max:255|required_without:name_en',
        'name_en' => 'nullable|string|max:255|required_without:name_ar',
        'description_ar' => 'nullable|string',
        'description_en' => 'nullable|string',
    ]);

    $data = Page::findOrFail($id);

    $requestData = $request->all();
    $requestData['name'] = $request->name_en ?? $request->name_ar;
    $requestData['description'] = $request->description_en ?? $request->description_ar;

    $data->update($requestData);

    $message = __('messages.update_form', ['form' => __($this->module_title)]);

    return response()->json(['message' => $message, 'status' => true], 200);
}
```

- [ ] **Step 4: Update form.blade.php**

Replace `Modules/Page/Resources/views/backend/pages/form.blade.php` entirely:

```blade
<div class="row mb-3">
    <!-- Name (Arabic) -->
    <div class="col-12 col-sm-6">
        <div class="form-group">
            {{ html()->label(__('page.lbl_name_ar') . ' <span class="text-danger">*</span>', 'name_ar')->class('form-label') }}
            {{ html()->text('name_ar')->placeholder(__('page.lbl_name_ar'))->class('form-control')->attribute('dir', 'rtl') }}
            @error('name_ar')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Name (English) -->
    <div class="col-12 col-sm-6">
        <div class="form-group">
            {{ html()->label(__('page.lbl_name_en') . ' <span class="text-danger">*</span>', 'name_en')->class('form-label') }}
            {{ html()->text('name_en')->placeholder(__('page.lbl_name_en'))->class('form-control') }}
            @error('name_en')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row mb-3">
    <!-- Description (Arabic) -->
    <div class="col-12 col-sm-6">
        <div class="form-group">
            {{ html()->label(__('page.lbl_description_ar'), 'description_ar')->class('form-label') }}
            {{ html()->textarea('description_ar')->placeholder(__('page.lbl_description_ar'))->class('form-control')->attribute('dir', 'rtl')->attribute('rows', '3') }}
            @error('description_ar')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Description (English) -->
    <div class="col-12 col-sm-6">
        <div class="form-group">
            {{ html()->label(__('page.lbl_description_en'), 'description_en')->class('form-label') }}
            {{ html()->textarea('description_en')->placeholder(__('page.lbl_description_en'))->class('form-control')->attribute('rows', '3') }}
            @error('description_en')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
```

- [ ] **Step 5: Update PageResource**

```php
public function toArray($request)
{
    return [
        'id'   => $this->id,
        'slug' => $this->slug,
        'name' => $this->name,
        'name_ar' => $this->name_ar,
        'name_en' => $this->name_en,
        'description' => $this->description,
        'description_ar' => $this->description_ar,
        'description_en' => $this->description_en,
        'url'  => route('pages', ['slug' => $this->slug]),
    ];
}
```

- [ ] **Step 6: Create translation file**

Create `lang/en/page.php`:

```php
<?php

return [
    'lbl_name_ar' => 'Name (Arabic)',
    'lbl_name_en' => 'Name (English)',
    'lbl_description_ar' => 'Description (Arabic)',
    'lbl_description_en' => 'Description (English)',
];
```

- [ ] **Step 7: Run migration and commit**

```bash
php artisan migrate
git add Modules/Page/database/migrations/2026_04_26_000004_add_bilingual_fields_to_pages_table.php Modules/Page/Models/Page.php Modules/Page/Http/Controllers/Backend/PagesController.php Modules/Page/Resources/views/backend/pages/form.blade.php Modules/Page/Transformers/PageResource.php lang/en/page.php
git commit -m "feat(page): add bilingual support for name and description"
```

---

### Task 10: ClinicsCategory — Migration, Model & API Resource

**Files:**
- Create: `Modules/Clinic/database/migrations/2026_04_26_000005_add_bilingual_fields_to_clinics_categories_table.php`
- Modify: `Modules/Clinic/Models/ClinicsCategory.php`
- Modify: `Modules/Clinic/Http/Requests/ClinicsCategoryRequest.php`
- Modify: `Modules/Clinic/Http/Controllers/ClinicsCategoryController.php`
- Modify: `Modules/Clinic/Transformers/CategoryResource.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics_categories', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->longText('description_ar')->nullable()->after('description');
            $table->longText('description_en')->nullable()->after('description_ar');
        });

        DB::statement('UPDATE clinics_categories SET name_en = name WHERE name IS NOT NULL');
        DB::statement('UPDATE clinics_categories SET description_en = description WHERE description IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('clinics_categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en']);
        });
    }
};
```

- [ ] **Step 2: Update ClinicsCategory model**

In `Modules/Clinic/Models/ClinicsCategory.php`:

```php
protected $fillable = ['slug', 'name', 'name_ar', 'name_en', 'parent_id', 'description', 'description_ar', 'description_en', 'vendor_id', 'featured', 'status'];

public function getNameAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;
    return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
}

public function getDescriptionAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;
    return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
}
```

- [ ] **Step 3: Update ClinicsCategoryRequest**

Add bilingual validation rules:

```php
// POST rules
'name_ar' => 'nullable|string|max:255|required_without:name_en',
'name_en' => 'nullable|string|max:255|required_without:name_ar',
'description_ar' => 'nullable|string',
'description_en' => 'nullable|string',
'status' => 'boolean',
```

- [ ] **Step 4: Update ClinicsCategoryController store**

In the store method (line 399), after getting data, add:

```php
$data['name'] = $request->name_en ?? $request->name_ar;
$data['description'] = $request->description_en ?? $request->description_ar;
$data['slug'] = strtolower(\Str::slug($data['name']));
```

Same for update method.

- [ ] **Step 5: Update CategoryResource**

```php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'slug' => $this->slug,
        'name' => $this->name,
        'name_ar' => $this->name_ar,
        'name_en' => $this->name_en,
        'description' => $this->description,
        'description_ar' => $this->description_ar,
        'description_en' => $this->description_en,
        'parent_id' => $this->parent_id,
        'status' => $this->status,
        'is_featured' => $this->featured,
        'category_image' => $this->file_url,
        'created_by' => $this->created_by,
        'updated_by' => $this->updated_by,
        'deleted_by' => $this->deleted_by,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
        'deleted_at' => $this->deleted_at,
    ];
}
```

- [ ] **Step 6: Add translation keys to category.php**

Add to `lang/en/category.php`:

```php
'lbl_name_ar' => 'Name (Arabic)',
'lbl_name_en' => 'Name (English)',
'lbl_description_ar' => 'Description (Arabic)',
'lbl_description_en' => 'Description (English)',
```

- [ ] **Step 7: Run migration and commit**

```bash
php artisan migrate
git add Modules/Clinic/database/migrations/2026_04_26_000005_add_bilingual_fields_to_clinics_categories_table.php Modules/Clinic/Models/ClinicsCategory.php Modules/Clinic/Http/Requests/ClinicsCategoryRequest.php Modules/Clinic/Http/Controllers/ClinicsCategoryController.php Modules/Clinic/Transformers/CategoryResource.php lang/en/category.php
git commit -m "feat(clinic-category): add bilingual support for name and description"
```

---

### Task 11: Service — Migrate from Spatie Translatable

**Files:**
- Create: `Modules/Service/database/migrations/2026_04_26_000006_migrate_services_from_translatable_to_bilingual.php`
- Modify: `Modules/Service/Models/Service.php`
- Modify: `Modules/Service/Http/Requests/ServiceRequest.php`
- Modify: `Modules/Service/Http/Controllers/Backend/ServicesController.php`
- Modify: `Modules/Service/Transformers/ServiceResource.php`

- [ ] **Step 1: Create migration**

This migration extracts data from the JSON translatable columns into explicit `_ar`/`_en` columns:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->longText('description_ar')->nullable()->after('description');
            $table->longText('description_en')->nullable()->after('description_ar');
        });

        // Extract JSON translatable data into new columns
        $services = DB::table('services')->get();
        foreach ($services as $service) {
            $nameData = json_decode($service->name, true);
            $descData = json_decode($service->description, true);

            $updates = [];
            if (is_array($nameData)) {
                $updates['name_en'] = $nameData['en'] ?? null;
                $updates['name_ar'] = $nameData['ar'] ?? null;
            } else {
                $updates['name_en'] = $service->name;
            }

            if (is_array($descData)) {
                $updates['description_en'] = $descData['en'] ?? null;
                $updates['description_ar'] = $descData['ar'] ?? null;
            } else {
                $updates['description_en'] = $service->description;
            }

            DB::table('services')->where('id', $service->id)->update($updates);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en']);
        });
    }
};
```

- [ ] **Step 2: Update Service model**

In `Modules/Service/Models/Service.php`:

1. Remove `use HasTranslations;` trait
2. Remove `public array $translatable = ['name', 'description'];`
3. Update fillable and add accessors:

```php
protected $fillable = ['slug', 'name', 'name_ar', 'name_en', 'description', 'description_ar', 'description_en', 'duration_min', 'default_price', 'category_id', 'sub_category_id', 'status'];

public function getNameAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;
    return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
}

public function getDescriptionAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;
    return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
}
```

- [ ] **Step 3: Update ServiceRequest**

Replace the `name` rule with bilingual rules:

```php
'name_ar' => 'nullable|string|max:255|required_without:name_en',
'name_en' => 'nullable|string|max:255|required_without:name_ar',
'description_ar' => 'nullable|string',
'description_en' => 'nullable|string',
'duration_min' => 'required|integer',
'default_price' => 'required',
'category_id' => 'required|integer',
'status' => 'boolean',
```

- [ ] **Step 4: Update ServicesController**

In store/update methods, replace translatable handling with:

```php
$data['name'] = $request->name_en ?? $request->name_ar;
$data['description'] = $request->description_en ?? $request->description_ar;
```

In edit method, remove the `getTranslations()` calls (lines 379-380) since we no longer use Spatie Translatable.

- [ ] **Step 5: Update ServiceResource**

```php
'name' => $this->name,
'name_ar' => $this->name_ar,
'name_en' => $this->name_en,
'description' => $this->description,
'description_ar' => $this->description_ar,
'description_en' => $this->description_en,
```

- [ ] **Step 6: Update Vue form component**

In `Modules/Service/Resources/assets/js/components/ServiceFormOffcanvas.vue`:

Replace `<TranslatableInput>` for name with two separate inputs:
- `name_ar` input with `dir="rtl"`
- `name_en` input

Replace `<TranslatableTextarea>` for description similarly.

Update the form data structure from `name: { en: '', ar: '' }` to `name_ar: '', name_en: ''`.

Update the form data handler for edit mode to map `data.name_ar` and `data.name_en` directly.

- [ ] **Step 7: Update DataTable filter**

In `Modules/Service/Resources/views/backend/services/index_datatable.blade.php`, replace `JSON_EXTRACT` queries with standard column queries:

```php
->where('name_en', 'like', "%{$search}%")
->orWhere('name_ar', 'like', "%{$search}%")
```

- [ ] **Step 8: Run migration and commit**

```bash
php artisan migrate
git add Modules/Service/database/migrations/2026_04_26_000006_migrate_services_from_translatable_to_bilingual.php Modules/Service/Models/Service.php Modules/Service/Http/Requests/ServiceRequest.php Modules/Service/Http/Controllers/Backend/ServicesController.php Modules/Service/Transformers/ServiceResource.php Modules/Service/Resources/assets/js/components/ServiceFormOffcanvas.vue Modules/Service/Resources/views/backend/services/index_datatable.blade.php
git commit -m "feat(service): migrate from Spatie Translatable to _ar/_en columns"
```

---

### Task 12: Clinics — Migrate from Spatie Translatable

**Files:**
- Create: `Modules/Clinic/database/migrations/2026_04_26_000007_migrate_clinics_from_translatable_to_bilingual.php`
- Modify: `Modules/Clinic/Models/Clinics.php`
- Modify: `Modules/Clinic/Http/Requests/ClinicRequest.php`
- Modify: `Modules/Clinic/Http/Controllers/ClinicesController.php`
- Modify: `Modules/Clinic/Transformers/ClinicDetailsResource.php`

- [ ] **Step 1: Create migration**

Same pattern as Service migration but for `clinic` table:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->text('description_ar')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_ar');
        });

        $clinics = DB::table('clinic')->get();
        foreach ($clinics as $clinic) {
            $nameData = json_decode($clinic->name, true);
            $descData = json_decode($clinic->description, true);

            $updates = [];
            if (is_array($nameData)) {
                $updates['name_en'] = $nameData['en'] ?? null;
                $updates['name_ar'] = $nameData['ar'] ?? null;
            } else {
                $updates['name_en'] = $clinic->name;
            }

            if (is_array($descData)) {
                $updates['description_en'] = $descData['en'] ?? null;
                $updates['description_ar'] = $descData['ar'] ?? null;
            } else {
                $updates['description_en'] = $clinic->description;
            }

            DB::table('clinic')->where('id', $clinic->id)->update($updates);
        }
    }

    public function down(): void
    {
        Schema::table('clinic', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en']);
        });
    }
};
```

- [ ] **Step 2: Update Clinics model**

In `Modules/Clinic/Models/Clinics.php`:

1. Remove `use HasTranslations;` trait
2. Remove `public array $translatable = ['name', 'description'];`
3. Add `name_ar`, `name_en`, `description_ar`, `description_en` to fillable
4. Add accessors:

```php
public function getNameAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->name_ar : $this->name_en;
    return $localized ?: ($locale === 'ar' ? $this->name_en : $this->name_ar) ?: '';
}

public function getDescriptionAttribute()
{
    $locale = app()->getLocale();
    $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;
    return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: '';
}
```

- [ ] **Step 3: Update ClinicRequest**

Replace `name` rule with:

```php
'name_ar' => 'nullable|string|max:255|required_without:name_en',
'name_en' => 'nullable|string|max:255|required_without:name_ar',
'description_ar' => 'nullable|string',
'description_en' => 'nullable|string',
```

- [ ] **Step 4: Update ClinicesController**

In store method:
```php
$data['name'] = $request->name_en ?? $request->name_ar;
$data['description'] = $request->description_en ?? $request->description_ar;
$data['slug'] = strtolower(\Str::slug($data['name'], '-'));
```

In edit method, remove `getTranslations()` calls.

In update method, same pattern as store.

- [ ] **Step 5: Update ClinicDetailsResource**

Add bilingual fields:

```php
'name' => $this->name,
'name_ar' => $this->name_ar,
'name_en' => $this->name_en,
'description' => $this->description,
'description_ar' => $this->description_ar,
'description_en' => $this->description_en,
```

- [ ] **Step 6: Update Vue form component**

In `Modules/Clinic/Resources/assets/js/component/ClinicFormOffcanvas.vue`:

Replace `<TranslatableInput>` and `<TranslatableTextarea>` components with separate `_ar`/`_en` input fields. Update form data structure and submit handling.

- [ ] **Step 7: Update DataTable filter**

Replace JSON_EXTRACT queries with standard column queries.

- [ ] **Step 8: Run migration and commit**

```bash
php artisan migrate
git add Modules/Clinic/database/migrations/2026_04_26_000007_migrate_clinics_from_translatable_to_bilingual.php Modules/Clinic/Models/Clinics.php Modules/Clinic/Http/Requests/ClinicRequest.php Modules/Clinic/Http/Controllers/ClinicesController.php Modules/Clinic/Transformers/ClinicDetailsResource.php Modules/Clinic/Resources/assets/js/component/ClinicFormOffcanvas.vue Modules/Clinic/Resources/views/backend/clinic/index_datatable.blade.php
git commit -m "feat(clinics): migrate from Spatie Translatable to _ar/_en columns"
```

---

### Task 13: Doctor — Add Missing title Migration & Fix API Resource

**Files:**
- Create: `Modules/Clinic/database/migrations/2026_04_26_000008_add_title_columns_to_doctors_table.php`
- Modify: `Modules/Clinic/Transformers/DoctorResource.php`

- [ ] **Step 1: Create migration for missing title columns**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('description_en');
            $table->string('title_en')->nullable()->after('title_ar');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_en']);
        });
    }
};
```

- [ ] **Step 2: Update DoctorResource**

In `Modules/Clinic/Transformers/DoctorResource.php`, add bilingual fields after existing `description` and `experience` (around lines 73-75):

```php
'description' => $this->description,
'description_ar' => $this->description_ar,
'description_en' => $this->description_en,
'experience' => $this->experience,
'experience_ar' => $this->experience_ar,
'experience_en' => $this->experience_en,
'title' => $this->title ?? '',
'title_ar' => $this->title_ar,
'title_en' => $this->title_en,
```

- [ ] **Step 3: Run migration and commit**

```bash
php artisan migrate
git add Modules/Clinic/database/migrations/2026_04_26_000008_add_title_columns_to_doctors_table.php Modules/Clinic/Transformers/DoctorResource.php
git commit -m "fix(doctor): add missing title_ar/title_en migration and update API resource"
```

---

### Task 14: Nurse — Fix API Resource

**Files:**
- Modify: `Modules/RequestNurse/Transformers/NurseResource.php`

- [ ] **Step 1: Update NurseResource to expose raw bilingual fields**

```php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'nurse_id' => $this->nurse_id,
        'name' => optional($this->user)->first_name ? optional($this->user)->first_name.' '.optional($this->user)->last_name : '',
        'first_name' => optional($this->user)->first_name ?? '',
        'last_name' => optional($this->user)->last_name ?? '',
        'email' => optional($this->user)->email ?? '',
        'mobile' => optional($this->user)->mobile ?? '',
        'specialization' => $this->specialization,
        'specialization_ar' => $this->specialization_ar,
        'specialization_en' => $this->specialization_en,
        'experience' => $this->experience,
        'experience_ar' => $this->experience_ar,
        'experience_en' => $this->experience_en,
        'about' => $this->about,
        'about_ar' => $this->about_ar,
        'about_en' => $this->about_en,
        'hourly_rate' => $this->hourly_rate,
        'availability_status' => $this->availability_status,
        'service_area' => $this->service_area,
        'service_area_ar' => $this->service_area_ar,
        'service_area_en' => $this->service_area_en,
        'governorate' => $this->governorate ? ['id' => $this->governorate->id, 'name' => $this->governorate->name] : null,
        'city' => $this->city ? ['id' => $this->city->id, 'name' => $this->city->name] : null,
        'profile_image' => $this->getFirstMediaUrl('profile_image') ?: null,
        'status' => $this->status,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}
```

- [ ] **Step 2: Commit**

```bash
git add Modules/RequestNurse/Transformers/NurseResource.php
git commit -m "fix(nurse): expose raw _ar/_en fields in NurseResource"
```

---

### Task 15: Consistency Audit — Existing Bilingual Models

This task audits all 20+ existing bilingual models against the 7-point checklist. The worker should check each model and fix any gaps found.

**7-Point Checklist per model:**
1. Migration has `_ar`/`_en` columns
2. Model has fields in `$fillable`
3. Model has locale-aware accessor with fallback
4. Admin Blade/Vue form has side-by-side AR/EN inputs
5. API Resource exposes both raw `_ar`/`_en` and computed accessor
6. Controller validates both `_ar`/`_en` fields
7. Translation file has `lbl_*_ar` and `lbl_*_en` keys

**Models to audit (in priority order):**

- [ ] **Step 1: Audit Hospital** (`Modules/IntensiveCare/Models/Hospital.php`)

Check all 7 points. Known status: likely complete (reference implementation). Verify HospitalResource exposes `name_ar`, `name_en`, `description_ar`, `description_en`, `address_ar`, `address_en` raw fields alongside the accessors.

- [ ] **Step 2: Audit IcuDepartment** (`Modules/IntensiveCare/Models/IcuDepartment.php`)

Check all 7 points. Verify IcuDepartmentResource exposes `_ar`/`_en` fields.

- [ ] **Step 3: Audit IcuAdmissionRequest** (`Modules/IntensiveCare/Models/IcuAdmissionRequest.php`)

Check all 7 points for fields: `medical_condition`, `diagnosis`, `admin_notes`, `rejection_reason`.

- [ ] **Step 4: Audit ClinicsService** (`Modules/Clinic/Models/ClinicsService.php`)

Check all 7 points for `name` and `description` fields.

- [ ] **Step 5: Audit Offer** (`Modules/Offer/Models/Offer.php` or similar)

Check all 7 points for `title`, `description`, `short_label`, `terms`, `banner_text`. Known: OfferResource is likely complete.

- [ ] **Step 6: Audit Pharmacy** (`Modules/Pharmacy/Models/Pharmacy.php`)

Check all 7 points for `name` and `description`.

- [ ] **Step 7: Audit PharmacyCoupon** (`Modules/Pharmacy/Models/PharmacyCoupon.php`)

Check all 7 points for `title` and `description`.

- [ ] **Step 8: Audit PharmacyDeliveryZone** (`Modules/Pharmacy/Models/PharmacyDeliveryZone.php`)

Check all 7 points for `name`.

- [ ] **Step 9: Audit PharmacyCategory** (`Modules/Pharmacy/Models/PharmacyCategory.php`)

Check all 7 points for `name`.

- [ ] **Step 10: Audit PharmacyProduct** (`Modules/Pharmacy/Models/PharmacyProduct.php`)

Check all 7 points for `name` and `description`.

- [ ] **Step 11: Audit Lab** (`Modules/RadiologyLab/Models/Lab.php`)

Check all 7 points for `name`.

- [ ] **Step 12: Audit RadiologyCenter** (`Modules/RadiologyLab/Models/RadiologyCenter.php`)

Check all 7 points for `name`.

- [ ] **Step 13: Audit HomeHealthcare** (`Modules/HomeHealthcare/Models/HomeHealthcare.php`)

Check all 7 points for `name`.

- [ ] **Step 14: Audit Governorate & GovernorateCity** (`Modules/Location/Models/Governorate.php`, `Modules/Location/Models/GovernorateCity.php`)

Check all 7 points for `name`.

- [ ] **Step 15: Audit PharmacyNotification** (`Modules/Pharmacy/Models/PharmacyNotification.php`)

Check all 7 points for `title` and `body`.

- [ ] **Step 16: Audit Specialization** (`Modules/Specialization/Models/Specialization.php`)

Verify all 7 points are complete (reference implementation).

- [ ] **Step 17: Fix all gaps found**

For each model that fails any of the 7 checks, apply the fix:
- Missing fillable: add `_ar`/`_en` to `$fillable`
- Missing accessor: add the standard locale-aware accessor
- Missing API fields: add `_ar`/`_en` raw fields to the Resource
- Missing form inputs: add side-by-side AR/EN inputs
- Missing validation: add `required_without` rules
- Missing translation keys: add `lbl_*_ar` and `lbl_*_en` entries

- [ ] **Step 18: Commit all audit fixes**

```bash
git add -A
git commit -m "fix(bilingual): consistency audit fixes across all existing bilingual models"
```

---

### Task 16: Final Verification

- [ ] **Step 1: Run all migrations**

```bash
php artisan migrate:status
```

Verify all new migrations have run.

- [ ] **Step 2: Clear caches**

```bash
php artisan optimize:clear
composer dump-autoload
```

- [ ] **Step 3: Run tests**

```bash
php artisan test
```

Verify no existing tests break.

- [ ] **Step 4: Build frontend assets**

```bash
npm run dev
```

Verify no build errors from Vue component changes.

- [ ] **Step 5: Manual smoke test**

Start the dev server with `php artisan serve` and verify:
- Blog create/edit form shows AR/EN fields
- FAQ create/edit form shows AR/EN fields
- Service form no longer uses translatable JSON
- Clinics form no longer uses translatable JSON
- Doctor form shows title AR/EN fields

- [ ] **Step 6: Final commit**

```bash
git add -A
git commit -m "chore(bilingual): final verification and cache clear"
```
