<?php

namespace Modules\Pharmacy\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pharmacy\Models\Pharmacy;
use Modules\Pharmacy\Models\PharmacyBrand;
use Modules\Pharmacy\Models\PharmacyCategory;
use Modules\Pharmacy\Models\PharmacyInventory;
use Modules\Pharmacy\Models\PharmacyProduct;
use Modules\Pharmacy\Models\PharmacyProductType;
use Tests\TestCase;

class PharmacyCatalogueApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_flutter_catalogue_filters_match_the_api_and_blank_images_are_null(): void
    {
        $category = PharmacyCategory::create(['name_ar' => 'أدوية', 'name_en' => 'Medicine', 'is_active' => true]);
        $subcategory = PharmacyCategory::create(['parent_id' => $category->id, 'name_ar' => 'مسكنات', 'name_en' => 'Pain relief', 'is_active' => true]);
        $brand = PharmacyBrand::create(['name' => 'QA Brand', 'is_active' => true]);
        $type = PharmacyProductType::create(['name_ar' => 'أقراص', 'name_en' => 'Tablets']);
        $pharmacy = Pharmacy::create(['name' => 'QA Pharmacy', 'phone' => '01000000000', 'address' => 'Cairo', 'is_active' => true]);
        $product = PharmacyProduct::create([
            'name_ar' => 'منتج', 'name_en' => 'QA Product', 'category_id' => $category->id,
            'sub_category_id' => $subcategory->id, 'brand_id' => $brand->id,
            'product_type_id' => $type->id, 'requires_prescription' => false, 'is_active' => true,
        ]);
        PharmacyInventory::create(['pharmacy_id' => $pharmacy->id, 'product_id' => $product->id, 'price' => 15, 'stock_qty' => 3, 'is_available' => true]);

        $this->getJson('/api/v1/pharmacy/categories')
            ->assertOk()
            ->assertJsonPath('data.0.image', null);

        $this->getJson('/api/v1/pharmacy/products?category_id='.$category->id.'&brand_id='.$brand->id.'&product_type_id='.$type->id.'&price_min=10&price_max=20&prescription_required=0&sort=price_asc')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.image', null);
    }
}
