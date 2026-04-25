<div class="row mb-3">
    <!-- Name (Arabic) -->
    <div class="col-12 col-sm-6">
        <div class="form-group">
            {{ html()->label(__('page.lbl_name_ar') . ' <span class="text-danger">*</span>', 'name_ar')->class('form-label') }}
            {{ html()->text('name_ar')->placeholder(__('page.lbl_name_ar'))->class('form-control')->attribute('dir', 'rtl') }}
            @error('name_ar')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Name (English) -->
    <div class="col-12 col-sm-6">
        <div class="form-group">
            {{ html()->label(__('page.lbl_name_en') . ' <span class="text-danger">*</span>', 'name_en')->class('form-label') }}
            {{ html()->text('name_en')->placeholder(__('page.lbl_name_en'))->class('form-control') }}
            @error('name_en')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row mb-3">
    <!-- Description (Arabic) -->
    <div class="col-12 col-sm-6">
        <div class="form-group">
            {{ html()->label(__('page.lbl_description_ar'), 'description_ar')->class('form-label') }}
            {{ html()->textarea('description_ar')->placeholder(__('page.lbl_description_ar'))->class('form-control')->attribute('dir', 'rtl')->attribute('rows', '3') }}
            @error('description_ar')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Description (English) -->
    <div class="col-12 col-sm-6">
        <div class="form-group">
            {{ html()->label(__('page.lbl_description_en'), 'description_en')->class('form-label') }}
            {{ html()->textarea('description_en')->placeholder(__('page.lbl_description_en'))->class('form-control')->attribute('rows', '3') }}
            @error('description_en')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
