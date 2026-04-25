<div class="d-flex justify-content-end align-items-center">
    <a class="btn text-primary p-0 fs-5" href="{{ route('backend.governorates.create', ['id' => $item->id]) }}" title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
        <i class="ph ph-pencil-simple-line"></i>
    </a>

    <a href="{{ route('backend.governorates.destroy', $item->id) }}"
        class="btn text-danger p-0 fs-5"
        data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
        data-confirm="{{ __('messages.are_you_sure?', ['form' => e($item->name) ?? __('Unknown'), 'module' => __('location.lbl_governorates')]) }}">
        <i class="ph ph-trash"></i>
    </a>
</div>
