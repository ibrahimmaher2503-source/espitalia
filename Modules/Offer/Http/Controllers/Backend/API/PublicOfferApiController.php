<?php

namespace Modules\Offer\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Offer\Http\Requests\CalculateDiscountRequest;
use Modules\Offer\Http\Requests\CouponValidateRequest;
use Modules\Offer\Models\Offer;
use Modules\Offer\Services\OfferEligibilityService;
use Modules\Offer\Services\OfferPricingService;
use Modules\Offer\Transformers\PublicOfferResource;

class PublicOfferApiController extends Controller
{
    protected OfferEligibilityService $eligibilityService;
    protected OfferPricingService $pricingService;

    public function __construct(OfferEligibilityService $eligibilityService, OfferPricingService $pricingService)
    {
        $this->eligibilityService = $eligibilityService;
        $this->pricingService = $pricingService;
    }

    public function publicIndex(Request $request)
    {
        $offers = Offer::active()
            ->validNow()
            ->where(function ($q) {
                $q->where('is_featured', true)
                  ->orWhere('requires_coupon', false);
            })
            ->withCount(['services', 'branches'])
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => true,
            'data' => PublicOfferResource::collection($offers->items()),
            'meta' => [
                'total' => $offers->total(),
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
            ],
        ]);
    }

    public function publicShow($slug)
    {
        $offer = Offer::active()
            ->validNow()
            ->where('slug', $slug)
            ->withCount(['services', 'branches'])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => new PublicOfferResource($offer),
        ]);
    }

    public function serviceOffers(Request $request, $serviceId)
    {
        $userId = auth()->id();

        // For unauthenticated users, show only non-personalized offers
        // (skip per-user checks by passing userId=null handled in service)
        $eligibleOffers = $this->eligibilityService->getEligibleOffers(
            $userId ?? 0,
            (int) $serviceId,
            $request->input('branch_id') ? (int) $request->input('branch_id') : null,
            $request->input('clinic_id') ? (int) $request->input('clinic_id') : null,
            (float) ($request->input('service_charge', 0)),
            $request->input('channel', 'web'),
            $request->input('payment_method'),
            null
        );

        // If unauthenticated, filter out customer-scoped offers
        if (! $userId) {
            $eligibleOffers = $eligibleOffers->filter(fn ($offer) => $offer->customer_scope === 'all' && ! $offer->first_booking_only);
        }

        return response()->json([
            'status' => true,
            'data' => PublicOfferResource::collection($eligibleOffers->values()),
        ]);
    }

    public function validateCoupon(CouponValidateRequest $request)
    {
        $context = [
            'userId' => auth()->id(),
            'serviceId' => (int) $request->service_id,
            'branchId' => $request->branch_id ? (int) $request->branch_id : null,
            'clinicId' => $request->clinic_id ? (int) $request->clinic_id : null,
            'serviceCharge' => (float) $request->service_charge,
            'channel' => $request->input('channel', 'web'),
            'paymentMethod' => $request->payment_method,
            'couponCode' => $request->code,
        ];

        $result = $this->eligibilityService->validateCouponWithReason($request->code, $context);

        if (! $result['valid']) {
            $reasonMessages = [
                'invalid_code' => __('offer.msg_coupon_invalid'),
                'expired' => __('offer.msg_coupon_expired'),
                'usage_limit_reached' => __('offer.msg_coupon_exhausted'),
                'per_user_limit_reached' => __('offer.msg_coupon_per_user_limit'),
                'below_minimum_amount' => __('offer.msg_below_minimum'),
                'offer_inactive' => __('offer.msg_coupon_invalid'),
            ];

            return response()->json([
                'status' => false,
                'message' => $reasonMessages[$result['reason']] ?? __('offer.msg_offer_not_applicable'),
                'data' => [
                    'code' => $request->code,
                    'reason' => $result['reason'],
                ],
            ]);
        }

        $offer = $result['offer'];
        $discountResult = $this->pricingService->calculateDiscount(
            $context['serviceCharge'],
            collect([$offer]),
            $context
        );

        return response()->json([
            'status' => true,
            'data' => [
                'offer_id' => $offer->id,
                'code' => $offer->code,
                'title' => $offer->title,
                'discount_type' => $offer->discount_type,
                'discount_value' => (float) $offer->discount_value,
                'original_price' => $discountResult['original_price'],
                'discount_amount' => $discountResult['discount_amount'],
                'final_price' => $discountResult['discounted_price'],
                'terms' => $offer->terms,
            ],
            'message' => __('offer.msg_coupon_valid'),
        ]);
    }

    public function calculateDiscount(CalculateDiscountRequest $request)
    {
        $userId = auth()->id();

        $eligibleOffers = $this->eligibilityService->getEligibleOffers(
            $userId,
            (int) $request->service_id,
            $request->branch_id ? (int) $request->branch_id : null,
            $request->clinic_id ? (int) $request->clinic_id : null,
            (float) $request->service_charge,
            $request->input('channel', 'web'),
            $request->payment_method,
            $request->coupon_code
        );

        $result = $this->pricingService->calculateDiscount(
            (float) $request->service_charge,
            $eligibleOffers,
            ['userId' => $userId, 'serviceId' => (int) $request->service_id]
        );

        return response()->json([
            'status' => true,
            'data' => $result,
        ]);
    }

    public function apply(Request $request)
    {
        $request->validate([
            'offer_id' => 'required|integer|exists:offers,id',
            'service_id' => 'required|integer',
            'service_charge' => 'required|numeric|min:0',
        ]);

        $offer = Offer::findOrFail($request->offer_id);

        $context = [
            'userId' => auth()->id(),
            'serviceId' => (int) $request->service_id,
            'branchId' => $request->branch_id ? (int) $request->branch_id : null,
            'clinicId' => $request->clinic_id ? (int) $request->clinic_id : null,
            'serviceCharge' => (float) $request->service_charge,
            'channel' => $request->input('channel', 'web'),
            'paymentMethod' => $request->payment_method,
            'couponCode' => $offer->code,
        ];

        $validation = $this->eligibilityService->validateOffer($offer, $context);

        if (! $validation['valid']) {
            return response()->json([
                'status' => false,
                'message' => __('offer.msg_offer_not_applicable'),
                'data' => ['reason' => $validation['reason']],
            ]);
        }

        $result = $this->pricingService->calculateDiscount(
            $context['serviceCharge'],
            collect([$offer]),
            $context
        );

        return response()->json([
            'status' => true,
            'data' => $result,
            'message' => __('offer.msg_coupon_valid'),
        ]);
    }

    public function remove(Request $request)
    {
        // Simply returns the original price without discount
        return response()->json([
            'status' => true,
            'data' => [
                'original_price' => (float) $request->input('service_charge', 0),
                'discount_amount' => 0,
                'discounted_price' => (float) $request->input('service_charge', 0),
                'applied_offer_id' => null,
                'applied_offer_code' => null,
                'applied_offer_title' => '',
                'discount_breakdown' => [],
            ],
            'message' => 'Offer removed',
        ]);
    }
}
