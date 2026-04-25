@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header"><h4>{{ $page_title }}</h4></div>
    <div class="card-body">
        <form id="form" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>EN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name_en" value="{{ $product->name_en ?? '' }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>AR <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name_ar" value="{{ $product->name_ar ?? '' }}" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Category <span class="text-danger">*</span></label>
                        <select class="form-control" name="category_id" required onchange="loadSubcategories()">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ isset($product) && $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Subcategory <span class="text-danger">*</span></label>
                        <select class="form-control" name="sub_category_id" id="subcat" required>
                            <option value="">Select Subcategory</option>
                            @if(isset($product) && $product->subcategory)
                                <option value="{{ $product->sub_category_id }}" selected>{{ $product->subcategory->name_en }}</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Brand</label>
                        <select class="form-control" name="brand_id">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ isset($product) && $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Product Type</label>
                        <select class="form-control" name="product_type_id">
                            <option value="">Select Type</option>
                            @foreach($productTypes as $type)
                                <option value="{{ $type->id }}" {{ isset($product) && $product->product_type_id == $type->id ? 'selected' : '' }}>{{ $type->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group mb-3">
                <label>Description</label>
                <textarea class="form-control" name="description" rows="3">{{ $product->description ?? '' }}</textarea>
            </div>
            <div class="form-group mb-3">
                <label>Image</label>
                <input type="file" class="form-control" name="image" accept="image/*">
                @if(isset($product) && $product->getFirstMediaUrl('product_image'))
                    <img src="{{ $product->getFirstMediaUrl('product_image') }}" style="max-width: 150px; margin-top: 10px;">
                @endif
            </div>
            <div class="form-group mb-3">
                <label><input type="checkbox" name="requires_prescription" value="1" {{ isset($product) && $product->requires_prescription ? 'checked' : '' }}> Requires Prescription</label>
            </div>
            <div class="form-group mb-3">
                <label><input type="checkbox" name="is_active" value="1" {{ isset($product) && $product->is_active ? 'checked' : 'checked' }}> Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('backend.pharmacy-products.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<script>
    function loadSubcategories() {
        const catId = document.querySelector('[name=category_id]').value;
        if (!catId) return;
        fetch(`{{ route('backend.pharmacy-categories.children', '') }}/${catId}`).then(r => r.json()).then(d => {
            document.getElementById('subcat').innerHTML = '<option value="">Select</option>' + d.data.map(s => `<option value="${s.id}">${s.name_en}</option>`).join('');
        });
    }
    document.getElementById('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fetch(@if(isset($product)) '{{ route('backend.pharmacy-products.update', $product->id) }}' @else '{{ route('backend.pharmacy-products.store') }}' @endif, {
            method: @if(isset($product)) 'PUT' @else 'POST' @endif,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: fd
        }).then(r => r.json()).then(d => { if (d.status) location.href = '{{ route('backend.pharmacy-products.index') }}'; });
    });
    loadSubcategories();
</script>
@endsection
