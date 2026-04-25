@extends('frontend::layouts.master')
@section('title', __('frontend.doctors'))

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container-fluid">

            {{-- ── Page header ── --}}
            <div class="pp-page-header">
                <h1 class="pp-page-title">{{ __('frontend.doctors') }}</h1>
            </div>

            {{-- ── Filter row ── --}}
            <div class="row gy-2 mb-3 align-items-end">
                <div class="col-xl-7 col-lg-9">
                    <div class="pp-filter-bar">
                        <label class="pp-filter-label">{{ __('frontend.filter_by') }}:</label>
                        <div class="row gx-2 gy-2 flex-grow-1">
                            <div class="col-md-4">
                                <select name="clinic" id="clinic"
                                        class="pp-filter-select select2 w-100"
                                        data-filter="select"
                                        aria-label="{{ __('service.lbl_clinic') }}">
                                    <option value="">{{ __('service.all') }} {{ __('service.lbl_clinic') }}</option>
                                    @foreach ($clinics as $clinic)
                                        <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="service" id="service"
                                        class="pp-filter-select select2 w-100"
                                        data-filter="select"
                                        aria-label="{{ __('frontend.select_service') }}">
                                    <option value="" disabled selected>{{ __('frontend.select_service') }}</option>
                                    <option value="">{{ __('service.all') }} {{ __('service.designation') }}</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="specialization" id="specialization"
                                        class="pp-filter-select select2 w-100"
                                        data-filter="select"
                                        multiple="multiple"
                                        aria-label="{{ __('specialization.lbl_specializations') }}">
                                    @foreach ($specializations as $spec)
                                        <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="rating" id="rating"
                                        class="pp-filter-select select2 w-100"
                                        data-filter="select"
                                        aria-label="{{ __('frontend.rating') }}">
                                    <option value="" selected>{{ __('service.all') }} {{ __('rating') }}</option>
                                    <option value="1-2">1 – 2 ★</option>
                                    <option value="2-3">2 – 3 ★</option>
                                    <option value="3-4">3 – 4 ★</option>
                                    <option value="4-5">4 – 5 ★</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 d-xl-block d-none"></div>

                {{-- Search --}}
                <div class="col-lg-3">
                    <div class="pp-filter-bar"
                         style="background:var(--pp-surface);border:1px solid var(--pp-border-mid);border-radius:var(--pp-r-sm);padding:0.35rem 0.75rem">
                        <i class="ph ph-magnifying-glass" style="color:var(--pp-muted);flex-shrink:0"></i>
                        <input type="text"
                               id="datatable-search"
                               class="pp-input"
                               style="border:none;background:transparent;padding:0;box-shadow:none"
                               placeholder="{{ __('messages.search') }}…"
                               aria-label="{{ __('messages.search') }}">
                    </div>
                </div>
            </div>

            {{-- ── Active filter pills ── --}}
            <div id="selected-filters" class="d-flex gap-2 flex-wrap mb-3" role="list"
                 aria-label="{{ __('frontend.active_filters') ?? 'Active filters' }}"></div>

            {{-- ── Doctor grid ── --}}
            <div class="card-style-slider">
                <div id="shimmer-loader" class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-3 p-1">
                    @for ($i = 0; $i < 8; $i++)
                        @include('frontend::components.card.shimmer_doctor_card')
                    @endfor
                </div>
                <table id="datatable" class="table table-responsive custom-card-table d-none"></table>
            </div>

        </div>
    </div>
@endsection

@push('after-scripts')
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

