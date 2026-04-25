<h5 class="card-title mb-3">{{ __('appointment.problems') }}</h5>
<div class="card bg-body">
    <input type="hidden" name="encounter_id" id="problem_encounter_id" value="{{ $data['id'] }}">
    <input type="hidden" name="user_id" id="problem_user_id" value="{{ $data['user_id'] }}">


    <div class="card-footer pb-0">
        <p class="mb-2 fs-12 clinical_details_notes text-danger">
            <b>{{ __('appointment.note_encounter_problem') }}</b>
        </p>
        @if ($data['status'] == 1)
            <select id="problem" name="problem_id" class="form-control select2"
                placeholder="{{ __('appointment.select_problems') }}" data-filter="select">
                <option value="">{{ __('appointment.select_problems') }}</option>
                @foreach ($problem_list as $problem)
                    <option value="{{ $problem->name }}">{{ $problem->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="card-body medial-history-card medial-history-card-problem">
        <ul class="list-inline m-0 p-0">
            @foreach ($data['selectedProblemList'] as $index => $problem)
                <li class="mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-1">
                        <span>{{ $index + 1 }}. {{ $problem['title'] }}</span>
                        @if ($data['status'] == 1)
                            <button class="btn p-0 text-danger" onclick="removeProblemData({{ $problem['id'] }})">
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
            $('#problem').select2({
                placeholder: '{{ __('appointment.select_problems') }}',
                allowClear: true // Optional: Allows clearing the selection
            });

            $('#problem').on('select2:open', function () {
        var problemInputField = $('.select2-container--open .select2-search__field');

        problemInputField.off('keydown'); // Remove previous listeners
        problemInputField.on('keydown', function (event) {
            if (event.key === "Enter") {
                var newOption = $(this).val();
                if (newOption) {
                    var newOptionElement = new Option(newOption, newOption, true, true);
                    $('#problem').append(newOptionElement).trigger('change');
                    $('#problem').select2('close');
                }
            }
        });
    });
        });


        function removeProblemData(problemId) {
            if (problemId) {
                $.ajax({
                    url: '{{ url('/app/encounter/remove-histroy-data') }}',
                    method: 'GET',
                    data: {
                        id: problemId,
                        type: 'encounter_problem'
                    },
                    success: function(response) {
                        if (response && response.status) {
                            updateProblemList(response.medical_histroy);
                        } else {
                            console.error('Failed to remove problem.');
                        }
                    },
                    error: function(error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }
        }

        function updateProblemList(medicalHistory) {
            var listHtml = '';
            medicalHistory.forEach(function(problem, index) {
                listHtml += `
                    <li class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-start justify-content-between gap-1">
                            <span>${index + 1}. ${problem.title}</span>
                            <button class="btn p-0 text-danger"
                                onclick="removeProblemData(${problem.id})">
                                <i class="ph ph-x-circle"></i>
                            </button>
                        </div>
                    </li>`;
            });
            $('.medial-history-card-problem ul').html(listHtml);
        }

        function updateDropdown(data) {
            var dropdownHtml = `<option value="">{{ __('appointment.select_problems') }}</option>`;
            data.forEach(function(problem) {
                dropdownHtml += `<option value="${problem.name}">${problem.name}</option>`;
            });
            $('#problem').html(dropdownHtml);

            // Reinitialize Select2
            $('#problem').select2({
                tags: true,
                placeholder: "{{ __('appointment.select_problems') }}",
                allowClear: true
            });
        }

        $(document).ready(function() {

            $('#problem').on('change', function() {

                var problemName = $(this).val();
                var encounterId = $('#problem_encounter_id').val();
                var userId = $('#problem_user_id').val();

                if (problemName) {
                    $.ajax({
                        url: '{{ url('/app/encounter/save-select-option') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            type: 'encounter_problem',
                            name: problemName,
                            encounter_id: encounterId,
                            user_id: userId
                        },
                        success: function(response) {
                            if (response && response.status) {
                                updateProblemList(response.medical_histroy);
                                updateDropdown(response.data);


                            } else {
                                console.error('Failed to save problem.');
                            }
                        },
                        error: function(error) {
                            console.error('AJAX Error:', error);
                        }
                    });
                }
            });


        });
    </script>
@endpush
