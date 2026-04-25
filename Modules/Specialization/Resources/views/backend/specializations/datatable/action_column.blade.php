<div class="d-flex gap-2 align-items-center justify-content-end">
  @if(!$data->trashed())
  @can('edit_specialization')
      <a class="btn text-success p-0 fs-5" href="{{ route('backend.specializations.edit', $data->id) }}">
          <i class="ph ph-pencil-simple-line align-middle"></i>
      </a>
  @endcan

  @can('delete_specialization')
      <a href="{{ route('backend.specializations.destroy', $data->id) }}" id="delete-specialization-{{ $data->id }}" class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip" title="{{ __('messages.delete') }}" data-confirm="{{ __('messages.are_you_sure_delete') }}">
          <i class="ph ph-trash align-middle"></i>
      </a>
  @endcan
  @else
  @can('delete_specialization')
      <a class="btn text-secondary p-0 fs-5 restore-tax" data-bs-toggle="tooltip" title="{{ __('messages.restore') }}" href="{{ route('backend.specializations.restore', $data->id) }}" data-confirm-message="{{ __('messages.are_you_sure_restore') }}" data-success-message="{{ __('messages.restore_form') }}">
          <i class="ph ph-arrow-clockwise align-middle"></i>
      </a>

      <a href="{{ route('backend.specializations.force_delete', $data->id) }}" id="force-delete-specialization-{{ $data->id }}" class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip" title="{{ __('messages.permanent_delete') }}" data-confirm="{{ __('messages.are_you_sure_delete') }}">
          <i class="ph ph-trash align-middle"></i>
      </a>
  @endcan
  @endif
</div>
