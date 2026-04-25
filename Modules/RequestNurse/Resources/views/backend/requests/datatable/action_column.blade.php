<?php
    $auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center">
    @if(!$nurseRequest->trashed())
        @if($auth_user->can('view_request_nurse'))
        <a class="btn text-info p-0 fs-5" href="{{ route('backend.nurse-requests.show', $nurseRequest->id) }}" title="{{ __('request_nurse.lbl_view_details') }}" data-bs-toggle="tooltip">
            <i class="ph ph-eye"></i>
        </a>
        @endif

        @if($auth_user->can('edit_request_nurse'))
        <a class="btn text-primary p-0 fs-5" href="{{ route('backend.nurse-requests.create', ['id' => $nurseRequest->id]) }}" title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
            <i class="ph ph-pencil-simple-line"></i>
        </a>
        @endif

        @if($auth_user->can('delete_request_nurse'))
        <a href="{{ route('backend.nurse-requests.destroy', $nurseRequest->id) }}"
            id="delete-request-{{ $nurseRequest->id }}" class="btn text-danger p-0 fs-5"
            data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => '#' . $nurseRequest->id, 'module' => __('request_nurse.plural_title')]) }}">
            <i class="ph ph-trash"></i>
        </a>
        @endif
    @endif

    @if(auth()->user()->hasAnyRole(['admin']) && $nurseRequest->trashed())
        <a class="btn text-primary p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_restore') }}"
            data-success-message="{{ __('messages.restore_form') }}"
            href="{{ route('backend.nurse-requests.action', ['id' => $nurseRequest->id, 'type' => 'restore']) }}" title="{{ __('messages.restore') }}" data-bs-toggle="tooltip">
            <i class="ph ph-arrow-clockwise align-middle"></i>
        </a>

        <a class="btn text-danger p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_restore') }}"
            data-success-message="{{ __('messages.restore_form') }}"
            href="{{ route('backend.nurse-requests.action', ['id' => $nurseRequest->id, 'type' => 'forcedelete']) }}" title="{{ __('messages.permanent_delete') }}" data-bs-toggle="tooltip">
            <i class="ph ph-trash align-middle"></i>
        </a>
    @endif
</div>
