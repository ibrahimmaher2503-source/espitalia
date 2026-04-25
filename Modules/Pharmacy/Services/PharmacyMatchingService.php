<?php

namespace Modules\Pharmacy\Services;

use Illuminate\Support\Facades\DB;
use Modules\Pharmacy\Models\Pharmacy;
use Modules\Pharmacy\Models\PharmacyCartItem;
use Modules\Pharmacy\Transformers\AvailablePharmacyResource;

class PharmacyMatchingService
{
    /**
     * Find pharmacies that can fulfill the entire cart.
     * Uses a single query with JOIN + GROUP BY + HAVING instead of N+1.
     */
    public function findAvailablePharmacies($userId, $addressId = null)
    {
        // Get cart items
        $cartItems = PharmacyCartItem::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return [
                'pharmacies' => [],
                'message' => 'Your cart is empty. Please add items before selecting a pharmacy.',
            ];
        }

        $productIds = $cartItems->pluck('product_id')->toArray();
        $totalProducts = count($productIds);

        // Build a quantity requirements map
        $quantityMap = $cartItems->keyBy('product_id')->map(fn ($item) => $item->quantity);

        // Single query: find pharmacies that have ALL cart products in stock with sufficient quantity
        $pharmacyIds = DB::table('pharmacy_inventory')
            ->join('pharmacies', 'pharmacies.id', '=', 'pharmacy_inventory.pharmacy_id')
            ->where('pharmacies.is_active', true)
            ->where('pharmacy_inventory.is_available', true)
            ->whereIn('pharmacy_inventory.product_id', $productIds)
            ->where(function ($query) use ($quantityMap) {
                // Each product must have enough stock
                foreach ($quantityMap as $productId => $qty) {
                    $query->orWhere(function ($q) use ($productId, $qty) {
                        $q->where('pharmacy_inventory.product_id', $productId)
                          ->where('pharmacy_inventory.stock_qty', '>=', $qty);
                    });
                }
            })
            ->groupBy('pharmacy_inventory.pharmacy_id')
            ->havingRaw('COUNT(DISTINCT pharmacy_inventory.product_id) = ?', [$totalProducts])
            ->pluck('pharmacy_inventory.pharmacy_id')
            ->toArray();

        if (empty($pharmacyIds)) {
            return [
                'pharmacies' => [],
                'message' => 'No pharmacy can fulfill all items in your cart. Please adjust your cart and try again.',
            ];
        }

        // Now load only matching pharmacies with their inventory for pricing
        $pharmacies = Pharmacy::whereIn('id', $pharmacyIds)
            ->with(['inventory' => function ($q) use ($productIds) {
                $q->whereIn('product_id', $productIds)->where('is_available', true);
            }])
            ->get();

        $availablePharmacies = [];
        foreach ($pharmacies as $pharmacy) {
            $subtotal = 0;
            $canFulfill = true;

            foreach ($cartItems as $cartItem) {
                $inv = $pharmacy->inventory->firstWhere('product_id', $cartItem->product_id);
                if (! $inv || $inv->stock_qty < $cartItem->quantity) {
                    $canFulfill = false;
                    break;
                }
                $subtotal += $inv->price * $cartItem->quantity;
            }

            if ($canFulfill) {
                $pharmacy->subtotal = $subtotal;
                $availablePharmacies[] = new AvailablePharmacyResource($pharmacy);
            }
        }

        return [
            'pharmacies' => $availablePharmacies,
            'message' => empty($availablePharmacies) ? 'No pharmacy can fulfill all items in your cart.' : null,
        ];
    }
}
