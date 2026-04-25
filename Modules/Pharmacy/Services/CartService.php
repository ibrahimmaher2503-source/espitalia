<?php

namespace Modules\Pharmacy\Services;

use Modules\Pharmacy\Models\PharmacyCartItem;
use Modules\Pharmacy\Models\PharmacyProduct;
use Modules\Pharmacy\Transformers\CartResource;

class CartService
{
    /**
     * Get user's cart
     */
    public function getCart($userId)
    {
        $items = PharmacyCartItem::where('user_id', $userId)
            ->with(['product', 'product.media', 'product.inventories' => function ($q) {
                $q->where('stock_qty', '>', 0)->where('is_available', true);
            }])
            ->get();

        return new CartResource((object) ['items' => $items]);
    }

    /**
     * Add item to cart with stock validation
     */
    public function addItem($userId, $productId, $quantity)
    {
        // Validate product exists and is active
        $product = PharmacyProduct::where('id', $productId)->where('is_active', true)->firstOrFail();

        // Validate stock availability - at least one pharmacy has required quantity
        $maxStock = $product->inventories()
            ->where('stock_qty', '>=', $quantity)
            ->where('is_available', true)
            ->max('stock_qty');

        if (! $maxStock) {
            throw new \Exception(__('pharmacy.msg_insufficient_stock_any'));
        }

        // Add or update cart item
        $cartItem = PharmacyCartItem::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $productId],
            ['quantity' => $quantity]
        );

        return $this->getCart($userId);
    }

    /**
     * Update cart item quantity with stock validation
     */
    public function updateItem($cartItemId, $quantity, $userId)
    {
        $cartItem = PharmacyCartItem::where('id', $cartItemId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Validate stock availability
        $maxStock = $cartItem->product->inventories()
            ->where('stock_qty', '>=', $quantity)
            ->where('is_available', true)
            ->max('stock_qty');

        if (! $maxStock) {
            throw new \Exception(__('pharmacy.msg_insufficient_stock_any'));
        }

        $cartItem->update(['quantity' => $quantity]);

        return $this->getCart($userId);
    }

    /**
     * Remove item from cart
     */
    public function removeItem($cartItemId, $userId)
    {
        $cartItem = PharmacyCartItem::where('id', $cartItemId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $cartItem->delete();

        return $this->getCart($userId);
    }

    /**
     * Clear entire cart
     */
    public function clearCart($userId)
    {
        PharmacyCartItem::where('user_id', $userId)->delete();
    }
}
