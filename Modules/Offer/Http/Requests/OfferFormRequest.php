<?php

namespace Modules\Offer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('offer');

        return [
            // Bilingual content
            'title_en' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string|max:5000',
            'description_ar' => 'nullable|string|max:5000',
            'short_label_en' => 'nullable|string|max:100',
            'short_label_ar' => 'nullable|string|max:100',
            'terms_en' => 'nullable|string|max:5000',
            'terms_ar' => 'nullable|string|max:5000',
            'banner_text_en' => 'nullable|string|max:255',
            'banner_text_ar' => 'nullable|string|max:255',
            'internal_name' => 'nullable|string|max:255',

            // Discount
            'discount_type' => 'required|in:percentage,fixed_amount,override_price,bundle_price,buy_x_get_y,free_addon',
            'discount_value' => [
                'required', 'numeric', 'min:0',
                function ($attr, $value, $fail) {
                    if ($this->discount_type === 'percentage' && $value > 100) {
                        $fail(__('offer.validation_percentage_max'));
                    }
                },
            ],
            'max_discount_amount' => 'nullable|numeric|min:0',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'override_price' => 'required_if:discount_type,override_price|nullable|numeric|min:0',
            'buy_quantity' => 'required_if:discount_type,buy_x_get_y|nullable|integer|min:1',
            'get_quantity' => 'required_if:discount_type,buy_x_get_y|nullable|integer|min:1',
            'get_service_id' => 'nullable|integer|exists:clinics_services,id',
            'get_discount_percentage' => 'nullable|numeric|min:0|max:100',

            // Coupon
            'code' => [
                'nullable', 'string', 'max:50',
                Rule::unique('offers', 'code')->ignore($id)->whereNull('deleted_at'),
            ],
            'requires_coupon' => 'nullable|boolean',
            'auto_apply' => 'nullable|boolean',

            // Targeting
            'target_scope' => 'required|in:global,service,category,branch,package,vendor',
            'applies_to_all_services' => 'nullable|boolean',
            'applies_to_all_branches' => 'nullable|boolean',
            'applies_to_all_categories' => 'nullable|boolean',
            'vendor_id' => 'nullable|exists:users,id',
            'service_ids' => 'required_if:applies_to_all_services,false|nullable|array',
            'service_ids.*' => 'integer|exists:clinics_services,id',
            'branch_ids' => 'required_if:applies_to_all_branches,false|nullable|array',
            'branch_ids.*' => 'integer|exists:branches,id',
            'category_ids' => 'required_if:applies_to_all_categories,false|nullable|array',
            'category_ids.*' => 'integer|exists:clinics_categories,id',

            // Customer
            'customer_scope' => 'nullable|in:all,first_time,returning,segment',
            'first_booking_only' => 'nullable|boolean',
            'min_completed_bookings' => 'nullable|integer|min:0',
            'max_completed_bookings' => 'nullable|integer|min:0|gte:min_completed_bookings',
            'customer_segments' => 'nullable|array',
            'customer_segments.*.segment_type' => 'required_with:customer_segments|in:role,tag,subscription_plan,custom',
            'customer_segments.*.segment_value' => 'required_with:customer_segments|string|max:100',

            // Schedule
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_flash' => 'nullable|boolean',
            'schedules' => 'nullable|array',
            'schedules.*.day_of_week' => 'nullable|integer|min:0|max:6',
            'schedules.*.start_time' => 'nullable|date_format:H:i',
            'schedules.*.end_time' => 'nullable|date_format:H:i|after:schedules.*.start_time',

            // Usage
            'usage_limit_total' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',

            // Priority
            'priority' => 'nullable|integer|min:0|max:100',
            'stackable' => 'nullable|boolean',
            'exclusive_group' => 'nullable|string|max:50',

            // Channel
            'allowed_channels' => 'nullable|array',
            'allowed_channels.*' => 'in:web,mobile,reception',
            'allowed_payment_methods' => 'nullable|array',
            'allowed_payment_methods.*' => 'in:cash,online,advance',

            // Display
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|in:draft,scheduled,active,paused',
            'commission_on_original' => 'nullable|boolean',
            'banner_image' => 'nullable|image|max:5120',
        ];
    }
}
