@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <div class="row">

        <div class="col-lg-12 m-3 col-md-8">
            <h4 class="card-title mb-3">{{ __('appointment.other_detail') }}</h4>
            <div class="card">
                <div class="d-flex justify-content-between flex-wrap gap-3 card-header mb-4" id="nav-tab" role="tablist">
                    <h4 class="card-title mb-0"> Clinic
                        Details</h4>
                </div>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row row mx-2 d-flex justify-content-between flex-wrap gap-3 ">
                            {{-- Encounter Problem --}}

                            {{-- @if (!empty($data['selectedProblemList'])) --}}
                            <div class="col-lg-3 col-md-6 ">

                                @include(
                                    'appointment::backend.encounter_template.template_encounter_problem',
                                    ['data' => $data, 'problem_list' => $problem_list]
                                )
                            </div>

                            {{-- @endif --}}


                            {{-- Encounter Observation --}}
                            {{-- @if ($data['selectedObservationList']->isNotEmpty()) --}}
                            <div class="col-lg-3 col-md-6">
                                @include(
                                    'appointment::backend.encounter_template.template_encounter_observation',
                                    ['data' => $data, 'observation_list' => $observation_list]
                                )
                            </div>
                            {{-- @endif --}}

                            {{-- Encounter Notes --}}
                            {{-- @if ($data['notesList']->isNotEmpty()) --}}
                            <div class="col-lg-3 ">
                                @include(
                                    'appointment::backend.encounter_template.template_encounter_note',
                                    ['data' => $data]
                                )
                            </div>
                            {{-- @endif --}}
                        </div>

                        {{-- Medical Report --}}
                        <div class="card-body border-top">


                            {{-- Prescription --}}
                            <div class="card bg-body">
                                <div class="card-header d-flex justify-content-between flex-wrap gap-3 my-3">
                                    <h5 class="card-title">{{ __('appointment.prescription') }}</h5>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addprescriptiontemplae">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <i class="ph ph-plus"></i>
                                            {{ __('appointment.add_prescription') }}
                                        </div>
                                    </button>
                                </div>

                                @if ($data['prescriptions'])
                                    <div id="prescription_table">
                                        @include(
                                            'appointment::backend.encounter_template.template_prescription_table',
                                            ['data' => $data]
                                        )
                                    </div>
                                @endif

                            </div>

                            <input type="hidden" name="template_id" id="template_id" value="{{ $data['id'] }}">

                            {{-- Other Details --}}
                            <div class="card bg-body other-detail">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">{{ __('appointment.other_information') }}</h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control h-auto" rows="3" placeholder="{{ __('appointment.enter_other_details') }}"
                                        name="other_details" id="other_details" style="min-height: max-content">{{ old('other_details', $data['other_details'] ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="offcanvas-footer border-top pt-4" id="save_button">
                                <div class="d-grid d-sm-flex justify-content-sm-end gap-3">
                                    <a href="{{ route('backend.encounter-template.index') }}" class="btn btn-light">
                                        {{ __('messages.cancel') }}
                                    </a>
                                    <button class="btn btn-secondary" type="button" id="saveOtherDetailsBtn">
                                        {{ __('messages.save') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SOAP Tab --}}
                    {{-- <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div id="soap">
                            @include('appointment::backend.patient_encounter.component.soap', ['data' => $data])
                        </div>
                    </div> --}}

                    {{-- Contact Tab --}}
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                        {{-- Add contact details here if needed --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('appointment::backend.encounter_template.template_prescription', ['data' => $data])
    {{-- @include('appointment::backend.patient_encounter.component.billing_details', ['data' => $data]) --}}
@endsection

@push('after-scripts')
    <script>
        $(document).ready(function() {
            $('select').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                tags: true,
                searchable: true,
                createTag: function(params) {
                    return {
                        id: params.term,
                        text: params.term
                    };
                }
            });
        });



        $(document).ready(function() {
            $('#saveOtherDetailsBtn').on('click', function() {
                const otherDetails = $('#other_details').val();
                const templateId = $('#template_id').val();
                $.ajax({
                    url: "{{ route('backend.encounter-template.save-other-details') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        other_details: otherDetails,
                        template_id: templateId
                    },
                    success: function(response) {
                        console.log(response)
                        if (response.status) {
                            window.successSnackbar('EncounterTemplate save successFully')
                        } else {
                            window.errorSnackbar('Something went wrong! Please check.');
                        }
                    },
                    error: function(xhr) {
                        window.errorSnackbar('Something went wrong! Please check.');
                    }
                });
            });
        });
    </script>
@endpush
