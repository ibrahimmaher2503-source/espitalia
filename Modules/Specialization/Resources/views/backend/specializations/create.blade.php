@extends('backend.layouts.app')

@section('title') {{ $module_title }} @endsection

@section('content')

<x-backend.section-header>
    <x-slot name="toolbar">
        <div class="d-flex justify-content-end">
            <a href="{{ route('backend.specializations.index') }}" class="btn btn-primary" data-type="ajax" data-bs-toggle="tooltip">
                {{ __('appointment.back') }}
            </a>
        </div>
    </x-slot>
</x-backend.section-header>

{{ html()->form('POST', isset($data) ? route('backend.specializations.update', $data->id) : route('backend.specializations.store'))
    ->attribute('enctype', 'multipart/form-data')
    ->attribute('data-toggle', 'validator')
    ->attribute('id', 'form-submit')
    ->class('requires-validation')
    ->attribute('novalidate', 'novalidate')
    ->open() }}
@csrf
@if(isset($data))
    @method('PUT')
@endif

    <div class="card">
        <div class="card-body">
            <div class="row gy-3">
                <!-- Name (Arabic) -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('specialization.lbl_name_ar') . ' <span class="text-danger">*</span>', 'name_ar')->class('form-label') }}
                        {{ html()->text('name_ar')
                            ->attribute('value', old('name_ar', $data->name_ar ?? ''))
                            ->placeholder(__('specialization.lbl_name_ar'))
                            ->class('form-control')
                            ->attribute('dir', 'rtl')
                            ->required() }}
                        @error('name_ar')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Name (English) -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('specialization.lbl_name_en') . ' <span class="text-danger">*</span>', 'name_en')->class('form-label') }}
                        {{ html()->text('name_en')
                            ->attribute('value', old('name_en', $data->name_en ?? ''))
                            ->placeholder(__('specialization.lbl_name_en'))
                            ->class('form-control')
                            ->required() }}
                        @error('name_en')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Description (Arabic) -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('specialization.lbl_description_ar'), 'description_ar')->class('form-label') }}
                        {{ html()->textarea('description_ar')
                            ->attribute('value', old('description_ar', $data->description_ar ?? ''))
                            ->placeholder(__('specialization.lbl_description_ar'))
                            ->class('form-control')
                            ->attribute('dir', 'rtl')
                            ->attribute('rows', '3') }}
                        @error('description_ar')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Description (English) -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('specialization.lbl_description_en'), 'description_en')->class('form-label') }}
                        {{ html()->textarea('description_en')
                            ->attribute('value', old('description_en', $data->description_en ?? ''))
                            ->placeholder(__('specialization.lbl_description_en'))
                            ->class('form-control')
                            ->attribute('rows', '3') }}
                        @error('description_en')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Parent Specialization -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('specialization.lbl_parent'), 'parent_id')->class('form-label') }}
                        <select name="parent_id" id="parent_id" class="form-control select2" style="width:100%">
                            <option value="">{{ __('specialization.lbl_no_parent') }}</option>
                            @foreach($specializations as $spec)
                                <option value="{{ $spec->id }}" {{ old('parent_id', $data->parent_id ?? '') == $spec->id ? 'selected' : '' }}>
                                    {{ $spec->name_en }} — {{ $spec->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Icon Class -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('specialization.lbl_icon'), 'icon')->class('form-label') }}
                        {{ html()->text('icon')
                            ->attribute('value', old('icon', $data->icon ?? ''))
                            ->placeholder(__('specialization.lbl_icon_hint'))
                            ->class('form-control') }}
                        @error('icon')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Icon Image (for mobile app) -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('specialization.lbl_icon_image'), 'icon_image')->class('form-label') }}
                        <input type="file" name="icon_image" id="icon_image" class="form-control" accept="image/png,image/svg+xml,image/jpeg">
                        <small class="text-muted">{{ __('specialization.lbl_icon_image_hint') }}</small>
                        @if(isset($data) && $data->icon_image)
                            <div class="mt-2">
                                <img src="{{ url('storage/'.$data->icon_image) }}" alt="icon" style="max-height: 48px;">
                            </div>
                        @endif
                        @error('icon_image')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="col-md-6 form-group">
                    <div class="form-group">
                        <div class="d-flex align-items-center gap-3">
                            {{ html()->label(__('specialization.lbl_status'), 'status')->class('form-label') }}
                            <div class="form-check form-switch">
                                {{ html()->hidden('status', 0) }}
                                {{ html()->checkbox('status', old('status', isset($data) ? $data->status : true), 1)
                                    ->class('form-check-input')
                                    ->id('status') }}
                            </div>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Show on Home -->
                <div class="col-md-6 form-group">
                    <div class="form-group">
                        <div class="d-flex align-items-center gap-3">
                            {{ html()->label(__('specialization.lbl_show_on_home'), 'show_on_home')->class('form-label') }}
                            <div class="form-check form-switch">
                                {{ html()->hidden('show_on_home', 0) }}
                                {{ html()->checkbox('show_on_home', old('show_on_home', isset($data) ? $data->show_on_home : false), 1)
                                    ->class('form-check-input')
                                    ->id('show_on_home') }}
                            </div>
                            @error('show_on_home')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Display Order -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('specialization.lbl_display_order'), 'display_order')->class('form-label') }}
                        {{ html()->number('display_order')
                            ->attribute('value', old('display_order', $data->display_order ?? 0))
                            ->placeholder(__('specialization.lbl_display_order_hint'))
                            ->class('form-control')
                            ->attribute('min', '0') }}
                        <small class="text-muted">{{ __('specialization.lbl_display_order_hint') }}</small>
                        @error('display_order')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid d-sm-flex justify-content-sm-end gap-3 mb-5">
        {{ html()->submit(trans('messages.save'))->class('btn btn-md btn-primary float-right')->id('submit-button') }}
    </div>

{{ html()->form()->close() }}
@endsection

@push('after-scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endpush