<script type="text/javascript">
(function () {
    'use strict';

    var finalColumns = [{
        data:       'card',
        name:       'card',
        orderable:  false,
        searchable: true
    }];

    document.addEventListener('DOMContentLoaded', function () {

        var clinicFilter  = $('#clinic');
        var serviceFilter = $('#service');
        var ratingFilter  = $('#rating');
        var specFilter    = $('#specialization');
        var selectedDiv   = document.getElementById('selected-filters');

        /* ── Select2 init ── */
        clinicFilter.select2({
            placeholder: '{{ __('service.lbl_clinic') }}',
            allowClear: true,
            width: '100%'
        });
        serviceFilter.select2({
            placeholder: '{{ __('frontend.select_service') }}',
            allowClear: true,
            width: '100%'
        });
        ratingFilter.select2({
            placeholder: '{{ __('frontend.rating') }}',
            allowClear: true,
            width: '100%'
        });
        specFilter.select2({
            placeholder: '{{ __('specialization.lbl_specializations') }}',
            allowClear: true,
            width: '100%'
        });

        /* ── Filter pill rendering (CSS-class based, no inline styles) ── */
        function updateFilterPills() {
            selectedDiv.innerHTML = '';

            var items = [
                { key: 'clinic',  val: clinicFilter.val(),  label: clinicFilter.find('option:selected').text() },
                { key: 'service', val: serviceFilter.val(), label: serviceFilter.find('option:selected').text() },
                { key: 'rating',  val: ratingFilter.val(),  label: ratingFilter.find('option:selected').text() }
            ];

            var specVals = specFilter.val() || [];
            specFilter.find('option:selected').each(function () {
                items.push({ key: 'specialization', val: $(this).val(), label: $(this).text() });
            });

            items.forEach(function (item) {
                if (!item.val || item.label.toLowerCase().includes('all')) return;

                var pill = document.createElement('span');
                pill.className   = 'pp-filter-pill';
                pill.setAttribute('role', 'listitem');
                pill.textContent = item.label + ' ';

                var btn = document.createElement('button');
                btn.type      = 'button';
                btn.className = 'pp-filter-pill-remove';
                btn.setAttribute('aria-label', 'Remove ' + item.label + ' filter');
                btn.innerHTML = '<i class="ph ph-x" aria-hidden="true"></i>';
                btn.addEventListener('click', function () {
                    clearFilter(item.key, item.val);
                });

                pill.appendChild(btn);
                selectedDiv.appendChild(pill);
            });
        }

        function clearFilter(key, val) {
            if (key === 'specialization' && val) {
                var current = specFilter.val() || [];
                current = current.filter(function (v) { return v !== val; });
                specFilter.val(current).trigger('change');
            } else {
                var map = { clinic: clinicFilter, service: serviceFilter, rating: ratingFilter };
                if (map[key]) map[key].val('').trigger('change');
            }
            if (window.renderedDataTable) window.renderedDataTable.draw();
        }

        /* ── Listen for filter changes ── */
        [clinicFilter, serviceFilter, ratingFilter, specFilter].forEach(function (f) {
            f.on('change', function () {
                updateFilterPills();
                if (window.renderedDataTable) window.renderedDataTable.draw();
            });
        });

        /* ── DataTable ── */
        var shimmer = document.getElementById('shimmer-loader');
        var tableEl = document.getElementById('datatable');

        frontInitDatatable({
            url: '{{ route('doctor.index_data') }}',
            finalColumns: finalColumns,
            advanceFilter: function () {
                return {
                    clinic_id:  clinicFilter.val(),
                    service_id: serviceFilter.val(),
                    rating:     ratingFilter.val(),
                    specialization_ids: specFilter.val()
                };
            },
            onLoadStart: function () {
                if (shimmer) shimmer.classList.remove('d-none');
                if (tableEl) tableEl.classList.add('d-none');
            },
            onLoadComplete: function () {
                if (shimmer) shimmer.classList.add('d-none');
                if (tableEl) tableEl.classList.remove('d-none');
            }
        });

        /* ── Search ── */
        var searchInput = document.getElementById('datatable-search');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                if (window.renderedDataTable) {
                    window.renderedDataTable.search(this.value).draw();
                }
            });
        }

        updateFilterPills();
    });
}());
</script>
@endpush
