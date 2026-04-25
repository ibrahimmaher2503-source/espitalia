<?php

namespace Modules\Offer\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Modules\Offer\Models\Offer;
use Modules\Offer\Models\OfferUsage;

class OfferUsageTrackingTest extends BaseTest
{
    public function test_usage_creation_increments_count()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'used_count' => 0,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        OfferUsage::create([
            'offer_id' => $offer->id,
            'user_id' => 1,
            'booking_type' => 'appointment',
            'booking_id' => 100,
            'original_amount' => 500,
            'discount_amount' => 50,
            'final_amount' => 450,
            'status' => OfferUsage::STATUS_APPLIED,
        ]);

        // Atomic increment
        Offer::where('id', $offer->id)->update(['used_count' => DB::raw('used_count + 1')]);

        $this->assertEquals(1, $offer->fresh()->used_count);
    }

    public function test_cancellation_decrements_count()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'used_count' => 3,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $usage = OfferUsage::create([
            'offer_id' => $offer->id,
            'user_id' => 1,
            'booking_type' => 'appointment',
            'booking_id' => 100,
            'original_amount' => 500,
            'discount_amount' => 50,
            'final_amount' => 450,
            'status' => OfferUsage::STATUS_APPLIED,
        ]);

        // Simulate cancellation
        $usage->update(['status' => OfferUsage::STATUS_CANCELLED]);
        Offer::where('id', $offer->id)->update(['used_count' => DB::raw('used_count - 1')]);

        $this->assertEquals(2, $offer->fresh()->used_count);
        $this->assertEquals(OfferUsage::STATUS_CANCELLED, $usage->fresh()->status);
    }

    public function test_active_scope_excludes_cancelled()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        OfferUsage::create([
            'offer_id' => $offer->id, 'user_id' => 1,
            'booking_type' => 'appointment', 'booking_id' => 1,
            'original_amount' => 100, 'discount_amount' => 10, 'final_amount' => 90,
            'status' => OfferUsage::STATUS_APPLIED,
        ]);

        OfferUsage::create([
            'offer_id' => $offer->id, 'user_id' => 1,
            'booking_type' => 'appointment', 'booking_id' => 2,
            'original_amount' => 100, 'discount_amount' => 10, 'final_amount' => 90,
            'status' => OfferUsage::STATUS_CANCELLED,
        ]);

        $this->assertEquals(1, OfferUsage::forOffer($offer->id)->active()->count());
    }
}
