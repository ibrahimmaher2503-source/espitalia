{{ html()->form('POST', route('landing_page_settings_updates'))->attribute('enctype', 'multipart/form-data')->attribute('data-toggle', 'validator')->open() }}

{{ html()->hidden('id', $landing_page->id)->placeholder('id')->class('form-control') }}
{{ html()->hidden('type', $tabpage)->placeholder('id')->class('form-control') }}

<div class="form-group">
    <div class="form-control d-flex align-items-center justify-content-between">
        <label class="mb-0" for="enable_section_9">Enable Blog Section</label>
        <div class="form-check form-switch m-0">
            <input type="checkbox" class="form-check-input section_9" name="status" id="section_9" data-type="section_9"
                {{ !empty($landing_page) && $landing_page->status == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="section_9"></label>
        </div>
    </div>
</div>
<div class="form-section" id='enable_section_9'>
    <div class="row g-2 mb-3">
        <div class="col-md-6 form-group">
            <label class="form-control-label">{{ trans('messages.title') }} <span class="badge bg-danger-subtle text-danger fw-normal">AR</span> <span class="text-danger">*</span></label>
            <input type="text" name="title_ar" id="title_ar" class="form-control" dir="rtl" placeholder="العنوان بالعربية">
        </div>
        <div class="col-md-6 form-group">
            <label class="form-control-label">{{ trans('messages.title') }} <span class="badge bg-primary-subtle text-primary fw-normal">EN</span> <span class="text-danger">*</span></label>
            <input type="text" name="title_en" id="title_en" class="form-control" placeholder="Title in English">
        </div>
    </div>
    <div class="row g-2 mb-3">
        <div class="col-md-6 form-group">
            <label class="form-control-label">{{ trans('messages.subtitle') }} <span class="badge bg-danger-subtle text-danger fw-normal">AR</span></label>
            <input type="text" name="subtitle_ar" id="subtitle_ar" class="form-control" dir="rtl" placeholder="العنوان الفرعي بالعربية">
        </div>
        <div class="col-md-6 form-group">
            <label class="form-control-label">{{ trans('messages.subtitle') }} <span class="badge bg-primary-subtle text-primary fw-normal">EN</span></label>
            <input type="text" name="subtitle_en" id="subtitle_en" class="form-control" placeholder="Subtitle in English">
        </div>
    </div>

    <div class="form-group" id='enable_select_blog'>
        {{ html()->label(__('messages.select_blog_name', ['select' => __('messages.blog')]) . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
        <br />
        {{ html()->select('blog_id[]', [], old('blog_id'))->class('select2js form-control blog_id')->id('blog_id')->attribute('data-placeholder', __('messages.select_blog_name', ['select' => __('messages.blog')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'blog', 'is_featured' => 1]))->multiple() }}
    </div>

</div>


{{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-md-end submit_section1') }}
{{ html()->form()->close() }}

<script>
    var enable_section_9 = $("input[name='status']").prop('checked');
    checkSection7(enable_section_9);

    $('#section_9').change(function() {
        value = $(this).prop('checked') == true ? true : false;
        checkSection7(value);

    });

    function checkSection7(value) {
        if (value == true) {
            $('#enable_section_9').removeClass('d-none');
            $('#title_ar').prop('required', true);
            $('#title_en').prop('required', true);
            $('#blog_id').prop('required', true).trigger('change.select2');
        } else {
            $('#enable_section_9').addClass('d-none');
            $('#title_ar').prop('required', false);
            $('#title_en').prop('required', false);
            $('#blog_id').prop('required', false).trigger('change.select2');
        }
    }

    var get_value = $('input[name="status"]:checked').data("type");
    getConfig(get_value)
    $('.section_9').change(function() {
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
                var blog_ids = null;

                if (response && response.data) {
                    if (response.data.key == 'section_9') {
                        obj = JSON.parse(response.data.value);
                    }
                    if (obj !== null) {
                        blog_ids = obj.blog_id;
                        $('#title_ar').val(obj.title_ar || obj.title || '');
                        $('#title_en').val(obj.title_en || '');
                        $('#subtitle_ar').val(obj.subtitle_ar || obj.subtitle || '');
                        $('#subtitle_en').val(obj.subtitle_en || '');
                        loadBlog(blog_ids);
                    }
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function loadBlog(blog_ids) {
        console.log(blog_ids);
        var blog_route = "{{ route('ajax-list', ['type' => 'blog']) }}";
        // if (blog_ids && blog_ids.length > 0) {
        $.ajax({
            url: blog_route,
            data: {
                is_featured: 1,
                ids: blog_ids
            },
            success: function(result) {
                $('#blog_id').select2({
                    width: '100%',
                    placeholder: "{{ trans('messages.select_name', ['select' => trans('messages.blog')]) }}",
                    data: result.results
                });
                $('#blog_id').val(blog_ids).trigger('change');
            }
        });
        // } else {
        //     $('#blog_id').empty().trigger('change');
        // }
    }
</script>
