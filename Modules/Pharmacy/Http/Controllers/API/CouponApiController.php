<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyCoupon;

class CouponApiController extends Controller
{
    /**
     * Validate a coupon code against a pharmacy and subtotal
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = PharmacyCoupon::where('code', $request->code)
            ->where(function ($q) use ($request) {
                $q->where('pharmacy_id', $request->pharmacy_id)
                    ->orWhereNull('pharmacy_id');
            })
            ->first();

        if (! $coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid coupon code',
            ], 422);
        }

        if (! $coupon->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'This coupon is no longer active',
            ], 422);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'This coupon has expired',
            ], 422);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return response()->json([
                'status' => false,
                'message' => 'This coupon is not yet valid',
            ], 422);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json([
                'status' => false,
                'message' => 'This coupon has reached its usage limit',
            ], 422);
        }

        if (! $coupon->isValidForUser(auth()->id())) {
            return response()->json([
                'status' => false,
                'message' => 'You have already used this coupon the maximum number of times',
            ], 422);
        }

        if ($request->subtotal < $coupon->min_order_amount) {
            return response()->json([
                'status' => false,
                'message' => "Minimum order amount is {$coupon->min_order_amount}",
            ], 422);
        }

        $discount = $coupon->calculateDiscount($request->subtotal);

        return response()->json([
            'status' => true,
            'data' => [
                'coupon_id' => $coupon->id,
                'code' => $coupon->code,
                'title' => $coupon->title,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'discount_amount' => $discount,
                'subtotal' => $request->subtotal,
                'total_after_discount' => $request->subtotal - $discount,
            ],
            'message' => 'Coupon is valid',
        ]);
    }
}
