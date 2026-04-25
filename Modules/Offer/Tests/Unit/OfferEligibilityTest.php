<?php

namespace Modules\Offer\Tests\Unit;

use Modules\Offer\Models\Offer;
use Modules\Offer\Services\OfferEligibilityService;

class OfferEligibilityTest extends BaseTest
{
    protected OfferEligibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OfferEligibilityService();
    }

    public function test_inactive_offer_is_invalid()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => false,
        ]);

        $result = $this->service->validateOffer($offer, $this->baseContext());

        $this->assertFalse($result['valid']);
        $this->assertEquals('offer_inactive', $result['reason']);
    }

    public function test_draft_offer_is_invalid()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'target_scope' => 'global', 'status' => 'draft', 'is_active' => true,
        ]);

        $result = $this->service->validateOffer($offer, $this->baseContext());

        $this->assertFalse($result['valid']);
        $this->assertEquals('offer_inactive', $result['reason']);
    }

    public function test_expired_offer_is_invalid()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
            'ends_at' => now()->subDay(),
        ]);

        $result = $this->service->validateOffer($offer, $this->baseContext());

        $this->assertFalse($result['valid']);
        $this->assertEquals('expired', $result['reason']);
    }

    public function test_not_yet_started_offer_is_invalid()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
            'starts_at' => now()->addDay(),
        ]);

        $result = $this->service->validateOffer($offer, $this->baseContext());

        $this->assertFalse($result['valid']);
        $this->assertEquals('not_started', $result['reason']);
    }

    public function test_active_global_offer_is_valid()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
            'applies_to_all_services' => true,
            'applies_to_all_branches' => true,
            'applies_to_all_categories' => true,
        ]);

        $result = $this->service->validateOffer($offer, $this->baseContext());

        $this->assertTrue($result['valid']);
    }

    public function test_below_minimum_amount_is_invalid()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'minimum_order_amount' => 200,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
            'applies_to_all_services' => true,
            'applies_to_all_branches' => true,
            'applies_to_all_categories' => true,
        ]);

        $context = $this->baseContext();
        $context['serviceCharge'] = 100;

        $result = $this->service->validateOffer($offer, $context);

        $this->assertFalse($result['valid']);
        $this->assertEquals('below_minimum_amount', $result['reason']);
    }

    public function test_coupon_required_without_code_is_invalid()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'code' => 'TEST10', 'requires_coupon' => true,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
            'applies_to_all_services' => true,
            'applies_to_all_branches' => true,
            'applies_to_all_categories' => true,
        ]);

        $context = $this->baseContext();
        $context['couponCode'] = null;

        $result = $this->service->validateOffer($offer, $context);

        $this->assertFalse($result['valid']);
        $this->assertEquals('coupon_required', $result['reason']);
    }

    public function test_coupon_with_correct_code_is_valid()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'code' => 'TEST10', 'requires_coupon' => true,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
            'applies_to_all_services' => true,
            'applies_to_all_branches' => true,
            'applies_to_all_categories' => true,
        ]);

        $context = $this->baseContext();
        $context['couponCode'] = 'TEST10';

        $result = $this->service->validateOffer($offer, $context);

        $this->assertTrue($result['valid']);
    }

    public function test_usage_limit_total_reached()
    {
        $offer = Offer::create([
            'title_en' => 'Test', 'title_ar' => 'Test',
            'discount_type' => 'percentage', 'discount_value' => 10,
            'usage_limit_total' => 5, 'used_count' => 5,
            'target_scope' => 'global', 'status' => 'active', 'is_active' => true,
            'applies_to_all_services' => true,
            'applies_to_all_branches' => true,
            'applies_to_all_categories' => true,
        ]);

        $result = $this->service->validateOffer($offer, $this->baseContext());

        $this->assertFalse($result['valid']);
        $this->assertEquals('usage_limit_reached', $result['reason']);
    }

    protected function baseContext(): array
    {
        return [
            'userId' => 1,
            'serviceId' => 1,
            'branchId' => null,
            'clinicId' => null,
            'serviceCharge' => 500.00,
            'channel' => 'web',
            'paymentMethod' => null,
            'couponCode' => null,
        ];
    }
}
