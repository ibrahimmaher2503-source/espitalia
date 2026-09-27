{{ html()->form('POST', route('landing_page_settings_updates'))->attribute('enctype', 'multipart/form-data')->attribute('data-toggle', 'validator')->id('frontend_setting')->open() }}
{{ html()->hidden('id', $landing_page->id)->class('form-control')->placeholder('id') }}
{{ html()->hidden('type', $tabpage)->class('form-control')->placeholder('id') }}

<div class="row">
    <div class="form-group">
        <div class="form-control d-flex align-items-center justify-content-between">
            <label for="enable_section_4" class="mb-0">Enable App section</label>
            <div class="form-check form-switch m-0">
                <input type="checkbox" class="form-check-input section_4" name="status" id="section_4"
                    data-type="section_4" {{ !empty($landing_page) && $landing_page->status == 1 ? 'checked' : '' }}>
                <label class="custom-control-label" for="section_4"></label>
            </div>
        </div>
    </div>
</div>
<div class="row" id='enable_section_4'>
    <div class="col-md-3 form-group">
        <label class="form-control-label">{{ trans('messages.title') }} <span class="badge bg-danger-subtle text-danger fw-normal">AR</span> <span class="text-danger">*</span></label>
        <input type="text" name="title_ar" id="title_ar" class="form-control" dir="rtl" placeholder="العنوان بالعربية">
    </div>
    <div class="col-md-3 form-group">
        <label class="form-control-label">{{ trans('messages.title') }} <span class="badge bg-primary-subtle text-primary fw-normal">EN</span> <span class="text-danger">*</span></label>
        <input type="text" name="title_en" id="title_en" class="form-control" placeholder="Title in English">
    </div>

    <div class="form-group col-md-6">
        <label for="avatar" class="col-sm-6 form-control-label">Main Image</label>
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-4">
                    <img src="{{ getSingleMedia($landing_page, 'main_image') }}" width="100" id="main_image_preview"
                        alt="main_image" class="image main_image main_image_preview">
                    @if ($landing_page && getSingleMedia($landing_page, 'main_image', null))
                        <a class="text-danger remove-file" href="" data--submit="confirm_form"
                            data--confirmation='true' data--ajax="true"
                            title='{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}'
                            data-title='{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}'
                            data-message='{{ __('messages.remove_file_msg') }}'>
                            <i class="ri-close-circle-line"></i>
                        </a>
                    @endif
                </div>
                <div class="col-sm-8 mt-sm-0 mt-2">
                    <div class="custom-file col-md-12">
                        {{ html()->file('main_image')->class('custom-file-input custom-file-input-sm detail')->id('main_image')->attribute('lang', 'en')->attribute('accept', 'image/*')->attribute('onchange', 'preview()') }}
                        @if ($landing_page && getSingleMedia($landing_page, 'main_image'))
                            {{-- <label class="custom-file-label upload-label">{{ $landing_page->getFirstMedia('main_image')->file_name }}</label> --}}
                        @else
                            <label
                                class="custom-file-label upload-label">{{ __('messages.choose_file', ['file' => __('messages.attachments')]) }}</label>
                        @endif
                    </div>
                    {{-- <img id="main_image" src="" width="150px" /> --}}
                </div>
            </div>

        </div>
    </div>
    <div class="form-group col-md-6">
        <label for="avatar" class="col-sm-6 form-control-label">Google Image</label>
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-4">
                    <img src="{{ getSingleMedia($landing_page, 'google_play') }}" width="100"
                        id="google_play_preview" alt="google_play" class="image google_play google_play_preview">
                    @if ($landing_page && getSingleMedia($landing_page, 'google_play'))
                        <a class="text-danger remove-file" href="" data--submit="confirm_form"
                            data--confirmation='true' data--ajax="true"
                            title='{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}'
                            data-title='{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}'
                            data-message='{{ __('messages.remove_file_msg') }}'>
                            <i class="ri-close-circle-line"></i>
                        </a>
                    @endif
                </div>
                <div class="col-sm-8 mt-sm-0 mt-2">
                    <div class="custom-file col-md-12">
                        {{ html()->file('google_play')->class('custom-file-input custom-file-input-sm detail')->id('google_play')->attribute('lang', 'en')->attribute('accept', 'image/*')->attribute('onchange', 'preview()') }}

                        @if ($landing_page && getSingleMedia($landing_page, 'google_play'))
                            {{-- <label class="custom-file-label upload-label">{{ $landing_page->getFirstMedia('google_play')->file_name }}</label> --}}
                        @else
                            <label
                                class="custom-file-label upload-label">{{ __('messages.choose_file', ['file' => __('messages.attachments')]) }}</label>
                        @endif
                    </div>
                    {{-- <img id="google_play" src="" width="150px" /> --}}
                </div>
            </div>

        </div>
    </div>
    <div class="form-group col-md-6">
        <label for="avatar" class="col-sm-6 form-control-label">App Store</label>
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-4">
                    <img src="{{ getSingleMedia($landing_page, 'app_store') }}" width="100" id="app_store_preview"
                        alt="app_store" class="image app_store app_store_preview">
                    @if ($landing_page && getSingleMedia($landing_page, 'app_store'))
                        <a class="text-danger remove-file" href="" data--submit="confirm_form"
                            data--confirmation='true' data--ajax="true"
                            title='{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}'
                            data-title='{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}'
                            data-message='{{ __('messages.remove_file_msg') }}'>
                            <i class="ri-close-circle-line"></i>
                        </a>
                    @endif
                </div>
                <div class="col-sm-8 mt-sm-0 mt-2">
                    <div class="custom-file col-md-12">
                        {{ html()->file('app_store')->class('custom-file-input custom-file-input-sm detail')->id('app_store')->attribute('lang', 'en')->attribute('accept', 'image/*')->attribute('onchange', 'preview()') }}
                        @if ($landing_page && getSingleMedia($landing_page, 'app_store'))
                            {{-- <label class="custom-file-label upload-label">{{ $landing_page->getFirstMedia('app_store')->file_name }}</label> --}}
                        @else
                            <label
                                class="custom-file-label upload-label">{{ __('messages.choose_file', ['file' => __('messages.attachments')]) }}</label>
                        @endif
                    </div>
                    {{-- <img id="app_store" src="" width="150px" /> --}}
                </div>
            </div>

        </div>
    </div>
    <div class="col-md-6 form-group">
        <label class="form-control-label">{{ __('messages.description') }} <span class="badge bg-danger-subtle text-danger fw-normal">AR</span></label>
        <textarea name="description_ar" id="description_ar" class="form-control" rows="2" dir="rtl" placeholder="الوصف بالعربية"></textarea>
    </div>
    <div class="col-md-6 form-group">
        <label class="form-control-label">{{ __('messages.description') }} <span class="badge bg-primary-subtle text-primary fw-normal">EN</span></label>
        <textarea name="description_en" id="description_en" class="form-control" rows="2" placeholder="Description in English"></textarea>
    </div>
