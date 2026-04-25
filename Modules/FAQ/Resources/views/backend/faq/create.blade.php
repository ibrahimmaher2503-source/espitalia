@extends('backend.layouts.app')

@section('content')
 
<x-backend.section-header>
    <x-slot name="toolbar">
        <div class="d-flex justify-content-end">
            <a href="{{ route('backend.faqs.index') }}" class="btn btn-primary" data-type="ajax"
                data-bs-toggle="tooltip">
                {{ __('appointment.back') }}
            </a>
        </div>
    </x-slot>
</x-backend.section-header>

{{ html()->form('POST', route('backend.faqs.store'))
        ->attribute('enctype', 'multipart/form-data')
        ->attribute('data-toggle', 'validator')
        ->attribute('id', 'form-submit')  // Add the id attribute here
        ->class('requires-validation')  // Add the requires-validation class
        ->attribute('novalidate', 'novalidate')  // Disable default browser validation
        ->open() }}
@csrf
    <div class="card">
        <div class="card-body">
            <div class="row gy-3">
                <!-- Question Arabic Field -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('faq.lbl_question_ar'), 'question_ar')->class('form-label') }}
                        {{ html()->text('question_ar')
                                ->attribute('value', old('question_ar'))
                                ->attribute('dir', 'rtl')
                                ->placeholder(__('faq.lbl_question_ar'))
                                ->class('form-control') }}
                        @error('question_ar')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Question English Field -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('faq.lbl_question_en'), 'question_en')->class('form-label') }}
                        {{ html()->text('question_en')
                                ->attribute('value', old('question_en'))
                                ->placeholder(__('faq.lbl_question_en'))
                                ->class('form-control') }}
                        @error('question_en')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Answer Arabic Field -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('faq.lbl_answer_ar'), 'answer_ar')->class('form-label') }}
                        {{ html()->textarea('answer_ar')
                                ->attribute('dir', 'rtl')
                                ->placeholder(__('faq.lbl_answer_ar'))
                                ->class('form-control tinymce-template') }}
                        @error('answer_ar')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Answer English Field -->
                <div class="col-md-6">
                    <div class="form-group">
                        {{ html()->label(__('faq.lbl_answer_en'), 'answer_en')->class('form-label') }}
                        {{ html()->textarea('answer_en')
                                ->placeholder(__('faq.lbl_answer_en'))
                                ->class('form-control tinymce-template') }}
                        @error('answer_en')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>


                <!-- Status Field -->
                <div class="col-md-6 form-group">
                    <div class="form-group">
                        <div class="d-flex align-items-center gap-3">
                            {{ html()->label(__('messages.lbl_status'), 'status')->class('form-label') }}
                            <!-- {{ html()->label(__('messages.active'), 'status')->class('form-label mb-0 text-body') }} -->
                            <div class="form-check form-switch">
                                {{ html()->hidden('status', 1) }}
                                {{ html()->checkbox('status', old('status', true))
                                        ->class('form-check-input')
                                        ->id('status') }}
                            </div>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
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
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>

<script>
    $(document).ready(function() {
        // Initialize TinyMCE editor for answer field
        tinymce.init({
            selector: '.tinymce-template',
            height: 300,
            menubar: false,
            plugins: 'autoresize link image code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | link image',
            setup: function(editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });

        // Initialize Select2 for any select fields if needed
        $('.select2').select2();
    });
</script>
@endpush
