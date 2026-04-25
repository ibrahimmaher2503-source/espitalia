@if($data->incident_type != '')

<select name="branch_for" class="select2 change-select" data-token="{{csrf_token()}}" data-url="{{route('backend.incidence.updateStatus', ['id' => $data->id, 'action_type' => 'update-status'])}}" style="width: 100%;">
  @foreach ($appointment_status as $key => $value )
    <option value="{{$value}}" {{$data->incident_type == $value ? 'selected' : ''}} >{{$key}}</option>
  @endforeach
</select>
@else
<span class="text-capitalize badge bg-soft-success p-3"> {{ $data->incident_type }}</span>
@endif