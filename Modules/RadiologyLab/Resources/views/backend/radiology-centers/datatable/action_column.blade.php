<?php
    $auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center">
    @if(!$radiologyCenter->trashed())
        @if($auth_user->can('edit_radiology_centers'))
        <a class="btn text-primary p-0 fs-5" href="{{ route('backend.radiology-centers.create', ['id' => $radiologyCenter->id]) }}" title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
            <i class="ph ph-pencil-simple-line"></i>
        </a>
        @endif

        @if($auth_user->can('delete_radiology_centers'))
        <a href="{{ route('backend.radiology-centers.destroy', $radiologyCenter->id) }}"
            id="delete-radiology-center-{{ $radiologyCenter->id }}" class="btn text-danger p-0 fs-5"
            data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => e($radiologyCenter->name) ?? __('Unknown'), 'module' => __('radiology_lab.radiology_center_plural')]) }}">
            <i class="ph ph-trash"></i>
        </a>
        @endif
    @endif

    @if(auth()->user()->hasAnyRole(['admin']) && $radiologyCenter->trashed())
        <a class="btn text-primary p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_restore') }}"
            data-success-message="{{ __('messages.restore_form') }}"
            href="{{ route('backend.radiology-centers.action', ['id' => $radiologyCenter->id, 'type' => 'restore']) }}" title="{{ __('messages.restore') }}" data-bs-toggle="tooltip">
            <i class="ph ph-arrow-clockwise align-middle"></i>
        </a>

        <a class="btn text-danger p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_delete') }}"
            data-success-message="{{ __('messages.permanent_delete') }}"
            href="{{ route('backend.radiology-centers.action', ['id' => $radiologyCenter->id, 'type' => 'forcedelete']) }}" title="{{ __('messages.permanent_delete') }}" data-bs-toggle="tooltip">
            <i class="ph ph-trash align-middle"></i>
        </a>
    @endif
</div>
