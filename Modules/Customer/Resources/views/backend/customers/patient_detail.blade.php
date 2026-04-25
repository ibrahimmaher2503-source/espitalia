@extends('backend.layouts.app')
@section('title')
  {{ __($module_title) }}
@endsection

@push('after-styles')
<link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
    <h4>{{$data['patientInfo']['name']}}  {{ __('customer.overview') }}</h4>
    <a href="{{ route('backend.customers.index') }}" class="btn btn-primary">{{ __('messages.back') }}
    </a>
</div>


<ul class="nav nav-pills mb-4 patient-overview-tab" id="pills-tab" role="tablist">
    <li class="nav-item" role="presentation">
        <div class="d-flex align-items-center">
            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">
                <i class="ph ph-notebook"></i>
                <span>{{ __('customer.overview') }}</span></button>
        </div>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">
      <i class="ph ph-users-three"></i>
      <span>{{ __('customer.book_for_other') }}</span></button>
    </li>

  </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
            <div class="card">
                <div class="card-body">

                    <div class="row gy-3 mb-5 pb-2">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary-subtle">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-1">
                                        <h5 class="mb-0">{{ __('customer.total_appointment') }}</h5>
                                        <div class="avatar-60 badge rounded-circle bg-icon fs-2">
                                            <i class="ph ph-calendar-dots text-primary"></i>
                                        </div>
                                    </div>
                                    <h3 class="text-secondary mb-0">{{ $data['totalAppointments'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary-subtle">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-1">
                                        <h5 class="mb-0">{{ __('customer.cancelled_appointments') }}</h5>
                                        <div class="avatar-60 badge rounded-circle bg-icon fs-2">
                                            <i class="ph ph-calendar-x text-primary"></i>
                                        </div>
                                    </div>
                                    <h3 class="text-secondary mb-0">{{ $data['cancelledAppointments'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary-subtle">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-1">
                                        <h5 class="mb-0">{{ __('customer.completed_appointments') }}</h5>
                                        <div class="avatar-60 badge rounded-circle bg-icon fs-2">
                                            <i class="ph ph-calendar-check text-primary"></i>
                                        </div>
                                    </div>
                                    <h3 class="text-secondary mb-0">{{ $data['completedAppointments'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary-subtle">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-1">
                                        <h5 class="mb-0">{{ __('customer.upcoming_appointments') }}</h5>
                                        <div class="avatar-60 badge rounded-circle bg-icon fs-2">
                                            <i class="ph ph-users-three text-primary"></i>
                                        </div>
                                    </div>
                                    <h3 class="text-secondary mb-0">{{ $data['upcomingAppointments'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="mb-3">{{ __('customer.patient_basic_info') }}</h4>
                        <div class="d-flex gap-3 align-items-center p-4 bg-body roubded flex-md-nowrap flex-wrap">
                        <div>
                            <img src="{{ $patient->profile_image ?? default_user_avatar() }}" alt="Profile Image"
                                    class="avatar avatar-80 rounded-pill">
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="m-0">{{ $data['patientInfo']['name'] }}</h4>
                            <div class="d-flex align-items-center column-gap-3 row-gap-2 mt-3 flex-md-nowrap flex-wrap">
                                <div class="d-flex align-items-center gap-2 text-break">
                                    <i class="ph ph-envelope-simple text-heading"></i>
                                    <a href="#" class="text-secondary text-decoration-underline font-size-16">
                                            {{ $data['patientInfo']['email'] }}
                                    </a>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <i class="ph ph-phone text-heading"></i>
                                    <a href="#" class="text-primary text-decoration-underline font-size-16">
                                            {{ $data['patientInfo']['contact'] }}
                                    </a>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <i class="ph ph-cake text-heading"></i>
                                    <span class="font-size-16"> {{ \Carbon\Carbon::parse($data['patientInfo']['dob'])->format('d-m-Y') }}</span>
                                </div>

                            </div>
                        </div>
                      </div>

                    @if(isset($data['topDoctors']) && $data['topDoctors']->isNotEmpty())
                    <div class="mt-5 pt-2">
                        <h4 class="mb-3">{{ __('customer.most_booked_doctors') }}</h4>
                        <div class="row gy-3">
                            @foreach($data['topDoctors'] as $doctor)

                            <div class="col-xl-3 col-md-6">
                                <div class="d-flex gap-3 align-items-center p-4 bg-body roubded flex-md-nowrap flex-wrap">
                                    <div>
                                        <img src="{{ $doctor->profile_image ?? default_user_avatar() }}" alt="Profile Image" class="avatar avatar-60 rounded-pill">
                                    </div>
                                    <div class="d-flex row-gap-2  flex-column">

                                        <h5 class="mb-0">Dr.{{ $doctor->first_name }} {{ $doctor->last_name }}</h5>
                                        <span class="font-size-12 text-transform-uppercase">{{ $doctor->email }}</span>
                                        @php
                                            $patientAppointmentsCount = Modules\Appointment\Models\Appointment::where('doctor_id', $doctor->id)
                                                ->where('user_id', $data['patientInfo']['id'])
                                                ->count();
                                        @endphp
                                        <span class="text-primary font-size-12 fw-semibold">{{ $patientAppointmentsCount }} {{ __('messages.appointment') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>
                    </div>
                    @endif


                    @if(isset($data['topClinics']) && $data['topClinics']->isNotEmpty())
                    <div class="mt-5 pt-2">
                    <h4 class="mb-3">{{ __('customer.most_visited_clinics') }}</h4>
                        <div class="row gy-3">
                            @foreach($data['topClinics'] as $clinic)

                            <div class="col-xl-3 col-md-6">
                                <div class="d-flex gap-3 align-items-center p-4 bg-body roubded flex-md-nowrap flex-wrap">
                                    <div>
                                        <img src="{{ $clinic->file_url}}" alt="Profile Image" class="avatar avatar-60 rounded-3">
                                    </div>
                                    <div class="d-flex row-gap-2  flex-column">
                                        <h5 class="mb-0">{{ $clinic->name }}</h5>
                                        <div class="d-flex align-items-center gap-2">
                                        <i class="ph ph-map-pin text-heading"></i>
                                        <span class="font-size-12">{{ $clinic->address }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ph ph-phone text-heading"></i>
                                        <a href="#" class="text-decoration-none text-primary font-size-12 fw-semibold">
                                            {{ $clinic->contact_number }}
                                        </a>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                    </div>

                    @endif

                </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-end mb-3">
                        <button class="btn btn-secondary"  data-bs-toggle="modal" data-bs-target="#addOtherPatientModal">
                            {{ __('customer.add_other_patient') }}
                        </button>
                    </div>
                    <div class="row gy-3">
                        @if ($otherPatients->isNotEmpty())
                            @foreach ($otherPatients as $otherPatient)
                                <div class="col-lg-12">

                                    <div class="card rounded-3 card-end bg-body">
                                        <div class="card-body">
                                            <div class="d-flex flex-sm-nowrap flex-wrap gap-3">
                                                <div class="avatar-wrapper">
                                                    <img src="{{ $otherPatient->getFirstMediaUrl('profile_image') ? asset($otherPatient->getFirstMediaUrl('profile_image')) : default_user_avatar() }}"
                                                    alt="profile image" class="rounded-circle me-3" width="60" height="60">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                                            <h5 class="font-size-18 mb-0">{{ $otherPatient->first_name }} {{ $otherPatient->last_name }}</h5>
                                                            <span class="badge bg-secondary-subtle rounded-pill">{{ $otherPatient->relation }}</span>
                                                        </div>
                                                        <div class="d-flex align-items-center column-gap-3 row-gap-2">
                                                            <button type="button editBtn" class="btn btn-link p-0 editBtn text-icon" data-id="{{$otherPatient->id}}" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal_{{ $otherPatient->id }}">
                                                                    <i class="ph ph-pencil-simple font-size-18"></i>
                                                            </button>
                                                            {{-- <button class="btn btn-link p-0 text-icon deleteBtn delete-patient"  data-id="{{ $otherPatient->id }}" data-name="{{ $otherPatient->first_name }} {{ $otherPatient->last_name }}" title="Delete">
                                                            <i class="ph ph-trash font-size-18"></i>
                                                            </button> --}}

                                                            <form method="POST" action="{{ route('backend.customers.otherPatient.delete', $otherPatient->id) }}" class="d-inline delete-form">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button"
                                                                    class="btn btn-link p-0 text-icon deleteBtn delete-patient"
                                                                    data-id="{{ $otherPatient->id }}"
                                                                    data-name="{{ $otherPatient->first_name }} {{ $otherPatient->last_name }}"
                                                                    data-url="{{ route('backend.customers.otherPatient.delete', $otherPatient->id) }}"
                                                                    title="Delete">
                                                                    <i class="ph ph-trash font-size-18"></i>
                                                                </button>
                                                            </form>

                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center flex-wrap gap-3">
                                                        <div class="d-flex align-items-center gap-3 font-size-14">
                                                            <i class="ph ph-user text-heading"></i>{{ $otherPatient->gender }}
                                                        </div>
                                                        <div class="d-flex align-items-center gap-3 font-size-14">
                                                            <i class="ph ph-phone text-heading"></i>{{ $otherPatient->contactNumber }}
                                                        </div>
                                                        <div class="d-flex align-items-center gap-3 font-size-14">
                                                            <i class="ph ph-cake text-heading"></i> {{ \Carbon\Carbon::parse($otherPatient->dob)->format('d-m-Y') }}
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info col-12">
                                {{ __('customer.no_patient_available') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- Add Other Patient Modal -->
     <div class="modal fade" id="addOtherPatientModal" tabindex="-1" aria-labelledby="addOtherPatientLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addPatientForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addOtherPatientLabel">{{ __('customer.add_new_patient') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">{{ __('customer.lbl_first_name') }}</label><span class="required-star text-danger">*</span>
                            <input type="text" class="form-control" id="first_name" name="first_name"      placeholder="{{ __('clinic.lbl_first_name') }}">
                            <span class="error text-danger"></span>

                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">{{ __('customer.lbl_last_name') }}</label><span class="required-star text-danger">*</span>
                            <input type="text" class="form-control" id="last_name" name="last_name"      placeholder="{{ __('clinic.lbl_last_name') }}">
                            <span class="error text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <label for="dob" class="form-label">{{ __('customer.lbl_date_of_birth') }}</label><span class="required-star text-danger">*</span>
                            <input type="date" class="form-control" id="dob" name="dob"     placeholder="{{ __('customer.select_date_of_birth') }}">
                            <span class="error text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <label for="contactNumber" class="form-label">{{ __('customer.lbl_phone_number') }}</label><span class="required-star text-danger">*</span>
                            <input type="text" class="form-control" id="contactNumber" name="contactNumber"   placeholder="{{ __('employee.lbl_phone_number_placeholder') }}">
                            <span class="error text-danger"></span>
                        </div>
                        {{-- <div class="mb-3">
                            <label  class="form-label">{{ __('customer.lbl_gender') }}</label>
                            <div class="d-flex gap-3">
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="male" value="Male" v-model="gender">
                                <label class="form-check-label" for="male">{{ __('customer.male') }}</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="female" value="Female" v-model="gender">
                                <label class="form-check-label" for="female">{{ __('customer.female') }}</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="other" value="Other" v-model="gender">
                                <label class="form-check-label" for="other">{{ __('customer.other') }}</label>
                              </div>
                            </div>
                            <span class="error text-danger gender-error"></span>
                          </div> --}}

                        <div class="mb-3 gender-wrapper">
                            <label class="form-label">
                                {{ __('customer.lbl_gender') }}
                                <span class="required-star text-danger">*</span>
                            </label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="male" value="Male" v-model="gender">
                                    <label class="form-check-label" for="male">{{ __('customer.male') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="female" value="Female" v-model="gender">
                                    <label class="form-check-label" for="female">{{ __('customer.female') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="other" value="Other" v-model="gender">
                                    <label class="form-check-label" for="other">{{ __('customer.other') }}</label>
                                </div>
                            </div>
                            <span class="error text-danger gender-error"></span>
                        </div>


                        {{-- <div class="mb-3">
                            <label class="form-label">{{ __('customer.relation') }}</label>
                            <select class="form-select" id="relation" name="relation" v-model="relation">
                            <option value="">{{__('messages.select_relation')}}</option>
                              <option value="Parents">{{ __('customer.parents') }}</option>
                              <option value="Siblings">{{ __('customer.siblings') }}</option>
                              <option value="Spouse">{{ __('customer.spouse') }}</option>
                              <option value="Others">{{ __('customer.other') }}</option>
                            </select>
                            <span class="error text-danger"></span>
                          </div> --}}

                        <div class="mb-3">
                            <label class="form-label">
                                {{ __('customer.relation') }}
                                <span class="required-star text-danger">*</span>
                            </label>
                            <select class="form-select" id="relation" name="relation" v-model="relation">
                                <option value="">{{ __('messages.select_relation') }}</option>
                                <option value="Parents">{{ __('customer.parents') }}</option>
                                <option value="Siblings">{{ __('customer.siblings') }}</option>
                                <option value="Spouse">{{ __('customer.spouse') }}</option>
                                <option value="Others">{{ __('customer.other') }}</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>


                          <div class="mb-3">
                            <label for="profile_image" class="form-label">{{ __('customer.lbl_profile_image') }}</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('customer.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('customer.save_patient') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($otherPatients->isNotEmpty())
    @foreach ($otherPatients as $otherPatient)
    <div class="modal fade" id="editModal_{{ $otherPatient->id }}" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('backend.customers.otherPatient.update', $otherPatient->id) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="edit-form"
                        data-patient-id="{{ $otherPatient->id }}">

                    @csrf
                @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">{{ __('customer.edit_patient_details') }} </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="first_name_{{ $otherPatient->id }}" class="form-label">{{ __('customer.lbl_first_name') }}</label><span class="required-star text-danger" style="display:none;">*</span>
                            <input type="text"
                                class="form-control"
                                id="first_name_{{ $otherPatient->id }}"
                                name="first_name"
                                value="{{ $otherPatient->first_name }}"
                                placeholder="{{ __('clinic.lbl_first_name') }}">
                                <span class="error text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label for="last_name_{{ $otherPatient->id }}" class="form-label">{{ __('customer.lbl_last_name') }}</label><span class="required-star text-danger" style="display:none;">*</span>
                            <input type="text"
                                class="form-control"
                                id="last_name_{{ $otherPatient->id }}"
                                name="last_name"
                                value="{{ $otherPatient->last_name }}"
                                placeholder="{{ __('clinic.lbl_last_name') }}">
                                <span class="error text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label for="dob_{{ $otherPatient->id }}" class="form-label">{{ __('customer.lbl_date_of_birth') }}</label><span class="required-star text-danger" style="display:none;">*</span>
                            <input type="date"
                                class="form-control"
                                id="dob_{{ $otherPatient->id }}"
                                name="dob"
                                value="{{ $otherPatient->dob }}"
                                placeholder="{{ __('customer.select_date_of_birth') }}">
                                <span class="error text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label for="contactNumber_{{ $otherPatient->id }}" class="form-label">{{ __('customer.lbl_phone_number') }}</label><span class="required-star text-danger" style="display:none;">*</span>
                            <input type="text"
                                class="form-control"
                                id="contactNumber_{{ $otherPatient->id }}"
                                name="contactNumber"
                                value="{{ $otherPatient->contactNumber }}"
                                placeholder="{{ __('employee.lbl_phone_number_placeholder') }}">
                                <span class="error text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gender</label><span class="required-star text-danger" style="display:none;">*</span>
                            <div class="d-flex gap-3">
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="male_{{ $otherPatient->id }}" value="Male" {{ $otherPatient->gender == 'Male' ? 'checked' : '' }}>
                                <label class="form-check-label" for="male_{{ $otherPatient->id }}">{{ __('messages.male') }}</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="female_{{ $otherPatient->id }}" value="Female" {{ $otherPatient->gender == 'Female' ? 'checked' : '' }}>
                                <label class="form-check-label" for="female_{{ $otherPatient->id }}">{{ __('customer.female') }}</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" id="other_{{ $otherPatient->id }}" value="Other" {{ $otherPatient->gender == 'Other' ? 'checked' : '' }}>
                                <label class="form-check-label" for="other_{{ $otherPatient->id }}">{{ __('customer.other') }}</label>
                              </div>
                            </div>
                            <span class="error text-danger gender-error"></span>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Relation</label><span class="required-star text-danger" style="display:none;">*</span>
                            <select class="form-select" name="relation">
                                <option value="Parents" {{ $otherPatient->relation == 'Parents' ? 'selected' : '' }}>{{ __('customer.parents') }}</option>
                                <option value="Siblings" {{ $otherPatient->relation == 'Siblings' ? 'selected' : '' }}>{{ __('customer.siblings') }}</option>
                                <option value="Spouse" {{ $otherPatient->relation == 'Spouse' ? 'selected' : '' }}>{{ __('customer.spouse') }}</option>
                                <option value="Others" {{ $otherPatient->relation == 'Others' ? 'selected' : '' }}>{{ __('customer.other') }}</option>
                            </select>
                            <span class="error text-danger"></span>
                          </div>
                          <div class="mb-3">
                            <label for="profile_image_{{ $otherPatient->id }}" class="form-label">{{ __('customer.lbl_profile_image') }}</label>
                            <input type="file" class="form-control" id="profile_image_{{ $otherPatient->id }}" name="profile_image">
                            @if ($otherPatient->getFirstMediaUrl('profile_image'))
                                <div class="mt-2">
                                    <strong>Current Image:</strong><br>
                                    <img src="{{ $otherPatient->getFirstMediaUrl('profile_image') }}" alt="Profile Image" width="100">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('customer.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('customer.save_changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endif

@endsection

@push('after-scripts')
<script src="{{ mix('modules/customer/script.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const overviewBtn = document.getElementById('overview-btn');
    const detailsBtn = document.getElementById('details-btn');
    const overviewContent = document.getElementById('overview-content');
    const detailsContent = document.getElementById('details-content');

    // Default state: Show overview, hide details
    overviewContent.style.display = 'block';
    detailsContent.style.display = 'none';

    // Overview button click
    overviewBtn.addEventListener('click', () => {
      overviewContent.style.display = 'block';
      detailsContent.style.display = 'none';
      overviewBtn.classList.add('btn-primary');
      overviewBtn.classList.remove('btn-secondary');
      detailsBtn.classList.remove('btn-primary');
      detailsBtn.classList.add('btn-secondary');
    });

    // Details button click
    detailsBtn.addEventListener('click', () => {
      overviewContent.style.display = 'none';
      detailsContent.style.display = 'block';
      detailsBtn.classList.add('btn-primary');
      detailsBtn.classList.remove('btn-secondary');
      overviewBtn.classList.remove('btn-primary');
      overviewBtn.classList.add('btn-secondary');
    });
  });

  document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("addPatientForm");
    const modalElement = document.getElementById("addOtherPatientModal");
    const modal = new bootstrap.Modal(modalElement); // Initialize Bootstrap Modal

    // Extract ID from URL
    const urlSegments = window.location.pathname.split('/');
    const userId = urlSegments[urlSegments.length - 1]; // Get last part of the URL

    // Add event listener to handle form submission
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Clear previous error messages
        form.querySelectorAll(".error").forEach(el => el.textContent = '');

        const firstName = form.querySelector('[name="first_name"]');
        const lastName = form.querySelector('[name="last_name"]');
        const dob = form.querySelector('[name="dob"]');
        const contactNumber = form.querySelector('[name="contactNumber"]');
        const gender = form.querySelector('[name="gender"]:checked');
        const relation = form.querySelector('[name="relation"]');

        // Reset validation if there are missing fields
        if (!firstName.value.trim() || !lastName.value.trim() || !dob.value.trim() || !contactNumber.value.trim() || !gender || !relation.value.trim()) {
            if (!firstName.value.trim()) {
                const container = firstName.closest('.mb-3');
                container.querySelector('.error').textContent = 'First Name field is required.';
                container.querySelector('.required-star').style.display = 'inline';
            } else {
                const container = firstName.closest('.mb-3');
                container.querySelector('.error').textContent = '';
                container.querySelector('.required-star').style.display = 'none';
            }

            if (!lastName.value.trim()) {
                const container = lastName.closest('.mb-3');
                container.querySelector('.error').textContent = 'Last Name field is required.';
                container.querySelector('.required-star').style.display = 'inline';
            } else {
                const container = lastName.closest('.mb-3');
                container.querySelector('.error').textContent = '';
                container.querySelector('.required-star').style.display = 'none';
            }

            if (!dob.value.trim()) {
                const container = dob.closest('.mb-3');
                container.querySelector('.error').textContent = 'Date of Birth field is required.';
                container.querySelector('.required-star').style.display = 'inline';
            } else {
                const container = dob.closest('.mb-3');
                container.querySelector('.error').textContent = '';
                container.querySelector('.required-star').style.display = 'none';
            }

            if (!contactNumber.value.trim()) {
                const container = contactNumber.closest('.mb-3');
                container.querySelector('.error').textContent = 'Phone Number field is required.';
                container.querySelector('.required-star').style.display = 'inline';
            } else {
                const container = contactNumber.closest('.mb-3');
                container.querySelector('.error').textContent = '';
                container.querySelector('.required-star').style.display = 'none';
            }

            if (!gender) {
                const genderContainer = form.querySelector('.gender-wrapper');
                const genderError = genderContainer.querySelector('.gender-error');
                const genderStar = genderContainer.querySelector('.required-star');
                genderError.textContent = 'Gender field is required.';
                genderStar.style.display = 'inline';
            } else {
                const genderContainer = form.querySelector('.gender-wrapper');
                const genderError = genderContainer.querySelector('.gender-error');
                const genderStar = genderContainer.querySelector('.required-star');
                genderError.textContent = '';
                genderStar.style.display = 'none';
            }

            if (!relation.value.trim()) {
                const container = relation.closest('.mb-3');
                container.querySelector('.error').textContent = 'Relation field is required.';
                container.querySelector('.required-star').style.display = 'inline';
            } else {
                const container = relation.closest('.mb-3');
                container.querySelector('.error').textContent = '';
                container.querySelector('.required-star').style.display = 'none';
            }

            return; // Stop form submission if validation fails
        }

        // Proceed with form submission if all fields are valid
        const formData = new FormData(form);
        formData.append("user_id", userId); // Pass extracted ID as user_id

        fetch("{{ route('backend.appointment.other_patient') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "Accept": "application/json"
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                $("#addOtherPatientModal").modal("hide");
                successSnackbar("Patient added successfully!");

                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        })
        .catch(error => console.error("Error:", error));
    });

    // Add event listener to hide validation and required star when the modal is closed
    modalElement.addEventListener('hidden.bs.modal', function () {
        // Clear error messages
        form.querySelectorAll(".error").forEach(el => el.textContent = '');

        // Hide the red required asterisk
        form.querySelectorAll(".required-star").forEach(el => el.style.display = 'none');
    });
});


  // Update the delete confirmation handler
//   document.querySelectorAll('.delete-patient').forEach(button => {
//       button.addEventListener('click', function(e) {
//           e.preventDefault(); // Prevent form submission
//           const patientId = this.dataset.id;
//           const patientName = this.dataset.name;
//           const deleteForm = this.closest('form'); // Get the parent form

//           Swal.fire({
//               title: '{{ __("messages.are_you_sure") }}',
//               html: `{{ __("messages.delete_confirm") }} <br><strong>${patientName}</strong>?`,
//               icon: 'warning',
//               showCancelButton: true,
//               confirmButtonColor: '#d33',
//               cancelButtonColor: '#3085d6',
//               confirmButtonText: '{{ __("messages.yes_delete") }}',
//               cancelButtonText: '{{ __("messages.cancel") }}'
//           }).then((result) => {
//               if (result.isConfirmed) {
//                   // Show loading state


//                   // Submit form with fetch to handle response
//                   fetch(deleteForm.action, {
//                       method: 'POST',
//                       body: new FormData(deleteForm),
//                       headers: {
//                           'X-Requested-With': 'XMLHttpRequest'
//                       }
//                   })
//                   .then(response => response.json())
//                   .then(data => {
//                       Swal.fire({
//                           title: '{{ __("messages.deleted") }}',
//                           text: '{{ __("messages.delete_success") }}',
//                           icon: 'success',
//                           timer: 2000,
//                           showConfirmButton: false
//                       }).then(() => {
//                           // Reload page after success message
//                           window.location.reload();
//                       });
//                   })
//                   .catch(error => {
//                       Swal.fire({
//                           title: '{{ __("messages.error") }}',
//                           text: '{{ __("messages.delete_error") }}',
//                           icon: 'error'
//                       });
//                   });
//               }
//           });
//       });
//   });


  document.querySelectorAll('.delete-patient').forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault();

        const deleteUrl = this.dataset.url;
        const patientName = this.dataset.name;

        Swal.fire({
            title: '{{ __("messages.are_you_sure") }}',
            html: `{{ __("messages.delete_confirm") }} <br><strong>${patientName}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{ __("messages.yes_delete") }}',
            cancelButtonText: '{{ __("messages.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                        'Accept': 'application/json'
                    }
                })

                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire({
                            title: '{{ __("messages.deleted") }}',
                            text: '{{ __("messages.delete_success") }}',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: '{{ __("messages.error") }}',
                            text: data.message ?? '{{ __("messages.delete_error") }}',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire({
                        title: '{{ __("messages.error") }}',
                        text: '{{ __("messages.delete_error") }}',
                        icon: 'error'
                    });
                });
            }
        });
    });
});

 document.querySelectorAll('.edit-form').forEach(function (form) {
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        form.querySelectorAll(".error").forEach(el => el.remove());
        form.querySelectorAll(".required-star").forEach(el => el.style.display = "none");

        let hasError = false;
        const patientId = form.dataset.patientId;

        const firstName = form.querySelector('[name="first_name"]');
        const lastName = form.querySelector('[name="last_name"]');
        const dob = form.querySelector('[name="dob"]');
        const contactNumber = form.querySelector('[name="contactNumber"]');
        const gender = form.querySelector('[name="gender"]:checked');
        const relation = form.querySelector('[name="relation"]');

        // First Name
        if (!firstName.value.trim()) {
            firstName.insertAdjacentHTML('afterend', '<small class="text-danger error">First Name is required.</small>');
            form.querySelector(`#first_name_${patientId}`).closest('.mb-3').querySelector('.required-star').style.display = 'inline';
            hasError = true;
        }

        // Last Name
        if (!lastName.value.trim()) {
            lastName.insertAdjacentHTML('afterend', '<small class="text-danger error">Last Name is required.</small>');
            form.querySelector(`#last_name_${patientId}`).closest('.mb-3').querySelector('.required-star').style.display = 'inline';
            hasError = true;
        }

        // DOB
        if (!dob.value.trim()) {
            dob.insertAdjacentHTML('afterend', '<small class="text-danger error">Date of Birth is required.</small>');
            form.querySelector(`#dob_${patientId}`).closest('.mb-3').querySelector('.required-star').style.display = 'inline';
            hasError = true;
        }

        // Contact
        if (!contactNumber.value.trim()) {
            contactNumber.insertAdjacentHTML('afterend', '<small class="text-danger error">Phone Number is required.</small>');
            form.querySelector(`#contactNumber_${patientId}`).closest('.mb-3').querySelector('.required-star').style.display = 'inline';
            hasError = true;
        }

        // Gender
        if (!gender) {
            const genderWrapper = form.querySelector('.mb-3 .required-star').closest('.mb-3');
            genderWrapper.querySelector('.required-star').style.display = 'inline';
            form.querySelector('.gender-error').innerText = 'Gender is required.';
            hasError = true;
        }

        // Relation
        if (!relation.value.trim()) {
            relation.insertAdjacentHTML('afterend', '<small class="text-danger error">Relation is required.</small>');
            form.querySelector('[name="relation"]').closest('.mb-3').querySelector('.required-star').style.display = 'inline';
            hasError = true;
        }

        if (hasError) return;

        const formData = new FormData(form);
        const actionUrl = form.getAttribute("action");

        fetch(actionUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "Accept": "application/json"
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                successSnackbar("Patient updated successfully!");
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        })
        .catch(error => console.error("Error:", error));
    });

    // Reset validation when modal is closed
    const modal = new bootstrap.Modal(form.closest('.modal')); // Assuming each edit-form is inside a modal
    const modalElement = form.closest('.modal');

    modalElement.addEventListener('hidden.bs.modal', function () {
        // Reset error messages and required-star display when modal closes
        form.querySelectorAll(".error").forEach(el => el.remove());
        form.querySelectorAll(".required-star").forEach(el => el.style.display = "none");
    });
});

</script>
@endpush
