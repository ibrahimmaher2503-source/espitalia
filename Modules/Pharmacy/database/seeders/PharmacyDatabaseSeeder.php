<?php

namespace Modules\Pharmacy\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Pharmacy\Models\Pharmacy;
use Modules\Pharmacy\Models\PharmacyBrand;
use Modules\Pharmacy\Models\PharmacyCategory;
use Modules\Pharmacy\Models\PharmacyInventory;
use Modules\Pharmacy\Models\PharmacyProduct;
use Modules\Pharmacy\Models\PharmacyProductType;

class PharmacyDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // First, seed permissions
        $this->call(PharmacyPermissionSeeder::class);
        $this->call(PharmacyCommissionPermissionSeeder::class);

        // Create main categories with subcategories
        $mainCategories = [
            ['name_ar' => 'الأدوية', 'name_en' => 'Medicines', 'sort_order' => 1],
            ['name_ar' => 'المكملات الغذائية', 'name_en' => 'Supplements', 'sort_order' => 2],
        ];

        $categoryMap = [];
        foreach ($mainCategories as $catData) {
            $cat = PharmacyCategory::create(array_merge($catData, ['is_active' => true]));
            $categoryMap[$cat->id] = $cat;

            // Add subcategories
            $subCats = [
                ['name_ar' => 'مسكنات', 'name_en' => 'Pain Relief', 'sort_order' => 1],
                ['name_ar' => 'خافضات الحمى', 'name_en' => 'Fever Reducers', 'sort_order' => 2],
                ['name_ar' => 'السعال', 'name_en' => 'Cough Remedies', 'sort_order' => 3],
            ];

            foreach ($subCats as $subData) {
                PharmacyCategory::create(array_merge(
                    $subData,
                    ['parent_id' => $cat->id, 'is_active' => true]
                ));
            }
        }

        // Create brands
        $brands = ['GSK', 'Pfizer', 'Roche', 'Novartis', 'Merck'];
        $brandMap = [];
        foreach ($brands as $brandName) {
            $brand = PharmacyBrand::create(['name' => $brandName, 'is_active' => true]);
            $brandMap[$brand->id] = $brand;
        }

        // Create product types
        $types = [
            ['name_ar' => 'أقراص', 'name_en' => 'Tablets'],
            ['name_ar' => 'شراب', 'name_en' => 'Syrup'],
            ['name_ar' => 'حقن', 'name_en' => 'Injection'],
            ['name_ar' => 'مرهم', 'name_en' => 'Ointment'],
        ];
        $typeMap = [];
        foreach ($types as $typeData) {
            $type = PharmacyProductType::create($typeData);
            $typeMap[$type->id] = $type;
        }

        // Create products
        $products = [
            ['name_ar' => 'بانادول إكسترا', 'name_en' => 'Panadol Extra', 'description' => 'Pain reliever', 'requires_prescription' => false],
            ['name_ar' => 'أسبرين', 'name_en' => 'Aspirin', 'description' => 'Blood thinner', 'requires_prescription' => false],
            ['name_ar' => 'ديفتروليكس', 'name_en' => 'Deftrolex', 'description' => 'Iron supplement', 'requires_prescription' => false],
            ['name_ar' => 'فيتامين سي', 'name_en' => 'Vitamin C', 'description' => 'Immune support', 'requires_prescription' => false],
            ['name_ar' => 'شراب السعال', 'name_en' => 'Cough Syrup', 'description' => 'Cough relief', 'requires_prescription' => false],
            ['name_ar' => 'كريم موضعي', 'name_en' => 'Topical Cream', 'description' => 'Skin care', 'requires_prescription' => false],
            ['name_ar' => 'عصير السعال', 'name_en' => 'Cough Juice', 'description' => 'Throat relief', 'requires_prescription' => false],
            ['name_ar' => 'مرهم العضلات', 'name_en' => 'Muscle Rub', 'description' => 'Muscle relief', 'requires_prescription' => false],
            ['name_ar' => 'غسول الفم', 'name_en' => 'Mouthwash', 'description' => 'Oral hygiene', 'requires_prescription' => false],
            ['name_ar' => 'حبوب منع الحمل', 'name_en' => 'Birth Control', 'description' => 'Contraceptive', 'requires_prescription' => true],
        ];

        $productMap = [];
        $categories = PharmacyCategory::whereNull('parent_id')->get();
        $subCategories = PharmacyCategory::whereNotNull('parent_id')->get();
        $brandIds = array_keys($brandMap);
        $typeIds = array_keys($typeMap);

        foreach ($products as $idx => $prodData) {
            $category = $categories->count() > 0 ? $categories->random() : $categories->first();
            $relatedSubs = $subCategories->filter(fn ($s) => $s->parent_id === $category->id);
            $subCategory = $relatedSubs->count() > 0 ? $relatedSubs->random() : $subCategories->first();

            $product = PharmacyProduct::create(array_merge(
                $prodData,
                [
                    'category_id' => $category->id,
                    'sub_category_id' => $subCategory->id,
                    'brand_id' => $brandIds[array_rand($brandIds)],
                    'product_type_id' => $typeIds[array_rand($typeIds)],
                    'is_active' => true,
                ]
            ));
            $productMap[$product->id] = $product;
        }

        // Create pharmacies
        $pharmacies = [
            [
                'name' => 'Al-Shifa Pharmacy',
                'phone' => '+965-12345678',
                'address' => 'Block 4, Street 10, Hawally',
                'latitude' => 29.3375,
                'longitude' => 47.9774,
                'delivery_fee' => 2.50,
            ],
            [
                'name' => 'Green Pharmacy',
                'phone' => '+965-87654321',
                'address' => 'Salmiya, Kuwait',
                'latitude' => 29.3725,
                'longitude' => 47.8000,
                'delivery_fee' => 3.00,
            ],
        ];

        $pharmacyMap = [];
        foreach ($pharmacies as $pharmData) {
            $pharmacy = Pharmacy::create(array_merge($pharmData, ['is_active' => true]));
            $pharmacyMap[$pharmacy->id] = $pharmacy;
        }

        // Create inventory for pharmacies
        $productIds = array_keys($productMap);
        foreach ($pharmacyMap as $pharmacy) {
            // Stock first 6 products or all if less than 6
            $productsToStock = array_slice($productIds, 0, min(6, count($productIds)));

            foreach ($productsToStock as $productId) {
                PharmacyInventory::create([
                    'pharmacy_id' => $pharmacy->id,
                    'product_id' => $productId,
                    'price' => rand(2, 50) + (rand(0, 99) / 100),
                    'stock_qty' => rand(10, 200),
                    'is_available' => true,
                ]);
            }
        }

        // Seed admin-testable data: commissions, orders, earnings, payouts, staff user
        $this->call(PharmacyAdminTestSeeder::class);
    }
}
