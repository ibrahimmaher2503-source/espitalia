{{--
  @include('backend.components.translatable-input', [
    'field' => 'name',
    'label' => __('radiology_lab.lbl_test_name'),
    'required' => true,
    'data' => $data ?? null,
  ])
--}}
@php
    $fieldName = $field;
    $uniqueId = $fieldName . '_' . uniqid();
    $existingEn = '';
    $existingAr = '';
    if (isset($data) && $data && method_exists($data, 'getTranslation')) {
        $existingEn = old("{$fieldName}.en", $data->getTranslation($fieldName, 'en', false) ?? '');
        $existingAr = old("{$fieldName}.ar", $data->getTranslation($fieldName, 'ar', false) ?? '');
    } else {
        $existingEn = old("{$fieldName}.en", '');
        $existingAr = old("{$fieldName}.ar", '');
    }
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $uniqueId }}_en">
        {{ $label }} @if($required ?? false)<span class="text-danger">*</span>@endif
    </label>
    <ul class="nav nav-tabs mb-1" id="{{ $uniqueId }}-tabs" style="font-size: 0.75rem;">
        <li class="nav-item">
            <button class="nav-link py-1 px-2 active" type="button" data-bs-toggle="tab" data-bs-target="#{{ $uniqueId }}-en-pane">EN</button>
        </li>
        <li class="nav-item">
            <button class="nav-link py-1 px-2" type="button" data-bs-toggle="tab" data-bs-target="#{{ $uniqueId }}-ar-pane">AR</button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="{{ $uniqueId }}-en-pane">
            <input type="text"
                   class="form-control @error("{$fieldName}.en") is-invalid @enderror"
                   name="{{ $fieldName }}[en]"
                   id="{{ $uniqueId }}_en"
                   value="{{ $existingEn }}"
                   dir="ltr"
                   @if($required ?? false) required @endif>
            @error("{$fieldName}.en") <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="tab-pane fade" id="{{ $uniqueId }}-ar-pane">
            <input type="text"
                   class="form-control @error("{$fieldName}.ar") is-invalid @enderror"
                   name="{{ $fieldName }}[ar]"
                   id="{{ $uniqueId }}_ar"
                   value="{{ $existingAr }}"
                   dir="rtl">
            @error("{$fieldName}.ar") <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
