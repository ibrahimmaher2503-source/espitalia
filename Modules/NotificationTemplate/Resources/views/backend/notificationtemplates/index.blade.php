@extends('backend.layouts.app')

@section('title') {{ __($module_action) }} {{ __($module_title) }} @endsection



@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <span class="text-muted small">{{ __('menu.main') }}</span>
    <i class="ph ph-caret-double-right text-muted" style="font-size: 0.7rem;"></i>
    <span class="fw-medium small">{{ __($module_title) }}</span>
</div>
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body">

        <x-backend.section-header>
            <i class="{{ $module_icon }}"></i> {{ $module_title }} <small class="text-muted">{{ __($module_action) }}</small>

            <x-slot name="subtitle">
                @lang(":module_name Management Dashboard", ['module_name'=>Str::title($module_name)])
            </x-slot>
            <x-slot name="toolbar">
                @can('add_'.$module_name)
                <x-buttons.create route='{{ route("backend.notification-templates.create") }}' title="{{__('Create')}} {{ __($module_title) }}" />
                @endcan

                @can('restore_'.$module_name)
                <div class="btn-group">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-target="#module-dropdown-01" aria-expanded="false">
                        <i class="ph ph-gear"></i>
                    </button>
                    <ul class="dropdown-menu" id="module-dropdown-01">
                        <li>
                            <a class="dropdown-item" href='{{ route("backend.notification-templates.trashed") }}'>
                                <i class="ph ph-eye-slash"></i> View trash
                            </a>
                        </li>
                    </ul>
                </div>
                @endcan
            </x-slot>
        </x-backend.section-header>
        <div class="row mt-4">
          <div class="col">
              <table id="datatable" class="table table-hover table-responsive">
                  <tbody>
                      @foreach($$module_name as $module_name_singular)
                      <tr>
                          <td>
                              {{ $module_name_singular->id }}
                          </td>
                          <td>
                              <a href="{{ url("app/$module_name", $module_name_singular->id) }}">{{ $module_name_singular->name }}</a>
                          </td>
                          <td>
                              {{ $module_name_singular->updated_at->diffForHumans() }}
                          </td>
                          <td>
                              {{ $module_name_singular->created_by }}
                          </td>
                          <td class="text-end">
                              <a href='{!!route("backend.$module_name.edit", $module_name_singular)!!}' class='btn btn-sm btn-primary mt-1' data-bs-toggle="tooltip" title="Edit {{ __($module_title) }}"><i class="ph ph-pencil-simple"></i></a>
                              <a href='{!!route("backend.$module_name.show", $module_name_singular)!!}' class='btn btn-sm btn-outline-primary mt-1' data-bs-toggle="tooltip" title="Show {{ __($module_title) }}"><i class="ph ph-eye"></i></a>
                          </td>
                      </tr>
                      @endforeach
                  </tbody>
              </table>
          </div>
      </div>
  </div>
  <div class="card-footer">
      <div class="row">
          <div class="col-7">
              <div class="float-left">
                  Total {{ $$module_name->total() }} {{ __($module_name) }}
              </div>
          </div>
          <div class="col-5">
              <div class="float-end">
                  {!! $$module_name->render() !!}
              </div>
          </div>
      </div>
  </div>
</div>
@endsection

@push('after-styles')
<style>
    #datatable thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        border-bottom: 2px solid #e2e8f0;
    }
    #datatable tbody td {
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    #datatable tbody tr:hover {
        background-color: rgba(99,102,241,0.04);
    }
    .card-footer {
        background: transparent;
        border-top: 1px solid #f1f5f9;
    }
</style>
@endpush

