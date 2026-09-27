{{ html()->form('POST', route('landing_page_settings_updates'))->attribute('enctype', 'multipart/form-data')->attribute('data-toggle', 'validator')->open() }}

{{ html()->hidden('id', $landing_page->id)->placeholder('id')->class('form-control') }}
{{ html()->hidden('type', $tabpage)->placeholder('id')->class('form-control') }}

<div class="form-group">
    <div class="form-control d-flex align-items-center justify-content-between">
        <label for="enable_section_2" class="mb-0">Enable Popular Categories</label>
        <div class="form-check form-switch m-0">
            <input type="checkbox" class="form-check-input section_2" name="status" id="section_2" data-type="section_2"
                {{ !empty($landing_page) && $landing_page->status == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="section_2"></label>
        </div>
    </div>
</div>
<div id='enable_section_2'>
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

    <div class="form-group" id='enable_select_category'>
        {{ html()->label(__('messages.select_category_name', ['select' => __('messages.category')]) . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
        <br />
        {{ html()->select('category_id[]', [], old('category_id'))->class('select2js form-control category_id')->id('category_id')->attribute('data-placeholder', __('messages.select_category_name', ['select' => __('messages.category')]))->attribute('data-ajax--url', route('ajax-list', ['type' => 'category', 'is_featured' => 1]))->multiple() }}
    </div>

</div>
{{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-md-end submit_section1') }}
{{ html()->form()->close() }}


<script>
    var enable_section_2 = $("input[name='status']").prop('checked');
    checkSection2(enable_section_2);

    $('#section_2').change(function() {
        value = $(this).prop('checked') == true ? true : false;
        checkSection2(value);
    });

    function checkSection2(value) {
        if (value == true) {
            $('#enable_section_2').removeClass('d-none');
            $('#title_ar').prop('required', true);
            $('#title_en').prop('required', true);
            $('#category_id').prop('required', true).trigger('change.select2');
        } else {
            $('#enable_section_2').addClass('d-none');
            $('#title_ar').prop('required', false);
            $('#title_en').prop('required', false);
            $('#category_id').prop('required', false).trigger('change.select2');
        }
    }

    ///// open select popular category ///////////




    $(document).ready(function() {
        $('.select2js').select2();

        $('#category_id').on('change', function() {
            var selectedOptions = $(this).val();
            if (selectedOptions && selectedOptions.length > 8) {
                selectedOptions.pop();
                $(this).val(selectedOptions).trigger('change.select2');
            }
        });
    });


    var get_value = $('input[name="status"]:checked').data("type");
    getConfig(get_value)
    $('.section_2').change(function() {
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
                var category_ids = null;

                if (response && response.data) {
                    if (response.data.key == 'section_2') {
                        obj = JSON.parse(response.data.value);
                    }
                    if (obj !== null) {
                        category_ids = obj.category_id;
                        $('#title_ar').val(obj.title_ar || obj.title || '');
                        $('#title_en').val(obj.title_en || '');
                        $('#subtitle_ar').val(obj.subtitle_ar || obj.subtitle || '');
                        $('#subtitle_en').val(obj.subtitle_en || '');
                    }
                    if (category_ids && category_ids.length > 0) {
                        loadCategory(category_ids);
                    }
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function loadCategory(category_ids) {
        var category_route = "{{ route('ajax-list', ['type' => 'category']) }}";
        if (category_ids && category_ids.length > 0) {
            $.ajax({
                url: category_route,
                data: {
                    is_featured: 1,
                    ids: category_ids
                },
                success: function(result) {
                    $('#category_id').select2({
                        width: '100%',
                        placeholder: "{{ trans('messages.select_name', ['select' => trans('messages.category')]) }}",
                        data: result.results
                    });
                    $('#category_id').val(category_ids).trigger('change');
                }
            });
        } else {
            $('#category_id').empty().trigger('change');
        }
    }
</script>
