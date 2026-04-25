<?php
    $auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center">
    @if(!$department->trashed())
        @if($auth_user->can('view_icu_departments'))
        <a class="btn text-info p-0 fs-5" href="{{ route('backend.icu-departments.show', $department->id) }}" title="{{ __('messages.view') }}" data-bs-toggle="tooltip">
            <i class="ph ph-eye"></i>
        </a>
        @endif

        @if($auth_user->can('edit_icu_departments'))
        <a class="btn text-primary p-0 fs-5" href="{{ route('backend.icu-departments.create', ['id' => $department->id]) }}" title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
            <i class="ph ph-pencil-simple-line"></i>
        </a>
        @endif

        @if($auth_user->can('delete_icu_departments'))
        <a href="{{ route('backend.icu-departments.destroy', $department->id) }}"
            id="delete-department-{{ $department->id }}" class="btn text-danger p-0 fs-5"
            data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => e($department->name) ?? __('Unknown'), 'module' => __('intensive_care.department_plural')]) }}">
            <i class="ph ph-trash"></i>
        </a>
        @endif
    @endif

    @if(auth()->user()->hasAnyRole(['admin']) && $department->trashed())
        <a class="btn text-primary p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_restore') }}"
            data-success-message="{{ __('messages.restore_form') }}"
            href="{{ route('backend.icu-departments.action', ['id' => $department->id, 'type' => 'restore']) }}" title="{{ __('messages.restore') }}" data-bs-toggle="tooltip">
            <i class="ph ph-arrow-clockwise align-middle"></i>
        </a>

        <a class="btn text-danger p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_delete') }}"
            data-success-message="{{ __('messages.delete_form') }}"
            href="{{ route('backend.icu-departments.action', ['id' => $department->id, 'type' => 'forcedelete']) }}" title="{{ __('messages.permanent_delete') }}" data-bs-toggle="tooltip">
            <i class="ph ph-trash align-middle"></i>
        </a>
    @endif
</div>
