@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header"><h4 class="card-title">{{ $page_title }}</h4></div>
    <div class="card-body">
        <form id="form">
            @csrf
            <div class="form-group mb-3">
                <label for="title" class="form-label">{{ __('pharmacy.lbl_commission_template') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $commission->title ?? '' }}" required>
            </div>
            <div class="form-group mb-3">
                <label for="commission_type" class="form-label">{{ __('pharmacy.lbl_commission_type') }} <span class="text-danger">*</span></label>
                <select class="form-control" id="commission_type" name="commission_type" required>
                    <option value="">-- Select --</option>
                    <option value="percentage" {{ isset($commission) && $commission->commission_type === 'percentage' ? 'selected' : '' }}>{{ __('pharmacy.lbl_percentage') }}</option>
                    <option value="fixed" {{ isset($commission) && $commission->commission_type === 'fixed' ? 'selected' : '' }}>{{ __('pharmacy.lbl_fixed') }}</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="commission_value" class="form-label" id="value-label">{{ __('pharmacy.lbl_commission_value') }} <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="commission_value" name="commission_value" value="{{ $commission->commission_value ?? '' }}" required>
                <small class="form-text text-muted" id="value-hint"></small>
            </div>
            <div class="form-group mb-3">
                <label><input type="checkbox" name="status" value="1" {{ isset($commission) && $commission->status ? 'checked' : 'checked' }}> {{ __('messages.active') }}</label>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            <a href="{{ route('backend.pharmacy-commissions.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </form>
    </div>
</div>
<script>
    document.getElementById('commission_type').addEventListener('change', function() {
        const label = document.getElementById('value-label');
        const hint = document.getElementById('value-hint');
        if (this.value === 'percentage') {
            label.textContent = '{{ __("pharmacy.lbl_percentage") }} (0-100) {{ __("messages.required") }}';
            hint.textContent = '{{ __("pharmacy.lbl_percentage") }}';
        } else {
            label.textContent = '{{ __("pharmacy.lbl_fixed") }} {{ __("messages.required") }}';
            hint.textContent = '{{ __("pharmacy.lbl_fixed") }} {{ __("messages.amount") }}';
        }
    });
    document.getElementById('commission_type').dispatchEvent(new Event('change'));

    document.getElementById('form').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(@if(isset($commission)) '{{ route('backend.pharmacy-commissions.update', $commission->id) }}' @else '{{ route('backend.pharmacy-commissions.store') }}' @endif, {
            method: @if(isset($commission)) 'PUT' @else 'POST' @endif,
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({
                title: document.getElementById('title').value,
                commission_type: document.getElementById('commission_type').value,
                commission_value: document.getElementById('commission_value').value,
                status: document.querySelector('[name=status]').checked ? 1 : 0
            })
        }).then(r => r.json()).then(d => { if (d.status) location.href = '{{ route('backend.pharmacy-commissions.index') }}'; });
    });
</script>
@endsection
