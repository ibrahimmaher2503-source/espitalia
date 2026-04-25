@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{ html()->form('POST', route('backend.lab-test-categories.store'))->id('category-form')->open() }}
            @if(isset($data) && $data->id)
                <input type="hidden" name="id" value="{{ $data->id }}">
            @endif

            {{-- Category Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-folders me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.lab-test-categories.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            @include('backend.components.translatable-input', [
                                'field' => 'name',
                                'label' => __('radiology_lab.lbl_name'),
                                'required' => true,
                                'data' => $data ?? null,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            @include('backend.components.translatable-textarea', [
                                'field' => 'description',
                                'label' => __('radiology_lab.lbl_description'),
                                'required' => false,
                                'rows' => 3,
                                'data' => $data ?? null,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="icon">{{ __('radiology_lab.lbl_icon') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="{{ old('icon', $data->icon ?? 'ph ph-drop') }} fs-5" id="icon-preview"></i></span>
                                <input type="text" class="form-control" name="icon" id="icon" value="{{ old('icon', $data->icon ?? '') }}" placeholder="ph ph-drop">
                            </div>
                            <small class="text-muted">{{ __('messages.use_phosphor_icon_classes') ?? 'Use Phosphor icon classes (e.g. ph ph-drop)' }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="display_order">{{ __('radiology_lab.lbl_display_order') }}</label>
                            <input type="number" class="form-control" name="display_order" id="display_order" value="{{ old('display_order', $data->display_order ?? 0) }}" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="status">{{ __('radiology_lab.lbl_status') }}</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="status" value="0">
                                    <input type="checkbox" class="form-check-input" name="status" id="status" value="1" {{ old('status', $data->status ?? 1) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('backend.lab-test-categories.index') }}" class="btn btn-outline-secondary">
                    {{ __('messages.close') }}
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="ph ph-floppy-disk me-1"></i> {{ __('messages.save') }}
                </button>
            </div>
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
    $(document).ready(function() {
        // Icon preview
        $('#icon').on('input', function() {
            var iconClass = $(this).val() || 'ph ph-drop';
            $('#icon-preview').attr('class', iconClass + ' fs-5');
        });
    });
</script>
@endpush
