<div class="d-flex gap-3 align-items-center">
<button type='button' data-assign-module="{{$data->id}}" data-assign-target='#session-form-offcanvas' data-assign-event='employee_assign' class='btn text-success p-0 fs-6' data-bs-toggle='tooltip' title='{{ __('clinic.lbl_session') }}'><i class='ph ph-clock-user'></i>
</button>
<button type='button' data-assign-module="{{$data->id}}" data-assign-target='#doctorDetails-offcanvas' data-assign-event='doctor-details' class='btn text-secondary p-0 fs-5' data-bs-toggle='tooltip' title='{{ __('clinic.lbl_view') }}'><i class="ph ph-eye align-middle"></i>
</button>
@unless(auth()->user()->hasRole('receptionist'))  
   <button type='button' data-assign-module="{{ $data->id }}" data-assign-target='#doctor_change_password' data-assign-event='doctor_assign' class='btn text-info p-0 fs-5' data-bs-toggle="tooltip" title="{{ __('messages.change_password') }}"><i class="ph ph-key align-middle"></i></button>
   @endunless
   @hasPermission('edit_doctors')
      <button type="button" class="btn text-success p-0 fs-5" data-crud-id="{{$data->id}}" title="{{ __('messages.edit') }} " data-bs-toggle="tooltip"> <i class="ph ph-pencil-simple-line align-middle"></i></button>
   @endhasPermission
  @hasPermission('delete_doctors')
      <a href="{{route("backend.$module_name.destroy", $data->id)}}" id="delete-{{$module_name}}-{{$data->id}}" class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{csrf_token()}}" data-bs-toggle="tooltip" title="{{__('messages.delete')}}" data-confirm="{{ __('messages.are_you_sure?', ['form' => ($data->full_name) ?? __('Unknown'), 'module' => __('appointment.lbl_doctor')])  }}"> <i class="ph ph-trash align-middle"></i></a>
   @endhasPermission


   @if($customform)

      @foreach($customform as $form)

         @php
            $formdata=json_decode($form->formdata);
            $clinic= json_decode($form->appointment_status);

         @endphp

         {{--@if(in_array($data->clinic, $appointment_status) && $data->status !== null) --}}

         <button type="button"
            data-assign-target="#customform-offcanvas"
            data-assign-event="custom_form_assign"
            data-appointment-type="doctor"
            data-appointment-id="{{ $data->id }}"
            data-form-id="{{ $form->id }}"
            class="btn text-info p-0 fs-5"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="{{ $formdata->form_title }}"
            onclick="dispatchCustomEvent(this)">
            <i class="icon ph ph-file align-middle"></i>
         </button>
         {{-- @endif --}}
      @endforeach
   @endif
</div>



