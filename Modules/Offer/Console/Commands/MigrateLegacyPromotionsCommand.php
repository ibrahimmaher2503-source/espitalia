<?php

namespace Modules\Offer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Offer\Models\Offer;
use Modules\Offer\Models\OfferUsage;

class MigrateLegacyPromotionsCommand extends Command
{
    protected $signature = 'offer:migrate-legacy';

    protected $description = 'Migrate legacy Promotion/Coupon data into the Offer module';

    public function handle(): int
    {
        if (! \Schema::hasTable('promotions_coupon')) {
            $this->warn('Legacy promotions_coupon table not found. Nothing to migrate.');

            return self::SUCCESS;
        }

        $coupons = DB::table('promotions_coupon')
            ->leftJoin('promotions', 'promotions_coupon.promotion_id', '=', 'promotions.id')
            ->select(
                'promotions_coupon.*',
                'promotions.name as promotion_name',
                'promotions.start_date_time',
                'promotions.end_date_time',
                'promotions.status as promotion_status'
            )
            ->get();

        $migratedCount = 0;

        foreach ($coupons as $coupon) {
            // Skip if already migrated (check by code)
            if (Offer::where('code', $coupon->coupon_code)->exists()) {
                $this->line("Skipping coupon '{$coupon->coupon_code}' — already exists.");

                continue;
            }

            $discountType = $coupon->discount_type === 'percentage' ? 'percentage' : 'fixed_amount';
            $discountValue = $discountType === 'percentage'
                ? ($coupon->discount_percentage ?? 0)
                : ($coupon->discount_amount ?? 0);

            $offer = Offer::create([
                'title_en' => $coupon->promotion_name ?? 'Migrated Coupon',
                'title_ar' => $coupon->promotion_name ?? 'كوبون مرحّل',
                'code' => $coupon->coupon_code,
                'slug' => Str::slug($coupon->coupon_code).'-'.Str::random(4),
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'requires_coupon' => true,
                'auto_apply' => false,
                'target_scope' => 'global',
                'applies_to_all_services' => true,
                'applies_to_all_branches' => true,
                'applies_to_all_categories' => true,
                'customer_scope' => 'all',
                'starts_at' => $coupon->start_date_time,
                'ends_at' => $coupon->end_date_time,
                'usage_limit_total' => $coupon->use_limit > 0 ? $coupon->use_limit : null,
                'usage_limit_per_user' => 1,
                'status' => $coupon->promotion_status == 1 ? Offer::STATUS_ACTIVE : Offer::STATUS_PAUSED,
                'is_active' => $coupon->promotion_status == 1,
            ]);

            $migratedCount++;
            $this->info("Migrated coupon '{$coupon->coupon_code}' → Offer #{$offer->id}");
        }

        // Migrate usage records
        if (\Schema::hasTable('user_coupon_redeem')) {
            $redeems = DB::table('user_coupon_redeem')->get();
            $usageCount = 0;

            foreach ($redeems as $redeem) {
                $offer = Offer::where('code', $redeem->coupon_code)->first();
                if (! $offer) {
                    continue;
                }

                OfferUsage::create([
                    'offer_id' => $offer->id,
                    'user_id' => $redeem->user_id,
                    'booking_type' => 'appointment',
                    'booking_id' => $redeem->booking_id ?? 0,
                    'original_amount' => 0,
                    'discount_amount' => $redeem->discount ?? 0,
                    'final_amount' => 0,
                    'coupon_code' => $redeem->coupon_code,
                    'status' => 'completed',
                ]);
                $usageCount++;
            }

            $this->info("Migrated {$usageCount} coupon usage records.");
        }

        $this->info("Migration complete. {$migratedCount} coupons migrated to Offers.");

        return self::SUCCESS;
    }
}
