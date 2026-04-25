<h5 class="card-title mb-3">{{ __('appointment.problems') }}</h5>
<div class="card bg-body">
    <input type="hidden" name="encounter_id" id="problem_encounter_id" value="{{ $data['id'] }}">
    <input type="hidden" name="user_id" id="problem_user_id" value="{{ $data['user_id'] }}">
    <div class="card-footer pb-0">
        <p class="mb-2 fs-12 clinical_details_notes text-danger">
            <b>{{ __('appointment.note_encounter_problem') }}</b>
        </p>
        <select id="problem" name="problem_id" class="form-control select2"
            placeholder="{{ __('appointment.select_problems') }}" data-filter="select">
            <option value="">{{ __('appointment.select_problems') }}</option>
            @foreach ($problem_list as $problem)
                <option value="{{ $problem->name }}">{{ $problem->name }}</option>
            @endforeach
        </select>

    </div>
    <div class="card-body medial-history-card medial-history-card-problem">
        <ul class="list-inline m-0 p-0">
            @foreach ($data['selectedProblemList'] as $index => $problem)
                <li class="mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-1">
                        <span>{{ $index + 1 }}. {{ $problem['title'] }}</span>
                        @if ($data['status'] == 1)
                            <button class="btn p-0 text-danger" onclick="removeProblem({{ $problem['id'] }})">
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

            $('#problem').on('change', function() {
                var problemName = $(this).val();
                var encounterId = $('#problem_encounter_id').val();
                var userId = $('#problem_user_id').val();

                if (problemName) {
                    $.ajax({
                        url: baseUrl + '/app/encounter-template/save-template-histroy',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            type: 'encounter_problem',
                            name: problemName,
                            encounter_id: encounterId,
                            template_id: encounterId,
                            user_id: userId
                        },
                        success: function(response) {
                            if (response && response.status) {
                                // Update the problem list
                                var listHtml = '';
                                response.medical_histroy.forEach(function(problem, index) {
                                    listHtml += `
                                <li class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-start justify-content-between gap-1">
                                        <span>${index + 1}. ${problem.title}</span>
                                        <button class="btn p-0 text-danger"
                                            onclick="removeProblem(${problem.id})">
                                            <i class="ph ph-x-circle"></i>
                                        </button>
                                    </div>
                                </li>`;
                                });
                                $('.medial-history-card-problem ul').html(listHtml);

                                // Update the dropdown options
                                var dropdownHtml =
                                    `<option value="">{{ __('appointment.select_problems') }}</option>`;
                                response.constant_data.forEach(function(problem) {
                                    dropdownHtml +=
                                        `<option value="${problem.name}">${problem.name}</option>`;
                                });
                                $('#problem').html(dropdownHtml);

                                $('#problem').val('').trigger('change');
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


        function removeProblem(problemId) {
            var baseUrl = '{{ url('/') }}';

            if (problemId) {
                $.ajax({
                    url: baseUrl + '/app/encounter-template/remove-template-histroy?id=' + problemId +
                        '&type=encounter_problem',
                    method: 'GET',

                    success: function(response) {
                        if (response && response.status) {
                            // Update the problem list
                            var listHtml = '';
                            response.medical_histroy.forEach(function(problem, index) {
                                listHtml += `
                            <li class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-start justify-content-between gap-1">
                                    <span>${index + 1}. ${problem.title}</span>
                                    <button class="btn p-0 text-danger"
                                        onclick="removeProblem(${problem.id})">
                                        <i class="ph ph-x-circle"></i>
                                    </button>
                                </div>
                            </li>`;
                            });
                            $('.medial-history-card-problem ul').html(listHtml);

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
