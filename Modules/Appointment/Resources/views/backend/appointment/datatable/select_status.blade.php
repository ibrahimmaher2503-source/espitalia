@if($data->status != 'completed' && $data->status != 'check_in' && $data->status != 'checkout' && $data->status != 'cancelled')
{{-- @if(!in_array($data->status, ['completed', 'check_in', 'checkout', 'cancelled'])) --}}
<select name="branch_for" class="select2 change-select"
        data-token="{{ csrf_token() }}"
        data-id="{{ $data->id }}",
        data-charge="{{ $data->getCancellationCharges() ?? 0 }}"
        style="width: 100%;">
    @foreach ($status as $key => $value)
        <option value="{{ $value->name }}" {{ $data->status == $value->name ? 'selected' : '' }}>
            {{ $value['value'] }}
        </option>
    @endforeach
</select>
{{-- @endif --}}
@elseif($data->status == 'cancelled')
<span class="text-capitalize badge bg-danger-subtle p-2"> {{ str_replace('_', ' ', $data->status) }}</span>

@elseif($data->status == 'check_in')
<span class="text-capitalize badge bg-info-subtle p-2"> {{ str_replace('_', ' ', $data->status) }}</span>

@else
<span class="text-capitalize badge bg-success-subtle p-2"> {{ __('messages.lbl_complete') }}</span>
@endif
<!-- Cancel Modal -->
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.2); z-index: 1055; align-items: center; justify-content: center;">

  <div style="background: #fff; width: 500px; max-width: 90%; height: auto; padding: 30px 25px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); position: relative; text-align: center; animation: fadeIn 0.3s ease;">

    <!-- Circle X Icon (centered at the top) -->
    <div style="width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 2px solid #6c8cff;">
      <span style="font-size: 28px; color: #6c8cff; font-weight: 300;">✕</span>
    </div>

    <h5 style="font-weight: 500; font-size: 20px; color: #333; margin-bottom: 10px;">{{ __('messages.cancel_appointment') }}</h5>

    <p style="color: #777; font-size: 14px; margin-bottom: 20px; line-height: 1.4;">
      {{ __('messages.cancel_this_appointment') }}
      <br>
      {{ __('messages.cancellation_charge') }}
    </p>

    <p id="cancel_charge_info" class="text-danger fw-semibold text-center mb-3" style="font-size: 14px;"></p>

    <!-- Input Reason -->
    <div style="text-align: left; margin-bottom: 20px;">
      <label for="cancel_reason" style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 5px; color: #333;">
        {{ __('messages.lbl_reason') }}<span style="color: red;">*</span>
      </label>
      <input type="text" id="cancel_reason" class="form-control" placeholder=' {{ __('messages.lbl_emergency') }}' style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" />
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 12px; margin-top: 5px;">
      <button onclick="cancelAbort()" style="flex: 1; padding: 10px; border-radius: 6px; background: white; border: 1px solid #ddd; cursor: pointer; font-weight: 500; color: #555;">
        {{ __('messages.cancel') }}
      </button>
      <button id="confirm_btn" onclick="submitCancellation()" style="flex: 1; padding: 10px; border-radius: 6px; background: #f87f7f; border: none; cursor: pointer; font-weight: 500; color: white;">
        {{ __('messages.lbl_confirm') }}
      </button>
    </div>

  </div>
</div>

<script>

  let previousStatus = null;
  let currentSelect = null;
  let selectedStatus = null;
  let appointmentId = null;
  let token = null;
  let cancellation_charge = @json(setting('cancellation_charge'));
  let cancelltion_Type = @json(setting('cancellation_type'));

  $(document).on('focus', '.change-select', function () {
    previousStatus = $(this).val();
  });

  $(document).on('change', '.change-select', function (e) {
    currentSelect = $(this);
    selectedStatus = currentSelect.val();
    appointmentId = currentSelect.data('id');
    token = currentSelect.data('token');

    if (selectedStatus === 'cancelled') {
      let charge = currentSelect.data('charge');

      if (charge > 0) {
        $('#cancel_charge_info').html(`Cancellation charges will be applied: <strong>${currencyFormat(charge)}</strong>`);
      } else {
        $('#cancel_charge_info').html(`{{ __('messages.no_cancellation_charge') }}`);
      }
      $('#cancelModal').css('display', 'flex');
      e.preventDefault();
      currentSelect.blur();
      $('#cancelModal').fadeIn();
    } else {
      updateStatus(selectedStatus);
    }
  });

  function updateStatus(status, reason = null, charge = 0) {


    let url = "{{ route('backend.appointments.updateStatus', ['id' => '__id__', 'action_type' => 'update-status']) }}".replace('__id__', appointmentId);

    $.ajax({
      type: 'POST',
      url: url,
      headers: { 'X-CSRF-TOKEN': token },
      data: {
        value: status,
        ...(status === 'cancelled' ? {
          reason: reason,
          cancellation_charge_amount: charge,
          cancellation_type: cancelltion_Type,
          cancellation_charge: cancellation_charge
        } : {})
      },
      success: function (res) {
        window.successSnackbar(res.message);
        closeCancelModal();
        $('#datatable').DataTable().ajax.reload();
      },
      error: function () {
        console.error('Something went wrong.');
      }
    });
  }

  function closeCancelModal() {
    $('#cancelModal').hide();
    $('#cancel_reason').val('');
  }

  function cancelAbort() {
    closeCancelModal();
    $('#datatable').DataTable().ajax.reload();
  }

  function submitCancellation() {
    const reason = $('#cancel_reason').val();
    let charge = currentSelect.data('charge');

    $('#cancel_reason').removeClass('is-invalid');
    $('#cancel_reason_error').remove();

    if (!reason) {
      $('#cancel_reason').addClass('is-invalid');
      $('#cancel_reason').after('<div id="cancel_reason_error" class="invalid-feedback d-block">{{ __("messages.please_enter_reason") }}</div>');
      return;
    }

    $('#confirm_btn').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...');

    updateStatus('cancelled', reason, charge);
  }

  // Expose cancel and submit functions globally if needed
  window.cancelAbort = cancelAbort;
  window.submitCancellation = submitCancellation;

</script>




