@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{ html()->form('POST', route('backend.lab-tests.store'))->id('lab-test-form')->open() }}
            @if(isset($data) && $data->id)
                <input type="hidden" name="id" value="{{ $data->id }}">
            @endif

            {{-- Test Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-flask me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.lab-tests.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            @include('backend.components.translatable-input', [
                                'field' => 'name',
                                'label' => __('radiology_lab.lbl_test_name'),
                                'required' => true,
                                'data' => $data ?? null,
                            ])
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="code">{{ __('radiology_lab.lbl_test_code') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" id="code" value="{{ old('code', $data->code ?? '') }}" placeholder="e.g. CBC" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="category_id">{{ __('radiology_lab.lbl_category') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('category_id') is-invalid @enderror" name="category_id" id="category_id" style="width:100%" required>
                                <option value="">{{ __('radiology_lab.lbl_select_category') }}</option>
                                @foreach($categories as $catId => $catName)
                                    <option value="{{ $catId }}" {{ old('category_id', $data->category_id ?? '') == $catId ? 'selected' : '' }}>{{ $catName }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="department">{{ __('radiology_lab.lbl_department') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('department') is-invalid @enderror" name="department" id="department" required>
                                <option value="">{{ __('messages.select') ?? 'Select...' }}</option>
                                <option value="laboratory" {{ old('department', $data->department ?? '') == 'laboratory' ? 'selected' : '' }}>{{ __('radiology_lab.dept_laboratory') }}</option>
                                <option value="radiology" {{ old('department', $data->department ?? '') == 'radiology' ? 'selected' : '' }}>{{ __('radiology_lab.dept_radiology') }}</option>
                            </select>
                            @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="sample_type">{{ __('radiology_lab.lbl_sample_type') }}</label>
                            <select class="form-control" name="sample_type" id="sample_type">
                                <option value="">{{ __('messages.select') ?? 'Select...' }}</option>
                                <option value="blood" {{ old('sample_type', $data->sample_type ?? '') == 'blood' ? 'selected' : '' }}>{{ __('radiology_lab.sample_blood') }}</option>
                                <option value="urine" {{ old('sample_type', $data->sample_type ?? '') == 'urine' ? 'selected' : '' }}>{{ __('radiology_lab.sample_urine') }}</option>
                                <option value="stool" {{ old('sample_type', $data->sample_type ?? '') == 'stool' ? 'selected' : '' }}>{{ __('radiology_lab.sample_stool') }}</option>
                                <option value="tissue" {{ old('sample_type', $data->sample_type ?? '') == 'tissue' ? 'selected' : '' }}>{{ __('radiology_lab.sample_tissue') }}</option>
                                <option value="imaging" {{ old('sample_type', $data->sample_type ?? '') == 'imaging' ? 'selected' : '' }}>{{ __('radiology_lab.sample_imaging') }}</option>
                                <option value="swab" {{ old('sample_type', $data->sample_type ?? '') == 'swab' ? 'selected' : '' }}>{{ __('radiology_lab.sample_swab') }}</option>
                                <option value="other" {{ old('sample_type', $data->sample_type ?? '') == 'other' ? 'selected' : '' }}>{{ __('radiology_lab.sample_other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="turnaround_time">{{ __('radiology_lab.lbl_turnaround_time') }}</label>
                            <input type="text" class="form-control" name="turnaround_time" id="turnaround_time" value="{{ old('turnaround_time', $data->turnaround_time ?? '') }}" placeholder="e.g. 24 hours">
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
                        <div class="col-md-12">
                            @include('backend.components.translatable-textarea', [
                                'field' => 'preparation_instructions',
                                'label' => __('radiology_lab.lbl_preparation_instructions'),
                                'required' => false,
                                'rows' => 3,
                                'placeholder' => 'e.g. Fasting for 8-12 hours before test',
                                'data' => $data ?? null,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="default_price">{{ __('radiology_lab.lbl_default_price') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-currency-dollar"></i></span>
                                <input type="number" step="0.01" class="form-control @error('default_price') is-invalid @enderror" name="default_price" id="default_price" value="{{ old('default_price', $data->default_price ?? 0) }}" required min="0">
                                @error('default_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center h-100 pt-4">
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
                <a href="{{ route('backend.lab-tests.index') }}" class="btn btn-outline-secondary">
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

@push('after-styles')
<style>
    .input-group-text {
        background: transparent;
        border-right: none;
    }
    .input-group-text + .form-control {
        border-left: none;
    }
    .input-group-text + .form-control:focus {
        border-left: none;
    }
</style>
@endpush

@push('after-scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#category_id').select2({
                placeholder: "{{ __('radiology_lab.lbl_select_category') }}",
                allowClear: true,
            });
        }
    });
</script>
@endpush
