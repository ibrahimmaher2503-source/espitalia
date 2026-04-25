<?php
    $auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center">
    @if(!$item->trashed())
        <a class="btn text-primary p-0 fs-5" href="{{ route('backend.home-healthcares.create', ['id' => $item->id]) }}" title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
            <i class="ph ph-pencil-simple-line"></i>
        </a>

        <a href="{{ route('backend.home-healthcares.destroy', $item->id) }}"
            class="btn text-danger p-0 fs-5"
            data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => e($item->name) ?? __('Unknown'), 'module' => __('location.home_healthcare_plural')]) }}">
            <i class="ph ph-trash"></i>
        </a>
    @endif

    @if(auth()->user()->hasAnyRole(['admin']) && $item->trashed())
        <a class="btn text-primary p-0 fs-5 restore-tax"
            data-confirm-message="{{ __('messages.are_you_sure_restore') }}"
            data-success-message="{{ __('messages.restore_form') }}"
            href="{{ route('backend.home-healthcares.destroy', $item->id) }}" title="{{ __('messages.restore') }}" data-bs-toggle="tooltip">
            <i class="ph ph-arrow-clockwise align-middle"></i>
        </a>
    @endif
</div>
