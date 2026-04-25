<?php
    $auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center">
    @if(!$admissionRequest->trashed())
        @if($auth_user->can('view_icu_admission_requests'))
        <a class="btn text-info p-0 fs-5" href="{{ route('backend.icu-admission-requests.show', $admissionRequest->id) }}" title="{{ __('messages.view') }}" data-bs-toggle="tooltip">
            <i class="ph ph-eye"></i>
        </a>
        @endif

        @if($auth_user->can('delete_icu_admission_requests'))
        <a href="{{ route('backend.icu-admission-requests.destroy', $admissionRequest->id) }}"
            id="delete-request-{{ $admissionRequest->id }}" class="btn text-danger p-0 fs-5"
            data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => $admissionRequest->request_number, 'module' => __('intensive_care.request_plural')]) }}">
            <i class="ph ph-trash"></i>
        </a>
        @endif
    @endif

    @if(auth()->user()->hasAnyRole(['admin']) && $admissionRequest->trashed())
        <a class="btn text-primary p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_restore') }}"
            data-success-message="{{ __('messages.restore_form') }}"
            href="{{ route('backend.icu-admission-requests.action', ['id' => $admissionRequest->id, 'type' => 'restore']) }}" title="{{ __('messages.restore') }}" data-bs-toggle="tooltip">
            <i class="ph ph-arrow-clockwise align-middle"></i>
        </a>

        <a class="btn text-danger p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_delete') }}"
            data-success-message="{{ __('messages.delete_form') }}"
            href="{{ route('backend.icu-admission-requests.action', ['id' => $admissionRequest->id, 'type' => 'forcedelete']) }}" title="{{ __('messages.permanent_delete') }}" data-bs-toggle="tooltip">
            <i class="ph ph-trash align-middle"></i>
        </a>
    @endif
</div>
