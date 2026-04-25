<h5 class="card-title mb-3">{{ __('appointment.note') }}</h5>
<div class="card bg-body">
    <input type="hidden" name="encounter_id" id="notes_encounter_id" value="{{ $data['id'] }}">
    <input type="hidden" name="user_id" id="notes_user_id" value="{{ $data['user_id'] }}">

    <div class="card-footer pb-0 excounter-note">
        @if ($data['status'] == 1)
            <div class="position-relative">
                <textarea class="form-control h-auto" rows="1" placeholder="Enter Notes" v-model="notes" name="notes"
                    id="notes" style="min-height: max-content"></textarea>
                <button class="btn btn-sm btn-primary" onclick="addNotesValue()"><i
                        class="ph ph-plus me-2"></i>{{ __('appointment.add') }}</button>
            </div>
        @endif
    </div>

    <div class="card-body medial-history-card medial-history-notes">
        <ul class="list-inline m-0 p-0">
            @foreach ($data['notesList'] as $index => $note)
                <li class="mb-3">
                    <div class="d-flex align-items-start justify-content-between gap-1">
                        <span>{{ $index + 1 }}. {{ $note->title }}</span>
                        @if ($data['status'] == 1)
                            <button class="btn p-0 text-danger" onclick="removeNotes({{ $note->id }})">
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

            // Define the addNotesValue function globally
            window.addNotesValue = function() {
                var notes = $('#notes').val();
                var encounterId = $('#notes_encounter_id').val();
                var userId = $('#notes_user_id').val();

                if (notes) {
                    $.ajax({
                        url: baseUrl + '/app/encounter/save-select-option',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            type: 'encounter_notes',
                            name: notes,
                            encounter_id: encounterId,
                            user_id: userId
                        },
                        success: function(response) {
                            if (response && response.status) {
                                // Update the notes list
                                $('#notes').val('');
                                var listHtml = '';
                                response.medical_histroy.forEach(function(note, index) {
                                    listHtml += `
                                <li class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-start justify-content-between gap-1">
                                        <span>${index + 1}. ${note.title}</span>
                                        <button class="btn p-0 text-danger"
                                            onclick="removeNotes(${note.id})">
                                            <i class="ph ph-x-circle"></i>
                                        </button>
                                    </div>
                                </li>`;
                                });
                                $('.medial-history-notes ul').html(listHtml);
                            } else {
                                console.log('Failed to save note.');
                            }
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            };

            // Define the removeNotes function globally
            window.removeNotes = function(Id) {
                if (Id) {
                    $.ajax({
                        url: baseUrl + '/app/encounter/remove-histroy-data?id=' + Id +
                            '&type=encounter_notes',
                        method: 'GET',
                        success: function(response) {
                            if (response && response.status) {
                                // Update the notes list
                                var listHtml = '';
                                response.medical_histroy.forEach(function(note, index) {
                                    listHtml += `
                                <li class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-start justify-content-between gap-1">
                                        <span>${index + 1}. ${note.title}</span>
                                        <button class="btn p-0 text-danger"
                                            onclick="removeNotes(${note.id})">
                                            <i class="ph ph-x-circle"></i>
                                        </button>
                                    </div>
                                </li>`;
                                });
                                $('.medial-history-notes ul').html(listHtml);

                                console.log('Note removed successfully!');
                            } else {
                                console.error('Failed to remove note.');
                            }
                        },
                        error: function(error) {
                            console.error('Error:', error);
                        }
                    });
                }
            };
        });
    </script>
@endpush
