<?php
    $auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center">
    @if(!$booking->trashed())
        @if($auth_user->can('view_facility_bookings'))
        <a class="btn text-primary p-0 fs-5" href="{{ route('backend.facility-bookings.show', $booking->id) }}" title="{{ __('radiology_lab.lbl_view_details') }}" data-bs-toggle="tooltip">
            <i class="ph ph-eye"></i>
        </a>
        @endif

        @if($auth_user->can('manage_facility_bookings') && $booking->status === 'pending')
        <a class="btn text-info p-0 fs-5 booking-confirm-btn"
            href="javascript:void(0)"
            data-url="{{ route('backend.facility-bookings.confirm', $booking->id) }}"
            title="{{ __('radiology_lab.lbl_confirm_order') }}" data-bs-toggle="tooltip">
            <i class="ph ph-check-circle"></i>
        </a>
        @endif

        @if($auth_user->can('manage_facility_bookings') && in_array($booking->status, ['pending', 'confirmed']))
        <a href="{{ route('backend.facility-bookings.cancel', $booking->id) }}"
            class="btn text-danger p-0 fs-5"
            data-type="ajax" data-method="POST" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => e($booking->booking_number), 'module' => __('radiology_lab.booking_plural')]) }}"
            title="{{ __('radiology_lab.lbl_cancel_order') }}">
            <i class="ph ph-x-circle"></i>
        </a>
        @endif
    @endif

    @if(auth()->user()->hasAnyRole(['admin']) && $booking->trashed())
        <a class="btn text-primary p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_restore') }}"
            data-success-message="{{ __('messages.restore_form') }}"
            href="{{ route('backend.facility-bookings.action', ['id' => $booking->id, 'type' => 'restore']) }}" title="{{ __('messages.restore') }}" data-bs-toggle="tooltip">
            <i class="ph ph-arrow-clockwise align-middle"></i>
        </a>

        <a class="btn text-danger p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_delete') }}"
            data-success-message="{{ __('messages.permanent_delete') }}"
            href="{{ route('backend.facility-bookings.action', ['id' => $booking->id, 'type' => 'forcedelete']) }}" title="{{ __('messages.permanent_delete') }}" data-bs-toggle="tooltip">
            <i class="ph ph-trash align-middle"></i>
        </a>
    @endif
</div>
