<?php

return [
    // General
    'lbl_offer' => 'Offer',
    'lbl_offers' => 'Offers',
    'lbl_offer_details' => 'Offer Details',
    'lbl_create_offer' => 'Create Offer',
    'lbl_edit_offer' => 'Edit Offer',
    'lbl_offer_analytics' => 'Offer Analytics',
    'lbl_offer_logs' => 'Offer Usage Logs',

    // Identity
    'lbl_title_en' => 'Title (English)',
    'lbl_title_ar' => 'Title (Arabic)',
    'lbl_description_en' => 'Description (English)',
    'lbl_description_ar' => 'Description (Arabic)',
    'lbl_short_label_en' => 'Short Label (English)',
    'lbl_short_label_ar' => 'Short Label (Arabic)',
    'lbl_terms_en' => 'Terms (English)',
    'lbl_terms_ar' => 'Terms (Arabic)',
    'lbl_banner_text_en' => 'Banner Text (English)',
    'lbl_banner_text_ar' => 'Banner Text (Arabic)',
    'lbl_internal_name' => 'Internal Name',
    'lbl_slug' => 'Slug',

    // Discount
    'lbl_discount_type' => 'Discount Type',
    'lbl_discount_value' => 'Discount Value',
    'lbl_max_discount_amount' => 'Max Discount Amount',
    'lbl_minimum_order_amount' => 'Minimum Order Amount',
    'lbl_override_price' => 'Override Price',
    'lbl_buy_quantity' => 'Buy Quantity (X)',
    'lbl_get_quantity' => 'Get Quantity (Y)',
    'lbl_get_service' => 'Free/Discounted Service',
    'lbl_get_discount_percentage' => 'Get Discount %',

    // Discount types
    'type_percentage' => 'Percentage',
    'type_fixed_amount' => 'Fixed Amount',
    'type_override_price' => 'Override Price',
    'type_bundle_price' => 'Bundle Price',
    'type_buy_x_get_y' => 'Buy X Get Y',
    'type_free_addon' => 'Free Add-on',

    // Coupon
    'lbl_coupon_code' => 'Coupon Code',
    'lbl_requires_coupon' => 'Requires Coupon',
    'lbl_auto_apply' => 'Auto-Apply',
    'lbl_generate_code' => 'Generate Random Code',

    // Targeting
    'lbl_target_scope' => 'Target Scope',
    'lbl_applies_to_all_services' => 'Applies to All Services',
    'lbl_applies_to_all_branches' => 'Applies to All Branches',
    'lbl_applies_to_all_categories' => 'Applies to All Categories',
    'lbl_select_services' => 'Select Services',
    'lbl_select_branches' => 'Select Branches',
    'lbl_select_categories' => 'Select Categories',
    'lbl_vendor' => 'Vendor',
    'scope_global' => 'Global',
    'scope_service' => 'Service',
    'scope_category' => 'Category',
    'scope_branch' => 'Branch',
    'scope_package' => 'Package',
    'scope_vendor' => 'Vendor',

    // Customer
    'lbl_customer_scope' => 'Customer Scope',
    'lbl_first_booking_only' => 'First Booking Only',
    'lbl_min_completed_bookings' => 'Min Completed Bookings',
    'lbl_max_completed_bookings' => 'Max Completed Bookings',
    'lbl_customer_segments' => 'Customer Segments',
    'customer_all' => 'All Customers',
    'customer_first_time' => 'First-Time Customers',
    'customer_returning' => 'Returning Customers',
    'customer_segment' => 'Specific Segments',

    // Schedule
    'lbl_starts_at' => 'Starts At',
    'lbl_ends_at' => 'Ends At',
    'lbl_flash_sale' => 'Flash Sale',
    'lbl_schedule' => 'Recurring Schedule',
    'lbl_day_of_week' => 'Day of Week',
    'lbl_start_time' => 'Start Time',
    'lbl_end_time' => 'End Time',

    // Usage
    'lbl_usage_limit' => 'Total Usage Limit',
    'lbl_usage_per_user' => 'Per-User Limit',
    'lbl_used_count' => 'Times Used',

    // Priority & Stacking
    'lbl_priority' => 'Priority',
    'lbl_stackable' => 'Stackable',
    'lbl_exclusive_group' => 'Exclusive Group',

    // Channel
    'lbl_allowed_channels' => 'Allowed Channels',
    'lbl_allowed_payment_methods' => 'Allowed Payment Methods',
    'channel_web' => 'Web',
    'channel_mobile' => 'Mobile',
    'channel_reception' => 'Reception',

    // Display
    'lbl_featured' => 'Featured',
    'lbl_banner_image' => 'Banner Image',
    'lbl_commission_on_original' => 'Commission on Original Price',

    // Status
    'lbl_status' => 'Status',
    'status_draft' => 'Draft',
    'status_scheduled' => 'Scheduled',
    'status_active' => 'Active',
    'status_paused' => 'Paused',
    'status_expired' => 'Expired',
    'status_exhausted' => 'Exhausted',

    // Analytics
    'lbl_active_offers' => 'Active Offers',
    'lbl_total_redemptions' => 'Total Redemptions',
    'lbl_total_discount_given' => 'Total Discount Given',
    'lbl_avg_discount' => 'Average Discount',
    'lbl_revenue_impact' => 'Revenue Impact',
    'lbl_top_offers' => 'Top Performing Offers',
    'lbl_redemption_timeline' => 'Redemption Timeline',

    // Usage log columns
    'lbl_booking_type' => 'Booking Type',
    'lbl_booking_id' => 'Booking ID',
    'lbl_original_amount' => 'Original Amount',
    'lbl_discount_amount' => 'Discount Amount',
    'lbl_final_amount' => 'Final Amount',
    'lbl_usage_status' => 'Usage Status',

    // Messages
    'msg_created' => 'Offer created successfully',
    'msg_updated' => 'Offer updated successfully',
    'msg_deleted' => 'Offer deleted successfully',
    'msg_status_updated' => 'Offer status updated',
    'msg_duplicated' => 'Offer duplicated successfully',
    'msg_coupon_valid' => 'Coupon applied successfully',
    'msg_coupon_invalid' => 'Invalid coupon code',
    'msg_coupon_expired' => 'This coupon has expired',
    'msg_coupon_exhausted' => 'This coupon has reached its usage limit',
    'msg_coupon_per_user_limit' => 'You have already used this coupon',
    'msg_below_minimum' => 'Order amount is below minimum required',
    'msg_offer_not_applicable' => 'This offer is not applicable to your selection',

    // Validation
    'validation_percentage_max' => 'Percentage discount cannot exceed 100.',
];