</div>



{{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-md-end submit_section1') }}
{{ html()->form()->close() }}


<script>
    var enable_section_4 = $("input[name='status']").prop('checked');
    checkSection4(enable_section_4);

    $('#section_4').change(function() {
        value = $(this).prop('checked') == true ? true : false;
        checkSection4(value);

    });

    function checkSection4(value) {
        if (value == true) {
            $('#enable_section_4').removeClass('d-none');
            $('#title_ar').prop('required', true);
            $('#title_en').prop('required', true);
        } else {
            $('#enable_section_4').addClass('d-none');
            $('#title_ar').prop('required', false);
            $('#title_en').prop('required', false);
        }
    }




    var get_value = $('input[name="status"]:checked').data("type");
    getConfig(get_value)
    $('.section_4').change(function() {
        value = $(this).prop('checked') == true ? true : false;
        type = $(this).data("type");
        getConfig(type)

    });

    function getConfig(type) {
        var _token = $('meta[name="csrf-token"]').attr('content');
        var page = "{{ $tabpage }}";
        var getDataRoute = "{{ route('getLandingLayoutPageConfig') }}";
        $.ajax({
            url: getDataRoute,
            type: "POST",
            data: {
                type: type,
                page: page,
                _token: _token
            },
            success: function(response) {
                var obj = null;

                if (response && response.data) {
                    if (response.data.key == 'section_4') {
                        obj = JSON.parse(response.data.value);
                    }
                    if (obj !== null) {
                        $('#title_ar').val(obj.title_ar || obj.title || '');
                        $('#title_en').val(obj.title_en || '');
                        $('#description_ar').val(obj.description_ar || obj.description || '');
                        $('#description_en').val(obj.description_en || '');
                    }
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function getExtension(filename) {
        var parts = filename.split('.');
        return parts[parts.length - 1];
    }

    function isImage(filename) {
        var ext = getExtension(filename);
        switch (ext.toLowerCase()) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'ico':
                return true;
        }
        return false;
    }

    function readURL(input, className) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            var res = isImage(input.files[0].name);
            if (res == false) {
                var msg = 'Image should be png/PNG, jpg/JPG & jpeg/JPG.';
                Snackbar.show({
                    text: msg,
                    pos: 'bottom-right',
                    backgroundColor: '#d32f2f',
                    actionTextColor: '#fff'
                });
                $(input).val("");
                return false;
            }
            reader.onload = function(e) {
                $(document).find('img.' + className).attr('src', e.target.result);
                $(document).find("label." + className).text((input.files[0].name));
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
    $(document).ready(function() {
        $('.select2js').select2();
        $(document).on('change', '#main_image', function() {
            readURL(this, 'main_image');
        });
        $(document).on('change', '#google_play', function() {
            readURL(this, 'google_play');
        });
        $(document).on('change', '#app_store', function() {
            readURL(this, 'app_store');
        });
    })

    function preview() {
        var input = event.target;
        var previewImage;
        if (input.name === 'main_image') {
            previewImage = main_image;
        } else if (input.name === 'google_play') {
            previewImage = google_play;
        } else if (input.name === 'app_store') {
            previewImage = app_store;
        }
        previewImage.src = URL.createObjectURL(input.files[0]);
        var fileName = input.files[0].name;
        var label = $(input).closest('.custom-file').find('.custom-file-label');
        label.text(fileName);
    }
</script>
