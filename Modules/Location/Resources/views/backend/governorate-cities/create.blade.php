@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            {{ html()->form('POST', route('backend.governorate-cities.store'))->id('city-form')->open() }}
            @if(isset($data) && $data->id)
                <input type="hidden" name="id" value="{{ $data->id }}">
            @endif

            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-buildings me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.governorate-cities.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="governorate_id">{{ __('location.lbl_governorate') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('governorate_id') is-invalid @enderror" name="governorate_id" id="governorate_id" style="width:100%" required>
                                <option value="">{{ __('location.lbl_select_governorate') }}</option>
                                @foreach($governorates as $governorate)
                                    <option value="{{ $governorate->id }}" {{ old('governorate_id', $data->governorate_id ?? '') == $governorate->id ? 'selected' : '' }}>
                                        {{ $governorate->name_ar }} - {{ $governorate->name_en }}
                                    </option>
                                @endforeach
                            </select>
                            @error('governorate_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name_ar">{{ __('location.lbl_name_ar') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" name="name_ar" id="name_ar" dir="rtl" value="{{ old('name_ar', $data->name_ar ?? '') }}" required>
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name_en">{{ __('location.lbl_name_en') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name_en') is-invalid @enderror" name="name_en" id="name_en" dir="ltr" value="{{ old('name_en', $data->name_en ?? '') }}" required>
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('backend.governorate-cities.index') }}" class="btn btn-outline-secondary">
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
