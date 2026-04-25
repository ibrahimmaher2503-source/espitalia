@extends('backend.layouts.app')

@section('content')

    <x-backend.section-header>
        <x-slot name="toolbar">
            <div class="d-flex justify-content-end">
                <a href="{{ route('backend.blog.index') }}" class="btn btn-primary" data-type="ajax"
                    data-bs-toggle="tooltip">
                    {{ __('appointment.back') }}
                </a>
            </div>
        </x-slot>
    </x-backend.section-header>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', route('backend.blog.store'))->attributes(['enctype' => 'multipart/form-data', 'data-toggle' => 'validator', 'id' => 'blog'])->open() }}
                        {{ html()->hidden('id', $blogdata->id ?? null) }}

                        <div class="row  align-items-center">
                            <!-- Title AR -->
                            <div class="form-group col-md-4">
                                <div class="form-group">
                                    {{ html()->label(__('blog.lbl_title_ar') . ' <span class="text-danger">*</span>', 'title_ar')->class('form-label') }}
                                    {{ html()->text('title_ar', $blogdata->title_ar ?? old('title_ar'))
                                        ->placeholder(__('blog.lbl_title_ar'))
                                        ->class('form-control')
                                        ->attribute('dir', 'rtl') }}
                                    @error('title_ar')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Title EN -->
                            <div class="form-group col-md-4">
                                <div class="form-group">
                                    {{ html()->label(__('blog.lbl_title_en') . ' <span class="text-danger">*</span>', 'title_en')->class('form-label') }}
                                    {{ html()->text('title_en', $blogdata->title_en ?? old('title_en'))
                                        ->placeholder(__('blog.lbl_title_en'))
                                        ->class('form-control') }}
                                    @error('title_en')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Author (Vendor List) -->
                            @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.author')]), 'author_id')->class('form-label') }}
                                    <br />
                                    {{ html()->select('author_id', $vendors->pluck('full_name', 'id')->toArray(), $blogdata->author_id ?? null)
                                        ->class('form-control select2js')
                                        ->id('author_id')
                                        ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.author')]))
                                    }}
                                </div>
                            </div>
                        @endif
                        
                        <!-- Tags -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="tags">{{ __('messages.tags') }}</label>
                                <select class="form-control select2-tag" name="tags[]" multiple>
                                    @if(isset($blogdata) && $blogdata->tags != null)
                                        @foreach(json_decode($blogdata->tags) as $tags)
                                            <option value="{{ $tags }}" selected>{{ $tags }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        
                        <!-- Image -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="blog_attachment">{{ __('messages.image') }}</label>
                                <div class="custom-file">
                                    <input type="file" name="blog_attachment[]" class="custom-file-input form-control" multiple>
                                    @if(isset($blogdata) && $blogdata->getMedia('blog_attachment')->isNotEmpty())
                                        <div class="mt-2">
                                            <ul class="list-unstyled d-flex flex-wrap">
                                                @foreach($blogdata->getMedia('blog_attachment') as $media)
                                                    <li style="position: relative; margin-right: 10px; margin-bottom: 10px;">
                                                        <!-- Image display -->
                                                        <img src="{{ $media->getUrl() }}" alt="Blog Image" 
                                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;" />
                            
                                                        <a href="{{ route('backend.blog.remove-media', ['id' => $blogdata->id, 'media_id' => $media->id]) }}" 
                                                            class="text-danger" 
                                                            style="position: absolute; top: 0; right: 0; background:  color: white; padding: 5px; border-radius: 50%; font-size: 14px;">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                         <!-- Status -->
                         <div class="col-md-4">
                             <div class="form-group">
                                <!-- {{ html()->label(trans('messages.status') . ' <span class="text-danger">*</span>', 'status')->class('form-label') }} -->
                                <div class="d-flex align-items-center gap-3">
                                    {{ html()->label(__('messages.status'), 'status')->class('form-label mb-0') }}
                                    <div class="form-check form-switch">
                                        {{ html()->hidden('status', 0) }} 
                                        {{ html()->checkbox('status', old('status', $blogdata->status ?? 1))  
                                            ->class('form-check-input')
                                            ->id('status')
                                            ->value(1) }} 
                                    </div>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                            <!-- Description AR -->
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ html()->label(__('blog.lbl_description_ar'), 'description_ar')->class('form-control-label') }}
                                {{ html()->textarea('description_ar', $blogdata->description_ar ?? old('description_ar'))
                                    ->class('form-control tinymce-template')
                                    ->attribute('dir', 'rtl')
                                    ->placeholder(__('blog.lbl_description_ar')) }}
                                @error('description_ar')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                            <!-- Description EN -->
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ html()->label(__('blog.lbl_description_en'), 'description_en')->class('form-control-label') }}
                                {{ html()->textarea('description_en', $blogdata->description_en ?? old('description_en'))
                                    ->class('form-control tinymce-template')
                                    ->placeholder(__('blog.lbl_description_en')) }}
                                @error('description_en')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        </div>

                        {{ html()->submit(trans('messages.save'))->class('btn btn-md btn-primary float-end') }}
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('bottom_script')
    @push('after-scripts')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>

<script>
    $(document).ready(function() {
            $('.select2-tag').select2({
                tags: true,
                createTag: function(params) {
                    if (params.term.length > 2) {
                        return {
                            id: params.term,
                            text: params.term,
                            newTag: true
                        };
                    }
                    return null;
                }
            });
        });
    $(document).ready(function() {
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
    });
</script>
@endpush
@endsection
