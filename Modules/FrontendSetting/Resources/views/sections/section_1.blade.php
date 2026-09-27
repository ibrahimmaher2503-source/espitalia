<div class="section-content">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('landing_page_settings_updates') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <input type="hidden" name="id" value="{{ $landing_page->id }}" class="form-control"
                        placeholder="id">
                    <input type="hidden" name="type" value="section_1" class="form-control" placeholder="section_1">

                    <div class="form-group">
                        <div class="form-control d-flex align-items-center justify-content-between">
                            <label class="mb-0" for="enable_section_1">Enable Home Slider</label>
                            <div class="form-check form-switch m-0">
                                <input type="checkbox" class="form-check-input section_1" name="status" id="section_1"
                                    data-type="section_1"
                                    {{ !empty($landing_page) && $landing_page->status == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="section_1"></label>
                            </div>
                        </div>
                    </div>

                    <div class="row" id='enable_section_1'>
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

                        <div class="form-group">
                            <div class="form-control d-flex align-items-center justify-content-between">
                                <label for="enable_search" class="mb-0">Enable Search</label>
                                <div class="form-check form-switch m-0">
                                    <input type="checkbox" class="form-check-input" name="enable_search"
                                        id="enable_search"> <label class="form-check-label" for="enable_search"></label>
                                </div>
                            </div>
                        </div>


                        <div class="form-group mt-3">
                            <label for="instant_link" class="form-label">Instant Link</label>
                            <select class="form-select select2" id="instant_link" name="instant_link[]" multiple>
                                <option value="doctors"
                                    {{ in_array('doctors', old('instant_link', [])) ? 'selected' : '' }}>Doctor</option>
                                <option value="services"
                                    {{ in_array('services', old('instant_link', [])) ? 'selected' : '' }}>Service
                                </option>
                                <option value="clinics"
                                    {{ in_array('clinics', old('instant_link', [])) ? 'selected' : '' }}>Clinic</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="form-control d-flex align-items-center justify-content-between">
                                <label for="enable_quick_booking" class="mb-0">Enable Quick Booking</label>
                                <div class="form-check form-switch m-0">
                                    <input type="checkbox" class="form-check-input" name="enable_quick_booking"
                                        id="enable_quick_booking">
                                    <label class="custom-control-label" for="enable_quick_booking"></label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#instant_link').select2();
    });

    var enable_section_1 = $("input[name='status']").prop('checked');
    checkSection1(enable_section_1);

    $('#section_1').change(function() {
        value = $(this).prop('checked') == true ? true : false;
        checkSection1(value);
    });

    function checkSection1(value) {
        if (value == true) {
            $('#enable_section_1').removeClass('d-none');
            $('#title_ar').prop('required', true);
            $('#title_en').prop('required', true);
        } else {
            $('#enable_section_1').addClass('d-none');
            $('#title_ar').prop('required', false);
            $('#title_en').prop('required', false);
        }
    }

    var get_value = $('input[name="status"]:checked').data("type");
    getConfig(get_value)
    $('.section_1').change(function() {
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
                    if (response.data.key == 'section_1') {
                        obj = JSON.parse(response.data.value);
                    }
                    if (obj !== null) {
                        $('#title_ar').val(obj.title_ar || obj.title || '');
                        $('#title_en').val(obj.title_en || '');
                        $('#enable_search').prop('checked', obj.enable_search == 'on');
                        $('#enable_quick_booking').prop('checked', obj.enable_quick_booking == 'on');
                        if (obj.instant_link && Array.isArray(obj.instant_link)) {
                            $('#instant_link').val(obj.instant_link).trigger('change');
                        }
                        $('#instant_link').select2();
                        $('#instant_link').trigger('change');
                    }
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }
</script>
