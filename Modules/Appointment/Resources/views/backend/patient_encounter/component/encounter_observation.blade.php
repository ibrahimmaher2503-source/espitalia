<h5 class="card-title mb-3">{{ __('appointment.observation') }}</h5>
<div class="card bg-body">
    <input type="hidden" name="encounter_id" id="observation_encounter_id" value="{{ $data['id'] }}">
    <input type="hidden" name="user_id" id="observation_user_id" value="{{ $data['user_id'] }}">

    <div class="card-footer pb-0">
        <p class="mb-2 mb-0 fs-12 clinical_details_notes text-danger">
            <b>{{ __('appointment.note_encounter_observation') }}</b>
        </p>
        @if ($data['status'] == 1)
            <select id="observations" name="observation_id" class="form-control select2 observation "
                placeholder="{{ __('appointment.select_observation') }}" data-filter="select">
                <option value="">{{ __('appointment.select_observation') }}</option>
                @foreach ($observation_list as $observation)
                    <option value="{{ $observation->name }}">{{ $observation->name }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="card-body medial-history-card medial-history-card-observation">
        <ul class="list-inline m-0 p-0">
            @foreach ($data['selectedObservationList'] as $index => $observation)
                <li class="mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-1">
                        <span>{{ $index + 1 }}. {{ $observation['title'] }}</span>
                        @if ($data['status'] == 1)
                            <button class="btn p-0 text-danger" onclick="removeobservation({{ $observation['id'] }})">
                                <i class="ph ph-x-circle"></i>
                            </button>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>


</div>

@push('after-scripts')
    <script>
        $(document).ready(function() {

            $('#observations').select2({
                placeholder: '{{ __('appointment.select_observation') }}',
                allowClear: true // Optional: Allows clearing the selection
            });
            $('#observations').on('select2:open', function () {
                var observationsInputField = $('.select2-container--open .select2-search__field');

                observationsInputField.off('keydown'); // Remove previous listeners
                observationsInputField.on('keydown', function (event) {
                    if (event.key === "Enter") {
                        var newOption = $(this).val();
                        if (newOption) {
                            var newOptionElement = new Option(newOption, newOption, true, true);
                            $('#observations').append(newOptionElement).trigger('change');
                            $('#observations').select2('close');
                        }
                    }
                });
            });

            var baseUrl = '{{ url('/') }}';

            $('#observations').on('change', function() {
                var observationName = $(this).val();
                var encounterId = $('#observation_encounter_id').val();
                var userId = $('#observation_user_id').val();

                if (observationName) {
                    $.ajax({
                        url: '{{ url('/app/encounter/save-select-option') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            type: 'encounter_observations',
                            name: observationName,
                            encounter_id: encounterId,
                            user_id: userId
                        },
                        success: function(response) {
                            if (response && response.status) {
                                updateObservationList(response.medical_histroy);
                                updateDropdown(response.data);
                            } else {
                                console.error('Failed to save observation.');
                            }
                        },
                        error: function(error) {
                            console.error('AJAX Error:', error);
                        }

                    });
                }
            });
        });

        function updateObservationList(medicalHistory) {
                var observationlistHtml = '';
                medicalHistory.forEach(function(observation, index) {
                    observationlistHtml += `
                <li class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-start justify-content-between gap-1">
                        <span>${index + 1}. ${observation.title}</span>
                        <button class="btn p-0 text-danger"
                            onclick="removeobservation(${observation.id})">
                            <i class="ph ph-x-circle"></i>
                        </button>
                    </div>
                </li>`;
                });
                $('.medial-history-card-observation ul').html(observationlistHtml);
            }

            function updateDropdown(data) {
                var observationdropdownHtml = `<option value="">{{ __('appointment.select_problems') }}</option>`;
                data.forEach(function(problem) {
                    observationdropdownHtml += `<option value="${observation.name}">${observation.name}</option>`;
                });
                $('#observations').html(observationdropdownHtml);

                // Reinitialize Select2
                $('#observations').select2({
                    tags: true,
                    placeholder: "{{ __('appointment.select_problems') }}",
                    allowClear: true
                });
            }



        function removeobservation(observationId) {
            if (observationId) {
                $.ajax({
                    url: '{{ url('/app/encounter/remove-histroy-data') }}',
                    method: 'GET',
                    data: {
                        id: observationId,
                        type: 'encounter_observations'
                    },
                    success: function(response) {
                        if (response && response.status) {
                            // Update the problem list
                            var observationlistHtml = '';
                            response.medical_histroy.forEach(function(observation, index) {
                                observationlistHtml += `
                            <li class="mb-3">
                                <div class="d-flex align-items-start justify-content-between gap-1">
                                    <span>${index + 1}. ${observation.title}</span>
                                    <button class="btn p-0 text-danger"
                                        onclick="removeobservation(${observation.id})">
                                        <i class="ph ph-x-circle"></i>
                                    </button>
                                </div>
                            </li>`;
                            });
                            $('.medial-history-card-observation ul').html(observationlistHtml);

                            console.log('History item removed successfully!');
                        } else {
                            console.error('Failed to remove history item.');
                        }
                    },
                    error: function(error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }
        }
    </script>
@endpush
