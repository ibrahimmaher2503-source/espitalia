@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header"><h4>{{ $page_title }}</h4></div>
    <div class="card-body">
        <form id="form" enctype="multipart/form-data">
            @csrf
            <div class="form-group mb-3">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="{{ $pharmacy->name ?? '' }}" required>
            </div>
            <div class="form-group mb-3">
                <label>Phone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" name="phone" value="{{ $pharmacy->phone ?? '' }}" required>
            </div>
            <div class="form-group mb-3">
                <label>Address <span class="text-danger">*</span></label>
                <textarea class="form-control" name="address" required>{{ $pharmacy->address ?? '' }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Latitude</label>
                        <input type="number" step="0.000001" class="form-control" name="latitude" value="{{ $pharmacy->latitude ?? '' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Longitude</label>
                        <input type="number" step="0.000001" class="form-control" name="longitude" value="{{ $pharmacy->longitude ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="form-group mb-3">
                <label>Delivery Fee</label>
                <input type="number" step="0.01" class="form-control" name="delivery_fee" value="{{ $pharmacy->delivery_fee ?? '0' }}">
            </div>
            <div class="form-group mb-3">
                <label>{{ __('pharmacy.lbl_commission_template') }}</label>
                <select class="form-control" name="commission_id">
                    <option value="">-- {{ __('messages.select') }} --</option>
                    @foreach($commissions ?? [] as $commission)
                        <option value="{{ $commission->id }}" {{ isset($pharmacy) && $pharmacy->commission_id === $commission->id ? 'selected' : '' }}>
                            {{ $commission->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3">
                <label>Logo</label>
                <input type="file" class="form-control" name="logo" accept="image/*">
                @if(isset($pharmacy) && $pharmacy->getFirstMediaUrl('logo'))
                    <img src="{{ $pharmacy->getFirstMediaUrl('logo') }}" style="max-width: 150px; margin-top: 10px;">
                @endif
            </div>
            <div class="form-group mb-3">
                <label><input type="checkbox" name="is_active" value="1" {{ isset($pharmacy) && $pharmacy->is_active ? 'checked' : 'checked' }}> Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('backend.pharmacies.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<script>
    document.getElementById('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fetch(@if(isset($pharmacy)) '{{ route('backend.pharmacies.update', $pharmacy->id) }}' @else '{{ route('backend.pharmacies.store') }}' @endif, {
            method: @if(isset($pharmacy)) 'PUT' @else 'POST' @endif,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: fd
        }).then(r => r.json()).then(d => { if (d.status) location.href = '{{ route('backend.pharmacies.index') }}'; });
    });
</script>
@endsection
