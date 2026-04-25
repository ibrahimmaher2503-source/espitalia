<?php

namespace Modules\Offer\Services;

use Illuminate\Support\Collection;
use Modules\Offer\Models\Offer;

class OfferPricingService
{
    /**
     * Calculate the best applicable discount for a booking line item.
     */
    public function calculateDiscount(float $serviceCharge, Collection $eligibleOffers, array $context = []): array
    {
        if ($eligibleOffers->isEmpty() || $serviceCharge <= 0) {
            return [
                'original_price' => $serviceCharge,
                'discount_amount' => 0,
                'discounted_price' => $serviceCharge,
                'applied_offer_id' => null,
                'applied_offer_code' => null,
                'applied_offer_title' => '',
                'discount_breakdown' => [],
            ];
        }

        // Separate stackable vs non-stackable
        $nonStackable = $eligibleOffers->where('stackable', false);
        $stackable = $eligibleOffers->where('stackable', true);

        // Calculate best non-stackable offer
        $bestNonStackable = $this->getBestNonStackableDiscount($serviceCharge, $nonStackable);

        // Calculate sum of stackable discounts (respecting exclusive groups)
        $stackableResult = $this->getStackableDiscounts($serviceCharge, $stackable);

        // Choose the better option
        if ($bestNonStackable['discount_amount'] >= $stackableResult['total_discount']) {
            $discountAmount = min($bestNonStackable['discount_amount'], $serviceCharge);
            return [
                'original_price' => $serviceCharge,
                'discount_amount' => round($discountAmount, 2, PHP_ROUND_HALF_UP),
                'discounted_price' => round($serviceCharge - $discountAmount, 2, PHP_ROUND_HALF_UP),
                'applied_offer_id' => $bestNonStackable['offer_id'],
                'applied_offer_code' => $bestNonStackable['offer_code'],
                'applied_offer_title' => $bestNonStackable['offer_title'],
                'discount_breakdown' => $bestNonStackable['breakdown'],
            ];
        }

        $discountAmount = min($stackableResult['total_discount'], $serviceCharge);
        $primaryOffer = $stackableResult['offers']->first();

        return [
            'original_price' => $serviceCharge,
            'discount_amount' => round($discountAmount, 2, PHP_ROUND_HALF_UP),
            'discounted_price' => round($serviceCharge - $discountAmount, 2, PHP_ROUND_HALF_UP),
            'applied_offer_id' => $primaryOffer ? $primaryOffer->id : null,
            'applied_offer_code' => $primaryOffer ? $primaryOffer->code : null,
            'applied_offer_title' => $primaryOffer ? $primaryOffer->title : '',
            'discount_breakdown' => $stackableResult['breakdown'],
        ];
    }

    protected function getBestNonStackableDiscount(float $serviceCharge, Collection $offers): array
    {
        $best = [
            'discount_amount' => 0,
            'offer_id' => null,
            'offer_code' => null,
            'offer_title' => '',
            'breakdown' => [],
        ];

        // Sort by priority DESC, then we'll pick the one with highest discount
        $sorted = $offers->sortByDesc('priority');

        foreach ($sorted as $offer) {
            $discount = $this->calculateOfferDiscount($serviceCharge, $offer);
            if ($discount > $best['discount_amount']) {
                $best = [
                    'discount_amount' => $discount,
                    'offer_id' => $offer->id,
                    'offer_code' => $offer->code,
                    'offer_title' => $offer->title,
                    'breakdown' => [[
                        'offer_id' => $offer->id,
                        'title' => $offer->title,
                        'code' => $offer->code,
                        'discount_type' => $offer->discount_type,
                        'discount_amount' => round($discount, 2, PHP_ROUND_HALF_UP),
                    ]],
                ];
            }
        }

        return $best;
    }

    protected function getStackableDiscounts(float $serviceCharge, Collection $offers): array
    {
        $totalDiscount = 0;
        $breakdown = [];
        $usedGroups = [];
        $appliedOffers = collect();

        // Sort by priority DESC
        $sorted = $offers->sortByDesc('priority');

        foreach ($sorted as $offer) {
            // Enforce exclusive group
            if ($offer->exclusive_group && in_array($offer->exclusive_group, $usedGroups)) {
                continue;
            }

            $discount = $this->calculateOfferDiscount($serviceCharge, $offer);
            if ($discount > 0) {
                $totalDiscount += $discount;
                $appliedOffers->push($offer);
                $breakdown[] = [
                    'offer_id' => $offer->id,
                    'title' => $offer->title,
                    'code' => $offer->code,
                    'discount_type' => $offer->discount_type,
                    'discount_amount' => round($discount, 2, PHP_ROUND_HALF_UP),
                ];

                if ($offer->exclusive_group) {
                    $usedGroups[] = $offer->exclusive_group;
                }
            }
        }

        return [
            'total_discount' => round($totalDiscount, 2, PHP_ROUND_HALF_UP),
            'breakdown' => $breakdown,
            'offers' => $appliedOffers,
        ];
    }

    /**
     * Calculate discount amount for a single offer.
     */
    public function calculateOfferDiscount(float $serviceCharge, Offer $offer): float
    {
        return match ($offer->discount_type) {
            Offer::TYPE_PERCENTAGE => $this->calcPercentage($serviceCharge, $offer),
            Offer::TYPE_FIXED_AMOUNT => $this->calcFixed($serviceCharge, $offer),
            Offer::TYPE_OVERRIDE_PRICE => $this->calcOverride($serviceCharge, $offer),
            Offer::TYPE_BUNDLE_PRICE => $this->calcBundle($serviceCharge, $offer),
            Offer::TYPE_BUY_X_GET_Y => $this->calcBuyXGetY($serviceCharge, $offer),
            Offer::TYPE_FREE_ADDON => 0, // Free addon doesn't discount the primary service
            default => 0,
        };
    }

    protected function calcPercentage(float $serviceCharge, Offer $offer): float
    {
        $discount = round($serviceCharge * $offer->discount_value / 100, 2, PHP_ROUND_HALF_UP);

        if ($offer->max_discount_amount && $discount > $offer->max_discount_amount) {
            $discount = (float) $offer->max_discount_amount;
        }

        return min($discount, $serviceCharge);
    }

    protected function calcFixed(float $serviceCharge, Offer $offer): float
    {
        return min((float) $offer->discount_value, $serviceCharge);
    }

    protected function calcOverride(float $serviceCharge, Offer $offer): float
    {
        $override = (float) $offer->override_price;
        if ($override >= $serviceCharge) {
            return 0; // No discount if override is >= current price
        }

        return round($serviceCharge - $override, 2, PHP_ROUND_HALF_UP);
    }

    protected function calcBundle(float $serviceCharge, Offer $offer): float
    {
        $bundlePrice = (float) $offer->discount_value;
        if ($bundlePrice >= $serviceCharge) {
            return 0;
        }

        return round($serviceCharge - $bundlePrice, 2, PHP_ROUND_HALF_UP);
    }

    protected function calcBuyXGetY(float $serviceCharge, Offer $offer): float
    {
        // Buy X Get Y: the discount is the percentage off the "get" service
        // In a single-service context, this applies when booking qualifies
        $getDiscount = (float) ($offer->get_discount_percentage ?? 100);

        return round($serviceCharge * $getDiscount / 100, 2, PHP_ROUND_HALF_UP);
    }
}
