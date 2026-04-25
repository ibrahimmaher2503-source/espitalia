@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header"><h4 class="card-title">{{ $page_title }}</h4></div>
    <div class="card-body">
        <form id="form">
            @csrf
            <div class="form-group mb-3">
                <label for="name" class="form-label">{{ __('pharmacy.lbl_name_en') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $brand->name ?? '' }}" required>
            </div>
            <div class="form-group mb-3">
                <label><input type="checkbox" name="is_active" value="1" {{ isset($brand) && $brand->is_active ? 'checked' : 'checked' }}> {{ __('messages.active') }}</label>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            <a href="{{ route('backend.pharmacy-brands.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </form>
    </div>
</div>
<script>
    document.getElementById('form').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(@if(isset($brand)) '{{ route('backend.pharmacy-brands.update', $brand->id) }}' @else '{{ route('backend.pharmacy-brands.store') }}' @endif, {
            method: @if(isset($brand)) 'PUT' @else 'POST' @endif,
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({name: document.getElementById('name').value, is_active: document.querySelector('[name=is_active]').checked ? 1 : 0})
        }).then(r => r.json()).then(d => { if (d.status) location.href = '{{ route('backend.pharmacy-brands.index') }}'; });
    });
</script>
@endsection
