<?php
    $auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center">
    @if(!$order->trashed())
        @if($auth_user->can('view_test_orders'))
        <a class="btn text-info p-0 fs-5" href="{{ route('backend.test-orders.show', $order->id) }}" title="{{ __('radiology_lab.lbl_view_details') }}" data-bs-toggle="tooltip">
            <i class="ph ph-eye"></i>
        </a>
        @endif

        @if($auth_user->can('edit_test_orders'))
        <a class="btn text-primary p-0 fs-5" href="{{ route('backend.test-orders.create', ['id' => $order->id]) }}" title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
            <i class="ph ph-pencil-simple-line"></i>
        </a>
        @endif

        @if($auth_user->can('delete_test_orders'))
        <a href="{{ route('backend.test-orders.destroy', $order->id) }}"
            id="delete-order-{{ $order->id }}" class="btn text-danger p-0 fs-5"
            data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => $order->order_number, 'module' => __('radiology_lab.order_plural')]) }}">
            <i class="ph ph-trash"></i>
        </a>
        @endif
    @endif

    @if(auth()->user()->hasAnyRole(['admin']) && $order->trashed())
        <a class="btn text-primary p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_restore') }}"
            data-success-message="{{ __('messages.restore_form') }}"
            href="{{ route('backend.test-orders.action', ['id' => $order->id, 'type' => 'restore']) }}" title="{{ __('messages.restore') }}" data-bs-toggle="tooltip">
            <i class="ph ph-arrow-clockwise align-middle"></i>
        </a>

        <a class="btn text-danger p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_delete') }}"
            data-success-message="{{ __('messages.permanent_delete') }}"
            href="{{ route('backend.test-orders.action', ['id' => $order->id, 'type' => 'forcedelete']) }}" title="{{ __('messages.permanent_delete') }}" data-bs-toggle="tooltip">
            <i class="ph ph-trash align-middle"></i>
        </a>
    @endif
</div>
