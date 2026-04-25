<?php

namespace Modules\Offer\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // Identity
            'title_en' => $this->title_en,
            'title_ar' => $this->title_ar,
            'title' => $this->title,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'description' => $this->description,
            'short_label_en' => $this->short_label_en,
            'short_label_ar' => $this->short_label_ar,
            'short_label' => $this->short_label,
            'terms_en' => $this->terms_en,
            'terms_ar' => $this->terms_ar,
            'terms' => $this->terms,
            'banner_text_en' => $this->banner_text_en,
            'banner_text_ar' => $this->banner_text_ar,
            'banner_text' => $this->banner_text,
            'slug' => $this->slug,
            'internal_name' => $this->internal_name,

            // Discount
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'max_discount_amount' => $this->max_discount_amount ? (float) $this->max_discount_amount : null,
            'minimum_order_amount' => (float) $this->minimum_order_amount,
            'override_price' => $this->override_price ? (float) $this->override_price : null,
            'buy_quantity' => $this->buy_quantity,
            'get_quantity' => $this->get_quantity,
            'get_service_id' => $this->get_service_id,
            'get_discount_percentage' => $this->get_discount_percentage ? (float) $this->get_discount_percentage : null,

            // Coupon
            'code' => $this->code,
            'requires_coupon' => $this->requires_coupon,
            'auto_apply' => $this->auto_apply,

            // Targeting
            'target_scope' => $this->target_scope,
            'applies_to_all_services' => $this->applies_to_all_services,
            'applies_to_all_branches' => $this->applies_to_all_branches,
            'applies_to_all_categories' => $this->applies_to_all_categories,
            'vendor_id' => $this->vendor_id,
            'services' => $this->whenLoaded('services', fn () => $this->services->map(fn ($s) => [
                'id' => $s->id,
                'service_id' => $s->service_id,
                'service_name' => $s->service?->name ?? '',
            ])),
            'branches' => $this->whenLoaded('branches', fn () => $this->branches->map(fn ($b) => [
                'id' => $b->id,
                'branch_id' => $b->branch_id,
                'branch_name' => $b->branch?->name ?? '',
            ])),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($c) => [
                'id' => $c->id,
                'category_id' => $c->category_id,
                'category_name' => $c->category?->name ?? '',
            ])),

            // Customer
            'customer_scope' => $this->customer_scope,
            'first_booking_only' => $this->first_booking_only,
            'min_completed_bookings' => $this->min_completed_bookings,
            'max_completed_bookings' => $this->max_completed_bookings,
            'customer_segments' => $this->whenLoaded('customerSegments'),

            // Schedule
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'is_flash' => $this->is_flash,
            'schedules' => $this->whenLoaded('schedules'),

            // Usage
            'usage_limit_total' => $this->usage_limit_total,
            'usage_limit_per_user' => $this->usage_limit_per_user,
            'used_count' => $this->used_count,

            // Priority
            'priority' => $this->priority,
            'stackable' => $this->stackable,
            'exclusive_group' => $this->exclusive_group,

            // Display
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'status' => $this->status,

            // Channel
            'allowed_channels' => $this->allowed_channels,
            'allowed_payment_methods' => $this->allowed_payment_methods,

            // Commission
            'commission_on_original' => $this->commission_on_original,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
