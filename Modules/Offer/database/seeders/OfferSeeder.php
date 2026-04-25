<?php

namespace Modules\Offer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Offer\Models\Offer;
use Modules\Offer\Models\OfferSchedule;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        if (! env('IS_DUMMY_DATA')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $now = now();

        $offers = [
            // 1. Percentage discount — platform-wide welcome offer
            [
                'title_ar' => 'خصم ترحيبي 20%',
                'title_en' => '20% Welcome Discount',
                'description_ar' => 'احصل على خصم 20% على أول حجز لك في أي فرع من فروعنا',
                'description_en' => 'Get 20% off your first booking at any of our branches',
                'short_label_ar' => 'خصم 20%',
                'short_label_en' => '20% OFF',
                'terms_ar' => 'يسري على الحجز الأول فقط. لا يمكن دمجه مع عروض أخرى.',
                'terms_en' => 'Valid for first booking only. Cannot be combined with other offers.',
                'banner_text_ar' => 'مرحباً! خصم 20% على أول زيارة',
                'banner_text_en' => 'Welcome! 20% off your first visit',
                'internal_name' => 'welcome_20pct',
                'discount_type' => Offer::TYPE_PERCENTAGE,
                'discount_value' => 20.00,
                'max_discount_amount' => 500.00,
                'minimum_order_amount' => 100.00,
                'code' => 'WELCOME20',
                'requires_coupon' => true,
                'auto_apply' => false,
                'target_scope' => 'global',
                'applies_to_all_services' => true,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => true,
                'customer_scope' => Offer::SCOPE_FIRST_TIME,
                'first_booking_only' => true,
                'starts_at' => $now->copy()->subDays(30),
                'ends_at' => $now->copy()->addMonths(6),
                'usage_limit_total' => 1000,
                'usage_limit_per_user' => 1,
                'priority' => 10,
                'stackable' => false,
                'is_active' => true,
                'is_featured' => true,
                'status' => Offer::STATUS_ACTIVE,
                'allowed_channels' => json_encode(['web', 'mobile']),
            ],

            // 2. Fixed amount discount — seasonal campaign
            [
                'title_ar' => 'خصم 50 جنيه على الفحوصات',
                'title_en' => 'EGP 50 Off Checkups',
                'description_ar' => 'وفر 50 جنيه على أي فحص طبي شامل هذا الشهر',
                'description_en' => 'Save EGP 50 on any comprehensive medical checkup this month',
                'short_label_ar' => 'وفر 50 ج.م',
                'short_label_en' => 'Save EGP 50',
                'terms_ar' => 'يسري على الفحوصات الشاملة فقط. الحد الأدنى للحجز 200 جنيه.',
                'terms_en' => 'Valid for comprehensive checkups only. Minimum booking EGP 200.',
                'banner_text_ar' => 'وفر 50 جنيه على فحصك القادم!',
                'banner_text_en' => 'Save EGP 50 on your next checkup!',
                'internal_name' => 'checkup_50off',
                'discount_type' => Offer::TYPE_FIXED_AMOUNT,
                'discount_value' => 50.00,
                'minimum_order_amount' => 200.00,
                'code' => 'CHECKUP50',
                'requires_coupon' => true,
                'auto_apply' => false,
                'target_scope' => 'global',
                'applies_to_all_services' => false,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => false,
                'customer_scope' => Offer::SCOPE_ALL,
                'starts_at' => $now->copy()->startOfMonth(),
                'ends_at' => $now->copy()->endOfMonth(),
                'usage_limit_total' => 500,
                'usage_limit_per_user' => 2,
                'priority' => 5,
                'stackable' => false,
                'is_active' => true,
                'is_featured' => true,
                'status' => Offer::STATUS_ACTIVE,
                'allowed_channels' => json_encode(['web', 'mobile', 'reception']),
            ],

            // 3. Auto-apply percentage — returning patients loyalty
            [
                'title_ar' => 'خصم ولاء المرضى 10%',
                'title_en' => '10% Patient Loyalty Discount',
                'description_ar' => 'خصم تلقائي 10% لجميع المرضى المتكررين على كل حجز',
                'description_en' => 'Automatic 10% discount for all returning patients on every booking',
                'short_label_ar' => 'خصم ولاء 10%',
                'short_label_en' => '10% Loyalty',
                'terms_ar' => 'يسري تلقائياً للمرضى الذين أكملوا 3 حجوزات أو أكثر.',
                'terms_en' => 'Applies automatically for patients with 3 or more completed bookings.',
                'internal_name' => 'loyalty_10pct',
                'discount_type' => Offer::TYPE_PERCENTAGE,
                'discount_value' => 10.00,
                'max_discount_amount' => 300.00,
                'requires_coupon' => false,
                'auto_apply' => true,
                'target_scope' => 'global',
                'applies_to_all_services' => true,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => true,
                'customer_scope' => Offer::SCOPE_RETURNING,
                'min_completed_bookings' => 3,
                'starts_at' => $now->copy()->subMonths(1),
                'ends_at' => $now->copy()->addYear(),
                'usage_limit_per_user' => 0,
                'priority' => 1,
                'stackable' => true,
                'is_active' => true,
                'is_featured' => false,
                'status' => Offer::STATUS_ACTIVE,
                'allowed_channels' => json_encode(['web', 'mobile', 'reception']),
            ],

            // 4. Flash sale — weekend special
            [
                'title_ar' => 'عرض نهاية الأسبوع - خصم 30%',
                'title_en' => 'Weekend Flash Sale - 30% Off',
                'description_ar' => 'خصم حصري 30% على جميع الخدمات خلال عطلة نهاية الأسبوع',
                'description_en' => 'Exclusive 30% off all services during the weekend',
                'short_label_ar' => 'عرض خاطف!',
                'short_label_en' => 'Flash Sale!',
                'terms_ar' => 'يسري يومي الجمعة والسبت فقط. الحد الأقصى للخصم 400 جنيه.',
                'terms_en' => 'Valid on Friday and Saturday only. Maximum discount EGP 400.',
                'banner_text_ar' => '🔥 عرض نهاية الأسبوع! خصم 30%',
                'banner_text_en' => '🔥 Weekend Flash Sale! 30% OFF',
                'internal_name' => 'weekend_flash_30',
                'discount_type' => Offer::TYPE_PERCENTAGE,
                'discount_value' => 30.00,
                'max_discount_amount' => 400.00,
                'minimum_order_amount' => 150.00,
                'code' => 'WEEKEND30',
                'requires_coupon' => false,
                'auto_apply' => true,
                'target_scope' => 'global',
                'applies_to_all_services' => true,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => true,
                'customer_scope' => Offer::SCOPE_ALL,
                'starts_at' => $now->copy()->subWeek(),
                'ends_at' => $now->copy()->addMonths(3),
                'is_flash' => true,
                'usage_limit_total' => 200,
                'usage_limit_per_user' => 1,
                'priority' => 15,
                'stackable' => false,
                'exclusive_group' => 'flash_sales',
                'is_active' => true,
                'is_featured' => true,
                'status' => Offer::STATUS_ACTIVE,
                'allowed_channels' => json_encode(['web', 'mobile']),
            ],

            // 5. Override price — fixed-price promotion
            [
                'title_ar' => 'استشارة بسعر ثابت 99 جنيه',
                'title_en' => 'Flat Rate Consultation - EGP 99',
                'description_ar' => 'احجز استشارة طبية بسعر ثابت 99 جنيه فقط بدلاً من السعر المعتاد',
                'description_en' => 'Book a medical consultation at a flat rate of EGP 99 only',
                'short_label_ar' => '99 ج.م فقط',
                'short_label_en' => 'Only EGP 99',
                'terms_ar' => 'يسري على الاستشارات العامة فقط. لا يشمل الفحوصات والتحاليل.',
                'terms_en' => 'Valid for general consultations only. Does not include tests or lab work.',
                'internal_name' => 'flat_consultation_99',
                'discount_type' => Offer::TYPE_OVERRIDE_PRICE,
                'override_price' => 99.00,
                'code' => 'FLAT99',
                'requires_coupon' => true,
                'auto_apply' => false,
                'target_scope' => 'global',
                'applies_to_all_services' => false,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => false,
                'customer_scope' => Offer::SCOPE_ALL,
                'starts_at' => $now->copy()->subDays(7),
                'ends_at' => $now->copy()->addMonths(2),
                'usage_limit_total' => 300,
                'usage_limit_per_user' => 1,
                'priority' => 8,
                'stackable' => false,
                'is_active' => true,
                'is_featured' => false,
                'status' => Offer::STATUS_ACTIVE,
                'allowed_channels' => json_encode(['web', 'mobile', 'reception']),
            ],

            // 6. Fixed amount — draft offer (not yet launched)
            [
                'title_ar' => 'عرض رمضان - خصم 100 جنيه',
                'title_en' => 'Ramadan Offer - EGP 100 Off',
                'description_ar' => 'خصم 100 جنيه على جميع الخدمات خلال شهر رمضان المبارك',
                'description_en' => 'EGP 100 off all services during the holy month of Ramadan',
                'short_label_ar' => 'عرض رمضان',
                'short_label_en' => 'Ramadan Special',
                'terms_ar' => 'يسري خلال شهر رمضان فقط. الحد الأدنى للحجز 300 جنيه.',
                'terms_en' => 'Valid during Ramadan only. Minimum booking EGP 300.',
                'banner_text_ar' => '🌙 عرض رمضان المبارك',
                'banner_text_en' => '🌙 Blessed Ramadan Offer',
                'internal_name' => 'ramadan_100off',
                'discount_type' => Offer::TYPE_FIXED_AMOUNT,
                'discount_value' => 100.00,
                'minimum_order_amount' => 300.00,
                'code' => 'RAMADAN100',
                'requires_coupon' => true,
                'auto_apply' => false,
                'target_scope' => 'global',
                'applies_to_all_services' => true,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => true,
                'customer_scope' => Offer::SCOPE_ALL,
                'usage_limit_total' => 2000,
                'usage_limit_per_user' => 3,
                'priority' => 20,
                'stackable' => false,
                'is_active' => false,
                'is_featured' => false,
                'status' => Offer::STATUS_DRAFT,
            ],

            // 7. Percentage — expired offer (historical)
            [
                'title_ar' => 'عرض افتتاح الفرع الجديد',
                'title_en' => 'New Branch Grand Opening',
                'description_ar' => 'خصم 25% بمناسبة افتتاح فرعنا الجديد',
                'description_en' => '25% discount to celebrate the opening of our new branch',
                'short_label_ar' => 'افتتاح جديد!',
                'short_label_en' => 'Grand Opening!',
                'terms_ar' => 'يسري في الفرع الجديد فقط لمدة أسبوعين.',
                'terms_en' => 'Valid at the new branch only for two weeks.',
                'internal_name' => 'branch_opening_25',
                'discount_type' => Offer::TYPE_PERCENTAGE,
                'discount_value' => 25.00,
                'max_discount_amount' => 350.00,
                'code' => 'NEWBRANCH25',
                'requires_coupon' => false,
                'auto_apply' => true,
                'target_scope' => 'global',
                'applies_to_all_services' => true,
                'applies_to_all_branches' => false,
                'applies_to_all_categories' => true,
                'customer_scope' => Offer::SCOPE_ALL,
                'starts_at' => $now->copy()->subMonths(3),
                'ends_at' => $now->copy()->subMonths(3)->addWeeks(2),
                'used_count' => 87,
                'usage_limit_total' => 200,
                'usage_limit_per_user' => 1,
                'priority' => 12,
                'stackable' => false,
                'is_active' => false,
                'is_featured' => false,
                'status' => Offer::STATUS_EXPIRED,
            ],

            // 8. Buy X Get Y — bundle promotion
            [
                'title_ar' => 'احجز 3 جلسات واحصل على 1 مجاناً',
                'title_en' => 'Book 3 Sessions Get 1 Free',
                'description_ar' => 'احجز 3 جلسات علاج طبيعي واحصل على الجلسة الرابعة مجاناً',
                'description_en' => 'Book 3 physiotherapy sessions and get the 4th session for free',
                'short_label_ar' => '3+1 مجاناً',
                'short_label_en' => 'Buy 3 Get 1',
                'terms_ar' => 'يسري على جلسات العلاج الطبيعي فقط. يجب حجز الجلسات الأربعة معاً.',
                'terms_en' => 'Valid for physiotherapy sessions only. All 4 sessions must be booked together.',
                'internal_name' => 'physio_buy3get1',
                'discount_type' => Offer::TYPE_BUY_X_GET_Y,
                'buy_quantity' => 3,
                'get_quantity' => 1,
                'get_discount_percentage' => 100.00,
                'code' => 'PHYSIO3PLUS1',
                'requires_coupon' => true,
                'auto_apply' => false,
                'target_scope' => 'global',
                'applies_to_all_services' => false,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => false,
                'customer_scope' => Offer::SCOPE_ALL,
                'starts_at' => $now->copy()->subDays(14),
                'ends_at' => $now->copy()->addMonths(3),
                'usage_limit_total' => 100,
                'usage_limit_per_user' => 2,
                'priority' => 7,
                'stackable' => false,
                'is_active' => true,
                'is_featured' => true,
                'status' => Offer::STATUS_ACTIVE,
                'allowed_channels' => json_encode(['web', 'mobile', 'reception']),
            ],

            // 9. Percentage — VIP segment offer
            [
                'title_ar' => 'عرض حصري لعملاء VIP - 15%',
                'title_en' => 'Exclusive VIP Offer - 15% Off',
                'description_ar' => 'خصم 15% حصري لعملائنا المميزين على جميع الخدمات',
                'description_en' => '15% exclusive discount for our VIP customers on all services',
                'short_label_ar' => 'حصري VIP',
                'short_label_en' => 'VIP Only',
                'terms_ar' => 'حصري لعملاء VIP. يسري تلقائياً عند الحجز.',
                'terms_en' => 'Exclusive to VIP customers. Applied automatically at booking.',
                'internal_name' => 'vip_15pct',
                'discount_type' => Offer::TYPE_PERCENTAGE,
                'discount_value' => 15.00,
                'max_discount_amount' => 500.00,
                'requires_coupon' => false,
                'auto_apply' => true,
                'target_scope' => 'global',
                'applies_to_all_services' => true,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => true,
                'customer_scope' => Offer::SCOPE_SEGMENT,
                'min_completed_bookings' => 10,
                'starts_at' => $now->copy()->subMonths(2),
                'ends_at' => $now->copy()->addYear(),
                'usage_limit_per_user' => 0,
                'priority' => 3,
                'stackable' => true,
                'is_active' => true,
                'is_featured' => false,
                'status' => Offer::STATUS_ACTIVE,
                'allowed_channels' => json_encode(['web', 'mobile', 'reception']),
            ],

            // 10. Fixed amount — scheduled (future) offer
            [
                'title_ar' => 'عرض الصيف - خصم 75 جنيه',
                'title_en' => 'Summer Sale - EGP 75 Off',
                'description_ar' => 'خصم 75 جنيه على جميع الخدمات خلال فترة الصيف',
                'description_en' => 'EGP 75 off all services during the summer period',
                'short_label_ar' => 'عرض الصيف',
                'short_label_en' => 'Summer Sale',
                'terms_ar' => 'يسري من يونيو حتى أغسطس. الحد الأدنى للحجز 250 جنيه.',
                'terms_en' => 'Valid from June to August. Minimum booking EGP 250.',
                'banner_text_ar' => '☀️ عروض الصيف قادمة!',
                'banner_text_en' => '☀️ Summer deals are coming!',
                'internal_name' => 'summer_75off',
                'discount_type' => Offer::TYPE_FIXED_AMOUNT,
                'discount_value' => 75.00,
                'minimum_order_amount' => 250.00,
                'code' => 'SUMMER75',
                'requires_coupon' => true,
                'auto_apply' => false,
                'target_scope' => 'global',
                'applies_to_all_services' => true,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => true,
                'customer_scope' => Offer::SCOPE_ALL,
                'starts_at' => $now->copy()->addMonths(2),
                'ends_at' => $now->copy()->addMonths(5),
                'usage_limit_total' => 1500,
                'usage_limit_per_user' => 3,
                'priority' => 10,
                'stackable' => false,
                'is_active' => true,
                'is_featured' => false,
                'status' => Offer::STATUS_SCHEDULED,
                'allowed_channels' => json_encode(['web', 'mobile']),
            ],
        ];

        foreach ($offers as $offerData) {
            $offer = Offer::updateOrCreate(
                ['internal_name' => $offerData['internal_name']],
                $offerData
            );
            $offer->saveQuietly();
        }

        // Seed schedules for the weekend flash sale
        $weekendOffer = Offer::where('internal_name', 'weekend_flash_30')->first();
        if ($weekendOffer) {
            OfferSchedule::updateOrCreate(
                ['offer_id' => $weekendOffer->id, 'day_of_week' => 5],
                ['start_time' => '00:00', 'end_time' => '23:59']
            );
            OfferSchedule::updateOrCreate(
                ['offer_id' => $weekendOffer->id, 'day_of_week' => 6],
                ['start_time' => '00:00', 'end_time' => '23:59']
            );
        }

        // Seed customer segments for VIP offer
        $vipOffer = Offer::where('internal_name', 'vip_15pct')->first();
        if ($vipOffer) {
            $vipOffer->customerSegments()->updateOrCreate(
                ['segment_type' => 'tier', 'segment_value' => 'vip'],
            );
            $vipOffer->customerSegments()->updateOrCreate(
                ['segment_type' => 'tier', 'segment_value' => 'premium'],
            );
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->command->info('Seeded '.count($offers).' offers with schedules and segments.');
    }
}
