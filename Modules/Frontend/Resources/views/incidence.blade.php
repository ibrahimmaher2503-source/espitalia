@extends('frontend::layouts.master')

@section('title', 'Incidence Report')

@section('content')
@include('frontend::components.section.breadcrumb')
    <div class="pp-page list-page section-spacing px-0">
        <div class="page-title" id="page_title">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        {{-- Tab navigation --}}
                        <ul class="pp-nav-pills d-flex flex-wrap gap-2 mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="pp-nav-link active" data-bs-toggle="pill" data-tab="all" href="#all-incidences" role="tab">
                                    <i class="ph ph-squares-four" aria-hidden="true"></i>
                                    <span>{{ __('frontend.all') }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="pp-nav-link" data-bs-toggle="pill" data-tab="open" href="#all-incidences" role="tab">
                                    <i class="ph ph-calendar-plus" aria-hidden="true"></i>
                                    <span>{{ __('messages.lbl_open') }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="pp-nav-link" data-bs-toggle="pill" data-tab="close" href="#all-incidences" role="tab">
                                    <i class="ph ph-check-circle" aria-hidden="true"></i>
                                    <span>{{ __('messages.closed') }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="pp-nav-link" data-bs-toggle="pill" data-tab="reject" href="#all-incidences" role="tab">
                                    <i class="ph ph-x-circle" aria-hidden="true"></i>
                                    <span>{{ __('messages.lbl_rejected') }}</span>
                                </a>
                            </li>
                        </ul>
                        <!-- End Nav Pills -->
                        <div class="tab-content">
                            <div class="tab-pane active p-0 all-incidences" id="all-incidences">
                                <ul class="list-inline m-0">
                                    <div id="shimmer-loader" class="d-flex gap-3 flex-wrap p-4 shimmer-loader">
                                        @for ($i = 0; $i < 8; $i++)
                                            @include('frontend::components.card.shimmer_incidence_card')
                                        @endfor
                                    </div>
                                    <table id="datatable" class="table table-responsive custom-card-table">
                                    </table>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mt-lg-5 mt-5">
                        <div class="pp-card">
                            <div class="pp-card-header">
                                <h2 class="pp-card-title">
                                    <i class="ph ph-warning" aria-hidden="true"></i>
                                    {{ __('messages.we_are_here_to_support') }}
                                </h2>
                            </div>
                            <div class="pp-card-body">
                            <p style="font-size:0.82rem;color:var(--pp-muted);margin-bottom:1.25rem;font-family:var(--pp-font)">{{ __('messages.our_passion_is_to_provide') }}</p>

                            <form id="incidence-form" method="POST" action="{{ route('incidence.store') }}">
                                @csrf
                                <div class="form">
                                    <div class="row">
                                        <div class="col-lg-12 mb-3">
                                            <div class="pp-input-group">
                                                <input type="text" name="title" class="pp-input" placeholder="{{ __('messages.lbl_title') }}" value="">
                                                <span class="pp-input-icon"><i class="ph ph-text-t" aria-hidden="true"></i></span>
                                            </div>
                                            <div class="pp-field-error d-none" id="error-title"></div>
                                        </div>
                                        <div class="col-lg-12 mb-3">
                                            <div class="pp-input-group">
                                                <input type="email" name="email" class="pp-input" placeholder="{{ __('messages.lbl_emailid') }}" value="">
                                                <span class="pp-input-icon"><i class="ph ph-envelope-simple" aria-hidden="true"></i></span>
                                            </div>
                                            <div class="pp-field-error d-none" id="error-email"></div>
                                        </div>
                                        <div class="col-lg-12 mb-3">
                                            <div class="pp-input-group">
                                                <input type="text" id="phone" name="phone" class="pp-input" placeholder="{{ __('messages.lbl_phone_number') }}" value="">
                                                <span class="pp-input-icon"><i class="ph ph-phone" aria-hidden="true"></i></span>
                                            </div>
                                            <div class="pp-field-error d-none" id="error-phone"></div>
                                        </div>
                                        <div class="col-lg-12 mb-3">
                                            <textarea name="description" id="description" class="pp-input" style="height:auto" placeholder="{{ __('messages.lbl_description') }}" maxlength="500" rows="3"></textarea>
                                            <div class="pp-field-error d-none" id="error-description"></div>
                                            <div class="text-end mt-1">
                                                <small style="font-size:0.72rem;color:var(--pp-muted);font-family:var(--pp-font)" id="description-count">0 / 500</small>
                                            </div>
                                        </div>

                                        <div class="col-lg-12 mb-3">
                                            <div class="pp-input-group">
                                                <input type="file" name="file_url" class="pp-input" accept="image/*" id="image-input">
                                                <span class="pp-input-icon"><i class="ph ph-image" aria-hidden="true"></i></span>
                                            </div>
                                            <div id="image-preview-container" class="mt-2" style="display:none;">
                                                <div class="position-relative d-inline-block">
                                                    <img id="image-preview" src="" class="avatar-100" alt="Image Preview"/>
                                                    <button type="button"
                                                            class="pp-icon-btn pp-icon-btn-danger position-absolute"
                                                            style="top:0;right:0;padding:0.2rem 0.35rem"
                                                            id="remove-image-preview"
                                                            aria-label="{{ __('messages.remove') ?? 'Remove' }}">
                                                        <i class="ph ph-x" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="pp-field-error d-none" id="error-image"></div>
                                        </div>

                                        <div class="d-flex justify-content-md-end">
                                            <button type="submit" id="submit-btn" class="pp-btn pp-btn-teal">
                                                <i class="ph ph-paper-plane-right" aria-hidden="true"></i>
                                                <span id="submit-text">{{ __('messages.lbl_submit') }}</span>
                                                <span id="submit-spinner" class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                        <div id="snackbar" class="snackbar">{{ __('messages.created_successfully') }}</div>
                                    </div>
                                </div>
                            </form>

                        </div>{{-- /.pp-card-body --}}
                        </div>{{-- /.pp-card --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
@endpush

@push('after-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script type="text/javascript" defer>
    let finalColumns = [{
        data: 'card',
        name: 'card',
        orderable: false,
        searchable: true
    }];
    let datatable = null;

    document.addEventListener('DOMContentLoaded', (event) => {
        let activeTab = "all"; // Default
        dataTableReload(activeTab);

        $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
            activeTab = $(e.target).attr('data-tab');
            dataTableReload(activeTab);
        });
    });

    function dataTableReload(status) {
        if ($.fn.dataTable.isDataTable('#datatable')) {
            $('#datatable').DataTable().clear().destroy();
        }
        const shimmerLoader = document.querySelector('.shimmer-loader');
        const dataTableElement = document.getElementById('datatable');
        frontInitDatatable({
            url: '{{ route('incidence.index_data') }}',
            finalColumns,
            cardColumnClass: 'row-cols-1',
            advanceFilter: () => {
                return {
                    status: status,
                    doctor_id: $('#doctor').val(),
                }
            },
            onLoadStart: () => {
                // Show shimmer loader before loading data
                shimmerLoader.classList.remove('d-none');
                dataTableElement.classList.add('d-none');
            },
            onLoadComplete: () => {
                shimmerLoader.classList.add('d-none');
                dataTableElement.classList.remove('d-none');
            },
        })
    }

    // Initialize Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

     const imageInput = document.getElementById('image-input');
    const previewContainer = document.getElementById('image-preview-container');
    const previewImagedata = document.getElementById('image-preview');
    const removeBtn = document.getElementById('remove-image-preview');
    document.addEventListener('DOMContentLoaded', function() {


    imageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImagedata.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            previewImagedata.src = '';
            previewContainer.style.display = 'none';
        }
    });

    removeBtn.addEventListener('click', function() {
        imageInput.value = '';
        previewImagedata.src = '';
        previewContainer.style.display = 'none';
    });
});

</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.querySelector("#phone");
        var iti = window.intlTelInput(input, {
            initialCountry: "in",
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js" // To handle number formatting
        });

        input.addEventListener("countrychange", function () {
            var fullPhoneNumber = iti.getNumber();
            document.getElementById('phone').value = fullPhoneNumber;
        });

        input.addEventListener("blur", function () {
            var fullPhoneNumber = iti.getNumber();
            document.getElementById('phone').value = fullPhoneNumber;
        });
    });
