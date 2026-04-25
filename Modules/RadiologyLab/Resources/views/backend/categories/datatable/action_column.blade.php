<?php
    $auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center">
    @if(!$category->trashed())
        @if($auth_user->can('edit_lab_test_categories'))
        <a class="btn text-primary p-0 fs-5" href="{{ route('backend.lab-test-categories.create', ['id' => $category->id]) }}" title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
            <i class="ph ph-pencil-simple-line"></i>
        </a>
        @endif

        @if($auth_user->can('delete_lab_test_categories'))
        <a href="{{ route('backend.lab-test-categories.destroy', $category->id) }}"
            id="delete-category-{{ $category->id }}" class="btn text-danger p-0 fs-5"
            data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => e($category->name) ?? __('Unknown'), 'module' => __('radiology_lab.category_plural')]) }}">
            <i class="ph ph-trash"></i>
        </a>
        @endif
    @endif

    @if(auth()->user()->hasAnyRole(['admin']) && $category->trashed())
        <a class="btn text-primary p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_restore') }}"
            data-success-message="{{ __('messages.restore_form') }}"
            href="{{ route('backend.lab-test-categories.action', ['id' => $category->id, 'type' => 'restore']) }}" title="{{ __('messages.restore') }}" data-bs-toggle="tooltip">
            <i class="ph ph-arrow-clockwise align-middle"></i>
        </a>

        <a class="btn text-danger p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_delete') }}"
            data-success-message="{{ __('messages.permanent_delete') }}"
            href="{{ route('backend.lab-test-categories.action', ['id' => $category->id, 'type' => 'forcedelete']) }}" title="{{ __('messages.permanent_delete') }}" data-bs-toggle="tooltip">
            <i class="ph ph-trash align-middle"></i>
        </a>
    @endif
</div>
