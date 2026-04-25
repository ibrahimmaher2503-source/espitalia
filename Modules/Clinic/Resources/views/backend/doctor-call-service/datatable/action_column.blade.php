<div class="d-flex gap-2">
    <a href="{{ route('backend.doctor-call-service.edit', $data->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.edit') }}">
        <i class="ph ph-pencil-simple"></i>
    </a>
    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $data->id }}" data-url="{{ route('backend.doctor-call-service.destroy', $data->id) }}" title="{{ __('messages.delete') }}">
        <i class="ph ph-trash"></i>
    </button>
</div>
