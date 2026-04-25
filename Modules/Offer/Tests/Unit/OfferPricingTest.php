<?php

namespace Modules\Offer\Tests\Unit;

use Modules\Offer\Models\Offer;
use Modules\Offer\Services\OfferPricingService;

class OfferPricingTest extends BaseTest
{
    protected OfferPricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OfferPricingService();
    }

    public function test_percentage_discount()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 20,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $result = $this->service->calculateDiscount(500.00, collect([$offer]));

        $this->assertEquals(500.00, $result['original_price']);
        $this->assertEquals(100.00, $result['discount_amount']);
        $this->assertEquals(400.00, $result['discounted_price']);
    }

    public function test_percentage_discount_with_cap()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 50,
            'max_discount_amount' => 100,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $result = $this->service->calculateDiscount(500.00, collect([$offer]));

        $this->assertEquals(100.00, $result['discount_amount']); // capped at 100
        $this->assertEquals(400.00, $result['discounted_price']);
    }

    public function test_fixed_amount_discount()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'fixed_amount', 'discount_value' => 75,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $result = $this->service->calculateDiscount(200.00, collect([$offer]));

        $this->assertEquals(75.00, $result['discount_amount']);
        $this->assertEquals(125.00, $result['discounted_price']);
    }

    public function test_fixed_amount_cannot_exceed_service_charge()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'fixed_amount', 'discount_value' => 500,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $result = $this->service->calculateDiscount(200.00, collect([$offer]));

        $this->assertEquals(200.00, $result['discount_amount']); // capped at service charge
        $this->assertEquals(0.00, $result['discounted_price']);
    }

    public function test_override_price()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'override_price', 'discount_value' => 0,
            'override_price' => 150,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $result = $this->service->calculateDiscount(500.00, collect([$offer]));

        $this->assertEquals(350.00, $result['discount_amount']);
        $this->assertEquals(150.00, $result['discounted_price']);
    }

    public function test_override_price_no_effect_when_higher()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'override_price', 'discount_value' => 0,
            'override_price' => 600,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $result = $this->service->calculateDiscount(500.00, collect([$offer]));

        $this->assertEquals(0, $result['discount_amount']);
        $this->assertEquals(500.00, $result['discounted_price']);
    }

    public function test_empty_offers_returns_zero_discount()
    {
        $result = $this->service->calculateDiscount(500.00, collect());

        $this->assertEquals(0, $result['discount_amount']);
        $this->assertEquals(500.00, $result['discounted_price']);
        $this->assertNull($result['applied_offer_id']);
    }

    public function test_non_stackable_picks_best()
    {
        $offer1 = Offer::create([
            'title_en' => 'Low', 'title_ar' => 'Low',
            'discount_type' => 'fixed_amount', 'discount_value' => 50,
            'stackable' => false, 'priority' => 1,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $offer2 = Offer::create([
            'title_en' => 'High', 'title_ar' => 'High',
            'discount_type' => 'fixed_amount', 'discount_value' => 100,
            'stackable' => false, 'priority' => 2,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $result = $this->service->calculateDiscount(500.00, collect([$offer1, $offer2]));

        $this->assertEquals(100.00, $result['discount_amount']);
        $this->assertEquals($offer2->id, $result['applied_offer_id']);
    }

    public function test_stackable_offers_accumulate()
    {
        $offer1 = Offer::create([
            'title_en' => 'Stack1', 'title_ar' => 'Stack1',
            'discount_type' => 'fixed_amount', 'discount_value' => 30,
            'stackable' => true,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $offer2 = Offer::create([
            'title_en' => 'Stack2', 'title_ar' => 'Stack2',
            'discount_type' => 'fixed_amount', 'discount_value' => 20,
            'stackable' => true,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
        ]);

        $result = $this->service->calculateDiscount(500.00, collect([$offer1, $offer2]));

        $this->assertEquals(50.00, $result['discount_amount']);
        $this->assertEquals(450.00, $result['discounted_price']);
    }
}
