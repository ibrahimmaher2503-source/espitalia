@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header"><h4>{{ $page_title }}</h4></div>
    <div class="card-body">
        <form id="form">
            @csrf
            <div class="form-group mb-3">
                <label>EN <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name_en" value="{{ $type->name_en ?? '' }}" required>
            </div>
            <div class="form-group mb-3">
                <label>AR <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name_ar" value="{{ $type->name_ar ?? '' }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('backend.pharmacy-product-types.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<script>
    document.getElementById('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const data = {name_en: document.querySelector('[name=name_en]').value, name_ar: document.querySelector('[name=name_ar]').value};
        fetch(@if(isset($type)) '{{ route('backend.pharmacy-product-types.update', $type->id) }}' @else '{{ route('backend.pharmacy-product-types.store') }}' @endif, {
            method: @if(isset($type)) 'PUT' @else 'POST' @endif,
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify(data)
        }).then(r => r.json()).then(d => { if (d.status) location.href = '{{ route('backend.pharmacy-product-types.index') }}'; });
    });
</script>
@endsection