</script>
<script>
    document.getElementById('phone').addEventListener('input', function (e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    </script>
<script>
    const validationMessages = {
        required: "{{ __('messages.validation_field_required') }}",
        submitting: "{{ __('messages.submitting') }}",
        submitmessage: "{{ __('messages.lbl_submit') }}",
        incidence_created_success: "{{ __('messages.created_successfully') }}",
        fields: {
            title: "{{ __('messages.field_title') }}",
            emailid: "{{ __('messages.field_emailid') }}",
            phone_number: "{{ __('messages.field_phone_number') }}",
            description: "{{ __('messages.field_description') }}",
            // Add more if needed
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        const description = document.getElementById('description');
        const count = document.getElementById('description-count');
        const maxLength = 500;

        description.addEventListener('input', function() {
            let currentLength = description.value.length;
            if (currentLength > maxLength) {
                description.value = description.value.substring(0, maxLength);
                currentLength = maxLength;
            }
            count.textContent = `${currentLength} / ${maxLength}`;
        });
    });

    document.getElementById('incidence-form').addEventListener('submit', function(e)
    {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);
        let isValid = true;

        const submitBtn = document.getElementById('submit-btn');
        const spinner = document.getElementById('submit-spinner');
        const text = document.getElementById('submit-text');

        // Disable button and show spinner
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = validationMessages.submitting;

        // Clear previous errors
        document.querySelectorAll('.pp-field-error').forEach(function (el) {
            el.textContent = '';
            el.classList.add('d-none');
        });

        // Function to set error message
        function setError(field, message) {
            var el = document.getElementById('error-' + field);
            if (el) {
                el.textContent = message;
                el.classList.remove('d-none');
            }
            isValid = false;
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            text.textContent = validationMessages.submitmessage;
        }

        // Required Fields Validation
        const requiredFields = ['title', 'email', 'phone', 'description'];

       requiredFields.forEach(field => {
            const value = formData.get(field);
            if (!value || value.trim() === '') {
                // Get the translated field label, fallback to formatted key if not found
                const formattedField = validationMessages.fields[field] ||
                                    field.replace(/_/g, ' ')
                                            .replace(/\b\w/g, c => c.toUpperCase());

                // Replace :field in the message template
                const message = validationMessages.required.replace(':field', formattedField);

                setError(field, message);
            }
        });
        // Email Validation
        const email = formData.get('email');
        if (email && !/^[\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,}$/.test(email)) {
            setError('email', "Please enter a valid email address.");
        }
        // Phone Number Validation
        const phone = formData.get('phone');
        if (phone && !/^\+?[0-9\s-]+$/.test(phone)) {
            setError('phone', "Please enter a valid phone number.");
        }

        // Description Validation (enforce limit)
        const description = formData.get('description');
        if (description && description.length > 500) {
            setError('description', "Description cannot exceed 500 characters.");
        }

        // Stop submission if there are errors
        if (!isValid) return;

        // Submit form via AJAX
        // Ensure image file is included in formData if selected
        const imageInput = document.getElementById('image-input');
        // If a file is selected, append it to formData
        if (imageInput && imageInput.files.length > 0) {
            formData.set('file_url', imageInput.files[0]);
        }

        console.log("Form Data:", Array.from(formData.entries()));

        fetch(form.action, {
            method: 'POST',
            headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        })
        .then(async response => {
            let data;
            try {
                data = await response.json();
            } catch (e) {
                throw new Error('Invalid JSON response');
            }
            if (response.ok && data && data.success) {
                window.successSnackbar(validationMessages.incidence_created_success);
                let activeTab = "all-incidences"; // Default active tab
                dataTableReload(activeTab);
                document.getElementById('incidence-form').reset();
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                previewImagedata.src = '';
                previewContainer.style.display = 'none';
                removeBtn.style.display = 'none';
                text.textContent = validationMessages.submitmessage;
            } else {
                window.errorSnackbar(data && data.message ? data.message : "An error occurred while creating the incidence.");
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                text.textContent = validationMessages.submitmessage;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            window.errorSnackbar("Something went wrong!");
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            text.textContent = validationMessages.submitmessage;
        });
    });

    function previewImage(event, id, value = null) {
        const input = value ;
      
        fetch("{{ route('changestatus') }}",
            {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    'id': id,
                    'incident_type': input
                }),
            })
            .then(response => response.json())
            .then(data => {
                // window.successSnackbar(data.message);
                if (data.message) {
                    window.successSnackbar(data.message);
                    // Reload the page to reflect the status change
                    location.reload();
                } else {
                    console.log("Error :", data.message);
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                window.errorSnackbar("Something went wrong!");
            });
    }
</script>
@endpush
