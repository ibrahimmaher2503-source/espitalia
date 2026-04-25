@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <form id="staffForm">
            @csrf
            @if(isset($staff)) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">User <span class="text-danger">*</span></label>
                    <select class="form-control" name="user_id" required>
                        <option value="">{{ __('messages.select') }}</option>
                        @foreach(\App\Models\User::orderBy('first_name')->get() as $user)
                            <option value="{{ $user->id }}" {{ isset($staff) && $staff->user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_role') }} <span class="text-danger">*</span></label>
                    <select class="form-control" name="role" required>
                        <option value="owner" {{ isset($staff) && $staff->role == 'owner' ? 'selected' : '' }}>{{ __('pharmacy.role_owner') }}</option>
                        <option value="manager" {{ isset($staff) && $staff->role == 'manager' ? 'selected' : '' }}>{{ __('pharmacy.role_manager') }}</option>
                        <option value="pharmacist" {{ isset($staff) && $staff->role == 'pharmacist' ? 'selected' : '' }}>{{ __('pharmacy.role_pharmacist') }}</option>
                        <option value="cashier" {{ isset($staff) && $staff->role == 'cashier' ? 'selected' : '' }}>{{ __('pharmacy.role_cashier') }}</option>
                        <option value="delivery" {{ isset($staff) && $staff->role == 'delivery' ? 'selected' : '' }}>{{ __('pharmacy.role_delivery') }}</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_license_number') }}</label>
                    <input type="text" class="form-control" name="license_number" value="{{ $staff->license_number ?? '' }}">
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_hired_date') }}</label>
                    <input type="date" class="form-control" name="hired_date" value="{{ isset($staff) && $staff->hired_date ? $staff->hired_date->format('Y-m-d') : '' }}">
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">{{ __('pharmacy.lbl_notes') }}</label>
                <textarea class="form-control" name="notes" rows="3">{{ $staff->notes ?? '' }}</textarea>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" {{ isset($staff) ? ($staff->is_active ? 'checked' : '') : 'checked' }}>
                    {{ __('messages.active') }}
                </label>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            <a href="{{ route('backend.pharmacy-panel-staff.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </form>
    </div>
</div>

<script>
document.getElementById('staffForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const url = @if(isset($staff)) '{{ route('backend.pharmacy-panel-staff.update', $staff->id) }}' @else '{{ route('backend.pharmacy-panel-staff.store') }}' @endif;
    fetch(url, {
        method: @if(isset($staff)) 'PUT' @else 'POST' @endif,
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
        body: JSON.stringify(Object.fromEntries(formData))
    }).then(r => r.json()).then(data => {
        if (data.status) window.location.href = '{{ route('backend.pharmacy-panel-staff.index') }}';
    });
});
</script>
@endsection
