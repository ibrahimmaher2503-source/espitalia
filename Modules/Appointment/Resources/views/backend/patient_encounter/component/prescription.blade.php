<div class="modal fade" id="addprescription" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('clinic.add_prescription') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form method="post" id="form-submit" class="requires-validation" novalidate>
                    @csrf
                    <div class="row" id="prescription-model">


                        <input type="hidden" name="id" id="id" value="">
                        <input type="hidden" name="encounter_id" id="problem_encounter_id" value="{{ $data['id'] }}">
                        <input type="hidden" name="user_id" id="problem_user_id" value="{{ $data['user_id'] }}">
                        <input type="hidden" name="type" value="encounter_prescription">

                        <label class="form-label col-md-12">
                            {{ __('clinic.name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="prescription_name" class="form-control col-md-12"
                            placeholder="{{ __('clinic.lbl_name') }}" value="" required>
                        <div class="invalid-feedback">
                            {{ __('Please provide a valid Name.') }}
                        </div>

                        <!-- Frequency -->
                        <div class="form-group">
                            <label class="form-label col-md-12">
                                {{ __('clinic.lbl_frequency') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="frequency" id="frequency" class="form-control col-md-12"
                                placeholder="{{ __('clinic.lbl_frequency') }}" value="" required>
                            <div class="invalid-feedback">
                                {{ __('Please provide a valid frequency.') }}
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="form-group">
                            <label class="form-label col-md-12">
                                {{ __('clinic.lbl_duration') }} <span class="text-danger">*</span>
                            </label>
                            {{-- <input type="number" name="duration" id="duration" class="form-control col-md-12"
                                placeholder="{{ __('clinic.lbl_duration') }}" value="" required>
                            <div class="invalid-feedback">
                                {{ __('Please provide a valid duration.') }}
                            </div> --}}

                            <input type="number" name="duration" id="duration" class="form-control col-md-12"
                                placeholder="{{ __('clinic.lbl_duration') }}" required>
                            <div class="invalid-feedback">
                                {{ __('Please provide a valid duration.') }}
                            </div>
                        </div>

                        <!-- Instruction -->
                        <div class="form-group">
                            <label class="form-label" for="instruction">{{ __('clinic.lbl_instruction') }}</label>
                            <textarea class="form-control" name="instruction" id="instruction" placeholder="{{ __('clinic.lbl_instruction') }}">{{ old('instruction') }}</textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('after-scripts')
    <script>
        $(document).ready(function() {


            var baseUrl = '{{ url('/') }}';

            // $('#form-submit').on('submit', function(event) {
            //     event.preventDefault();

            //     let form = $(this)[0];
            //     if (form.checkValidity() === false) {
            //         event.stopPropagation();
            //         form.classList.add('was-validated');
            //         return;
            //     }

            //     let formData = $(this).serializeArray();
            //     let hasId = formData.some(field => field.name === 'id' && field.value !== '');
            //     let id = formData.find(field => field.name === 'id')?.value || null;

            //     let route = hasId ?
            //         `${baseUrl}/app/encounter/update-prescription/${id}` // Update route
            //         :
            //         `${baseUrl}/app/encounter/save-prescription`; // Save route

            //     $.ajax({
            //         url: route,
            //         method: 'POST',
            //         data: $(this).serialize(),
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
            //                 'content') // Include CSRF token for security
            //         },
            //         success: function(response) {
            //             if (response.html) {

            //                 document.getElementById('prescription_table').innerHTML = response
            //                     .html;
            //                 $('#addprescription').modal('hide');

            //                 $('#form-submit').trigger('reset');

            //                 $('#id').val('');


            //                 $('#form-submit')[0].classList.remove('was-validated');

            //                 window.successSnackbar(
            //                     `Prescription ${hasId ? 'updated' : 'added'} successfully`);
            //             } else {
            //                 window.errorSnackbar('Something went wrong! Please check.');
            //             }
            //         },
            //         error: function(xhr) {
            //             alert('An error occurred: ' + xhr.responseText);
            //         }
            //     });
            // });

            $('#form-submit').on('submit', function(event) {
                event.preventDefault();

                const form = this;
                let isValid = true;

                // Clear previous validation styles
                form.classList.remove('was-validated');
                $('#duration').removeClass('is-invalid is-valid');

                // ✅ Duration validation starts here
                const durationVal = $('#duration').val();
                const duration = Number(durationVal);

                // Check if it's a positive integer (no decimals, not negative, not zero)
                if (!Number.isInteger(duration) || duration <= 0) {
                    $('#duration').removeClass('is-valid').addClass('is-invalid');
                    $('#duration').next('.invalid-feedback').text('Duration must be a positive whole number (e.g., 1, 2, 3).');
                    isValid = false;
                } else {
                    $('#duration').removeClass('is-invalid').addClass('is-valid');
                    $('#duration').next('.invalid-feedback').text('');
                }


                // ✅ Stop here if invalid
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                if (!isValid) return;

                // Only mark duration as valid if everything passed
                $('#duration').addClass('is-valid');

                // Submit via AJAX
                const formData = $(this).serializeArray();
                const hasId = formData.some(field => field.name === 'id' && field.value !== '');
                const id = formData.find(field => field.name === 'id')?.value || null;

                const route = hasId
                    ? `${baseUrl}/app/encounter/update-prescription/${id}`
                    : `${baseUrl}/app/encounter/save-prescription`;

                $.ajax({
                    url: route,
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.html) {
                            $('#prescription_table').html(response.html);
                            $('#addprescription').modal('hide');
                            $('#form-submit').trigger('reset').removeClass('was-validated');
                            $('#id').val('');
                            $('#duration').removeClass('is-valid is-invalid');
                            window.successSnackbar(`Prescription ${hasId ? 'updated' : 'added'} successfully`);
                        } else {
                            window.errorSnackbar('Something went wrong! Please check.');
                        }
                    },
                    error: function(xhr) {
                        alert('An error occurred: ' + xhr.responseText);
                    }
                });
            });


            $('#addprescription').on('hidden.bs.modal', function() {

                $('#id').val('')
                $('#form-submit').trigger('reset').removeClass('was-validated');
            });

        });
    </script>
@endpush
