<h5 class="card-title mb-3">{{ __('appointment.observation') }}</h5>
<div class="card bg-body">
    <input type="hidden" name="encounter_id" id="observation_encounter_id" value="{{ $data['id'] }}">
    <input type="hidden" name="user_id" id="observation_user_id" value="{{ $data['user_id'] }}">
    <div class="card-footer pb-0">
        <p class="mb-2 mb-0 fs-12 clinical_details_notes text-danger">
            <b>{{ __('appointment.note_encounter_observation') }}</b>
        </p>
        <select id="observations" name="observation_id" class="form-control select2"
            placeholder="{{ __('appointment.select_observation') }}" data-filter="select">
            <option value="">{{ __('appointment.select_observation') }}</option>
            @foreach ($observation_list as $observation)
                <option value="{{ $observation->name }}">{{ $observation->name }}
                </option>
            @endforeach
        </select>

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
            var baseUrl = '{{ url('/') }}';

            $('#observations').on('change', function() {
                var observationName = $(this).val();
                var encounterId = $('#observation_encounter_id').val();
                var userId = $('#observation_user_id').val();

                if (observationName) {
                    $.ajax({
                        url: baseUrl + '/app/encounter-template/save-template-histroy',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            type: 'encounter_observations',
                            name: observationName,
                            encounter_id: encounterId,
                            template_id: encounterId,
                            user_id: userId
                        },
                        success: function(response) {
                            if (response && response.status) {
                                // Update the problem list
                                var listHtml = '';
                                response.medical_histroy.forEach(function(observation, index) {
                                    listHtml += `
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
                                $('.medial-history-card-observation ul').html(listHtml);

                                // Update the dropdown options
                                var dropdownHtml =
                                    `<option value="">{{ __('appointment.select_observation') }}</option>`;
                                response.constant_data.forEach(function(observation) {
                                    dropdownHtml +=
                                        `<option value="${observation.name}">${observation.name}</option>`;
                                });
                                $('#observations').html(dropdownHtml);

                                $('#observations').val('').trigger('change');
                            } else {
                                console.log('Failed to save problem.');
                            }
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });
        });


        function removeobservation(Id) {
            var baseUrl = '{{ url('/') }}';

            if (Id) {
                $.ajax({
                    url: baseUrl + '/app/encounter-template/remove-template-histroy?id=' + Id +
                        '&type=encounter_observation',
                    method: 'GET',

                    success: function(response) {
                        if (response && response.status) {
                            // Update the problem list
                            var listHtml = '';
                            response.medical_histroy.forEach(function(observation, index) {
                                listHtml += `
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
                            $('.medial-history-card-observation ul').html(listHtml);

                            console.log('History item removed successfully!');
                        } else {
                            console.error('Failed to remove history item.');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }
        }
    </script>
@endpush
