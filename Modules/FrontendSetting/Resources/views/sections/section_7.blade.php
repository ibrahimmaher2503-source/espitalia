{{ html()->form('POST', route('landing_page_settings_updates'))->attribute('enctype', 'multipart/form-data')->attribute('data-toggle', 'validator')->open() }}

{{ html()->hidden('id', $landing_page->id)->placeholder('id')->class('form-control') }}
{{ html()->hidden('type', $tabpage)->placeholder('id')->class('form-control') }}

<div class="form-group">
    <div class="form-control d-flex align-items-center justify-content-between">
        <label class="mb-0" for="enable_section_7">Enable FAQ Section</label>
        <div class="form-check form-switch m-0">
            <input type="checkbox" class="form-check-input section_7" name="status" id="section_7" data-type="section_7"
                {{ !empty($landing_page) && $landing_page->status == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="section_7"></label>
        </div>
    </div>
</div>
<div class="form-section" id='enable_section_7'>
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
    <div class="row g-2 mb-3">
        <div class="col-md-6 form-group">
            <label class="form-control-label">{{ trans('messages.description') }} <span class="badge bg-danger-subtle text-danger fw-normal">AR</span></label>
            <input type="text" name="description_ar" id="description_ar" class="form-control" dir="rtl" placeholder="الوصف بالعربية">
        </div>
        <div class="col-md-6 form-group">
            <label class="form-control-label">{{ trans('messages.description') }} <span class="badge bg-primary-subtle text-primary fw-normal">EN</span></label>
            <input type="text" name="description_en" id="description_en" class="form-control" placeholder="Description in English">
        </div>
    </div>

</div>


{{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-md-end submit_section1') }}
{{ html()->form()->close() }}

<script>
    var enable_section_7 = $("input[name='status']").prop('checked');
    checkSection7(enable_section_7);

    $('#section_7').change(function() {
        value = $(this).prop('checked') == true ? true : false;
        checkSection7(value);

    });

    function checkSection7(value) {
        if (value == true) {
            $('#enable_section_7').removeClass('d-none');
            $('#title_ar').prop('required', true);
            $('#title_en').prop('required', true);
        } else {
            $('#enable_section_7').addClass('d-none');
            $('#title_ar').prop('required', false);
            $('#title_en').prop('required', false);
        }
    }

    var get_value = $('input[name="status"]:checked').data("type");
    getConfig(get_value)
    $('.section_7').change(function() {
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
                    if (response.data.key == 'section_7') {
                        obj = JSON.parse(response.data.value);
                    }
                    if (obj !== null) {
                        $('#title_ar').val(obj.title_ar || obj.title || '');
                        $('#title_en').val(obj.title_en || '');
                        $('#subtitle_ar').val(obj.subtitle_ar || obj.subtitle || '');
                        $('#subtitle_en').val(obj.subtitle_en || '');
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
</script>
