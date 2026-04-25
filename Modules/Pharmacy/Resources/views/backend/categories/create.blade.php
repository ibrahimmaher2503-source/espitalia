@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <form id="categoryForm" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($category))
                @method('PUT')
            @endif

            <div class="form-group mb-3">
                <label for="name_en" class="form-label">{{ __('pharmacy.lbl_name_en') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name_en" name="name_en" value="{{ $category->name_en ?? '' }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="name_ar" class="form-label">{{ __('pharmacy.lbl_name_ar') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ $category->name_ar ?? '' }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="parent_id" class="form-label">{{ __('messages.parent') }}</label>
                <select class="form-control" id="parent_id" name="parent_id">
                    <option value="">-- {{ __('messages.select') }} --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ isset($category) && $category->parent_id == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="sort_order" class="form-label">{{ __('messages.sort_order') }}</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ $category->sort_order ?? 0 }}" min="0">
            </div>

            <div class="form-group mb-3">
                <label for="image" class="form-label">{{ __('messages.image') }}</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                @if(isset($category) && $category->getFirstMediaUrl('category_image'))
                    <img src="{{ $category->getFirstMediaUrl('category_image') }}" alt="Category Image" style="max-width: 200px; margin-top: 10px;">
                @endif
            </div>

            <div class="form-group mb-3">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" {{ isset($category) && $category->is_active ? 'checked' : 'checked' }}>
                    {{ __('messages.active') }}
                </label>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                <a href="{{ route('backend.pharmacy-categories.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const url = @if(isset($category)) '{{ route('backend.pharmacy-categories.update', $category->id) }}' @else '{{ route('backend.pharmacy-categories.store') }}' @endif;
        const method = @if(isset($category)) 'PUT' @else 'POST' @endif;

        fetch(url, {
            method: method,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: formData
        }).then(r => r.json()).then(data => {
            if (data.status) {
                window.location.href = '{{ route('backend.pharmacy-categories.index') }}';
            }
        });
    });
</script>
@endsection
